# Book aarhus 

## Requirements
- PHP 8.1

## Installation
```
itkdev-docker-compose composer up -d
itkdev-docker-compose composer install
```

## Setup
Create .env.local with the following settings:
```
# Get info from 1password.
CLIENT_ID=
TENANT_ID=

# This should be a microsoft service account.
USERNAME=
PASSWORD=
```

## Testing microsoft graph connection
```
symfony php bin/console app:test-microsoft-graph /me
```
