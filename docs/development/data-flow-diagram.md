# 📊 Biểu đồ Luồng Dữ liệu (Data Flow Diagram) - Hệ thống Đặt Lịch Gia Sư

## 🎯 **TỔNG QUAN**

Biểu đồ luồng dữ liệu (DFD) mô tả cách dữ liệu di chuyển qua hệ thống đặt lịch gia sư, từ đầu vào đến đầu ra, qua các quá trình xử lý và lưu trữ.

---

## 📋 **DFD CẤP 0 - SƠ ĐỒ BỐI CẢNH**

```
                    ┌─────────────────────────────────────────────────────────────┐
                    │                                                             │
                    │            HỆ THỐNG ĐẶT LỊCH GIA SƯ                        │
                    │                                                             │
                    │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │
                    │  │   Bảng      │  │   Bảng      │  │   Bảng      │        │
                    │  │ Điều khiển  │  │ Điều khiển  │  │ Điều khiển  │        │
                    │  │  Học viên   │  │   Gia sư    │  │ Quản trị    │        │
                    │  └─────────────┘  └─────────────┘  └─────────────┘        │
                    │                                                             │
                    └─────────────────────────────────────────────────────────────┘
                             ↑                ↑                ↑
                             │                │                │
                    ┌────────┴────────┐   ┌──┴──┐   ┌────────┴────────┐
                    │                 │   │     │   │                 │
              ┌─────────────┐   ┌─────────────┐   ┌─────────────┐   ┌─────────────┐
              │   HỌC VIÊN  │   │   GIA SƯ   │   │  QUẢN TRỊ   │   │   CỔNG      │
              │             │   │             │   │             │   │  THANH TOÁN │
              │ - Hồ sơ     │   │ - Hồ sơ     │   │ - Quản lý   │   │   VNPAY     │
              │ - Tìm kiếm  │   │ - Lịch dạy  │   │ - Giám sát  │   │             │
              │ - Đặt lịch  │   │ - Thu nhập  │   │ - Báo cáo   │   │ - Thanh toán│
              │ - Đánh giá  │   │ - Đánh giá  │   │ - Phân tích │   │ - Hoàn tiền │
              └─────────────┘   └─────────────┘   └─────────────┘   └─────────────┘
```

---

## 🔄 **DFD CẤP 1 - CÁC QUY TRÌNH CHÍNH**

### **1. Quy trình Xác thực & Phân quyền**

```
Thông tin đăng nhập → [1.0 Xác thực người dùng] → Phiên đăng nhập → [1.1 Phân quyền] → Quyền truy cập
                               ↓                                             ↓
                     [D1: Người dùng]                              [D2: Phiên làm việc]
```

### **2. Quy trình Quản lý Người dùng**

```
Dữ liệu hồ sơ → [2.0 Quản lý người dùng] → Hồ sơ cập nhật → [2.1 Xác thực hồ sơ] → Hồ sơ lưu trữ
                           ↓                                             ↓
                    [D1: Người dùng]                                [D3: Gia sư]
                           ↓                                             ↓
                    [D4: Học vấn]                                  [D5: Môn học]
```

### **3. Quy trình Đặt lịch & Quản lý lịch học**

```
Yêu cầu đặt lịch → [3.0 Tạo đặt lịch] → Bản ghi đặt lịch → [3.1 Xác thực lịch học] → Lịch học xác nhận
                           ↓                                             ↓
                    [D6: Đặt lịch]                               [D7: Lịch trống]
                           ↓                                             ↓
                    [3.2 Gửi thông báo] ← Cập nhật trạng thái ← [3.3 Quản lý trạng thái]
```

### **4. Quy trình Thanh toán**

```
Yêu cầu thanh toán → [4.0 Xử lý thanh toán] → Giao dịch → [4.1 Cổng VNPay] → Kết quả thanh toán
                              ↓                                        ↓
                    [D8: Giao dịch]                           [D9: Nhật ký thanh toán]
                              ↓                                        ↓
                    [4.2 Tính hoa hồng] → Thu nhập → [4.3 Xử lý rút tiền]
```

---

## 🗂️ **DFD CẤP 2 - CHI TIẾT CÁC QUY TRÌNH**

