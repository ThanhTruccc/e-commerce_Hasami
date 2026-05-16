<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "COLUMNS: " . implode(', ', $columns);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
