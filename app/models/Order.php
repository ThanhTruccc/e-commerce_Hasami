<?php
require_once CORE_PATH . '/Model.php';
require_once APP_PATH . '/models/OrderDetail.php';

// ============================================================
//  MODEL: Order.php
// ============================================================

class Order extends Model {

    protected string $table = 'orders';

    public function createOrder(int $userId, array $cartItems, array $shipping, ?int $couponId = null, float $discount = 0, string $paymentMethod = 'cod'): int {
        $totalAmount   = array_reduce($cartItems, fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);
        $shippingFee   = $totalAmount >= 300000 ? 0 : 30000;
        $finalAmount   = ($totalAmount - $discount) + $shippingFee;

        $orderId = $this->insert([
            'user_id'          => $userId,
            'coupon_id'        => $couponId,
            'total_amount'     => $totalAmount,
            'discount_amount'  => $discount,
            'final_amount'     => max(0, $finalAmount),
            'payment_method'   => $paymentMethod,
            'payment_status'   => $paymentMethod === 'cod' ? 'unpaid' : 'pending',
            'status'           => 'pending',
            'shipping_name'    => $shipping['name'],
            'shipping_phone'   => $shipping['phone'],
            'shipping_address' => $shipping['address'],
            'note'             => $shipping['note'] ?? '',
        ]);

        // Insert order details
        $detailModel = new OrderDetail();
        foreach ($cartItems as $item) {
            $detailModel->insert([
                'order_id'   => $orderId,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $item['subtotal'],
            ]);
        }

        return $orderId;
    }

    public function getByUser(int $userId, int $page = 1): array {
        $offset = ($page - 1) * ORDERS_PER_PAGE;
        $data   = $this->query("
            SELECT o.*, COUNT(od.id) AS item_count
            FROM orders o
            LEFT JOIN order_details od ON o.id = od.order_id
            WHERE o.user_id = :uid
            GROUP BY o.id ORDER BY o.ordered_at DESC
            LIMIT " . ORDERS_PER_PAGE . " OFFSET {$offset}
        ", [':uid' => $userId])->fetchAll();
        $total = $this->count(['user_id' => $userId]);
        $pages = ceil($total / ORDERS_PER_PAGE);
        return compact('data', 'total', 'pages', 'page');
    }

    public function getDetail(int $orderId): array|false {
        $order = $this->findById($orderId);
        if (!$order) return false;
        $order['items'] = (new OrderDetail())->getByOrder($orderId);
        return $order;
    }

    public function getRevenueStats(): array {
        return $this->query("
            SELECT
                COUNT(*) AS total_orders,
                SUM(CASE WHEN status='delivered' THEN final_amount ELSE 0 END) AS total_revenue,
                SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN DATE(ordered_at) = CURDATE() THEN 1 ELSE 0 END) AS today_orders
            FROM orders
        ")->fetch();
    }

    public function getMonthlyRevenue(int $months = 6): array {
        return $this->query("
            SELECT DATE_FORMAT(ordered_at, '%Y-%m') AS month,
                   SUM(final_amount) AS revenue,
                   COUNT(*) AS order_count
            FROM orders WHERE status = 'delivered'
              AND ordered_at >= DATE_SUB(NOW(), INTERVAL :m MONTH)
            GROUP BY month ORDER BY month ASC
        ", [':m' => $months])->fetchAll();
    }

    public function getAll(int $page = 1, string $status = ''): array {
        $where  = $status ? "WHERE o.status = :status" : "";
        $binds  = $status ? [':status' => $status] : [];
        $offset = ($page - 1) * ORDERS_PER_PAGE;

        $data = $this->query("
            SELECT o.*, u.name AS user_name, u.email,
                   COUNT(od.id) AS item_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_details od ON o.id = od.order_id
            {$where} GROUP BY o.id
            ORDER BY o.ordered_at DESC
            LIMIT " . ORDERS_PER_PAGE . " OFFSET {$offset}
        ", $binds)->fetchAll();

        return ['data' => $data];
    }

    public function updateStatus(int $id, string $status): bool {
        return $this->update($id, ['status' => $status]);
    }
}

