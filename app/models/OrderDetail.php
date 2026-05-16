<?php
require_once CORE_PATH . '/Model.php';

class OrderDetail extends Model {
    protected string $table = 'order_details';

    /**
     * Lấy danh sách sản phẩm theo mã đơn hàng
     */
    public function getByOrder(int $orderId): array {
        return $this->query("
            SELECT od.*, p.name AS product_name, p.image, p.slug
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = :oid
        ", [':oid' => $orderId])->fetchAll();
    }

    /**
     * Lấy top sản phẩm bán chạy nhất cho Dashboard Admin
     */
    public function getBestSellers(int $limit = 5): array {
        return $this->query("
            SELECT p.id, p.name, p.image, p.brand,
                   SUM(od.quantity) AS total_sold,
                   SUM(od.subtotal) AS total_revenue
            FROM order_details od
            JOIN orders o  ON od.order_id = o.id
            JOIN products p ON od.product_id = p.id
            WHERE o.status = 'delivered'
            GROUP BY od.product_id
            ORDER BY total_sold DESC LIMIT :lim
        ", [':lim' => $limit])->fetchAll();
    }
}
