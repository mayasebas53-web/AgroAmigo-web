<?php
function load_environment_file(): void
{
    $environmentFile = __DIR__ . '/.env';
    if (!is_readable($environmentFile)) {
        return;
    }

    foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name !== '' && getenv($name) === false) {
            putenv("{$name}={$value}");
        }
    }
}

load_environment_file();

function supabase_connection(): PgSql\Connection
{
    $requiredVariables = ['SUPA_HOST', 'SUPA_USERNAME', 'SUPA_PASSWORD'];
    foreach ($requiredVariables as $variable) {
        if (!getenv($variable)) {
            http_response_code(500);
            exit("Falta configurar la variable de entorno {$variable}.");
        }
    }

    $connection = pg_connect(sprintf(
        'host=%s dbname=%s user=%s password=%s port=%s sslmode=require',
        getenv('SUPA_HOST'),
        getenv('SUPA_DBNAME') ?: 'postgres',
        getenv('SUPA_USERNAME'),
        getenv('SUPA_PASSWORD'),
        getenv('SUPA_PORT') ?: '6543'
    ));

    if (!$connection) {
        http_response_code(500);
        exit('No fue posible conectar con Supabase. Revisa las variables de entorno.');
    }

    return $connection;
}