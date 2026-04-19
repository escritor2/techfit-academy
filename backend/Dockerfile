FROM php:8.3-cli

# Instala dependências do sistema e do PHP necessárias para o SQLite e Zip
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    && docker-php-ext-install pdo_sqlite zip

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /app

# Copia todos os arquivos do projeto para dentro do container
COPY . .

# Instala as dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Cria o arquivo SQLite vazio (se não existir)
RUN touch database/database.sqlite

# Inicia rodando as migrations e depois liga o servidor (Tudo na hora do Start para conseguir ler as Variáveis de Ambiente)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
