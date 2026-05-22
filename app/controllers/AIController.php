<?php
// ============================================================
//  CONTROLLER: AIController.php
// ============================================================

require_once CORE_PATH . '/Controller.php';

class AIController extends Controller {

    private function ensureTableExists($db) {
        $db->exec("CREATE TABLE IF NOT EXISTS ai_chat_history (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id     INT UNSIGNED NULL,
            session_id  VARCHAR(64) NULL,
            sender      ENUM('user', 'bot') NOT NULL,
            message     TEXT NOT NULL,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_session (session_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function history() {
        header('Content-Type: application/json; charset=utf-8');
        
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $db = $this->model('Product')->getDb();
        
        $this->ensureTableExists($db);
        
        if ($userAuth) {
            $userId = (int)$userAuth['id'];
            $stmt = $db->prepare("SELECT sender, message FROM ai_chat_history WHERE user_id = :user_id ORDER BY id ASC LIMIT 50");
            $stmt->execute([':user_id' => $userId]);
        } else {
            $sessionId = session_id();
            $stmt = $db->prepare("SELECT sender, message FROM ai_chat_history WHERE session_id = :session_id ORDER BY id ASC LIMIT 50");
            $stmt->execute([':session_id' => $sessionId]);
        }
        
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'history' => $history
        ], JSON_UNESCAPED_UNICODE);
    }

    public function ask() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Chỉ nhận yêu cầu POST JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $userMsg = $input['message'] ?? '';

        if (empty($userMsg)) {
            echo json_encode(['status' => 'error', 'message' => 'Empty message']);
            return;
        }

        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        $db = $this->model('Product')->getDb();
        
        // Đảm bảo bảng lịch sử tồn tại
        $this->ensureTableExists($db);
        
        // Lưu tin nhắn của người dùng vào lịch sử
        $userId = $userAuth ? (int)$userAuth['id'] : null;
        $sessionId = $userAuth ? null : session_id();
        
        $stmt = $db->prepare("INSERT INTO ai_chat_history (user_id, session_id, sender, message) VALUES (:user_id, :session_id, 'user', :message)");
        $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':message' => $userMsg
        ]);

        // ── 1. Lấy ngữ cảnh người dùng ────────────────────────
        $userInfo = $this->getUserContext();
        
        // ── 2. Xử lý logic AI (Giả lập thông minh) ─────────────
        $reply = $this->generateAIResponse($userMsg, $userInfo);

        // Lưu phản hồi của AI vào lịch sử
        $stmt = $db->prepare("INSERT INTO ai_chat_history (user_id, session_id, sender, message) VALUES (:user_id, :session_id, 'bot', :message)");
        $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':message' => $reply
        ]);

        echo json_encode([
            'status' => 'success',
            'reply'  => $reply
        ], JSON_UNESCAPED_UNICODE);
    }

    private function getUserContext() {
        $userAuth = $_SESSION['user_auth'] ?? $_SESSION['admin_auth'] ?? null;
        
        // 1. Lấy thông tin người dùng và hoạt động hiện tại
        $userContextStr = "";
        if ($userAuth) {
            $userId    = (int)$userAuth['id'];
            $userModel = $this->model('User');
            $user      = $userModel->findById($userId);
            $skinType  = SKIN_TYPES[$user['skin_type']] ?? 'Chưa xác định';

            // Lấy thông tin giỏ hàng
            $cartModel = $this->model('Cart');
            $cartData  = $cartModel->getTotal($userId);
            $cartSummary = $cartData['count'] > 0 
                ? "Đang có {$cartData['count']} sản phẩm trong giỏ hàng (Tổng trị giá: " . number_format($cartData['total'], 0, ',', '.') . "đ). "
                : "Giỏ hàng hiện tại đang trống. ";

            // Lấy đơn hàng gần đây
            $orderModel = $this->model('Order');
            $recentOrders = $orderModel->getByUser($userId, 1); // Lấy trang 1
            $orderSummary = "";
            if ($recentOrders['total'] > 0) {
                $latest = $recentOrders['data'][0];
                $statusLabels = ORDER_STATUS;
                $status = $statusLabels[$latest['status']] ?? $latest['status'];
                $orderSummary = "Đơn hàng gần đây nhất có mã số #{$latest['id']} đang ở trạng thái: '{$status}'.";
            } else {
                $orderSummary = "Chưa thực hiện đơn hàng nào trên hệ thống.";
            }
            
            $userContextStr = "THÔNG TIN KHÁCH HÀNG:\n- Tên khách hàng: {$user['name']}\n- Loại da đã được xác định qua bài kiểm tra: {$skinType}\n- Trạng thái giỏ hàng: {$cartSummary}\n- Trạng thái đơn hàng: {$orderSummary}";
        } else {
            $userContextStr = "THÔNG TIN KHÁCH HÀNG:\n- Khách vãng lai (Chưa đăng nhập tài khoản hệ thống).";
        }

        // 2. Lấy danh mục sản phẩm (rút gọn để tiết kiệm token)
        $productModel = $this->model('Product');
        $db = $productModel->getDb();
        $stmt = $db->query("SELECT id, name, brand, price, sale_price, skin_types, ingredients FROM products WHERE status = 'active'");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $catalogStr = "\nSẢN PHẨM HASAMI:\n";
        foreach ($products as $p) {
            $skins = implode(',', json_decode($p['skin_types'] ?? '[]', true));
            $price = !empty($p['sale_price']) ? number_format($p['sale_price'],0,',','.') . 'đ(KM)' : number_format($p['price'],0,',','.') . 'đ';
            $catalogStr .= "#{$p['id']} {$p['name']}|{$p['brand']}|{$price}|Da:{$skins}|TC:{$p['ingredients']}\n";
        }

        return $userContextStr . "\n" . $catalogStr;
    }

    private function generateAIResponse($msg, $context) {
        $apiKey = GEMINI_API_KEY;

        $models = [
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-2.0-flash',
            'gemini-1.5-flash'
        ];

        // System Prompt rút gọn để tiết kiệm token
        $systemPrompt = "Bạn là Bác sĩ Da liễu tư vấn cho cửa hàng mỹ phẩm Hasami. Trả lời ngắn gọn, khoa học, dùng Markdown.

Dữ liệu:
$context

Quy tắc:
- Xưng 'Bác sĩ', gọi khách 'Bạn'. Giọng ân cần, chuyên nghiệp.
- CHỈ gợi ý sản phẩm CÓ TRONG danh mục trên. KHÔNG bịa sản phẩm.
- Khi gợi ý: nêu tên, giá, phân tích hoạt chất phù hợp loại da.
- Hỗ trợ giỏ hàng/đơn hàng dựa trên context.
- Trả lời 2-3 đoạn: Phân tích da → Gợi ý sản phẩm → Hướng dẫn sử dụng.";

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $systemPrompt . "\n\nKhách hàng hỏi: " . $msg]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 1500
            ]
        ];

        $payload = json_encode($data);

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // set timeout for each try

            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                // If there's a network/timeout error with this model, try the next one
                continue;
            }

            $result = json_decode($response, true);
            
            // Check if there is an error in response (e.g. key expired, quota limit, model overloaded)
            if (isset($result['error'])) {
                continue;
            }

            $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($reply) {
                return $reply;
            }
        }

        // Nếu tất cả các mô hình đều thất bại hoặc có lỗi hệ thống
        return "Chào bạn, Bác sĩ đã nhận được câu hỏi từ bạn. Hiện tại hệ thống phân tích da đang quá tải. Với làn da của bạn, lời khuyên chung là hãy chú trọng làm sạch dịu nhẹ và duy trì độ ẩm đầy đủ hằng ngày để củng cố hàng rào bảo vệ da nhé.";
    }
}

