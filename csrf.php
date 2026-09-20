<?php
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf-token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function require_valid_csrf(): void
{
    $submittedToken = $_POST['csrf-token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if ($sessionToken === '' || $submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
        http_response_code(403);
        exit('La solicitud no es válida. Recarga la página e inténtalo de nuevo.');
    }
}
