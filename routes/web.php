<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Ruta raíz - redirigir al dashboard si está autenticado, sino al login
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard') 
        : redirect()->route('login');
});

// Rutas de autenticación
require __DIR__.'/auth.php';

// Rutas protegidas por autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Gestión de archivos
    Route::resource('files', FileController::class);
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::post('files/{file}/share', [FileController::class, 'share'])->name('files.share');
    Route::get('files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    
    // Gestión de evidencias
    Route::resource('evidences', EvidenceController::class);
    Route::post('evidences/{evidence}/evaluate', [EvidenceController::class, 'evaluate'])->name('evidences.evaluate');
    Route::patch('evidences/{evidence}/status', [EvidenceController::class, 'updateStatus'])->name('evidences.status');
    Route::get('evidences/{evidence}/history', [EvidenceController::class, 'history'])->name('evidences.history');
    Route::post('evidences/{evidence}/assign', [EvidenceController::class, 'assign'])->name('evidences.assign');
    
    // Gestión de grupos
    Route::resource('groups', GroupController::class);
    Route::post('groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::delete('groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::post('groups/{group}/invite', [GroupController::class, 'invite'])->name('groups.invite');
    Route::patch('groups/{group}/members/{user}', [GroupController::class, 'updateMember'])->name('groups.members.update');
    Route::delete('groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    
    // Sistema de mensajería
    Route::resource('messages', MessageController::class)->except(['edit', 'update']);
    Route::post('messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::patch('messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');
    Route::delete('messages/bulk-delete', [MessageController::class, 'bulkDelete'])->name('messages.bulk-delete');
    
    // Analytics y reportes
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/files', [AnalyticsController::class, 'files'])->name('analytics.files');
    Route::get('analytics/evidences', [AnalyticsController::class, 'evidences'])->name('analytics.evidences');
    Route::get('analytics/users', [AnalyticsController::class, 'users'])->name('analytics.users');
    Route::get('analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');
    
    // Búsqueda global
    Route::get('search', [SearchController::class, 'index'])->name('search');
    Route::get('search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    
    // Perfil de usuario
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::patch('profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
    Route::patch('profile/privacy', [ProfileController::class, 'updatePrivacy'])->name('profile.privacy');
    
    // Notificaciones básicas (se redefinen más abajo con más funcionalidades)
    Route::get('notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    
    // Exportaciones
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('files', [FileController::class, 'export'])->name('files');
        Route::get('evidences', [EvidenceController::class, 'export'])->name('evidences');
        Route::get('groups', [GroupController::class, 'export'])->name('groups');
        Route::get('analytics', [AnalyticsController::class, 'exportData'])->name('analytics');
    });
    
    // API endpoints para funcionalidades AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('files/search', [FileController::class, 'search'])->name('files.search');
        Route::get('users/search', [ProfileController::class, 'searchUsers'])->name('users.search');
        Route::get('groups/search', [GroupController::class, 'search'])->name('groups.search');
        Route::post('files/{file}/toggle-favorite', [FileController::class, 'toggleFavorite'])->name('files.favorite');
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('notifications/unread-count', function () {
            return response()->json([
                'count' => auth()->user()->unreadNotifications->count()
            ]);
        })->name('notifications.unread-count');
    });
});

// Rutas de administración (solo para administradores)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard de administración
    Route::get('/', function () {
        return redirect()->route('admin.users.index');
    });
    
    // Gestión de usuarios
    Route::resource('users', AdminUserController::class);
    Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('users/{user}/activity', [AdminUserController::class, 'activity'])->name('users.activity');
    Route::post('users/bulk-action', [AdminUserController::class, 'bulkAction'])->name('users.bulk-action');
    
    // Configuraciones del sistema
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::patch('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/backup', [AdminSettingsController::class, 'backup'])->name('settings.backup');
    Route::post('settings/backup/create', [AdminSettingsController::class, 'createBackup'])->name('settings.backup.create');
    Route::get('settings/logs', [AdminSettingsController::class, 'logs'])->name('settings.logs');
    Route::delete('settings/logs/clear', [AdminSettingsController::class, 'clearLogs'])->name('settings.logs.clear');
    
    // Estadísticas y reportes administrativos
    Route::get('reports', [AdminSettingsController::class, 'reports'])->name('reports.index');
    Route::get('reports/users', [AdminSettingsController::class, 'userReports'])->name('reports.users');
    Route::get('reports/files', [AdminSettingsController::class, 'fileReports'])->name('reports.files');
    Route::get('reports/evidences', [AdminSettingsController::class, 'evidenceReports'])->name('reports.evidences');
    Route::get('reports/system', [AdminSettingsController::class, 'systemReports'])->name('reports.system');
    
    // Gestión de permisos y roles
    Route::get('permissions', [AdminSettingsController::class, 'permissions'])->name('permissions.index');
    Route::patch('permissions', [AdminSettingsController::class, 'updatePermissions'])->name('permissions.update');
    
    // Mantenimiento del sistema
    Route::get('maintenance', [AdminSettingsController::class, 'maintenance'])->name('maintenance.index');
    Route::post('maintenance/cache-clear', [AdminSettingsController::class, 'clearCache'])->name('maintenance.cache-clear');
    Route::post('maintenance/optimize', [AdminSettingsController::class, 'optimize'])->name('maintenance.optimize');
    Route::post('maintenance/migrate', [AdminSettingsController::class, 'migrate'])->name('maintenance.migrate');
});

