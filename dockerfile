# Sử dụng image PHP 8 với Apache
FROM php:8.2.12-apache

# Cài đặt các gói phụ thuộc cần thiết
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql mysqli gettext \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Bật mod_rewrite để hỗ trợ .htaccess
RUN a2enmod rewrite

RUN a2enmod headers

# Cấu hình ServerName để tránh cảnh báo
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Bật chế độ hiển thị lỗi trong PHP (dùng khi phát triển, không nên dùng trên production)
RUN echo "display_errors=On" >> /usr/local/etc/php/conf.d/docker-php.ini

# Copy mã nguồn ứng dụng vào thư mục làm việc của Apache
WORKDIR /var/www/html
COPY . /var/www/html

# Cài đặt thư viện eclo/app sau khi copy mã nguồn
RUN composer require eclo/app

# Mở cổng 80 cho ứng dụng
EXPOSE 80

# Thiết lập quyền truy cập
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Chạy Apache khi container khởi động
CMD ["apache2-foreground"]
