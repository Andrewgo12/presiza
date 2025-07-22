<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Display a listing of messages.
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender', 'group'])
            ->receivedBy(Auth::id());
        
        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('unread')) {
            $query->unreadBy(Auth::id());
        }
        
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        $messages = $query->latest()->paginate(20)->withQueryString();
        
        // Estadísticas
        $stats = [
            'total' => Message::receivedBy(Auth::id())->count(),
            'unread' => Message::unreadBy(Auth::id())->count(),
            'direct' => Message::receivedBy(Auth::id())->direct()->count(),
            'group' => Message::receivedBy(Auth::id())->group()->count(),
        ];
        
        return view('messages.index', compact('messages', 'stats'));
    }
    
    /**
     * Show the form for creating a new message.
     */
    public function create(Request $request)
    {
        $users = User::active()
            ->where('id', '!=', Auth::id())
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
        
        $groups = Auth::user()->groups()->active()->get(['id', 'name']);
        
        $replyTo = null;
        if ($request->filled('reply_to')) {
            $replyTo = Message::findOrFail($request->reply_to);
        }
        
        return view('messages.create', compact('users', 'groups', 'replyTo'));
    }
    
    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:direct,group',
            'priority' => 'required|in:low,normal,high,urgent',
            'recipients' => 'required_if:type,direct|array',
            'recipients.*' => 'exists:users,id',
            'group_id' => 'required_if:type,group|exists:groups,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            $message = Message::create([
                'subject' => $request->subject,
                'content' => $request->content,
                'sender_id' => Auth::id(),
                'type' => $request->type,
                'group_id' => $request->type === 'group' ? $request->group_id : null,
                'priority' => $request->priority,
            ]);
            
            // Agregar destinatarios
            if ($request->type === 'direct') {
                $message->addRecipients($request->recipients);
            } else {
                // Para mensajes de grupo, agregar todos los miembros como destinatarios
                $group = Group::findOrFail($request->group_id);
                $memberIds = $group->members()->pluck('users.id')->toArray();
                $message->addRecipients($memberIds);
            }
            
            DB::commit();
            
            return redirect()->route('messages.show', $message)
                ->with('success', 'Mensaje enviado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al enviar el mensaje: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified message.
     */
    public function show(Message $message)
    {
        // Verificar que el usuario puede ver el mensaje
        if (!$message->recipients()->where('user_id', Auth::id())->exists() && 
            $message->sender_id !== Auth::id() && 
            !Auth::user()->isAdmin()) {
            abort(403, 'No tienes acceso a este mensaje.');
        }
        
        $message->load(['sender', 'group', 'recipients']);
        
        // Marcar como leído si es destinatario
        if ($message->recipients()->where('user_id', Auth::id())->exists()) {
            $message->markAsReadBy(Auth::user());
        }
        
        return view('messages.show', compact('message'));
    }
    
    /**
     * Reply to a message.
     */
    public function reply(Request $request, Message $message)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $reply = Message::create([
                'subject' => 'Re: ' . $message->subject,
                'content' => $request->content,
                'sender_id' => Auth::id(),
                'type' => $message->type,
                'group_id' => $message->group_id,
                'priority' => $message->priority,
            ]);
            
            // Agregar destinatarios (remitente original + otros destinatarios)
            $recipientIds = $message->recipients()->pluck('user_id')->toArray();
            if (!in_array($message->sender_id, $recipientIds)) {
                $recipientIds[] = $message->sender_id;
            }
            
            // Remover al usuario actual de los destinatarios
            $recipientIds = array_filter($recipientIds, fn($id) => $id !== Auth::id());
            
            $reply->addRecipients($recipientIds);
            
            DB::commit();
            
            return redirect()->route('messages.show', $reply)
                ->with('success', 'Respuesta enviada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al enviar la respuesta: ' . $e->getMessage());
        }
    }
    
    /**
     * Forward a message.
     */
    public function forward(Request $request, Message $message)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'exists:users,id',
            'content' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $forwardedMessage = Message::create([
                'subject' => 'Fwd: ' . $message->subject,
                'content' => ($request->content ? $request->content . "\n\n" : '') . 
                           "--- Mensaje reenviado ---\n" . 
                           "De: " . $message->sender->full_name . "\n" .
                           "Fecha: " . $message->created_at->format('d/m/Y H:i') . "\n" .
                           "Asunto: " . $message->subject . "\n\n" .
                           $message->content,
                'sender_id' => Auth::id(),
                'type' => 'direct',
                'priority' => $message->priority,
            ]);
            
            $forwardedMessage->addRecipients($request->recipients);
            
            DB::commit();
            
            return redirect()->route('messages.show', $forwardedMessage)
                ->with('success', 'Mensaje reenviado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al reenviar el mensaje: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark message as read/unread.
     */
    public function toggleRead(Message $message)
    {
        if (!$message->recipients()->where('user_id', Auth::id())->exists()) {
            abort(403, 'No tienes acceso a este mensaje.');
        }
        
        if ($message->isReadBy(Auth::user())) {
            $message->markAsUnreadBy(Auth::user());
            $status = 'no leído';
        } else {
            $message->markAsReadBy(Auth::user());
            $status = 'leído';
        }
        
        return back()->with('success', "Mensaje marcado como {$status}.");
    }
    
    /**
     * Delete message.
     */
    public function destroy(Message $message)
    {
        // Solo el remitente o admin pueden eliminar mensajes
        if ($message->sender_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'No tienes permisos para eliminar este mensaje.');
        }
        
        $message->delete();
        
        return redirect()->route('messages.index')
            ->with('success', 'Mensaje eliminado exitosamente.');
    }
    
    /**
     * Get unread messages count.
     */
    public function unreadCount()
    {
        $count = Message::unreadBy(Auth::id())->count();
        
        return response()->json(['count' => $count]);
    }
    
    /**
     * Mark all messages as read.
     */
    public function markAllRead()
    {
        $messages = Message::unreadBy(Auth::id())->get();
        
        foreach ($messages as $message) {
            $message->markAsReadBy(Auth::user());
        }
        
        return back()->with('success', 'Todos los mensajes marcados como leídos.');
    }
    
    /**
     * Search messages.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }
        
        $messages = Message::search($query)
            ->receivedBy(Auth::id())
            ->with('sender')
            ->limit(10)
            ->get();
        
        return response()->json([
            'results' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'excerpt' => $message->excerpt,
                    'sender' => $message->sender->full_name,
                    'created_at' => $message->created_at->diffForHumans(),
                    'url' => route('messages.show', $message),
                    'is_read' => $message->isReadBy(Auth::user()),
                ];
            }),
        ]);
    }
    
    /**
     * Export messages.
     */
    public function export(Request $request)
    {
        $messages = Message::receivedBy(Auth::id())
            ->with(['sender', 'group'])
            ->get();
        
        $filename = 'messages_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($messages) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Asunto', 'Remitente', 'Tipo', 'Prioridad', 
                'Grupo', 'Leído', 'Fecha'
            ]);
            
            // Data
            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->id,
                    $message->subject,
                    $message->sender->full_name,
                    $message->type_display_name,
                    $message->priority_display_name,
                    $message->group->name ?? 'N/A',
                    $message->isReadBy(Auth::user()) ? 'Sí' : 'No',
                    $message->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
