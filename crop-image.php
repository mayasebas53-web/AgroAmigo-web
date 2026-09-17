<?php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$cropId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$cropId) {
    http_response_code(400);
    exit;
}

$connection = supabase_connection();
$result = pg_query_params(
    $connection,
    'SELECT foto, foto_tipo FROM cultivos WHERE id_cultivo = $1 AND id_usuario = $2',
    [$cropId, $_SESSION['user_id']]
);
$crop = $result ? pg_fetch_assoc($result) : false;

if (!$crop || $crop['foto'] === null) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $crop['foto_tipo']);
header('X-Content-Type-Options: nosniff');
echo pg_unescape_bytea($crop['foto']);