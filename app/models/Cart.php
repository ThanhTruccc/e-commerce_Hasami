<?php
require_once CORE_PATH . '/Model.php';

// ============================================================
//  MODEL: Cart.php
// ============================================================

class Cart extends Model {

    protected string $table = 'carts';

    public function getByUser(int $userId): array {
        return $this->query("
            SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock,
                   COALESCE(p.sale_price, p.price) AS unit_price,
                   COALESCE(p.sale_price, p.price) * c.quantity AS subtotal
            FROM carts c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :uid ORDER BY c.created_at DESC
        ", [':uid' => $userId])->fetchAll();
    }

    public function addOrUpdate(int $userId, int $productId, int $qty = 1): void {
        $exists = $this->query(
            "SELECT id, quantity FROM carts WHERE user_id=:u AND product_id=:p",
            [':u' => $userId, ':p' => $productId]
        )->fetch();

        if ($exists) {
            $this->query(
                "UPDATE carts SET quantity = quantity + :q WHERE id = :id",
                [':q' => $qty, ':id' => $exists['id']]
            );
        } else {
            $this->insert(['user_id' => $userId, 'product_id' => $productId, 'quantity' => $qty]);
        }
    }

    public function updateQty(int $userId, int $productId, int $qty): void {
        if ($qty <= 0) {
            $this->removeItem($userId, $productId);
            return;
        }
        $this->query(
            "UPDATE carts SET quantity = :q WHERE user_id = :u AND product_id = :p",
            [':q' => $qty, ':u' => $userId, ':p' => $productId]
        );
    }

    public function removeItem(int $userId, int $productId): void {
        $this->query(
            "DELETE FROM carts WHERE user_id = :u AND product_id = :p",
            [':u' => $userId, ':p' => $productId]
        );
    }

    public function clearCart(int $userId): void {
        $this->query("DELETE FROM carts WHERE user_id = :u", [':u' => $userId]);
    }

    public function getTotal(int $userId): array {
        $items = $this->getByUser($userId);
        $total = array_sum(array_column($items, 'subtotal'));
        $count = array_sum(array_column($items, 'quantity'));
        return ['items' => $items, 'total' => $total, 'count' => $count];
    }

    public function getItemCount(int $userId): int {
        return (int)$this->query(
            "SELECT SUM(quantity) FROM carts WHERE user_id = :u",
            [':u' => $userId]
        )->fetchColumn();
    }
}
