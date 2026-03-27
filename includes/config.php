<?php
// Database configuration
define('DB_HOST', 'sql211.infinityfree.com');
define('DB_USER', 'if0_41490045');
define('DB_PASS', 'wjBv7XlxTvvrxiK');
define('DB_NAME', 'if0_41490045_srmap_digitalnoticeboard');

// Website configuration
define('SITE_NAME', 'SRMAP Noticeboard');
define('SITE_URL', 'http://localhost/srmap-noticeboard'); // Adjust if needed

// File upload paths
define('PROFILE_IMG_PATH', '../uploads/profile_images/');
define('ATTACHMENT_PATH', '../uploads/attachments/');

// Ensure uploads directories exist
$dirs = array('../uploads', '../uploads/profile_images', '../uploads/attachments');
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Session start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
ini_set('display_errors', 0);
error_reporting(0);

// Time zone setting
date_default_timezone_set('Asia/Kolkata');

// Database connection - include this in all files that need database access
require_once __DIR__ . '/db_connect.php';

// Common Functions

// Sanitize input data to prevent XSS
function sanitize_input($data) {
    // Fix: Check if $data is null before applying trim
    if ($data === null) {
        return '';
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Force login check and redirect if not logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        // Not logged in, redirect to login page
        header("Location: ../login.php");
        exit;
    }
}

// Check if user is admin, redirect if not
function check_admin() {
    // First make sure user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
    
    // Then check if user is admin
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: ../student/dashboard.php");
        exit;
    }
}

// Redirect to a URL
function redirect($url) {
    header("Location: $url");
    exit;
}

// Flash messages
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Get user data by ID (defaults to current logged in user)
function get_user_data($user_id = null) {
    global $conn;

    if (!$user_id && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }

    if (!$user_id) {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

// Get all categories from notices
function get_categories() {
    global $conn;

    $stmt = $conn->query("SELECT DISTINCT category FROM notices ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Format date for display
function format_date($date_string) {
    $date = new DateTime($date_string);
    return $date->format('F j, Y, g:i a');
}

// Format date and time for display (includes seconds)
function format_datetime($date_string) {
    $date = new DateTime($date_string);
    return $date->format('F j, Y, g:i:s a');
}
?>