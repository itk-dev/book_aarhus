# Production

## .env.local

Add the following fields to `.env.local` with relevant values:

```shell
APP_ENV=prod
APP_SECRET="<INSERT A NEW SECRET>"

MICROSOFT_GRAPH_TENANT_ID="<INSERT TENANT ID>"
MICROSOFT_GRAPH_CLIENT_ID="<INSERT CLIENT ID>"
MICROSOFT_GRAPH_SERVICE_ACCOUNT_NAME="<INSERT SERVICE ACCOUNT NAME>"
MICROSOFT_GRAPH_SERVICE_ACCOUNT_USERNAME="<INSERT SERVICE ACCOUNT EMAIL>"
MICROSOFT_GRAPH_SERVICE_ACCOUNT_PASSWORD="<INSERT SERVICE ACCOUNT PASSWORD>"

BOOKING_RESOURCES_DATABASE_HOST="<INSERT AZURE SQL HOST>"
BOOKING_RESOURCES_DATABASE_USER="<INSERT SERVICE ACCOUNT EMAIL>"
BOOKING_RESOURCES_DATABASE_PASSWORD="<INSERT SERVICE ACCOUNT PASSWORD>"

ADMIN_NOTIFICATION_EMAIL="<INSERT MAIL TO RECEIVE ADMIN NOTIFICATIONS>"
EMAIL_FROM_ADDRESS="<INSERT FROM MAIL FOR NOTIFICATIONS>"
```

## Job handling

Setup RabbitMQ on server and use supervisor to make sure the queue is running.

See [https://symfony.com/doc/current/messenger.html#deploying-to-production](https://symfony.com/doc/current/messenger.html#deploying-to-production).

For example use Supervisor ([https://symfony.com/doc/current/messenger.html#supervisor-configuration](https://symfony.com/doc/current/messenger.html#supervisor-configuration))

## Caching of resources

Resources are cached for the `/v1/resources-all` endpoint. 

Set up a cronjob to refresh the cache, e.g. every 25 minutes. The cache has a default lifetime of 30 minutes.

```shell
bin/console app:resource:cache --env=prod --no-debug
```

## Installing/Updating

```shell
# Install dependencies
composer install --no-dev -o

# Migrate the local database.
bin/console doctrine:migrations:migrate
```
