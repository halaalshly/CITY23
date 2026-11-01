FROM php:8.2-apache
# تثبيت إضافة mysqli لدعم قواعد البيانات
RUN docker-php-ext-install mysqli pdo pdo_mysql
COPY . /var/www/html/
EXPOSE 80
