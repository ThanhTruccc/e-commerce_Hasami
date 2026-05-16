<?php
// ============================================================

// Bảo mật cơ bản
define('GLOWVIET', true);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Load config
require_once dirname(__DIR__) . '/config/config.php';

// Cấu hình hiển thị lỗi theo môi trường
if (defined('APP_ENV') && APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Khởi động session
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Load core
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Controller.php';

// Autoload models cần thiết trong navbar (Cart count)
require_once APP_PATH . '/models/Cart.php';

// Khởi động router
require_once CORE_PATH . '/App.php';
new App();
