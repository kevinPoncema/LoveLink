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
echo "🎨 Verificando assets..."
if [ -d "/var/www/html/public/build" ]; then
    echo "✅ Assets compilados encontrados"
    ls -la /var/www/html/public/build/
else
    echo "❌ Assets no encontrados, ejecutando build..."
    npm run build
fi

# 4. Ejecutar migraciones si es necesario
echo "🗄️  Verificando base de datos..."
php artisan migrate --force --no-interaction || echo "⚠️  Migraciones fallaron o no son necesarias"

# 5. Crear enlace de storage si no existe
echo "🔗 Verificando enlace de storage..."
php artisan storage:link || echo "📁 Enlace de storage ya existe"

# 6. Iniciar Supervisor para gestionar Nginx y PHP-FPM
echo "✅ Iniciando servicios web..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
