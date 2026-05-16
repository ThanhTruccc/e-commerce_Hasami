<?php
require_once CORE_PATH . '/Model.php';

// ============================================================
//  MODEL: Review.php
// ============================================================

class Review extends Model {

    protected string $table = 'reviews';

    public function getByProduct(int $productId, int $page = 1): array {
        $offset = ($page - 1) * 5;
        $data   = $this->query("
            SELECT r.*, u.name AS user_name, u.avatar
            FROM reviews r JOIN users u ON r.user_id = u.id
            WHERE r.product_id = :pid
            ORDER BY r.created_at DESC LIMIT 5 OFFSET {$offset}
        ", [':pid' => $productId])->fetchAll();

        $stats = $this->query("
            SELECT AVG(rating) AS avg, COUNT(*) AS total,
                   SUM(CASE WHEN rating=5 THEN 1 ELSE 0 END) AS r5,
                   SUM(CASE WHEN rating=4 THEN 1 ELSE 0 END) AS r4,
                   SUM(CASE WHEN rating=3 THEN 1 ELSE 0 END) AS r3,
                   SUM(CASE WHEN rating=2 THEN 1 ELSE 0 END) AS r2,
                   SUM(CASE WHEN rating=1 THEN 1 ELSE 0 END) AS r1
            FROM reviews WHERE product_id = :pid
        ", [':pid' => $productId])->fetch();

        return compact('data', 'stats');
    }

    public function canReview(int $userId, int $productId): bool {
        // Kiểm tra đã mua hàng chưa
        $bought = $this->query("
            SELECT 1 FROM orders o
            JOIN order_details od ON o.id = od.order_id
            WHERE o.user_id=:uid AND od.product_id=:pid AND o.status='delivered' LIMIT 1
        ", [':uid' => $userId, ':pid' => $productId])->fetch();

        if (!$bought) return false;

        // Kiểm tra đã review chưa
        $exists = $this->query(
            "SELECT 1 FROM reviews WHERE user_id=:u AND product_id=:p LIMIT 1",
            [':u' => $userId, ':p' => $productId]
        )->fetch();

        return !$exists;
    }

    public function addReview(int $userId, int $productId, array $data): bool {
        if (!$this->canReview($userId, $productId)) return false;
        $this->insert([
            'product_id'  => $productId,
            'user_id'     => $userId,
            'rating'      => (int)$data['rating'],
            'title'       => trim($data['title'] ?? ''),
            'comment'     => trim($data['comment'] ?? ''),
            'is_verified' => 1,
        ]);
        return true;
    }
}

