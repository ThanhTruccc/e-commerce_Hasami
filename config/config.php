<?php
// ============================================================
//  CONFIG - Cấu hình toàn hệ thống
// ============================================================

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'manguonmo_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App
define('APP_NAME', 'Hasami - Mỹ Phẩm Thuần Việt');
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE'); // Thay thế bằng key của bạn
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('APP_URL', $protocol . '://' . $host . '/manguonmo/public');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // Đổi thành 'production' khi deploy

// Paths
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/images/products');
define('UPLOAD_URL', APP_URL . '/images/products');

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// AI Recommendation weights
define('AI_WEIGHT_SKIN_TYPE', 0.35);
define('AI_WEIGHT_CATEGORY', 0.25);
define('AI_WEIGHT_PRICE', 0.20);
define('AI_WEIGHT_INGREDIENT', 0.20);
define('AI_MAX_RECOMMENDATIONS', 8);

// Session
define('SESSION_LIFETIME', 7200); // 2 hours

// Skin types
define('SKIN_TYPES', [
    'oily' => 'Da dầu',
    'dry' => 'Da khô',
    'combination' => 'Da hỗn hợp',
    'sensitive' => 'Da nhạy cảm',
    'normal' => 'Da thường',
]);

// Order statuses
define('ORDER_STATUS', [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã huỷ',
]);

// Error reporting - được kiểm soát qua APP_ENV trong index.php

// VNPay Config (Sandbox) - Lấy từ https://sandbox.vnpayment.vn/devreg/
define('VNP_TMNCODE', 'RFYE71IV');               // ← Thay bằng TmnCode của bạn
define('VNP_HASHSECRET', 'CBKSXP0N44EUHUV3KEL0H02R92I736TR'); // ← Thay bằng HashSecret của bạn
define('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNP_RETURNURL', APP_URL . '/order/vnpayReturn');
