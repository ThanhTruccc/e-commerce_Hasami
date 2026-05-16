<!-- app/views/user/profile.php -->
<?php $pageTitle = 'Hồ Sơ Cá Nhân | GlowViet'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Profile Header -->
            <div class="profile-header mb-4 p-4 rounded-4 bg-white shadow-sm d-flex align-items-center">
                <div class="profile-avatar me-4">
                    <div class="avatar-circle bg-primary text-white fs-1 fw-bold">
                        <?= mb_substr($user['name'], 0, 1) ?>
                    </div>
                </div>
                <div class="profile-info">
                    <h2 class="mb-1 fw-bold"><?= htmlspecialchars($user['name']) ?></h2>
                    <p class="text-muted mb-0"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-light text-dark mt-2">
                        <i class="bi bi-person-badge me-1"></i>Da: <?= SKIN_TYPES[$user['skin_type']] ?? 'Chưa cập nhật' ?>
                    </span>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show rounded-3" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Sidebar Tabs -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="list-group list-group-flush" id="profileTabs" role="tablist">
                            <button class="list-group-item list-group-item-action active p-3 border-0" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                                <i class="bi bi-person-circle me-2"></i>Thông tin cá nhân
                            </button>
                            <button class="list-group-item list-group-item-action p-3 border-0" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                <i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu
                            </button>
                            <a href="<?= APP_URL ?>/order/history" class="list-group-item list-group-item-action p-3 border-0">
                                <i class="bi bi-bag-check me-2"></i>Lịch sử mua hàng
                            </a>
                            <a href="<?= APP_URL ?>/auth/logout" class="list-group-item list-group-item-action p-3 border-0 text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="col-md-8">
                    <div class="tab-content">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h5 class="fw-bold mb-4">Chỉnh sửa thông tin</h5>
                                <form action="<?= APP_URL ?>/user/updateProfile" method="POST">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label text-muted small fw-bold">Họ và tên</label>
                                            <input type="text" name="name" class="form-control rounded-3 p-2" value="<?= htmlspecialchars($user['name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">Email (Không thể thay đổi)</label>
                                            <input type="email" class="form-control rounded-3 p-2 bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">Số điện thoại</label>
                                            <input type="tel" name="phone" class="form-control rounded-3 p-2" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label text-muted small fw-bold mb-0">Loại da của bạn</label>
                                                <button type="button" class="btn btn-link btn-sm text-primary p-0 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#skinTestModal">
                                                    <i class="bi bi-magic me-1"></i>Kiểm tra lại loại da
                                                </button>
                                            </div>
                                            <select name="skin_type" id="reg_skin_type" class="form-select rounded-3 p-2">
                                                <?php foreach (SKIN_TYPES as $key => $label): ?>
                                                <option value="<?= $key ?>" <?= $user['skin_type'] === $key ? 'selected' : '' ?>>
                                                    <?= $label ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 mt-4">
                                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                                                Lưu thay đổi
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Change Password Tab -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h5 class="fw-bold mb-4">Đổi mật khẩu</h5>
                                <form action="<?= APP_URL ?>/user/changePassword" method="POST">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label text-muted small fw-bold">Mật khẩu hiện tại</label>
                                            <input type="password" name="current_password" class="form-control rounded-3 p-2" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">Mật khẩu mới</label>
                                            <input type="password" name="new_password" class="form-control rounded-3 p-2" required minlength="6">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">Xác nhận mật khẩu mới</label>
                                            <input type="password" name="confirm_password" class="form-control rounded-3 p-2" required minlength="6">
                                        </div>
                                        <div class="col-12 mt-4">
                                            <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill fw-bold">
                                                Cập nhật mật khẩu
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── SKIN TEST MODAL (Reused) ──────────────────────────────── -->
<div class="modal fade" id="skinTestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden" style="border-radius: 25px;">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-magic me-2"></i>Kiểm Tra Loại Da</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="skinQuizSteps">
                    <div class="quiz-step active" data-step="1">
                        <h6 class="fw-bold mb-3">Câu 1: Buổi sáng thức dậy, da bạn trông như thế nào?</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="oily">A. Bóng dầu khắp mặt</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="dry">B. Khô căng, hơi bong tróc</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="combination">C. Chỉ bóng vùng chữ T</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="normal">D. Mềm mại, thoải mái</button>
                        </div>
                    </div>
                    <div class="quiz-step d-none" data-step="2">
                        <h6 class="fw-bold mb-3">Câu 2: Lỗ chân lông của bạn trông ra sao?</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="oily">A. To và rõ rệt khắp mặt</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="dry">B. Rất nhỏ, khó nhìn thấy</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="combination">C. Chỉ to ở vùng mũi/trán</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="normal">D. Trung bình, không quá rõ</button>
                        </div>
                    </div>
                    <div class="quiz-step d-none" data-step="3">
                        <h6 class="fw-bold mb-3">Câu 3: Da bạn có thường xuyên bị đỏ hoặc châm chích không?</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="sensitive">A. Thường xuyên bị kích ứng</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="none">B. Rất hiếm khi bị kích ứng</button>
                        </div>
                    </div>
                </div>
                <div id="quizResult" class="d-none text-center py-4">
                    <div class="mb-3"><i class="bi bi-check-circle-fill text-success fs-1"></i></div>
                    <h5 class="fw-bold">Kết quả: Da <span id="skinResultName" class="text-primary">...</span></h5>
                    <button type="button" class="btn btn-primary px-5 rounded-pill" data-bs-dismiss="modal">Cập nhật ngay</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(214, 40, 40, 0.2);
}
.profile-header {
    border: 1px solid rgba(0,0,0,0.05);
}
.list-group-item.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
</style>

<script>
// Skin Quiz Logic (Same as home/register)
(function() {
    let scores = { oily: 0, dry: 0, combination: 0, normal: 0, sensitive: 0 };
    let currentStep = 1;

    document.querySelectorAll('#skinTestModal .quiz-opt').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            if (type !== 'none') scores[type]++;

            if (currentStep < 3) {
                document.querySelector(`#skinTestModal .quiz-step[data-step="${currentStep}"]`).classList.add('d-none');
                currentStep++;
                document.querySelector(`#skinTestModal .quiz-step[data-step="${currentStep}"]`).classList.remove('d-none');
            } else {
                finishQuiz();
            }
        });
    });

    function finishQuiz() {
        document.getElementById('skinQuizSteps').classList.add('d-none');
        document.getElementById('quizResult').classList.remove('d-none');

        let result = 'normal';
        if (scores.sensitive > 0) result = 'sensitive';
        else {
            let maxScore = -1;
            for (let type in scores) {
                if (type !== 'sensitive' && scores[type] > maxScore) {
                    maxScore = scores[type];
                    result = type;
                }
            }
        }

        const labels = {'oily': 'Dầu', 'dry': 'Khô', 'combination': 'Hỗn hợp', 'normal': 'Thường', 'sensitive': 'Nhạy cảm'};
        document.getElementById('skinResultName').innerText = labels[result];
        document.getElementById('reg_skin_type').value = result;
    }

    document.getElementById('skinTestModal').addEventListener('hidden.bs.modal', function () {
        scores = { oily: 0, dry: 0, combination: 0, normal: 0, sensitive: 0 };
        currentStep = 1;
        document.getElementById('skinQuizSteps').classList.remove('d-none');
        document.getElementById('quizResult').classList.add('d-none');
        document.querySelectorAll('#skinTestModal .quiz-step').forEach(s => s.classList.add('d-none'));
        document.querySelector('#skinTestModal .quiz-step[data-step="1"]').classList.remove('d-none');
    });
})();
</script>
