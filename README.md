# 📚 Hệ thống Đặt Lịch Học Trực Tuyến Web_Booking

## 📖 Giới thiệu

Web_Booking là một hệ thống đặt lịch học trực tuyến hiện đại, kết nối học viên với các gia sư chuyên nghiệp. Hệ thống được xây dựng với Laravel PHP Framework, cung cấp giao diện thân thiện và các tính năng quản lý toàn diện.

### 🎯 Mục tiêu dự án
- Tạo nền tảng kết nối học viên và gia sư một cách hiệu quả
- Cung cấp hệ thống đặt lịch học linh hoạt và dễ sử dụng
- Quản lý thanh toán trực tuyến an toàn qua VNPay và Stripe
- Hỗ trợ đánh giá và phản hồi để nâng cao chất lượng dịch vụ

## 🔗 Liên kết mã nguồn

**Repository chính:** `c:\wamp64\www\web_booking`
**Cấu trúc dự án:** Laravel 12.x Framework

## 🛠️ Công nghệ và công cụ sử dụng

### Backend Framework
- **Laravel 12.x** - PHP Framework chính
- **PHP 8.2+** - Ngôn ngữ lập trình
- **MySQL** - Cơ sở dữ liệu
- **Composer** - Quản lý dependencies PHP

### Frontend Technologies
- **Inertia.js** - Modern monolith approach
- **Alpine.js 3.x** - JavaScript framework nhẹ
- **Tailwind CSS 3.x** - Utility-first CSS framework
- **Flowbite 3.x** - Component library for Tailwind
- **Vite 6.x** - Build tool và development server

### Thanh toán và tích hợp
- **VNPay** - Cổng thanh toán Việt Nam
- **Stripe** - Cổng thanh toán quốc tế
- **Intervention Image** - Xử lý hình ảnh

### Development Tools
- **Laravel Breeze** - Authentication scaffolding
- **PHPStan** - Static analysis tool
- **Laravel Pint** - Code style fixer
- **Pest PHP** - Testing framework
- **Laravel Sail** - Docker development environment

### Các package quan trọng khác
- **Doctrine DBAL** - Database abstraction layer
- **Carbon** - Date/time manipulation
- **Faker** - Test data generation

## 📋 Yêu cầu hệ thống

### Yêu cầu tối thiểu
- **PHP:** 8.2 hoặc cao hơn
- **Composer:** 2.0+
- **Node.js:** 18.0+
- **NPM/Yarn:** Để quản lý frontend dependencies
- **MySQL:** 8.0+ hoặc MariaDB 10.3+
- **Web Server:** Apache/Nginx

### Khuyến nghị
- **RAM:** 2GB+ cho development
- **Disk Space:** 1GB+ cho project và dependencies
- **PHP Extensions:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## 🚀 Hướng dẫn cài đặt

### Bước 1: Clone và cài đặt dependencies

```bash
# Clone repository (nếu từ Git)
git clone <repository-url> web_booking
cd web_booking

# Hoặc nếu đã có source code, di chuyển vào thư mục dự án
cd c:\wamp64\www\web_booking

# Cài đặt PHP dependencies
composer install

# Cài đặt JavaScript dependencies
npm install
```

### Bước 2: Cấu hình môi trường

```bash
# Sao chép file environment
cp .env.example .env

# Tạo application key
php artisan key:generate
```

### Bước 3: Cấu hình database

Chỉnh sửa file `.env` với thông tin database của bạn:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web_booking
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Bước 4: Thiết lập cơ sở dữ liệu

```bash
# Tạo database tables
php artisan migrate

# Chạy seeders để tạo dữ liệu mẫu
php artisan db:seed
```

### Bước 5: Cấu hình storage và permissions

```bash
# Tạo symbolic link cho storage
php artisan storage:link

# Thiết lập quyền truy cập (Linux/macOS)
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Bước 6: Build frontend assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### Bước 7: Khởi chạy ứng dụng

```bash
# Development server
php artisan serve

# Hoặc sử dụng script tích hợp (chạy đồng thời server, queue, và vite)
composer run dev
```

Truy cập ứng dụng tại: `http://localhost:8000`

## ⚙️ Cấu hình nâng cao

### Cấu hình thanh toán

#### VNPay Setup
```env
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
```

