# 🚀 LoveLink - Guía de Despliegue Completo

Esta guía cubre el despliegue completo de LoveLink desde cero, incluyendo configuración SSL, base de datos, seeders de temas y verificación del funcionamiento.

## 📋 **Prerrequisitos**

### **Servidor (Digital Ocean Droplet recomendado):**
- Ubuntu 22.04 LTS o superior
- 1 vCPU, 2GB RAM mínimo
- 25GB SSD storage
- IP pública asignada
- Dominio DNS apuntando al servidor

### **Herramientas necesarias:**
- Docker y Docker Compose
- Git
- SSH access al servidor

---

## 🔧 **Paso 1: Configuración inicial del servidor**

### **1.1 Conectar al servidor**
```bash
# Conectar via SSH
ssh root@tu_ip_servidor

# Actualizar sistema
apt update && apt upgrade -y

# Instalar dependencias básicas
apt install -y curl wget git
```

### **1.2 Instalar Docker**
```bash
# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Instalar Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Verificar instalación
docker --version
docker-compose --version
```

---

## 📂 **Paso 2: Clonar y configurar proyecto**

### **2.1 Clonar repositorio**
```bash
# Ir al directorio home
cd ~

# Clonar proyecto
git clone https://github.com/tu-usuario/lovelink.git LoveLink
cd LoveLink

# Dar permisos a scripts
chmod +x setup-ssl-host.sh ssl-setup.sh docker/ssl-check.sh
```

### **2.2 Configurar variables de entorno**
```bash
# Copiar archivo de variables
cp .env.prod.example .env

# Editar variables de producción (IMPORTANTE)
nano .env
```

**Variables críticas a configurar en `.env`:**
```env
# Aplicación
APP_NAME=LoveLink
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Generar clave nueva (IMPORTANTE)
APP_KEY=base64:tu_clave_app_generada

# Base de datos
DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=lovelink
DB_USERNAME=lovelink
DB_PASSWORD=tu_password_seguro

# SSL
SSL_DOMAIN=tu-dominio.com
SSL_EMAIL=tu-email@dominio.com

# Digital Ocean Spaces
AWS_ACCESS_KEY_ID=tu_do_spaces_access_key
AWS_SECRET_ACCESS_KEY=tu_do_spaces_secret_key
AWS_BUCKET=lovelink-storage

CLOUD_ACCESS_KEY_ID=tu_do_spaces_access_key
CLOUD_SECRET_ACCESS_KEY=tu_do_spaces_secret_key
CLOUD_BUCKET=lovelink-storage

# Media storage (S3 para producción)
FILESYSTEM_DISK=s3
MEDIA_DISK=media_cloud
```

---

## 🔐 **Paso 3: Configuración SSL automática**

### **3.1 Verificar DNS**
```bash
# Verificar que el dominio apunta al servidor
nslookup tu-dominio.com

# Debe mostrar la IP de tu servidor
```

### **3.2 Ejecutar configuración SSL automática**
```bash
# Ejecutar script de configuración SSL
./setup-ssl-host.sh
```

**¿Qué hace este script?**
- ✅ Instala Certbot
- ✅ Genera certificados SSL para tu dominio
- ✅ Configura renovación automática
- ✅ Reconstruye contenedores con SSL
- ✅ Verifica funcionamiento

### **3.3 Verificar SSL**
```bash
# Script de verificación rápida
cat > verify-ssl.sh << 'EOF'
#!/bin/bash
echo "🔍 === VERIFICACIÓN SSL ==="
echo "📊 Estado contenedores:"
docker-compose ps
echo
echo "🔐 Test HTTPS:"
curl -I https://tu-dominio.com
echo
echo "📋 Certificado válido hasta:"
sudo openssl x509 -enddate -noout -in /etc/letsencrypt/live/tu-dominio.com/fullchain.pem
EOF

chmod +x verify-ssl.sh
./verify-ssl.sh
```

---

## 🗄️ **Paso 4: Configuración de base de datos**

### **4.1 Generar clave de aplicación**
```bash
# Generar clave APP_KEY (si no la tienes)
docker-compose exec app php artisan key:generate

# El comando actualizará automáticamente el .env
```

### **4.2 Ejecutar migraciones**
```bash
# Ejecutar migraciones para crear tablas
docker-compose exec app php artisan migrate --force

# Verificar que las tablas se crearon
docker-compose exec app php artisan migrate:status
```

