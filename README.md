# Book Aarhus 

## Requirements

- PHP 8.1

## Development Setup

A `docker-compose.yml` file is included in this project.
To install the dependencies you can run

```shell
# Up the docker containers
docker compose up -d

# Install dependencies
docker compose exec phpfpm composer install

# Run migrations
docker compose exec phpfpm bin/console doctrine:migrations:migrate
```

The api can be accessed at `/`.

## PRs

Before creating a PR make sure the code is ready.
The following command will apply coding standards, run tests, normalize composer.json and update the openapi spec.

```
docker compose exec phpfpm composer prepare-code
```

## Microsoft Graph

The application relies on Microsoft Graph to handle free/busy and booking requests.

To enable this the following environment variables should be set in `.env.local`:

```
###> App ###
MICROSOFT_GRAPH_TENANT_ID=""
MICROSOFT_GRAPH_CLIENT_ID=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_NAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_USERNAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_PASSWORD=""
###< App ###
```

A command is available to test requests in Microsoft Graph:

```shell
docker compose exec phpfpm bin/console app:graph:test
```

## Booking resources in Azure SQL Edge
Booking resources - e.g. anything that can be booked by the system - are exposed 
in an Azure SQL Edge database. This database is read only.

Info: See [How to Work with multiple Entity Managers and Connections](https://symfony.com/doc/current/doctrine/multiple_entity_managers.html) in the Symfony docs for
general information on working with multiple databases.

For local development an `azure-sql-edge` container is part of the docker setup.

Doctrine is configured with two connections `default` and `azure_sql`. And with
two ORM configurations `default` and `resources`. Entity classes are split in two 
namespaces `App\Entity\Main` (default) and `App\Entity\Resources`.  

When working with doctrine commands
the `default` values will be used unless you actively specify otherwise. Depending 
on the command you will need to add `--connection=azure_sql` or `--em=resources`
to work with the azure sql database:
```shell
bin/console doctrine:database:create --connection=azure_sql
bin/console doctrine:schema:validate --em=resources
```  

For dependency injection you can use `EntityManagerInterface $defaultEntityManager` 
or `EntityManagerInterface $resourcesEntityManager` to have the relevant entitymanager 
injected.

### Migrations for development resource db

To ease development, migrations are provided for the azure sql container.
They are added to the `migrations_resources` directory. A special configuration file
is supplied for this.

To use the migrations supply the `--em` and `--configuration` parameters to
doctrine migrations commands as below

```shell
# Migrate
bin/console doctrine:migrations:migrate --em=resources --configuration=config/config-migrations/doctrine-migrations-resources.yaml

# Status
bin/console doctrine:migrations:status --em=resources --configuration=config/config-migrations/doctrine-migrations-resources.yaml

# Diff
bin/console doctrine:migrations:diff --em=resources --configuration=config/config-migrations/doctrine-migrations-resources.yaml
```

Fixtures are supplied to populate the development resources database.

```
bin/console doctrine:fixtures:load --em=resources --group=ResourceFixtures
```

### Development access to the real Azure DB
If you need to access the actaul azure database from the development set up a few 
steps are needed because the Azure DB is behind a firewall with an IP filter. 
The `phpfpm` container in `docker-compose.yml` has ab `extra_hosts` entry. This 
allows the container to tunnel to the Azure DB through the STG server. Execute 
the following commands to enable the tunnel from the container (requires vpn):
```shell
# Force recreate to ensure 'extra_hosts' are set
# Only needed if you have up'ed the container before this was added
docker compose up -d --force-recreate

# Copy ssh keys to container
docker compose exec phpfpm mkdir -p /root/.ssh
docker compose cp ~/.ssh/id_rsa phpfpm:/root/.ssh/id_rsa
docker compose cp ~/.ssh/id_rsa-cert.pub phpfpm:/root/.ssh/id_rsa-cert.pub
# Create ssh tunnel using IPv4 (`-4`)
docker compose exec phpfpm ssh -4 -f deploy@admwebstgitk01.admnonwin.aarhuskommune.dk -L 1433:externalbooking.database.windows.net:1433 -N
```

This will allow your local docker container to connect directly with the Azure database. 
To do this 
* set the relevant `BOOKING_RESOURCES_DATABASE_XYZ` values in the `.env.local` file.
* Uncomment `authentication: ActiveDirectoryPassword` in `doctrine.yaml:21`

To troubleshoot this setup do 
```shell
# Open shell in container
docker compose exec phpfpm bash

# Ensure that "extra_hosts" entry is set
# You should see a line with "127.0.0.1	externalbooking.database.windows.net"
cat /etc/hosts

# Ensure that the SSH tunnel is running
# You should see a line similar to
# root       121     1  0 08:20 ?        00:00:00 ssh -4 -f deploy@admwebstgitk01.admnonwin.aarhuskommune.dk -L 1433:externalbooking.database.windows.net:1433 -N 
ps -ef | grep ssh
```

### Entities from DB schema
To generate basic entity files from the DB schema you can use the following command when connected to 
the real Azure DB
```shell
bin/console doctrine:mapping:import "App\Entity\Resources" annotation --path=src/Entity/Resources --em=resources
```

Please note that this will NOT give you fully functional classes. Most importantly it will NOT
recognise relations and foreign keys. These will be mapped as simple `int` fields. Also getters
and setters are not generated.

(Note: Command is deprecated: https://symfony.com/doc/current/doctrine/reverse_engineering.html)

## Authentication

### ApiKey Authentication

Generate an ApiKey with the following command:

```shell
docker compose exec phpfpm bin/console app:auth:create-apikey
```

To authenticate with an ApiKey add the Authentication header to each request to the api in the following way:

```
Authorization: Apikey [THE API KEY]
```

In the swagger UI press the "Authorize" button in the top and enter

```
Apikey [THE API KEY]
```

## Queue

CRUD of bookings are handled through a queue (RabbitMQ) to ensure they are correctly handled.

See https://symfony.com/doc/current/messenger.html for symfony messenger documentation.

When a booking request is received it is added to the queue, and handled when the queue consumes the message.

To consume messages run the following command

```shell
docker compose exec phpfpm composer queues
```

### Production

Make sure proper production handling is set up.

See https://symfony.com/doc/current/messenger.html#deploying-to-production.

For example use Supervisor (https://symfony.com/doc/current/messenger.html#supervisor-configuration).

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
