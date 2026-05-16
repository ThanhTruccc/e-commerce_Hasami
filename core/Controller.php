<?php
// ============================================================
//  CORE/CONTROLLER.PHP - Base Controller
// ============================================================

class Controller {
    public function __construct() {
        // Khởi tạo mặc định
    }

    // ── Render View ──────────────────────────────────────────

    public function view(string $view, array $data = [], bool $withLayout = true): void {
        extract($data);
        $viewFile = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: {$view}");
        }

        if ($withLayout) {
            // Capture view content
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            // Render with layout
            $isAdmin = str_starts_with($view, 'admin.');
            $layout  = $isAdmin
                ? APP_PATH . '/views/layouts/admin_layout.php'
                : APP_PATH . '/views/layouts/main_layout.php';
            require $layout;
        } else {
            require $viewFile;
        }
    }

    // ── Load Model ───────────────────────────────────────────

    public function model(string $model): object {
        $file = APP_PATH . '/models/' . $model . '.php';
        if (!file_exists($file)) die("Model not found: {$model}");
        require_once $file;
        return new $model();
    }

    // ── Redirect ─────────────────────────────────────────────

    public function redirect(string $url): void {
        if (!str_starts_with($url, 'http')) {
            $url = APP_URL . '/' . ltrim($url, '/');
        }
        echo "<script>window.location.href = '$url';</script>";
        exit;
    }

    // ── Session helpers ──────────────────────────────────────

    public function isLoggedIn(): bool {
        return isset($_SESSION['user_auth']);
    }

    public function isAdmin(): bool {
        return isset($_SESSION['admin_auth']);
    }

    public function requireAuth(): void {
        if (!$this->isLoggedIn() && !$this->isAdmin()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
            }
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('auth/login');
        }
    }

    private function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function requireAdmin(): void {
        if (!$this->isAdmin()) {
            $this->redirect('home');
        }
    }

    // ── JSON response ────────────────────────────────────────

    public function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Flash messages ───────────────────────────────────────

    public function setFlash(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    // ── Input helpers ────────────────────────────────────────

    public function post(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $_GET[$key] ?? $default;
    }

    public function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
