# JavaScript Module System

Hệ thống này cho phép tách JavaScript từ các file Blade thành các file riêng biệt để dễ quản lý và bảo trì.

## Cấu trúc thư mục

```
public/js/
├── app-modules.js          # JavaScript Module Manager
└── pages/                  # Các file JavaScript riêng biệt
    ├── booking-create.js
    ├── admin-payouts-index.js
    ├── tutors-show.js
    └── ...
```

## Cách sử dụng

### 1. Tự động load (Khuyến nghị)

JavaScript sẽ tự động được load dựa trên route hiện tại. Chỉ cần thêm data attribute vào body tag:

```blade
<body data-page-module="bookings.create">
```

### 2. Load thủ công

```javascript
// Load một module
JSManager.loadModule('bookings.create');

// Load nhiều modules
JSManager.loadModules(['bookings.create', 'components.language-switcher']);
```

### 3. Trong Blade templates

Thay vì inline JavaScript:

```blade
@push('scripts')
<script>
    // JavaScript code here
</script>
@endpush
```

Sử dụng file riêng:

```blade
@push('scripts')
    <script src="{{ asset('js/pages/your-page.js') }}"></script>
@endpush
```

## Quy tắc đặt tên file

- Admin pages: `admin-[section]-[action].js`
- Booking pages: `bookings-[action].js`
- Tutor pages: `tutors-[action].js`
- Components: `components-[name].js`

## Lợi ích

1. **Tách biệt concerns**: JavaScript riêng biệt khỏi HTML
2. **Cache tốt hơn**: Browser có thể cache JavaScript files
3. **Dễ debug**: Dễ dàng tìm và sửa lỗi
4. **Tái sử dụng**: Có thể sử dụng chung code giữa các trang
5. **Minification**: Có thể minify các file JavaScript riêng biệt

## Tools

### Extract JavaScript Script

Chạy script để tự động tách JavaScript từ Blade files:

```bash
php scripts/extract-js.php
```

Script sẽ:
- Tìm tất cả file Blade có chứa JavaScript
- Tách JavaScript ra thành file riêng
- Tạo backup của file gốc
- Cập nhật Blade file để include JavaScript file mới

### Khôi phục từ backup

Nếu có lỗi, bạn có thể khôi phục từ backup:

```bash
# Ví dụ khôi phục file booking create
cp resources/views/bookings/create.blade.php.backup.2025-07-15-03-51-33 resources/views/bookings/create.blade.php
```

## Best Practices

1. **Sử dụng DOMContentLoaded**: Luôn wrap code trong `DOMContentLoaded` event
2. **Error handling**: Thêm error handling cho các AJAX calls
3. **Namespace**: Sử dụng IIFE để tránh global namespace pollution
4. **Comments**: Thêm comments cho complex logic
5. **Validation**: Validate DOM elements tồn tại trước khi sử dụng

## Ví dụ JavaScript module structure

```javascript
/**
 * Booking Create Page JavaScript
 * Handles date/time input formatting and validation
 */
document.addEventListener('DOMContentLoaded', function () {
    // Check if required elements exist
    const form = document.querySelector('form');
    if (!form) {
        console.warn('Booking form not found');
        return;
    }

    // Your JavaScript code here
    
    // Error handling
    form.addEventListener('submit', function(e) {
        try {
            // Validation logic
        } catch (error) {
            console.error('Form submission error:', error);
            e.preventDefault();
        }
    });
});
```

## Troubleshooting

### JavaScript không load

1. Kiểm tra console browser cho errors
2. Đảm bảo file path đúng
3. Kiểm tra `app-modules.js` đã được include trong layout

### Module không tự động load

1. Kiểm tra `data-page-module` attribute
2. Đảm bảo module name có trong `pageModules` mapping
3. Kiểm tra URL routing

### Performance Issues

1. Sử dụng lazy loading cho non-critical modules
2. Minify JavaScript files trong production
3. Combine small related modules