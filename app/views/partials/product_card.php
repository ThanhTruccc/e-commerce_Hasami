<!-- app/views/partials/product_card.php -->
<?php
$displayPrice = $product['sale_price'] ?? $product['price'];
$hasDiscount  = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
$discountPct  = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
$avgRating    = round($product['avg_rating'] ?? 0, 1);
$isWl         = (isset($_SESSION['user_auth']) || isset($_SESSION['admin_auth'])) && isset($wishlistIds) && in_array($product['id'], $wishlistIds);
?>
<div class="product-card" data-product-id="<?= $product['id'] ?>">
    <div class="product-img-wrap">
        <?php if ($product['image']): ?>
        <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($product['image']) ?>"
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="product-img" loading="lazy">
        <?php else: ?>
        <div class="product-img-placeholder">
            <i class="bi bi-image text-muted fs-1"></i>
        </div>
        <?php endif; ?>

        <?php if ($hasDiscount): ?>
        <span class="badge-sale">-<?= $discountPct ?>%</span>
        <?php endif; ?>
        <?php if ($product['featured'] ?? false): ?>
        <span class="badge-featured">✦ Nổi bật</span>
        <?php endif; ?>

        <div class="product-actions">
            <?php if (isset($_SESSION['user_auth']) || isset($_SESSION['admin_auth'])): ?>
            <button class="btn-wishlist <?= $isWl ? 'active' : '' ?>"
                    onclick="toggleWishlist(<?= $product['id'] ?>, this)"
                    title="<?= $isWl ? 'Bỏ yêu thích' : 'Yêu thích' ?>">
                <i class="bi <?= $isWl ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
            </button>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>"
               class="btn-quickview" title="Xem nhanh">
                <i class="bi bi-eye"></i>
            </a>
        </div>
    </div>

    <div class="product-info">
        <span class="product-brand"><?= htmlspecialchars($product['brand'] ?? '') ?></span>
        <h6 class="product-name">
            <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>">
                <?= htmlspecialchars($product['name']) ?>
            </a>
        </h6>

        <!-- Rating Stars -->
        <?php if ($avgRating > 0): ?>
        <div class="product-rating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="bi <?= $i <= $avgRating ? 'bi-star-fill' : ($i - 0.5 <= $avgRating ? 'bi-star-half' : 'bi-star') ?> text-warning"></i>
            <?php endfor; ?>
            <span class="rating-count">(<?= $product['review_count'] ?? 0 ?>)</span>
        </div>
        <?php endif; ?>

        <!-- Price -->
        <div class="product-price">
            <span class="price-sale"><?= number_format($displayPrice, 0, ',', '.') ?>đ</span>
            <?php if ($hasDiscount): ?>
            <span class="price-original"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
            <?php endif; ?>
        </div>

        <!-- AI Reason Tag -->
        <?php if (!empty($product['ai_reason'])): ?>
        <div class="ai-reason-tag">
            <i class="bi bi-magic me-1"></i><?= htmlspecialchars($product['ai_reason']) ?>
        </div>
        <?php endif; ?>

        <!-- Add to Cart -->
        <form action="<?= APP_URL ?>/cart/add" method="POST" class="add-cart-form mt-2">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn btn-add-cart w-100">
                <i class="bi bi-bag-plus me-1"></i>Thêm giỏ
            </button>
        </form>
    </div>
</div>