### **2.1 Quy trình Tìm kiếm & Khám phá**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         QUY TRÌNH TÌM KIẾM & KHÁM PHÁ                           │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  Tiêu chí tìm kiếm → [2.1.1 Xử lý bộ lọc] → Kết quả lọc                       │
│        ↓                     ↓                        ↓                         │
│   [D5: Môn học]      [D3: Gia sư]               [2.1.2 Xếp hạng]              │
│        ↓                     ↓                        ↓                         │
│   [D10: Đánh giá]     [D7: Lịch trống]          Kết quả xếp hạng               │
│        ↓                     ↓                        ↓                         │
│   [2.1.3 Lưu cache] ← Tạo khóa cache ← [2.1.4 Định dạng kết quả]             │
│        ↓                                              ↓                         │
│   [D11: Bộ nhớ đệm]                            Hiển thị kết quả                │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### **2.2 Quy trình Quản lý Đặt lịch**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         QUẢN LÝ ĐẶT LỊCH HỌC                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  Yêu cầu đặt lịch → [2.2.1 Xác thực] → Yêu cầu hợp lệ                        │
│        ↓                   ↓                    ↓                               │
│   [D3: Gia sư]      [D7: Lịch trống]     [2.2.2 Kiểm tra xung đột]           │
│        ↓                   ↓                    ↓                               │
│   [D6: Đặt lịch]    [2.2.3 Tính giá] → [2.2.4 Tạo đặt lịch]                  │
│        ↓                   ↓                    ↓                               │
│   [D8: Giao dịch]   [D12: Hoa hồng] → [2.2.5 Cập nhật trạng thái]            │
│        ↓                                        ↓                               │
│   [2.2.6 Gửi thông báo] → Gia sư/Học viên → [2.2.7 Hàng đợi email]           │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### **2.3 Quy trình Thanh toán VNPay**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         QUY TRÌNH THANH TOÁN VNPAY                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  Yêu cầu thanh toán → [2.3.1 Xác thực trước] → Thanh toán hợp lệ              │
│        ↓                     ↓                      ↓                           │
│   [D6: Đặt lịch]      [D8: Giao dịch]         [2.3.2 Tạo URL VNPay]           │
│        ↓                     ↓                      ↓                           │
│   [2.3.3 Chuyển hướng] → Cổng VNPay → [2.3.4 Kết quả thanh toán]              │
│        ↓                                            ↓                           │
│   [2.3.5 Callback] → [2.3.6 Xác minh] → [2.3.7 Cập nhật trạng thái]          │
│        ↓                     ↓                      ↓                           │
│   [D8: Giao dịch]     [D9: Nhật ký thanh toán] [2.3.8 Tính hoa hồng]          │
│        ↓                                            ↓                           │
│   [2.3.9 Thông báo] → Thành công/Thất bại → [2.3.10 Cập nhật đặt lịch]        │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 💾 **CÁC KHO DỮ LIỆU**

### **Cơ sở dữ liệu chính:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              KHO DỮ LIỆU                                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  D1: Người dùng               │  D2: Phiên làm việc                             │
│  - id, tên, email, vai trò    │  - id_phiên, id_người_dùng, dữ_liệu           │
│  - ảnh_đại_diện, sdt, địa_chỉ │  - hết_hạn_lúc, địa_chỉ_ip                    │
│  - trạng_thái_tài_khoản       │                                                 │
│                                                                                 │
│  D3: Gia sư                   │  D4: Học vấn                                    │
│  - id, id_người_dùng, tiểu_sử │  - id, id_gia_sư, bằng_cấp                     │
│  - giá_theo_giờ, kinh_nghiệm  │  - trường_học, năm, hình_ảnh                   │
│  - có_sẵn, chuyên_môn        │                                                 │
│                                                                                 │
│  D5: Môn học                  │  D6: Đặt lịch                                   │
│  - id, tên, mô_tả             │  - id, id_học_viên, id_gia_sư                  │
│  - biểu_tượng, thể_loại       │  - thời_gian_bắt_đầu, kết_thúc, giá            │
│  - đang_hoạt_động            │  - đã_xác_nhận, đã_hủy, hoàn_thành             │
│                                                                                 │
│  D7: Lịch trống               │  D8: Giao dịch                                  │
│  - id, id_gia_sư, ngày_tuần   │  - id, id_đặt_lịch, số_tiền                    │
│  - giờ_bắt_đầu, giờ_kết_thúc │  - phương_thức_thanh_toán, trạng_thái          │
│  - có_sẵn                    │  - mã_vnpay, siêu_dữ_liệu                      │
│                                                                                 │
│  D9: Nhật ký thanh toán       │  D10: Đánh giá                                  │
│  - id, id_giao_dịch          │  - id, id_gia_sư, id_học_viên                  │
│  - phản_hồi_cổng             │  - điểm_số, bình_luận, tạo_lúc                 │
│  - xử_lý_lúc, trạng_thái     │                                                 │
│                                                                                 │
│  D11: Bộ nhớ đệm              │  D12: Hoa hồng                                  │
│  - khóa, giá_trị, hết_hạn     │  - id, id_đặt_lịch, tỷ_lệ                      │
│  - loại, thẻ                  │  - phí_nền_tảng, thu_nhập_gia_sư               │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 **LUỒNG DỮ LIỆU THEO KỊCH BẢN**

