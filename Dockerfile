##############################################################################
# DataPortalV2 — Production Dockerfile
# Base: PHP 8.3 FPM (FastCGI Process Manager) on Debian Bookworm
# This image runs the Laravel backend. Nginx runs alongside it via
# docker-compose and proxies all web requests into this container.
##############################################################################

# ── Stage 1: Node.js Builder ──────────────────────────────────────────────────
# We use a separate Node.js stage purely to compile the frontend assets (CSS,
# JS, CesiumJS viewer). Once built, the compiled files are copied into the
# final PHP image. The Node.js runtime is NOT included in the final image,
# keeping the production image lean.
FROM node:20-bookworm-slim AS node_builder

WORKDIR /app

# Copy package files first for Docker layer caching
COPY package.json package-lock.json ./
RUN npm ci

# Copy all source files needed by Vite to build
RUN echo "Cache bust trigger: 2026-07-06 15:33"
COPY resources/ ./resources/
COPY vite.config.js ./
COPY tsconfig.json ./
COPY tailwind.config.js ./
COPY postcss.config.js ./
# public/build is the output directory — Vite needs public to exist
RUN mkdir -p public

# Build the production frontend assets
RUN npm run build


# ── Stage 2: PHP 8.3 FPM Production Image ────────────────────────────────────
FROM php:8.4-fpm-bookworm

# Install system dependencies required by PHP extensions and SFTP/SSH libraries
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libssl-dev \
    libssh2-1-dev \
    nginx \
    supervisor \
    openssl \
    && rm -rf /var/lib/apt/lists/*

# Install the required PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    curl \
    zip \
    bcmath \
    intl \
    pcntl \
    opcache

# Install Composer globally
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ── Set the working directory for the application ────────────────────────────
WORKDIR /var/www/html

# ── Copy Composer dependency files first for Docker layer caching ─────────────
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# ── Copy the rest of the application source code ─────────────────────────────
COPY . .

# ── Copy the compiled frontend assets from the Node.js build stage ───────────
COPY --from=node_builder /app/public/build ./public/build

# ── Run post-install scripts now that all files are in place ─────────────────
RUN composer run-script post-autoload-dump || true

# ── Set correct file permissions for Laravel ─────────────────────────────────
# www-data is the user that PHP-FPM runs as inside the container
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/public/data \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/public/data

# ── Create dummy self-signed certificate for local development fallback ──────
RUN mkdir -p /etc/nginx/certs \
    && openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
       -keyout /etc/nginx/certs/dataportal.pem \
       -out /etc/nginx/certs/dataportal.pem \
       -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# ── Copy production configuration files ──────────────────────────────────────
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN rm -f /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-dataportal.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/dataportal.ini
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ── Create the Nitro temporary chunk storage directory ───────────────────────
RUN mkdir -p /var/www/nitro_storage \
    && chown www-data:www-data /var/www/nitro_storage \
    && chmod 775 /var/www/nitro_storage

# Expose port 80 (Nginx listens here inside the container)
EXPOSE 80

# The entrypoint script runs artisan commands then starts supervisor
ENTRYPOINT ["/entrypoint.sh"]
