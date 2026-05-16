<?php
require_once CORE_PATH . '/Model.php';

class Coupon extends Model {
    protected string $table = 'coupons';

    public function validateCode(string $code, float $total): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code = :code AND is_active = 1 AND (expires_at IS NULL OR expires_at >= CURDATE())");
        $stmt->execute([':code' => $code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn'];
        if ($total < $coupon['min_order']) return ['valid' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($coupon['min_order']) . 'đ'];
        if ($coupon['used_count'] >= $coupon['max_uses']) return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];

        $discount = ($coupon['type'] === 'percent') ? ($total * $coupon['value'] / 100) : $coupon['value'];
        return ['valid' => true, 'coupon' => $coupon, 'discount' => $discount];
    }

    public function use(int $id): void {
        $this->db->prepare("UPDATE {$this->table} SET used_count = used_count + 1 WHERE id = :id")->execute([':id' => $id]);
    }
}
