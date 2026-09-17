<?php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-plant.html');
    exit;
}

$cropName = trim($_POST['crop-name'] ?? '');
$cropType = trim($_POST['crop-type'] ?? '');
$sowingDate = $_POST['initial-sowing-date'] ?? '';
$plotSize = trim($_POST['plot-size'] ?? '');
$location = trim($_POST['plot-location'] ?? '');
$zone = trim($_POST['crop-zone'] ?? '');
$status = trim($_POST['crop-status'] ?? 'Activo');
$photo = $_FILES['plant-photo'] ?? null;
$allowedStatuses = ['Activo', 'En riesgo', 'Cosechado'];

if ($cropName === '' || $cropType === '' || $sowingDate === '' || $location === '') {
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

$photoData = null;
$photoName = null;
$photoType = null;
if ($photo && $photo['error'] !== UPLOAD_ERR_NO_FILE) {
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

$connection = supabase_connection();
$photoValue = $photoData !== null ? pg_escape_bytea($connection, $photoData) : null;
$result = pg_query_params(
    $connection,
    'INSERT INTO cultivos (id_usuario, nombre_cultivo, tipo_cultivo, fecha_siembra, tamaño_terreno, ubicacion_cultivo, zona, estado_cultivo, foto, foto_nombre, foto_tipo) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)',
    [$_SESSION['user_id'], $cropName, $cropType, $sowingDate, $plotSize !== '' ? $plotSize : null, $location, $zone !== '' ? $zone : null, $status, $photoValue, $photoName, $photoType]
);

if (!$result) {
    http_response_code(500);
    exit('No fue posible guardar el cultivo.');
}

header('Location: home.php?crop_added=1');
exit;