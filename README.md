# 🎓 Hệ thống Đặt lịch Gia sư Web_Booking

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 📋 Giới thiệu

Hệ thống Web_Booking là một nền tảng kết nối gia sư và học viên được xây dựng trên Laravel framework. Hệ thống cung cấp các chức năng đầy đủ cho việc tìm kiếm gia sư, đặt lịch học, thanh toán và quản lý.

## ✨ Tính năng chính

- **🔍 Tìm kiếm gia sư**: Tìm kiếm theo môn học, giá cả, địa điểm
- **📅 Đặt lịch học**: Hệ thống booking với xác nhận tự động
- **💳 Thanh toán VNPay**: Tích hợp cổng thanh toán VNPay
- **⭐ Đánh giá**: Hệ thống review và rating
- **💬 Tin nhắn**: Chat trực tiếp giữa gia sư và học viên
- **📊 Dashboard**: Giao diện quản lý cho học viên, gia sư và admin
- **🔔 Thông báo**: Hệ thống notification real-time

## 🛠️ Công nghệ sử dụng

- **Backend**: Laravel 11.x
- **Frontend**: Blade Templates + TailwindCSS + Alpine.js
- **Database**: MySQL 8.0
- **Payment**: VNPay Gateway
- **Testing**: PHPUnit + PestPHP
- **Code Quality**: PHPStan, Laravel Pint

## 🚀 Cài đặt

### Yêu cầu hệ thống

- PHP 8.2 trở lên
- Composer 2.x
- MySQL 8.0
- Node.js 18.x
- Redis (tuỳ chọn)

### Các bước cài đặt

1. **Clone repository**
   ```bash
   git clone https://github.com/your-username/web_booking.git
   cd web_booking
   ```

2. **Cài đặt dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Cấu hình môi trường**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Cấu hình database**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=web_booking
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Chạy migration và seeder**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Khởi chạy server**
   ```bash
   php artisan serve
   ```

## 🏗️ Cấu hình Admin Dashboard

Để sử dụng trang admin độc lập, thực hiện các bước sau:

### Cấu hình môi trường

Thêm các dòng sau vào file `.env`:

```env
APP_DOMAIN=web-booking.test
ADMIN_DOMAIN=admin.web-booking.test
```

### Cấu hình Local Development

Đối với môi trường phát triển local, bạn cần cấu hình file hosts:

1. **Mở file hosts**:
   - Windows: `C:\Windows\System32\drivers\etc\hosts`
   - Mac/Linux: `/etc/hosts`

2. **Thêm các dòng sau**:
   ```
   127.0.0.1  web-booking.test
   127.0.0.1  admin.web-booking.test
   ```

3. **Lưu file hosts** (có thể cần quyền administrator)

4. **Khởi động lại web server**

### Truy cập hệ thống

1. **Trang chính**: http://web-booking.test
2. **Admin dashboard**: http://admin.web-booking.test

## 🎯 Cấu trúc dự án

```
web_booking/
├── app/
│   ├── Http/Controllers/     # Controllers xử lý request
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic layer
│   ├── Repositories/        # Data access layer
│   ├── Notifications/       # Hệ thống thông báo
│   └── Policies/           # Authorization policies
├── database/
│   ├── migrations/         # Database migrations
│   ├── seeders/           # Database seeders
│   └── factories/         # Model factories
├── resources/
│   ├── views/             # Blade templates
│   ├── css/               # CSS files
│   └── js/                # JavaScript files
├── routes/
│   ├── web.php            # Web routes
│   └── auth.php           # Authentication routes
└── tests/                 # Test files
```

## 🔧 Phát triển

### Chạy tests

```bash
# PHPUnit tests
php artisan test

# Pest tests
./vendor/bin/pest

# Với coverage
./vendor/bin/pest --coverage
```

### Code quality

```bash
# Chạy PHPStan
./vendor/bin/phpstan analyse

# Laravel Pint (code formatting)
./vendor/bin/pint

# Chạy tất cả checks
composer check
```

### Development commands

```bash
# Tạo migration
php artisan make:migration create_table_name

# Tạo model với resource
php artisan make:model ModelName -mcr

# Tạo service
php artisan make:service ServiceName

# Tạo repository
php artisan make:repository RepositoryName

# Xóa cache
php artisan optimize:clear
```

## 🌟 Đóng góp

Cảm ơn bạn đã quan tâm đến việc đóng góp cho dự án! Vui lòng:

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📝 Quy tắc đóng góp

- Tuân thủ PSR-12 coding standards
- Viết tests cho code mới
- Cập nhật documentation khi cần
- Sử dụng conventional commits

## 🐛 Báo cáo lỗi

Nếu phát hiện lỗi bảo mật, vui lòng gửi email trực tiếp đến team thay vì tạo issue công khai.

## 📄 Giấy phép

Dự án này được phát hành dưới giấy phép [MIT License](https://opensource.org/licenses/MIT).

## 🤝 Hỗ trợ

- **Documentation**: Xem thư mục `docs/` để biết thêm chi tiết
- **Issues**: Tạo issue trên GitHub
- **Email**: support@web-booking.test

## 🎉 Cảm ơn

Cảm ơn tất cả những người đã đóng góp cho dự án này!

---

**Phiên bản Laravel**: 11.x  
**Cập nhật lần cuối**: 17 tháng 7, 2025
