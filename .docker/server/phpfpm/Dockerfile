#FROM itkdev/php8.1-fpm:alpine
# THIS IS A HACK
FROM itkdev/php8.1-fpm:alpine-1.1.15

# Install dependencies for building packages
RUN apk --update add --no-cache --virtual .build-deps \
    autoconf \
    g++ \
    make \
    gnupg \
    unixodbc-dev \
# https://docs.microsoft.com/en-us/sql/connect/odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver15#alpine17
    && curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.8.1.1-1_amd64.apk \
    && curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.8.1.1-1_amd64.sig \
    && curl https://packages.microsoft.com/keys/microsoft.asc | gpg --import - \
    && gpg --verify msodbcsql17_17.8.1.1-1_amd64.sig msodbcsql17_17.8.1.1-1_amd64.apk \
# Reply "yes" to accept the license
    && yes | apk add --allow-untrusted msodbcsql17_17.8.1.1-1_amd64.apk \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && apk del .build-deps \
# Clean up
    && rm msodbcsql17_17.8.1.1-1_amd64.*
