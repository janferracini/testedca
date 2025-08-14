# Use uma imagem oficial do PHP como imagem base
FROM php:8.1-fpm

# Instale as dependências do sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libzip-dev

# Instale as extensões PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl gd

# Instale o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie o código do aplicativo para o diretório de trabalho
COPY . /var/www

# Defina o diretório de trabalho
WORKDIR /var/www

# Dê permissão de escrita para o diretório de cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exponha a porta 9000 e inicie o servidor PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