#### Stripe Setup
```env
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
```

### Cấu hình email

```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Cấu hình Queue (tùy chọn)

```env
QUEUE_CONNECTION=database
# Hoặc sử dụng Redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 👥 Hệ thống người dùng

### Loại tài khoản
1. **Học viên (Student)** - Đặt lịch học, thanh toán, đánh giá gia sư
2. **Gia sư (Tutor)** - Quản lý lịch dạy, nhận đặt lịch, quản lý thu nhập
3. **Quản trị viên (Admin)** - Quản lý toàn bộ hệ thống

### Tính năng chính

#### Cho học viên
- Tìm kiếm và lựa chọn gia sư theo môn học, giá cả, đánh giá
- Đặt lịch học linh hoạt với gia sư
- Thanh toán trực tuyến an toàn
- Đánh giá và phản hồi về gia sư
- Theo dõi lịch sử học tập

#### Cho gia sư
- Tạo hồ sơ gia sư chuyên nghiệp
- Quản lý lịch dạy và thời gian rảnh
- Nhận và xử lý yêu cầu đặt lịch
- Theo dõi thu nhập và yêu cầu rút tiền
- Xem đánh giá từ học viên

#### Cho quản trị viên
- Quản lý người dùng và tài khoản
- Theo dõi và quản lý giao dịch
- Quản lý môn học và danh mục
- Xem báo cáo thống kê hệ thống

## 📊 Cấu trúc dự án

```
web_booking/
├── app/                    # Mã nguồn chính Laravel
│   ├── Http/Controllers/   # Controllers xử lý request
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic layer
│   ├── Repositories/      # Data access layer
│   └── Components/        # Reusable components
├── database/              # Database migrations và seeders
├── resources/
│   ├── views/            # Blade templates
│   ├── js/               # JavaScript files
│   └── css/              # CSS/SCSS files
├── routes/               # Route definitions
├── public/               # Public assets
└── storage/              # File storage
```

## 🧪 Testing

```bash
# Chạy tất cả tests
php artisan test

# Chạy tests với coverage
php artisan test --coverage

# Chạy specific test file
php artisan test tests/Feature/BookingTest.php
```

## 🔧 Development Tools

### Code Quality

```bash
# Kiểm tra code style
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse

# Format code
./vendor/bin/pint --dirty
```

### Database

```bash
# Fresh migration với seeder
php artisan migrate:fresh --seed

# Rollback migration
php artisan migrate:rollback

# Tạo factory và seeder mới
php artisan make:factory ModelFactory
php artisan make:seeder TableSeeder
```

## 🚀 Deployment

### Production Setup

1. **Server Requirements**
   - PHP 8.2+ với các extension cần thiết
   - MySQL 8.0+
   - Nginx/Apache với URL rewrite enabled
   - SSL certificate

2. **Environment Configuration**
   ```bash
   # Thiết lập production environment
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   ```

3. **Optimization Commands**
   ```bash
   # Cache configuration
   php artisan config:cache
   
   # Cache routes
   php artisan route:cache
   
   # Cache views
   php artisan view:cache
   
   # Optimize autoloader
   composer install --optimize-autoloader --no-dev
   ```

### CI/CD Pipeline

Dự án hỗ trợ automated deployment với:
- GitHub Actions workflow
- Docker containerization với Laravel Sail
- Automated testing và code quality checks

## 📞 Hỗ trợ và đóng góp

### Báo cáo lỗi
Nếu bạn phát hiện lỗi, vui lòng tạo issue với thông tin chi tiết:
- Mô tả lỗi
- Các bước tái tạo lỗi
- Environment và version thông tin
- Screenshots (nếu có)

### Đóng góp code
1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

### Coding Standards
- Tuân thủ PSR-12 coding standard
- Viết tests cho features mới
- Sử dụng meaningful commit messages
- Documentation cho các function và class quan trọng

## 📄 License

Dự án này được phát hành dưới MIT License. Xem file `LICENSE` để biết thêm chi tiết.

## 🔗 Liên kết hữu ích

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [VNPay Integration Guide](https://sandbox.vnpayment.vn/apis/)

---

**Phiên bản:** 1.0.0  
**Cập nhật cuối:** Tháng 7, 2025  
**Tác giả:** Development Team
