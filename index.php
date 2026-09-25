<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (authenticated_user() !== null) {
    redirect('/home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = LoginValidator::normalize($_POST);
    $errors = LoginValidator::validate($data);

    if (!csrf_is_valid($_POST['_token'] ?? null)) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    if ($errors === []) {
        $result = $authService->attempt($data['email'], $data['password'], time());

        if ($result['ok'] && is_array($result['user'])) {
            sign_in_session($result['user']);
            redirect('/home.php');
        }

        $errors['form'] = $result['reason'] === 'locked'
            ? 'Too many sign-in attempts. Please wait a few minutes and try again.'
            : 'The email or password is incorrect.';
    }

    flash('errors', $errors);
    flash('old_email', $data['email']);
    redirect('/');
}

$errors = pull_flash('errors', []);
$oldEmail = pull_flash('old_email', '');
$notice = pull_flash('notice');

function login_error(array $errors, string $field): ?string
{
    return isset($errors[$field]) && is_string($errors[$field]) ? $errors[$field] : null;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A secure PHP database authentication demo using PDO, password hashing, CSRF protection, and login throttling.">
    <meta name="color-scheme" content="light dark">
    <title>VaultDB — Secure Database Login</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <a class="skip-link" href="#login-form">Skip to sign in</a>

    <main class="auth-shell">
        <section class="story-panel" aria-labelledby="story-title">
            <a class="brand" href="/" aria-label="VaultDB home">
                <span class="brand-mark" aria-hidden="true">V</span>
                <span>VaultDB</span>
            </a>

            <div class="story-copy">
                <p class="eyebrow">PDO authentication / database-backed</p>
                <h1 id="story-title">A database login should be secure before it is stylish.</h1>
                <p>VaultDB modernizes the original 2023 MySQL exercise with prepared statements, password hashing, hardened sessions, CSRF protection, and portable PDO support for MySQL and SQLite.</p>
            </div>

            <ul class="security-list" aria-label="Security controls">
                <li><span>01</span><div><strong>Prepared queries</strong><small>User input never becomes executable SQL.</small></div></li>
                <li><span>02</span><div><strong>Hashed passwords</strong><small>Plaintext credentials are never stored by the current app.</small></div></li>
                <li><span>03</span><div><strong>Hardened sessions</strong><small>Session IDs rotate and idle sessions expire.</small></div></li>
                <li><span>04</span><div><strong>Login throttling</strong><small>Repeated failures trigger a temporary lock window.</small></div></li>
            </ul>
        </section>

        <section class="form-panel" aria-labelledby="login-title">
            <div class="form-card">
                <p class="section-index">Database access / 01</p>
                <h2 id="login-title">Sign in</h2>
                <p class="form-intro">Use an account created through the CLI. No default credentials are committed to this repository.</p>

                <?php if (is_string($notice) && $notice !== ''): ?>
                    <div class="notice notice--info" role="status"><?= e($notice) ?></div>
                <?php endif; ?>

                <?php if (($errors['form'] ?? null) !== null): ?>
                    <div class="notice notice--error" role="alert"><?= e((string) $errors['form']) ?></div>
                <?php endif; ?>

                <form id="login-form" method="post" action="/" novalidate>
                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">

                    <div class="field">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            maxlength="254"
                            autocomplete="username"
                            required
                            value="<?= e(is_string($oldEmail) ? $oldEmail : '') ?>"
                            <?= login_error($errors, 'email') ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>
                        >
                        <?php if ($error = login_error($errors, 'email')): ?>
                            <p class="field-error" id="email-error"><?= e($error) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            maxlength="4096"
                            autocomplete="current-password"
                            required
                            <?= login_error($errors, 'password') ? 'aria-invalid="true" aria-describedby="password-error"' : '' ?>
                        >
                        <?php if ($error = login_error($errors, 'password')): ?>
                            <p class="field-error" id="password-error"><?= e($error) ?></p>
                        <?php endif; ?>
                    </div>

                    <button class="primary-button" type="submit">Enter dashboard <span aria-hidden="true">→</span></button>
                </form>

                <div class="local-note">
                    <span aria-hidden="true">i</span>
                    <p>Provision an account with <code>php bin/create-user.php</code>. Passwords are hashed before persistence.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
