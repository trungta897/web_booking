# 🧪 Test ngrok với WAMP (localhost/web_booking)

## ⚡ Hướng dẫn nhanh

### 1. **Cài đặt ngrok**
```bash
# Cách 1: Download từ https://ngrok.com/download
# Giải nén và thêm vào PATH

# Cách 2: Dùng npm
npm install -g ngrok

# Cách 3: Dùng chocolatey  
choco install ngrok
```

### 2. **Chạy script tự động**
```bash
# Chạy file batch đã tạo
setup-ngrok-wamp.bat
```

### 3. **Hoặc chạy manual**
```bash
# Kiểm tra WAMP đang chạy
netstat -an | findstr :80

# Khởi động ngrok cho port 80
ngrok http 80
```

## 📋 Kết quả mong đợi

Khi ngrok khởi động thành công, bạn sẽ thấy:

```
Session Status                online
Account                       your-account (Plan: Free)
Version                       3.x.x
Region                        Asia Pacific (ap)
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123.ngrok.io -> http://localhost:80

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

## 🔗 URLs để cấu hình VNPay

Từ kết quả ngrok trên, URLs sẽ là:

- **Base URL:** `https://abc123.ngrok.io`
- **Web booking:** `https://abc123.ngrok.io/web_booking/`
- **IPN URL:** `https://abc123.ngrok.io/web_booking/payments/vnpay/ipn`
- **Return URL:** `https://abc123.ngrok.io/web_booking/payments/vnpay/return`

## ✅ Test các endpoint

### 1. Test website access
```bash
# Mở browser và truy cập
https://abc123.ngrok.io/web_booking/
```

### 2. Test IPN endpoint
```bash
curl -X POST https://abc123.ngrok.io/web_booking/payments/vnpay/ipn \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "vnp_ResponseCode=00&vnp_TxnRef=TEST_$(date +%s)"
```

### 3. Test VNPay pages
```bash
# Test VNPay demo page
https://abc123.ngrok.io/web_booking/vnpay-demo

# Test VNPay admin test (admin only)
https://abc123.ngrok.io/web_booking/test-vnpay
```

## 🚨 Troubleshooting

### Ngrok không khởi động được
- ✅ Kiểm tra port 80 có bị chiếm không: `netstat -an | findstr :80`
- ✅ Đảm bảo WAMP đang chạy
- ✅ Chạy Command Prompt as Administrator

### WAMP không chạy port 80
- ✅ Kiểm tra Apache có start không
- ✅ Kiểm tra Skype/IIS có chiếm port 80 không
- ✅ Đổi WAMP sang port khác (8080) và dùng `ngrok http 8080`

### Ngrok tunnel timeout
- ✅ Free account có giới hạn thời gian
- ✅ Restart ngrok khi cần
- ✅ Cập nhật URL mới trong VNPay portal

## 📊 Monitor ngrok

### Web Interface
- Mở http://127.0.0.1:4040 để xem traffic
- Monitor requests/responses real-time
- Debug IPN calls từ VNPay

### Log files
```bash
# Xem Laravel logs
tail -f C:\wamp64\www\web_booking\storage\logs\laravel.log | findstr "VNPay"
```

## 🎯 Cấu hình VNPay Portal

Khi có ngrok URL, cấu hình trong VNPay sandbox:

1. **Login:** https://sandbox.vnpayment.vn/
2. **IPN Config:** Cấu hình → Cấu hình IPN
   - URL: `https://your-ngrok-url.ngrok.io/web_booking/payments/vnpay/ipn`
   - Method: POST
   - Status: Active
3. **Return URL:** Cấu hình → Cấu hình website  
   - URL: `https://your-ngrok-url.ngrok.io/web_booking/payments/vnpay/return`

---

💡 **Tip:** Giữ terminal ngrok mở trong suốt quá trình test. URL sẽ thay đổi mỗi khi restart ngrok (trừ khi có paid plan). 