// Rutas públicas (sin autenticación)
Route::prefix('public')->name('public.')->group(function () {
    Route::get('files/{file}', [FileController::class, 'publicView'])
        ->name('files.view')
        ->middleware('signed');
    
    Route::get('files/{file}/download', [FileController::class, 'publicDownload'])
        ->name('files.download')
        ->middleware('signed');
});

// Rutas de Proyectos - Nivel Empresarial
Route::middleware(['auth', 'verified'])->group(function () {

    // === PROYECTOS ===
    Route::prefix('projects')->name('projects.')->group(function () {
        // Rutas básicas CRUD con políticas
        Route::get('/', [ProjectController::class, 'index'])->name('index')
            ->middleware('throttle:60,1');
        Route::get('/create', [ProjectController::class, 'create'])->name('create')
            ->middleware('can:create,App\Models\Project');
        Route::post('/', [ProjectController::class, 'store'])->name('store')
            ->middleware(['can:create,App\Models\Project', 'throttle:10,1']);
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show')
            ->middleware('can:view,project')
            ->where('project', '[0-9]+');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit')
            ->middleware('can:update,project')
            ->where('project', '[0-9]+');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update')
            ->middleware(['can:update,project', 'throttle:20,1'])
            ->where('project', '[0-9]+');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy')
            ->middleware('can:delete,project')
            ->where('project', '[0-9]+');

        // Gestión de miembros del proyecto
        Route::post('/{project}/members', [ProjectController::class, 'addMember'])->name('members.add')
            ->middleware(['can:manageMembers,project', 'throttle:30,1'])
            ->where('project', '[0-9]+');
        Route::delete('/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('members.remove')
            ->middleware('can:manageMembers,project')
            ->where(['project' => '[0-9]+', 'user' => '[0-9]+']);
        Route::patch('/{project}/members/{user}', [ProjectController::class, 'updateMember'])->name('members.update')
            ->middleware('can:manageMembers,project')
            ->where(['project' => '[0-9]+', 'user' => '[0-9]+']);

        // Reportes y exportación
        Route::get('/export/data', [ProjectController::class, 'export'])->name('export')
            ->middleware(['can:export,App\Models\Project', 'throttle:5,1']);
        Route::get('/{project}/reports', [ProjectController::class, 'reports'])->name('reports')
            ->middleware('can:viewReports,project');
        Route::get('/{project}/analytics', [ProjectController::class, 'analytics'])->name('analytics')
            ->middleware('can:viewReports,project');

        // Archivos del proyecto
        Route::get('/{project}/files', [ProjectController::class, 'files'])->name('files')
            ->middleware('can:view,project');
        Route::post('/{project}/files', [ProjectController::class, 'uploadFile'])->name('files.upload')
            ->middleware(['can:update,project', 'throttle:20,1']);
    });

    // === MILESTONES ===
    Route::prefix('projects/{project}/milestones')->name('projects.milestones.')->group(function () {
        // Verificar acceso al proyecto en todas las rutas
        Route::middleware('can:view,project')->group(function () {
            Route::get('/', [MilestoneController::class, 'index'])->name('index');
            Route::get('/create', [MilestoneController::class, 'create'])->name('create')
                ->middleware('can:update,project');
            Route::post('/', [MilestoneController::class, 'store'])->name('store')
                ->middleware(['can:update,project', 'throttle:20,1']);
            Route::get('/{milestone}', [MilestoneController::class, 'show'])->name('show');
            Route::get('/{milestone}/edit', [MilestoneController::class, 'edit'])->name('edit')
                ->middleware('can:update,project');
            Route::put('/{milestone}', [MilestoneController::class, 'update'])->name('update')
                ->middleware(['can:update,project', 'throttle:30,1']);
            Route::delete('/{milestone}', [MilestoneController::class, 'destroy'])->name('destroy')
                ->middleware('can:update,project');

            // Acciones específicas de milestones
            Route::patch('/{milestone}/progress', [MilestoneController::class, 'updateProgress'])->name('progress')
                ->middleware(['can:update,project', 'throttle:60,1']);
            Route::patch('/{milestone}/complete', [MilestoneController::class, 'markCompleted'])->name('complete')
                ->middleware('can:update,project');
            Route::patch('/{milestone}/assign', [MilestoneController::class, 'assign'])->name('assign')
                ->middleware('can:update,project');

            // Reordenamiento de milestones
            Route::post('/reorder', [MilestoneController::class, 'reorder'])->name('reorder')
                ->middleware(['can:update,project', 'throttle:10,1']);
        });
    });

    // === TIME LOGS ===
    Route::prefix('time-logs')->name('time-logs.')->group(function () {
        // Rutas básicas con throttling
        Route::get('/', [TimeLogController::class, 'index'])->name('index')
            ->middleware('throttle:60,1');
        Route::get('/create', [TimeLogController::class, 'create'])->name('create')
            ->middleware('throttle:30,1');
        Route::post('/', [TimeLogController::class, 'store'])->name('store')
            ->middleware('throttle:30,1');
        Route::get('/{timeLog}', [TimeLogController::class, 'show'])->name('show')
            ->middleware('can:view,timeLog');
        Route::get('/{timeLog}/edit', [TimeLogController::class, 'edit'])->name('edit')
            ->middleware('can:update,timeLog');
        Route::put('/{timeLog}', [TimeLogController::class, 'update'])->name('update')
            ->middleware(['can:update,timeLog', 'throttle:30,1']);
        Route::delete('/{timeLog}', [TimeLogController::class, 'destroy'])->name('destroy')
            ->middleware('can:delete,timeLog');

        // Aprobación de time logs
        Route::patch('/{timeLog}/approve', [TimeLogController::class, 'approve'])->name('approve')
            ->middleware(['can:approve,timeLog', 'throttle:60,1']);
        Route::post('/bulk-approve', [TimeLogController::class, 'bulkApprove'])->name('bulk-approve')
            ->middleware(['throttle:10,1']);

        // Exportación y reportes
        Route::get('/export/data', [TimeLogController::class, 'export'])->name('export')
            ->middleware(['can:export,App\Models\TimeLog', 'throttle:5,1']);
        Route::get('/reports/summary', [TimeLogController::class, 'summaryReport'])->name('reports.summary')
            ->middleware('throttle:10,1');
        Route::get('/reports/detailed', [TimeLogController::class, 'detailedReport'])->name('reports.detailed')
            ->middleware('throttle:10,1');

        // APIs para funcionalidades avanzadas
        Route::get('/api/timer/status', [TimeLogController::class, 'timerStatus'])->name('api.timer.status')
            ->middleware('throttle:120,1');
        Route::post('/api/timer/start', [TimeLogController::class, 'startTimer'])->name('api.timer.start')
            ->middleware('throttle:30,1');
        Route::post('/api/timer/stop', [TimeLogController::class, 'stopTimer'])->name('api.timer.stop')
            ->middleware('throttle:30,1');
    });

    // === RUTAS DE ANALYTICS ===
    Route::prefix('analytics')->name('analytics.')->middleware(['throttle:30,1'])->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/team', [AnalyticsController::class, 'team'])->name('team')
            ->middleware('can:view-team-analytics');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('export')
            ->middleware('can:export-project-data');
    });

    // === RUTAS ADICIONALES DE DASHBOARD ===
    Route::prefix('dashboard')->name('dashboard.')->middleware('throttle:60,1')->group(function () {
        Route::get('/live-stats', [DashboardController::class, 'liveStats'])->name('live-stats');
        Route::get('/revenue-data', [DashboardController::class, 'revenueData'])->name('revenue-data');
        Route::get('/chart-data', [DashboardController::class, 'chartData'])->name('chart-data');
    });

    // === RUTAS DE BÚSQUEDA AVANZADA ===
    Route::prefix('search')->name('search.')->middleware('throttle:120,1')->group(function () {
        Route::get('/projects', [SearchController::class, 'projects'])->name('projects');
        Route::get('/time-logs', [SearchController::class, 'timeLogs'])->name('time-logs');
        Route::get('/milestones', [SearchController::class, 'milestones'])->name('milestones');
        Route::get('/global', [SearchController::class, 'global'])->name('global');
    });

    // === RUTAS DE NOTIFICACIONES ===
    Route::prefix('notifications')->name('notifications.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read')
            ->where('notification', '[0-9a-f-]+');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy')
            ->where('notification', '[0-9a-f-]+');
        Route::delete('/clear', [NotificationController::class, 'clear'])->name('clear');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::post('/bulk-action', [NotificationController::class, 'bulkAction'])->name('bulk-action');
    });

    // === RUTAS DE ACTIVIDAD Y AUDITORÍA ===
    Route::prefix('activity')->name('activity.')->middleware('throttle:30,1')->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::get('/project/{project}', [ActivityController::class, 'project'])->name('project')
            ->middleware('can:view,project')
            ->where('project', '[0-9]+');
        Route::get('/user/{user}', [ActivityController::class, 'user'])->name('user')
            ->middleware('can:viewProfile,user')
            ->where('user', '[0-9]+');
        Route::get('/chart-data', [ActivityController::class, 'chartData'])->name('chart-data');
    });
});

// === RUTAS DE CONECTIVIDAD ===
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'server' => 'Laravel ' . app()->version()
    ]);
})->middleware('throttle:60,1');

// Health check endpoint
Route::get('/health', function () {
    try {
        // Check database connection
        \DB::connection()->getPdo();

        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 503);
    }
})->middleware('throttle:30,1');

// Rutas de desarrollo (solo en entorno local)
if (app()->environment('local')) {
    Route::get('/dev/test-email', function () {
        return new App\Mail\TestMail();
    });
    
    Route::get('/dev/test-notification', function () {
        auth()->user()->notify(new App\Notifications\TestNotification());
        return 'Notification sent!';
    });
}

// Fallback route para manejar 404s
Route::fallback(function () {
    return view('errors.404');
});
