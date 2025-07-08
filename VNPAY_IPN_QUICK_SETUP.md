# ⚡ VNPay IPN Quick Setup (5 phút)

## 🎯 Tổng quan
VNPay IPN đã được tích hợp sẵn, chỉ cần cấu hình URLs!

## ⚡ Cấu hình nhanh

### 1. Cập nhật .env (30 giây)
```env
VNPAY_TMN_CODE=your_terminal_code_here
VNPAY_HASH_SECRET=your_hash_secret_here
VNPAY_IPN_URL=https://yourdomain.com/payments/vnpay/ipn
VNPAY_RETURN_URL=https://yourdomain.com/payments/vnpay/return
```

### 2. VNPay Portal (2 phút)
1. **Login:** https://sandbox.vnpayment.vn/merchant_webapi/merchant.html
2. **IPN URL:** Cấu hình → Cấu hình IPN → `https://yourdomain.com/payments/vnpay/ipn`
3. **Return URL:** Cấu hình → Cấu hình website → `https://yourdomain.com/payments/vnpay/return`

### 3. Test (1 phút)
```bash
# Test IPN endpoint
curl -X POST https://yourdomain.com/payments/vnpay/ipn \
  -d "vnp_ResponseCode=00&vnp_TxnRef=TEST123"

# Should return: OK
```

### 4. Monitor (30 giây)
```bash
# Xem logs
tail -f storage/logs/laravel.log | grep "VNPay IPN"
```

## 🧪 Development với localhost

### Sử dụng ngrok
```bash
# Cài ngrok
npm install -g ngrok

# Start tunnel
ngrok http 80

# Copy URL và cập nhật VNPay Portal
# Ví dụ: https://abc123.ngrok.io/payments/vnpay/ipn
```

## ✅ Tính năng đã có

- ✅ **IPN Processing** - Tự động xử lý thanh toán
- ✅ **Signature Verification** - Xác thực chữ ký VNPay  
- ✅ **Retry Logic** - Tránh xử lý trùng lặp
- ✅ **Rate Limiting** - Bảo vệ khỏi spam
- ✅ **Error Handling** - Xử lý lỗi thông minh
- ✅ **Detailed Logging** - Log chi tiết để debug
- ✅ **Security Checks** - Kiểm tra bảo mật
- ✅ **Transaction Tracking** - Theo dõi giao dịch

## 🚨 Troubleshooting nhanh

### IPN không hoạt động?
```bash
# 1. Kiểm tra endpoint
curl -I https://yourdomain.com/payments/vnpay/ipn

# 2. Kiểm tra logs
grep "VNPay IPN" storage/logs/laravel.log | tail -5

# 3. Kiểm tra cấu hình
php artisan config:clear && php artisan config:cache
```

### Signature failed?
```bash
# Kiểm tra HASH_SECRET trong .env
grep VNPAY_HASH_SECRET .env
```

### 500 Error?
```bash
# Kiểm tra permissions
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## 📋 VNPay Portal URLs

- **Sandbox:** https://sandbox.vnpayment.vn/merchant_webapi/merchant.html
- **Production:** https://vnpayment.vn/merchant_webapi/merchant.html

## 🔧 Helper Script

Sử dụng script tiện ích để test và monitor:

```bash
# Test all
./scripts/vnpay-ipn-helper.sh yourdomain.com

# Test endpoint only  
./scripts/vnpay-ipn-helper.sh test yourdomain.com

# Monitor logs
./scripts/vnpay-ipn-helper.sh monitor

# Check ngrok
./scripts/vnpay-ipn-helper.sh ngrok
```

---

💡 **Lưu ý:** 
- IPN URL phải accessible từ internet
- Dùng ngrok cho localhost development  
- VNPay sẽ retry nếu không nhận response 200
- Check logs khi có vấn đề 
