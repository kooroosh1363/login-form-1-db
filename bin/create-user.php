<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This command can only run from the CLI.\n");
    exit(1);
}

$email = trim((string) ($argv[1] ?? ''));
$displayName = trim((string) ($argv[2] ?? ''));
$password = (string) ($argv[3] ?? '');

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "Usage: php bin/create-user.php user@example.com 'Display Name' 'strong-password'\n");
    exit(1);
}

$nameError = LoginValidator::validateDisplayName($displayName);
if ($nameError !== null) {
    fwrite(STDERR, $nameError . "\n");
    exit(1);
}

$passwordError = LoginValidator::validateNewPassword($password);
if ($passwordError !== null) {
    fwrite(STDERR, $passwordError . "\n");
    exit(1);
}

try {
    $id = $userRepository->createUser($email, $displayName, $password);
    fwrite(STDOUT, sprintf("Created user #%d for %s.\n", $id, $email));
} catch (PDOException $error) {
    $message = $error->getMessage();

    if (
        (string) $error->getCode() === '23000'
        || str_contains($message, 'UNIQUE constraint failed')
        || str_contains($message, 'Duplicate entry')
    ) {
        fwrite(STDERR, "A user with that email already exists.\n");
        exit(1);
    }

    throw $error;
}
