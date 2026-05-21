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

        // 2. Lấy toàn bộ danh mục sản phẩm đang hoạt động của hệ thống
        $productModel = $this->model('Product');
        $db = $productModel->getDb();
        $stmt = $db->query("SELECT id, name, brand, price, sale_price, skin_types, description, ingredients, usage_guide FROM products WHERE status = 'active'");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $catalogStr = "\nDANH MỤC SẢN PHẨM HIỆN CÓ TẠI CỬA HÀNG HASAMI:\n";
        foreach ($products as $p) {
            $skins = json_decode($p['skin_types'] ?? '[]', true);
            $skinLabels = [];
            foreach ($skins as $sk) {
                $skinLabels[] = SKIN_TYPES[$sk] ?? $sk;
            }
            $skinsStr = implode(', ', $skinLabels);
            $priceText = number_format($p['price'], 0, ',', '.') . 'đ';
            if (!empty($p['sale_price'])) {
                $priceText = number_format($p['sale_price'], 0, ',', '.') . 'đ (Đang giảm giá từ ' . number_format($p['price'], 0, ',', '.') . 'đ)';
            }
            $catalogStr .= "- **[Sản phẩm ID: {$p['id']}]** {$p['name']} (Thương hiệu: {$p['brand']})\n";
            $catalogStr .= "  + Giá bán: {$priceText}\n";
            $catalogStr .= "  + Loại da phù hợp: {$skinsStr}\n";
            $catalogStr .= "  + Hoạt chất & Thành phần: {$p['ingredients']}\n";
            $catalogStr .= "  + Công dụng & Mô tả: {$p['description']}\n";
            $catalogStr .= "  + Hướng dẫn sử dụng: " . ($p['usage_guide'] ?? 'Thoa đều lên vùng da cần chăm sóc.') . "\n\n";
        }

        return $userContextStr . "\n" . $catalogStr;
    }

    private function generateAIResponse($msg, $context) {
        $apiKey = GEMINI_API_KEY;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        // Xây dựng System Prompt cực kỳ chuyên nghiệp, học thuật và chi tiết
        $systemPrompt = "Bạn là PGS.TS. Bác sĩ Da liễu, chuyên gia tư vấn cao cấp của hệ thống mỹ phẩm thuần Việt Hasami. Bạn có hơn 15 năm kinh nghiệm lâm sàng và nghiên cứu chuyên sâu về các bệnh lý cũng như cơ chế sinh học của làn da người Việt.

Dưới đây là thông tin về khách hàng đang trò chuyện với bạn và danh mục sản phẩm thực tế có sẵn tại cửa hàng Hasami:
==================================
$context
==================================

Hãy tuân thủ nghiêm ngặt các quy tắc ứng xử và nghiệp vụ chuyên gia sau đây:

1. PHONG CÁCH XƯNG HÔ VÀ THÁI ĐỘ:
   - Xưng hô là 'Bác sĩ' hoặc 'Mình', gọi người dùng là 'Bạn' hoặc 'Khách hàng'. Luôn thể hiện thái độ ân cần, lắng nghe, thấu hiểu sâu sắc, và tôn trọng tuyệt đối.
   - Sử dụng ngôn từ tinh tế, khoa học nhưng vẫn dễ hiểu đối với người tiêu dùng phổ thông. Tránh cách nói sáo rỗng hoặc mang tính quảng cáo lộ liễu.

2. TƯ VẤN Y KHOA VÀ CƠ CHẾ SINH HỌC:
   - Khi nhận dạng được loại da hoặc vấn đề da của khách hàng (hoặc dựa vào thông tin loại da trong context nếu có), hãy giải thích ngắn gọn, súc tích về cơ chế sinh lý của loại da đó (ví dụ: tại sao da dầu lại dễ bít tắc lỗ chân lông, tại sao da khô lại thiếu hụt lipid rào cản, v.v.).
   - Khuyên dùng các phác đồ chăm sóc da khoa học gồm các bước cốt lõi: Làm sạch -> Cân bằng -> Điều trị chuyên sâu -> Dưỡng ẩm -> Bảo vệ.

3. GỢI Ý SẢN PHẨM CHÍNH XÁC TỪ CỬA HÀNG:
   - CHỈ ĐƯỢC PHÉP gợi ý các sản phẩm thực tế ĐANG CÓ TRONG DANH MỤC SẢN PHẨM của Hasami (đã cung cấp ở trên). TUYỆT ĐỐI KHÔNG tự bịa ra sản phẩm hoặc giới thiệu các sản phẩm của bên thứ ba không có trong danh mục.
   - Khi gợi ý sản phẩm nào, hãy nêu rõ Tên sản phẩm, Thương hiệu, và phân tích chi tiết vì sao các HOẠT CHẤT (như Salicylic Acid, Niacinamide, Hyaluronic Acid, Ceramide...) có trong sản phẩm đó lại giải quyết được vấn đề da của họ.
   - Ghi rõ Giá bán (hoặc giá khuyến mãi nếu có) và khuyên họ thêm vào giỏ hàng hoặc tiến hành thanh toán nếu sản phẩm đó phù hợp.

4. HỖ TRỢ XỬ LÝ ĐƠN HÀNG VÀ GIỎ HÀNG:
   - Nếu khách hàng hỏi về giỏ hàng hoặc đơn hàng của họ, hãy dựa vào thông tin trong phần context để giải đáp một cách chu đáo, tận tình (ví dụ: xác nhận đơn hàng đang ở trạng thái nào, hỗ trợ khách hàng cách thức thanh toán online qua VNPay hoặc COD).

5. ĐỊNH DẠNG CÂU TRẢ LỜI (MARKDOWN):
   - Trình bày câu trả lời của bạn một cách rõ ràng, trực quan, chuyên nghiệp bằng cách sử dụng các thẻ Markdown: tiêu đề lớn (###), danh sách gạch đầu dòng, chữ in đậm cho các hoạt chất quan trọng.
   - Chia câu trả lời thành 3 phần rõ rệt:
     * Chẩn Đoán & Phân Tích Da (Giải thích cơ chế)
     * Liệu Trình & Gợi Ý Sản Phẩm Phù Hợp (Phân tích hoạt chất, giá bán cụ thể của sản phẩm thuộc Hasami)
     * Hướng Dẫn Sử Dụng & Lời Khuyên Y Khoa (Tần suất, thứ tự bôi, lưu ý khi ra nắng...)";

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

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "Xin lỗi bạn, Bác sĩ đang gặp một chút sự cố về đường truyền kết nối dữ liệu. Bạn vui lòng gửi lại câu hỏi sau ít phút nhé. Chúc bạn một ngày tốt lành!";
        }

        $result = json_decode($response, true);
        $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$reply) {
            return "Chào bạn, Bác sĩ đã nhận được câu hỏi từ bạn. Hiện tại hệ thống phân tích da đang quá tải. Với làn da của bạn, lời khuyên chung là hãy chú trọng làm sạch dịu nhẹ và duy trì độ ẩm đầy đủ hằng ngày để củng cố hàng rào bảo vệ da nhé.";
        }

        return $reply;
    }
}

