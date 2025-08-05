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
docker compose exec phpfpm bin/console doctrine:fixtures:load --group=AppFixtures
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
