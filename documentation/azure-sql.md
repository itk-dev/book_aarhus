# Azure SQL

Booking resources - e.g. anything that can be booked by the system - are exposed in an Azure SQL Edge database. This
database is read only.

Info:
See [How to Work with multiple Entity Managers and Connections](https://symfony.com/doc/current/doctrine/multiple_entity_managers.html)
in the Symfony docs for
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

```shell
bin/console doctrine:fixtures:load --em=resources --group=ResourceFixtures
```

### Development access to the real Azure DB

If you need to access the actual azure database from the development set up a few
steps are needed because the Azure DB is behind a firewall with an IP filter.
The `phpfpm` container in `docker-compose.yml` has ab `extra_hosts` entry. This
allows the container to tunnel to the Azure DB through the STG server. Execute
the following commands to enable the tunnel from the container (requires vpn):

```shell
# Force recreate to ensure 'extra_hosts' are set
# Only needed if you have up'ed the container before this was added
docker compose up -d --force-recreate

# Copy ssh keys to container
docker compose exec --user root phpfpm mkdir -p /root/.ssh
docker compose cp ~/.ssh/id_rsa phpfpm:/root/.ssh/id_rsa
docker compose cp ~/.ssh/id_rsa-cert.pub phpfpm:/root/.ssh/id_rsa-cert.pub
# Create ssh tunnel using IPv4 (`-4`)
docker compose exec phpfpm ssh -4 -f deploy@dmzwebstgitk03.dmz.aarhuskommune.dk -L 1433:externalbooking.database.windows.net:1433 -N
```

This will allow your local docker container to connect directly with the Azure database.
To do this

- Set the relevant `BOOKING_RESOURCES_DATABASE_XYZ` values in the `.env.local` file.
- Uncomment `authentication: ActiveDirectoryPassword` in `doctrine.yaml:21`

#### Test for access

A command can be used to access data for a resource with the given email:

```shell
bin/console app:resource:display resource@aarhus.dk
```

#### To troubleshoot this setup do

Open shell in container

```shell
docker compose exec phpfpm bash
```

Ensure that "extra_hosts" entry is set. You should see a line with "127.0.0.1 externalbooking.database.windows.net"

```shell
cat /etc/hosts
```

Ensure that the SSH tunnel is running. You should see a line similar to

```shell
# root 121 1 0 08:20 ? 00:00:00 ssh -4 -f deploy@admwebstgitk01.admnonwin.aarhuskommune.dk -L 1433:externalbooking.database.windows.net:1433 -N 
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

__Note__: Command is
deprecated: [https://symfony.com/doc/current/doctrine/reverse_engineering.html](https://symfony.com/doc/current/doctrine/reverse_engineering.html)
