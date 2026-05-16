<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hasami - Website mỹ phẩm chính hãng với AI gợi ý sản phẩm phù hợp loại da của bạn">
    <title><?= $pageTitle ?? APP_NAME ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="<?= isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'admin-body' : '' ?>">

<!-- ── TOPBAR ────────────────────────────────────────────── -->
<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="topbar-text"><i class="bi bi-truck"></i> Miễn phí vận chuyển đơn từ 300k</span>
        <div class="d-flex gap-3">
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
            <a href="#"><i class="bi bi-tiktok"></i></a>
        </div>
    </div>
</div>

<!-- ── NAVBAR ────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand brand-logo" href="<?= APP_URL ?>">
            <span class="brand-icon">✦</span> Hasami
        </a>

        <!-- Hamburger -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <!-- Main Links -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>">Trang Chủ</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Skincare</a>
                    <ul class="dropdown-menu mega-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/product?category=4">Sữa Rửa Mặt</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/product?category=5">Toner</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/product?category=6">Serum</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/product?category=7">Kem Dưỡng</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Makeup</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/product?category=8">Son Môi</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/product?category=9">Phấn Nền</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/product?category=10">Mascara</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/product">Khuyến Mãi</a></li>
            </ul>

            <!-- Search -->
            <form class="search-form me-3" action="<?= APP_URL ?>/product" method="GET">
                <div class="input-group">
                    <input type="text" name="q" class="form-control search-input"
                           placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button class="btn btn-search" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <!-- Nav Icons -->
            <div class="nav-icons-container d-flex align-items-center" style="gap: 25px !important; margin-left: 20px;">
                <?php 
                $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
                if ($userAuth): 
                ?>
                    <!-- Wishlist -->
                    <div class="nav-item-wrapper">
                        <a href="<?= APP_URL ?>/wishlist" class="nav-icon-btn" title="Yêu thích">
                            <i class="bi bi-heart"></i>
                        </a>
                    </div>
                    
                    <!-- Cart -->
                    <div class="nav-item-wrapper" style="position: relative !important;">
                        <a href="<?= APP_URL ?>/cart" class="nav-icon-btn cart-btn-fixed" title="Giỏ hàng">
                            <i class="bi bi-bag"></i>
                            <span class="cart-badge-fixed" id="cartBadge">
                                <?= (new Cart())->getItemCount((int)$userAuth['id']) ?>
                            </span>
                        </a>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="user-btn-fixed dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span class="user-name-nav"><?= htmlspecialchars($userAuth['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                            <li class="dropdown-header">
                                <div class="user-avatar-small"><?= mb_substr($userAuth['name'], 0, 1) ?></div>
                                <div><?= htmlspecialchars($userAuth['name']) ?></div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/user/profile"><i class="bi bi-person-circle me-2"></i>Hồ sơ cá nhân</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/order/history"><i class="bi bi-bag-check me-2"></i>Đơn hàng</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/wishlist"><i class="bi bi-heart me-2"></i>Yêu thích</a></li>
                            <?php if (isset($_SESSION['admin_auth'])): ?>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/admin"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/cart" class="nav-icon-btn" title="Giỏ hàng"><i class="bi bi-bag"></i></a>
                    <a href="<?= APP_URL ?>/auth/login" class="btn btn-outline-primary btn-sm">Đăng nhập</a>
                    <a href="<?= APP_URL ?>/auth/register" class="btn btn-primary btn-sm">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Message -->
<?php
$flash = $_SESSION['flash'] ?? null;
if ($flash) {
    unset($_SESSION['flash']);
    $alertClass = match($flash['type']) {
        'success' => 'alert-success', 'error' => 'alert-danger',
        'warning' => 'alert-warning', default  => 'alert-info'
    };
?>
<div class="container mt-2">
    <div class="alert <?= $alertClass ?> alert-dismissible fade show flash-alert" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php } ?>

