<?php
// ============================================================
//  CORE/MAILSERVICE.PHP
//  Dịch vụ gửi email thông báo đặt hàng cao cấp của Hasami
//  Tự động gửi email thật và lưu bản xem trước HTML tại thư mục public/emails
// ============================================================

class MailService {

    /**
     * Gửi email xác nhận đặt hàng thành công
     * 
     * @param int $orderId
     * @param array $user
     * @param array $items
     * @param array $shipping
     * @param float $discount
     * @param string $paymentMethod
     * @return bool
     */
    public static function sendOrderConfirmation(
        int $orderId, 
        array $user, 
        array $items, 
        array $shipping, 
        float $discount, 
        string $paymentMethod
    ): bool {
        $to = $user['email'];
        $subject = "✦ Hasami - Xác nhận đơn hàng thành công #" . $orderId;
        
        // 1. Phân tích loại da để đưa ra lời khuyên cá nhân hóa trong email
        $skinTypeKey = $user['skin_type'] ?? 'normal';
        $skinTypeLabel = SKIN_TYPES[$skinTypeKey] ?? 'Da thường';
        $skincareAdvice = self::getPersonalizedSkincareAdvice($skinTypeKey);

        // 2. Tính toán các chi phí đơn hàng
        $subtotal = 0;
        $itemsHtml = "";
        foreach ($items as $item) {
            $price = (float)($item['sale_price'] ?? $item['price']);
            $itemSubtotal = $price * (int)$item['quantity'];
            $subtotal += $itemSubtotal;
            
            $priceText = number_format($price, 0, ',', '.') . 'đ';
            $subtotalText = number_format($itemSubtotal, 0, ',', '.') . 'đ';
            
            $itemsHtml .= "
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #E5E7EB; font-family: sans-serif; font-size: 14px;'>
                    <strong style='color: #1F2937;'>{$item['name']}</strong><br>
                    <span style='font-size: 12px; color: #6B7280;'>Hãng: {$item['brand']}</span>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #E5E7EB; text-align: center; font-family: sans-serif; font-size: 14px; color: #4B5563;'>
                    {$item['quantity']}
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #E5E7EB; text-align: right; font-family: sans-serif; font-size: 14px; color: #4B5563;'>
                    {$priceText}
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #E5E7EB; text-align: right; font-family: sans-serif; font-size: 14px; font-weight: bold; color: #111827;'>
                    {$subtotalText}
                </td>
            </tr>";
        }

        $finalAmount = $subtotal - $discount;
        $paymentText = $paymentMethod === 'online' ? 'Thanh toán trực tuyến (VNPay)' : 'Thanh toán khi nhận hàng (COD)';

        // 3. Tạo mẫu thiết kế HTML Email Premium (Tone màu hồng dịu/nude ấm áp của Hasami)
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$subject}</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #F9FAFB; font-family: \"Segoe UI\", Helvetica, Arial, sans-serif;'>
            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #F9FAFB; padding: 20px 0;'>
                <tr>
                    <td align='center'>
                        <table width='600' border='0' cellspacing='0' cellpadding='0' style='background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                            
                            <!-- HEADER -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #FFCCD8 0%, #FFB6C1 100%); padding: 35px 30px; text-align: center;'>
                                    <div style='font-size: 28px; font-weight: 800; color: #4A1E26; letter-spacing: 2px;'>✦ HASAMI</div>
                                    <div style='font-size: 13px; color: #6A2C37; margin-top: 5px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px;'>Vẻ đẹp tự nhiên — Glow từ trong ra ngoài</div>
                                </td>
                            </tr>
                            
                            <!-- WELCOME -->
                            <tr>
                                <td style='padding: 30px 30px 20px 30px;'>
                                    <h2 style='color: #111827; margin: 0 0 10px 0; font-size: 20px;'>Chào {$user['name']},</h2>
                                    <p style='color: #4B5563; font-size: 15px; line-height: 1.6; margin: 0;'>
                                        Chúc mừng bạn đã đặt hàng thành công tại **Hasami**! Lựa chọn thông thái này là bước tiến tuyệt vời trên hành trình nâng niu và phục hồi làn da của bạn. Đơn hàng của bạn đã được tiếp nhận và đang trong quá trình chuẩn bị để giao tới bạn nhanh nhất.
                                    </p>
                                </td>
                            </tr>