### **Kịch bản 1: Học viên đặt lịch học**

```
1. Tìm kiếm học viên    → [Xử lý bộ lọc] → [D5: Môn học] → [D3: Gia sư]
                                ↓
2. Chọn gia sư         → [Kiểm tra lịch trống] → [D7: Lịch trống]
                                ↓
3. Tạo đặt lịch        → [Xác thực] → [D6: Đặt lịch] → [Thông báo]
                                ↓
4. Xử lý thanh toán    → [Cổng VNPay] → [D8: Giao dịch]
                                ↓
5. Xác nhận           → [Cập nhật trạng thái] → [D6: Đặt lịch] → [Hàng đợi email]
```

### **Kịch bản 2: Gia sư quản lý thu nhập**

```
1. Xem thu nhập       → [Tính hoa hồng] → [D8: Giao dịch] → [D12: Hoa hồng]
                                ↓
2. Yêu cầu rút tiền   → [Xác thực] → [D13: Rút tiền] → [Hàng đợi quản trị]
                                ↓
3. Xử lý quản trị     → [Duyệt] → [D13: Rút tiền] → [Chuyển khoản ngân hàng]
                                ↓
4. Xác nhận          → [Cập nhật trạng thái] → [Thông báo] → [Hàng đợi email]
```

### **Kịch bản 3: Quản trị viên quản lý hệ thống**

```
1. Xem bảng điều khiển → [Tổng hợp thống kê] → [D1: Người dùng] + [D6: Đặt lịch]
                                ↓
2. Quản lý người dùng  → [Cập nhật vai trò] → [D1: Người dùng] → [Xóa phiên]
                                ↓
3. Tạo báo cáo        → [Xuất dữ liệu] → [Nhiều bảng] → [CSV/PDF]
                                ↓
4. Giám sát hệ thống  → [Kiểm tra hiệu suất] → [D9: Nhật ký thanh toán] → [Cảnh báo]
```

---

## 📊 **LUỒNG DỮ LIỆU THỜI GIAN THỰC**

### **Luồng dữ liệu theo thời gian thực:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                      LUỒNG DỮ LIỆU THỜI GIAN THỰC                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  Hành động người dùng → [Kích hoạt sự kiện] → [Hệ thống hàng đợi] → [Công việc nền] │
│        ↓                     ↓                      ↓                    ↓           │
│   [D14: Sự kiện]      [D15: Công việc]       [D16: Thông báo]    [D17: Email]      │
│        ↓                     ↓                      ↓                    ↓           │
│   [WebSocket] → [Phát sóng] → [Cập nhật máy khách] → [Làm mới giao diện]         │
│                                                                                 │
│  Callback thanh toán → [Webhook] → [Xác minh] → [Cập nhật trạng thái]           │
│        ↓                              ↓                ↓                        │
│   [D8: Giao dịch]             [D9: Nhật ký thanh toán] [D6: Đặt lịch]          │
│        ↓                              ↓                ↓                        │
│   [Thông báo tự động] → [Hàng đợi email] → [Hàng đợi SMS] → [Thông báo đẩy]    │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔐 **LUỒNG DỮ LIỆU BẢO MẬT**