<!-- ── MAIN CONTENT ──────────────────────────────────────── -->
<main class="main-content">
    <?= $content ?>
</main>

<!-- ── FOOTER ────────────────────────────────────────────── -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <span class="brand-icon">✦</span> Hasami
                </div>
                <p class="footer-desc">Mỹ phẩm chính hãng, được AI gợi ý phù hợp với làn da của bạn. Vẻ đẹp tự nhiên — glow từ trong ra ngoài.</p>
                <div class="social-links">
                    <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-tiktok"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2">
                <h6 class="footer-title">Danh Mục</h6>
                <ul class="footer-links">
                    <li><a href="#">Skincare</a></li>
                    <li><a href="#">Makeup</a></li>
                    <li><a href="#">Chăm sóc cá nhân</a></li>
                    <li><a href="#">Khuyến mãi</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h6 class="footer-title">Hỗ Trợ</h6>
                <ul class="footer-links">
                    <li><a href="#">Chính sách đổi trả</a></li>
                    <li><a href="#">Hướng dẫn mua hàng</a></li>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                    <li><a href="#">Liên hệ</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="footer-title">Đăng Ký Nhận Ưu Đãi</h6>
                <p class="text-white-50 small">Nhận ngay voucher 10% cho đơn hàng đầu tiên</p>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Email của bạn...">
                        <button class="btn btn-primary" type="button">Đăng ký</button>
                    </div>
                </form>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <img src="<?= APP_URL ?>/images/badge-cod.svg" alt="COD" class="payment-badge" onerror="this.style.display='none'">
                    <span class="badge bg-success py-2 px-3">✓ COD</span>
                    <span class="badge bg-primary py-2 px-3">✓ Hoàn trả 7 ngày</span>
                    <span class="badge bg-warning text-dark py-2 px-3">✓ Chính hãng 100%</span>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="footer-copy">© 2026 Hasami. Thiết kế bởi sinh viên — Đồ án CNTT.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span class="text-white-50 small">Powered by PHP MVC + AI Recommendation Engine</span>
            </div>
        </div>
    </div>
</footer>

    <!-- AI Chat Consultant -->
    <div class="ai-chat-wrapper" id="aiChatWrapper">
        <div class="ai-chat-bubble" id="aiChatBubble" title="Tư vấn da với AI">
            <i class="bi bi-magic"></i>
            <span class="ai-chat-badge">AI</span>
        </div>
        
        <div class="ai-chat-window" id="aiChatWindow">
            <div class="ai-chat-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="ai-avatar">✦</div>
                    <div>
                        <div class="ai-name">Hasami AI</div>
                        <div class="ai-status">Chuyên gia tư vấn da liễu</div>
                    </div>
                </div>
                <button class="ai-close" id="aiClose"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <div class="ai-chat-messages" id="aiChatMessages">
                <div class="ai-msg ai-msg-bot">
                    Chào bạn! Mình là trợ lý AI của Hasami. Bạn cần tư vấn về sản phẩm hay cách chăm sóc da hôm nay?
                </div>
            </div>
            
            <div class="ai-chat-input-area">
                <div class="ai-chat-suggestions">
                    <button class="ai-suggest-btn">Tư vấn cho da dầu</button>
                    <button class="ai-suggest-btn">Gợi ý kem chống nắng</button>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" id="aiInput" placeholder="Nhập câu hỏi của bạn...">
                    <button class="btn btn-primary" id="aiSendBtn"><i class="bi bi-send"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS variables -->
    <script>
        const APP_URL = '<?= APP_URL ?>';
        const IS_LOGGED_IN = <?= (isset($_SESSION['user_auth']) || isset($_SESSION['admin_auth'])) ? 'true' : 'false' ?>;
    </script>
    <!-- Custom JS -->
    <script src="<?= APP_URL ?>/js/main.js?v=<?= time() ?>"></script>
    <script src="<?= APP_URL ?>/js/ai-chat.js?v=<?= time() ?>"></script>
</body>
</html>
