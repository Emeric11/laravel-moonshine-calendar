<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Roles permitidos:
     * 1 = Admin
     * 2 = Embarques
     * 3 = Calidad
     * 4 = Invitado
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth('moonshine')->user();
        
        if (!$user) {
            return redirect()->route('moonshine.login');
        }
        
        // Convertir nombres de roles a IDs
        $roleMap = [
            'admin' => 1,
            'embarques' => 2,
            'calidad' => 3,
            'invitado' => 4,
        ];
        
        $allowedRoleIds = array_map(fn($role) => $roleMap[$role] ?? null, $roles);
        
        if (!in_array($user->moonshine_user_role_id, $allowedRoleIds)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        
        return $next($request);
    }
}
