<?php
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/AI/RecommendationEngine.php';

// ============================================================
//  CONTROLLER: ProductController.php
// ============================================================

class ProductController extends Controller {

    public function index(): void {
        $productModel  = $this->model('Product');
        $categoryModel = $this->model('Category');

        $params = [
            'search'      => $this->get('q', ''),
            'category_id' => (int)$this->get('category', 0),
            'brand'       => $this->get('brand', ''),
            'skin_type'   => $this->get('skin_type', ''),
            'price_min'   => (float)$this->get('price_min', 0),
            'price_max'   => (float)$this->get('price_max', 0),
            'sort'        => $this->get('sort', 'newest'),
        ];

        $page   = max(1, (int)$this->get('page', 1));
        $result = $productModel->filter($params, $page);

        $categories = $categoryModel->getTree();
        $brands     = $productModel->getBrands();
        $skinTypes  = SKIN_TYPES;
        $flash      = $this->getFlash();

        $this->view('product.list',
            compact('result', 'params', 'categories', 'brands', 'skinTypes', 'flash')
        );
    }

    public function detail(int $id): void {
        $productModel = $this->model('Product');
        $product      = $productModel->getWithCategory($id);

        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại');
            $this->redirect('product');
        }

        // Track hành vi xem
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)($userAuth['id'] ?? 0);
        $engine = new RecommendationEngine($productModel->getDb());
        $engine->trackBehavior($userId, $id, 'view');

        // Gợi ý sản phẩm tương tự (AI)
        $similar = $engine->getSimilarProducts($id, $userId, 4);

        // Đánh giá
        $reviewModel = $this->model('Review');
        $reviews     = $reviewModel->getByProduct($id);
        $canReview   = $userId ? $reviewModel->canReview($userId, $id) : false;

        // Wishlist status
        $isWishlisted = false;
        if ($userId) {
            $isWishlisted = $this->model('Wishlist')->isWishlisted($userId, $id);
        }

        $this->view('product.detail',
            compact('product', 'similar', 'reviews', 'canReview', 'isWishlisted')
        );
    }
}