### **4.3 Crear enlace de storage**
```bash
# Crear enlace simbólico para archivos públicos
docker-compose exec app php artisan storage:link
```

---

## 🎨 **Paso 5: Ejecutar seeders de temas**

### **5.1 Seeders disponibles**
El proyecto incluye dos seeders de temas:

**ThemeSeeder.php** - Temas principales:
- Noche Estrellada (oscuro con dorado)
- Pasión Nocturna (oscuro con rojo)
- Bosque Neón (oscuro con verde esmeralda)

**SystemThemeSeeder.php** - Temas del sistema:
- Elegante Clásico (dorado y blanco)
- Romance Rosa (tonos rosados)
- Naturaleza Verde (verdes frescos)
- Océano Azul (tonos azules)
- Atardecer Cálido (naranjas y amarillos)

### **5.2 Ejecutar seeders individualmente**
```bash
# Ejecutar seeder principal de temas
docker-compose exec app php artisan db:seed --class=ThemeSeeder

# Ejecutar seeder de temas del sistema
docker-compose exec app php artisan db:seed --class=SystemThemeSeeder

# Verificar que se crearon los temas
docker-compose exec app php artisan tinker
# Dentro de tinker:
# App\Models\Theme::count()
# App\Models\Theme::all()
# exit
```

### **5.3 Ejecutar todos los seeders**
```bash
# Ejecutar todos los seeders disponibles
docker-compose exec app php artisan db:seed

# O ejecutar seeders específicos
docker-compose exec app php artisan db:seed --class=DatabaseSeeder
```

### **5.4 Re-ejecutar seeders (si es necesario)**
```bash
# Limpiar y volver a ejecutar migraciones con seeders
docker-compose exec app php artisan migrate:fresh --seed --force

# CUIDADO: Esto eliminará todos los datos existentes
```

---

## ☁️ **Paso 6: Configurar Digital Ocean Spaces**

### **6.1 Crear bucket en Digital Ocean**
1. Ir a **Digital Ocean Dashboard**
2. **Spaces** → **Create Space**
3. **Configuración:**
   - Name: `lovelink-storage`
   - Region: `Amsterdam 3 (ams3)`
   - CDN: Habilitar
   - Access: Private (recomendado)

### **6.2 Generar claves API**
1. **API** → **Spaces Keys** → **Generate New Key**
2. Copiar **Access Key ID** y **Secret Key**
3. Actualizar variables en `.env`:
   ```bash
   nano .env
   # Actualizar:
   # AWS_ACCESS_KEY_ID=tu_access_key_real
   # AWS_SECRET_ACCESS_KEY=tu_secret_key_real
   # CLOUD_ACCESS_KEY_ID=tu_access_key_real
   # CLOUD_SECRET_ACCESS_KEY=tu_secret_key_real
   ```

### **6.3 Verificar conexión a Spaces**
```bash
# Test de conectividad con DO Spaces
docker-compose exec app php artisan tinker

# Dentro de tinker:
Storage::disk('media_cloud')->put('test.txt', 'Hello LoveLink!');
Storage::disk('media_cloud')->exists('test.txt');
Storage::disk('media_cloud')->url('test.txt');
exit
```

---

## ✅ **Paso 7: Verificación completa**

### **7.1 Script de verificación integral**
```bash
cat > full-verification.sh << 'EOF'
#!/bin/bash
echo "🔍 === VERIFICACIÓN COMPLETA LOVELINK ==="
echo

echo "📊 1. Estado de contenedores:"
docker-compose ps
echo

echo "🌐 2. Test HTTP/HTTPS:"
echo "HTTP (debe redirigir):"
curl -I http://tu-dominio.com 2>/dev/null | head -1
echo "HTTPS:"
curl -I https://tu-dominio.com 2>/dev/null | head -1
echo

echo "🗄️ 3. Estado de base de datos:"
docker-compose exec app php -r "
try {
    \$pdo = new PDO('mysql:host=mariadb;dbname=lovelink', 'lovelink', 'tu_password');
    echo 'Database: ✅ Conectado\n';
} catch (Exception \$e) {
    echo 'Database: ❌ Error: ' . \$e->getMessage() . '\n';
}
"

echo "🎨 4. Temas disponibles:"
docker-compose exec app php -r "
\$themes = \App\Models\Theme::count();
echo 'Total temas: ' . \$themes . '\n';
"

echo "☁️ 5. Test Digital Ocean Spaces:"
docker-compose exec app php -r "
try {
    \Storage::disk('media_cloud')->put('health-check.txt', 'OK');
    echo 'DO Spaces: ✅ Conectado\n';
    \Storage::disk('media_cloud')->delete('health-check.txt');
} catch (Exception \$e) {
    echo 'DO Spaces: ❌ Error: ' . \$e->getMessage() . '\n';
}
"

echo "🔐 6. Certificado SSL:"
sudo openssl x509 -enddate -noout -in /etc/letsencrypt/live/tu-dominio.com/fullchain.pem 2>/dev/null || echo "❌ Sin certificado SSL"
echo

echo "✅ Verificación completada"
echo "🌐 Tu aplicación está en: https://tu-dominio.com"
EOF

chmod +x full-verification.sh
./full-verification.sh
```

