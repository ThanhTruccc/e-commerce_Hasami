<?php
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/AI/RecommendationEngine.php';

// ============================================================
//  CONTROLLER: CartController.php
// ============================================================

class CartController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];
        $cartModel = $this->model('Cart');
        $cartData  = $cartModel->getTotal($userId);
        $flash     = $this->getFlash();
        $this->view('cart.index', array_merge($cartData, compact('flash')));
    }

    public function add(): void {
        $this->requireAuth();
        
        // Cố gắng lấy ID từ nhiều nguồn (Post, Get, hoặc JSON)
        $productId = (int)$this->post('product_id', 0);
        if ($productId === 0) $productId = (int)$this->get('product_id', 0);
        
        $quantity  = max(1, (int)$this->post('quantity', 1));
        $userAuth  = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId    = (int)$userAuth['id'];

        // Kiểm tra stock
        $productModel = $this->model('Product');
        $product      = $productModel->findById($productId);

        if (!$product) {
            $this->json(['success' => false, 'message' => "Không tìm thấy sản phẩm (ID: $productId)"], 400);
        }
        if ($product['stock'] < $quantity) {
            $this->json(['success' => false, 'message' => "Sản phẩm không đủ hàng (Kho: {$product['stock']}, Cần: $quantity)"], 400);
        }

        $this->model('Cart')->addOrUpdate($userId, $productId, $quantity);
        $count = $this->model('Cart')->getItemCount($userId);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'count' => $count, 'message' => 'Đã thêm vào giỏ hàng']);
        } else {
            $this->setFlash('success', 'Đã thêm vào giỏ hàng!');
            $this->redirect('cart');
        }
    }

    public function update(): void {
        $this->requireAuth();
        $productId = (int)$this->post('product_id', 0);
        $quantity  = (int)$this->post('quantity', 1);
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];

        $this->model('Cart')->updateQty($userId, $productId, $quantity);

        if ($this->isAjax()) {
            $cartData = $this->model('Cart')->getTotal($userId);
            $this->json(['success' => true, 'total' => $cartData['total'], 'count' => $cartData['count']]);
        } else {
            $this->redirect('cart');
        }
    }

    public function remove(): void {
        $this->requireAuth();
        $productId = (int)$this->post('product_id', 0);
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];
        $this->model('Cart')->removeItem($userId, $productId);

        if ($this->isAjax()) {
            $cartData = $this->model('Cart')->getTotal($userId);
            $this->json(['success' => true, 'total' => $cartData['total'], 'count' => $cartData['count']]);
        } else {
            $this->redirect('cart');
        }
    }

    private function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
