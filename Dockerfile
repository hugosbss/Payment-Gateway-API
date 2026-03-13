FROM laravelsail/php82-composer

RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-install intl pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*