### **7.2 URLs importantes**
- **🌐 Aplicación principal:** `https://tu-dominio.com`
- **📊 Dashboard de usuario:** `https://tu-dominio.com/dashboard`
- **🎨 Crear landing:** `https://tu-dominio.com/landings/create`

---

## 📋 **Comandos útiles pos-despliegue**

### **Gestión de la aplicación**
```bash
# Ver logs en tiempo real
docker-compose logs -f app

# Reiniciar aplicación
docker-compose restart app

# Actualizar código desde Git
git pull origin main
docker-compose build --no-cache
docker-compose up -d

# Limpiar cachés Laravel
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### **Gestión de base de datos**
```bash
# Backup de base de datos
docker-compose exec mariadb mysqldump -u lovelink -p lovelink > backup.sql

# Conectar a base de datos
docker-compose exec mariadb mysql -u lovelink -p lovelink

# Ver estado de migraciones
docker-compose exec app php artisan migrate:status
```

### **Gestión de SSL**
```bash
# Verificar renovación SSL
sudo certbot renew --dry-run

# Forzar renovación SSL
sudo certbot renew --force-renewal

# Ver certificados activos
sudo certbot certificates
```

---

## 🚨 **Troubleshooting común**

### **Problema: 502 Bad Gateway**
```bash
# Verificar logs de nginx
docker-compose logs app | grep nginx

# Reiniciar servicios
docker-compose restart app

# Verificar configuración nginx
docker-compose exec app nginx -t
```

### **Problema: Base de datos no conecta**
```bash
# Verificar estado de MariaDB
docker-compose logs mariadb

# Verificar conexión
docker-compose exec app php artisan migrate:status
```

### **Problema: Archivos no se suben a DO Spaces**
```bash
# Verificar configuración
docker-compose exec app php -r "
echo 'FILESYSTEM_DISK: ' . env('FILESYSTEM_DISK') . '\n';
echo 'MEDIA_DISK: ' . env('MEDIA_DISK') . '\n';
"

# Test manual
docker-compose exec app php artisan tinker
# Storage::disk('media_cloud')->put('test.txt', 'test');
```

---

## 📊 **Resumen de configuración exitosa**

**✅ Después del despliegue deberías tener:**

1. **🌐 HTTPS funcionando** en tu dominio
2. **🗄️ Base de datos** MariaDB conectada
3. **🎨 8 temas** precargados en la base de datos
4. **☁️ DO Spaces** configurado para archivos multimedia
5. **🔐 SSL automático** con renovación cada 60 días
6. **🚀 Aplicación Laravel** funcionando en producción

**🎯 URLs de prueba:**
- Landing pública: `https://tu-dominio.com/p/test-slug`
- Dashboard: `https://tu-dominio.com/dashboard`
- Registro: `https://tu-dominio.com/register`

---

## 📞 **Soporte y mantenimiento**

### **Logs importantes:**
```bash
# Logs de SSL
docker-compose exec app tail -f /var/log/ssl-check.log

# Logs de Laravel
docker-compose exec app tail -f storage/logs/laravel.log

# Logs de sistema
docker-compose logs app --tail 50
```

### **Monitoreo automático:**
- ✅ SSL se renueva automáticamente cada sábado a las 12:00 PM
- ✅ Backup automático de base de datos via volúmenes Docker
- ✅ Logs rotados automáticamente para evitar acumulación

¡Tu aplicación **LoveLink** está lista para producción! 🎉