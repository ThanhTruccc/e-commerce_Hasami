<!-- app/views/order/checkout.php -->
<?php $pageTitle = 'Thanh Toán | GlowViet'; ?>

<section class="checkout-section py-5">
    <div class="container">
        <h2 class="page-title mb-4"><i class="bi bi-bag-check me-2"></i>Thanh Toán</h2>

        <form action="<?= APP_URL ?>/order/placeOrder" method="POST" id="checkoutForm">
            <div class="row g-4">
                <!-- Shipping Info -->
                <div class="col-lg-7">
                    <div class="checkout-card">
                        <h5 class="checkout-section-title"><i class="bi bi-geo-alt me-2"></i>Thông Tin Giao Hàng</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_name" class="form-control" required
                                       value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Nguyễn Thị Lan">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" name="shipping_phone" class="form-control" required
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="0901234567">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                <textarea name="shipping_address" class="form-control" rows="2" required
                                          placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú cho người giao hàng (tuỳ chọn)..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card mt-4">
                        <h5 class="checkout-section-title"><i class="bi bi-cash-coin me-2"></i>Phương Thức Thanh Toán</h5>
                        
                        <!-- COD -->
                        <div class="payment-option mb-3 active">
                            <input type="radio" name="payment_method" value="cod" id="payCOD" checked>
                            <label for="payCOD" class="payment-label">
                                <i class="bi bi-truck fs-4 text-primary me-2"></i>
                                <div>
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <p class="mb-0 text-muted small">Kiểm tra hàng trước khi thanh toán</p>
                                </div>
                                <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                            </label>
                        </div>

                        <!-- VNPay -->
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="online" id="payOnline">
                            <label for="payOnline" class="payment-label">
                                <i class="bi bi-wallet2 fs-4 text-primary me-2"></i>
                                <div>
                                    <strong>Thanh toán Online (VNPay)</strong>
                                    <p class="mb-0 text-muted small">Thanh toán an toàn qua cổng VNPay</p>
                                </div>
                                <i class="bi bi-check-circle-fill text-success ms-auto d-none"></i>
                            </label>
                        </div>
                    </div>

                    <!-- Coupon -->
                    <div class="checkout-card mt-4">
                        <h5 class="checkout-section-title"><i class="bi bi-ticket-perforated me-2"></i>Mã Giảm Giá</h5>
                        <div class="input-group">
                            <input type="text" name="coupon_code" id="couponCode" class="form-control" placeholder="Nhập mã giảm giá...">
                            <button type="button" class="btn btn-outline-primary" id="verifyCoupon">Kiểm tra</button>
                        </div>
                        <div id="couponStatus" class="mt-2 small"></div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-5">
                    <div class="checkout-card sticky-sidebar">
                        <h5 class="checkout-section-title"><i class="bi bi-receipt me-2"></i>Đơn Hàng Của Bạn</h5>

                        <div class="order-items-list">
                            <?php foreach ($items as $item): ?>
                            <div class="order-item d-flex gap-3 mb-3 pb-3 border-bottom">
                                <div class="order-item-img">
                                    <?php if ($item['image']): ?>
                                    <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($item['image']) ?>"
                                         alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-2">
                                    <?php endif; ?>
                                </div>
                                <div class="flex-fill">
                                    <div class="fw-600 small"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="text-muted small">x<?= $item['quantity'] ?></div>
                                </div>
                                <div class="fw-700 text-primary small"><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="checkout-summary mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính</span>
                                <span><?= number_format($total, 0, ',', '.') ?>đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Vận chuyển</span>
                                <span><?= $total >= 300000 ? '<span class="text-success">Miễn phí</span>' : '30.000đ' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 d-none" id="discountLine">
                                <span class="text-success">Giảm giá</span>
                                <span class="text-success fw-700" id="discountShow">-0đ</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-800 fs-5 text-primary">
                                <span>Tổng cộng</span>
                                <span id="finalTotal"><?= number_format($total >= 300000 ? $total : $total + 30000, 0, ',', '.') ?>đ</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-place-order w-100 mt-4" id="btnPlaceOrder">
                            <i class="bi bi-bag-check me-2"></i>Đặt Hàng Ngay (COD)
                        </button>
                        <p class="text-center text-muted small mt-2">
                            <i class="bi bi-shield-lock me-1"></i>Đơn hàng được bảo vệ bởi GlowViet
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>


<style>
.checkout-card          { background: white; border-radius: 16px; border: 1px solid #E5E7EB; padding: 24px; }
.checkout-section-title { font-size: 1rem; font-weight: 700; padding-bottom: 12px; border-bottom: 2px solid #FFD6EB; margin-bottom: 16px; }
.payment-option         { border: 2px solid #E5E7EB; border-radius: 12px; padding: 16px; margin-bottom: 12px; transition: all 0.3s ease; cursor: pointer; }
.payment-option.active  { border-color: #E91E8C; background: #FFF0F9; }
.payment-label          { display: flex; align-items: center; cursor: pointer; gap: 10px; margin: 0; width: 100%; }
.payment-label input    { display: none; }
.order-item-img         { width: 56px; height: 56px; border-radius: 8px; overflow: hidden; background: #f8f8f8; flex-shrink: 0; }
.order-item-img img     { width: 100%; height: 100%; object-fit: cover; }
.btn-place-order { background: linear-gradient(135deg,#E91E8C,#6366F1); color: white; border: none; border-radius: 50px; padding: 16px; font-size: 1rem; font-weight: 700; transition: all .3s; }
.btn-place-order:hover  { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(233,30,140,.4); }
</style>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Reset all
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('active');
            opt.querySelector('.bi-check-circle-fill').classList.add('d-none');
        });
        
        // Set active
        if (this.checked) {
            const parent = this.closest('.payment-option');
            parent.classList.add('active');
            parent.querySelector('.bi-check-circle-fill').classList.remove('d-none');
        }
    });
});
</script>
</body>
