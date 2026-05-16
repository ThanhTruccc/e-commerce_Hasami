<!-- app/views/admin/products.php -->
<?php $pageTitle = 'Quản lý Sản phẩm | GlowViet Admin'; ?>

<div class="admin-page-header d-flex justify-content-between align-items-center">
    <h1 class="admin-page-title"><i class="bi bi-box-seam me-2"></i>Quản lý Sản phẩm</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
        <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm mới
    </button>
</div>

<!-- Search & Filter -->
<div class="admin-card mb-4">
    <div class="admin-card-body">
        <form action="<?= APP_URL ?>/admin/products" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Tìm theo tên, thương hiệu..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($params['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary w-100">Lọc</button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th width="80">Ảnh</th>
                        <th>Thông tin sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Kho</th>
                        <th>Trạng thái</th>
                        <th width="100"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['data'] as $p): ?>
                    <tr>
                        <td>
                            <img src="<?= APP_URL ?>/images/products/<?= $p['image'] ?>" class="rounded" width="60" height="60" style="object-fit: cover" 
                                 onerror="this.src='https://placehold.co/100x100?text=No+Image'">
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($p['brand']) ?></small>
                        </td>
                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($p['category_name']) ?></span></td>
                        <td>
                            <?php if ($p['sale_price']): ?>
                                <div class="text-danger fw-bold"><?= number_format($p['sale_price'], 0, ',', '.') ?>đ</div>
                                <small class="text-muted text-decoration-line-through"><?= number_format($p['price'], 0, ',', '.') ?>đ</small>
                            <?php else: ?>
                                <div class="fw-bold"><?= number_format($p['price'], 0, ',', '.') ?>đ</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="<?= $p['stock'] <= 5 ? 'text-danger fw-bold' : '' ?>">
                                <?= $p['stock'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= $p['status'] === 'active' ? 'Đang bán' : 'Ẩn' ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick='editProduct(<?= json_encode($p) ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= APP_URL ?>/admin/productDelete/<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xoá sản phẩm này?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($result['pages'] > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <?php for($i=1; $i<=$result['pages']; $i++): ?>
        <li class="page-item <?= $i == $result['page'] ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($search ?? '') ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" action="<?= APP_URL ?>/admin/productSave" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="prodId" value="0">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" name="name" id="prodName" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Danh mục</label>
                        <select name="category_id" id="prodCat" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thương hiệu</label>
                        <input type="text" name="brand" id="prodBrand" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Giá bán</label>
                        <input type="number" name="price" id="prodPrice" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" name="sale_price" id="prodSalePrice" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Số lượng kho</label>
                        <input type="number" name="stock" id="prodStock" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" id="prodDesc" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Thành phần (cách nhau bởi dấu phẩy)</label>
                        <textarea name="ingredients" id="prodIng" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">Loại da phù hợp (AI Gợi ý)</label>
                        <?php foreach (SKIN_TYPES as $k => $v): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input skin-check" type="checkbox" name="skin_types[]" value="<?= $k ?>" id="skin_<?= $k ?>">
                            <label class="form-check-label" for="skin_<?= $k ?>"><?= $v ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<script>
function editProduct(p) {
    document.getElementById('modalTitle').textContent = 'Chỉnh sửa sản phẩm';
    document.getElementById('prodId').value = p.id;
    document.getElementById('prodName').value = p.name;
    document.getElementById('prodCat').value = p.category_id;
    document.getElementById('prodBrand').value = p.brand;
    document.getElementById('prodPrice').value = p.price;
    document.getElementById('prodSalePrice').value = p.sale_price;
    document.getElementById('prodStock').value = p.stock;
    document.getElementById('prodDesc').value = p.description;
    document.getElementById('prodIng').value = p.ingredients;
    
    // Clear & check skin types
    document.querySelectorAll('.skin-check').forEach(cb => cb.checked = false);
    const types = JSON.parse(p.skin_types || '[]');
    types.forEach(t => {
        const cb = document.getElementById('skin_' + t);
        if (cb) cb.checked = true;
    });

    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
}
</script>
