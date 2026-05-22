# ============================================================
#  Dockerfile - Hasami E-Commerce (PHP 8.2 + Apache)
#  Dành cho triển khai trên Render.com (Docker Runtime)
# ============================================================

FROM php:8.2-apache

# 1. Cài đặt extension PHP cần thiết
RUN docker-php-ext-install pdo pdo_mysql

# 2. Kích hoạt Apache mod_rewrite (cho .htaccess URL rewriting)
RUN a2enmod rewrite

# 3. Copy toàn bộ source code vào container
COPY . /var/www/html/

# 4. Cấu hình Apache: trỏ Document Root vào /var/www/html/public
#    và bật AllowOverride All để .htaccess hoạt động đúng
RUN sed -ri -e 's|/var/www/html|/var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's|/var/www/html|/var/www/html/public|g' \
        /etc/apache2/apache2.conf \
    && echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' \
        >> /etc/apache2/apache2.conf

# 5. Cấp quyền cho www-data
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 6. Expose port 80
EXPOSE 80
