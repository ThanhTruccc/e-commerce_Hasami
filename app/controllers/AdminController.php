<?php
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/AI/RecommendationEngine.php';

// ============================================================
//  CONTROLLER: AdminController.php
// ============================================================

class AdminController extends Controller {

    public function __construct() {
        // Sẽ được kiểm tra trong từng method
    }

    public function index(): void {
        $this->requireAdmin();

        $orderModel   = $this->model('Order');
        $productModel = $this->model('Product');
        $userModel    = $this->model('User');
        $detailModel  = $this->model('OrderDetail');

        $revenueStats    = $orderModel->getRevenueStats();
        $monthlyRevenue  = $orderModel->getMonthlyRevenue(6);
        $bestSellers     = $detailModel->getBestSellers(5);
        $productStats    = $productModel->getDashboardStats();
        $recentOrders    = $orderModel->getAll(1);

        $this->view('admin.dashboard', compact(
            'revenueStats', 'monthlyRevenue', 'bestSellers',
            'productStats', 'recentOrders'
        ));
    }

    // ── Sản phẩm ─────────────────────────────────────────────

    public function products(): void {
        $this->requireAdmin();
        $page     = max(1, (int)$this->get('page', 1));
        $search   = $this->get('q', '');
        $result   = $this->model('Product')->filter(['search' => $search], $page);
        $categories = $this->model('Category')->findAll('name ASC');
        $flash    = $this->getFlash();
        $this->view('admin.products', compact('result', 'categories', 'search', 'flash'));
    }

    public function productSave(): void {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('admin/products');

        $id   = (int)$this->post('id', 0);
        $data = [
            'category_id'  => (int)$this->post('category_id'),
            'name'         => trim($this->post('name', '')),
            'slug'         => $this->makeSlug($this->post('name', '')),
            'brand'        => trim($this->post('brand', '')),
            'price'        => (float)$this->post('price', 0),
            'sale_price'   => $this->post('sale_price') ?: null,
            'stock'        => (int)$this->post('stock', 0),
            'description'  => $this->post('description', ''),
            'ingredients'  => $this->post('ingredients', ''),
            'skin_types'   => json_encode(array_filter((array)$this->post('skin_types', []))),
            'featured'     => (int)$this->post('featured', 0),
            'status'       => $this->post('status', 'active'),
        ];

        // Upload ảnh
        if (!empty($_FILES['image']['name'])) {
            $data['image'] = $this->uploadImage($_FILES['image']);
        }

        $productModel = $this->model('Product');
        if ($id > 0) {
            $productModel->update($id, $data);
            $this->setFlash('success', 'Cập nhật sản phẩm thành công!');
        } else {
            $productModel->insert($data);
            $this->setFlash('success', 'Thêm sản phẩm thành công!');
        }
        $this->redirect('admin/products');
    }

    public function productDelete(int $id): void {
        $this->requireAdmin();
        $this->model('Product')->delete($id);
        $this->setFlash('success', 'Đã xoá sản phẩm');
        $this->redirect('admin/products');
    }

    // ── Đơn hàng ─────────────────────────────────────────────

    public function orders(): void {
        $this->requireAdmin();
        $page   = max(1, (int)$this->get('page', 1));
        $status = $this->get('status', '');
        $result = $this->model('Order')->getAll($page, $status);
        $statuses = ORDER_STATUS;
        $flash  = $this->getFlash();
        $this->view('admin.orders', compact('result', 'statuses', 'status', 'flash'));
    }

    public function orderStatus(int $id): void {
        $this->requireAdmin();
        $status = $this->post('status', '');
        if (array_key_exists($status, ORDER_STATUS)) {
            $this->model('Order')->updateStatus($id, $status);
            $this->setFlash('success', 'Cập nhật trạng thái thành công');
        }
        $this->redirect('admin/orders');
    }

    public function orderDetail(int $id): void {
        $this->requireAdmin();
        $order = $this->model('Order')->getDetail($id);
        if (!$order) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            $this->redirect('admin/orders');
        }
        
        // Fetch user detail for order
        $user = $this->model('User')->findById((int)$order['user_id']);
        
        $this->view('admin.order_detail', compact('order', 'user'));
    }

    // ── Người dùng ───────────────────────────────────────────

    public function users(): void {
        $this->requireAdmin();
        $page   = max(1, (int)$this->get('page', 1));
        $result = $this->model('User')->getAll($page);
        $this->view('admin.users', compact('result'));
    }

    public function userToggle(int $id): void {
        $this->requireAdmin();
        $user = $this->model('User')->findById($id);
        if ($user) {
            $this->model('User')->update($id, ['is_active' => $user['is_active'] ? 0 : 1]);
        }
        $this->redirect('admin/users');
    }

    // ── Helpers ───────────────────────────────────────────────

    private function makeSlug(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[àáảãạăắặẵẩấầ]/u',  'a', $text);
        $text = preg_replace('/[èéẹẻẽêếềệểễ]/u',   'e', $text);
        $text = preg_replace('/[ìíịỉĩ]/u',           'i', $text);
        $text = preg_replace('/[òóọỏõôốồộổỗơớờợởỡ]/u','o', $text);
        $text = preg_replace('/[ùúụủũưứừựửữ]/u',    'u', $text);
        $text = preg_replace('/[ỳýỵỷỹ]/u',           'y', $text);
        $text = preg_replace('/đ/u',                  'd', $text);
        $text = preg_replace('/[^a-z0-9\s-]/',        '',  $text);
        $text = preg_replace('/[\s-]+/',               '-', trim($text));
        return $text . '-' . time();
    }

    private function uploadImage(array $file): string {
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) return '';

        $filename = uniqid('img_') . '.' . $ext;
        
        // Đảm bảo thư mục tồn tại và có quyền ghi
        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/' . $filename)) {
            return $filename;
        }
        
        return '';
    }
}

// Removed inline ReviewController and WishlistController to their own files.
