<?php
/**
 * முக்கிய அறிவிப்புகள் API (Announcements API)
 * Get, Create, Update, Delete Announcements
 */

require_once __DIR__ . '/db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdmin() {
    if (!empty($_SESSION['admin_logged_in'])) return true;
    $authKey = $_SERVER['HTTP_X_ADMIN_KEY'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($authKey === ADMIN_SECRET_KEY) return true;
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if (in_array($remoteIp, ['127.0.0.1', '::1', 'localhost'])) return true;
    return false;
}

// 1. GET (அறிவிப்புகள் பெறுதல்)
if ($method === 'GET' && empty($action)) {
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM announcements ORDER BY id DESC");
            $announcements = $stmt->fetchAll();
            jsonResponse(true, 'அறிவிப்புகள் பட்டியல்', $announcements);
        } catch (Exception $e) {
            jsonResponse(false, $e->getMessage(), null, 500);
        }
    } else {
        $sampleAnnouncements = [
            ['id' => 1, 'title' => 'தினசரி மகா அன்னதான திட்டம்', 'content' => 'தினமும் மதியம் 12:15 மணிக்கு 500 பக்தர்களுக்கு அறுசுவை அன்னதானம் வழங்கப்படுகிறது.'],
            ['id' => 2, 'title' => 'வரவிருக்கும் பிரதோஷ சிறப்பு வழிபாடு', 'content' => 'வரும் பிரதோஷ நன்னாளில் மாலை 4:30 மணிக்கு நந்திகேஸ்வரருக்கும் மூலவருக்கும் சிறப்பு அபிஷேகம்.'],
            ['id' => 3, 'title' => 'திருக்கோவில் திருப்பணி நன்கொடை', 'content' => 'கோவில் ராஜகோபுர தங்க முலாம் பூசுதல் மற்றும் புதிய நந்தவனம் அமைக்கும் திருப்பணி நடைபெறுகிறது.']
        ];
        jsonResponse(true, 'அறிவிப்புகள் பட்டியல் (Mock Data)', $sampleAnnouncements);
    }
}

// 2. DELETE
if ($method === 'DELETE' || $action === 'delete') {
    if (!checkAdmin()) {
        jsonResponse(false, 'அனுமதிக்கப்படவில்லை', null, 401);
    }
    $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
    if ($id <= 0) {
        $raw = json_decode(file_get_contents('php://input'), true);
        $id = intval($raw['id'] ?? 0);
    }

    if ($id <= 0) {
        jsonResponse(false, 'செல்லுபடியற்ற எண்', null, 400);
    }

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(true, 'அறிவிப்பு வெற்றிகரமாக நீக்கப்பட்டது', ['id' => $id]);
        } catch (Exception $e) {
            jsonResponse(false, 'பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'அறிவிப்பு நீக்கப்பட்டது');
    }
}

// 3. POST / PUT (புதியது சேர்த்தல் அல்லது திருத்துதல்)
if ($method === 'POST' || $method === 'PUT') {
    if (!checkAdmin()) {
        jsonResponse(false, 'அனுமதிக்கப்படவில்லை', null, 401);
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!$input) {
        $input = $_POST;
    }

    $id = isset($input['id']) ? intval($input['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
    $title = trim($input['title'] ?? '');
    $content = trim($input['content'] ?? '');

    if (empty($title) || empty($content)) {
        jsonResponse(false, 'தலைப்பு மற்றும் விபரம் இரண்டும் அவசியம்', null, 400);
    }

    if ($pdo) {
        try {
            if ($id > 0) {
                // Check if exists
                $chk = $pdo->prepare("SELECT id FROM announcements WHERE id = ?");
                $chk->execute([$id]);
                if ($chk->fetch()) {
                    $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
                    $stmt->execute([$title, $content, $id]);
                    $targetId = $id;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO announcements (id, title, content, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$id, $title, $content]);
                    $targetId = $id;
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content, is_active) VALUES (?, ?, 1)");
                $stmt->execute([$title, $content]);
                $targetId = $pdo->lastInsertId();
            }
            jsonResponse(true, 'அறிவிப்பு வெற்றிகரமாக சேமிக்கப்பட்டது!', [
                'id' => $targetId,
                'title' => $title,
                'content' => $content
            ]);
        } catch (Exception $e) {
            jsonResponse(false, 'சேமிப்பதில் பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'அறிவிப்பு சேமிக்கப்பட்டது (Local)', ['id' => $id ?: time(), 'title' => $title, 'content' => $content]);
    }
}
