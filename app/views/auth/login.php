<!-- app/views/auth/login.php -->
<?php $pageTitle = 'Đăng Nhập | Hasami'; ?>

<section class="auth-section">
    <div class="auth-bg">
        <div class="auth-blob auth-blob-1"></div>
        <div class="auth-blob auth-blob-2"></div>
    </div>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-5 col-lg-4">
                <div class="auth-card">
                    <!-- Logo -->
                    <div class="auth-logo text-center mb-4">
                        <a href="<?= APP_URL ?>" class="brand-logo">
                            <span class="brand-icon">✦</span> Hasami
                        </a>
                    </div>

                    <h2 class="auth-title">Chào mừng trở lại!</h2>
                    <p class="auth-sub">Đăng nhập để tiếp tục mua sắm</p>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger fade-in">
                        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form action="<?= APP_URL ?>/auth/login" method="POST" class="auth-form" id="loginForm">
                        <div class="form-group mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" name="email" class="form-control auth-input" id="loginEmail"
                                       value="<?= htmlspecialchars($email ?? '') ?>"
                                       placeholder="email@example.com" required autofocus>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label d-flex justify-content-between">
                                Mật khẩu
                                <a href="#" class="forgot-link">Quên mật khẩu?</a>
                            </label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" name="password" class="form-control auth-input" id="loginPassword"
                                       placeholder="••••••••" required>
                                <button type="button" class="toggle-pw" onclick="togglePw('loginPassword', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Ghi nhớ đăng nhập</label>
                        </div>
                        <button type="submit" class="btn btn-auth w-100" id="btnLogin">
                            <span class="btn-text">Đăng nhập</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="auth-divider"><span>hoặc</span></div>

                    <div class="text-center">
                        <p class="mb-0 text-muted">Chưa có tài khoản?
                            <a href="<?= APP_URL ?>/auth/register" class="auth-link">Đăng ký ngay</a>
                        </p>
                    </div>

                    <!-- Demo accounts -->
                    <div class="demo-accounts mt-4">
                        <p class="demo-label">Tài khoản demo:</p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-demo" onclick="fillDemo('admin@hasami.vn', 'password')">
                                <i class="bi bi-person-fill-gear me-1"></i>Admin
                            </button>
                            <button class="btn btn-demo" onclick="fillDemo('lan@gmail.com', 'password')">
                                <i class="bi bi-person me-1"></i>User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}
function fillDemo(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pass;
}
</script>

<!-- app/views/auth/register.php - Nhúng cùng file để tiết kiệm -->
