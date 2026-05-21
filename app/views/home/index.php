<!-- ============================================================
     HOME PAGE - app/views/home/index.php
     ============================================================ -->

<?php $pageTitle = 'Hasami - Mỹ Phẩm Chính Hãng | AI Gợi Ý Sản Phẩm'; ?>

<!-- ── HERO SECTION ──────────────────────────────────────── -->
<section class="hero-section">
    <div class="hero-bg-blur"></div>
    <div class="container">
        <div class="row align-items-center min-vh-80">
            <div class="col-lg-6 hero-content">
                <span class="hero-badge"><i class="bi bi-stars"></i> AI Skincare Advisor</span>
                <h1 class="hero-title">
                    Làn Da Rạng Rỡ<br>
                    <span class="gradient-text">Dành Riêng Cho Bạn</span>
                </h1>
                <p class="hero-sub">
                    Hệ thống AI thông minh phân tích loại da và gợi ý sản phẩm
                    phù hợp nhất. Hơn 500+ sản phẩm chính hãng.
                </p>
                <div class="hero-actions d-flex gap-3 flex-wrap">
                    <a href="#ai-section" class="btn btn-hero-primary">
                        <i class="bi bi-magic me-2"></i>Thử AI Gợi Ý
                    </a>
                    <a href="<?= APP_URL ?>/product" class="btn btn-hero-outline">
                        Xem Tất Cả <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
                <div class="hero-stats d-flex gap-4 mt-4">
                    <div class="stat-item"><span class="stat-num">500+</span><span class="stat-label">Sản phẩm</span>
                    </div>
                    <div class="stat-item"><span class="stat-num">10K+</span><span class="stat-label">Khách hàng</span>
                    </div>
                    <div class="stat-item"><span class="stat-num">4.9★</span><span class="stat-label">Đánh giá</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-image-col d-none d-lg-block">
                <div class="hero-image-wrapper">
                    <div class="hero-blob"></div>
                    <div class="floating-card card-1">
                        <i class="bi bi-check-circle-fill text-success"></i> Da dầu → BHA 2%
                    </div>
                    <div class="floating-card card-2">
                        <i class="bi bi-stars text-warning"></i> AI gợi ý cho bạn
                    </div>
                    <div class="hero-product-circle">
                        <span class="hero-emoji">✨</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Scroll indicator -->
    <div class="scroll-indicator">
        <div class="scroll-dot"></div>
    </div>
</section>

<!-- ── CATEGORY STRIP ────────────────────────────────────── -->
<section class="category-strip py-5">
    <div class="container">
        <div class="row g-3 justify-content-center">
            <?php
            $catIcons = ['skincare' => 'bi-droplet-fill', 'makeup' => 'bi-palette-fill', 'personal-care' => 'bi-heart-fill'];
            $catColors = ['skincare' => '#FF6B9D', 'makeup' => '#A855F7', 'personal-care' => '#06B6D4'];
            foreach ($categories as $cat):
                if ($cat['parent_id'])
                    continue; // Chỉ hiện danh mục gốc
                $icon = $catIcons[$cat['slug']] ?? 'bi-grid-fill';
                $color = $catColors[$cat['slug']] ?? '#6366F1';
                ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="<?= APP_URL ?>/product?category=<?= $cat['id'] ?>" class="category-card text-decoration-none">
                        <div class="cat-icon" style="background: <?= $color ?>22; color: <?= $color ?>">
                            <i class="bi <?= $icon ?> fs-2"></i>
                        </div>
                        <h6 class="cat-name"><?= htmlspecialchars($cat['name']) ?></h6>
                        <span class="cat-count"><?= $cat['product_count'] ?> sản phẩm</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── FLASH SALE SECTION ────────────────────────────────── -->
