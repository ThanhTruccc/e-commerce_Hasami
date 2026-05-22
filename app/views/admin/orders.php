<!-- app/views/admin/orders.php -->
<?php $pageTitle = 'Quản lý Đơn hàng | GlowViet Admin'; ?>

<div class="admin-page-header">
    <h1 class="admin-page-title"><i class="bi bi-receipt me-2"></i>Quản lý Đơn hàng</h1>
</div>

<div class="admin-card mb-4">
    <div class="admin-card-body">
        <form action="<?= APP_URL ?>/admin/orders" method="GET" class="d-flex gap-2">
            <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <?php foreach (ORDER_STATUS as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($status ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['data'] as $ord): ?>
                    <tr>
                        <td><strong>#<?= $ord['id'] ?></strong></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($ord['user_name']) ?></div>
                            <small class="text-muted"><?= $ord['email'] ?></small>
                        </td>
                        <td>
                            <div class="fw-bold"><?= number_format($ord['final_amount'], 0, ',', '.') ?>đ</div>
                            <small class="text-muted">SL: <?= $ord['item_count'] ?></small>
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-uppercase"><?= $ord['payment_method'] ?></span>
                                <br>
                                <span class="badge bg-<?= $ord['payment_status'] === 'paid' ? 'success' : 'secondary' ?> pt-0 pb-0" style="font-size: 0.7rem;">
                                    <?= $ord['payment_status'] === 'paid' ? 'Đã thu' : 'Chưa thu' ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php
                            $badgeColors = ['pending'=>'warning','confirmed'=>'info','shipping'=>'primary','delivered'=>'success','cancelled'=>'danger'];
                            $bc = $badgeColors[$ord['status'] ?? ''] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $bc ?>"><?= ORDER_STATUS[$ord['status'] ?? ''] ?? 'Không xác định' ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($ord['ordered_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="<?= APP_URL ?>/admin/orderDetail/<?= $ord['id'] ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="<?= APP_URL ?>/admin/orderStatus/<?= $ord['id'] ?>" method="POST" class="d-flex gap-1">
                                <select name="status" class="form-select form-select-sm w-auto">
                                    <?php foreach (ORDER_STATUS as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $k === $ord['status'] ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
