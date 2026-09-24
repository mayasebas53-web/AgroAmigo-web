<?php
session_start();
require_once __DIR__ . '/database.php';

function redirect_login_error(string $error, string $email = ''): never
{
    $query = http_build_query(['error' => $error, 'email' => $email]);
    header('Location: index.html?' . $query);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '') {
    redirect_login_error('missing_email');
}

if ($password === '') {
    redirect_login_error('missing_password', $email);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_login_error('invalid_email', $email);
}

try {
    $connection = supabase_connection(true);
    $result = pg_query_params(
        $connection,
        'SELECT id_usuario, nombre, contraseña FROM usuarios WHERE correo_electronico = $1',
        [$email]
    );

    if (!$result) {
        throw new RuntimeException('No fue posible consultar la cuenta.');
    }

    $user = pg_fetch_assoc($result);
    if (!$user) {
        redirect_login_error('email_not_found', $email);
    }

    if (!password_verify($password, $user['contraseña'])) {
        redirect_login_error('incorrect_password', $email);
    }
} catch (Throwable $exception) {
    redirect_login_error('server_error', $email);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id_usuario'];
$_SESSION['user_name'] = $user['nombre'];
header('Location: home.php');
exit;