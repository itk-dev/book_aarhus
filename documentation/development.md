# Development Setup

## .env.local

Add the following fields to `.env.local` with relevant values:

```shell
MICROSOFT_GRAPH_TENANT_ID=""
MICROSOFT_GRAPH_CLIENT_ID=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_NAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_USERNAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_PASSWORD=""

ADMIN_NOTIFICATION_EMAIL="admin-notifications@bookaarhus.local.itkdev.dk"
EMAIL_FROM_ADDRESS="no-reply@bookaarhus.local.itkdev.dk"

# For the app:resources:update command, use the following values to used the fixtures in public/fixtures.
RESOURCES_LIST=http://bookaarhus-nginx-1.frontend:8080/fixtures/resources/resources.json
RESOURCES_LOCATIONS=http://bookaarhus-nginx-1.frontend:8080/fixtures/resources/locations.json
RESOURCES_CVR_WHITELIST=http://bookaarhus-nginx-1.frontend:8080/fixtures/resources/cvr_whitelist.json
RESOURCES_OPENING_HOURS=http://bookaarhus-nginx-1.frontend:8080/fixtures/resources/opening_hours.json
RESOURCES_HOLIDAY_OPENING_HOURS=http://bookaarhus-nginx-1.frontend:8080/fixtures/resources/holiday_opening_hours.json
```

## Setup

A `docker-compose.yml` file is included in this project.

```shell
# Up the docker containers
docker compose up -d

# Install dependencies
docker compose exec phpfpm composer install

# Run migrations
docker compose exec phpfpm bin/console doctrine:migrations:migrate

# Load fixtures
docker compose exec phpfpm bin/console doctrine:fixtures:load
```

## Testing

Run tests with

```shell
docker compose exec phpfpm composer tests
```

Get coverage report to `coverage/` folder

```shell
docker compose exec phpfpm composer tests-coverage
```

## Docker setup

The docker setup consists of:

* A MariaDB container for local data.
* A Mailhog container for intercepting emails.
* A Redis container for caching.
* A phpfpm and nginx container.
