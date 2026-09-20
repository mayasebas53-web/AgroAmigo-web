<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/crop-placeholder.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

$connection = supabase_connection();
$result = pg_query_params(
    $connection,
    'SELECT id_cultivo, nombre_cultivo, tipo_cultivo, fecha_siembra, tamaño_terreno, ubicacion_cultivo, zona, estado_cultivo, foto IS NOT NULL AS tiene_foto FROM cultivos WHERE id_usuario = $1 ORDER BY fecha_creacion DESC, id_cultivo DESC',
    [$_SESSION['user_id']]
);

if (!$result) {
    http_response_code(500);
    exit('No fue posible cargar la biblioteca de cultivos.');
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
    $nextStatus = $crop['estado_cultivo'] === 'Activo' ? 'Cosechado' : 'Activo';
    $statusAction = $crop['estado_cultivo'] === 'Activo' ? 'Marcar inactivo' : 'Activar';
    $date = (new DateTimeImmutable($crop['fecha_siembra']))->format('d/m/Y');
    $area = $crop['tamaño_terreno'] !== null ? htmlspecialchars($crop['tamaño_terreno'], ENT_QUOTES, 'UTF-8') . ' m²' : 'Área no indicada';
    $placeholderEmoji = crop_placeholder_emoji($crop['tipo_cultivo'], $cropId);
    $csrfField = csrf_field();
    $image = $crop['tiene_foto'] === 't' ? '<img src="crop-image.php?id=' . $cropId . '" alt="Foto de ' . $cropName . '" class="crop-photo">' : '<div class="crop-photo crop-photo-empty" aria-label="Este cultivo no tiene foto">' . $placeholderEmoji . '</div>';

    $cropCards .= <<<HTML
        <article class="crop-card">
            {$image}
            <div class="crop-card-body">
                <div class="crop-card-heading">
                    <div><p class="eyebrow">{$cropType}</p><h2>{$cropName}</h2></div>
                    <span class="crop-status {$statusClass}">{$status}</span>
                </div>
                <dl class="crop-details">
                    <div><dt>Siembra</dt><dd>{$date}</dd></div>
                    <div><dt>Área</dt><dd>{$area}</dd></div>
                    <div><dt>Parcela</dt><dd>{$location}</dd></div>
                    <div><dt>Zona</dt><dd>{$zone}</dd></div>
                </dl>
                <div class="crop-actions">
                    <a class="section-link" href="edit-crop.php?id={$cropId}">Editar</a>
                    <form action="update-crop-status.php" method="post">
                        {$csrfField}
                        <input type="hidden" name="crop-id" value="{$cropId}">
                        <input type="hidden" name="status" value="{$nextStatus}">
                        <button class="text-button" type="submit">{$statusAction}</button>
                    </form>
                    <form id="delete-form-{$cropId}" action="delete-crop.php" method="post">
                        {$csrfField}
                        <input type="hidden" name="crop-id" value="{$cropId}">
                        <button class="text-button danger delete-button" type="button" data-delete-form="delete-form-{$cropId}" data-crop-name="{$cropName}">Eliminar</button>
                    </form>
                </div>
            </div>
        </article>
HTML;
}

$displayName = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');
$body = $cropCards !== ''
    ? '<div class="crops-grid library-grid">' . $cropCards . '</div>'
    : '<div class="empty-crops"><span>🌱</span><h2>Aún no tienes cultivos</h2><p>Registra tu primer cultivo para comenzar a llevar el seguimiento.</p><a class="primary-action" href="add-plant.php">Registrar el primero</a></div>';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroAmigo - Biblioteca de cultivos</title>
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
        <details class="menu-wrapper">
            <summary class="menu-button" aria-label="Abrir menú"><span></span><span></span><span></span></summary>
            <nav class="dropdown-menu" aria-label="Menú móvil">
                <a href="home.php">Inicio</a>
                <a href="crops.php">Mis cultivos</a>
                <a href="home.php#recomendaciones">Recomendaciones</a>
                <a href="home.php#clima">Clima</a>
                <a href="home.php#historial">Historial</a>
            </nav>
        </details>
    </header>
    <main class="library-page">
        <a class="back-link" href="home.php">← Volver al panel</a>
        <section>
            <div class="library-heading">
                <div><p class="eyebrow">Biblioteca de cultivos</p><h1>Todos tus cultivos</h1><p>Hola, <?= $displayName ?>. Aquí encuentras tus registros activos y anteriores.</p></div>
                <a class="primary-action" href="add-plant.php">＋ Nuevo cultivo</a>
            </div>
            <?= $body ?>
        </section>
    </main>
    <dialog class="delete-dialog" id="delete-dialog" aria-labelledby="delete-dialog-title">
        <form class="delete-dialog-card" method="dialog">
            <button class="dialog-close" type="submit" aria-label="Cerrar ventana">×</button>
            <p class="eyebrow">Confirmar eliminación</p>
            <h2 id="delete-dialog-title">¿Eliminar este cultivo?</h2>
            <p id="delete-dialog-message">Esta acción eliminará el registro seleccionado.</p>
            <p class="delete-warning">Esto es irreversible. ¿Deseas continuar?</p>
            <div class="dialog-actions">
                <button class="secondary-button" value="cancel" type="submit">Cancelar</button>
                <button class="danger-button" id="confirm-delete" value="confirm" type="submit">Eliminar cultivo</button>
            </div>
        </form>
    </dialog>
    <script>
        const deleteDialog = document.getElementById('delete-dialog');
        const deleteMessage = document.getElementById('delete-dialog-message');
        let selectedDeleteForm = null;

        document.querySelectorAll('.delete-button').forEach((button) => {
            button.addEventListener('click', () => {
                selectedDeleteForm = document.getElementById(button.dataset.deleteForm);
                deleteMessage.textContent = 'Se eliminará "' + button.dataset.cropName + '" de tu biblioteca.';
                deleteDialog.showModal();
            });
        });

        deleteDialog.addEventListener('close', () => {
            if (deleteDialog.returnValue === 'confirm' && selectedDeleteForm) {
                selectedDeleteForm.submit();
            }
            selectedDeleteForm = null;
        });
    </script>
</body>
</html>
