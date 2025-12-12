# Use official PHP 8.2 CLI image on Alpine
FROM php:8.4-cli-alpine

# Install build deps + linux-headers, build xdebug, then remove build deps again
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --no-cache $PHPIZE_DEPS linux-headers

# Xdebug config
RUN { \
  echo "zend_extension=xdebug.so"; \
  echo "xdebug.mode=debug,develop,coverage"; \
  echo "xdebug.start_with_request=yes"; \
  echo "xdebug.discover_client_host=false"; \
  echo "xdebug.client_host=host.docker.internal"; \
  echo "xdebug.client_port=9003"; \
  echo "xdebug.log=/tmp/xdebug.log"; \
} > /usr/local/etc/php/conf.d/xdebug.ini

# Install basic tools you’ll want in a dev container
RUN apk add --no-cache \
        bash \
        git \
        unzip \
        zip \
        curl

# (Optional) install extra PHP extensions if your lib needs them
# RUN docker-php-ext-install mbstring intl pdo_mysql

# Copy Composer from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Default command (can be overridden in docker-compose or docker run)
CMD ["php", "-v"]
