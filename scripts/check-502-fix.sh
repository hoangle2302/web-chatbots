#!/bin/bash
# Script để kiểm tra và fix lỗi 502 Bad Gateway

echo "=== Kiểm tra lỗi 502 Bad Gateway ==="
echo ""

# 1. Kiểm tra containers đang chạy
echo "1. Kiểm tra containers:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "php-backend|frontend-web|mysql"

echo ""
echo "2. Kiểm tra PHP-FPM có đang listen trên port 8000:"
docker exec php-backend netstat -tlnp 2>/dev/null | grep 8000 || docker exec php-backend ss -tlnp 2>/dev/null | grep 8000 || echo "⚠️  Không thể kiểm tra port trong container"

echo ""
echo "3. Kiểm tra PHP-FPM process:"
docker exec php-backend ps aux | grep php-fpm | head -3

echo ""
echo "4. Kiểm tra network connectivity từ Nginx đến PHP-FPM:"
docker exec frontend-web ping -c 2 php-backend 2>/dev/null || echo "⚠️  Không thể ping php-backend từ nginx container"

echo ""
echo "5. Kiểm tra Nginx error log:"
docker exec frontend-web tail -20 /var/log/nginx/error.log

echo ""
echo "6. Test PHP-FPM connection:"
docker exec frontend-web bash -c "echo -e 'GET /api/health.php HTTP/1.1\r\nHost: localhost\r\n\r\n' | nc php-backend 8000" || echo "⚠️  Không thể kết nối đến php-backend:8000"

echo ""
echo "=== Kết thúc kiểm tra ==="
echo ""
echo "Nếu PHP-FPM không chạy, thử:"
echo "  docker restart php-backend"
echo ""
echo "Nếu vẫn lỗi, kiểm tra PHP-FPM logs:"
echo "  docker logs php-backend --tail 50"



