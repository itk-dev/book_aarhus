# Book Aarhus

## Requirements

- PHP 8.1
- RabbitMQ
- Redis
- Azure SQL database (for resources)
- MariaDB (for other data)

## Development Setup

see [documentation/development.md](documentation/development.md) for information about setting up the project for development.

## Production

See [documentation/production.md](documentation/production.md) for information about setting up the project for production.

## PRs

Before creating a PR make sure the code is ready.
The following command will apply coding standards, run tests, normalize composer.json and update the openapi spec.

```shell
docker compose exec phpfpm composer prepare-code
```

## Resource cache

Resources are cached for the `/v1/resources-all` endpoint. The resources are retrieved if the cache entry does not exist.

To update cache entries from a command:

```shell
docker compose exec phpfpm bin/console app:resource:cache --no-debug
```

## Authentication

### ApiKey Authentication

Generate an ApiKey with the following command:

```shell
docker compose exec phpfpm bin/console app:auth:create-apikey
```

To authenticate with an ApiKey add the Authentication header to each request to the api in the following way:

```shell
Authorization: Apikey [THE API KEY]
```

In the swagger UI press the "Authorize" button in the top and enter

```shell
Apikey [THE API KEY]
```

## Queue

CRUD of bookings are handled through a queue (RabbitMQ) to ensure they are correctly handled.

See [https://symfony.com/doc/current/messenger.html](https://symfony.com/doc/current/messenger.html) for symfony
messenger documentation.

When a booking request is received it is added to the queue, and handled when the queue consumes the message.

To consume messages run the following command

```shell
docker compose exec phpfpm composer queues
```

To debug the queue find the address to the rabbitmq container with

```shell
docker compose ps
```

and open it in a browser.

## Microsoft Graph

Exchange is the data owner of booking data. The communication is handled through Microsoft Graph.

See [documentation/microsoft-graph.md](documentation/microsoft-graph.md) for information about the use of Microsoft Graph.

## Booking resources in Azure SQL Edge

Resources are retrieved from an Azure SQL service.

See [documentation/azure-sql.md](documentation/azure-sql.md) for a description of how this is set up.

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

- PHP files [PHP Coding Standards Fixer](https://cs.symfony.com/)

```shell
docker compose exec phpfpm composer coding-standards-check
```

To attempt to automatically fix coding style issues

```shell
docker compose exec phpfpm composer coding-standards-apply
```

## CI

Github Actions are used to run the test suite and code style checks on all PRs.

## Versioning

We use [SemVer](http://semver.org/) for versioning.
For the versions available, see the
[tags on this repository](https://github.com/itk-dev/book_aarhus/tags).

