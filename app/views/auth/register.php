<!-- app/views/auth/register.php -->
<?php $pageTitle = 'Đăng Ký | Hasami'; ?>

<section class="auth-section">
    <div class="auth-bg">
        <div class="auth-blob auth-blob-1"></div>
        <div class="auth-blob auth-blob-2"></div>
    </div>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="auth-logo text-center mb-4">
                        <a href="<?= APP_URL ?>" class="brand-logo">
                            <span class="brand-icon">✦</span> Hasami
                        </a>
                    </div>
                    <h2 class="auth-title">Tạo Tài Khoản</h2>
                    <p class="auth-sub">Tham gia cộng đồng skincare Hasami</p>

                    <form action="<?= APP_URL ?>/auth/register" method="POST" class="auth-form">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" name="name" class="form-control auth-input <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars($data['name'] ?? '') ?>"
                                           placeholder="Nguyễn Thị Lan" required>
                                </div>
                                <?php if (!empty($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input type="email" name="email" class="form-control auth-input <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                                           placeholder="email@example.com" required>
                                </div>
                                <?php if (!empty($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-lock input-icon"></i>
                                    <input type="password" name="password" class="form-control auth-input <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                                           placeholder="Tối thiểu 6 ký tự" required>
                                    <button type="button" class="toggle-pw" onclick="togglePw('regPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <?php if (!empty($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'] ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-telephone input-icon"></i>
                                    <input type="tel" name="phone" class="form-control auth-input"
                                           value="<?= htmlspecialchars($data['phone'] ?? '') ?>"
                                           placeholder="0901234567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0">Loại da</label>
                                    <a href="#" class="text-primary small fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#skinTestModal">
                                        <i class="bi bi-magic"></i> Test da
                                    </a>
                                </div>
                                <select name="skin_type" id="reg_skin_type" class="form-select auth-input">
                                    <option value="">-- Chọn loại da --</option>
                                    <?php foreach ($skinTypes ?? SKIN_TYPES as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($data['skin_type'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-check mt-3 mb-3">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                Tôi đồng ý với <a href="#" class="auth-link">Điều khoản sử dụng</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-auth w-100">
                            <span>Tạo tài khoản</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="auth-divider"><span>hoặc</span></div>
                    <div class="text-center">
                        <p class="mb-0 text-muted">Đã có tài khoản?
                            <a href="<?= APP_URL ?>/auth/login" class="auth-link">Đăng nhập</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── SKIN TEST MODAL ────────────────────────────────────── -->
<div class="modal fade" id="skinTestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden" style="border-radius: 25px;">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-magic me-2"></i>Kiểm Tra Loại Da Của Bạn</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="skinQuizSteps">
                    <!-- Step 1 -->
                    <div class="quiz-step active" data-step="1">
                        <h6 class="fw-bold mb-3">Câu 1: Buổi sáng thức dậy, da bạn trông như thế nào?</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="oily">A. Bóng dầu khắp mặt</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="dry">B. Khô căng, hơi bong tróc</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="combination">C. Chỉ bóng vùng chữ T (mũi/trán)</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="normal">D. Mềm mại, thoải mái</button>
                        </div>
                    </div>
                    <!-- Step 2 (Hidden) -->
                    <div class="quiz-step d-none" data-step="2">
                        <h6 class="fw-bold mb-3">Câu 2: Lỗ chân lông của bạn trông ra sao?</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="oily">A. To và rõ rệt khắp mặt</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="dry">B. Rất nhỏ, khó nhìn thấy</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="combination">C. Chỉ to ở vùng mũi/trán</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="normal">D. Trung bình, không quá rõ</button>
                        </div>
                    </div>
                    <!-- Step 3 (Hidden) -->
                    <div class="quiz-step d-none" data-step="3">
                        <h6 class="fw-bold mb-3">Câu 3: Da bạn có thường xuyên bị đỏ hoặc châm chích không?</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="sensitive">A. Thường xuyên bị kích ứng</button>
                            <button type="button" class="btn btn-outline-secondary text-start p-3 quiz-opt" data-type="none">B. Rất hiếm khi bị kích ứng</button>
                        </div>
                    </div>
                </div>

                <!-- Result Area (Hidden) -->
                <div id="quizResult" class="d-none text-center py-4">
                    <div class="mb-3"><i class="bi bi-check-circle-fill text-success fs-1"></i></div>
                    <h5 class="fw-bold">Kết quả: Da <span id="skinResultName" class="text-primary">...</span></h5>
                    <p class="text-muted small mb-4">Chúng tôi đã cập nhật loại da này vào đơn đăng ký của bạn!</p>
                    <button type="button" class="btn btn-primary px-5" data-bs-dismiss="modal">Tiếp tục đăng ký</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
        if (scores.sensitive > 0) {
            result = 'sensitive';
        } else {
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

function togglePw(id, btn) {
    const input = btn.previousElementSibling;
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    btn.querySelector('i').classList.toggle('bi-eye');
    btn.querySelector('i').classList.toggle('bi-eye-slash');
}
</script>
