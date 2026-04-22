FROM php:8.4-fpm-alpine

# Cài đặt các thư viện hệ thống cần thiết cho PHP và SQLite
RUN apk update && apk add --no-cache \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    curl \
    sqlite-dev

# Cài đặt các extension PHP cần thiết cho Laravel
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Lấy Composer bản mới nhất
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Phân quyền cho user mặc định của Alpine
RUN chown -R www-data:www-data /var/www

USER www-data
