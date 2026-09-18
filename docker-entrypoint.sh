#!/usr/bin/env bash

set -euo pipefail

port="${PORT:-10000}"

sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${port}>/g" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground