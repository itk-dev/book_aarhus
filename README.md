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
