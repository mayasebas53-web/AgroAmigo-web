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
$cropName = trim($_POST['crop-name'] ?? '');
$cropType = trim($_POST['crop-type'] ?? '');
$sowingDate = $_POST['initial-sowing-date'] ?? '';
$plotSize = trim($_POST['plot-size'] ?? '');
$location = trim($_POST['plot-location'] ?? '');
$zone = trim($_POST['crop-zone'] ?? '');
$status = trim($_POST['crop-status'] ?? '');
$allowedStatuses = ['Activo', 'En riesgo', 'Cosechado'];

if (!$cropId || $cropName === '' || $cropType === '' || $sowingDate === '' || $location === '') {
    exit('Completa todos los campos obligatorios del cultivo.');
}

$parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $sowingDate);
if (!$parsedDate || $parsedDate->format('Y-m-d') !== $sowingDate) {
    exit('La fecha de siembra no es válida.');
}

if ($plotSize !== '' && (!is_numeric($plotSize) || (float) $plotSize < 0)) {
    exit('El área del terreno no es válida.');
}

if (!in_array($status, $allowedStatuses, true)) {
    exit('El estado del cultivo no es válido.');
}

$connection = supabase_connection();
$photo = $_FILES['plant-photo'] ?? null;
$photoData = null;
$photoName = null;
$photoType = null;
$hasNewPhoto = $photo && $photo['error'] !== UPLOAD_ERR_NO_FILE;

if ($hasNewPhoto) {
    if ($photo['error'] !== UPLOAD_ERR_OK || $photo['size'] > 5 * 1024 * 1024) {
        exit('La foto no es válida o supera el límite de 5 MB.');
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $photoType = (new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);
    if (!in_array($photoType, $allowedTypes, true)) {
        exit('La foto debe ser JPG, PNG o WEBP.');
    }

    $photoData = file_get_contents($photo['tmp_name']);
    $photoName = basename($photo['name']);
}

if ($hasNewPhoto) {
    $photoValue = pg_escape_bytea($connection, $photoData);
    $result = pg_query_params(
        $connection,
        'UPDATE cultivos SET nombre_cultivo = $1, tipo_cultivo = $2, fecha_siembra = $3, tamaño_terreno = $4, ubicacion_cultivo = $5, zona = $6, estado_cultivo = $7, foto = $8, foto_nombre = $9, foto_tipo = $10 WHERE id_cultivo = $11 AND id_usuario = $12',
        [$cropName, $cropType, $sowingDate, $plotSize !== '' ? $plotSize : null, $location, $zone !== '' ? $zone : null, $status, $photoValue, $photoName, $photoType, $cropId, $_SESSION['user_id']]
    );
} else {
    $result = pg_query_params(
        $connection,
        'UPDATE cultivos SET nombre_cultivo = $1, tipo_cultivo = $2, fecha_siembra = $3, tamaño_terreno = $4, ubicacion_cultivo = $5, zona = $6, estado_cultivo = $7 WHERE id_cultivo = $8 AND id_usuario = $9',
        [$cropName, $cropType, $sowingDate, $plotSize !== '' ? $plotSize : null, $location, $zone !== '' ? $zone : null, $status, $cropId, $_SESSION['user_id']]
    );
}

if (!$result) {
    http_response_code(500);
    exit('No fue posible actualizar el cultivo.');
}

header('Location: crops.php');
exit;
