<?php
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/AI/RecommendationEngine.php';

// ============================================================
//  CONTROLLER: HomeController.php
// ============================================================

class HomeController extends Controller {

    public function index(): void {
        $productModel  = $this->model('Product');
        $categoryModel = $this->model('Category');

        // AI Recommendations
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)($userAuth['id'] ?? 0);
        $filters     = $_SESSION['ai_filters'] ?? [];
        $engine      = new RecommendationEngine($productModel->getDb());
        $recommended = $engine->getRecommendations($userId, 0, $filters);

        $featured   = $productModel->getFeatured(8);
        $categories = $categoryModel->getWithProductCount();
        $brands     = $productModel->getBrands();

        $this->view('home.index', compact('recommended', 'featured', 'categories', 'brands'));
    }

    // API endpoint cho AI filter (AJAX)
    public function aiFilter(): void {
        if ($this->isPost()) {
            $_SESSION['ai_filters'] = [
                'skin_type'  => $this->post('skin_type'),
                'price_min'  => (float)$this->post('price_min', 0),
                'price_max'  => (float)$this->post('price_max', 10000000),
                'category_id'=> (int)$this->post('category_id', 0),
            ];

            $productModel = $this->model('Product');
            $engine       = new RecommendationEngine($productModel->getDb());
            
            $userAuth     = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
            $userId       = (int)($userAuth['id'] ?? 0);
            
            $results      = $engine->getRecommendations($userId, 0, $_SESSION['ai_filters']);

            $this->json(['success' => true, 'count' => count($results), 'products' => $results]);
        }
    }
}
