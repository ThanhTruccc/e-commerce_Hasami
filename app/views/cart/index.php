<!-- app/views/cart/index.php -->
<?php $pageTitle = 'Giỏ Hàng | GlowViet'; ?>

<section class="cart-section py-5">
    <div class="container">
        <h2 class="page-title mb-4"><i class="bi bi-bag me-2"></i>Giỏ Hàng</h2>

        <?php if (!empty($items)): ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="cart-table-wrapper">
                    <table class="table cart-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">Đơn giá</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-center">Thành tiền</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr class="cart-row" data-product-id="<?= $item['product_id'] ?>">
                                <td>
                                    <div class="cart-product d-flex align-items-center gap-3">
                                        <div class="cart-img-wrap">
                                        <?php if ($item['image']): ?>
                                            <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($item['image']) ?>"
                                                 alt="<?= htmlspecialchars($item['name']) ?>" class="cart-img">
                                        <?php else: ?>
                                            <div class="cart-img-placeholder"><i class="bi bi-image"></i></div>
                                        <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="cart-product-name"><?= htmlspecialchars($item['name']) ?></h6>
                                            <small class="text-muted">Còn: <?= $item['stock'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="cart-unit-price"><?= number_format($item['unit_price'], 0,',','.') ?>đ</span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="qty-control d-flex align-items-center justify-content-center gap-1">
                                        <button class="qty-btn-sm" onclick="updateCart(<?= $item['product_id'] ?>, -1)">−</button>
                                        <span class="qty-val" id="qty_<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
                                        <button class="qty-btn-sm" onclick="updateCart(<?= $item['product_id'] ?>, 1)">+</button>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="cart-subtotal" id="sub_<?= $item['product_id'] ?>">
                                        <?= number_format($item['subtotal'], 0,',','.') ?>đ
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <button class="btn-remove" onclick="removeFromCart(<?= $item['product_id'] ?>)" title="Xóa">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= APP_URL ?>/product" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>Tiếp tục mua sắm
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="cart-summary-card">
                    <h5 class="summary-title">Tóm Tắt Đơn Hàng</h5>

                    <!-- Coupon -->
                    <div class="coupon-box mb-3">
                        <label class="coupon-label"><i class="bi bi-ticket-perforated me-1"></i>Mã giảm giá</label>
                        <div class="input-group">
                            <input type="text" id="couponInput" class="form-control" placeholder="Nhập mã...">
                            <button class="btn btn-outline-primary" id="btnApplyCoupon">Áp dụng</button>
                        </div>
                        <div id="couponMessage" class="mt-1 small"></div>
                    </div>

                    <div class="summary-rows">
                        <div class="summary-row">
                            <span>Tạm tính (<span id="summaryCount"><?= $count ?></span> sản phẩm)</span>
                            <span id="summaryTotal"><?= number_format($total, 0,',','.') ?>đ</span>
                        </div>
                        <div class="summary-row text-success d-none" id="discountRow">
                            <span>Giảm giá</span>
                            <span id="discountAmt">-0đ</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển</span>
                            <span id="shippingFee"><?= $total >= 300000 ? '<span class="text-success">Miễn phí</span>' : '30.000đ' ?></span>
                        </div>
                        <hr>
                        <div class="summary-row summary-total">
                            <strong>Tổng cộng</strong>
                            <strong id="grandTotal"><?= number_format($total >= 300000 ? $total : $total + 30000, 0,',','.') ?>đ</strong>
                        </div>
                    </div>

                    <a href="<?= APP_URL ?>/order/checkout" class="btn btn-checkout w-100 mt-3">
                        <i class="bi bi-bag-check me-2"></i>Tiến hành đặt hàng
                    </a>
                    <p class="text-center text-muted small mt-2">
                        <i class="bi bi-shield-lock me-1"></i>Thanh toán an toàn · COD
                    </p>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="empty-cart text-center py-5">
            <div class="empty-cart-icon"><i class="bi bi-bag-x"></i></div>
            <h4>Giỏ hàng trống</h4>
            <p class="text-muted">Hãy khám phá những sản phẩm tuyệt vời của chúng tôi!</p>
            <a href="<?= APP_URL ?>/product" class="btn btn-primary btn-lg">Mua sắm ngay</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
let cartData = { total: <?= $total ?? 0 ?>, count: <?= $count ?? 0 ?> };

function updateCart(productId, delta) {
    const qtyEl = document.getElementById('qty_' + productId);
    let qty = parseInt(qtyEl.textContent) + delta;
    if (qty < 1) { removeFromCart(productId); return; }

    fetch(APP_URL + '/cart/update', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({product_id: productId, quantity: qty})
    }).then(r => r.json()).then(res => {
        if (res.success) {
            qtyEl.textContent = qty;
            document.getElementById('summaryTotal').textContent = formatPrice(res.total);
            document.getElementById('summaryCount').textContent = res.count;
            document.getElementById('grandTotal').textContent = formatPrice(res.total >= 300000 ? res.total : res.total + 30000);
            cartData = res;
        }
    });
}

function removeFromCart(productId) {
    if (!confirm('Xoá sản phẩm này?')) return;
    fetch(APP_URL + '/cart/remove', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({product_id: productId})
    }).then(r => r.json()).then(res => {
        if (res.success) {
            document.querySelector('[data-product-id="'+ productId +'"]').remove();
            document.getElementById('cartBadge').textContent = res.count;
            if (res.count === 0) location.reload();
        }
    });
}

function formatPrice(v) { return new Intl.NumberFormat('vi-VN').format(v) + 'đ'; }
</script>
