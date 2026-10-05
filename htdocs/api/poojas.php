<?php
/**
 * பூஜை நேரங்கள் API (Pooja Schedules API)
 * Get, Create, Update, Delete Pooja Timings
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
            $stmt = $pdo->query("SELECT * FROM pooja_schedules ORDER BY sort_order ASC, id ASC");
            $poojas = $stmt->fetchAll();
            jsonResponse(true, 'பூஜை நேர அட்டவணை', $poojas);
        } catch (Exception $e) {
            jsonResponse(false, $e->getMessage(), null, 500);
        }
    } else {
        $samplePoojas = [
            ['id' => 1, 'name' => 'காலை பூஜை', 'time_slot' => 'காலை 6.00 மணி', 'deity' => 'மூலவர் & பரிவார மூர்த்திகள்', 'description' => 'வெள்ளிக்கிழமை காலை விசேட அபிஷேகம் மற்றும் மகா தீபாராதனை'],
            ['id' => 2, 'name' => 'உச்சிக்கால பூஜை', 'time_slot' => 'நண்பகல் 12.00 மணி', 'deity' => 'மூலவர் கந்தசுவாமி', 'description' => 'நண்பகல் உச்சிக்கால சிறப்பு நைவேத்திய தீபாராதனை மற்றும் பிரசாத விநியோகம்'],
            ['id' => 3, 'name' => 'சாயரட்சை பூஜை', 'time_slot' => 'மாலை 6.00 மணி', 'deity' => 'சுவாமி & அம்மன்', 'description' => 'மாலை நேர விசேட அலங்கார தீபாராதனை மற்றும் திருப்புகழ் பாராயணம்']
        ];
        jsonResponse(true, 'பூஜை நேர அட்டவணை (Mock Data)', $samplePoojas);
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
            $stmt = $pdo->prepare("DELETE FROM pooja_schedules WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(true, 'பூஜை அட்டவணை வெற்றிகரமாக நீக்கப்பட்டது', ['id' => $id]);
        } catch (Exception $e) {
            jsonResponse(false, 'பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'பூஜை அட்டவணை நீக்கப்பட்டது');
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
    $name = trim($input['name'] ?? '');
    $time_slot = trim($input['time_slot'] ?? '');
    $deity = trim($input['deity'] ?? '');
    $desc = trim($input['description'] ?? ($input['desc'] ?? ''));

    if (empty($name) || empty($time_slot)) {
        jsonResponse(false, 'பூஜை பெயர் மற்றும் நேரம் அவசியம்', null, 400);
    }

    if ($pdo) {
        try {
            if ($id > 0) {
                $chk = $pdo->prepare("SELECT id FROM pooja_schedules WHERE id = ?");
                $chk->execute([$id]);
                if ($chk->fetch()) {
                    $stmt = $pdo->prepare("UPDATE pooja_schedules SET name = ?, time_slot = ?, deity = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $time_slot, $deity, $desc, $id]);
                    $targetId = $id;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO pooja_schedules (id, name, time_slot, deity, description) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $name, $time_slot, $deity, $desc]);
                    $targetId = $id;
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO pooja_schedules (name, time_slot, deity, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $time_slot, $deity, $desc]);
                $targetId = $pdo->lastInsertId();
            }
            jsonResponse(true, 'பூஜை அட்டவணை வெற்றிகரமாக சேமிக்கப்பட்டது!', [
                'id' => $targetId,
                'name' => $name,
                'time_slot' => $time_slot,
                'deity' => $deity,
                'description' => $desc
            ]);
        } catch (Exception $e) {
            jsonResponse(false, 'சேமிப்பதில் பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'பூஜை அட்டவணை சேமிக்கப்பட்டது (Local)', ['id' => $id ?: time(), 'name' => $name, 'time_slot' => $time_slot]);
    }
}
