# Usa imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instala dependências do sistema e extensão curl do PHP
RUN apt-get update && \
    apt-get install -y libcurl4-openssl-dev pkg-config && \
    docker-php-ext-install curl

# Copia os arquivos do projeto para o diretório do Apache
COPY . /var/www/html/

# Ativa o módulo de reescrita do Apache (opcional)
RUN a2enmod rewrite

# Define a porta exposta
EXPOSE 80
