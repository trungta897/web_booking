# Hướng dẫn hoàn tiền VNPay thủ công

## Tổng quan

VNPay không hỗ trợ API hoàn tiền tự động. Tất cả hoàn tiền phải được xử lý thủ công qua VNPay Merchant Portal. Hệ thống cung cấp các công cụ để hỗ trợ quy trình này.

## Quy trình hoàn tiền

### 1. Phương thức Command Line (Khuyến nghị)

#### Xem danh sách yêu cầu hoàn tiền
```bash
php artisan vnpay:refund list
```

#### Bắt đầu xử lý hoàn tiền
```bash
php artisan vnpay:refund process --booking=129
```
Lệnh này sẽ hiển thị:
- Thông tin booking và học viên
- Số tiền cần hoàn
- Thông tin giao dịch gốc từ VNPay
- Hướng dẫn chi tiết từng bước

#### Hoàn thành hoàn tiền
```bash
php artisan vnpay:refund complete --booking=129 --txn=VNPAY_REFUND_TXN_ID
```

### 2. Phương thức Admin Panel

1. Truy cập `/admin/refunds`
2. Xem danh sách yêu cầu hoàn tiền
3. Click "Xử lý" cho yêu cầu cần xử lý
4. Làm theo hướng dẫn trong VNPay Portal
5. Click "Hoàn thành" và nhập mã giao dịch

## Chi tiết từng bước

### Bước 1: Đăng nhập VNPay Merchant Portal
- Truy cập: https://merchant.vnpay.vn
- Đăng nhập bằng tài khoản merchant

### Bước 2: Tìm giao dịch cần hoàn tiền
- Vào mục **"Quản lý giao dịch"** → **"Tra cứu giao dịch"**
- Tìm theo:
  - Mã giao dịch VNPay (vnp_TransactionNo)
  - Mã tham chiếu (vnp_TxnRef)
  - Thời gian giao dịch

### Bước 3: Thực hiện hoàn tiền
- Click vào giao dịch cần hoàn tiền
- Chọn **"Hoàn tiền"**
- Nhập số tiền hoàn (có thể hoàn một phần)
- Nhập lý do hoàn tiền
- Xác nhận hoàn tiền

### Bước 4: Lấy mã giao dịch hoàn tiền
- Sau khi hoàn tiền thành công, VNPay sẽ cung cấp mã giao dịch hoàn tiền
- Copy mã này để cập nhật vào hệ thống

### Bước 5: Cập nhật hệ thống
- Chạy command complete hoặc sử dụng admin panel
- Nhập mã giao dịch hoàn tiền từ VNPay
- Hệ thống sẽ tự động:
  - Cập nhật trạng thái booking
  - Gửi thông báo cho học viên
  - Ghi log hoạt động

## Trạng thái hoàn tiền

| Trạng thái | Mô tả |
|------------|-------|
| `pending` | Yêu cầu hoàn tiền mới tạo, chờ xử lý |
| `processing` | Đang xử lý thủ công trên VNPay Portal |
| `completed` | Hoàn tiền thành công, đã cập nhật hệ thống |
| `failed` | Hoàn tiền thất bại |

## Lưu ý quan trọng

1. **Thời gian hoàn tiền**: 1-3 ngày làm việc để tiền về tài khoản học viên
2. **Phí hoàn tiền**: VNPay có thể thu phí hoàn tiền theo chính sách
3. **Thông báo**: Học viên sẽ nhận email thông báo khi hoàn tiền hoàn tất
4. **Backup**: Tất cả giao dịch hoàn tiền được log chi tiết

## Troubleshooting

### Không tìm thấy giao dịch trên VNPay Portal
- Kiểm tra lại mã giao dịch
- Kiểm tra khoảng thời gian tìm kiếm
- Liên hệ VNPay support nếu cần

### Hoàn tiền thất bại
- Kiểm tra số dư merchant
- Kiểm tra thời hạn hoàn tiền (thường 180 ngày)
- Kiểm tra trạng thái giao dịch gốc

### Lỗi hệ thống
- Kiểm tra log file: `storage/logs/laravel.log`
- Kiểm tra database transactions table
- Liên hệ technical support

## API Reference

### Transaction Model
```php
// Tìm yêu cầu hoàn tiền
$refunds = Transaction::where('payment_method', 'vnpay')
    ->where('type', 'refund')
    ->where('status', 'pending')
    ->get();

// Cập nhật trạng thái
$refund->update(['status' => 'completed']);
```

### Command Usage
```bash
# Xem help
php artisan vnpay:refund --help

# Liệt kê tất cả pending refunds
php artisan vnpay:refund list

# Xử lý booking cụ thể
php artisan vnpay:refund process --booking=123

# Hoàn thành với mã giao dịch
php artisan vnpay:refund complete --booking=123 --txn=REF123456789
```

## Support

- **Technical Support**: tech@yourcompany.com
- **VNPay Support**: 1900 55 55 77
- **Documentation**: https://sandbox.vnpayment.vn/apis/

---

*Cập nhật lần cuối: {{ now()->format('d/m/Y') }}* 
