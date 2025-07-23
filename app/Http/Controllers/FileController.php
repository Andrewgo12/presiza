<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileController extends Controller
{
    /**
     * Display a listing of files.
     */
    public function index(Request $request)
    {
        $query = File::with(['uploader'])
            ->where('uploaded_by', Auth::id());
        
        // Aplicar filtros
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('original_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case 'year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
            }
        }
        
        // Ordenamiento
        $sort = $request->get('sort', 'created_at_desc');
        switch ($sort) {
            case 'created_at_asc':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy('original_name');
                break;
            case 'name_desc':
                $query->orderByDesc('original_name');
                break;
            case 'size_asc':
                $query->orderBy('size');
                break;
            case 'size_desc':
                $query->orderByDesc('size');
                break;
            default:
                $query->latest();
        }
        
        $files = $query->paginate(20)->withQueryString();
        
        // Formatear archivos para la vista
        $files->getCollection()->transform(function ($file) {
            $file->size_formatted = $this->formatFileSize($file->size);
            $file->thumbnail_url = $file->thumbnail_path 
                ? Storage::url($file->thumbnail_path)
                : null;
            return $file;
        });
        
        return view('files.index', compact('files'));
    }
    
    /**
     * Show the form for creating a new file.
     */
    public function create()
    {
        return view('files.create');
    }
    
    /**
     * Store newly uploaded files.
     */
    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:' . (config('filesystems.max_file_size', 2048) * 1024),
            'category' => 'required|in:document,image,video,audio,archive,other',
            'access_level' => 'required|in:public,internal,restricted,confidential',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|json',
            'is_public' => 'boolean',
            'expires_at' => 'nullable|date|after:now'
        ]);
        
        $uploadedFiles = [];
        
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $uploadedFile) {
                $file = $this->processFile($uploadedFile, $request);
                $uploadedFiles[] = $file;
            }
        }
        
        if (count($uploadedFiles) === 1) {
            return redirect()->route('files.show', $uploadedFiles[0])
                ->with('success', 'Archivo subido exitosamente.');
        }
        
        return redirect()->route('files.index')
            ->with('success', count($uploadedFiles) . ' archivos subidos exitosamente.');
    }
    
    /**
     * Display the specified file.
     */
    public function show(File $file)
    {
        $this->authorize('view', $file);
        
        // Incrementar contador de vistas
        $file->increment('view_count');
        
        // Formatear información del archivo
        $file->size_formatted = $this->formatFileSize($file->size);
        $file->can_preview = $this->canPreview($file);
        $file->preview_url = $file->can_preview ? Storage::url($file->path) : null;
        
        return view('files.show', compact('file'));
    }
    
    /**
     * Download the specified file.
     */
    public function download(File $file)
    {
        $this->authorize('download', $file);
        
        // Incrementar contador de descargas
        $file->increment('download_count');
        
        return Storage::download($file->path, $file->original_name);
    }
    
    /**
     * Show the form for editing the specified file.
     */
    public function edit(File $file)
    {
        $this->authorize('update', $file);
        
        return view('files.edit', compact('file'));
    }
    
    /**
     * Update the specified file.
     */
    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);
        
        $request->validate([
            'category' => 'required|in:document,image,video,audio,archive,other',
            'access_level' => 'required|in:public,internal,restricted,confidential',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|json',
            'is_public' => 'boolean',
            'expires_at' => 'nullable|date|after:now'
        ]);
        
        $file->update([
            'category' => $request->category,
            'access_level' => $request->access_level,
            'description' => $request->description,
            'tags' => $request->tags ? json_decode($request->tags) : null,
            'is_public' => $request->boolean('is_public'),
            'expires_at' => $request->expires_at
        ]);
        
        return redirect()->route('files.show', $file)
            ->with('success', 'Archivo actualizado exitosamente.');
    }
    
    /**
     * Remove the specified file.
     */
    public function destroy(File $file)
    {
        $this->authorize('delete', $file);
        
        // Eliminar archivo físico
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }
        
        // Eliminar thumbnail si existe
        if ($file->thumbnail_path && Storage::exists($file->thumbnail_path)) {
            Storage::delete($file->thumbnail_path);
        }
        
        $file->delete();
        
        return redirect()->route('files.index')
            ->with('success', 'Archivo eliminado exitosamente.');
    }
    
    /**
     * Process uploaded file.
     */
    private function processFile($uploadedFile, $request)
    {
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs('files', $filename, 'public');
        
        $file = File::create([
            'filename' => $filename,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'category' => $request->category,
            'description' => $request->description,
            'tags' => $request->tags ? json_decode($request->tags) : null,
            'uploaded_by' => Auth::id(),
            'is_public' => $request->boolean('is_public'),
            'access_level' => $request->access_level,
            'expires_at' => $request->expires_at
        ]);
        
        // Generar thumbnail para imágenes
        if (str_starts_with($file->mime_type, 'image/')) {
            $this->generateThumbnail($file);
        }
        
        return $file;
    }
    
    /**
     * Generate thumbnail for image files.
     */
    private function generateThumbnail($file)
    {
        try {
            $thumbnailPath = 'thumbnails/' . pathinfo($file->filename, PATHINFO_FILENAME) . '_thumb.jpg';
            
            $image = Image::make(Storage::disk('public')->path($file->path))
                ->fit(300, 300)
                ->encode('jpg', 80);
            
            Storage::disk('public')->put($thumbnailPath, $image);
            
            $file->update(['thumbnail_path' => $thumbnailPath]);
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to generate thumbnail: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if file can be previewed.
     */
    private function canPreview($file)
    {
        $previewableTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'text/plain', 'text/html', 'text/css', 'text/javascript',
            'application/json'
        ];
        
        return in_array($file->mime_type, $previewableTypes);
    }
    
    /**
     * View public file (no authentication required).
     */
    public function publicView(File $file)
    {
        // Check if file is public
        if (!$file->is_public) {
            abort(404);
        }

        // Check if file is expired
        if ($file->is_expired) {
            abort(410, 'File has expired');
        }

        // Increment view count
        $file->increment('view_count');

        // Return file response
        return Storage::disk($file->disk)->response($file->path, $file->original_name);
    }

    /**
     * Export files data.
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', File::class);

        $query = File::with(['uploader']);

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('access_level')) {
            $query->where('access_level', $request->access_level);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $files = $query->get();

        $csvData = [];
        $csvData[] = [
            'ID',
            'Nombre Original',
            'Tamaño',
            'Tipo MIME',
            'Categoría',
            'Nivel de Acceso',
            'Subido por',
            'Fecha de Subida',
            'Descargas',
            'Vistas'
        ];

        foreach ($files as $file) {
            $csvData[] = [
                $file->id,
                $file->original_name,
                $file->size_formatted,
                $file->mime_type,
                $file->category,
                $file->access_level,
                $file->uploader->full_name ?? 'Usuario eliminado',
                $file->created_at->format('Y-m-d H:i:s'),
                $file->download_count,
                $file->view_count
            ];
        }

        $filename = 'files_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
