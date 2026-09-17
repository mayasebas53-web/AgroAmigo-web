<?php
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmation = $_POST['confirm-password'] ?? '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    exit('Completa los datos correctamente. La contraseña debe tener al menos 8 caracteres.');
}

if ($password !== $confirmation) {
    exit('Las contraseñas no coinciden.');
}

$connection = supabase_connection();
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$result = pg_query_params(
    $connection,
    'INSERT INTO usuarios (nombre, correo_electronico, contraseña) VALUES ($1, $2, $3)',
    [$name, $email, $passwordHash]
);

if (!$result) {
    if (str_contains(pg_last_error($connection), 'duplicate key')) {
        exit('Ese correo ya está registrado.');
    }
    http_response_code(500);
    exit('No fue posible crear la cuenta.');
}

header('Location: index.html?registered=1');
exit;