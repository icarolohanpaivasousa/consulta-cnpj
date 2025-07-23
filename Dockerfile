# Usa imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instala a extensão cURL
RUN docker-php-ext-install curl

# Copia todos os arquivos do projeto para o diretório do Apache
COPY . /var/www/html/

# Ativa o módulo de reescrita do Apache (opcional)
RUN a2enmod rewrite

# Define porta exposta
EXPOSE 80