                            <!-- ORDER DETAILS CARD -->
                            <tr>
                                <td style='padding: 0 30px;'>
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #FFF5F7; border: 1px solid #FFE4E8; border-radius: 12px; padding: 20px;'>
                                        <tr>
                                            <td style='font-size: 14px; color: #4B5563; padding-bottom: 8px;'>
                                                <strong>Mã đơn hàng:</strong> <span style='color: #E05275; font-weight: bold;'>#{$orderId}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='font-size: 14px; color: #4B5563; padding-bottom: 8px;'>
                                                <strong>Phương thức thanh toán:</strong> {$paymentText}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='font-size: 14px; color: #4B5563;'>
                                                <strong>Tình trạng thanh toán:</strong> " . ($paymentMethod === 'online' ? '<span style="color: #059669; font-weight: bold;">Đang xử lý / Đã liên kết VNPay</span>' : '<span style="color: #D97706; font-weight: bold;">Thanh toán khi nhận hàng (COD)</span>') . "
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- PRODUCTS TABLE -->
                            <tr>
                                <td style='padding: 30px 30px 20px 30px;'>
                                    <h3 style='color: #111827; font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #FFE4E8; padding-bottom: 8px;'>Chi Tiết Đơn Hàng</h3>
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-collapse: collapse;'>
                                        <thead>
                                            <tr style='background-color: #F9FAFB;'>
                                                <th style='text-align: left; padding: 10px 12px; color: #374151; font-size: 13px; font-weight: bold;'>Sản phẩm</th>
                                                <th style='text-align: center; padding: 10px 12px; color: #374151; font-size: 13px; font-weight: bold;'>SL</th>
                                                <th style='text-align: right; padding: 10px 12px; color: #374151; font-size: 13px; font-weight: bold;'>Đơn giá</th>
                                                <th style='text-align: right; padding: 10px 12px; color: #374151; font-size: 13px; font-weight: bold;'>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {$itemsHtml}
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- PRICING SUMMARY -->
                            <tr>
                                <td style='padding: 0 30px 30px 30px;'>
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                        <tr>
                                            <td width='60%'>&nbsp;</td>
                                            <td width='40%'>
                                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                    <tr>
                                                        <td style='padding: 6px 0; font-size: 14px; color: #6B7280; text-align: left;'>Tạm tính:</td>
                                                        <td style='padding: 6px 0; font-size: 14px; color: #374151; text-align: right;'>" . number_format($subtotal, 0, ',', '.') . "đ</td>
                                                    </tr>
                                                    " . ($discount > 0 ? "
                                                    <tr>
                                                        <td style='padding: 6px 0; font-size: 14px; color: #D97706; text-align: left;'>Giảm giá:</td>
                                                        <td style='padding: 6px 0; font-size: 14px; color: #D97706; text-align: right;'>-" . number_format($discount, 0, ',', '.') . "đ</td>
                                                    </tr>" : "") . "
                                                    <tr>
                                                        <td style='padding: 10px 0 0 0; font-size: 16px; font-weight: bold; color: #111827; text-align: left; border-top: 1px solid #E5E7EB;'>Tổng cộng:</td>
                                                        <td style='padding: 10px 0 0 0; font-size: 18px; font-weight: bold; color: #E05275; text-align: right; border-top: 1px solid #E5E7EB;'>" . number_format($finalAmount, 0, ',', '.') . "đ</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- SHIPPING INFO -->
                            <tr>
                                <td style='padding: 0 30px 20px 30px;'>
                                    <h3 style='color: #111827; font-size: 16px; margin: 0 0 12px 0; border-bottom: 2px solid #FFE4E8; padding-bottom: 8px;'>Thông Tin Giao Hàng</h3>
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='font-size: 14px; color: #4B5563; line-height: 1.6;'>
                                        <tr>
                                            <td style='padding: 4px 0;'><strong>Người nhận:</strong> {$shipping['name']}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 4px 0;'><strong>Số điện thoại:</strong> {$shipping['phone']}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 4px 0;'><strong>Địa chỉ nhận:</strong> {$shipping['address']}</td>
                                        </tr>
                                        " . (!empty($shipping['note']) ? "
                                        <tr>
                                            <td style='padding: 4px 0;'><strong>Ghi chú:</strong> <em>{$shipping['note']}</em></td>
                                        </tr>" : "") . "
                                    </table>
                                </td>
                            </tr>

