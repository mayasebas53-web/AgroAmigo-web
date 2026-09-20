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
$status = trim($_POST['status'] ?? '');
$allowedStatuses = ['Activo', 'En riesgo', 'Cosechado'];

if (!$cropId || !in_array($status, $allowedStatuses, true)) {
    http_response_code(400);
    exit('La solicitud de estado no es válida.');
}

$connection = supabase_connection();
$result = pg_query_params(
    $connection,
    'UPDATE cultivos SET estado_cultivo = $1 WHERE id_cultivo = $2 AND id_usuario = $3',
    [$status, $cropId, $_SESSION['user_id']]
);

if (!$result) {
    http_response_code(500);
    exit('No fue posible actualizar el estado del cultivo.');
}

header('Location: crops.php');
exit;
