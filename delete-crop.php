<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

require_valid_csrf();

$cropId = filter_input(INPUT_POST, 'crop-id', FILTER_VALIDATE_INT);
if (!$cropId) {
    http_response_code(400);
    exit('El cultivo indicado no es válido.');
}

$connection = supabase_connection();
$result = pg_query_params(
    $connection,
    'DELETE FROM cultivos WHERE id_cultivo = $1 AND id_usuario = $2',
    [$cropId, $_SESSION['user_id']]
);

if (!$result) {
    http_response_code(500);
    exit('No fue posible eliminar el cultivo.');
}

header('Location: crops.php');
exit;
