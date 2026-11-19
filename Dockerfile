# The demo as one container image, for demo.cms-provenance.org on mittwald
# container hosting. DDEV stays the way to run the demo locally; this image
# runs the same Build/demo-setup.sh on start, see Build/container/entrypoint.sh.

# PHP with the extensions TYPO3 and the provenance extensions need. Sodium,
# mbstring and the XML extensions are part of the official image already.
FROM php:8.4-apache AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev libicu-dev libjpeg62-turbo-dev libpng-dev libwebp-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd intl mysqli opcache pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

FROM base AS builder
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
COPY packages packages
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader
COPY Build Build
COPY config config
# TYPO3's own Apache rules for the document root.
RUN cp vendor/typo3/cms-install/Resources/Private/FolderStructureTemplateFiles/root-htaccess public/.htaccess

FROM base
# mariadb-client dumps and restores the demo state (Build/demo-reset.sh),
# curl renders every page once after the setup.
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl graphicsmagick mariadb-client \
    && rm -rf /var/lib/apt/lists/*
COPY Build/container/php.ini /usr/local/etc/php/conf.d/zz-demo.ini
COPY Build/container/apache.conf /etc/apache2/sites-available/000-default.conf
# Apache runs as www-data and listens on 8080, so the image needs no root.
RUN a2enmod rewrite headers expires \
    && sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2

WORKDIR /var/www/html
COPY --from=builder /app ./
# Everything TYPO3 writes: var/ (a volume, see docker-compose.mittwald.yml)
# also holds settings.php, so the installation survives a new container.
RUN mkdir -p var public/fileadmin public/typo3temp \
    && ln -s ../../var/demo/settings.php config/system/settings.php \
    && chown -R www-data:www-data var public/fileadmin public/typo3temp config/system

# Late ARG so a new commit does not rebuild the layers above.
ARG DEMO_VERSION=unknown
RUN echo "$DEMO_VERSION" > public/demo-version.txt
ENV DEMO_CONTAINER=true \
    DEMO_VERSION=$DEMO_VERSION \
    DEMO_ADMIN_LOGIN_FILE=var/demo/admin-credentials.txt \
    DEMO_RENDER_URL=http://localhost:8080 \
    TYPO3_CONTEXT=Production

USER www-data
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=10s --start-period=5m \
    CMD curl --fail --silent --output /dev/null http://localhost:8080/demo-version.txt || exit 1
ENTRYPOINT ["Build/container/entrypoint.sh"]
