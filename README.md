# Book Aarhus 

## Requirements

- PHP 8.1

## Installation

```bash
# Up the docker containers
itkdev-docker-compose up -d

# Install the php packages
itkdev-docker-compose composer install

# Migrate the database
itkdev-docker-compose bin/console doctrine:migrations:migrate
```

The api can be accessed at `/api/`.

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

### Composer normalizer

[Composer normalize](https://github.com/ergebnis/composer-normalize) is used for
formatting `composer.json`

```shell
docker compose exec phpfpm composer normalize
```

### Coding Standards

The following command let you test that the code follows
the coding standard for the project.

* PHP files [PHP Coding Standards Fixer](https://cs.symfony.com/)

```shell
docker compose exec phpfpm composer check-coding-standards
```

To attempt to automatically fix coding style issues

```sh
docker compose exec phpfpm composer apply-coding-standards
```

## CI

Github Actions are used to run the test suite and code style checks on all PRs.

## Versioning

We use [SemVer](http://semver.org/) for versioning.
For the versions available, see the
[tags on this repository](https://github.com/itk-dev/openid-connect/tags).
