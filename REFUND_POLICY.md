# 🔄 CHÍNH SÁCH VÀ QUY TRÌNH HOÀN TIỀN

## 📋 TỔNG QUAN

Hệ thống hoàn tiền được thiết kế để đảm bảo tính công bằng cho cả gia sư và học viên, đồng thời tuân thủ các quy định của VNPay và pháp luật Việt Nam.

## 🎯 CÁC LOẠI HOÀN TIỀN

### 1. Hoàn tiền toàn bộ (Full Refund)
- **Định nghĩa**: Hoàn lại 100% số tiền đã thanh toán
- **Kết quả**: Buổi học bị hủy hoàn toàn
- **Trạng thái booking**: Chuyển sang `cancelled`

### 2. Hoàn tiền một phần (Partial Refund)  
- **Định nghĩa**: Hoàn lại một phần số tiền đã thanh toán
- **Kết quả**: Buổi học vẫn diễn ra với thời gian rút gọn
- **Trạng thái booking**: Giữ nguyên nhưng cập nhật `payment_status`

## ⏰ ĐIỀU KIỆN THỜI GIAN

### Quy tắc 30 phút
- **Không thể hoàn tiền** sau khi buổi học đã bắt đầu quá 30 phút
- **Lý do**: Đảm bảo tính công bằng và tránh lạm dụng

### Thời gian xử lý
- **Tạo yêu cầu**: Ngay lập tức
- **Xử lý thủ công**: 1-3 ngày làm việc (qua VNPay portal)
- **Nhận tiền**: 3-5 ngày làm việc sau khi được duyệt

## 🔐 PHÂN QUYỀN VÀ BẢO MẬT

### Ai có thể yêu cầu hoàn tiền?
- **Chỉ gia sư** của buổi học đó
- **Không phải admin** (để tránh can thiệp không đúng quy trình)

### Điều kiện bắt buộc
- Booking phải ở trạng thái `confirmed` hoặc `pending`
- Payment status phải là `paid`
- Chưa được hoàn tiền trước đó (full refund)
- Trong khung thời gian cho phép

## 📝 LÝ DO HOÀN TIỀN ĐƯỢC CHẤP NHẬN

### 1. Gia sư không có mặt (`tutor_unavailable`)
- Gia sư đột ngột không thể dạy
- Trường hợp khẩn cấp của gia sư

### 2. Tình huống khẩn cấp (`emergency`)
- Sự cố y tế
- Thiên tai, thảm họa
- Tình huống bất khả kháng

### 3. Vấn đề kỹ thuật (`technical_issues`)
- Sự cố hệ thống nghiêm trọng
- Mất kết nối internet kéo dài
- Lỗi thiết bị không thể khắc phục

### 4. Xung đột lịch trình (`schedule_conflict`)
- Trùng lặp booking không thể giải quyết
- Thay đổi lịch học bất khả kháng

### 5. Lý do khác (`other`)
- Các trường hợp đặc biệt khác
- Cần mô tả chi tiết

## 🔄 QUY TRÌNH XỬ LÝ

### Bước 1: Gia sư tạo yêu cầu
```
1. Truy cập booking detail
2. Click "Hoàn tiền cho học viên"
3. Chọn loại hoàn tiền (full/partial)
4. Nhập số tiền (nếu partial)
5. Chọn lý do và mô tả
6. Xác nhận yêu cầu
```

### Bước 2: Hệ thống xử lý tự động
```
1. Validate điều kiện hoàn tiền
2. Tạo Transaction record với status 'pending'
3. Gửi thông báo cho học viên
4. Gửi email thông báo
5. Tạo log cho admin
```

### Bước 3: Admin xử lý thủ công (VNPay)
```
1. Truy cập Admin Panel > Refunds
2. Xem chi tiết yêu cầu hoàn tiền
3. Đăng nhập VNPay Merchant Portal
4. Thực hiện refund transaction
5. Cập nhật status thành 'completed'
```

## 💰 TÍNH TOÁN SỐ TIỀN HOÀN

### Hoàn tiền toàn bộ
```php
$refundAmount = $booking->price; // 100% giá trị booking
```

### Hoàn tiền một phần
```php
$maxRefund = $booking->price - $totalPreviousRefunds;
$refundAmount = min($requestedAmount, $maxRefund);
```

