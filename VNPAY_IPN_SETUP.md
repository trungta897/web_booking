# 🔔 Hướng dẫn cấu hình IPN URL cho VNPay Sandbox

## 📋 Tóm tắt nhanh

**IPN (Instant Payment Notification)** là webhook mà VNPay gửi về server của bạn để thông báo kết quả thanh toán, đảm bảo hệ thống được cập nhật ngay cả khi user không quay lại website.

## ⚡ Cấu hình nhanh

### 1. Cập nhật file `.env`
```env
VNPAY_TMN_CODE=your_terminal_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://localhost/web_booking/payments/vnpay/return
VNPAY_IPN_URL=http://localhost/web_booking/payments/vnpay/ipn
```

### 2. Cấu hình trong VNPay Portal

#### Truy cập VNPay Sandbox
🌐 https://sandbox.vnpayment.vn/

#### Cấu hình IPN
1. **Menu:** Cấu hình → Cấu hình IPN
2. **IPN URL:** `https://yourdomain.com/payments/vnpay/ipn`
3. **Method:** POST
4. **Status:** Kích hoạt ✅

#### Cấu hình Return URL  
1. **Menu:** Cấu hình → Cấu hình website
2. **Return URL:** `https://yourdomain.com/payments/vnpay/return`

## 🧪 Cho localhost development

### Sử dụng ngrok với WAMP (khuyến nghị)
```bash
# Cài đặt ngrok
npm install -g ngrok

# Tạo tunnel cho WAMP (port 80)
ngrok http 80

# Sử dụng URL ngrok vào VNPay portal
# Ví dụ: https://abc123.ngrok.io/web_booking/payments/vnpay/ipn
```

### URL cấu hình với ngrok cho WAMP
- **Return URL:** `https://abc123.ngrok.io/web_booking/payments/vnpay/return`
- **IPN URL:** `https://abc123.ngrok.io/web_booking/payments/vnpay/ipn`

### 🔧 Script tự động cho WAMP
Chạy file `setup-ngrok-wamp.bat` để tự động kiểm tra và khởi động ngrok.

## ✅ Test IPN hoạt động

### 1. Kiểm tra endpoint
```bash
# Cho WAMP localhost
curl -X POST http://localhost/web_booking/payments/vnpay/ipn \
  -d "vnp_ResponseCode=00&vnp_TxnRef=TEST123"
  
# Hoặc với ngrok URL
curl -X POST https://your-ngrok-url.ngrok.io/web_booking/payments/vnpay/ipn \
  -d "vnp_ResponseCode=00&vnp_TxnRef=TEST123"
```

### 2. Xem log
```bash
tail -f storage/logs/laravel.log | grep "VNPay IPN"
```

### 3. Test thật
1. Tạo thanh toán test qua VNPay demo
2. Hoàn tất thanh toán
3. Kiểm tra log để xem IPN có được gọi

## 🚨 Troubleshooting

### IPN không được gọi
- ✅ Kiểm tra URL đúng format
- ✅ Đảm bảo server accessible từ internet  
- ✅ Firewall/security group mở port 80/443

### Error 500 từ IPN
- ✅ Kiểm tra `storage/logs/laravel.log`
- ✅ Đảm bảo database connection OK
- ✅ File permissions đúng

### Signature verification failed
- ✅ Kiểm tra `VNPAY_HASH_SECRET` chính xác
- ✅ Đảm bảo data encoding đúng

## 📊 Debug IPN

Thêm debug vào `app/Http/Controllers/PaymentController.php`:

```php
public function vnpayIpn(Request $request): Response
{
    // Debug logging
    Log::info('VNPay IPN Debug', [
        'method' => $request->method(),
        'all_data' => $request->all(),
        'headers' => $request->headers->all(),
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    try {
        $this->paymentService->handleVnpayIpn($request->all());
        Log::info('VNPay IPN processed successfully');
        return response('OK', 200);
    } catch (Exception $e) {
        Log::error('VNPay IPN failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response('ERROR', 400);
    }
}
```

## 🎯 Kết quả mong đợi

Khi IPN hoạt động đúng:
- ✅ VNPay gửi POST request tới IPN URL
- ✅ Laravel log ghi nhận "VNPay IPN processed successfully"  
- ✅ Booking status được cập nhật tự động
- ✅ Notifications được gửi tới user

---

💡 **Lưu ý:** IPN URL phải public và accessible từ internet. VNPay sẽ retry gửi IPN nếu nhận được response code khác 200. 
