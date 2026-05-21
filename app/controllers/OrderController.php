<?php
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/AI/RecommendationEngine.php';

// ============================================================
//  CONTROLLER: OrderController.php
// ============================================================

class OrderController extends Controller {

    public function checkout(): void {
        $this->requireAuth();
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];
        $cartModel = $this->model('Cart');
        $cartData  = $cartModel->getTotal($userId);

        if (empty($cartData['items'])) {
            $this->setFlash('warning', 'Giỏ hàng trống');
            $this->redirect('cart');
        }

        $user = $this->model('User')->findById($userId);
        $this->view('order.checkout', array_merge($cartData, compact('user')));
    }

    public function placeOrder(): void {
        $this->requireAuth();
        if (!$this->isPost()) $this->redirect('cart');

        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];
        $cartModel = $this->model('Cart');
        $cartData  = $cartModel->getTotal($userId);

        if (empty($cartData['items'])) $this->redirect('cart');

        // Validate coupon
        $couponId = null;
        $discount = 0;
        $couponCode = trim($this->post('coupon_code', ''));
        if ($couponCode) {
            $couponModel = $this->model('Coupon');
            $couponResult = $couponModel->validateCode($couponCode, $cartData['total']);
            if ($couponResult['valid']) {
                $couponId = $couponResult['coupon']['id'];
                $discount = $couponResult['discount'];
                $couponModel->use($couponId);
            }
        }

        $shipping = [
            'name'    => trim($this->post('shipping_name', '')),
            'phone'   => trim($this->post('shipping_phone', '')),
            'address' => trim($this->post('shipping_address', '')),
            'note'    => trim($this->post('note', '')),
        ];

        if (empty($shipping['name']) || empty($shipping['phone']) || empty($shipping['address'])) {
            $this->setFlash('error', 'Vui lòng điền đầy đủ thông tin giao hàng');
            $this->redirect('order/checkout');
        }

        $paymentMethod = $this->post('payment_method', 'cod');
        
        $orderModel = $this->model('Order');
        $orderId    = $orderModel->createOrder($userId, $cartData['items'], $shipping, $couponId, $discount, $paymentMethod);

        // Cập nhật stock + behavior tracking
        $productModel = $this->model('Product');
        $engine = new RecommendationEngine($productModel->getDb());
        foreach ($cartData['items'] as $item) {
            $productModel->updateStock((int)$item['product_id'], (int)$item['quantity']);
            $engine->trackBehavior($userId, (int)$item['product_id'], 'purchase');
        }

        $cartModel->clearCart($userId);
        
        // Gửi email xác nhận đặt hàng thành công kèm tư vấn loại da cá nhân hóa
        require_once CORE_PATH . '/MailService.php';
        $user = $this->model('User')->findById($userId);
        MailService::sendOrderConfirmation($orderId, $user, $cartData['items'], $shipping, (float)$discount, $paymentMethod);
        
        if ($paymentMethod === 'online') {
            require_once CORE_PATH . '/Payment/VNPay.php';
            $paymentUrl = VNPay::createPaymentUrl([
                'order_id' => $orderId,
                'amount'   => $cartData['total']
            ]);
            
            if ($paymentUrl) {
                $this->redirect($paymentUrl);
                return;
            } else {
                $this->setFlash('error', 'Không thể tạo liên kết thanh toán VNPay.');
                $this->redirect('order/detail/' . $orderId);
                return;
            }
        }

        $this->setFlash('success', "Đặt hàng thành công! Mã đơn: #{$orderId}");
        $this->redirect('order/history');
    }

    public function history(): void {
        $this->requireAuth();
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $userId   = (int)$userAuth['id'];
        $page      = max(1, (int)$this->get('page', 1));
        $result    = $this->model('Order')->getByUser($userId, $page);
        $flash     = $this->getFlash();
        $this->view('order.history', array_merge($result, compact('flash')));
    }

    public function detail(int $orderId): void {
        $this->requireAuth();
        $order = $this->model('Order')->getDetail($orderId);
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        if (!$order || (int)$order['user_id'] !== (int)$userAuth['id']) {
            $this->redirect('order/history');
        }
        $this->view('order.detail', compact('order'));
    }

    /**
     * Thanh toán lại cho đơn hàng chưa thanh toán
     */
    public function repay(int $orderId): void {
        $this->requireAuth();
        $order = $this->model('Order')->getDetail($orderId);
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        
        if (!$order || (int)$order['user_id'] !== (int)$userAuth['id']) {
            $this->redirect('order/history');
        }

        if ($order['payment_status'] === 'paid') {
            $this->setFlash('info', 'Đơn hàng này đã được thanh toán.');
            $this->redirect('order/detail/' . $orderId);
            return;
        }

        require_once CORE_PATH . '/Payment/VNPay.php';
        $paymentUrl = VNPay::createPaymentUrl([
            'order_id' => $orderId,
            'amount'   => $order['final_amount']
        ]);

        $this->redirect($paymentUrl);
    }

    /**
     * Xử lý kết quả trả về từ VNPay
     */
    public function vnpayReturn(): void {
        require_once CORE_PATH . '/Payment/VNPay.php';
        $isValid = VNPay::validateResponse($_GET);
        
        $vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
        $parts = explode('_', $vnp_TxnRef);
        $orderId = (int)$parts[0];
        
        $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';

        if ($isValid && $vnp_ResponseCode === '00') {
            $this->model('Order')->update($orderId, [
                'payment_status' => 'paid',
                'status' => 'processing' // Chuyển sang trạng thái Đang xử lý để Admin thấy
            ]);
            $this->setFlash('success', 'Thanh toán đơn hàng #' . $orderId . ' thành công!');
        } else {
            $this->setFlash('error', 'Thanh toán không thành công hoặc đã bị hủy.');
        }

        $this->redirect('order/detail/' . $orderId);
    }
}
