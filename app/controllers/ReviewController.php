<?php
require_once CORE_PATH . '/Controller.php';

class ReviewController extends Controller {

    public function add(int $productId): void {
        $this->requireAuth();
        if (!$this->isPost()) $this->redirect("product/detail/{$productId}");

        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];
        $result = $this->model('Review')->addReview($userId, $productId, [
            'rating'  => (int)$this->post('rating', 5),
            'title'   => $this->post('title', ''),
            'comment' => $this->post('comment', ''),
        ]);

        $this->setFlash($result ? 'success' : 'error',
            $result ? 'Cảm ơn đánh giá của bạn!' : 'Bạn cần mua sản phẩm để đánh giá.');
        $this->redirect("product/detail/{$productId}");
    }
}
