<?php
/**
 * கோவில் விபரங்கள் & அமைப்புகள் API (Temple Settings & History API)
 */

require_once __DIR__ . '/db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

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
if ($method === 'GET') {
    $type = $_GET['type'] ?? 'all';

    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM temple_info WHERE id = 1 LIMIT 1");
            $info = $stmt->fetch();
            if (!$info) {
                $info = [
                    'temple_name' => 'கைதடி வடக்கு கயிற்றசிட்டி அருள்மிகு கந்தசுவாமி தேவஸ்தானம்',
                    'phone' => '+94 77 123 4567, 021-2223456',
                    'email' => 'kaithadykandanswamy@gmail.com'
                ];
            }
            jsonResponse(true, 'கோவில் அமைப்புகள்', $info);
        } catch (Exception $e) {
            jsonResponse(false, $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'கோவில் அமைப்புகள் (Mock Data)', []);
    }
}

// 2. POST / PUT
if ($method === 'POST' || $method === 'PUT') {
    if (!checkAdmin()) {
        jsonResponse(false, 'அனுமதிக்கப்படவில்லை', null, 401);
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!$input) {
        $input = $_POST;
    }

    $type = $input['type'] ?? 'settings'; // 'settings' or 'history'

    if ($pdo) {
        try {
            if ($type === 'history') {
                $historySummary = trim($input['heroTitle'] ?? '') . ' - ' . trim($input['heroSubtitle'] ?? '');
                $historyFull = json_encode($input, JSON_UNESCAPED_UNICODE);

                $stmt = $pdo->prepare("UPDATE temple_info SET history_summary = ?, history_full = ? WHERE id = 1");
                $stmt->execute([$historySummary, $historyFull]);

                jsonResponse(true, 'ஸ்தல வரலாறு தரவுத்தளத்தில் வெற்றிகரமாக சேமிக்கப்பட்டது!', $input);
            } else {
                $name = trim($input['name'] ?? '');
                $tagline = trim($input['tagline'] ?? '');
                $phone = trim($input['phone'] ?? '');
                $email = trim($input['email'] ?? '');
                $address = trim($input['address'] ?? '');
                $bank = trim($input['bank'] ?? '');
                $branch = trim($input['branch'] ?? '');
                $mapUrl = trim($input['mapUrl'] ?? '');

                $stmt = $pdo->prepare("UPDATE temple_info SET temple_name = ?, tagline = ?, phone = ?, email = ?, address = ?, bank_name = ?, bank_branch = ?, map_embed_url = ? WHERE id = 1");
                $stmt->execute([$name, $tagline, $phone, $email, $address, $bank, $branch, $mapUrl]);

                jsonResponse(true, 'கோவில் அமைப்புகள் தரவுத்தளத்தில் வெற்றிகரமாக சேமிக்கப்பட்டது!', $input);
            }
        } catch (Exception $e) {
            jsonResponse(false, 'சேமிப்பதில் பிழை: ' . $e->getMessage(), null, 500);
        }
    } else {
        jsonResponse(true, 'சேமிக்கப்பட்டது (Local)', $input);
    }
}
