<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This command can only run from the CLI.\n");
    exit(1);
}

fwrite(STDOUT, sprintf(
    "Database schema is ready using the %s driver.\n",
    $userRepository->driver(),
));