### **Luồng dữ liệu bảo mật:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         LUỒNG DỮ LIỆU BẢO MẬT                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  Yêu cầu đăng nhập → [Xác thực] → [D1: Người dùng] → [Tạo phiên]               │
│        ↓                ↓                              ↓                        │
│   [Token CSRF]    [Mã hóa mật khẩu]              [D2: Phiên làm việc]          │
│        ↓                ↓                              ↓                        │
│   [Xác thực] → [Kiểm tra vai trò] → [D18: Quyền] → [Cấp quyền truy cập]        │
│        ↓                              ↓                                         │
│   [Nhật ký kiểm tra] → [D19: Nhật ký bảo mật] → [Giám sát]                     │
│                                                                                 │
│  Dữ liệu thanh toán → [Mã hóa] → [Lưu trữ an toàn] → [D8: Giao dịch]          │
│        ↓                              ↓                                         │
│   [Tuân thủ PCI] → [Che dấu dữ liệu] → [Đường mòn kiểm tra]                    │
│        ↓                              ↓                                         │
│   [D20: Nhật ký kiểm tra] → [Báo cáo bảo mật] → [Bảng điều khiển quản trị]     │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📈 **LUỒNG DỮ LIỆU PHÂN TÍCH**

### **Phân tích và báo cáo:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         LUỒNG DỮ LIỆU PHÂN TÍCH                                │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  Hoạt động người dùng → [Theo dõi sự kiện] → [D21: Phân tích] → [Tổng hợp dữ liệu] │
│        ↓                    ↓                                    ↓               │
│   [Lượt xem trang]    [Sự kiện nhấp chuột]              [D22: Báo cáo]         │
│        ↓                    ↓                                    ↓               │
│   [Dữ liệu phiên] → [Phân tích hành vi] → [Xử lý ML] → [Đề xuất]               │
│        ↓                                        ↓                               │
│   [D23: Mô hình ML] → [Dữ liệu đào tạo] → [Cập nhật mô hình] → [API dự đoán]   │
│                                                                                 │
│  Chỉ số kinh doanh → [Tính toán KPI] → [Bảng điều khiển] → [Báo cáo điều hành] │
│        ↓                    ↓                    ↓                               │
│   [Dữ liệu doanh thu] [Chỉ số người dùng] [Dữ liệu hiệu suất]                  │
│        ↓                    ↓                    ↓                               │
│   [D24: Cache KPI] → [Cập nhật thời gian thực] → [Bảng điều khiển quản trị]    │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🎯 **TỔNG KẾT LUỒNG DỮ LIỆU**

### **Đặc điểm chính:**

1. **Kiến trúc phân tầng**: Kho dữ liệu → Dịch vụ → Điều khiển → Giao diện
2. **Xử lý bất đồng bộ**: Hệ thống hàng đợi cho thông báo và email
3. **Bộ nhớ đệm hiệu quả**: Cache Redis/File cho dữ liệu thường xuyên truy cập
4. **Bảo mật toàn diện**: Mã hóa, xác thực, phân quyền
5. **Giám sát và ghi nhật ký**: Đường mòn kiểm tra toàn diện
6. **Khả năng mở rộng**: Kiến trúc sẵn sàng cho microservices

### **Luồng dữ liệu quan trọng:**

- **Quản lý người dùng**: Xác thực → Phân quyền → Quản lý hồ sơ
- **Luồng đặt lịch**: Tìm kiếm → Chọn lựa → Thanh toán → Xác nhận
- **Xử lý thanh toán**: Tích hợp VNPay → Ghi nhận giao dịch → Tính hoa hồng
- **Cập nhật thời gian thực**: WebSocket → Phát sóng → Cập nhật máy khách
- **Phân tích**: Theo dõi sự kiện → Xử lý dữ liệu → Tạo báo cáo

### **Các quy trình nghiệp vụ chính:**

1. **Đăng ký và xác thực tài khoản**
2. **Tạo và quản lý hồ sơ gia sư**
3. **Tìm kiếm và lọc gia sư**
4. **Đặt lịch học và xác nhận**
5. **Thanh toán và xử lý giao dịch**
6. **Quản lý lịch dạy và học**
7. **Tính toán và rút tiền hoa hồng**
8. **Đánh giá và phản hồi**
9. **Thông báo và nhắn tin**
10. **Báo cáo và phân tích**

---

*Tài liệu này mô tả luồng dữ liệu chi tiết bằng tiếng Việt của hệ thống đặt lịch gia sư, từ tổng quan đến từng quy trình cụ thể.*

**Cập nhật lần cuối:** Tháng 7, 2025