<!-- app/views/order/history.php -->
<?php $pageTitle = 'Lịch Sử Đơn Hàng | GlowViet'; ?>

<section class="order-history-section py-5">
    <div class="container">
        <h2 class="page-title mb-4"><i class="bi bi-clock-history me-2"></i>Lịch Sử Đơn Hàng</h2>

        <?php if (!empty($data)): ?>
        <div class="orders-list">
            <?php foreach ($data as $order):
                $badgeColors = ['pending'=>'warning','confirmed'=>'info','shipping'=>'primary','delivered'=>'success','cancelled'=>'danger'];
                $bc = $badgeColors[$order['status']] ?? 'secondary';
            ?>
            <div class="order-history-card mb-4">
                <div class="order-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="order-id"><strong>#<?= $order['id'] ?></strong></div>
                        <span class="badge bg-<?= $bc ?> px-3 py-2"><?= ORDER_STATUS[$order['status']] ?></span>
                    </div>
                    <div class="order-date text-muted small">
                        <i class="bi bi-calendar me-1"></i><?= date('d/m/Y H:i', strtotime($order['ordered_at'])) ?>
                    </div>
                </div>
                <div class="order-body d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <p class="mb-1"><i class="bi bi-bag me-1 text-muted"></i><?= $order['item_count'] ?> sản phẩm</p>
                        <p class="mb-0"><i class="bi bi-geo-alt me-1 text-muted"></i><?= htmlspecialchars($order['shipping_address']) ?></p>
                    </div>
                    <div class="text-end">
                        <div class="order-total"><?= number_format($order['final_amount'], 0, ',', '.') ?>đ</div>
                        <div class="text-muted small">COD</div>
                    </div>
                </div>
                <div class="order-footer d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <span class="text-success small"><i class="bi bi-tag me-1"></i>Đã giảm <?= number_format($order['discount_amount'], 0, ',', '.') ?>đ</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= APP_URL ?>/order/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                        Xem chi tiết <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <nav class="d-flex justify-content-center">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state text-center py-5">
            <div class="empty-icon mb-3" style="font-size:4rem; opacity:.3"><i class="bi bi-bag-x"></i></div>
            <h5>Chưa có đơn hàng nào</h5>
            <p class="text-muted">Khám phá sản phẩm và đặt hàng ngay hôm nay!</p>
            <a href="<?= APP_URL ?>/product" class="btn btn-primary">Mua sắm ngay</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.order-history-card { background: white; border-radius: 16px; border: 1px solid #E5E7EB; padding: 20px; transition: all .3s; }
.order-history-card:hover { box-shadow: 0 8px 32px rgba(233,30,140,.1); border-color: #FFD6EB; }
.order-id   { font-size: 1rem; }
.order-total { font-size: 1.2rem; font-weight: 800; color: #E91E8C; }
.page-title  { font-size: 1.8rem; font-weight: 800; }
</style>