### Validation
- Số tiền hoàn ≥ 1,000 VND
- Số tiền hoàn ≤ Số tiền có thể hoàn còn lại
- Không vượt quá giá trị booking ban đầu

## 📊 TRACKING VÀ MONITORING

### Transaction States
```
pending → processing → completed
                   ↘ failed
                   ↘ cancelled (auto cleanup)
```

### Cleanup tự động
- **Stale refunds**: Sau 7 ngày ở trạng thái pending
- **Command**: `php artisan refunds:cleanup`
- **Frequency**: Daily cronjob

### Monitoring metrics
- Số lượng hoàn tiền theo trạng thái
- Tổng tiền hoàn theo thời gian
- Thời gian xử lý trung bình
- Top lý do hoàn tiền
- Xu hướng hoàn tiền hàng ngày

## 🔔 HỆ THỐNG THÔNG BÁO

### Email notifications
- **Học viên**: Nhận thông báo khi có yêu cầu hoàn tiền
- **Admin**: Nhận alert khi có yêu cầu mới
- **Gia sư**: Nhận xác nhận khi hoàn tiền thành công

### Database notifications
- Lưu trữ lịch sử thông báo
- Tracking trạng thái đã đọc/chưa đọc

## 🧪 TESTING STRATEGY

### Unit Tests
```bash
php artisan test tests/Feature/RefundSystemTest.php
```

### Test Cases
- ✅ Refund permissions và security
- ✅ Business logic validation
- ✅ Partial refund calculations
- ✅ Notification sending
- ✅ Transaction state management

### Manual Testing
```bash
# Dry run cleanup
php artisan refunds:cleanup --dry-run

# Test with specific scenarios
php artisan refunds:cleanup --send-reminders --dry-run
```

## 🚨 TROUBLESHOOTING

### Vấn đề thường gặp

#### 1. "Không thể hoàn tiền sau khi buổi học đã bắt đầu"
- **Nguyên nhân**: Vi phạm quy tắc 30 phút
- **Giải pháp**: Liên hệ admin để xử lý đặc biệt

#### 2. "Số tiền hoàn vượt quá số tiền có thể hoàn"
- **Nguyên nhân**: Đã có hoàn tiền một phần trước đó
- **Giải pháp**: Kiểm tra lịch sử transactions

#### 3. VNPay refund stuck ở "processing"
- **Nguyên nhân**: Chưa xử lý trên VNPay portal
- **Giải pháp**: Admin cần complete refund trên portal

### Emergency procedures

#### Force complete refund
```bash
php artisan vnpay:refund {booking_id} --force --amount={amount}
```

#### Reset booking payment status
```bash
php artisan booking:reset-payment {booking_id}
```

## 📈 PERFORMANCE OPTIMIZATION

### Database indexes
```sql
-- Transaction queries
INDEX idx_transactions_type_status (type, status);
INDEX idx_transactions_booking_id (booking_id);
INDEX idx_transactions_created_at (created_at);

-- Booking queries  
INDEX idx_bookings_payment_status (payment_status);
INDEX idx_bookings_tutor_id_start_time (tutor_id, start_time);
```

### Caching strategy
- Stats dashboard: Cache 5 phút
- Refund trends: Cache 1 giờ
- Top reasons: Cache 30 phút

## 🔒 SECURITY CONSIDERATIONS

### Input validation
- Validate refund amount range
- Sanitize reason descriptions
- Check user permissions

### Rate limiting
- Maximum 5 refund requests per tutor per day
- Prevent spam refund requests

### Audit logging
- Log tất cả refund actions
- Track user IP và timestamps
- Monitor suspicious patterns

## 📞 SUPPORT & ESCALATION

### Tier 1: Automated Help
- FAQ section trong help docs
- Error messages với hướng dẫn rõ ràng

### Tier 2: Admin Support
- Admin có thể xem full refund history
- Tools để manual override nếu cần

### Tier 3: Developer Support
- Debug commands
- Direct database access
- VNPay technical support

---

## 📅 VERSION HISTORY

- **v1.0** (2025-01): Initial refund system
- **v1.1** (2025-07): Added partial refund support
- **v1.2** (2025-07): Enhanced monitoring và automation

---

*Tài liệu này được cập nhật thường xuyên. Lần cập nhật cuối: {{ now()->format('d/m/Y') }}* 
