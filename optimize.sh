#!/bin/bash

echo "🚀 Bắt đầu tối ưu hóa hiệu suất website..."

# 1. Clear and optimize caches
echo "🧹 Xóa cache cũ..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Optimize for production
echo "⚡ Tối ưu hóa cho production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 3. Run database migrations and optimizations
echo "🗄️ Chạy migration và tối ưu database..."
php artisan migrate --force
php artisan db:seed --class=DatabaseOptimizationSeeder --force

# 4. Optimize Composer autoloader
echo "📦 Tối ưu hóa Composer autoloader..."
composer dump-autoload --optimize --classmap-authoritative

# 5. Build and optimize frontend assets
echo "🎨 Build frontend assets..."
npm run build

# 6. Optimize images (if imagemagick is available)
if command -v convert &> /dev/null; then
    echo "🖼️ Tối ưu hóa hình ảnh..."
    find public/storage -name "*.jpg" -exec convert {} -quality 80 {} \;
    find public/storage -name "*.png" -exec convert {} -quality 80 {} \;
else
    echo "⚠️ ImageMagick không được cài đặt, bỏ qua tối ưu hóa hình ảnh"
fi

# 7. Set proper file permissions
echo "🔒 Thiết lập quyền file..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 8. Restart services (if using systemd)
if systemctl is-active --quiet php8.2-fpm; then
    echo "🔄 Khởi động lại PHP-FPM..."
    sudo systemctl restart php8.2-fpm
fi

if systemctl is-active --quiet nginx; then
    echo "🔄 Khởi động lại Nginx..."
    sudo systemctl restart nginx
fi

# 9. Warm up application
echo "🔥 Làm nóng ứng dụng..."
curl -s http://localhost > /dev/null || echo "⚠️ Không thể truy cập localhost"

echo "✅ Hoàn thành tối ưu hóa hiệu suất!"
echo ""
echo "🎯 Các bước tiếp theo:"
echo "1. Kiểm tra website tại: http://localhost"
echo "2. Chạy lệnh: php artisan queue:work (nếu dùng queue)"
echo "3. Thiết lập cron job cho: php artisan schedule:run"
echo "4. Cân nhắc dùng Redis cache cho production"
echo "5. Thiết lập CDN cho static assets"
