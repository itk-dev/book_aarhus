# Production

## .env.local

Add the following fields to `.env.local` with relevant values:

```text
APP_ENV=prod
APP_SECRET="<INSERT A NEW SECRET>"

MICROSOFT_GRAPH_TENANT_ID="<INSERT TENANT ID>"
MICROSOFT_GRAPH_CLIENT_ID="<INSERT CLIENT ID>"
MICROSOFT_GRAPH_SERVICE_ACCOUNT_NAME="<INSERT SERVICE ACCOUNT NAME>"
MICROSOFT_GRAPH_SERVICE_ACCOUNT_USERNAME="<INSERT SERVICE ACCOUNT EMAIL>"
MICROSOFT_GRAPH_SERVICE_ACCOUNT_PASSWORD="<INSERT SERVICE ACCOUNT PASSWORD>"

RESOURCES_LIST="<INSERT RESOURCE LIST ENDPOINT>"
RESOURCES_LOCATIONS="<INSERT LOCATION ENDPOINT>"
RESOURCES_CVR_WHITELIST="<INSERT CVR WHITELIST ENDPOINT>"
RESOURCES_OPENING_HOURS="<INSERT OPEN HOURS ENDPOINT>"
RESOURCES_HOLIDAY_OPENING_HOURS ="<INSERT HOLIDAY OPEN HOURS ENDPOINT>"

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

## Releasing new versions

Run the deploy script on the server for the relevant git tag. E.g.

```shell
../scripts/deploy 1.1.0
```

Replace `1.1.0` with the tag you are releasing.

### Steps explained

1. Stop containers
2. Checkout the relevant git tag
3. Pull docker images
4. Run composer install
5. Run migrations
6. Clear cache
7. Start containers

**Important**: The job consumers MUST be stopped and restarted when doing releases. If they are not
we risk having a consumer of version a previous version process messages from the current version (E.g. `1.0.0` process
messages from version `1.1.0`.). This is most easily done by simply restarting the containers.
