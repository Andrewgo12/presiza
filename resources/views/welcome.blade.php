<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sistema de Gestión de Evidencias - Hospital</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- CSS Framework -->
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js'])

    <!-- Additional Styles for Enhanced UI -->
    <style>
        :root {
            --primary-50: #eff6ff;
            --primary-100: #dbeafe;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
        }
    </style>
</head>
<body class="welcome-container">
    <div class="min-h-full">
        <!-- Enhanced Navigation with Medical Theme -->
        <header class="welcome-header">
            <nav class="welcome-nav">
                <div class="welcome-logo">
                    <div class="welcome-logo-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <span>EvidenceManager Pro</span>
                </div>
                <div class="welcome-nav-links">
                    @guest
                        <a href="#features" class="welcome-nav-link">Características</a>
                        <a href="#demo" class="welcome-nav-link">Demo</a>
                        <a href="{{ route('login') }}" class="welcome-nav-link">Iniciar Sesión</a>
                        <a href="{{ route('login') }}" class="welcome-login-btn">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Acceder al Sistema
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="welcome-login-btn">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>
                    @endguest
                </div>
            </nav>
        </header>

        <!-- Enhanced Hero Section -->
        <section class="welcome-hero">
            <div class="welcome-hero-badge">
                🏥 Certificado para Instituciones de Salud
            </div>
            <h1 class="welcome-hero-title">
                <span class="block">Sistema de Gestión</span>
                <span class="block highlight">de Evidencias Médicas</span>
            </h1>
            <p class="welcome-hero-subtitle">
                Plataforma profesional diseñada específicamente para hospitales y centros médicos.
                Gestión segura de evidencias con estándares de grado bancario, trazabilidad completa
                y cumplimiento normativo para el sector salud.
            </p>
            <div class="welcome-hero-actions">
                <a href="{{ route('login') }}" class="welcome-hero-btn-primary">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Acceder al Sistema
                </a>
                <a href="#demo" class="welcome-hero-btn-secondary">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ver Demostración
                </a>
            </div>
        </section>

        <!-- Features Section -->
        <div class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Características</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Todo lo que necesitas para gestionar evidencias
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Herramientas profesionales diseñadas para hospitales y organizaciones que requieren el más alto nivel de seguridad y trazabilidad.
                    </p>
                </div>

                <div class="mt-10">
                    <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
                        <div class="relative">
                            <dt>
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Gestión de Evidencias</p>
                            </dt>
                            <dd class="mt-2 ml-16 text-base text-gray-500">
                                Registro, seguimiento y análisis completo de evidencias con flujos de aprobación y trazabilidad completa.
                            </dd>
                        </div>

                        <div class="relative">
                            <dt>
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Gestión de Proyectos</p>
                            </dt>
                            <dd class="mt-2 ml-16 text-base text-gray-500">
                                Organización de proyectos con hitos, asignación de equipos y seguimiento de progreso en tiempo real.
                            </dd>
                        </div>

                        <div class="relative">
                            <dt>
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </div>
                                <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Control de Acceso</p>
                            </dt>
                            <dd class="mt-2 ml-16 text-base text-gray-500">
                                Sistema de roles y permisos granular con autenticación de dos factores y auditoría completa.
                            </dd>
                        </div>

                        <div class="relative">
                            <dt>
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                </div>
                                <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Analytics y Reportes</p>
                            </dt>
                            <dd class="mt-2 ml-16 text-base text-gray-500">
                                Dashboards interactivos con métricas en tiempo real y generación automática de reportes.
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Demo Credentials Section -->
        <div id="demo" class="bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Acceso de Demostración</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Prueba el sistema con credenciales de demo
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Utiliza cualquiera de estas credenciales para explorar las diferentes funcionalidades según el rol de usuario.
                    </p>
                </div>

                <div class="welcome-demo-grid">
                    <!-- Admin Role - Red Theme -->
                    <div class="welcome-demo-card admin">
                        <div class="welcome-demo-card-header">
                            <div class="welcome-demo-card-icon admin">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="welcome-demo-card-badge admin">ADMIN</div>
                        </div>
                        <div class="welcome-demo-card-body">
                            <h3 class="welcome-demo-card-title">Administrador</h3>
                            <p class="welcome-demo-card-description">Control total del sistema, gestión de usuarios y configuraciones</p>
                            <div class="welcome-demo-card-credentials">
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Email:</span>
                                    <span class="welcome-demo-credential-value">admin@hospital.gov.co</span>
                                </div>
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Password:</span>
                                    <span class="welcome-demo-credential-value">password</span>
                                </div>
                            </div>
                        </div>
                        <div class="welcome-demo-card-footer">
                            <a href="{{ route('login') }}" class="welcome-demo-card-btn admin">Probar Rol</a>
                        </div>
                    </div>

                    <!-- Medical Role - Blue Theme -->
                    <div class="welcome-demo-card medical">
                        <div class="welcome-demo-card-header">
                            <div class="welcome-demo-card-icon medical">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <div class="welcome-demo-card-badge medical">MÉDICO</div>
                        </div>
                        <div class="welcome-demo-card-body">
                            <h3 class="welcome-demo-card-title">Médico Investigador</h3>
                            <p class="welcome-demo-card-description">Gestión de evidencias médicas y evaluación de casos clínicos</p>
                            <div class="welcome-demo-card-credentials">
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Email:</span>
                                    <span class="welcome-demo-credential-value">medico@hospital.gov.co</span>
                                </div>
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Password:</span>
                                    <span class="welcome-demo-credential-value">password</span>
                                </div>
                            </div>
                        </div>
                        <div class="welcome-demo-card-footer">
                            <a href="{{ route('login') }}" class="welcome-demo-card-btn medical">Probar Rol</a>
                        </div>
                    </div>

                    <!-- EPS/Analyst Role - Green Theme -->
                    <div class="welcome-demo-card eps">
                        <div class="welcome-demo-card-header">
                            <div class="welcome-demo-card-icon eps">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                            <div class="welcome-demo-card-badge eps">EPS</div>
                        </div>
                        <div class="welcome-demo-card-body">
                            <h3 class="welcome-demo-card-title">Analista EPS</h3>
                            <p class="welcome-demo-card-description">Análisis de datos, reportes y seguimiento de indicadores</p>
                            <div class="welcome-demo-card-credentials">
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Email:</span>
                                    <span class="welcome-demo-credential-value">eps@hospital.gov.co</span>
                                </div>
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Password:</span>
                                    <span class="welcome-demo-credential-value">password</span>
                                </div>
                            </div>
                        </div>
                        <div class="welcome-demo-card-footer">
                            <a href="{{ route('login') }}" class="welcome-demo-card-btn eps">Probar Rol</a>
                        </div>
                    </div>

                    <!-- Systems Role - Orange Theme -->
                    <div class="welcome-demo-card systems">
                        <div class="welcome-demo-card-header">
                            <div class="welcome-demo-card-icon systems">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="welcome-demo-card-badge systems">SISTEMAS</div>
                        </div>
                        <div class="welcome-demo-card-body">
                            <h3 class="welcome-demo-card-title">Administrador de Sistemas</h3>
                            <p class="welcome-demo-card-description">Monitoreo técnico, respaldos y mantenimiento del sistema</p>
                            <div class="welcome-demo-card-credentials">
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Email:</span>
                                    <span class="welcome-demo-credential-value">sistemas@hospital.gov.co</span>
                                </div>
                                <div class="welcome-demo-credential">
                                    <span class="welcome-demo-credential-label">Password:</span>
                                    <span class="welcome-demo-credential-value">password</span>
                                </div>
                            </div>
                        </div>
                        <div class="welcome-demo-card-footer">
                            <a href="{{ route('login') }}" class="welcome-demo-card-btn systems">Probar Rol</a>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Probar el Sistema Ahora
                        <svg class="ml-2 -mr-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center space-x-6 md:order-2">
                    <p class="text-center text-sm text-gray-500">
                        &copy; {{ date('Y') }} Sistema de Gestión de Evidencias. Todos los derechos reservados.
                    </p>
                </div>
                <div class="mt-8 md:mt-0 md:order-1">
                    <p class="text-center text-sm text-gray-500">
                        Desarrollado con estándares de seguridad bancaria para instituciones de salud.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
