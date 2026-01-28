<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Usuario - Calendario')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f5f6fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar */
        .navbar-custom {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand i {
            font-size: 1.8rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white !important;
        }
        
        .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white !important;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            height: calc(100vh - 76px);
            position: fixed;
            left: 0;
            top: 76px;
            overflow-y: auto;
            padding: 1.5rem 0;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 0.75rem;
            font-weight: 500;
        }
        
        .sidebar-menu a i {
            width: 20px;
            font-size: 1.1rem;
        }
        
        .sidebar-menu a:hover {
            background-color: #f7fafc;
            color: #0ea5e9;
            padding-left: 2rem;
        }
        
        .sidebar-menu a.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }
        
        .sidebar-menu a.active:hover {
            padding-left: 1.5rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            margin-top: 76px;
            padding: 2rem;
            min-height: calc(100vh - 76px);
            flex: 1;
            transition: all 0.3s ease;
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 90px;
            left: 20px;
            z-index: 1000;
            background: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 999;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 998;
            }
            
            .overlay.show {
                display: block;
            }
        }
        
        /* Card */
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }
        
        /* User Info */
        .user-info {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .user-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }
        
        .user-role {
            font-size: 0.85rem;
            color: #718096;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="fas fa-calendar-alt"></i>
                <span>Sistema de Producción</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @php
                        $canAccessAdmin = class_exists('App\Helpers\PermissionHelper') 
                            && \App\Helpers\PermissionHelper::canAccessAdminPanel();
                    @endphp
                    
                    @if($canAccessAdmin)
                    <li class="nav-item">
                        <a class="nav-link" href="/admin" target="_top">
                            <i class="fas fa-cog me-1"></i>Admin Panel
                        </a>
                    </li>
                    @endif
                    
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="event.preventDefault(); alert('Función de logout');">
                            <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Overlay for mobile -->
    <div class="overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            @php
                $user = auth('moonshine')->user();
                $roleName = class_exists('App\Helpers\PermissionHelper') 
                    ? \App\Helpers\PermissionHelper::getRoleName() 
                    : 'Usuario';
            @endphp
            <div class="user-name">{{ $user?->name ?? 'Usuario' }}</div>
            <div class="user-role">{{ $roleName }}</div>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="/calendar" class="{{ request()->is('calendar') ? 'active' : '' }}">
                    <i class="fas fa-calendar-day"></i>
                    <span>Calendario</span>
                </a>
            </li>
            <li>
                <a href="/calendar" class="{{ request()->is('eventos') ? 'active' : '' }}">
                    <i class="fas fa-list"></i>
                    <span>Mis Eventos</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="alert('Próximamente')">
                    <i class="fas fa-chart-line"></i>
                    <span>Reportes</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="alert('Próximamente')">
                    <i class="fas fa-file-pdf"></i>
                    <span>Documentos</span>
                </a>
            </li>
            @php
                $canAccessAdmin = class_exists('App\Helpers\PermissionHelper') 
                    && \App\Helpers\PermissionHelper::canAccessAdminPanel();
            @endphp
            @if($canAccessAdmin)
            <li>
                <a href="/admin" target="_top">
                    <i class="fas fa-shield-alt"></i>
                    <span>Panel Admin</span>
                </a>
            </li>
            @endif
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
    </script>
</body>
</html>
