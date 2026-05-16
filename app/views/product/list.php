<!-- app/views/product/list.php -->
<?php $pageTitle = 'Danh Sách Sản Phẩm | GlowViet'; ?>

<section class="product-list-section py-5">
    <div class="container">
        <div class="row g-4">

            <!-- ── SIDEBAR FILTER ──────────────────────────── -->
            <div class="col-lg-3">
                <div class="filter-sidebar sticky-sidebar">
                    <h5 class="filter-title"><i class="bi bi-funnel me-2"></i>Bộ Lọc</h5>
                    <form action="<?= APP_URL ?>/product" method="GET" id="filterForm">

                        <!-- Tìm kiếm -->
                        <?php if (!empty($params['search'])): ?>
                        <div class="filter-group">
                            <p class="text-muted small">Kết quả tìm kiếm: "<strong><?= htmlspecialchars($params['search']) ?></strong>"</p>
                            <input type="hidden" name="q" value="<?= htmlspecialchars($params['search']) ?>">
                        </div>
                        <?php endif; ?>

                        <!-- Danh mục -->
                        <div class="filter-group">
                            <h6 class="filter-group-title">Danh Mục</h6>
                            <?php foreach ($categories as $cat): ?>
                            <div class="filter-check">
                                <input class="form-check-input" type="radio" name="category"
                                       id="cat_<?= $cat['id'] ?>" value="<?= $cat['id'] ?>"
                                       <?= ((int)$params['category_id'] === (int)$cat['id']) ? 'checked' : '' ?>
                                       onchange="document.getElementById('filterForm').submit()">
                                <label class="form-check-label" for="cat_<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                    <?php if (!empty($cat['children'])): ?>
                                    <ul class="sub-cat-list mt-1">
                                        <?php foreach ($cat['children'] as $sub): ?>
                                        <li>
                                            <input type="radio" name="category" value="<?= $sub['id'] ?>"
                                                   id="cat_<?= $sub['id'] ?>"
                                                   <?= ((int)$params['category_id'] === (int)$sub['id']) ? 'checked' : '' ?>
                                                   onchange="document.getElementById('filterForm').submit()">
                                            <label for="cat_<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></label>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Loại da -->
                        <div class="filter-group">
                            <h6 class="filter-group-title"><i class="bi bi-person-badge me-1"></i>Loại Da</h6>
                            <select name="skin_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Tất cả loại da</option>
                                <?php foreach ($skinTypes as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($params['skin_type'] === $key) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Giá -->
                        <div class="filter-group">
                            <h6 class="filter-group-title"><i class="bi bi-currency-dollar me-1"></i>Khoảng Giá</h6>
                            <div class="price-range-labels d-flex justify-content-between mb-2">
                                <span id="priceMinLabel"><?= number_format($params['price_min'] ?: 0) ?>đ</span>
                                <span id="priceMaxLabel"><?= number_format($params['price_max'] ?: 5000000) ?>đ</span>
                            </div>
                            <input type="range" class="form-range" name="price_max" id="priceRange"
                                   min="0" max="5000000" step="50000"
                                   value="<?= $params['price_max'] ?: 5000000 ?>"
                                   oninput="document.getElementById('priceMaxLabel').textContent = formatPrice(this.value)">
                            <input type="hidden" name="price_min" value="0">
                        </div>

                        <!-- Thương hiệu -->
                        <div class="filter-group">
                            <h6 class="filter-group-title"><i class="bi bi-tag me-1"></i>Thương Hiệu</h6>
                            <div class="brand-list" style="max-height: 200px; overflow-y: auto">
                                <?php foreach ($brands as $brand): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="brand"
                                           id="brand_<?= md5($brand) ?>" value="<?= htmlspecialchars($brand) ?>"
                                           <?= ($params['brand'] === $brand) ? 'checked' : '' ?>
                                           onchange="this.form.submit()">
                                    <label class="form-check-label" for="brand_<?= md5($brand) ?>">
                                        <?= htmlspecialchars($brand) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">Áp dụng bộ lọc</button>
                        <a href="<?= APP_URL ?>/product" class="btn btn-outline-secondary w-100 btn-sm">Xoá bộ lọc</a>
                    </form>
                </div>
            </div>

            <!-- ── PRODUCT GRID ────────────────────────────── -->
            <div class="col-lg-9">

                <!-- Toolbar -->
                <div class="product-toolbar d-flex justify-content-between align-items-center mb-4">
                    <div class="result-info">
                        <span class="result-count"><?= $result['total'] ?> sản phẩm</span>
                        <?php if (!empty($params['skin_type'])): ?>
                        <span class="active-filter-tag"><?= SKIN_TYPES[$params['skin_type']] ?> <a href="#" onclick="clearFilter('skin_type')">×</a></span>
                        <?php endif; ?>
                    </div>
                    <div class="sort-box d-flex align-items-center gap-2">
                        <label class="sort-label">Sắp xếp:</label>
                        <select name="sort" class="form-select form-select-sm sort-select"
                                onchange="setSortAndSubmit(this.value)">
                            <option value="newest"     <?= ($params['sort']==='newest')     ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="bestseller" <?= ($params['sort']==='bestseller') ? 'selected' : '' ?>>Bán chạy</option>
                            <option value="price_asc"  <?= ($params['sort']==='price_asc')  ? 'selected' : '' ?>>Giá tăng</option>
                            <option value="price_desc" <?= ($params['sort']==='price_desc') ? 'selected' : '' ?>>Giá giảm</option>
                            <option value="rating"     <?= ($params['sort']==='rating')     ? 'selected' : '' ?>>Đánh giá cao</option>
                        </select>
                    </div>
                </div>

                <!-- Products -->
                <?php if (!empty($result['data'])): ?>
                <div class="row g-4" id="productGrid">
                    <?php foreach ($result['data'] as $product): ?>
                    <div class="col-6 col-md-4">
                        <?php include APP_PATH . '/views/partials/product_card.php'; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($result['pages'] > 1): ?>
                <nav class="mt-5 d-flex justify-content-center">
                    <ul class="pagination">
                        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                        <li class="page-item <?= ($p === $result['page']) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>">
                                <?= $p ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <?php else: ?>
                <div class="empty-state text-center py-5">
                    <div class="empty-icon"><i class="bi bi-search fs-1"></i></div>
                    <h5>Không tìm thấy sản phẩm</h5>
                    <p class="text-muted">Thử thay đổi bộ lọc hoặc từ khoá tìm kiếm</p>
                    <a href="<?= APP_URL ?>/product" class="btn btn-primary">Xem tất cả sản phẩm</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function setSortAndSubmit(val) {
    const url = new URL(window.location);
    url.searchParams.set('sort', val);
    url.searchParams.set('page', 1);
    window.location = url;
}
function formatPrice(val) {
    return new Intl.NumberFormat('vi-VN').format(val) + 'đ';
}
</script>
