FROM php:8.2-cli

# Instalar dependencias del sistema y extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev

# Instalar extensiones de PHP requeridas por Laravel
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www

# Copiar todo el proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Dar permisos a las carpetas de almacenamiento
RUN chown -R www-data:www-data storage bootstrap/cache

# Exponer el puerto
EXPOSE 8080

# Ejecutar migraciones (opcional, por seguridad mejor hacerlo manual) y levantar el servidor
CMD php artisan serve --host=0.0.0.0 --port=8080