<?php
/**
 * நிர்வாகி அமர்வு பாதுகாப்பு (Admin Session Authentication Check)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
