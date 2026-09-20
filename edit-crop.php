<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

$cropId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$cropId) {
    http_response_code(400);
    exit('El cultivo indicado no es válido.');
}

$connection = supabase_connection();
$result = pg_query_params(
    $connection,
    'SELECT id_cultivo, nombre_cultivo, tipo_cultivo, fecha_siembra, tamaño_terreno, ubicacion_cultivo, zona, estado_cultivo, foto IS NOT NULL AS tiene_foto FROM cultivos WHERE id_cultivo = $1 AND id_usuario = $2',
    [$cropId, $_SESSION['user_id']]
);
$crop = $result ? pg_fetch_assoc($result) : false;

if (!$crop) {
    http_response_code(404);
    exit('No se encontró el cultivo.');
}

function edit_value(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroAmigo - Editar cultivo</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="app-shell">
    <header class="topbar">
        <a class="brand" href="home.php">🌱 AgroAmigo</a>
        <nav class="desktop-nav" aria-label="Navegación principal">
            <a href="home.php">Inicio</a>
            <a class="active" href="crops.php">Mis cultivos</a>
            <a href="home.php#recomendaciones">Recomendaciones</a>
            <a href="home.php#clima">Clima</a>
            <a href="home.php#historial">Historial</a>
        </nav>
    </header>
    <main class="form-page">
        <a class="back-link" href="crops.php">← Volver a la biblioteca</a>
        <section class="plant-card">
            <div class="section-heading">
                <p class="eyebrow">Mis cultivos</p>
                <h1>Editar cultivo</h1>
                <p>Actualiza los datos de este registro.</p>
            </div>
            <form class="full-plant-form" action="update-crop.php" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="crop-id" value="<?= (int) $crop['id_cultivo'] ?>">
                <div class="input-group">
                    <label for="plant-photo">Cambiar foto</label>
                    <input type="file" id="plant-photo" name="plant-photo" accept="image/jpeg,image/png,image/webp">
                    <small>Deja vacío para conservar la foto actual. Máximo 5 MB.</small>
                </div>
                <div class="input-group">
                    <label for="crop-name">Nombre del cultivo</label>
                    <input type="text" id="crop-name" name="crop-name" value="<?= edit_value($crop['nombre_cultivo']) ?>" required>
                </div>
                <div class="input-group">
                    <label for="crop-type">Tipo de cultivo</label>
                    <input type="text" id="crop-type" name="crop-type" value="<?= edit_value($crop['tipo_cultivo']) ?>" required>
                </div>
                <div class="input-group">
                    <label for="initial-sowing-date">Fecha de siembra / germinación</label>
                    <input type="date" id="initial-sowing-date" name="initial-sowing-date" value="<?= edit_value($crop['fecha_siembra']) ?>" required>
                </div>
                <div class="input-group">
                    <label for="plot-size">Área del terreno en m²</label>
                    <input type="number" id="plot-size" name="plot-size" min="0" step="0.01" value="<?= edit_value((string) $crop['tamaño_terreno']) ?>" required>
                </div>
                <div class="input-group">
                    <label for="plot-location">Parcela o lote</label>
                    <input type="text" id="plot-location" name="plot-location" value="<?= edit_value($crop['ubicacion_cultivo']) ?>" required>
                </div>
                <div class="input-group">
                    <label for="crop-zone">Zona</label>
                    <input type="text" id="crop-zone" name="crop-zone" value="<?= edit_value((string) $crop['zona']) ?>">
                </div>
                <div class="input-group">
                    <label for="crop-status">Estado del cultivo</label>
                    <select id="crop-status" name="crop-status" required>
                        <?php foreach (['Activo', 'En riesgo', 'Cosechado'] as $status): ?>
                            <option value="<?= edit_value($status) ?>" <?= $crop['estado_cultivo'] === $status ? 'selected' : '' ?>><?= edit_value($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-login">Guardar cambios</button>
            </form>
        </section>
    </main>
</body>
</html>
