<?php
// ============================================================
//  CONFIG - Cấu hình toàn hệ thống
// ============================================================

// Tự động nhận diện môi trường (Localhost vs Production)
$isLocal = (php_sapi_name() === 'cli') 
    || in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '[::1]']) 
    || str_starts_with($_SERVER['HTTP_HOST'] ?? '', '192.168.') 
    || str_starts_with($_SERVER['HTTP_HOST'] ?? '', '10.') 
    || str_starts_with($_SERVER['HTTP_HOST'] ?? '', '172.');

if ($isLocal) {
    // Cấu hình Local (XAMPP / local development)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'manguonmo_db');
    define('DB_USER', 'root');
    define('DB_PASS', ''); // Mật khẩu MySQL local từ README
    define('APP_ENV', 'development');
} else {
    // Cấu hình Production (Clever Cloud)
    define('DB_HOST', 'b5neczaaiiqfemvyrzsw-mysql.services.clever-cloud.com');
    define('DB_NAME', 'b5neczaaiiqfemvyrzsw');
    define('DB_USER', 'utwz24rsr9daa4v2');
    define('DB_PASS', 'XfEj0K5bhPkC76X0RHuL');
    define('APP_ENV', 'production');
}

// Tự động nhận dạng URL cho cả Local và Production
if (php_sapi_name() === 'cli') {
    define('APP_URL', 'http://localhost/manguonmo/public');
} else {
    // Nhận diện Protocol (HTTP hay HTTPS)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false);
    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Tìm đường dẫn gốc của ứng dụng
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = rtrim($scriptDir, '/');
    
    // Cố gắng tự động thêm /public nếu đang ở local mà URL bị thiếu (trên hosting thường trỏ document root thẳng vào public hoặc up đè thư mục)
    if ($isLocal && !str_contains($scriptDir, '/public') && !str_ends_with($scriptDir, '/public')) {
        $scriptDir = rtrim($scriptDir, '/') . '/public';
    }
    
    define('APP_URL', $protocol . '://' . $host . $scriptDir);
}
define('DB_CHARSET', 'utf8mb4');

// App
define('APP_NAME', 'Hasami - Mỹ Phẩm Cao Cấp');
define('GEMINI_API_KEY', 'AIzaSyB3oVYG4xOsdSC2SgcoFUeoTJqwZph8Ock'); // Thay thế bằng key của bạn
define('APP_VERSION', '1.0.0');

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
