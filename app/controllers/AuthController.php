<?php
require_once CORE_PATH . '/Controller.php';

// ============================================================
//  CONTROLLER: AuthController.php
// ============================================================

class AuthController extends Controller {

    public function login(): void {
        if ($this->isLoggedIn()) $this->redirect('home');

        if ($this->isPost()) {
            $email    = trim($this->post('email', ''));
            $password = $this->post('password', '');

            $userModel = $this->model('User');
            $user      = $userModel->login($email, $password);

            if ($user) {
                session_regenerate_id(true);
                $sessionKey = ($user['role'] === 'admin') ? 'admin_auth' : 'user_auth';
                
                $_SESSION[$sessionKey] = [
                    'id'        => $user['id'],
                    'name'      => $user['name'],
                    'role'      => $user['role'],
                    'skin_type' => $user['skin_type']
                ];

                $redirect = $_SESSION['redirect_after_login'] ?? null;
                unset($_SESSION['redirect_after_login']);

                $this->setFlash('success', 'Chào mừng trở lại, ' . $user['name'] . '!');
                $this->redirect($user['role'] === 'admin' ? 'admin' : ($redirect ?: 'home'));
            } else {
                $error = 'Email hoặc mật khẩu không đúng';
                $this->view('auth.login', compact('error', 'email'), true);
            }
        } else {
            $this->view('auth.login');
        }
    }

    public function register(): void {
        if ($this->isLoggedIn()) $this->redirect('home');

        if ($this->isPost()) {
            $data = [
                'name'      => trim($this->post('name', '')),
                'email'     => trim($this->post('email', '')),
                'password'  => $this->post('password', ''),
                'phone'     => trim($this->post('phone', '')),
                'skin_type' => $this->post('skin_type', null),
            ];

            $errors = $this->validateRegister($data);
            if (!empty($errors)) {
                $this->view('auth.register', compact('errors', 'data'));
                return;
            }

            $userModel = $this->model('User');
            $userId    = $userModel->register($data);

            if ($userId === false) {
                $errors['email'] = 'Email đã được sử dụng';
                $this->view('auth.register', compact('errors', 'data'));
                return;
            }

            $this->setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
            $this->redirect('auth/login');
        } else {
            $this->view('auth.register', ['skinTypes' => SKIN_TYPES]);
        }
    }

    public function logout(): void {
        unset($_SESSION['user_auth']);
        unset($_SESSION['admin_auth']);
        session_destroy();
        $this->redirect('home');
    }

    private function validateRegister(array $data): array {
        $errors = [];
        if (empty($data['name']))                          $errors['name']     = 'Vui lòng nhập họ tên';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
        if (strlen($data['password']) < 6)                 $errors['password'] = 'Mật khẩu tối thiểu 6 ký tự';
        return $errors;
    }
}
