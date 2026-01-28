<?php

namespace App\Helpers;

class PermissionHelper
{
    const ROLE_ADMIN = 1;
    const ROLE_EMBARQUES = 2;
    const ROLE_CALIDAD = 3;
    const ROLE_INVITADO = 4;
    
    /**
     * Verificar si el usuario puede acceder al admin panel
     */
    public static function canAccessAdminPanel(): bool
    {
        $user = auth('moonshine')->user();
        return $user && $user->moonshine_user_role_id === self::ROLE_ADMIN;
    }
    
    /**
     * Verificar si el usuario puede crear eventos
     */
    public static function canCreateEvents(): bool
    {
        $user = auth('moonshine')->user();
        if (!$user) return false;
        
        return in_array($user->moonshine_user_role_id, [
            self::ROLE_ADMIN,
            self::ROLE_EMBARQUES,
            self::ROLE_CALIDAD,
        ]);
    }
    
    /**
     * Verificar si el usuario puede editar eventos
     */
    public static function canEditEvents(): bool
    {
        $user = auth('moonshine')->user();
        if (!$user) return false;
        
        return in_array($user->moonshine_user_role_id, [
            self::ROLE_ADMIN,
            self::ROLE_EMBARQUES,
            self::ROLE_CALIDAD,
        ]);
    }
    
    /**
     * Verificar si el usuario puede eliminar eventos
     */
    public static function canDeleteEvents(): bool
    {
        $user = auth('moonshine')->user();
        if (!$user) return false;
        
        // Solo Admin puede eliminar
        return $user->moonshine_user_role_id === self::ROLE_ADMIN;
    }
    
    /**
     * Verificar si el usuario puede subir PDFs
     */
    public static function canUploadPdfs(): bool
    {
        $user = auth('moonshine')->user();
        if (!$user) return false;
        
        return in_array($user->moonshine_user_role_id, [
            self::ROLE_ADMIN,
            self::ROLE_EMBARQUES,
            self::ROLE_CALIDAD,
        ]);
    }
    
    /**
     * Obtener nombre del rol
     */
    public static function getRoleName(?int $roleId = null): string
    {
        $user = auth('moonshine')->user();
        $id = $roleId ?? $user?->moonshine_user_role_id;
        
        return match($id) {
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_EMBARQUES => 'Embarques',
            self::ROLE_CALIDAD => 'Calidad',
            self::ROLE_INVITADO => 'Invitado',
            default => 'Sin rol',
        };
    }
}
