<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Config.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/LoginValidator.php';
require_once dirname(__DIR__) . '/src/UserRepository.php';
require_once dirname(__DIR__) . '/src/AuthService.php';

$tests = [];

function test(string $name, callable $callback): void { global $tests; $tests[] = [$name, $callback]; }
function expect_true(bool $condition, string $message = 'Expected true.'): void { if (!$condition) throw new RuntimeException($message); }
function expect_same(mixed $expected, mixed $actual, string $message = ''): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message !== '' ? $message : sprintf('Expected %s, got %s.', var_export($expected, true), var_export($actual, true)));
    }
}

function repository_for_dsn(string $dsn, ?string $user = null, ?string $password = null): UserRepository
{
    $pdo = Database::connect($dsn, $user, $password);
    $repo = new UserRepository($pdo);
    $repo->migrate();
    return $repo;
}

function exercise_repository(UserRepository $repo): void
{
    $email = 'user+' . bin2hex(random_bytes(4)) . '@example.com';
    $password = 'correct-horse-battery-staple';

    $id = $repo->createUser($email, 'Demo User', $password);
    expect_true($id > 0);

    $user = $repo->findByEmail(strtoupper($email));
    expect_true(is_array($user));
    expect_same('Demo User', $user['display_name']);
    expect_true($user['password_hash'] !== $password);
    expect_true(password_verify($password, $user['password_hash']));

    $auth = new AuthService($repo);
    $bad = $auth->attempt($email, 'wrong-password', 1000);
    expect_same(false, $bad['ok']);

    $good = $auth->attempt($email, $password, 1001);
    expect_same(true, $good['ok']);
    expect_same($email, $good['user']['email']);
}

test('validates login input', function (): void {
    $valid = LoginValidator::validate(LoginValidator::normalize([
        'email' => ' user@example.com ',
        'password' => 'secret',
    ]));
    expect_same([], $valid);

    $invalid = LoginValidator::validate(LoginValidator::normalize([
        'email' => 'not-an-email',
        'password' => '',
    ]));
    expect_true(isset($invalid['email']));
    expect_true(isset($invalid['password']));
});

test('enforces provisioning rules', function (): void {
    expect_true(LoginValidator::validateNewPassword('short') !== null);
    expect_same(null, LoginValidator::validateNewPassword('correct-horse-battery-staple'));
    expect_true(LoginValidator::validateDisplayName('A') !== null);
    expect_same(null, LoginValidator::validateDisplayName('Ada Lovelace'));
});

test('runs full authentication flow on SQLite', function (): void {
    expect_true(in_array('sqlite', PDO::getAvailableDrivers(), true), 'pdo_sqlite is required.');
    exercise_repository(repository_for_dsn('sqlite::memory:'));
});

test('locks after repeated failures', function (): void {
    $repo = repository_for_dsn('sqlite::memory:');
    $email = 'locked@example.com';
    $password = 'correct-horse-battery-staple';
    $repo->createUser($email, 'Locked User', $password);
    $auth = new AuthService($repo);

    for ($i = 1; $i <= 5; $i++) {
        $result = $auth->attempt($email, 'wrong-password', 2000 + $i);
    }

    expect_same('locked', $result['reason']);
    expect_same(false, $auth->attempt($email, $password, 2100)['ok']);
    expect_same(true, $auth->attempt($email, $password, 2400)['ok']);
});

$mysqlDsn = getenv('TEST_MYSQL_DSN');
if (is_string($mysqlDsn) && $mysqlDsn !== '') {
    test('runs full authentication flow on MySQL', function () use ($mysqlDsn): void {
        expect_true(in_array('mysql', PDO::getAvailableDrivers(), true), 'pdo_mysql is required.');
        exercise_repository(repository_for_dsn(
            $mysqlDsn,
            getenv('TEST_MYSQL_USER') ?: null,
            getenv('TEST_MYSQL_PASSWORD') ?: null,
        ));
    });
}

$failures = 0;
foreach ($tests as [$name, $callback]) {
    try {
        $callback();
        fwrite(STDOUT, "[pass] {$name}\n");
    } catch (Throwable $error) {
        $failures++;
        fwrite(STDERR, "[fail] {$name}: {$error->getMessage()}\n");
    }
}

fwrite(STDOUT, sprintf("\n%d test(s), %d failure(s).\n", count($tests), $failures));
exit($failures === 0 ? 0 : 1);
