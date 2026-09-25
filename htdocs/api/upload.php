<?php
/**
 * புகைப்படங்கள் பதிவேற்றம் (Photo Upload API)
 * Handles direct Cloudinary URL registration and server-side fallback upload
 */

require_once __DIR__ . '/db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// 1. GET கோரிக்கை - Cloudinary உள்ளமைவு விபரங்கள் (Browser Direct Upload-க்கு)
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cloudinary_config') {
    jsonResponse(true, 'Cloudinary Config', [
        'cloud_name' => CLOUDINARY_CLOUD_NAME,
        'upload_preset' => CLOUDINARY_UPLOAD_PRESET,
        'api_key' => CLOUDINARY_API_KEY
    ]);
}

// 2. POST கோரிக்கை - புகைப்படத்தை பதிவேற்றுதல் அல்லது இணைத்தல்
if ($method === 'POST') {
    // A. நேரடி JSON வடிவில் புகைப்பட விபரங்கள் வந்தால் (Cloudinary Direct Browser Upload மூலம் பெறப்பட்டவை)
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if ($data && isset($data['image_url'])) {
        $eventId = intval($data['event_id'] ?? 0);
        $imageUrl = trim($data['image_url']);
        $thumbnailUrl = trim($data['thumbnail_url'] ?? $imageUrl);
        $cloudinaryId = trim($data['cloudinary_id'] ?? '');
        $caption = trim($data['caption'] ?? 'கோவில் நிகழ்வு புகைப்படம்');

        if ($pdo && $eventId > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO event_photos (event_id, image_url, thumbnail_url, cloudinary_id, caption) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$eventId, $imageUrl, $thumbnailUrl, $cloudinaryId, $caption]);
                $photoId = $pdo->lastInsertId();

                // முதல் புகைப்படம் எனில் நிகழ்வின் cover_image-ஐ புதுப்பிக்கவும்
                $chk = $pdo->prepare("SELECT cover_image FROM events WHERE id = ?");
                $chk->execute([$eventId]);
                $ev = $chk->fetch();
                if ($ev && empty($ev['cover_image'])) {
                    $pdo->prepare("UPDATE events SET cover_image = ? WHERE id = ?")->execute([$imageUrl, $eventId]);
                }

                jsonResponse(true, 'புகைப்படம் வெற்றிகரமாகச் சேர்க்கப்பட்டது', [
                    'id' => $photoId,
                    'image_url' => $imageUrl,
                    'thumbnail_url' => $thumbnailUrl
                ]);
            } catch (Exception $e) {
                jsonResponse(false, 'பிழை: ' . $e->getMessage(), null, 500);
            }
        } else {
            jsonResponse(true, 'புகைப்படம் இணைக்கப்பட்டது (Local)', [
                'id' => time(),
                'image_url' => $imageUrl,
                'thumbnail_url' => $thumbnailUrl
            ]);
        }
    }

    // B. கோப்பு (File Upload) நேரடியாக PHP வழியாக வந்தால் (Local Storage Fallback)
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $eventId = intval($_POST['event_id'] ?? 0);
        $caption = trim($_POST['caption'] ?? 'கோவில் புகைப்படம்');

        // அனுமதிக்கப்பட்ட வடிவங்கள்
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            jsonResponse(false, 'JPEG, PNG, WEBP படங்கள் மட்டுமே அனுமதிக்கப்படும்', null, 400);
        }

        // அதிகபட்ச அளவு 10MB
        if ($file['size'] > 10 * 1024 * 1024) {
            jsonResponse(false, 'கோப்பின் அளவு 10MB-க்கு குறைவாக இருக்க வேண்டும்', null, 400);
        }

        $uploadsDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'temple_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $targetPath = $uploadsDir . '/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Relative URL for website
            $publicUrl = 'uploads/' . $newFilename;

            if ($pdo && $eventId > 0) {
                $stmt = $pdo->prepare("INSERT INTO event_photos (event_id, image_url, thumbnail_url, caption) VALUES (?, ?, ?, ?)");
                $stmt->execute([$eventId, $publicUrl, $publicUrl, $caption]);
                $photoId = $pdo->lastInsertId();

                jsonResponse(true, 'புகைப்படம் வெற்றிகரமாக பதிவேற்றப்பட்டது', [
                    'id' => $photoId,
                    'image_url' => $publicUrl,
                    'thumbnail_url' => $publicUrl
                ]);
            } else {
                jsonResponse(true, 'புகைப்படம் சேமிக்கப்பட்டது', [
                    'id' => time(),
                    'image_url' => $publicUrl,
                    'thumbnail_url' => $publicUrl
                ]);
            }
        } else {
            jsonResponse(false, 'கோப்பைப் பதிவேற்றுவதில் தோல்வி', null, 500);
        }
    }

    jsonResponse(false, 'செல்லுபடியற்ற கோப்பு அல்லது தரவு', null, 400);
}
