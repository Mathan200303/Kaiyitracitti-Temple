<?php
/**
 * உற்சவ விபரங்கள் API (Festival Events API)
 * Get, Create, Update, Delete Festival Events
 */

require_once __DIR__ . '/db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
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

// 1. GET
if ($method === 'GET' && empty($action)) {
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT id, tamil_date AS tamilDate, english_date AS englishDate, day, name, time_text AS timeText, sponsor, sort_key AS sortKey FROM festival_events ORDER BY sort_key ASC, id ASC");
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Output format supporting both items and data
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => true,
                'message' => 'உற்சவ விபரங்கள்',
                'data' => $events,
                'items' => $events
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            jsonResponse(false, $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(false, 'Database connection failed', null, 500);
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
            $stmt = $pdo->prepare("DELETE FROM festival_events WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(true, 'உற்சவ விபரம் வெற்றிகரமாக நீக்கப்பட்டது', ['id' => $id]);
        } catch (Exception $e) {
            jsonResponse(false, 'பிழை: ' . $e->getMessage(), null, 500);
        }
    }
}

// 3. POST / PUT
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
    $tamil_date = trim($input['tamilDate'] ?? ($input['tamil_date'] ?? ''));
    $english_date = trim($input['englishDate'] ?? ($input['english_date'] ?? ''));
    $day = trim($input['day'] ?? '');
    $name = trim($input['name'] ?? '');
    $time_text = trim($input['timeText'] ?? ($input['time_text'] ?? ''));
    $sponsor = trim($input['sponsor'] ?? '');
    $sort_key = intval($input['sortKey'] ?? ($input['sort_key'] ?? 0));

    if (empty($name) || empty($tamil_date)) {
        jsonResponse(false, 'நிகழ்வு பெயர் மற்றும் தமிழ் திகதி அவசியம்', null, 400);
    }

    if ($pdo) {
        try {
            if ($id > 0) {
                $chk = $pdo->prepare("SELECT id FROM festival_events WHERE id = ?");
                $chk->execute([$id]);
                if ($chk->fetch()) {
                    $stmt = $pdo->prepare("UPDATE festival_events SET tamil_date = ?, english_date = ?, day = ?, name = ?, time_text = ?, sponsor = ?, sort_key = ? WHERE id = ?");
                    $stmt->execute([$tamil_date, $english_date, $day, $name, $time_text, $sponsor, $sort_key, $id]);
                    $targetId = $id;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO festival_events (id, tamil_date, english_date, day, name, time_text, sponsor, sort_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $tamil_date, $english_date, $day, $name, $time_text, $sponsor, $sort_key]);
                    $targetId = $id;
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO festival_events (tamil_date, english_date, day, name, time_text, sponsor, sort_key) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$tamil_date, $english_date, $day, $name, $time_text, $sponsor, $sort_key]);
                $targetId = $pdo->lastInsertId();
            }
            jsonResponse(true, 'உற்சவ விபரம் வெற்றிகரமாக சேமிக்கப்பட்டது!', [
                'id' => $targetId,
                'name' => $name,
                'tamilDate' => $tamil_date
            ]);
        } catch (Exception $e) {
            jsonResponse(false, 'சேமிப்பதில் பிழை: ' . $e->getMessage(), null, 500);
        }
    }
}