                            <!-- AI PERSONALIZED SKINCARE ADVICE (Đột phá công nghệ) -->
                            <tr>
                                <td style='padding: 20px 30px;'>
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background: linear-gradient(135deg, #FFF0F5 0%, #FFE4E1 100%); border-left: 5px solid #FF69B4; border-radius: 8px; padding: 20px;'>
                                        <tr>
                                            <td valign='top' width='35' style='font-size: 24px; line-height: 1;'>🩺</td>
                                            <td valign='top'>
                                                <h4 style='margin: 0 0 6px 0; color: #A2143B; font-size: 15px; font-weight: bold;'>Lời Khuyên Của Bác Sĩ Hasami (Dành cho: {$skinTypeLabel})</h4>
                                                <p style='margin: 0; color: #691B31; font-size: 13.5px; line-height: 1.6;'>
                                                    {$skincareAdvice}
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- FOOTER INFO -->
                            <tr>
                                <td style='background-color: #F3F4F6; padding: 30px; text-align: center; border-top: 1px solid #E5E7EB;'>
                                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #4B5563; font-weight: bold;'>Hasami Skincare Brand</p>
                                    <p style='margin: 0 0 15px 0; font-size: 12px; color: #6B7280; line-height: 1.5;'>
                                        Địa chỉ: 123 Đường Nguyễn Trãi, Quận 1, TP. Hồ Chí Minh<br>
                                        Hotline hỗ trợ khách hàng: 1900 888 999 - Email: support@hasami.vn
                                    </p>
                                    <div style='margin-top: 15px;'>
                                        <a href='" . APP_URL . "' style='display: inline-block; padding: 10px 20px; background-color: #E05275; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: bold; border-radius: 20px; box-shadow: 0 2px 5px rgba(224,82,117,0.3);'>Ghé thăm Cửa hàng Hasami</a>
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        // 4. Lưu một bản HTML sao lưu ra thư mục public/emails để Admin hoặc User dễ dàng click xem lại (Demo & QC cực tốt)
        $publicEmailsDir = APP_PATH . '/../public/emails';
        if (!is_dir($publicEmailsDir)) {
            mkdir($publicEmailsDir, 0777, true);
        }
        $filePath = $publicEmailsDir . "/order_" . $orderId . ".html";
        file_put_contents($filePath, $htmlContent);

        // 5. Thiết lập headers và gọi hàm mail() thật của PHP
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Hasami Skincare <sales@hasami.vn>" . "\r\n";
        
        try {
            // Sử dụng dấu tắt lỗi @ để tránh báo lỗi trên Localhost khi không cài SMTP
            @mail($to, $subject, $htmlContent, $headers);
        } catch (Exception $e) {
            // Ghi nhận lỗi nhưng không làm sập tiến trình mua hàng
            error_log("Failed to send order email for order #" . $orderId . ": " . $e->getMessage());
        }

        return true;
    }

    /**
     * Lấy tư vấn skincare cá nhân hóa theo loại da dựa trên phác đồ Hasami
     */
    private static function getPersonalizedSkincareAdvice(string $skinType): string {
        switch ($skinType) {
            case 'oily':
                return "Làn da dầu của bạn có tuyến bã nhờn hoạt động mạnh mẽ, dễ gây mụn và bít tắc lỗ chân lông. Hãy đặc biệt ưu tiên làm sạch sâu bằng gel rửa mặt dịu nhẹ, kết hợp tẩy tế bào chết hóa học chứa **BHA 2%** để làm sạch sâu trong thành lỗ chân lông. Đừng quên cấp nước mỏng nhẹ bằng kem dưỡng dạng gel (như Neutrogena Hydro Boost) để da không bị mất nước gây đổ dầu bù nhé!";
            case 'dry':
                return "Làn da khô đang thiếu hụt độ ẩm tự nhiên và có hàng rào lipid bảo vệ khá yếu, dẫn đến bong tróc hoặc lão hóa nhanh. Bác sĩ khuyên bạn hãy lựa chọn các dòng sữa rửa mặt dưỡng ẩm dịu nhẹ (như CeraVe Hydrating), bổ sung thêm các thành phần khóa ẩm sâu chứa **Ceramides**, **Hyaluronic Acid**, và dùng kem dưỡng ẩm kết cấu đặc hơn để duy trì làn da căng mướt suốt cả ngày.";
            case 'combination':
                return "Da hỗn hợp có đặc điểm dầu vùng chữ T và khô/thường ở hai bên má. Bạn nên thiết lập quy trình chăm sóc đa vùng (Multi-masking/Multi-treatment). Tập trung kiềm dầu nhẹ nhàng bằng nước cân bằng dịu nhẹ ở vùng trán, mũi và dưỡng ẩm sâu hơn ở vùng má bằng kem dưỡng có kết cấu trung bình. Sử dụng các hoạt chất toàn năng như **Niacinamide** để cân bằng sắc tố và điều tiết dầu hiệu quả.";
            case 'sensitive':
                return "Da nhạy cảm cực kỳ dễ kích ứng, mẩn đỏ khi gặp thời tiết xấu hoặc sản phẩm lạ. Nguyên tắc cốt lõi của bạn là 'Tối giản và An toàn'. Tránh tuyệt đối các sản phẩm chứa cồn khô, hương liệu nhân tạo hoặc chất tẩy rửa mạnh. Hãy tập trung củng cố hàng rào da bằng các sản phẩm giàu dưỡng chất phục hồi như **Centella (Rau má)**, **B5**, và luôn bôi kem chống nắng vật lý mỏng nhẹ mỗi khi ra ngoài.";
            case 'normal':
            default:
                return "Xin chúc mừng, bạn đang sở hữu làn da thường lý tưởng nhất với sự cân bằng tuyệt vời giữa dầu và nước! Để duy trì trạng thái hoàn hảo này, bạn chỉ cần một liệu trình cơ bản: Làm sạch dịu nhẹ hàng ngày, bổ sung **Vitamin C** buổi sáng để tăng sinh collagen, chống oxy hóa, dưỡng ẩm đầy đủ ban đêm, và bảo vệ da tuyệt đối bằng kem chống nắng phổ rộng nhé!";
        }
    }
}
