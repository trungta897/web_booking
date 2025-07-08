# 🔔 Hướng dẫn cấu hình VNPay IPN cho Production

## 📋 Tổng quan

VNPay IPN (Instant Payment Notification) là webhook tự động gửi về server khi có giao dịch thành công. Hệ thống đã được tích hợp sẵn, chỉ cần cấu hình đúng URL.

## 🛠️ Bước 1: Cấu hình Environment Variables

Cập nhật file `.env` của bạn:

```env
# VNPay Configuration for Production
APP_URL=https://yourdomain.com
APP_DOMAIN=yourdomain.com
ADMIN_DOMAIN=admin.yourdomain.com

# VNPay Settings
VNPAY_TMN_CODE=your_vnpay_terminal_code
VNPAY_HASH_SECRET=your_vnpay_hash_secret

# Sandbox URLs (cho test)
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=https://yourdomain.com/payments/vnpay/return
VNPAY_IPN_URL=https://yourdomain.com/payments/vnpay/ipn

# Production URLs (khi go-live)
# VNPAY_URL=https://vnpayment.vn/paymentv2/vpcpay.html

# Logging for debugging
LOG_LEVEL=info
```

## 🌐 Bước 2: Cấu hình trong VNPay Portal

### 2.1 Truy cập VNPay Portal

**Sandbox:** https://sandbox.vnpayment.vn/merchant_webapi/merchant.html  
**Production:** https://vnpayment.vn/merchant_webapi/merchant.html

### 2.2 Cấu hình IPN URL

1. **Login** vào VNPay Portal với tài khoản merchant
2. Vào menu **"Cấu hình"** → **"Cấu hình IPN"**
3. Điền thông tin:
   - **IPN URL:** `https://yourdomain.com/payments/vnpay/ipn`
   - **Method:** `POST`
   - **Status:** `Kích hoạt` ✅
4. Nhấn **"Lưu cấu hình"**

### 2.3 Cấu hình Return URL

1. Vào menu **"Cấu hình"** → **"Cấu hình website"**
2. Điền:
   - **Return URL:** `https://yourdomain.com/payments/vnpay/return`
3. Nhấn **"Lưu cấu hình"**

## 🧪 Bước 3: Test cho Development (Localhost)

### 3.1 Sử dụng ngrok (Khuyến nghị)

```bash
# Cài đặt ngrok
npm install -g ngrok

# Tạo tunnel cho Nginx (port 80)
ngrok http 80

# Hoặc cho development server
ngrok http 8000

# Sử dụng URL ngrok trong VNPay portal
# Ví dụ: https://abc123.ngrok.io/payments/vnpay/ipn
```

### 3.2 Cập nhật .env cho development với ngrok

```env
# Development với ngrok
VNPAY_RETURN_URL=https://abc123.ngrok.io/payments/vnpay/return
VNPAY_IPN_URL=https://abc123.ngrok.io/payments/vnpay/ipn
```

### 3.3 Script tự động cho ngrok

Tạo file `start-ngrok.sh`:

```bash
#!/bin/bash
echo "🚀 Starting ngrok tunnel for VNPay IPN testing..."

# Kill existing ngrok
pkill ngrok

# Start ngrok for port 80 (Nginx)
ngrok http 80 --log=stdout > ngrok.log &

sleep 3

# Get public URL
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o '"public_url":"https://[^"]*' | cut -d'"' -f4)

if [ ! -z "$NGROK_URL" ]; then
    echo "✅ Ngrok URL: $NGROK_URL"
    echo "📋 VNPay IPN URL: $NGROK_URL/payments/vnpay/ipn"
    echo "📋 VNPay Return URL: $NGROK_URL/payments/vnpay/return"
    echo ""
    echo "🔧 Cập nhật URLs này trong VNPay Portal!"
else
    echo "❌ Ngrok failed to start"
fi
```

## ✅ Bước 4: Test IPN hoạt động

### 4.1 Kiểm tra endpoint

```bash
# Test IPN endpoint
curl -X POST https://yourdomain.com/payments/vnpay/ipn \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "vnp_Amount=10000000&vnp_BankCode=NCB&vnp_ResponseCode=00&vnp_TxnRef=TEST123"

# Kiểm tra response (should return "OK")
```

### 4.2 Kiểm tra logs

```bash
# Xem VNPay IPN logs
tail -f storage/logs/laravel.log | grep "VNPay IPN"

# Hoặc filter logs
grep "VNPay IPN" storage/logs/laravel.log | tail -20
```

### 4.3 Test từ VNPay Portal

1. Vào VNPay Portal → **"Công cụ test"** → **"Test IPN"**
2. Nhập IPN URL và test data
3. Kiểm tra logs server để xem IPN có được nhận

### 4.4 Test với thanh toán thật

1. Tạo booking test
2. Thanh toán qua VNPay sandbox
3. Hoàn tất thanh toán
4. Kiểm tra logs:
   ```bash
   grep "VNPay IPN processed successfully" storage/logs/laravel.log
   ```

## 🔧 Bước 5: Nginx Configuration

Đảm bảo Nginx cấu hình đúng cho IPN:

