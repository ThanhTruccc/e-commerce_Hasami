<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin | Hasami' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- ── SIDEBAR ────────────────────────────────────────── -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-logo">
            <span class="brand-icon">✦</span>
            <span class="sidebar-brand-text">Hasami</span>
            <span class="sidebar-badge">Admin</span>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Tổng Quan</div>
            <a href="<?= APP_URL ?>/admin" class="sidebar-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>

            <div class="nav-section-label">Quản Lý</div>
            <a href="<?= APP_URL ?>/admin/products" class="sidebar-link <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> <span>Sản phẩm</span>
            </a>
            <a href="<?= APP_URL ?>/admin/orders" class="sidebar-link <?= ($activePage ?? '') === 'orders' ? 'active' : '' ?>">
                <i class="bi bi-bag-check"></i> <span>Đơn hàng</span>
                <?php if (isset($_orders_pending) && $_orders_pending > 0): ?>
                <span class="sidebar-badge-count"><?= $_orders_pending ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= APP_URL ?>/admin/users" class="sidebar-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                <i class="bi bi-people"></i> <span>Người dùng</span>
            </a>

            <div class="nav-section-label">Hệ Thống</div>
            <a href="<?= APP_URL ?>" class="sidebar-link" target="_blank">
                <i class="bi bi-globe"></i> <span>Xem Website</span>
            </a>
            <a href="<?= APP_URL ?>/auth/logout" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-right"></i> <span>Đăng xuất</span>
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?= mb_substr($_SESSION['user_name'] ?? 'A', 0, 1) ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
                <div class="sidebar-user-role">Administrator</div>
            </div>
        </div>
    </aside>

    <!-- ── MAIN CONTENT ──────────────────────────────────── -->
    <div class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title"><?= $pageTitle ?? 'Dashboard' ?></div>
            <div class="topbar-right d-flex align-items-center gap-3">
                <span class="text-muted small"><?= date('d/m/Y H:i') ?></span>
                <a href="<?= APP_URL ?>/auth/logout" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="admin-content">
            <?= $content ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const APP_URL = '<?= APP_URL ?>';
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('adminSidebar').classList.toggle('collapsed');
    });
</script>
</body>
</html>
