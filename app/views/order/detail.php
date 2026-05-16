<!-- app/views/order/detail.php -->
<?php $pageTitle = 'Chi Tiết Đơn Hàng | GlowViet'; ?>

<section class="order-detail-section py-5 bg-light">
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= APP_URL ?>/order/history" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <h2 class="mb-0 fw-800">Chi Tiết Đơn Hàng #<?= $order['id'] ?></h2>
        </div>

        <div class="row g-4">
            <!-- Order Status & Items -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <span class="text-muted small d-block">Ngày đặt hàng</span>
                                <span class="fw-600"><?= date('d/m/Y H:i', strtotime($order['ordered_at'])) ?></span>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block">Trạng thái</span>
                                <?php
                                $statusMap = [
                                    'pending'   => ['bg-warning', 'Chờ xử lý'],
                                    'confirmed' => ['bg-info', 'Đã xác nhận'],
                                    'shipping'  => ['bg-primary', 'Đang giao hàng'],
                                    'delivered' => ['bg-success', 'Đã giao hàng'],
                                    'cancelled' => ['bg-danger', 'Đã hủy']
                                ];
                                $st = $statusMap[$order['status']] ?? ['bg-secondary', $order['status']];
                                ?>
                                <span class="badge <?= $st[0] ?> rounded-pill px-3"><?= $st[1] ?></span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="product-img-mini">
                                                    <?php if ($item['image']): ?>
                                                    <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($item['image']) ?>" alt="" class="rounded">
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-600"><?= htmlspecialchars($item['product_name']) ?></div>
                                                    <div class="text-muted small">ID: #<?= $item['product_id'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">x<?= $item['quantity'] ?></td>
                                        <td class="text-end"><?= number_format($item['unit_price'], 0, ',', '.') ?>đ</td>
                                        <td class="text-end fw-600"><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end mt-4">
                            <div class="col-md-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính:</span>
                                    <span><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Giảm giá:</span>
                                    <span class="text-success">-<?= number_format($order['discount_amount'], 0, ',', '.') ?>đ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span><?= $order['total_amount'] >= 300000 ? 'Miễn phí' : '30.000đ' ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-700">Tổng thanh toán:</span>
                                    <span class="fs-4 fw-800 text-primary"><?= number_format($order['final_amount'], 0, ',', '.') ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Payment Info -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3"><i class="bi bi-geo-alt me-2"></i>Thông Tin Giao Hàng</h5>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Người nhận</span>
                            <span class="fw-600"><?= htmlspecialchars($order['shipping_name']) ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Số điện thoại</span>
                            <span class="fw-600"><?= htmlspecialchars($order['shipping_phone']) ?></span>
                        </div>
                        <div class="mb-0">
                            <span class="text-muted small d-block">Địa chỉ</span>
                            <span class="fw-500"><?= htmlspecialchars($order['shipping_address']) ?></span>
                        </div>
                        <?php if ($order['note']): ?>
                        <div class="mt-3 p-2 bg-light rounded small">
                            <strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3"><i class="bi bi-credit-card me-2"></i>Thanh Toán</h5>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Phương thức</span>
                            <span class="fw-600">
                                <?= $order['payment_method'] === 'online' ? 'Thanh toán Online (VNPay)' : 'Thanh toán khi nhận hàng (COD)' ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Trạng thái thanh toán</span>
                            <?php
                            $pStatusMap = [
                                'unpaid' => ['bg-secondary', 'Chưa thanh toán'],
                                'pending' => ['bg-warning', 'Đang chờ xử lý'],
                                'paid'    => ['bg-success', 'Đã thanh toán'],
                                'failed'  => ['bg-danger', 'Thất bại']
                            ];
                            $ps = $pStatusMap[$order['payment_status']] ?? ['bg-secondary', $order['payment_status']];
                            ?>
                            <span class="badge <?= $ps[0] ?>"><?= $ps[1] ?></span>
                        </div>
                        
                        <?php if ($order['payment_method'] === 'online' && $order['payment_status'] !== 'paid'): ?>
                        <div class="mt-4">
                            <a href="<?= APP_URL ?>/order/repay/<?= $order['id'] ?>" class="btn btn-primary w-100 rounded-pill">
                                Thử thanh toán lại
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.product-img-mini { width: 48px; height: 48px; flex-shrink: 0; }
.product-img-mini img { width: 100%; height: 100%; object-fit: cover; }
.fw-800 { font-weight: 800; }
.fw-700 { font-weight: 700; }
.fw-600 { font-weight: 600; }
</style>
