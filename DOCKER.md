# LoveLink Docker Setup con SSL Automático

## 🚀 Inicio Rápido (Recomendado)

```bash
# 1. Hacer pull del código actualizado
git pull origin main

# 2. Reconstruir imagen con SSL automático
docker compose build --no-cache

# 3. Lanzar aplicación (SSL se configura automáticamente)
docker compose up -d
```

**¡Eso es todo!** 🎉 SSL se verifica y configura automáticamente cada inicio y cada sábado.

---

## 🔐 SSL Automático Integrado

### ✨ Características Automáticas

- ✅ **Verificación al inicio**: SSL se verifica cada vez que se inicia el contenedor
- ✅ **Regeneración automática**: Si el certificado expira en <30 días, se regenera automáticamente
- ✅ **Cron automático**: Tarea programada sábados 12:00 PM para verificación
- ✅ **Sin duplicados**: No programa tareas cron si ya existen
- ✅ **Logs detallados**: Seguimiento completo en `/var/log/ssl-check.log`

### 📋 ¿Qué hace automáticamente?

1. **Al iniciar contenedor**:
   - Verifica si SSL está funcionando
   - Regenera certificado si es necesario
   - Configura cron para verificación semanal
   - Inicia todos los servicios

2. **Cada sábado a las 12:00**:
   - Ejecuta verificación SSL automática
   - Renueva certificados próximos a expirar
   - Reinicia Nginx si es necesario

---

## 🐳 Arquitectura del Sistema

```
Internet (HTTPS/HTTP)
       ↓
🔒 Nginx SSL Termination (puerto 443/80)
       ↓
🐘 PHP-FPM (puerto 9000)
       ↓
🌟 Laravel Application
       ↓
🗄️ MariaDB (puerto 3306)

🤖 SSL-Check Script (verificación automática)
📅 Cron Job (sábados 12:00 PM)
```

## 📋 Comandos de Gestión

### Básicos
```bash
# Ver logs en tiempo real
docker compose logs -f app

# Reiniciar aplicación
docker compose restart app

# Parar todo
docker compose down
```

### SSL específicos
```bash
# Verificar SSL manualmente
docker compose exec app /var/www/html/docker/ssl-check.sh

# Ver logs de SSL
docker compose exec app tail -f /var/log/ssl-check.log

# Ver estado del cron
docker compose exec app crontab -l
```

### Laravel
```bash
# Ejecutar migraciones
docker compose exec app php artisan migrate

# Limpiar caches
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
```

## 🔧 Variables de Entorno SSL

Las siguientes variables se configuran automáticamente en `docker-compose.yml`:

```yaml
environment:
  - SSL_DOMAIN=lovelink.kevinponcedev.xyz
  - SSL_EMAIL=kevin@kevinponcedev.xyz
  - SETUP_CRON=true  # Configura cron automáticamente
```

## 📁 Archivos SSL Importantes

```
/etc/letsencrypt/                    # Certificados (mapeado desde host)
/var/www/html/docker/ssl-check.sh    # Script de verificación automática
/var/log/ssl-check.log              # Logs de verificación SSL
```

## 🚨 Troubleshooting SSL

### Problema: SSL no funciona después del despliegue
```bash
# 1. Ver logs del SSL check
docker compose exec app tail -20 /var/log/ssl-check.log

# 2. Ejecutar verificación manual
docker compose exec app /var/www/html/docker/ssl-check.sh

# 3. Verificar certificados en el host
sudo ls -la /etc/letsencrypt/live/lovelink.kevinponcedev.xyz/

# 4. Verificar respuesta HTTPS
curl -I https://lovelink.kevinponcedev.xyz
```

### Problema: Cron job no se ejecuta
```bash
# Ver tareas programadas
docker compose exec app crontab -l

# Verificar servicio cron
docker compose exec app service cron status

# Ejecutar verificación manual
docker compose exec app /var/www/html/docker/ssl-check.sh
```

### Problema: Certificado no se renueva
```bash
# Forzar renovación
docker compose exec app certbot renew --force-renewal

# Reiniciar nginx
docker compose exec app nginx -s reload
```

## 📊 Logs y Monitoreo

### Ver logs SSL
```bash
# Logs completos SSL
docker compose exec app cat /var/log/ssl-check.log

# Logs en tiempo real
docker compose exec app tail -f /var/log/ssl-check.log

# Logs del contenedor
docker compose logs app | grep -i ssl
```

### Ver estado de servicios
```bash
# Estado general
docker compose ps

# Procesos dentro del contenedor
docker compose exec app ps aux

# Verificar puertos
docker compose exec app netstat -tlnp
```

## 🎯 URLs de Acceso

- **🌐 Aplicación HTTPS**: https://lovelink.kevinponcedev.xyz
- **📊 Dashboard**: https://lovelink.kevinponcedev.xyz/dashboard
- **🔧 Admin**: https://lovelink.kevinponcedev.xyz/admin

---

## 📝 Notas Importantes

- ✅ **Completamente automatizado**: Solo necesitas `git pull` y `docker compose up -d`
- ✅ **Sin mantenimiento manual**: SSL se gestiona automáticamente
- ✅ **Tolerante a fallos**: Continúa funcionando aunque SSL tenga problemas temporales
- ✅ **Logs completos**: Seguimiento detallado de todas las operaciones SSL
