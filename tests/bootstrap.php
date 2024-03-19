<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// executes the "php bin/console cache:clear" command
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup',
    $_ENV['APP_ENV'],
    __DIR__
));

echo "Create db \n";
passthru(sprintf(
    'php "%s/../bin/console" doctrine:database:create --env=test',
    __DIR__
));

echo "Updating schema \n";
passthru(sprintf(
    '"%s/../bin/console" doctrine:schema:update --force --env=test',
    __DIR__
));

echo "Loading Fixtures \n";
passthru('php bin/console doctrine:fixtures:load --env=test -n');