<?php
// Query direct to get active products with sale_price
$dbConn = (new Product())->getDb();
$flashProducts = $dbConn->query("SELECT * FROM products WHERE sale_price IS NOT NULL AND status = 'active' LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

if (!empty($flashProducts)):
    ?>
    <section class="flashsale-section-wrapper py-4">
        <div class="container">
            <div class="flashsale-section">
                <div
                    class="flashsale-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="flashsale-title"><span class="flashsale-fire">🔥</span> FLASH SALE GIỜ VÀNG</h3>
                    </div>
                    <div class="countdown-box">
                        <span class="countdown-label d-none d-sm-inline me-2"><i class="bi bi-clock-history"></i> Kết thúc
                            sau:</span>
                        <span class="countdown-time" id="countdown-hours">03</span>
                        <span class="countdown-colon">:</span>
                        <span class="countdown-time" id="countdown-minutes">45</span>
                        <span class="countdown-colon">:</span>
                        <span class="countdown-time" id="countdown-seconds">12</span>

?>
<section class="flashsale-section-wrapper py-4">
    <div class="container">
        <div class="flashsale-section">
            <div class="flashsale-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="flashsale-title"><span class="flashsale-fire">🔥</span> FLASH SALE GIỜ VÀNG</h3>
                </div>
                <div class="countdown-box">
                    <span class="countdown-label d-none d-sm-inline me-2"><i class="bi bi-clock-history"></i> Kết thúc sau:</span>
                    <span class="countdown-time" id="countdown-hours">03</span>
                    <span class="countdown-colon">:</span>
                    <span class="countdown-time" id="countdown-minutes">45</span>
                    <span class="countdown-colon">:</span>
                    <span class="countdown-time" id="countdown-seconds">12</span>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($flashProducts as $index => $product): 
                    $price = (float)$product['price'];
                    $salePrice = (float)$product['sale_price'];
                    $discountPercent = round((($price - $salePrice) / $price) * 100);
                    // Fake some progress based on product ID to make it look extremely active
                    $progressVal = (($product['id'] * 17) % 35) + 50; 
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card flashsale-card position-relative">
                        <span class="badge-discount">-<?= $discountPercent ?>%</span>
                        <div class="product-img-container">
                            <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($product['image']) ?>" 
                                 class="product-img" alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='<?= APP_URL ?>/images/placeholder.jpg'">
                            <div class="product-action-overlay">
                                <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>" class="btn-overlay-view"><i class="bi bi-eye"></i></a>
                                <button onclick="addToCart(<?= $product['id'] ?>)" class="btn-overlay-cart"><i class="bi bi-bag-plus"></i></button>
                            </div>
                        </div>
                        <div class="product-info-container">
                            <span class="product-brand"><?= htmlspecialchars($product['brand']) ?></span>
                            <h5 class="product-title-text text-truncate">
                                <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                            </h5>
                            
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="product-price-sale"><?= number_format($salePrice, 0, ',', '.') ?>đ</span>
                                <span class="product-price-original text-decoration-line-through"><?= number_format($price, 0, ',', '.') ?>đ</span>
                            </div>
                            
                            <!-- Progress sold bar -->
                            <div class="flashsale-progress-container">
                                <div class="flashsale-progress-text">
                                    <span>Đã bán <?= $progressVal ?>%</span>
                                    <span><?= ($progressVal > 75) ? '🔥 Sắp hết' : 'Đang bán chạy' ?></span>
                                </div>
                                <div class="flashsale-progress-bar">
                                    <div class="flashsale-progress-fill" style="width: <?= $progressVal ?>%"></div>
                                </div>
                            </div>
                        </div>
>>>>>>> d29ce50 (update project)
                    </div>
                </div>

                <div class="row g-4">
                    <?php foreach ($flashProducts as $index => $product):
                        $price = (float) $product['price'];
                        $salePrice = (float) $product['sale_price'];
                        $discountPercent = round((($price - $salePrice) / $price) * 100);
                        // Fake some progress based on product ID to make it look extremely active
                        $progressVal = (($product['id'] * 17) % 35) + 50;
                        ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card flashsale-card position-relative" data-product-id="<?= $product['id'] ?>">
                                <div class="product-img-wrap">
                                    <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($product['image']) ?>"
                                        class="product-img" alt="<?= htmlspecialchars($product['name']) ?>"
                                        onerror="this.src='<?= APP_URL ?>/images/placeholder.jpg'">
                                    
                                    <span class="badge-sale">-<?= $discountPercent ?>%</span>
                                    
                                    <div class="product-actions">
                                        <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>"
                                           class="btn-quickview" title="Xem nhanh">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button onclick="addToCart(<?= $product['id'] ?>)" class="btn-quick-cart" title="Thêm vào giỏ">
                                            <i class="bi bi-bag-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="product-info">
                                    <span class="product-brand"><?= htmlspecialchars($product['brand']) ?></span>
                                    <h6 class="product-name">
                                        <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                    </h6>

                                    <div class="product-price mt-2">
                                        <span class="price-sale"><?= number_format($salePrice, 0, ',', '.') ?>đ</span>
                                        <span class="price-original"><?= number_format($price, 0, ',', '.') ?>đ</span>
                                    </div>

                                    <!-- Progress sold bar -->
                                    <div class="flashsale-progress-container">
                                        <div class="flashsale-progress-text">
                                            <span>Đã bán <?= $progressVal ?>%</span>
                                            <span><?= ($progressVal > 75) ? '🔥 Sắp hết' : 'Đang bán chạy' ?></span>
                                        </div>
                                        <div class="flashsale-progress-bar">
                                            <div class="flashsale-progress-fill" style="width: <?= $progressVal ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Script for Real-time Countdown Timer -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Set target countdown: end of today (23:59:59)
            const targetDate = new Date();
            targetDate.setHours(23, 59, 59, 999);

            function updateTimer() {
                const now = new Date().getTime();
                const distance = targetDate.getTime() - now;

                if (distance < 0) {
                    // Reset to end of next day if finished
                    targetDate.setDate(targetDate.getDate() + 1);
                    targetDate.setHours(23, 59, 59, 999);
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                const hoursEl = document.getElementById('countdown-hours');
                const minutesEl = document.getElementById('countdown-minutes');
                const secondsEl = document.getElementById('countdown-seconds');

                if (hoursEl) hoursEl.innerText = String(hours).padStart(2, '0');
                if (minutesEl) minutesEl.innerText = String(minutes).padStart(2, '0');
                if (secondsEl) secondsEl.innerText = String(seconds).padStart(2, '0');
            }

            
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            const hoursEl = document.getElementById('countdown-hours');
            const minutesEl = document.getElementById('countdown-minutes');
            const secondsEl = document.getElementById('countdown-seconds');
            
            if (hoursEl) hoursEl.innerText = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.innerText = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.innerText = String(seconds).padStart(2, '0');
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    });
</script>
=======
    ?>
    <section class="flashsale-section-wrapper py-4">
        <div class="container">
            <div class="flashsale-section">
                <div
                    class="flashsale-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="flashsale-title"><span class="flashsale-fire">🔥</span> FLASH SALE GIỜ VÀNG</h3>
                    </div>
                    <div class="countdown-box">
                        <span class="countdown-label d-none d-sm-inline me-2"><i class="bi bi-clock-history"></i> Kết thúc
                            sau:</span>
                        <span class="countdown-time" id="countdown-hours">03</span>
                        <span class="countdown-colon">:</span>
                        <span class="countdown-time" id="countdown-minutes">45</span>
                        <span class="countdown-colon">:</span>
                        <span class="countdown-time" id="countdown-seconds">12</span>
                    </div>
                </div>

                <div class="row g-4">
                    <?php foreach ($flashProducts as $index => $product):
                        $price = (float) $product['price'];
                        $salePrice = (float) $product['sale_price'];
                        $discountPercent = round((($price - $salePrice) / $price) * 100);
                        // Fake some progress based on product ID to make it look extremely active
                        $progressVal = (($product['id'] * 17) % 35) + 50;
                        ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card flashsale-card position-relative" data-product-id="<?= $product['id'] ?>">
                                <div class="product-img-wrap">
                                    <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($product['image']) ?>"
                                        class="product-img" alt="<?= htmlspecialchars($product['name']) ?>"
                                        onerror="this.src='<?= APP_URL ?>/images/placeholder.jpg'">
                                    
                                    <span class="badge-sale">-<?= $discountPercent ?>%</span>
                                    
                                    <div class="product-actions">
                                        <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>"
                                           class="btn-quickview" title="Xem nhanh">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button onclick="addToCart(<?= $product['id'] ?>)" class="btn-quick-cart" title="Thêm vào giỏ">
                                            <i class="bi bi-bag-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="product-info">
                                    <span class="product-brand"><?= htmlspecialchars($product['brand']) ?></span>
                                    <h6 class="product-name">
                                        <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                    </h6>

                                    <div class="product-price mt-2">
                                        <span class="price-sale"><?= number_format($salePrice, 0, ',', '.') ?>đ</span>
                                        <span class="price-original"><?= number_format($price, 0, ',', '.') ?>đ</span>
                                    </div>

                                    <!-- Progress sold bar -->
                                    <div class="flashsale-progress-container">
                                        <div class="flashsale-progress-text">
                                            <span>Đã bán <?= $progressVal ?>%</span>
                                            <span><?= ($progressVal > 75) ? '🔥 Sắp hết' : 'Đang bán chạy' ?></span>
                                        </div>
                                        <div class="flashsale-progress-bar">
                                            <div class="flashsale-progress-fill" style="width: <?= $progressVal ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Script for Real-time Countdown Timer -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Set target countdown: end of today (23:59:59)
            const targetDate = new Date();
            targetDate.setHours(23, 59, 59, 999);

            function updateTimer() {
                const now = new Date().getTime();
                const distance = targetDate.getTime() - now;

                if (distance < 0) {
                    // Reset to end of next day if finished
                    targetDate.setDate(targetDate.getDate() + 1);
                    targetDate.setHours(23, 59, 59, 999);
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                const hoursEl = document.getElementById('countdown-hours');
                const minutesEl = document.getElementById('countdown-minutes');
                const secondsEl = document.getElementById('countdown-seconds');

                if (hoursEl) hoursEl.innerText = String(hours).padStart(2, '0');
                if (minutesEl) minutesEl.innerText = String(minutes).padStart(2, '0');
                if (secondsEl) secondsEl.innerText = String(seconds).padStart(2, '0');
            }
>>>>>>> d29ce50 (update project)

            updateTimer();
            setInterval(updateTimer, 1000);
        });
    </script>

