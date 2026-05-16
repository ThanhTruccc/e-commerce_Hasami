<!-- app/views/product/detail.php -->
<?php $pageTitle = htmlspecialchars($product['name']) . ' | GlowViet'; ?>

<section class="product-detail-section py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/product">Sản phẩm</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/product?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- ── ẢNH SẢN PHẨM ──────────────────────────── -->
            <div class="col-lg-5">
                <div class="product-gallery">
                    <div class="product-main-image">
                        <?php if ($product['image']): ?>
                        <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($product['image']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="img-fluid rounded-3 main-product-img" id="mainImg">
                        <?php else: ?>
                        <div class="product-detail-placeholder"><i class="bi bi-image fs-1 text-muted"></i></div>
                        <?php endif; ?>

                        <?php if ($product['sale_price']): ?>
                        <div class="detail-badge-sale">
                            -<?= round((1 - $product['sale_price']/$product['price'])*100) ?>% OFF
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── THÔNG TIN SẢN PHẨM ─────────────────────── -->
            <div class="col-lg-7">
                <div class="product-detail-info">
                    <span class="product-brand-detail"><?= htmlspecialchars($product['brand']) ?></span>
                    <h1 class="product-detail-name"><?= htmlspecialchars($product['name']) ?></h1>

                    <!-- Rating -->
                    <div class="detail-rating d-flex align-items-center gap-2 mb-3">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= round($product['avg_rating']) ? 'bi-star-fill' : 'bi-star' ?> text-warning"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-num"><?= number_format($product['avg_rating'], 1) ?></span>
                        <span class="text-muted">(<?= $product['review_count'] ?> đánh giá)</span>
                        <span class="text-muted">·</span>
                        <span class="text-muted">Đã bán: <?= $product['sold_count'] ?></span>
                    </div>

                    <!-- Price -->
                    <div class="detail-price mb-4">
                        <?php if ($product['sale_price']): ?>
                        <span class="detail-price-sale"><?= number_format($product['sale_price'], 0, ',', '.') ?>đ</span>
                        <span class="detail-price-original"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                        <span class="detail-save-text">Tiết kiệm <?= number_format($product['price'] - $product['sale_price'], 0, ',', '.') ?>đ</span>
                        <?php else: ?>
                        <span class="detail-price-sale"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                        <?php endif; ?>
                    </div>

                    <!-- Skin Types -->
                    <?php $skinTypes = json_decode($product['skin_types'] ?? '[]', true); ?>
                    <?php if (!empty($skinTypes)): ?>
                    <div class="skin-type-badges mb-3">
                        <span class="skin-type-label"><i class="bi bi-person-badge me-1"></i>Phù hợp:</span>
                        <?php foreach ($skinTypes as $st): ?>
                        <span class="badge-skin"><?= SKIN_TYPES[$st] ?? $st ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Stock -->
                    <p class="detail-stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
                        <i class="bi <?= $product['stock'] > 0 ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>
                        <?= $product['stock'] > 0 ? "Còn hàng ({$product['stock']})" : 'Hết hàng' ?>
                    </p>

                    <!-- Add to Cart -->
                    <?php if ($product['stock'] > 0): ?>
                    <form action="<?= APP_URL ?>/cart/add" method="POST" class="add-to-cart-form mb-3">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="quantity-selector mb-3">
                            <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                            <input type="number" name="quantity" id="qty" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>">
                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-add-cart-detail flex-fill">
                                <i class="bi bi-bag-plus me-2"></i>Thêm vào giỏ
                            </button>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <button type="button" class="btn btn-wishlist-detail <?= $isWishlisted ? 'active' : '' ?>"
                                    onclick="toggleWishlist(<?= $product['id'] ?>, this)">
                                <i class="bi <?= $isWishlisted ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <?php endif; ?>

                    <!-- Badges -->
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="trust-badge"><i class="bi bi-shield-check text-success me-1"></i>Chính hãng</span>
                        <span class="trust-badge"><i class="bi bi-truck text-primary me-1"></i>Free ship 300k</span>
                        <span class="trust-badge"><i class="bi bi-arrow-return-left text-warning me-1"></i>Đổi trả 7 ngày</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TABS: MÔ TẢ / THÀNH PHẦN / ĐÁNH GIÁ ────────── -->
        <div class="product-tabs mt-5">
            <ul class="nav nav-tabs product-tab-nav" id="productTabs">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabDesc">Mô tả</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabIngredients">Thành phần</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabUsage">Hướng dẫn</a></li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tabReviews">
                        Đánh giá <span class="badge bg-primary"><?= $reviews['stats']['total'] ?? 0 ?></span>
                    </a>
                </li>
            </ul>
            <div class="tab-content product-tab-content p-4">
                <!-- Mô tả -->
                <div class="tab-pane fade show active" id="tabDesc">
                    <div class="desc-content"><?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả.')) ?></div>
                </div>

                <!-- Thành phần -->
                <div class="tab-pane fade" id="tabIngredients">
                    <h5><i class="bi bi-list-check me-2"></i>Thành Phần Chính</h5>
                    <?php
                    $ingredients = array_filter(array_map('trim', explode(',', $product['ingredients'] ?? '')));
                    ?>
                    <div class="ingredient-tags mt-3">
                        <?php foreach ($ingredients as $ing): ?>
                        <span class="ingredient-tag"><?= htmlspecialchars($ing) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Hướng dẫn -->
                <div class="tab-pane fade" id="tabUsage">
                    <?= nl2br(htmlspecialchars($product['usage_guide'] ?? 'Sử dụng theo hướng dẫn ghi trên bao bì sản phẩm.')) ?>
                </div>

                <!-- Đánh giá -->
                <div class="tab-pane fade" id="tabReviews">
                    <?php $stats = $reviews['stats']; ?>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="rating-summary text-center">
                                <div class="rating-big"><?= number_format($stats['avg'] ?? 0, 1) ?></div>
                                <div class="stars-big">
                                    <?php for ($i=1; $i<=5; $i++): ?>
                                    <i class="bi <?= $i <= round($stats['avg']??0) ? 'bi-star-fill' : 'bi-star' ?> text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted small"><?= $stats['total'] ?? 0 ?> đánh giá</p>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <?php foreach ([5,4,3,2,1] as $star): ?>
                            <div class="rating-bar-row d-flex align-items-center gap-2 mb-2">
                                <span class="star-label"><?= $star ?>★</span>
                                <div class="progress flex-fill" style="height:8px">
                                    <div class="progress-bar bg-warning" style="width: <?= $stats['total'] ? round(($stats["r{$star}"]/$stats['total'])*100) : 0 ?>%"></div>
                                </div>
                                <span class="star-count"><?= $stats["r{$star}"] ?? 0 ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Review List -->
                    <div class="review-list mt-4">
                        <?php foreach ($reviews['data'] as $review): ?>
                        <div class="review-item">
                            <div class="review-header d-flex justify-content-between">
                                <div class="reviewer">
                                    <div class="reviewer-avatar"><?= mb_substr($review['user_name'], 0, 1) ?></div>
                                    <div>
                                        <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                                        <?php if ($review['is_verified']): ?>
                                        <span class="verified-badge"><i class="bi bi-check-circle-fill text-success"></i> Đã mua hàng</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="review-date"><?= date('d/m/Y', strtotime($review['created_at'])) ?></div>
                            </div>
                            <div class="review-stars">
                                <?php for($i=1;$i<=5;$i++): ?>
                                <i class="bi <?= $i<=$review['rating'] ? 'bi-star-fill' : 'bi-star' ?> text-warning"></i>
                                <?php endfor; ?>
                            </div>
                            <?php if ($review['title']): ?>
                            <strong class="review-title"><?= htmlspecialchars($review['title']) ?></strong>
                            <?php endif; ?>
                            <p class="review-body"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Write Review -->
                    <?php if ($canReview): ?>
                    <div class="write-review-form mt-4">
                        <h6><i class="bi bi-pencil-square me-2"></i>Viết đánh giá của bạn</h6>
                        <form action="<?= APP_URL ?>/review/add/<?= $product['id'] ?>" method="POST">
                            <div class="star-picker mb-3" id="starPicker">
                                <?php for($i=1;$i<=5;$i++): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" <?= $i===5?'checked':'' ?>>
                                <label for="star<?= $i ?>"><i class="bi bi-star-fill"></i></label>
                                <?php endfor; ?>
                            </div>
                            <input type="text" name="title" class="form-control mb-2" placeholder="Tiêu đề đánh giá">
                            <textarea name="comment" class="form-control mb-3" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                        </form>
                    </div>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <div class="alert alert-info mt-3">
                        <a href="<?= APP_URL ?>/auth/login">Đăng nhập</a> để viết đánh giá.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── AI GỢI Ý TƯƠNG TỰ ─────────────────────────── -->
        <?php if (!empty($similar)): ?>
        <div class="similar-products mt-5">
            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <span class="section-badge"><i class="bi bi-magic me-1"></i>AI Gợi Ý</span>
                    <h4 class="mb-0">Sản Phẩm Tương Tự</h4>
                </div>
                <a href="<?= APP_URL ?>/product" class="btn btn-sm btn-outline-primary">Xem thêm</a>
            </div>
            <div class="row g-4">
                <?php foreach ($similar as $product): ?>
                <div class="col-6 col-md-3">
                    <?php include APP_PATH . '/views/partials/product_card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function changeQty(delta) {
    const input = document.getElementById('qty');
    const max   = parseInt(input.max);
    let   val   = parseInt(input.value) + delta;
    input.value = Math.max(1, Math.min(max, val));
}
</script>
