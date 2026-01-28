# Despliegue en Producción SIN VirtualHost
## Dominio: cartoaplicaciones (gestionado por DNS)

---

## ✅ VERIFICACIONES PREVIAS

### 1. Verificar DocumentRoot con el administrador
**CRÍTICO**: El dominio `cartoaplicaciones` DEBE apuntar a:
```
/ruta/completa/a/laravelApp_calendar/public/
```

**NO debe apuntar a**:
```
/ruta/completa/a/laravelApp_calendar/  ❌ INCORRECTO
```

Si no apunta a `public/`, solicita al administrador que configure:
```apache
DocumentRoot "/ruta/completa/a/laravelApp_calendar/public"
```

---

## 🔧 PREPARACIÓN LOCAL (Antes de Subir)

### 1. Configurar .env para producción
```env
APP_NAME="Calendar App"
APP_ENV=production
APP_KEY=base64:... (mantener el existente)
APP_DEBUG=false
APP_URL=http://cartoaplicaciones

DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
```

### 2. Optimizar localmente
```bash
# Limpiar cachés antiguos
php artisan optimize:clear

# Instalar dependencias de producción
composer install --optimize-autoloader --no-dev

# Generar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Compilar assets
npm run build

# Optimizar autoload
composer dump-autoload --optimize
```

### 3. Verificar archivo .htaccess en public/
Asegúrate que `public/.htaccess` exista con este contenido:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## 📤 SUBIR ARCHIVOS AL SERVIDOR

### Archivos a subir (TODO excepto):
```
❌ NO subir:
/node_modules/
/vendor/ (si se reinstala en servidor)
/.env (configurar manualmente en servidor)
/storage/logs/*.log
/.git/
```

✅ SÍ subir:
```
/app/
/bootstrap/ (incluyendo /bootstrap/cache/)
/config/
/database/ (incluyendo database.sqlite si tiene datos)
/public/ (TODO, incluyendo /public/build/)
/resources/
/routes/
/storage/ (estructura de carpetas, sin logs)
/vendor/ (si no se reinstala en servidor)
.htaccess (en public/)
artisan
composer.json
composer.lock
package.json
```

### Método de transferencia
- FTP/SFTP: FileZilla, WinSCP
- Panel de control: cPanel, Plesk
- Git: Si el servidor soporta git pull

---

## 🔐 CONFIGURACIÓN EN SERVIDOR

### 1. Permisos de archivos
Si tienes acceso SSH:
```bash
cd /ruta/a/laravelApp_calendar

# Permisos de escritura
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod 664 database/database.sqlite

# Permisos del propietario (si es necesario)
chown -R www-data:www-data storage bootstrap/cache database
```

Si NO tienes SSH pero tienes panel de control:
- Buscar "File Manager" o "Administrador de Archivos"
- Seleccionar carpeta `storage` → Permisos → 775 (rwxrwxr-x)
- Seleccionar carpeta `bootstrap/cache` → Permisos → 775
- Seleccionar archivo `database/database.sqlite` → Permisos → 664

### 2. Crear/editar .env en servidor
Crear archivo `.env` en la raíz con:
```env
APP_NAME="Calendar App"
APP_ENV=production
APP_KEY=base64:j1NaxNpqbD2rj6495qyCCK4wtJDuvhhHJhoHSME0j7k=
APP_DEBUG=false
APP_URL=http://cartoaplicaciones

DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
MAIL_MAILER=log
```

### 3. Crear symlink para storage
**Con SSH:**
```bash
php artisan storage:link
```

**Sin SSH (método manual):**
1. Ir a `public/` en el File Manager
2. Crear enlace simbólico llamado `storage` que apunte a `../storage/app/public`
3. Si no es posible, copiar manualmente los archivos de `storage/app/public/` a `public/storage/`

---

## 🗄️ BASE DE DATOS

### Opción 1: Subir SQLite existente
```bash
# Subir database/database.sqlite con todos los datos
# Asegurarse de permisos 664
```

### Opción 2: Crear nueva base de datos
**Con SSH:**
```bash
php artisan migrate --force
php artisan db:seed --force --class=RolesSeeder
php artisan moonshine:user
```

**Sin SSH:**
- Subir `database.sqlite` vacío
- Usar herramienta como phpLiteAdmin (si disponible)
- O ejecutar migraciones localmente, subir el archivo

---

## 🧪 PRUEBAS DESPUÉS DE SUBIR

### 1. Verificar acceso básico
```
http://cartoaplicaciones
```
**Debe mostrar:** Página de inicio de Laravel (o tu vista welcome)

**Si ves listado de carpetas:** DocumentRoot NO apunta a public/ ⚠️

### 2. Verificar MoonShine
```
http://cartoaplicaciones/moonshine
```
**Debe mostrar:** Login de MoonShine

### 3. Verificar permisos
Intenta crear un evento en MoonShine
**Si error de escritura:** Permisos incorrectos en storage/

