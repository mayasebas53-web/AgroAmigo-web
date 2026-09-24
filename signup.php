<?php
require_once __DIR__ . '/database.php';

function redirect_signup_error(string $error, string $name = '', string $email = ''): never
{
    $query = http_build_query([
        'error' => $error,
        'name' => $name,
        'email' => $email,
    ]);
    header('Location: signup.html?' . $query);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmation = $_POST['confirm-password'] ?? '';

if ($name === '') {
    redirect_signup_error('missing_name', '', $email);
}

if ($email === '') {
    redirect_signup_error('missing_email', $name);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_signup_error('invalid_email', $name, $email);
}

if ($password === '') {
    redirect_signup_error('missing_password', $name, $email);
}

if (strlen($password) < 8) {
    redirect_signup_error('short_password', $name, $email);
}

if ($confirmation === '') {
    redirect_signup_error('missing_confirmation', $name, $email);
}

if ($password !== $confirmation) {
    redirect_signup_error('password_mismatch', $name, $email);
}

try {
    $connection = supabase_connection(true);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $result = pg_query_params(
        $connection,
        'INSERT INTO usuarios (nombre, correo_electronico, contraseña) VALUES ($1, $2, $3)',
        [$name, $email, $passwordHash]
    );

    if (!$result) {
        $sqlState = pg_last_error($connection);
        if (str_contains($sqlState, 'duplicate key') || str_contains($sqlState, '23505')) {
            redirect_signup_error('email_registered', $name, $email);
        }

        throw new RuntimeException('No fue posible crear la cuenta.');
    }
} catch (Throwable $exception) {
    redirect_signup_error('server_error', $name, $email);
}

header('Location: index.html?registered=1');
exit;