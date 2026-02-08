#!/bin/bash

# ========================================
# SSL Certificate Checker para LoveLink
# Verifica y regenera certificados automáticamente
# ========================================

set -e  # Salir si hay error

# Variables desde .env o valores por defecto
DOMAIN="${SSL_DOMAIN:-lovelink.kevinponcedev.xyz}"
EMAIL="${SSL_EMAIL:-kevin@kevinponcedev.xyz}"
PROJECT_PATH="${PROJECT_PATH:-/var/www/html}"
CERT_PATH="/etc/letsencrypt/live/$DOMAIN/fullchain.pem"
KEY_PATH="/etc/letsencrypt/live/$DOMAIN/privkey.pem"

# Función de logging
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] SSL-CHECK: $1"
}

# Función para verificar si el certificado existe y es válido
check_certificate() {
    local cert_file="$1"
    
    if [ ! -f "$cert_file" ]; then
        log "❌ Certificado no encontrado en $cert_file"
        return 1
    fi
    
    # Verificar si el certificado expira en menos de 30 días
    local expiry_date=$(openssl x509 -enddate -noout -in "$cert_file" | cut -d= -f2)
    local expiry_epoch=$(date -d "$expiry_date" +%s)
    local current_epoch=$(date +%s)
    local days_until_expiry=$(((expiry_epoch - current_epoch) / 86400))
    
    if [ $days_until_expiry -lt 30 ]; then
        log "⚠️  Certificado expira en $days_until_expiry días, renovando..."
        return 1
    fi
    
    log "✅ Certificado válido por $days_until_expiry días más"
    return 0
}

# Función para verificar si el sitio responde con SSL
check_ssl_response() {
    local domain="$1"
    
    if curl -sSf --max-time 10 "https://$domain" > /dev/null 2>&1; then
        log "✅ Sitio HTTPS responde correctamente"
        return 0
    else
        log "❌ Sitio HTTPS no responde"
        return 1
    fi
}

# Función para instalar certbot si no existe
install_certbot() {
    if ! command -v certbot &> /dev/null; then
        log "📦 Instalando Certbot via apt..."
        apt-get update
        apt-get install -y certbot python3-certbot-nginx
    else
        log "✅ Certbot ya está instalado"
    fi
}

# Función para generar/renovar certificado
generate_certificate() {
    local domain="$1"
    local email="$2"
    
    log "🔄 Generando/renovando certificado para $domain..."
    
    # Parar nginx temporalmente
    if pgrep nginx > /dev/null; then
        log "⏸️  Parando Nginx temporalmente..."
        pkill nginx || true
        sleep 2
    fi
    
    # Crear directorio webroot
    mkdir -p /tmp/letsencrypt-webroot
    
    # Generar certificado
    if certbot certonly \
        --webroot \
        -w /tmp/letsencrypt-webroot \
        -d "$domain" \
        --email "$email" \
        --agree-tos \
        --no-eff-email \
        --non-interactive \
        --force-renewal; then
        
        log "✅ Certificado generado exitosamente"
        
        # Reiniciar nginx
        log "🔄 Reiniciando Nginx..."
        nginx -t && nginx -s reload || nginx
        
        return 0
    else
        log "❌ Error generando certificado"
        
        # Reiniciar nginx aunque haya error
        nginx -t && nginx -s reload || nginx
        
        return 1
    fi
}

# Función para configurar cron job si no existe
setup_cron_job() {
    local cron_command="0 12 * * 6 /var/www/html/docker/ssl-check.sh >> /var/log/ssl-check.log 2>&1"
    
    # Verificar si el cron job ya existe
    if crontab -l 2>/dev/null | grep -q "ssl-check.sh"; then
        log "✅ Tarea cron ya está configurada"
        return 0
    fi
    
    # Agregar cron job
    log "📅 Configurando tarea cron para sábados a las 12:00..."
    (crontab -l 2>/dev/null || true; echo "$cron_command") | crontab -
    
    # Verificar que se agregó correctamente
    if crontab -l | grep -q "ssl-check.sh"; then
        log "✅ Tarea cron configurada exitosamente"
    else
        log "❌ Error configurando tarea cron"
        return 1
    fi
}

# Función principal
main() {
    log "🔐 Iniciando verificación SSL para $DOMAIN..."
    
    # Instalar certbot si es necesario
    install_certbot
    
    # Verificar certificado existente
    if check_certificate "$CERT_PATH" && check_ssl_response "$DOMAIN"; then
        log "🎉 SSL está funcionando correctamente, no se requiere acción"
    else
        log "🔧 SSL no está funcionando, regenerando certificado..."
        if generate_certificate "$DOMAIN" "$EMAIL"; then
            log "✅ Certificado regenerado exitosamente"
            
            # Verificar nuevamente
            sleep 5
            if check_ssl_response "$DOMAIN"; then
                log "🎉 SSL ahora funciona correctamente"
            else
                log "⚠️  SSL regenerado pero el sitio aún no responde, puede necesitar tiempo"
            fi
        else
            log "❌ Error regenerando certificado"
            exit 1
        fi
    fi
    
    # Configurar cron job si estamos en el contenedor principal
    if [ -n "$SETUP_CRON" ] && [ "$SETUP_CRON" = "true" ]; then
        setup_cron_job
    fi
    
    log "✅ Verificación SSL completada"
}

# Ejecutar solo si se llama directamente
if [ "${BASH_SOURCE[0]}" = "${0}" ]; then
    main "$@"
fi