FROM php:8.5-cli
RUN apt-get update && apt-get install -y git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml zip bcmath gd \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN printf '#!/bin/sh\nphp artisan config:clear\nphp artisan migrate --force\nphp artisan db:seed --force\nexec php -S 0.0.0.0:${PORT:-8080} -t public\n' > /start.sh && chmod +x /start.sh
CMD ["/start.sh"]
