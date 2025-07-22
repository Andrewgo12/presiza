<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): View
    {
        try {
            $user = Auth::user();
            
            $query = $user->notifications();
            
            // Filter by type if specified
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            
            // Filter by read status
            if ($request->filled('read')) {
                if ($request->read === 'unread') {
                    $query->whereNull('read_at');
                } elseif ($request->read === 'read') {
                    $query->whereNotNull('read_at');
                }
            }
            
            // Search in notification data
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('data->title', 'like', "%{$search}%")
                      ->orWhere('data->message', 'like', "%{$search}%");
                });
            }
            
            $notifications = $query->orderBy('created_at', 'desc')
                                 ->paginate(20)
                                 ->withQueryString();
            
            $stats = [
                'total' => $user->notifications()->count(),
                'unread' => $user->unreadNotifications()->count(),
                'today' => $user->notifications()->whereDate('created_at', today())->count(),
                'this_week' => $user->notifications()->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
            ];
            
            return view('notifications.index', compact('notifications', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error loading notifications: ' . $e->getMessage());
            return view('notifications.index', [
                'notifications' => collect(),
                'stats' => ['total' => 0, 'unread' => 0, 'today' => 0, 'this_week' => 0]
            ])->with('error', 'Error al cargar las notificaciones.');
        }
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($id);
            
            if (!$notification->read_at) {
                $notification->markAsRead();
                
                Log::info('Notification marked as read', [
                    'user_id' => $user->id,
                    'notification_id' => $id
                ]);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notificación marcada como leída'
                ]);
            }
            
            return back()->with('success', 'Notificación marcada como leída.');
            
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al marcar la notificación como leída'
                ], 500);
            }
            
            return back()->with('error', 'Error al marcar la notificación como leída.');
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $user = Auth::user();
            $unreadCount = $user->unreadNotifications()->count();
            
            if ($unreadCount > 0) {
                $user->unreadNotifications()->update(['read_at' => now()]);
                
                Log::info('All notifications marked as read', [
                    'user_id' => $user->id,
                    'count' => $unreadCount
                ]);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Se marcaron {$unreadCount} notificaciones como leídas",
                    'count' => $unreadCount
                ]);
            }
            
            return back()->with('success', "Se marcaron {$unreadCount} notificaciones como leídas.");
            
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al marcar todas las notificaciones como leídas'
                ], 500);
            }
            
            return back()->with('error', 'Error al marcar todas las notificaciones como leídas.');
        }
    }

    /**
     * Remove a specific notification.
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($id);
            
            $notification->delete();
            
            Log::info('Notification deleted', [
                'user_id' => $user->id,
                'notification_id' => $id
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notificación eliminada'
                ]);
            }
            
            return back()->with('success', 'Notificación eliminada.');
            
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la notificación'
                ], 500);
            }
            
            return back()->with('error', 'Error al eliminar la notificación.');
        }
    }

    /**
     * Clear all notifications for the user.
     */
    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $user = Auth::user();
            $count = $user->notifications()->count();
            
            $user->notifications()->delete();
            
            Log::info('All notifications cleared', [
                'user_id' => $user->id,
                'count' => $count
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Se eliminaron {$count} notificaciones",
                    'count' => $count
                ]);
            }
            
            return back()->with('success', "Se eliminaron {$count} notificaciones.");
            
        } catch (\Exception $e) {
            Log::error('Error clearing notifications: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar las notificaciones'
                ], 500);
            }
            
            return back()->with('error', 'Error al eliminar las notificaciones.');
        }
    }

    /**
     * Get unread notifications count (API endpoint).
     */
    public function count(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = $user->unreadNotifications()->count();
            
            return response()->json([
                'count' => $count,
                'has_unread' => $count > 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting notification count: ' . $e->getMessage());
            
            return response()->json([
                'count' => 0,
                'has_unread' => false,
                'error' => 'Error al obtener el conteo de notificaciones'
            ], 500);
        }
    }

    /**
     * Get recent notifications for dropdown (API endpoint).
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);
            
            $notifications = $user->notifications()
                                 ->orderBy('created_at', 'desc')
                                 ->limit($limit)
                                 ->get()
                                 ->map(function ($notification) {
                                     return [
                                         'id' => $notification->id,
                                         'type' => $notification->type,
                                         'data' => $notification->data,
                                         'read_at' => $notification->read_at,
                                         'created_at' => $notification->created_at,
                                         'time_ago' => $notification->created_at->diffForHumans(),
                                         'is_unread' => is_null($notification->read_at)
                                     ];
                                 });
            
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $user->unreadNotifications()->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting recent notifications: ' . $e->getMessage());
            
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
                'error' => 'Error al obtener las notificaciones recientes'
            ], 500);
        }
    }

    /**
     * Bulk actions on notifications.
     */
    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:mark_read,delete',
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $notificationIds = $request->notification_ids;
            $action = $request->action;
            
            $query = $user->notifications()->whereIn('id', $notificationIds);
            
            switch ($action) {
                case 'mark_read':
                    $count = $query->whereNull('read_at')->count();
                    $query->update(['read_at' => now()]);
                    $message = "Se marcaron {$count} notificaciones como leídas";
                    break;
                    
                case 'delete':
                    $count = $query->count();
                    $query->delete();
                    $message = "Se eliminaron {$count} notificaciones";
                    break;
                    
                default:
                    throw new \InvalidArgumentException('Acción no válida');
            }
            
            Log::info('Bulk notification action performed', [
                'user_id' => $user->id,
                'action' => $action,
                'count' => $count
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count' => $count
                ]);
            }
            
            return back()->with('success', $message);
            
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors());
            
        } catch (\Exception $e) {
            Log::error('Error performing bulk notification action: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al realizar la acción masiva'
                ], 500);
            }
            
            return back()->with('error', 'Error al realizar la acción masiva.');
        }
    }
}