```nginx
# /etc/nginx/sites-available/yourdomain
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/web_booking/public;

    # IPN endpoint cần accessible
    location /payments/vnpay/ipn {
        try_files $uri $uri/ /index.php?$query_string;
        
        # Log IPN requests
        access_log /var/log/nginx/vnpay_ipn.log;
    }

    # Regular Laravel config
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🚨 Troubleshooting

### Lỗi thường gặp:

#### 1. IPN không được gọi
```bash
# Kiểm tra firewall
sudo ufw status

# Kiểm tra Nginx logs
tail -f /var/log/nginx/access.log | grep ipn

# Kiểm tra DNS
nslookup yourdomain.com
```

#### 2. Error 500 từ IPN
```bash
# Kiểm tra PHP errors
tail -f /var/log/nginx/error.log

# Kiểm tra Laravel logs
tail -f storage/logs/laravel.log

# Kiểm tra permissions
ls -la storage/logs/
```

#### 3. Signature verification failed
- ✅ Kiểm tra `VNPAY_HASH_SECRET` đúng không
- ✅ Kiểm tra encoding UTF-8
- ✅ Xem logs để debug signature:

```php
// Thêm vào VnpayService::verifyIpn() để debug
Log::info('VNPay IPN signature debug', [
    'received_hash' => $vnpSecureHash,
    'calculated_hash' => $secureHash,
    'hash_data' => $hashData,
]);
```

#### 4. Booking không tìm thấy
```bash
# Kiểm tra database
mysql -u username -p
SELECT * FROM bookings WHERE vnpay_txn_ref LIKE 'BOOKING_%' ORDER BY id DESC LIMIT 5;
```

## 📊 Monitoring VNPay IPN

### Tạo script monitoring

```bash
#!/bin/bash
# vnpay-monitor.sh

echo "📊 VNPay IPN Monitoring Report"
echo "================================"

# Count IPN calls today
IPN_TODAY=$(grep "$(date '+%Y-%m-%d')" storage/logs/laravel.log | grep "VNPay IPN received" | wc -l)
echo "📈 IPN calls today: $IPN_TODAY"

# Count successful IPNs
IPN_SUCCESS=$(grep "$(date '+%Y-%m-%d')" storage/logs/laravel.log | grep "VNPay IPN processed successfully" | wc -l)
echo "✅ Successful IPNs: $IPN_SUCCESS"

# Count failed IPNs
IPN_FAILED=$(grep "$(date '+%Y-%m-%d')" storage/logs/laravel.log | grep "VNPay IPN handling failed" | wc -l)
echo "❌ Failed IPNs: $IPN_FAILED"

# Latest IPN
echo ""
echo "🕒 Latest IPN calls:"
grep "VNPay IPN" storage/logs/laravel.log | tail -5
```

### Crontab cho monitoring

```bash
# Chạy monitoring mỗi giờ
0 * * * * /path/to/vnpay-monitor.sh >> /var/log/vnpay-monitor.log 2>&1
```

## ✨ Advanced Configuration

### 1. Rate Limiting cho IPN

```php
// routes/web.php
Route::post('/payments/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])
    ->middleware('throttle:100,1') // 100 requests per minute
    ->name('payments.vnpay.ipn');
```

### 2. IPN Retry Logic

VNPay sẽ retry IPN nếu không nhận được response 200. Để handle retry:

```php
// Trong PaymentController::vnpayIpn()
$txnRef = $request->input('vnp_TxnRef');

// Check if already processed
$existingTransaction = Transaction::where('transaction_id', $txnRef)
    ->where('status', Transaction::STATUS_COMPLETED)
    ->first();

if ($existingTransaction) {
    Log::info('VNPay IPN already processed', ['txn_ref' => $txnRef]);
    return response('OK', 200); // Prevent duplicate processing
}
```

### 3. Webhook Security

```php
// Thêm IP whitelist cho VNPay
$allowedIps = [
    '113.160.92.202', // VNPay IP range
    '113.160.92.203',
    // Add more IPs as needed
];

if (!in_array($request->ip(), $allowedIps)) {
    Log::warning('Unauthorized IPN access', ['ip' => $request->ip()]);
    return response('Unauthorized', 403);
}
```

## 🎯 Checklist Deploy Production

- [ ] ✅ VNPAY_TMN_CODE và VNPAY_HASH_SECRET đã cấu hình
- [ ] ✅ Domain SSL certificate active
- [ ] ✅ IPN URL cấu hình trong VNPay Portal
- [ ] ✅ Return URL cấu hình trong VNPay Portal  
- [ ] ✅ Firewall mở port 80/443
- [ ] ✅ Nginx logs cấu hình để monitor
- [ ] ✅ Laravel logs rotation setup
- [ ] ✅ Database backup strategy
- [ ] ✅ Test IPN với sandbox trước
- [ ] ✅ Monitor script setup

---

💡 **Lưu ý quan trọng:**
- IPN URL phải **public** và accessible từ internet
- VNPay retry IPN nếu không nhận response 200
- Luôn validate signature trước khi xử lý
- Log đầy đủ để debug khi có vấn đề 
