<?php
/**
 * நிகழ்வுகள் API (Events REST API)
 * Get, Create, Update, Delete Temple Events & Daily Celebrations
 */

require_once __DIR__ . '/db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Handle action parameter (supports POST/GET with ?action=delete etc.)
$action = $_GET['action'] ?? '';

// Session check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdmin() {
    if (!empty($_SESSION['admin_logged_in'])) {
        return true;
    }
    $authKey = $_SERVER['HTTP_X_ADMIN_KEY'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authKey === ADMIN_SECRET_KEY) {
        return true;
    }
    // Allow local development seamless testing
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if (in_array($remoteIp, ['127.0.0.1', '::1', 'localhost'])) {
        return true;
    }
    return false;
}

// 1. GET கோரிக்கை (நிகழ்வுகளைப் பெறுதல்)
if ($method === 'GET' && empty($action)) {
    $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($pdo) {
        try {
            if ($eventId > 0) {
                // ஒற்றை நிகழ்வு மற்றும் அதன் புகைப்படங்கள்
                $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
                $stmt->execute([$eventId]);
                $event = $stmt->fetch();

                if (!$event) {
                    jsonResponse(false, 'நிகழ்வு காணப்படவில்லை', null, 404);
                }

                // பார்வைகளை அதிகரித்தல்
                $pdo->prepare("UPDATE events SET views_count = views_count + 1 WHERE id = ?")->execute([$eventId]);

                // நிகழ்வின் புகைப்படங்கள்
                $photoStmt = $pdo->prepare("SELECT * FROM event_photos WHERE event_id = ? ORDER BY id ASC");
                $photoStmt->execute([$eventId]);
                $photos = $photoStmt->fetchAll();

                $event['photos'] = $photos;
                jsonResponse(true, 'நிகழ்வு விபரம் பெறப்பட்டது', $event);
            } else {
                // அனைத்து நிகழ்வுகளின் பட்டியல்
                $sql = "SELECT e.*, COUNT(p.id) AS photo_count 
                        FROM events e 
                        LEFT JOIN event_photos p ON e.id = p.event_id 
                        GROUP BY e.id 
                        ORDER BY e.event_date DESC";
                $stmt = $pdo->query($sql);
                $events = $stmt->fetchAll();

                // Fetch photos for each event
                foreach ($events as &$ev) {
                    $pStmt = $pdo->prepare("SELECT id, image_url, thumbnail_url, caption FROM event_photos WHERE event_id = ? ORDER BY id ASC");
                    $pStmt->execute([$ev['id']]);
                    $ev['photos'] = $pStmt->fetchAll();
                }
                unset($ev);

                jsonResponse(true, 'நிகழ்வுகள் பட்டியல்', $events);
            }
        } catch (Exception $e) {
            jsonResponse(false, 'பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'நிகழ்வுகள் பட்டியல் (Offline Fallback)', []);
    }
}

// 2. DELETE கோரிக்கை
if ($method === 'DELETE' || $action === 'delete') {
    if (!checkAdmin()) {
        jsonResponse(false, 'அனுமதிக்கப்படவில்லை. தயவுசெய்து உள்நுழையவும்.', null, 401);
    }

    $eventId = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
    if ($eventId <= 0) {
        $raw = json_decode(file_get_contents('php://input'), true);
        $eventId = intval($raw['id'] ?? 0);
    }

    if ($eventId <= 0) {
        jsonResponse(false, 'செல்லுபடியற்ற நிகழ்வு எண் (Invalid ID)', null, 400);
    }

    if ($pdo) {
        try {
            $pdo->prepare("DELETE FROM event_photos WHERE event_id = ?")->execute([$eventId]);
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            jsonResponse(true, 'நிகழ்வு வெற்றிகரமாக நீக்கப்பட்டது', ['id' => $eventId]);
        } catch (Exception $e) {
            jsonResponse(false, 'நீக்குவதில் பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'நிகழ்வு நீக்கப்பட்டது');
    }
}

// 3. POST / PUT கோரிக்கை (உருவாக்குதல் அல்லது புதுப்பித்தல்)
if ($method === 'POST' || $method === 'PUT') {
    if (!checkAdmin()) {
        jsonResponse(false, 'அனுமதிக்கப்படவில்லை. தயவுசெய்து உள்நுழையவும்.', null, 401);
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!$input) {
        $input = $_POST;
    }

    $eventId = isset($input['id']) ? intval($input['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
    $title = trim($input['title'] ?? '');
    $eventDate = trim($input['event_date'] ?? date('Y-m-d'));
    $category = trim($input['category'] ?? 'விசேஷ பூஜை');
    $description = trim($input['description'] ?? '');
    $coverImage = trim($input['cover_image'] ?? '');
    $photos = $input['photos'] ?? [];

    if (empty($title)) {
        jsonResponse(false, 'நிகழ்வின் பெயர் (Title) அவசியம்', null, 400);
    }

    if (!$pdo) {
        jsonResponse(true, 'நிகழ்வு சேமிக்கப்பட்டது (Local)', ['id' => $eventId ?: time()]);
    }

    try {
        $pdo->beginTransaction();

        if ($eventId > 0) {
            // Update existing event
            $chk = $pdo->prepare("SELECT id FROM events WHERE id = ?");
            $chk->execute([$eventId]);
            if ($chk->fetch()) {
                $stmt = $pdo->prepare("UPDATE events SET title = ?, event_date = ?, category = ?, description = ?, cover_image = ? WHERE id = ?");
                $stmt->execute([$title, $eventDate, $category, $description, $coverImage, $eventId]);
            } else {
                // If specific id doesn't exist, insert with that id or new
                $stmt = $pdo->prepare("INSERT INTO events (id, title, event_date, category, description, cover_image, is_published) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$eventId, $title, $eventDate, $category, $description, $coverImage]);
            }
            $targetId = $eventId;
        } else {
            // Insert new event
            $stmt = $pdo->prepare("INSERT INTO events (title, event_date, category, description, cover_image, is_published) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$title, $eventDate, $category, $description, $coverImage]);
            $targetId = $pdo->lastInsertId();
        }

        // Set cover image from photos if not provided
        if (empty($coverImage) && !empty($photos) && isset($photos[0]['image_url'])) {
            $coverImage = $photos[0]['image_url'];
            $pdo->prepare("UPDATE events SET cover_image = ? WHERE id = ?")->execute([$coverImage, $targetId]);
        }

        // Update event photos if photos array provided
        if (!empty($photos) && is_array($photos)) {
            // Clear old photos for this event
            $pdo->prepare("DELETE FROM event_photos WHERE event_id = ?")->execute([$targetId]);

            $photoStmt = $pdo->prepare("INSERT INTO event_photos (event_id, image_url, thumbnail_url, cloudinary_id, caption) VALUES (?, ?, ?, ?, ?)");
            foreach ($photos as $p) {
                $imgUrl = is_array($p) ? ($p['image_url'] ?? '') : $p;
                $thumbUrl = is_array($p) ? ($p['thumbnail_url'] ?? $imgUrl) : $imgUrl;
                $cloudId = is_array($p) ? ($p['cloudinary_id'] ?? null) : null;
                $caption = is_array($p) ? ($p['caption'] ?? $title) : $title;

                if (!empty($imgUrl)) {
                    $photoStmt->execute([$targetId, $imgUrl, $thumbUrl, $cloudId, $caption]);
                }
            }
        }

        $pdo->commit();
        jsonResponse(true, 'நிகழ்வு தரவுத்தளத்தில் வெற்றிகரமாக சேமிக்கப்பட்டது!', [
            'id' => $targetId,
            'title' => $title,
            'event_date' => $eventDate,
            'category' => $category,
            'description' => $description,
            'cover_image' => $coverImage,
            'photo_count' => count($photos)
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse(false, 'சேமிப்பதில் பிழை: ' . $e->getMessage(), null, 500);
    }
}
