# Book Aarhus 

## Requirements

- PHP 8.1

## Development Setup

A `docker-compose.yml` file is included in this project.
To install the dependencies you can run

```shell
docker compose up -d
docker compose exec phpfpm composer install

# Run migrations
docker compose exec phpfpm bin/console doctrine:migrations:migrate
```

The api can be accessed at `/api/`.

## Microsoft Graph

The application relies on Microsoft Graph to handle free/busy and booking requests.

To enable this the following environment variables should be set in `.env.local`:

```shell
MICROSOFT_GRAPH_TENANT_ID=""
MICROSOFT_GRAPH_CLIENT_ID=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_USERNAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_PASSWORD=""
```

A command is available to test requests in Microsoft Graph:

```shell
docker compose exec phpfpm bin/console app:graph:test
```

## Doctrine Fixtures

Since the doctrine fixtures bundle may not be included in the project by default, start by requiring it via composer:

```shell
composer require orm-fixtures --dev
```

Make your fixtures file:

```shell
itkdev-docker-compose bin/console make:fixtures
```

Add your dummy data:
More on format: https://symfonycasts.com/screencast/symfony-doctrine/fixtures

Finally, load the fixtures:

```shell
itkdev-docker-compose bin/console doctrine:fixtures:load
```

## OpenAPI specification

The OpenAPI specification is committed to this repo as `public/api-spec-v1.yaml`
and as `public/api-spec-v1.json`.

A CI check will compare the current API implementation to the spec. If they
are different the check will fail.

If a PR makes _planned_ changes to the spec, the committed file must be updated:

```shell
docker compose exec phpfpm composer update-api-spec
```

If these are _breaking_ changes the API version must be changed accordingly.

## Composer normalizer

[Composer normalize](https://github.com/ergebnis/composer-normalize) is used for
formatting `composer.json`

```shell
docker compose exec phpfpm composer normalize
```

## Coding Standards

The following command let you test that the code follows
the coding standard for the project.

* PHP files [PHP Coding Standards Fixer](https://cs.symfony.com/)

```shell
docker compose exec phpfpm composer check-coding-standards
```

To attempt to automatically fix coding style issues

```shell
docker compose exec phpfpm composer apply-coding-standards
```

## CI

Github Actions are used to run the test suite and code style checks on all PRs.

## Versioning

We use [SemVer](http://semver.org/) for versioning.
For the versions available, see the
[tags on this repository](https://github.com/itk-dev/book_aarhus/tags).
