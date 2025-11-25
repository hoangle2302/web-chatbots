#!/bin/bash
# Script để kiểm tra PHP-FPM đang listen trên port nào

echo "=== Kiểm tra PHP-FPM port ==="
echo ""

echo "1. Kiểm tra PHP-FPM process và port:"
docker exec php-backend ps aux | grep php-fpm | head -3

echo ""
echo "2. Kiểm tra port nào đang listen:"
docker exec php-backend netstat -tlnp 2>/dev/null | grep -E "8000|9000" || \
docker exec php-backend ss -tlnp 2>/dev/null | grep -E "8000|9000" || \
echo "⚠️  Không thể kiểm tra port trong container"

echo ""
echo "3. Kiểm tra PHP-FPM config file:"
docker exec php-backend cat /usr/local/etc/php-fpm.d/www.conf | grep "^listen"

echo ""
echo "4. Test kết nối port 8000:"
docker exec frontend-web nc -zv php-backend 8000 2>&1 || echo "❌ Port 8000 không kết nối được"

echo ""
echo "5. Test kết nối port 9000:"
docker exec frontend-web nc -zv php-backend 9000 2>&1 || echo "❌ Port 9000 không kết nối được"

echo ""
echo "=== Kết thúc kiểm tra ==="



