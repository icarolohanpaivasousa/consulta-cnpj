# Usa imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instala dependências do sistema (sem tentar reinstalar cURL)
RUN apt-get update && \
    apt-get install -y libcurl4-openssl-dev pkg-config && \
    rm -rf /var/lib/apt/lists/*

# Copia os arquivos do projeto para o diretório do Apache
COPY . /var/www/html/

# Ativa o módulo de reescrita do Apache (opcional)
RUN a2enmod rewrite

# Define a porta exposta
EXPOSE 80
