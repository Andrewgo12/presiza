<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Evidence;
use App\Models\Group;
use App\Models\Message;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener estadísticas principales
        $stats = $this->getStats($user);
        
        // Obtener actividad reciente
        $recent_activities = $this->getRecentActivities($user);
        
        // Obtener archivos recientes
        $recent_files = $this->getRecentFiles($user);
        
        // Obtener datos para gráficos
        $chart_data = $this->getChartData($user);
        
        return view('dashboard.index', compact(
            'stats',
            'recent_activities', 
            'recent_files',
            'chart_data'
        ));
    }
    
    /**
     * Get dashboard statistics.
     */
    private function getStats($user)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Total de archivos
        $totalFiles = File::where('uploaded_by', $user->id)->count();
        $filesThisMonth = File::where('uploaded_by', $user->id)
            ->where('created_at', '>=', $currentMonth)
            ->count();
        $filesPreviousMonth = File::where('uploaded_by', $user->id)
            ->whereBetween('created_at', [$previousMonth, $currentMonth])
            ->count();
        
        $filesChange = $filesPreviousMonth > 0 
            ? round((($filesThisMonth - $filesPreviousMonth) / $filesPreviousMonth) * 100, 1)
            : 0;
        
        // Evidencias pendientes
        $pendingEvidences = $user->role === 'admin' 
            ? Evidence::where('status', 'pending')->count()
            : Evidence::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count();
        
        // Grupos activos
        $activeGroups = $user->groups()
            ->where('is_active', true)
            ->count();
        
        // Mensajes no leídos
        $unreadMessages = Message::whereHas('recipients', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereNull('read_at');
        })->count();

        // Proyectos del usuario
        $myProjects = $user->isAdmin()
            ? Project::active()->count()
            : Project::where('project_manager_id', $user->id)
                ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
                ->active()
                ->count();

        // Milestones asignados
        $myMilestones = ProjectMilestone::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        // Horas registradas esta semana
        $hoursThisWeek = TimeLog::byUser($user->id)
            ->thisWeek()
            ->sum('hours');

        return [
            'total_files' => $totalFiles,
            'files_change' => $filesChange,
            'pending_evidences' => $pendingEvidences,
            'active_groups' => $activeGroups,
            'unread_messages' => $unreadMessages,
            'my_projects' => $myProjects,
            'my_milestones' => $myMilestones,
            'hours_this_week' => $hoursThisWeek,
        ];
    }
    
    /**
     * Get recent activities.
     */
    private function getRecentActivities($user)
    {
        $activities = collect();
        
        // Archivos recientes
        $recentFiles = File::where('uploaded_by', $user->id)
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($recentFiles as $file) {
            $activities->push([
                'description' => "Subiste el archivo \"{$file->original_name}\"",
                'time' => $file->created_at->diffForHumans(),
                'datetime' => $file->created_at->toISOString(),
                'icon' => '<svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 18H3.75c-.621 0-1.125-.504-1.125-1.125V1.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v4.5c0 .621.504 1.125 1.125 1.125h4.125c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125H16.5a1.125 1.125 0 01-1.125-1.125v-1.5a1.125 1.125 0 00-1.125-1.125H12" /></svg>',
                'color' => 'bg-blue-500'
            ]);
        }
        
        // Evidencias recientes
        $recentEvidences = Evidence::where('submitted_by', $user->id)
            ->latest()
            ->take(3)
            ->get();
            
        foreach ($recentEvidences as $evidence) {
            $activities->push([
                'description' => "Creaste la evidencia \"{$evidence->title}\"",
                'time' => $evidence->created_at->diffForHumans(),
                'datetime' => $evidence->created_at->toISOString(),
                'icon' => '<svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>',
                'color' => 'bg-green-500'
            ]);
        }
        
        return $activities->sortByDesc('datetime')->take(8)->values();
    }
    
    /**
     * Get recent files.
     */
    private function getRecentFiles($user)
    {
        return File::where('uploaded_by', $user->id)
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'size_formatted' => $this->formatFileSize($file->size),
                    'mime_type' => $file->mime_type,
                    'url' => route('files.show', $file),
                    'thumbnail_url' => $file->thumbnail_path 
                        ? asset('storage/' . $file->thumbnail_path)
                        : null
                ];
            });
    }
    
    /**
     * Get chart data for the last 7 days.
     */
    private function getChartData($user)
    {
        $days = collect();
        $data = collect();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days->push($date->format('D'));
            
            $count = File::where('uploaded_by', $user->id)
                ->whereDate('created_at', $date)
                ->count();
                
            $data->push($count);
        }
        
        return [
            'labels' => $days->toArray(),
            'data' => $data->toArray()
        ];
    }
    
    /**
     * Format file size in human readable format.
     */
    private function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
