<?php
require_once CORE_PATH . '/Controller.php';

// ============================================================
//  CONTROLLER: UserController.php
// ============================================================

class UserController extends Controller {

    public function profile(): void {
        $this->requireAuth();
        
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];
        
        $userModel = $this->model('User');
        $user      = $userModel->findById($userId);
        
        if (!$user) {
            $this->redirect('home');
        }

        $flash = $this->getFlash();
        $this->view('user.profile', compact('user', 'flash'));
    }

    public function updateProfile(): void {
        $this->requireAuth();
        if (!$this->isPost()) $this->redirect('user/profile');

        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];

        $data = [
            'name'      => trim($this->post('name', '')),
            'phone'     => trim($this->post('phone', '')),
            'skin_type' => $this->post('skin_type', 'normal'),
        ];

        // Validation cơ bản
        if (empty($data['name'])) {
            $this->setFlash('error', 'Vui lòng nhập họ tên');
            $this->redirect('user/profile');
        }

        $userModel = $this->model('User');
        if ($userModel->update($userId, $data)) {
            // Cập nhật lại session
            $sessionKey = ($userAuth['role'] === 'admin') ? 'admin_auth' : 'user_auth';
            $_SESSION[$sessionKey]['name'] = $data['name'];
            $_SESSION[$sessionKey]['skin_type'] = $data['skin_type'];

            $this->setFlash('success', 'Cập nhật thông tin thành công!');
        } else {
            $this->setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
        }

        $this->redirect('user/profile');
    }

    public function changePassword(): void {
        $this->requireAuth();
        if (!$this->isPost()) $this->redirect('user/profile');

        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];

        $currentPw = $this->post('current_password', '');
        $newPw     = $this->post('new_password', '');
        $confirmPw = $this->post('confirm_password', '');

        if ($newPw !== $confirmPw) {
            $this->setFlash('error', 'Mật khẩu mới không khớp');
            $this->redirect('user/profile');
        }

        $userModel = $this->model('User');
        $user      = $userModel->findById($userId);

        if ($user && password_verify($currentPw, $user['password'])) {
            $userModel->update($userId, ['password' => password_hash($newPw, PASSWORD_DEFAULT)]);
            $this->setFlash('success', 'Đổi mật khẩu thành công!');
        } else {
            $this->setFlash('error', 'Mật khẩu hiện tại không đúng');
        }

        $this->redirect('user/profile');
    }
}
