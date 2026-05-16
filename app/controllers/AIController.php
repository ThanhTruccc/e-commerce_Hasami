<?php
// ============================================================
//  CONTROLLER: AIController.php
// ============================================================

require_once CORE_PATH . '/Controller.php';

class AIController extends Controller {

    public function chat() {
        // Chỉ nhận yêu cầu POST JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $userMsg = $input['message'] ?? '';

        if (empty($userMsg)) {
            echo json_encode(['status' => 'error', 'message' => 'Empty message']);
            return;
        }

        // ── 1. Lấy ngữ cảnh người dùng ────────────────────────
        $userInfo = $this->getUserContext();
        
        // ── 2. Xử lý logic AI (Giả lập thông minh) ─────────────
        $reply = $this->generateAIResponse($userMsg, $userInfo);

        echo json_encode([
            'status' => 'success',
            'reply'  => $reply
        ]);
    }

    private function getUserContext() {
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        if (!$userAuth) return "Khách vãng lai chưa đăng nhập.";

        $userId    = (int)$userAuth['id'];
        $userModel = $this->model('User');
        $user      = $userModel->findById($userId);
        $skinType  = SKIN_TYPES[$user['skin_type']] ?? 'Chưa xác định';

        // Lấy thông tin giỏ hàng
        $cartModel = $this->model('Cart');
        $cartData  = $cartModel->getTotal($userId);
        $cartSummary = $cartData['count'] > 0 
            ? "Đang có {$cartData['count']} sản phẩm trong giỏ hàng (Tổng: " . number_format($cartData['total'], 0, ',', '.') . "đ). "
            : "Giỏ hàng hiện đang trống. ";

        // Lấy đơn hàng gần đây
        $orderModel = $this->model('Order');
        $recentOrders = $orderModel->getByUser($userId, 1); // Lấy trang 1
        $orderSummary = "";
        if ($recentOrders['total'] > 0) {
            $latest = $recentOrders['data'][0];
            $statusLabels = ORDER_STATUS;
            $status = $statusLabels[$latest['status']] ?? $latest['status'];
            $orderSummary = "Đơn hàng gần nhất mã #{$latest['id']} đang ở trạng thái: {$status}.";
        } else {
            $orderSummary = "Chưa có đơn hàng nào.";
        }
        
        return "Người dùng tên {$user['name']}, da: {$skinType}. {$cartSummary}{$orderSummary}";
    }

    private function generateAIResponse($msg, $context) {
        $apiKey = GEMINI_API_KEY;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        // Xây dựng System Prompt để định hình "tính cách" AI
        $systemPrompt = "Bạn là chuyên gia tư vấn da liễu thông minh của thương hiệu mỹ phẩm Hasami. 
        Thông tin khách hàng hiện tại: $context.
        Nhiệm vụ của bạn:
        1. Trả lời thân thiện, chuyên nghiệp, sử dụng ngôn ngữ tự nhiên.
        2. Ưu tiên giải đáp các thắc mắc về đơn hàng và giỏ hàng nếu khách hàng hỏi.
        3. Tư vấn các sản phẩm Hasami phù hợp với loại da trong context.
        4. Nếu khách hàng hỏi về giá, hãy nhắc họ xem chi tiết trên web.
        5. Luôn xưng hô là 'Mình' và gọi khách hàng là 'Bạn'.";

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $systemPrompt . "\n\nKhách hàng: " . $msg]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 500
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tắt kiểm tra SSL nếu chạy trên localhost

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "Xin lỗi, mình đang gặp chút trục trặc kỹ thuật kết nối AI. Bạn có thể thử lại sau giây lát nhé!";
        }

        $result = json_decode($response, true);
        $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$reply) {
            // Fallback nếu API lỗi hoặc không trả về kết quả
            return "Cảm ơn bạn đã nhắn tin! Hiện tại mình đang bận một chút, nhưng với làn da của bạn, Hasami khuyên bạn nên tập trung vào bước làm sạch và dưỡng ẩm nhé.";
        }

        return $reply;
    }
}
