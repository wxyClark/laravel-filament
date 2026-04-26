FROM php:8.2-fpm AS builder

ENV TZ=Asia/Shanghai

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev libwebp-dev zip unzip libpq-dev \
    librabbitmq-dev supervisor \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mysqli gd zip bcmath opcache exif pcntl

RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install amqp && docker-php-ext-enable amqp

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php:8.2-fpm

ENV TZ=Asia/Shanghai
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev libwebp-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /usr/local/lib/php/extensions/* /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d/
COPY --from=builder /usr/bin/composer /usr/bin/composer
COPY --from=builder /usr/local/lib/php/extensions/*/redis.so /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/lib/php/extensions/*/amqp.so /usr/local/lib/php/extensions/

RUN docker-php-ext-enable redis amqp 2>/dev/null || true

RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.jit_buffer_size=100M" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/opcache.ini

RUN echo "date.timezone=Asia/Shanghai" >> /usr/local/etc/php/conf.d/date.ini \
    && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/php.ini \
    && echo "upload_max_filesize=20M" >> /usr/local/etc/php/conf.d/php.ini \
    && echo "post_max_size=25M" >> /usr/local/etc/php/conf.d/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 2>/dev/null || cp /usr/bin/composer /usr/local/bin/composer

ARG UID=1000
ARG GID=1000
RUN groupmod -g ${GID} www-data && usermod -u ${UID} -g ${GID} www-data

RUN chown -R www-data:www-data /var/www/html
