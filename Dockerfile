FROM php:8.3-cli

# ── System dependencies + PHP extensions ─────────────────────────────────────
RUN apt-get update && apt-get install -y \
        git curl zip unzip python3 python3-pip \
        libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring xml ctype bcmath zip gd intl \
    && rm -rf /var/lib/apt/lists/* \
    # Debian only ships 'python3', not 'python'. Create a symlink so any legacy
    # config that still has PYTHON_EXECUTABLE=python also works.
    && ln -s /usr/bin/python3 /usr/bin/python

# ── Node.js 20 ────────────────────────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ── Python engine ─────────────────────────────────────────────────────────────
COPY engine/requirements.txt ./engine/requirements.txt
RUN pip3 install -r engine/requirements.txt --break-system-packages
COPY engine/ ./engine/

# ── Laravel: PHP dependencies ─────────────────────────────────────────────────
# Copy manifest files first so this layer is only rebuilt when they change.
WORKDIR /app/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# ── Laravel: npm dependencies ─────────────────────────────────────────────────
# Separate layer so npm install is cached independently of source changes.
COPY backend/package.json backend/package-lock.json ./
RUN npm ci

# ── Laravel: copy all source files ───────────────────────────────────────────
# Invalidated whenever ANY backend file changes (Blade, PHP, CSS, JS, etc.).
# node_modules and vendor created above are NOT overwritten (COPY merges dirs).
COPY backend/ ./

# ── Laravel: build frontend assets ───────────────────────────────────────────
# Runs every time source files change. Tailwind v4 scans Blade templates here.
# Fails the build loudly if Vite does not produce a manifest — no silent CSS loss.
RUN npm run build \
    && test -f public/build/manifest.json \
    && echo "✓ Vite manifest OK" \
    && ls -lah public/build/assets/ | head -10

# ── Laravel: run post-install scripts now that source files are present ───────
RUN composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

# ── Ensure runtime storage directories exist ──────────────────────────────────
# Git only tracks .gitkeep files, not the directories themselves.
# .dockerignore excludes storage/framework/* — recreate them here.
RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             storage/logs \
             bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD sh -c "php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan storage:link || true \
    && php artisan serve --host=0.0.0.0 --port=8080"
