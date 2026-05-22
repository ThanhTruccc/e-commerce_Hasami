<?php
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/AI/RecommendationEngine.php';

class WishlistController extends Controller {

    public function toggle(): void {
        $this->requireAuth();
        $productId = (int)$this->post('product_id', 0);
        $userAuth  = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId    = (int)($userAuth['id'] ?? 0);
        
        $action    = $this->model('Wishlist')->toggle($userId, $productId);
        $count     = $this->model('Wishlist')->getCount($userId);
        
        // Track the behavior for AI Recommendation Engine if added
        if ($action === 'added') {
            $productModel = $this->model('Product');
            $engine = new RecommendationEngine($productModel->getDb());
            $engine->trackBehavior($userId, $productId, 'wishlist');
        }

        $this->json(['success' => true, 'action' => $action, 'count' => $count]);
    }

    public function index(): void {
        $this->requireAuth();
        $userAuth  = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId    = (int)($userAuth['id'] ?? 0);
        $products = $this->model('Wishlist')->getByUser($userId);
        $this->view('wishlist.index', compact('products'));
    }
}
