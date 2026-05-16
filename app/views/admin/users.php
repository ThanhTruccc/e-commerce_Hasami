<!-- app/views/admin/users.php -->
<?php $pageTitle = 'Quản lý Người dùng | GlowViet Admin'; ?>

<div class="admin-page-header">
    <h1 class="admin-page-title"><i class="bi bi-people me-2"></i>Quản lý Người dùng</h1>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người dùng</th>
                        <th>Vai trò</th>
                        <th>Loại da</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['data'] as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar-small bg-primary text-white">
                                    <?= mb_substr($u['name'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                                    <small class="text-muted"><?= $u['email'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-info' ?>">
                                <?= strtoupper($u['role']) ?>
                            </span>
                        </td>
                        <td><?= SKIN_TYPES[$u['skin_type']] ?? 'Chưa cập nhật' ?></td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $u['is_active'] ? 'Hoạt động' : 'Đã khoá' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                            <a href="<?= APP_URL ?>/admin/userToggle/<?= $u['id'] ?>" 
                               class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                <i class="bi <?= $u['is_active'] ? 'bi-lock' : 'bi-unlock' ?>"></i>
                                <?= $u['is_active'] ? 'Khoá' : 'Mở' ?>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
