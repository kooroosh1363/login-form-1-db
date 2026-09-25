<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
$user = require_authenticated_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Authenticated dashboard for the VaultDB secure database login demo.">
    <meta name="color-scheme" content="light dark">
    <title>Dashboard — VaultDB</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="dashboard-body">
    <header class="dashboard-header">
        <a class="brand" href="/home.php">
            <span class="brand-mark" aria-hidden="true">V</span>
            <span>VaultDB</span>
        </a>

        <form method="post" action="/logout.php">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <button class="secondary-button" type="submit">Sign out</button>
        </form>
    </header>

    <main class="dashboard-shell">
        <section class="dashboard-hero">
            <p class="eyebrow">Authenticated database session</p>
            <h1>Welcome, <?= e($user['display_name']) ?>.</h1>
            <p>Signed in as <strong><?= e($user['email']) ?></strong>. The application is using the <strong><?= e($userRepository->driver()) ?></strong> PDO driver.</p>
        </section>

        <section class="security-grid" aria-label="Authentication controls">
            <article><span>SQL</span><strong>Prepared</strong><p>All account lookup and write operations use parameterized PDO statements.</p></article>
            <article><span>Password</span><strong>Hashed</strong><p>Passwords use PHP's native password hashing and verification APIs.</p></article>
            <article><span>Session</span><strong>Rotating</strong><p>Session IDs rotate after sign-in and during longer sessions.</p></article>
            <article><span>CSRF</span><strong>Verified</strong><p>Sign-in and sign-out require session-bound anti-CSRF tokens.</p></article>
        </section>
    </main>
</body>
</html>
