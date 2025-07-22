<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes for mobile app or external integrations
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Projects API
    Route::get('projects/{project}/milestones', [App\Http\Controllers\MilestoneController::class, 'index'])
        ->where('project', '[0-9]+')
        ->middleware('can:view,project');

    // Milestones API for AJAX calls
    Route::get('milestones/{milestone}', [App\Http\Controllers\MilestoneController::class, 'show'])
        ->where('milestone', '[0-9]+')
        ->middleware('can:view,milestone');

    // Search API
    Route::get('search', [App\Http\Controllers\SearchController::class, 'index']);
    Route::get('search/suggestions', [App\Http\Controllers\SearchController::class, 'suggestions']);

    // Notifications API
    Route::get('notifications/count', [App\Http\Controllers\NotificationController::class, 'count']);
    Route::get('notifications/recent', [App\Http\Controllers\NotificationController::class, 'recent']);

    // Analytics API
    Route::get('analytics/chart-data', [App\Http\Controllers\AnalyticsController::class, 'chartData']);
    Route::get('activity/chart-data', [App\Http\Controllers\ActivityController::class, 'chartData']);

    // Dashboard API
    Route::get('dashboard/live-stats', [App\Http\Controllers\DashboardController::class, 'liveStats']);
    Route::get('dashboard/revenue-data', [App\Http\Controllers\DashboardController::class, 'revenueData']);

    // Time tracking API
    Route::get('time-logs/timer/status', [App\Http\Controllers\TimeLogController::class, 'timerStatus']);
    Route::post('time-logs/timer/start', [App\Http\Controllers\TimeLogController::class, 'startTimer']);
    Route::post('time-logs/timer/stop', [App\Http\Controllers\TimeLogController::class, 'stopTimer']);

    // Ping endpoint for connectivity checks
    Route::get('ping', function () {
        return response()->json(['status' => 'ok', 'timestamp' => now()]);
    });
});

// Enhanced API routes with ApiController
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/stats', [App\Http\Controllers\Api\ApiController::class, 'dashboardStats']);
});

Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/unread-count', [App\Http\Controllers\Api\ApiController::class, 'unreadNotificationsCount']);
    Route::get('/', [App\Http\Controllers\Api\ApiController::class, 'notifications']);
    Route::patch('/{id}/read', [App\Http\Controllers\Api\ApiController::class, 'markNotificationAsRead']);
    Route::patch('/read-all', [App\Http\Controllers\Api\ApiController::class, 'markAllNotificationsAsRead']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [App\Http\Controllers\Api\ApiController::class, 'profile']);
    Route::patch('/profile/preferences', [App\Http\Controllers\Api\ApiController::class, 'updatePreferences']);
    Route::get('/search', [App\Http\Controllers\Api\ApiController::class, 'search']);
});

// System status (public)
Route::get('/system/status', [App\Http\Controllers\Api\ApiController::class, 'systemStatus']);
