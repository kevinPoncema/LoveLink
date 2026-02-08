#!/bin/bash

# ========================================
# Script de instalación SSL para el HOST
# ¡Ejecutar ANTES de lanzar Docker!
# ========================================

set -e

# Variables
DOMAIN="lovelink.kevinponcedev.xyz"
EMAIL="kevin@kevinponcedev.xyz"

# Función de logging
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] SSL-SETUP: $1"
}

log "🔐 Configurando certificados SSL para $DOMAIN..."

# 1. Instalar certbot en el host
log "📦 Instalando Certbot en el host..."
sudo apt update
sudo apt install -y certbot

# 2. Parar contenedores si están corriendo
log "⏸️  Deteniendo contenedores Docker..."
docker compose down 2>/dev/null || true

# 3. Generar certificado usando el puerto 80 temporalmente
log "🎫 Generando certificado SSL..."
sudo certbot certonly \
    --standalone \
    -d "$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --non-interactive \
    --preferred-challenges http

# 4. Verificar que se creó el certificado
if [ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
    log "✅ Certificado creado exitosamente en:"
    sudo ls -la "/etc/letsencrypt/live/$DOMAIN/"
else
    log "❌ Error: No se pudo generar el certificado"
    echo "Asegúrate de que:"
    echo "1. El dominio $DOMAIN apunta a este servidor"
    echo "2. Los puertos 80 y 443 están abiertos"
    echo "3. No hay otros servicios usando el puerto 80"
    exit 1
fi

# 5. Configurar renovación automática
log "📅 Configurando renovación automática..."
(sudo crontab -l 2>/dev/null | grep -v certbot; echo "0 3 * * * /usr/bin/certbot renew --quiet --post-hook 'cd $PWD && docker compose restart app'") | sudo crontab -

# 6. Verificar permisos
log "🔧 Ajustando permisos..."
sudo chown -R root:root /etc/letsencrypt/
sudo chmod -R 755 /etc/letsencrypt/

# 7. Lanzar contenedores con SSL
log "🚀 Iniciando aplicación con SSL..."
docker compose build --no-cache
docker compose up -d

# 8. Esperar y verificar
log "⏳ Esperando que la aplicación esté lista..."
sleep 10

# 9. Test final
log "🔍 Verificando SSL..."
if curl -sSf -I https://"$DOMAIN" > /dev/null 2>&1; then
    log "🎉 ¡SSL configurado exitosamente!"
    echo ""
    echo "✅ Tu aplicación está disponible en: https://$DOMAIN"
    echo ""
else
    log "⚠️  SSL configurado, pero la aplicación puede estar iniciando..."
    echo ""
    echo "📊 Verifica el estado con:"
    echo "   docker compose logs app"
    echo ""
    echo "🌐 Prueba acceder en 1-2 minutos a:"
    echo "   https://$DOMAIN"
fi

log "✅ Configuración SSL completada"
log "📋 Certificado se renovará automáticamente cada 60 días"