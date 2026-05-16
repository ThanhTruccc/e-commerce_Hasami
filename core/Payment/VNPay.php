<?php
// ============================================================
//  CORE/PAYMENT/VNPAY.PHP - Tích hợp cổng thanh toán VNPay (v2.1.0)
// ============================================================

class VNPay {
    
    public static function createPaymentUrl(array $data): string {
        $vnp_TxnRef = $data['order_id'] . '_' . time();
        $vnp_Amount = (int)($data['amount'] * 100);
        $vnp_IpAddr = '127.0.0.1';

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => trim(VNP_TMNCODE),
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan don hang",
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => trim(VNP_RETURNURL),
            "vnp_TxnRef" => $vnp_TxnRef
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = VNP_URL . "?" . $query;
        if (defined('VNP_HASHSECRET')) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, trim(VNP_HASHSECRET));
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        
        return $vnp_Url;
    }

    public static function validateResponse(array $getData): bool {
        $vnp_SecureHash = $getData['vnp_SecureHash'] ?? '';
        
        // Chỉ lấy các tham số bắt đầu bằng vnp_ và loại bỏ SecureHash
        $inputData = [];
        foreach ($getData as $key => $value) {
            if (substr($key, 0, 4) === "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHashType'], $inputData['vnp_SecureHash']);
        
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, trim(VNP_HASHSECRET));
        return $secureHash === $vnp_SecureHash;
    }
}
