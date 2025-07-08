# 💳 Payment System Setup Guide

## 🔧 Cấu hình Environment Variables

Thêm các biến môi trường sau vào file `.env`:

```env
# Stripe Configuration
STRIPE_KEY=pk_test_your_stripe_public_key
STRIPE_SECRET=sk_test_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# VNPay Configuration
VNPAY_TMN_CODE=your_vnpay_terminal_code
VNPAY_HASH_SECRET=your_vnpay_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://localhost:8000/payments/vnpay/return
VNPAY_IPN_URL=http://localhost:8000/payments/vnpay/ipn
```

## 🏦 VNPay Setup

### 1. Đăng ký tài khoản VNPay

1. Truy cập [VNPay Developer Portal](https://sandbox.vnpayment.vn/)
2. Đăng ký tài khoản merchant (doanh nghiệp)
3. Lấy thông tin:
   - **TMN Code**: Mã terminal do VNPay cấp
   - **Hash Secret**: Khóa bí mật để mã hóa

### 2. Cấu hình Return URL và IPN URL

#### 🔗 URLs cần cấu hình:
- **Return URL**: `https://yourdomain.com/payments/vnpay/return`
- **IPN URL**: `https://yourdomain.com/payments/vnpay/ipn`

#### 📋 Bước cấu hình trong VNPay Sandbox Portal:

1. **Truy cập VNPay Sandbox Portal**
   - URL: https://sandbox.vnpayment.vn/
   - Đăng nhập với tài khoản merchant đã đăng ký

2. **Cấu hình Return URL**
   - Vào menu: **Cấu hình** → **Cấu hình website**
   - Tại mục "Return URL", nhập: `https://yourdomain.com/payments/vnpay/return`
   - Nhấn **Lưu cấu hình**

3. **Cấu hình IPN URL (Webhook)**
   - Vào menu: **Cấu hình** → **Cấu hình IPN**
   - Tại mục "IPN URL", nhập: `https://yourdomain.com/payments/vnpay/ipn`
   - Chọn phương thức: **POST**
   - Bật trạng thái: **Kích hoạt**
   - Nhấn **Lưu cấu hình**

#### 🧪 Đối với môi trường Development (localhost):

Vì VNPay không thể gọi trực tiếp tới localhost, bạn có 2 lựa chọn:

**Lựa chọn 1: Sử dụng ngrok (Khuyến nghị)**
```bash
# Cài đặt ngrok
npm install -g ngrok

# Tạo tunnel cho localhost:8000
ngrok http 8000

# Sử dụng URL ngrok cho cấu hình
# Ví dụ: https://abc123.ngrok.io/payments/vnpay/return
#        https://abc123.ngrok.io/payments/vnpay/ipn
```

**Lựa chọn 2: Deploy lên server test**
- Deploy code lên server có domain thật
- Cấu hình URL thật cho Return URL và IPN URL

### 3. Test với Sandbox

VNPay Sandbox URLs:
- **Payment Gateway**: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- **Query API**: `https://sandbox.vnpayment.vn/merchant_webapi/api/transaction`

#### 🧪 Test IPN URL

1. **Kiểm tra IPN endpoint có hoạt động:**
```bash
# Test IPN endpoint bằng cURL
curl -X POST https://yourdomain.com/payments/vnpay/ipn \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "vnp_Amount=10000000&vnp_BankCode=NCB&vnp_ResponseCode=00"
```

2. **Kiểm tra log để xác nhận IPN được nhận:**
```bash
# Xem log Laravel
tail -f storage/logs/laravel.log | grep "VNPay IPN"
```

3. **Test thanh toán thật trên sandbox:**
   - Tạo giao dịch test
   - Hoàn tất thanh toán trên VNPay sandbox
   - Kiểm tra log xem IPN có được gọi không

#### ⚠️ Troubleshooting IPN

**Lỗi thường gặp:**

1. **IPN không được gọi:**
   - Kiểm tra URL có đúng không
   - Kiểm tra firewall/security group
   - Đảm bảo endpoint trả về status 200

2. **IPN signature verification failed:**
   - Kiểm tra VNPAY_HASH_SECRET đúng không
   - Kiểm tra encoding của dữ liệu

3. **500 Error từ IPN endpoint:**
   - Kiểm tra log Laravel: `storage/logs/laravel.log`
   - Kiểm tra database connection
   - Kiểm tra permissions

**Debug IPN:**
```php
// Thêm vào PaymentController::vnpayIpn() để debug
Log::info('VNPay IPN received', [
    'all_data' => $request->all(),
    'headers' => $request->headers->all(),
    'ip' => $request->ip(),
]);
```

## 💰 Stripe Setup

### 1. Tạo tài khoản Stripe

1. Truy cập [Stripe Dashboard](https://dashboard.stripe.com/)
2. Đăng ký tài khoản
3. Lấy API Keys từ phần Developers > API Keys

### 2. Cấu hình Webhook

1. Trong Stripe Dashboard > Developers > Webhooks
2. Thêm endpoint: `https://yourdomain.com/webhook/stripe`
3. Chọn events: `payment_intent.succeeded`, `payment_intent.payment_failed`
4. Copy Webhook Secret

## 🚀 Cách sử dụng

### 1. Thanh toán VNPay

```php
// Tạo URL thanh toán VNPay
$vnpayService = new VnpayService();
$paymentUrl = $vnpayService->createPaymentUrl($booking, $request->ip());

// Redirect user đến VNPay
return redirect($paymentUrl);
```

### 2. Thanh toán Stripe

```javascript
// Frontend - Sử dụng Stripe Elements
const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
    payment_method: {
        card: card,
        billing_details: {
            name: 'Customer Name',
        },
    },
});
```

### 3. Xem Transaction History

```php
// Controller
public function viewTransactionHistory(Booking $booking)
{
    $transactions = $booking->transactions()
        ->orderBy('created_at', 'desc')
        ->get();
        
    return view('bookings.transactions', compact('booking', 'transactions'));
}
```

## 🎨 UI Features

### Giao diện thanh toán mới bao gồm:

- ✅ **Multi-payment method selection**: VNPay và Stripe
- ✅ **Modern card UI**: Hiển thị icons ngân hàng, thẻ tín dụng
- ✅ **Responsive design**: Tối ưu cho mobile và desktop
- ✅ **Interactive animations**: Hover effects và loading states
- ✅ **Vietnamese localization**: Đa ngôn ngữ hoàn chỉnh

### Transaction History Features:

- ✅ **Detailed transaction table**: ID, method, amount, status, date
- ✅ **Status badges**: Colored indicators for transaction status
- ✅ **Payment method icons**: Visual indicators for VNPay/Stripe
- ✅ **Summary statistics**: Count by status and total amounts

## 🔒 Security Features

### VNPay Security:
- ✅ **HMAC-SHA512**: Hash verification cho tất cả requests
- ✅ **IP validation**: Kiểm tra IP address
- ✅ **Transaction timeout**: 30 phút timeout cho mỗi giao dịch

### Stripe Security:
- ✅ **Webhook signatures**: Xác thực webhook events
- ✅ **PCI compliance**: Stripe handles card data securely
- ✅ **3D Secure**: Automatic 3DS for applicable cards

## 🗄️ Database Schema

### Bảng `transactions`:
```sql
- id: Primary key
- booking_id: Foreign key to bookings
- user_id: Foreign key to users
- transaction_id: Unique transaction identifier
- payment_method: enum('stripe', 'vnpay', 'paypal', etc.)
- type: enum('payment', 'refund', 'partial_refund')
- amount: decimal(10,2)
- currency: varchar(3) default 'VND'
- status: enum('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded')
- gateway_response: json
- metadata: json
- processed_at: timestamp
```

### Cập nhật bảng `bookings`:
```sql
- payment_method: enum('stripe', 'vnpay', 'paypal', 'cash')
- vnpay_txn_ref: varchar nullable
- exchange_rate: decimal(10,4) nullable
- currency: varchar(3) default 'VND'
- original_amount: decimal(10,2) nullable
- payment_metadata: json nullable
```

## 🧪 Testing

### Test VNPay Sandbox:

1. Sử dụng thông tin test:
   - **Bank**: NCB
   - **Card Number**: 9704198526191432198
   - **Card Holder**: NGUYEN VAN A
   - **Issue Date**: 07/15
   - **OTP**: 123456

### Test Stripe:

1. Sử dụng test cards:
   - **Visa**: 4242424242424242
   - **Visa (declined)**: 4000000000000002
   - **Mastercard**: 5555555555554444

## 🚨 Troubleshooting

### VNPay Common Issues:

1. **Invalid Hash**: Kiểm tra VNPAY_HASH_SECRET
2. **Invalid TMN Code**: Xác nhận VNPAY_TMN_CODE
3. **Wrong URL**: Đảm bảo return URL đúng format

### Stripe Common Issues:

1. **API Key không hợp lệ**: Kiểm tra STRIPE_SECRET
2. **Webhook verification failed**: Xác nhận STRIPE_WEBHOOK_SECRET
3. **Payment failed**: Kiểm tra card number và billing info

## 📝 Logs

Transaction logs được lưu tại:
- **Laravel Log**: `storage/logs/laravel.log`
- **Database**: `transactions` table
- **Booking metadata**: `payment_metadata` field

Tìm kiếm logs:
```bash
# VNPay logs
grep "VNPay" storage/logs/laravel.log

# Stripe logs  
grep "Stripe" storage/logs/laravel.log

# Payment logs
grep "Payment" storage/logs/laravel.log
``` 
