<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard.
     */
    public function index(Request $request): View
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', 'month'); // week, month, quarter, year
            
            // Get date range based on period
            $dateRange = $this->getDateRange($period);
            
            // General statistics
            $stats = [
                'total_projects' => $this->getTotalProjects($user),
                'active_projects' => $this->getActiveProjects($user),
                'total_hours' => $this->getTotalHours($user, $dateRange),
                'billable_hours' => $this->getBillableHours($user, $dateRange),
                'total_revenue' => $this->getTotalRevenue($user, $dateRange),
                'completed_milestones' => $this->getCompletedMilestones($user, $dateRange),
            ];
            
            // Chart data
            $chartData = [
                'hours_by_day' => $this->getHoursByDay($user, $dateRange),
                'projects_progress' => $this->getProjectsProgress($user),
                'revenue_by_project' => $this->getRevenueByProject($user, $dateRange),
                'team_productivity' => $this->getTeamProductivity($user, $dateRange),
                'milestone_completion' => $this->getMilestoneCompletion($user, $dateRange),
            ];
            
            // Top performers
            $topPerformers = [
                'most_productive_user' => $this->getMostProductiveUser($dateRange),
                'most_profitable_project' => $this->getMostProfitableProject($user, $dateRange),
                'fastest_milestone_completion' => $this->getFastestMilestoneCompletion($user, $dateRange),
            ];
            
            return view('analytics.index', compact(
                'stats',
                'chartData',
                'topPerformers',
                'period'
            ));
            
        } catch (\Exception $e) {
            Log::error('Analytics error: ' . $e->getMessage());
            return view('analytics.index', [
                'stats' => [],
                'chartData' => [],
                'topPerformers' => [],
                'period' => 'month',
                'error' => 'Error al cargar los análisis'
            ]);
        }
    }

    /**
     * Get team analytics.
     */
    public function team(Request $request): View|JsonResponse
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($period);
            
            // Team performance data
            $teamData = [
                'members_productivity' => $this->getMembersProductivity($user, $dateRange),
                'project_distribution' => $this->getProjectDistribution($user),
                'collaboration_metrics' => $this->getCollaborationMetrics($user, $dateRange),
                'workload_balance' => $this->getWorkloadBalance($user, $dateRange),
            ];
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $teamData,
                    'period' => $period
                ]);
            }
            
            return view('analytics.team', compact('teamData', 'period'));
            
        } catch (\Exception $e) {
            Log::error('Team analytics error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al cargar análisis del equipo'
                ], 500);
            }
            
            return view('analytics.team', [
                'teamData' => [],
                'period' => 'month',
                'error' => 'Error al cargar análisis del equipo'
            ]);
        }
    }

    /**
     * Export analytics data.
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $format = $request->get('format', 'json'); // json, csv, excel
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($period);
            
            $data = [
                'period' => $period,
                'date_range' => $dateRange,
                'stats' => [
                    'total_projects' => $this->getTotalProjects($user),
                    'active_projects' => $this->getActiveProjects($user),
                    'total_hours' => $this->getTotalHours($user, $dateRange),
                    'billable_hours' => $this->getBillableHours($user, $dateRange),
                    'total_revenue' => $this->getTotalRevenue($user, $dateRange),
                ],
                'projects' => $this->getProjectsAnalytics($user, $dateRange),
                'time_logs' => $this->getTimeLogsAnalytics($user, $dateRange),
                'milestones' => $this->getMilestonesAnalytics($user, $dateRange),
                'generated_at' => now()->toISOString(),
            ];
            
            // Here you would implement actual export logic based on format
            // For now, returning JSON
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'format' => $format
            ]);
            
        } catch (\Exception $e) {
            Log::error('Analytics export error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al exportar análisis'
            ], 500);
        }
    }

    /**
     * Get date range based on period.
     */
    private function getDateRange(string $period): array
    {
        switch ($period) {
            case 'week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
            case 'quarter':
                return [
                    'start' => now()->startOfQuarter(),
                    'end' => now()->endOfQuarter()
                ];
            case 'year':
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear()
                ];
            default:
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
        }
    }

    /**
     * Get total projects for user.
     */
    private function getTotalProjects(User $user): int
    {
        return $user->projects()->count();
    }

    /**
     * Get active projects for user.
     */
    private function getActiveProjects(User $user): int
    {
        return $user->projects()
                   ->whereIn('status', ['planning', 'in_progress'])
                   ->count();
    }

    /**
     * Get total hours for user in date range.
     */
    private function getTotalHours(User $user, array $dateRange): float
    {
        return TimeLog::where('user_id', $user->id)
                     ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                     ->sum('hours') ?? 0;
    }

    /**
     * Get billable hours for user in date range.
     */
    private function getBillableHours(User $user, array $dateRange): float
    {
        return TimeLog::where('user_id', $user->id)
                     ->where('is_billable', true)
                     ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                     ->sum('hours') ?? 0;
    }

    /**
     * Get total revenue for user in date range.
     */
    private function getTotalRevenue(User $user, array $dateRange): float
    {
        return TimeLog::where('user_id', $user->id)
                     ->where('is_billable', true)
                     ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                     ->sum(DB::raw('hours * hourly_rate')) ?? 0;
    }

    /**
     * Get completed milestones for user in date range.
     */
    private function getCompletedMilestones(User $user, array $dateRange): int
    {
        return Milestone::whereHas('project.users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    })
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
                    ->count();
    }

    /**
     * Get hours by day for chart.
     */
    private function getHoursByDay(User $user, array $dateRange): array
    {
        $data = TimeLog::where('user_id', $user->id)
                      ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                      ->selectRaw('DATE(date) as date, SUM(hours) as total_hours')
                      ->groupBy('date')
                      ->orderBy('date')
                      ->get();
        
        return [
            'labels' => $data->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('d/m');
            })->toArray(),
            'data' => $data->pluck('total_hours')->toArray()
        ];
    }

    /**
     * Get projects progress data.
     */
    private function getProjectsProgress(User $user): array
    {
        $projects = $user->projects()
                        ->with(['milestones'])
                        ->get()
                        ->map(function ($project) {
                            return [
                                'name' => $project->name,
                                'progress' => $project->progress_percentage,
                                'status' => $project->status
                            ];
                        });
        
        return [
            'labels' => $projects->pluck('name')->toArray(),
            'data' => $projects->pluck('progress')->toArray()
        ];
    }

    /**
     * Get revenue by project.
     */
    private function getRevenueByProject(User $user, array $dateRange): array
    {
        $data = TimeLog::join('projects', 'time_logs.project_id', '=', 'projects.id')
                      ->join('project_user', function ($join) use ($user) {
                          $join->on('projects.id', '=', 'project_user.project_id')
                               ->where('project_user.user_id', $user->id);
                      })
                      ->where('time_logs.is_billable', true)
                      ->whereBetween('time_logs.date', [$dateRange['start'], $dateRange['end']])
                      ->selectRaw('projects.name, SUM(time_logs.hours * time_logs.hourly_rate) as revenue')
                      ->groupBy('projects.id', 'projects.name')
                      ->orderBy('revenue', 'desc')
                      ->get();
        
        return [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('revenue')->toArray()
        ];
    }

    /**
     * Get team productivity data.
     */
    private function getTeamProductivity(User $user, array $dateRange): array
    {
        // Get all users from user's projects
        $teamMembers = User::whereHas('projects', function ($q) use ($user) {
                           $q->whereHas('users', function ($subQ) use ($user) {
                               $subQ->where('users.id', $user->id);
                           });
                       })
                       ->with(['timeLogs' => function ($q) use ($dateRange) {
                           $q->whereBetween('date', [$dateRange['start'], $dateRange['end']]);
                       }])
                       ->get()
                       ->map(function ($member) {
                           return [
                               'name' => $member->full_name,
                               'hours' => $member->timeLogs->sum('hours')
                           ];
                       })
                       ->sortByDesc('hours')
                       ->take(10);
        
        return [
            'labels' => $teamMembers->pluck('name')->toArray(),
            'data' => $teamMembers->pluck('hours')->toArray()
        ];
    }

    /**
     * Get milestone completion data.
     */
    private function getMilestoneCompletion(User $user, array $dateRange): array
    {
        $data = Milestone::whereHas('project.users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    })
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get();
        
        return [
            'labels' => $data->pluck('status')->map(function ($status) {
                return ucfirst(str_replace('_', ' ', $status));
            })->toArray(),
            'data' => $data->pluck('count')->toArray()
        ];
    }

    /**
     * Get most productive user.
     */
    private function getMostProductiveUser(array $dateRange): ?User
    {
        $userId = TimeLog::whereBetween('date', [$dateRange['start'], $dateRange['end']])
                        ->selectRaw('user_id, SUM(hours) as total_hours')
                        ->groupBy('user_id')
                        ->orderBy('total_hours', 'desc')
                        ->value('user_id');
        
        return $userId ? User::find($userId) : null;
    }

    /**
     * Get most profitable project.
     */
    private function getMostProfitableProject(User $user, array $dateRange): ?Project
    {
        $projectId = TimeLog::join('project_user', function ($join) use ($user) {
                           $join->on('time_logs.project_id', '=', 'project_user.project_id')
                                ->where('project_user.user_id', $user->id);
                       })
                       ->where('time_logs.is_billable', true)
                       ->whereBetween('time_logs.date', [$dateRange['start'], $dateRange['end']])
                       ->selectRaw('time_logs.project_id, SUM(time_logs.hours * time_logs.hourly_rate) as revenue')
                       ->groupBy('time_logs.project_id')
                       ->orderBy('revenue', 'desc')
                       ->value('project_id');
        
        return $projectId ? Project::find($projectId) : null;
    }

    /**
     * Get fastest milestone completion.
     */
    private function getFastestMilestoneCompletion(User $user, array $dateRange): ?Milestone
    {
        return Milestone::whereHas('project.users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    })
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
                    ->whereNotNull('started_at')
                    ->whereNotNull('completed_at')
                    ->orderByRaw('DATEDIFF(completed_at, started_at) ASC')
                    ->first();
    }

    // Additional helper methods would go here...
    private function getMembersProductivity(User $user, array $dateRange): array { return []; }
    private function getProjectDistribution(User $user): array { return []; }
    private function getCollaborationMetrics(User $user, array $dateRange): array { return []; }
    private function getWorkloadBalance(User $user, array $dateRange): array { return []; }
    private function getProjectsAnalytics(User $user, array $dateRange): array { return []; }
    private function getTimeLogsAnalytics(User $user, array $dateRange): array { return []; }
    private function getMilestonesAnalytics(User $user, array $dateRange): array { return []; }
}
