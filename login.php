<?php
session_start();
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    exit('Correo o contraseña incorrectos.');
}

$connection = supabase_connection();
$result = pg_query_params(
    $connection,
    'SELECT id_usuario, nombre, contraseña FROM usuarios WHERE correo_electronico = $1',
    [$email]
);
$user = $result ? pg_fetch_assoc($result) : false;

if (!$user || !password_verify($password, $user['contraseña'])) {
    exit('Correo o contraseña incorrectos.');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id_usuario'];
$_SESSION['user_name'] = $user['nombre'];
header('Location: home.php');
exit;