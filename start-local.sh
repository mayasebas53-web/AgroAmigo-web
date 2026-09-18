#!/usr/bin/env bash

set -euo pipefail

PORT="${PORT:-8080}"

cleanup() {
    echo
    echo "Servidor detenido. El puerto $PORT quedó libre."
    exit 0
}

trap cleanup INT TERM

if [[ -n "${PHP_BIN:-}" ]]; then
    php_candidates=("$PHP_BIN")
else
    php_candidates=(php php8.4 php8.3 php8.2 php8.1 php8.0)
fi

for php_candidate in "${php_candidates[@]}"; do
    if command -v "$php_candidate" >/dev/null 2>&1 &&
        "$php_candidate" -m 2>/dev/null | grep -qx 'pgsql' &&
        "$php_candidate" -m 2>/dev/null | grep -qx 'pdo_pgsql'; then
        echo "Iniciando AgroAmigo con $($php_candidate --version | head -n 1) en http://localhost:$PORT"
        "$php_candidate" -S "0.0.0.0:$PORT" -t .
        cleanup
    fi
done

cat >&2 <<'MESSAGE'
No se encontró un PHP con las extensiones pgsql y pdo_pgsql.
Instala esas extensiones para una versión de PHP disponible en este Codespace
o indica un binario compatible con: PHP_BIN=/ruta/al/php ./start-local.sh
MESSAGE
exit 1