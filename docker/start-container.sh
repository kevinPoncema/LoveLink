#!/bin/sh
set -e

# Iniciar Supervisor para gestionar Nginx y PHP-FPM
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
