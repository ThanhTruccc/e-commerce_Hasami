<!-- app/views/wishlist/index.php -->
<?php $pageTitle = 'Sản Phẩm Yêu Thích | Hasami'; ?>

<section class="wishlist-section py-5">
    <div class="container">
        <h2 class="page-title mb-4"><i class="bi bi-heart me-2"></i>Sản Phẩm Yêu Thích</h2>

        <?php if (!empty($products)): ?>
            <div class="row g-4">
                <?php foreach ($products as $product):
                    $price = (float) $product['price'];
                    $salePrice = (float) $product['sale_price'];
                    $discountPercent = $salePrice > 0 ? round((($price - $salePrice) / $price) * 100) : 0;
                ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card position-relative" data-product-id="<?= $product['id'] ?>">
                            <div class="product-img-wrap">
                                <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($product['image']) ?>"
                                     class="product-img" alt="<?= htmlspecialchars($product['name']) ?>"
                                     onerror="this.src='<?= APP_URL ?>/images/placeholder.jpg'">
                                     
                                <?php if ($discountPercent > 0): ?>
                                    <span class="badge-sale">-<?= $discountPercent ?>%</span>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>"
                                       class="btn-quickview" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="product-info mt-3 text-center">
                                <h6 class="product-name">
                                    <a href="<?= APP_URL ?>/product/detail/<?= $product['id'] ?>">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </h6>
                                <div class="product-price">
                                    <?php if ($salePrice > 0): ?>
                                        <span class="sale-price"><?= number_format($salePrice, 0, ',', '.') ?>đ</span>
                                        <span class="old-price text-muted text-decoration-line-through fs-sm"><?= number_format($price, 0, ',', '.') ?>đ</span>
                                    <?php else: ?>
                                        <span class="sale-price"><?= number_format($price, 0, ',', '.') ?>đ</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 d-flex justify-content-center gap-2">
                                    <button class="btn btn-outline-danger btn-sm" onclick="toggleWishlist(<?= $product['id'] ?>, this)">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                    <form class="add-to-cart-form d-inline-block">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-bag-plus"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist text-center py-5">
                <div class="empty-wishlist-icon" style="font-size: 4rem; color: #ccc;"><i class="bi bi-heartbreak"></i></div>
                <h4 class="mt-3">Danh sách yêu thích trống</h4>
                <p class="text-muted">Bạn chưa có sản phẩm nào trong danh sách yêu thích.</p>
                <a href="<?= APP_URL ?>/product" class="btn btn-primary mt-2">Khám phá sản phẩm</a>
            </div>
        <?php endif; ?>
    </div>
</section>
