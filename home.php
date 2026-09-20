<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/crop-placeholder.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

$connection = supabase_connection();
$activeResult = pg_query_params(
    $connection,
    "SELECT COUNT(*) AS total FROM cultivos WHERE id_usuario = $1 AND estado_cultivo = 'Activo'",
    [$_SESSION['user_id']]
);

if (!$activeResult) {
    http_response_code(500);
    exit('No fue posible calcular los cultivos activos.');
}

$activeCrops = (int) pg_fetch_result($activeResult, 0, 'total');
$result = pg_query_params(
    $connection,
    'SELECT id_cultivo, nombre_cultivo, tipo_cultivo, fecha_siembra, tamaño_terreno, ubicacion_cultivo, zona, estado_cultivo, foto IS NOT NULL AS tiene_foto FROM cultivos WHERE id_usuario = $1 ORDER BY fecha_creacion DESC, id_cultivo DESC LIMIT 3',
    [$_SESSION['user_id']]
);

if (!$result) {
    http_response_code(500);
    exit('No fue posible cargar los cultivos.');
}

$cropCards = '';
while ($crop = pg_fetch_assoc($result)) {
    $cropId = (int) $crop['id_cultivo'];
    $rawCropName = $crop['nombre_cultivo'];
    $cropName = htmlspecialchars($rawCropName, ENT_QUOTES, 'UTF-8');
    $cropType = htmlspecialchars($crop['tipo_cultivo'], ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars($crop['ubicacion_cultivo'], ENT_QUOTES, 'UTF-8');
    $zone = htmlspecialchars($crop['zona'] ?: 'Zona no indicada', ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars($crop['estado_cultivo'], ENT_QUOTES, 'UTF-8');
    $statusClass = match ($crop['estado_cultivo']) {
        'Activo' => 'crop-status-active',
        'En riesgo' => 'crop-status-risk',
        default => 'crop-status-inactive',
    };
    $date = (new DateTimeImmutable($crop['fecha_siembra']))->format('d/m/Y');
    $area = $crop['tamaño_terreno'] !== null ? htmlspecialchars($crop['tamaño_terreno'], ENT_QUOTES, 'UTF-8') . ' m²' : 'Área no indicada';
    $placeholderEmoji = crop_placeholder_emoji($crop['tipo_cultivo'], $cropId);
    $image = $crop['tiene_foto'] === 't' ? '<img src="crop-image.php?id=' . $cropId . '" alt="Foto de ' . $cropName . '" class="crop-photo">' : '<div class="crop-photo crop-photo-empty" aria-hidden="true">' . $placeholderEmoji . '</div>';

    $cropCards .= <<<HTML
                <article class="crop-card">
                    {$image}
                    <div class="crop-card-body">
                        <div class="crop-card-heading">
                            <div><p class="eyebrow">{$cropType}</p><h3>{$cropName}</h3></div>
                            <span class="crop-status {$statusClass}">{$status}</span>
                        </div>
                        <dl class="crop-details">
                            <div><dt>Siembra</dt><dd>{$date}</dd></div>
                            <div><dt>Área</dt><dd>{$area}</dd></div>
                            <div><dt>Parcela</dt><dd>{$location}</dd></div>
                            <div><dt>Zona</dt><dd>{$zone}</dd></div>
                        </dl>
                    </div>
                </article>
HTML;
}

$cropSection = '<section class="crops-section" id="cultivos"><div class="section-title"><div><p class="eyebrow">Tus registros</p><h2>Mis cultivos</h2></div><div class="section-actions"><a class="section-link" href="crops.php">Ver cultivos</a><a class="section-link" href="add-plant.php">＋ Nuevo cultivo</a></div></div>';
$cropSection .= $cropCards !== '' ? '<div class="crops-grid">' . $cropCards . '</div>' : '<div class="empty-crops"><span>🌱</span><p>Aún no tienes cultivos registrados.</p><a class="primary-action" href="add-plant.php">Agregar el primero</a></div>';
$cropSection .= '</section>';

$home = file_get_contents(__DIR__ . '/home.html');
$home = str_replace('Buenos días, agricultor', 'Buenos días, ' . htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'), $home);
$home = str_replace('<!-- active-crops-count -->', (string) $activeCrops, $home);
$home = str_replace('<!-- active-crops-summary -->', $activeCrops === 1 ? '1 cultivo activo' : $activeCrops . ' cultivos activos', $home);
$home = str_replace('<!-- dynamic-crops -->', $cropSection, $home);
echo $home;