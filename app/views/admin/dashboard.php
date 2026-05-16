<!-- app/views/admin/dashboard.php -->
<?php $pageTitle = 'Dashboard | GlowViet Admin'; ?>

<div class="admin-page-header">
    <h1 class="admin-page-title"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
</div>

<!-- ── STATS CARDS ─────────────────────────────────────────── -->
<div class="row g-4 mb-4">
    <?php
    $stats = [
        ['icon'=>'bi-currency-dollar', 'color'=>'#10B981', 'label'=>'Doanh Thu', 'value'=> number_format($revenueStats['total_revenue'] ?? 0, 0, ',', '.') . 'đ', 'sub'=>'Đơn đã giao'],
        ['icon'=>'bi-bag-check',       'color'=>'#6366F1', 'label'=>'Tổng Đơn',  'value'=> $revenueStats['total_orders'],               'sub'=>'Tất cả trạng thái'],
        ['icon'=>'bi-clock-history',   'color'=>'#F59E0B', 'label'=>'Chờ XN',    'value'=> $revenueStats['pending_count'],               'sub'=>'Cần xử lý ngay'],
        ['icon'=>'bi-calendar-check',  'color'=>'#3B82F6', 'label'=>'Hôm Nay',   'value'=> $revenueStats['today_orders'],                'sub'=>'Đơn hàng hôm nay'],
        ['icon'=>'bi-box-seam',        'color'=>'#EF4444', 'label'=>'Sản phẩm',  'value'=> $productStats['total_products'],              'sub'=> $productStats['out_of_stock'] . ' hết hàng'],
    ];
    foreach ($stats as $s):
    ?>
    <div class="col-6 col-md-4 col-xl">
        <div class="admin-stat-card">
            <div class="stat-icon-wrap" style="background: <?= $s['color'] ?>20; color: <?= $s['color'] ?>">
                <i class="bi <?= $s['icon'] ?>"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value"><?= $s['value'] ?></div>
                <div class="stat-label"><?= $s['label'] ?></div>
                <div class="stat-sub"><?= $s['sub'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── CHARTS + BEST SELLERS ─────────────────────────────── -->
<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-bar-chart me-2"></i>Doanh Thu 6 Tháng</h5>
            </div>
            <div class="admin-card-body">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Best Sellers -->
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-trophy me-2"></i>Top Sản Phẩm Bán Chạy</h5>
            </div>
            <div class="admin-card-body p-0">
                <div class="best-sellers-list">
                    <?php foreach ($bestSellers as $idx => $p): ?>
                    <div class="best-seller-item">
                        <div class="bs-rank rank-<?= $idx+1 ?>"><?= $idx+1 ?></div>
                        <div class="bs-info flex-fill">
                            <div class="bs-name"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="bs-brand"><?= htmlspecialchars($p['brand']) ?></div>
                        </div>
                        <div class="bs-stats text-end">
                            <div class="bs-sold"><?= number_format($p['total_sold'] ?? 0) ?> sold</div>
                            <div class="bs-revenue text-success"><?= number_format($p['total_revenue'] ?? 0, 0, ',', '.') ?>đ</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── RECENT ORDERS ─────────────────────────────────────── -->
<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between">
        <h5><i class="bi bi-receipt me-2"></i>Đơn Hàng Gần Đây</h5>
        <a href="<?= APP_URL ?>/admin/orders" class="btn btn-sm btn-primary">Xem tất cả</a>
    </div>
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr><th>#</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders['data'] as $ord): ?>
                    <tr>
                        <td><strong>#<?= $ord['id'] ?></strong></td>
                        <td><?= htmlspecialchars($ord['user_name']) ?><br><small class="text-muted"><?= $ord['email'] ?></small></td>
                        <td><?= number_format($ord['final_amount'], 0, ',', '.') ?>đ</td>
                        <td>
                            <?php
                            $badgeColors = ['pending'=>'warning','confirmed'=>'info','shipping'=>'primary','delivered'=>'success','cancelled'=>'danger'];
                            $bc = $badgeColors[$ord['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $bc ?>"><?= ORDER_STATUS[$ord['status']] ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($ord['ordered_at'])) ?></td>
                        <td>
                            <form action="<?= APP_URL ?>/admin/orderStatus/<?= $ord['id'] ?>" method="POST" class="d-inline">
                                <select name="status" class="form-select form-select-sm w-auto d-inline"
                                        onchange="this.form.submit()">
                                    <?php foreach (ORDER_STATUS as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $k === $ord['status'] ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
const monthlyData = <?= json_encode($monthlyRevenue, JSON_UNESCAPED_UNICODE) ?>;
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Doanh thu (đ)',
            data: monthlyData.map(d => d.revenue),
            backgroundColor: 'rgba(99,102,241,0.7)',
            borderColor: '#6366F1',
            borderWidth: 2,
            borderRadius: 8,
        }, {
            label: 'Đơn hàng',
            data: monthlyData.map(d => d.order_count * 100000),
            backgroundColor: 'rgba(16,185,129,0.3)',
            borderColor: '#10B981',
            borderWidth: 2,
            type: 'line',
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