<?php endif; ?>

<!-- ── AI RECOMMENDATION SECTION ────────────────────────── -->
<section class="ai-section py-5" id="ai-section">
    <div class="container">
        <!-- Header -->
        <div class="section-header text-center mb-5">
            <span class="section-badge"><i class="bi bi-robot me-1"></i>AI Smart Advisor</span>
            <h2 class="section-title">Gợi Ý Dành Riêng Cho Bạn</h2>
            <p class="section-sub">AI phân tích loại da, sở thích và lịch sử mua hàng để đề xuất sản phẩm hoàn hảo nhất
            </p>
        </div>

        <!-- AI Filter Panel -->
        <div class="ai-filter-panel mb-5">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="ai-filter-label mb-0"><i class="bi bi-person-badge me-1"></i>Loại da</label>
                        <a href="#" class="text-primary small fw-bold text-decoration-none" data-bs-toggle="modal"
                            data-bs-target="#skinTestModal">
                            <i class="bi bi-magic me-1"></i>Kiểm tra ngay
                        </a>
                    </div>
                    <select class="form-select ai-select" id="ai_skin_type">
                        <option value="">-- Tất cả loại da --</option>
                        <?php foreach (SKIN_TYPES as $key => $label): ?>
                            <option value="<?= $key ?>" <?= (($_SESSION['ai_filters']['skin_type'] ?? '') === $key) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="ai-filter-label"><i class="bi bi-currency-dollar me-1"></i>Từ (đ)</label>
                    <input type="number" class="form-control ai-input" id="ai_price_min" placeholder="0" min="0">
                </div>
                <div class="col-md-2">
                    <label class="ai-filter-label">Đến (đ)</label>
                    <input type="number" class="form-control ai-input" id="ai_price_max" placeholder="5.000.000"
                        min="0">
                </div>
                <div class="col-md-3">
                    <label class="ai-filter-label"><i class="bi bi-grid me-1"></i>Danh mục</label>
                    <select class="form-select ai-select" id="ai_category_id">
                        <option value="">-- Tất cả --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-ai-filter w-100" id="btnAiFilter">
                        <i class="bi bi-magic me-1"></i>Gợi ý ngay
                    </button>
                </div>
            </div>
        </div>

        <!-- AI Results Grid -->
        <div id="aiResultsGrid" class="row g-4">
            <?php if (!empty($recommended)): ?>
                <?php foreach ($recommended as $product): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <?php include APP_PATH . '/views/partials/product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-ai">
                        <div class="empty-icon"><i class="bi bi-robot"></i></div>
                        <h5>AI đang học về bạn...</h5>
                        <p class="text-muted">Hãy chọn loại da hoặc mua vài sản phẩm để AI gợi ý chính xác hơn!</p>
                        <a href="<?= APP_URL ?>/product" class="btn btn-primary">Xem Tất Cả Sản Phẩm</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- AI Loading Overlay -->
        <div id="aiLoading" class="ai-loading d-none">
            <div class="ai-spinner">
                <div class="spinner-ring"></div>
                <p>AI đang phân tích...</p>
            </div>
        </div>
    </div>
