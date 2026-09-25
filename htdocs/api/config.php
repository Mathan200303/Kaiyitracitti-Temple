<?php
/**
 * கோவில் இணையதள கட்டமைப்பு (Temple Website Configuration)
 * InfinityFree & Cloudinary Configuration
 */

// பிழை அறிக்கையிடல் (Development Error Reporting)
error_reporting(E_ALL);
ini_set('display_errors', 0); // InfinityFree-ல் பாதுகாப்புக்காக 0 என வைக்கப்பட்டுள்ளது

// CORS தலைப்புகள் (CORS Headers for API requests)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Admin-Key");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS preflight கோரிக்கையை கையாளுதல்
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -------------------------------------------------------------
// 1. தரவுத்தள இணைப்பு விபரங்கள் (MySQL Database Credentials)
// InfinityFree Control Panel-ல் உள்ள MySQL Details-ஐ இங்கு மாற்றவும்:
// -------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');       // எ.கா: sql123.infinityfree.com அல்லது localhost
define('DB_NAME', getenv('DB_NAME') ?: 'temple_db');       // எ.கா: if0_12345678_temple
define('DB_USER', getenv('DB_USER') ?: 'root');            // எ.கா: if0_12345678
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '200303'); // உங்கள் MySQL கடவுச்சொல்
define('DB_PORT', getenv('DB_PORT') ?: '3307');

// -------------------------------------------------------------
// 2. Cloudinary புகைப்பட சேமிப்பக விபரங்கள் (Cloudinary Image Storage)
// https://cloudinary.com இலவச கணக்கின் விபரங்கள்:
// -------------------------------------------------------------
define('CLOUDINARY_CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME') ?: 'demo');
define('CLOUDINARY_API_KEY', getenv('CLOUDINARY_API_KEY') ?: '');
define('CLOUDINARY_API_SECRET', getenv('CLOUDINARY_API_SECRET') ?: '');
define('CLOUDINARY_UPLOAD_PRESET', getenv('CLOUDINARY_UPLOAD_PRESET') ?: 'temple_photos'); // Unsigned Preset

// -------------------------------------------------------------
// 3. நிர்வாகி அமர்வு பாதுகாப்பு (Admin Session Settings)
// -------------------------------------------------------------
define('SITE_NAME', 'அருள்மிகு திருக்கோவில்');
define('ADMIN_SECRET_KEY', 'temple_divine_secret_key_2026');

// JSON உதவி செயல்பாடுகள் (JSON Helper Functions)
function jsonResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
