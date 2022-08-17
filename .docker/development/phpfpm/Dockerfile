FROM itkdev/php8.1-fpm:latest

# https://docs.microsoft.com/en-us/sql/connect/odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver15#ubuntu17
# https://docs.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver15#installing-on-ubuntu

RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
RUN apt-get update -y \
    && ACCEPT_EULA=Y apt-get install -y \
    msodbcsql17 \
    php8.1-dev \
    php8.1-xml \
    unixodbc-dev \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install sqlsrv pdo_sqlsrv

RUN bash -c 'printf "; priority=20\nextension=sqlsrv.so\n" > /etc/php/8.1/mods-available/sqlsrv.ini' \
    && bash -c 'printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /etc/php/8.1/mods-available/pdo_sqlsrv.ini' \
    && phpenmod sqlsrv pdo_sqlsrv