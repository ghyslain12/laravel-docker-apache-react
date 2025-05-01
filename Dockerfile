FROM php:8.1-apache

# Installation des dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip

# Activation du module rewrite d'Apache
RUN a2enmod rewrite

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration Apache
COPY ./docker/apache/apache.conf /etc/apache2/sites-available/000-default.conf

# Définition du répertoire de travail
WORKDIR /var/www

# Copie du projet Laravel existant
COPY ./project /var/www

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer les dépendances Composer (résout le problème d'autoload)
RUN composer install --no-scripts --no-interaction || true

# Définir les permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www
	
RUN php artisan cache:clear

# Exposition du port
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"]