</section>

<!-- ── FEATURED PRODUCTS ──────────────────────────────────── -->
<section class="featured-section py-5 bg-light-pink">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="section-badge">🔥 Hot</span>
                <h2 class="section-title mb-0">Sản Phẩm Nổi Bật</h2>
            </div>
            <a href="<?= APP_URL ?>/product" class="btn btn-outline-primary">Xem tất cả <i
                    class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <?php include APP_PATH . '/views/partials/product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── PROMO BANNER ──────────────────────────────────────── -->
<section class="promo-banner py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="promo-card promo-card-pink">
                    <div class="promo-content">
                        <span class="promo-tag">Skincare Deal</span>
                        <h3>Giảm 20%<br>Toàn bộ Serum</h3>
                        <a href="<?= APP_URL ?>/product?category=6" class="btn btn-white">Mua ngay</a>
                    </div>
                    <div class="promo-emoji">💧</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="promo-card promo-card-purple">
                    <div class="promo-content">
                        <span class="promo-tag">Makeup Mới</span>
                        <h3>Bộ Sưu Tập<br>Son Hè 2026</h3>
                        <a href="<?= APP_URL ?>/product?category=8" class="btn btn-white">Khám phá</a>
                    </div>
                    <div class="promo-emoji">💄</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── WHY US ─────────────────────────────────────────────── -->