### 4. Verificar PDFs
Sube un PDF en un evento
**Si no se guarda:** Permisos en `storage/app/public/facturas_certf_pdf/`

### 5. Verificar notificaciones
Crea un evento como Admin, revisa si otros usuarios reciben notificación

---

## 🚨 PROBLEMAS COMUNES

### 1. Error 500 - Internal Server Error
**Causas:**
- `.env` mal configurado o sin APP_KEY
- Permisos insuficientes en storage/bootstrap
- mod_rewrite deshabilitado

**Solución:**
```bash
# Ver logs
cat storage/logs/laravel.log
```

### 2. "The stream or file could not be opened"
**Causa:** Permisos en storage/logs/

**Solución:**
```bash
chmod -R 775 storage/logs
```

### 3. "No application encryption key"
**Causa:** .env sin APP_KEY

**Solución:**
```bash
php artisan key:generate --force
```

### 4. Rutas no funcionan (404 en /moonshine)
**Causa:** .htaccess no funciona o mod_rewrite deshabilitado

**Verificar con administrador:**
- mod_rewrite habilitado
- AllowOverride All en configuración de Apache

### 5. Archivos estáticos no cargan (CSS/JS)
**Causa:** Ruta de assets incorrecta

**Solución en .env:**
```env
ASSET_URL=http://cartoaplicaciones
```

### 6. PDFs no se guardan
**Causa:** storage/app/public sin permisos

**Solución:**
```bash
chmod -R 775 storage/app/public
# Verificar symlink
ls -la public/storage
```

---

## 📊 VERIFICACIÓN DE REQUISITOS DEL SERVIDOR

### Requisitos mínimos PHP
```
✅ PHP >= 8.2
✅ SQLite PDO Driver
✅ OpenSSL Extension
✅ Mbstring Extension
✅ Tokenizer Extension
✅ XML Extension
✅ Ctype Extension
✅ JSON Extension
✅ BCMath Extension
✅ Fileinfo Extension
✅ GD Extension (para imágenes)
```

### Verificar con phpinfo()
Crear archivo `public/info.php`:
```php
<?php phpinfo(); ?>
```
Acceder: `http://cartoaplicaciones/info.php`
**ELIMINAR después de verificar**

---

## 🔄 ACTUALIZACIONES FUTURAS

### Sin acceso SSH
1. Hacer cambios en local
2. Ejecutar `php artisan config:cache` localmente
3. Subir archivos modificados vía FTP
4. Subir `bootstrap/cache/config.php` actualizado

### Con acceso SSH
```bash
# Subir archivos
# Luego en servidor:
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🎯 CHECKLIST DE DESPLIEGUE

- [ ] Verificar que dominio apunte a /public/
- [ ] Configurar .env local para producción
- [ ] Ejecutar optimizaciones locales
- [ ] Compilar assets (npm run build)
- [ ] Subir archivos al servidor (excepto /vendor si se reinstala)
- [ ] Crear/editar .env en servidor
- [ ] Configurar permisos: storage/, bootstrap/cache/, database.sqlite
- [ ] Crear symlink storage (o copiar archivos)
- [ ] Migrar base de datos (o subir SQLite)
- [ ] Crear usuario Admin en MoonShine
- [ ] Probar acceso: http://cartoaplicaciones
- [ ] Probar MoonShine: http://cartoaplicaciones/moonshine
- [ ] Probar CRUD de eventos
- [ ] Probar subida de PDFs
- [ ] Probar notificaciones
- [ ] Verificar calendario de usuarios
- [ ] Probar permisos por roles
- [ ] Eliminar phpinfo.php si se creó

---

## 📞 CONTACTO CON ADMINISTRADOR DEL SERVIDOR

### Preguntas críticas a realizar:

1. **"¿El dominio cartoaplicaciones apunta a la carpeta public/ o a la raíz?"**
   - Si apunta a raíz: Solicitar cambio a /public/

2. **"¿Está habilitado mod_rewrite en Apache?"**
   - Necesario para que funcione .htaccess

3. **"¿Tengo permisos para ejecutar comandos PHP desde terminal/SSH?"**
   - Si NO: Ejecutar todo localmente antes de subir

4. **"¿Qué versión de PHP tiene el servidor?"**
   - Mínimo: PHP 8.2

5. **"¿Puedo crear enlaces simbólicos (symlinks)?"**
   - Necesario para storage link
   - Alternativa: Copiar archivos manualmente

6. **"¿Dónde puedo ver los logs de errores de Apache/PHP?"**
   - Para troubleshooting

---

## ✅ CONCLUSIÓN

**Laravel + MoonShine funcionarán SIN VirtualHost** siempre que:

1. ✅ El dominio apunte a `/public/`
2. ✅ Tengas permisos de escritura en storage/
3. ✅ mod_rewrite esté habilitado
4. ✅ PHP >= 8.2 con extensiones requeridas

**Limitación principal:** Si el dominio NO apunta a `/public/`, solicita al administrador que lo configure. Es el único cambio crítico en la configuración del servidor.
