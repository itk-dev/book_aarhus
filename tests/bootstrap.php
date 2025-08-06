<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Clear cache.
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup --no-debug',
    $_ENV['APP_ENV'],
    __DIR__
));

// Create database if it does not exist.
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" --env=test doctrine:database:create --no-interaction --if-not-exists --quiet',
    $_ENV['APP_ENV'],
    __DIR__
));

// Migrate to latest database schema.
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" --env=test doctrine:migrations:migrate --no-interaction --quiet',
    $_ENV['APP_ENV'],
    __DIR__
));

// Load database fixtures.
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" --env=test doctrine:fixtures:load --quiet',
    $_ENV['APP_ENV'],
    __DIR__
));
