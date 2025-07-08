@echo off
echo ========================================
echo Setup ngrok cho WAMP (localhost/web_booking)
echo ========================================

echo.
echo 1. Kiem tra WAMP dang chay...
netstat -an | findstr :80 > nul
if %errorlevel% equ 0 (
    echo ✅ WAMP dang chay tren port 80
) else (
    echo ❌ WAMP khong chay hoac khong dung port 80
    pause
    exit /b 1
)

echo.
echo 2. Kiem tra ngrok...
where ngrok > nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ ngrok da duoc cai dat
    ngrok version
) else (
    echo ❌ ngrok chua duoc cai dat
    echo.
    echo Vui long cai dat ngrok:
    echo - Truy cap: https://ngrok.com/download
    echo - Download va giai nen
    echo - Them vao PATH environment
    echo.
    pause
    exit /b 1
)

echo.
echo 3. Khoi dong ngrok cho port 80...
echo.
echo QUAN TRONG:
echo - Giu cua so nay mo de ngrok hoat dong
echo - Copy URL ngrok de cau hinh VNPay
echo - URL IPN: https://your-ngrok-url.ngrok.io/web_booking/payments/vnpay/ipn
echo - URL Return: https://your-ngrok-url.ngrok.io/web_booking/payments/vnpay/return
echo.
pause

echo Bat dau ngrok...
ngrok http 80