<section class="why-us py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Tại Sao Chọn Hasami?</h2>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['icon' => 'bi-robot', 'color' => '#6366F1', 'title' => 'AI Gợi Ý Thông Minh', 'desc' => 'Thuật toán phân tích loại da và lịch sử mua hàng của bạn'],
                ['icon' => 'bi-shield-check', 'color' => '#10B981', 'title' => 'Chính Hãng 100%', 'desc' => 'Mọi sản phẩm đều được kiểm định và nhập khẩu chính hãng'],
                ['icon' => 'bi-truck', 'color' => '#F59E0B', 'title' => 'Giao Hàng Nhanh', 'desc' => 'Nhận hàng trong 2-3 ngày, miễn phí ship đơn từ 300k'],
                ['icon' => 'bi-arrow-return-left', 'color' => '#EF4444', 'title' => 'Đổi Trả 7 Ngày', 'desc' => 'Không hài lòng? Đổi trả miễn phí trong vòng 7 ngày'],
            ];
            foreach ($features as $f):
                ?>
                <div class="col-6 col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon" style="color: <?= $f['color'] ?>; background: <?= $f['color'] ?>15">
                            <i class="bi <?= $f['icon'] ?>"></i>
                        </div>
                        <h6 class="feature-title"><?= $f['title'] ?></h6>
                        <p class="feature-desc"><?= $f['desc'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── SKIN TEST MODAL ────────────────────────────────────── -->
<div class="modal fade" id="skinTestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden" style="border-radius: 25px;">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-magic me-2"></i>Kiểm Tra Loại Da Của Bạn</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="skinQuizSteps">
                    <!-- Step 1 -->
                    <div class="quiz-step active" data-step="1">
                        <h6 class="fw-bold mb-3">Câu 1: Buổi sáng thức dậy, da bạn trông như thế nào?</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="oily">A. Bóng
                                dầu khắp mặt</button>
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="dry">B. Khô
                                căng, hơi bong tróc</button>
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="combination">C.
                                Chỉ bóng vùng chữ T (mũi/trán)</button>
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="normal">D. Mềm
                                mại, thoải mái</button>
                        </div>
                    </div>
                    <!-- Step 2 (Hidden) -->
                    <div class="quiz-step d-none" data-step="2">
                        <h6 class="fw-bold mb-3">Câu 2: Lỗ chân lông của bạn trông ra sao?</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="oily">A. To và
                                rõ rệt khắp mặt</button>
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="dry">B. Rất
                                nhỏ, khó nhìn thấy</button>
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="combination">C.
                                Chỉ to ở vùng mũi/trán</button>
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="normal">D.
                                Trung bình, không quá rõ</button>
                        </div>
                    </div>
                    <!-- Step 3 (Hidden) -->
                    <div class="quiz-step d-none" data-step="3">
                        <h6 class="fw-bold mb-3">Câu 3: Da bạn có thường xuyên bị đỏ hoặc châm chích không?</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="sensitive">A.
                                Thường xuyên bị kích ứng</button>
                            <button class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="none">B. Rất
                                hiếm khi bị kích ứng</button>
                        </div>
                    </div>
                </div>

                <!-- Result Area (Hidden) -->
                <div id="quizResult" class="d-none text-center py-4">
                    <div class="mb-3"><i class="bi bi-check-circle-fill text-success fs-1"></i></div>
                    <h5 class="fw-bold">Kết quả: Da <span id="skinResultName" class="text-primary">...</span></h5>
                    <p class="text-muted small mb-4">Hệ thống đã tự động chọn loại da này để gợi ý sản phẩm phù hợp cho
                        bạn.</p>
                    <button class="btn btn-primary px-5" data-bs-dismiss="modal">Khám phá ngay</button>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light justify-content-center">
                <small class="text-muted">Cung cấp bởi Hasami Smart AI</small>
            </div>
        </div>
    </div>
</div>

<!-- AI Filter Script -->
<script>
    // Logic bài kiểm tra da
    (function () {
        let scores = { oily: 0, dry: 0, combination: 0, normal: 0, sensitive: 0 };
        let currentStep = 1;

        document.querySelectorAll('.quiz-opt').forEach(btn => {
            btn.addEventListener('click', function () {
                const type = this.dataset.type;
                if (type !== 'none') scores[type]++;

                if (currentStep < 3) {
                    // Sang bước tiếp theo
                    document.querySelector(`.quiz-step[data-step="${currentStep}"]`).classList.add('d-none');
                    currentStep++;
                    document.querySelector(`.quiz-step[data-step="${currentStep}"]`).classList.remove('d-none');
                } else {
                    // Tính toán kết quả
                    finishQuiz();
                }
            });
        });

        function finishQuiz() {
            document.getElementById('skinQuizSteps').classList.add('d-none');
            document.getElementById('quizResult').classList.remove('d-none');

            let result = 'normal';
            if (scores.sensitive > 0) {
                result = 'sensitive';
            } else {
                // Tìm loại da có điểm cao nhất
                let maxScore = -1;
                for (let type in scores) {
                    if (type !== 'sensitive' && scores[type] > maxScore) {
                        maxScore = scores[type];
                        result = type;
                    }
                }
            }

            // Cập nhật giao diện
            const labels = {
                'oily': 'Dầu',
                'dry': 'Khô',
                'combination': 'Hỗn hợp',
                'normal': 'Thường',
                'sensitive': 'Nhạy cảm'
            };
            document.getElementById('skinResultName').innerText = labels[result];
            document.getElementById('ai_skin_type').value = result;

            // Kích hoạt nút "Gợi ý ngay"
            document.getElementById('btnAiFilter').click();
        }

        // Reset quiz khi đóng modal
        document.getElementById('skinTestModal').addEventListener('hidden.bs.modal', function () {
            scores = { oily: 0, dry: 0, combination: 0, normal: 0, sensitive: 0 };
            currentStep = 1;
            document.getElementById('skinQuizSteps').classList.remove('d-none');
            document.getElementById('quizResult').classList.add('d-none');
            document.querySelectorAll('.quiz-step').forEach(s => s.classList.add('d-none'));
            document.querySelector('.quiz-step[data-step="1"]').classList.remove('d-none');
        });
    })();

    document.getElementById('btnAiFilter')?.addEventListener('click', function () {
        const formData = new FormData();
        formData.append('skin_type', document.getElementById('ai_skin_type').value);
        formData.append('price_min', document.getElementById('ai_price_min').value || 0);
        formData.append('price_max', document.getElementById('ai_price_max').value || 10000000);
        formData.append('category_id', document.getElementById('ai_category_id').value);

        document.getElementById('aiLoading').classList.remove('d-none');
        document.getElementById('aiResultsGrid').style.opacity = '0.4';

        fetch(APP_URL + '/home/aiFilter', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
            .then(r => r.json())
            .then(res => {
                document.getElementById('aiLoading').classList.add('d-none');
                document.getElementById('aiResultsGrid').style.opacity = '1';
                if (res.success) {
                    renderAiProducts(res.products);
                }
            });
    });

    function renderAiProducts(products) {
        const grid = document.getElementById('aiResultsGrid');
        if (!products.length) {
            grid.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">Không tìm thấy sản phẩm phù hợp</p></div>';
            return;
        }
        grid.innerHTML = products.map(p => `
        <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card fade-in">
                <div class="product-img-wrap">
                    ${p.image ? `<img src="${APP_URL}/images/products/${p.image}" alt="${p.name}" class="product-img" loading="lazy">` : '<div class="product-img-placeholder"><i class="bi bi-image fs-1 text-muted"></i></div>'}
                    ${p.sale_price ? '<span class="badge-sale">Sale</span>' : ''}
                    <div class="product-actions">
                        <button class="btn-wishlist" onclick="toggleWishlist(${p.id}, this)"><i class="bi bi-heart"></i></button>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-brand">${p.brand}</span>
                    <h6 class="product-name"><a href="${APP_URL}/product/detail/${p.id}">${p.name}</a></h6>
                    <div class="product-price">
                        ${p.sale_price ? `<span class="price-sale">${formatPrice(p.sale_price)}</span><span class="price-original">${formatPrice(p.price)}</span>` : `<span class="price-sale">${formatPrice(p.price)}</span>`}
                    </div>
                    ${p.ai_reason ? `<div class="ai-reason-tag"><i class="bi bi-magic me-1"></i>${p.ai_reason}</div>` : ''}
                    <button class="btn btn-add-cart w-100 mt-2" onclick="addToCart(${p.id})">
                        <i class="bi bi-bag-plus me-1"></i>Thêm giỏ hàng
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    }
</script>