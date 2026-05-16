<?php
require_once CORE_PATH . '/Model.php';

class Wishlist extends Model {
    protected string $table = 'wishlists';

    public function toggle(int $userId, int $productId): string {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = :uid AND product_id = :pid");
        $stmt->execute([':uid' => $userId, ':pid' => $productId]);
        if ($stmt->fetch()) {
            $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = :uid AND product_id = :pid")->execute([':uid' => $userId, ':pid' => $productId]);
            return 'removed';
        } else {
            $this->db->prepare("INSERT INTO {$this->table} (user_id, product_id) VALUES (:uid, :pid)")->execute([':uid' => $userId, ':pid' => $productId]);
            return 'added';
        }
    }

    public function getByUser(int $userId): array {
        $sql = "SELECT p.*, COALESCE(p.sale_price, p.price) AS display_price 
                FROM {$this->table} w 
                JOIN products p ON w.product_id = p.id 
                WHERE w.user_id = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isWishlisted(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE user_id = :uid AND product_id = :pid LIMIT 1");
        $stmt->execute([':uid' => $userId, ':pid' => $productId]);
        return (bool)$stmt->fetch();
    }

    public function getCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}
