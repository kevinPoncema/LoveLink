#!/bin/bash

# ========================================
# Script de configuración SSL para LoveLink
# ========================================

set -e  # Salir si hay error

echo "🔐 Configurando SSL para LoveLink..."

# Variables
DOMAIN="lovelink.kevinponcedev.xyz"
EMAIL="kevin@kevinponcedev.xyz"
PROJECT_PATH="/home/kevin/Documentos/uspage"

# 1. Verificar que estamos en el servidor
echo "📍 Verificando ubicación del proyecto..."
if [ ! -d "$PROJECT_PATH" ]; then
    echo "❌ Error: No se encontró el proyecto en $PROJECT_PATH"
    echo "   Asegúrate de estar en el servidor correcto y que el proyecto esté clonado."
    exit 1
fi

cd "$PROJECT_PATH"

# 2. Actualizar sistema
echo "🔄 Actualizando sistema..."
sudo apt update && sudo apt upgrade -y

# 3. Instalar snap si no existe
echo "📦 Verificando snap..."
if ! command -v snap &> /dev/null; then
    echo " * Instalando snap..."
    sudo apt install snapd -y
fi

# 4. Instalar Certbot
echo "🔧 Instalando Certbot..."
sudo snap install core
sudo snap refresh core
sudo snap install --classic certbot

# 5. Crear enlace simbólico
echo "🔗 Configurando comando certbot..."
sudo ln -sf /snap/bin/certbot /usr/bin/certbot

# 6. Parar contenedores
echo "⏸️  Parando contenedores Docker..."
docker compose down

# 7. Crear directorio temporal para webroot
echo "📁 Preparando webroot para validación..."
sudo mkdir -p /tmp/letsencrypt-webroot
sudo chown -R www-data:www-data /tmp/letsencrypt-webroot

# 8. Generar certificado
echo "🎫 Generando certificado SSL para $DOMAIN..."
sudo certbot certonly \
    --webroot \
    -w /tmp/letsencrypt-webroot \
    -d "$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    --non-interactive

# 9. Verificar que el certificado se creó
echo "✅ Verificando certificado..."
if [ ! -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
    echo "❌ Error: No se pudo crear el certificado SSL"
    echo "   Verifica que el dominio apunte al servidor y que no haya firewalls bloqueando."
    exit 1
fi

echo "🎉 Certificado SSL creado exitosamente:"
sudo ls -la "/etc/letsencrypt/live/$DOMAIN/"

# 10. Configurar renovación automática
echo "🔄 Configurando renovación automática..."
(sudo crontab -l 2>/dev/null | grep -v certbot; echo "0 2 * * * /usr/bin/certbot renew --quiet --post-hook 'cd $PROJECT_PATH && docker compose restart app'") | sudo crontab -

# 11. Actualizar .env para producción
echo "⚙️  Configurando .env para HTTPS..."
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Actualizar configuraciones en .env
sed -i 's|APP_URL=.*|APP_URL=https://lovelink.kevinponcedev.xyz|g' .env
sed -i 's|APP_ENV=.*|APP_ENV=production|g' .env
sed -i 's|APP_DEBUG=.*|APP_DEBUG=false|g' .env
sed -i 's|SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|g' .env

# 12. Reconstruir y lanzar contenedores
echo "🐳 Reconstruyendo contenedores con SSL..."
docker compose build --no-cache
docker compose up -d

# 13. Esperar a que el contenedor esté listo
echo "⏳ Esperando a que el servicio esté listo..."
sleep 10

# 14. Verificar SSL
echo "🔍 Verificando configuración SSL..."
if curl -sSf https://"$DOMAIN" > /dev/null 2>&1; then
    echo "✅ SSL configurado correctamente!"
    echo "🌐 Tu sitio está disponible en: https://$DOMAIN"
else
    echo "⚠️  El sitio puede estar iniciando. Verifica en unos minutos."
fi

# 15. Mostrar logs si hay problemas
echo "📊 Estado de los contenedores:"
docker compose ps

echo ""
echo "🎉 ¡Configuración SSL completada!"
echo ""
echo "📋 Próximos pasos:"
echo "   1. Visita: https://$DOMAIN"
echo "   2. Configura Cloudflare en modo 'Full (strict)'"
echo "   3. Renueva tu certificado: sudo certbot renew --dry-run"
echo ""
echo "🔧 Comandos útiles:"
echo "   • Ver logs: docker compose logs app -f"
echo "   • Reiniciar: docker compose restart app"
echo "   • Ver certificados: sudo ls -la /etc/letsencrypt/live/"
echo ""