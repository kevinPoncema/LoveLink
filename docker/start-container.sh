#!/bin/sh
set -e

echo "🚀 Iniciando aplicación Laravel..."

# 1. Corregir permisos críticos
echo "📁 Configurando permisos..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Limpiar cachés previas y archivos de desarrollo
echo "🧹 Limpiando cachés y archivos de desarrollo..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
rm -f /var/www/html/public/hot

# 3. Generar cachés de configuración para producción
echo "⚡ Generando cachés de producción..."
php artisan config:cache
php artisan view:cache

# 4. Verificar assets compilados
echo "🎨 Verificando assets y configuración..."
if [ -d "/var/www/html/public/build" ]; then
    echo "✅ Assets compilados encontrados"
    rm -f /var/www/html/public/hot  # Eliminar archivo hot automáticamente
else
    echo "❌ Assets no encontrados, ejecutando build..."
    npm run build
fi

# Verificar configuración de PHP para uploads
echo "📁 Configuración PHP upload_max_filesize: $(php -r 'echo ini_get("upload_max_filesize");')"
echo "📁 Configuración PHP post_max_size: $(php -r 'echo ini_get("post_max_size");')"

# 4. Ejecutar migraciones si es necesario
echo "🗄️  Verificando base de datos..."
php artisan migrate --force --no-interaction || echo "⚠️  Migraciones fallaron o no son necesarias"

# 5. Crear enlace de storage si no existe
echo "🔗 Verificando enlace de storage..."
php artisan storage:link || echo "📁 Enlace de storage ya existe"

# 6. Verificar y configurar SSL automáticamente
echo "🔐 Verificando configuración SSL..."
export SETUP_CRON=true
export PROJECT_PATH=/var/www/html
/var/www/html/docker/ssl-check.sh || echo "⚠️  SSL check completado con advertencias"

# 7. Iniciar Supervisor para gestionar Nginx y PHP-FPM
echo "✅ Iniciando servicios web..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
