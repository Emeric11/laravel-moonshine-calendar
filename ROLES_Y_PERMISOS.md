# Sistema de Roles y Permisos - Calendario de Producción

## 📋 Roles Implementados

### 1. **Admin (ID: 1)**
- ✅ Acceso completo al Admin Panel
- ✅ Puede crear, editar y **eliminar** eventos
- ✅ Puede subir PDFs
- ✅ Ve todos los recursos en MoonShine

### 2. **Embarques (ID: 2)**
- ✅ Acceso a User Panel (calendario)
- ✅ Puede crear eventos
- ✅ Puede editar eventos
- ✅ Puede subir PDFs
- ❌ **NO puede eliminar eventos**
- ❌ NO ve el Admin Panel

### 3. **Calidad (ID: 3)**
- ✅ Acceso a User Panel (calendario)
- ✅ Puede crear eventos
- ✅ Puede editar eventos
- ✅ Puede subir PDFs
- ❌ **NO puede eliminar eventos**
- ❌ NO ve el Admin Panel

### 4. **Invitado (ID: 4)**
- ✅ Solo vista del calendario (read-only)
- ❌ NO puede crear eventos
- ❌ NO puede editar eventos
- ❌ NO puede eliminar eventos
- ❌ NO puede subir PDFs
- ❌ NO ve el Admin Panel

---

## 🔧 Archivos Creados

1. **database/seeders/RolesSeeder.php** - Crea los 4 roles
2. **app/Helpers/PermissionHelper.php** - Helper con métodos de verificación de permisos
3. **app/Http/Middleware/CheckUserRole.php** - Middleware para proteger rutas

---

## 🚀 Cómo Usar

### Crear Usuario con Rol Específico

```bash
php artisan moonshine:user
# Seleccionar el rol correspondiente al crear
```

O directamente en la base de datos:

```php
DB::table('moonshine_users')->insert([
    'moonshine_user_role_id' => 2, // 1=Admin, 2=Embarques, 3=Calidad, 4=Invitado
    'email' => 'embarques@empresa.com',
    'password' => bcrypt('password'),
    'name' => 'Usuario Embarques',
]);
```

### Verificar Permisos en Código

```php
use App\Helpers\PermissionHelper;

// Verificar si puede eliminar
if (PermissionHelper::canDeleteEvents()) {
    // Mostrar botón eliminar
}

// Verificar si puede acceder a admin
if (PermissionHelper::canAccessAdminPanel()) {
    // Mostrar enlace a admin
}
```

### Proteger Rutas

En `routes/web.php`:

```php
use App\Http\Middleware\CheckUserRole;

Route::middleware(['auth:moonshine', CheckUserRole::class.':admin'])->group(function () {
    // Solo Admin
});

Route::middleware(['auth:moonshine', CheckUserRole::class.':admin,embarques,calidad'])->group(function () {
    // Admin, Embarques y Calidad
});
```

---

## ✅ Funcionalidades Implementadas

### En MoonShine (Admin Panel)
- ✅ Botón "Crear" solo visible para Admin, Embarques y Calidad
- ✅ Botón "Editar" solo visible para Admin, Embarques y Calidad
- ✅ Botón "Eliminar" solo visible para Admin
- ✅ Campos de PDF solo editables para usuarios con permisos

### En User Panel (Calendario)
- ✅ Enlace "Admin Panel" solo visible para Admin
- ✅ Muestra nombre del usuario y su rol en sidebar
- ✅ Permisos aplicados en calendario para crear/editar/eliminar

---

## 🎯 Mejores Prácticas

### ✅ LO QUE DEBES HACER:

1. **Asignar roles al crear usuarios**
   ```bash
   php artisan moonshine:user
   ```

2. **Verificar permisos antes de mostrar botones**
   ```blade
   @if(\App\Helpers\PermissionHelper::canDeleteEvents())
       <button>Eliminar</button>
   @endif
   ```

3. **Usar el Helper en controladores**
   ```php
   if (!PermissionHelper::canCreateEvents()) {
       abort(403, 'No tienes permisos');
   }
   ```

### ❌ LO QUE NO DEBES HACER:

1. ❌ NO hardcodear IDs de roles en vistas
2. ❌ NO confiar solo en ocultar botones (verificar en backend)
3. ❌ NO eliminar las constantes de `PermissionHelper`

---

## 🔐 Seguridad Implementada

1. **Backend**: Permisos verificados en `CalendarEventResource::can()`
2. **Frontend**: Botones ocultos según rol
3. **Rutas**: Protegidas con middleware `CheckUserRole`
4. **Observer**: Funciona independiente de los roles

---

## 🧪 Cómo Probar

1. Crear usuarios con diferentes roles:
   ```bash
   php artisan moonshine:user
   ```

2. Iniciar sesión con cada usuario y verificar:
   - ¿Ve el enlace "Admin Panel"?
   - ¿Puede crear eventos?
   - ¿Puede editar eventos?
   - ¿Ve el botón "Eliminar"?

3. Intentar acceder a rutas protegidas:
   - `/admin` (solo Admin)
   - Crear evento (Admin, Embarques, Calidad)
   - Eliminar evento (solo Admin)

---

## 🆘 Solución de Problemas

### No se aplican los permisos
```bash
php artisan optimize:clear
```

### Usuario no tiene rol
```sql
UPDATE moonshine_users SET moonshine_user_role_id = 2 WHERE email = 'usuario@example.com';
```

### Error al ejecutar el seeder
```bash
php artisan migrate:fresh --seed
```

---

## 📊 Resumen de Permisos

| Acción | Admin | Embarques | Calidad | Invitado |
|--------|-------|-----------|---------|----------|
| Ver Admin Panel | ✅ | ❌ | ❌ | ❌ |
| Ver User Panel | ✅ | ✅ | ✅ | ✅ |
| Crear Evento | ✅ | ✅ | ✅ | ❌ |
| Editar Evento | ✅ | ✅ | ✅ | ❌ |
| Eliminar Evento | ✅ | ❌ | ❌ | ❌ |
| Subir PDFs | ✅ | ✅ | ✅ | ❌ |

---

**✅ Sistema completamente funcional sin romper nada existente.**
