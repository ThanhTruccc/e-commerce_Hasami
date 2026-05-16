<?php
require_once 'config/config.php';
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(20) DEFAULT 'cod' AFTER total_amount");
    $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) DEFAULT 'pending' AFTER payment_method");
    $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(50) NULL AFTER status");
    echo "Database updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
