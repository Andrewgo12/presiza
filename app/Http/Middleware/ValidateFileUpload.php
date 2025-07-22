<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateFileUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasFile('file') || $request->hasFile('files')) {
            $this->validateUploadedFiles($request);
        }

        return $next($request);
    }

    /**
     * Validate uploaded files for security.
     */
    protected function validateUploadedFiles(Request $request): void
    {
        $files = [];
        
        // Collect all uploaded files
        if ($request->hasFile('file')) {
            $files[] = $request->file('file');
        }
        
        if ($request->hasFile('files')) {
            $uploadedFiles = $request->file('files');
            if (is_array($uploadedFiles)) {
                $files = array_merge($files, $uploadedFiles);
            } else {
                $files[] = $uploadedFiles;
            }
        }

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $this->validateFileType($file, $request);
            $this->validateFileSize($file, $request);
            $this->validateFileContent($file, $request);
        }
    }

    /**
     * Validate file type and extension.
     */
    protected function validateFileType($file, Request $request): void
    {
        $allowedExtensions = [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'rtf', 'csv',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp',
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm',
            'mp3', 'wav', 'ogg', 'aac',
            'zip', 'rar', '7z', 'tar', 'gz',
        ];

        $dangerousExtensions = [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js',
            'jar', 'php', 'asp', 'aspx', 'jsp', 'py', 'rb', 'pl',
            'sh', 'bash', 'ps1', 'psm1',
        ];

        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Check for dangerous extensions
        if (in_array($extension, $dangerousExtensions)) {
            Log::warning('Dangerous file upload attempt', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $file->getSize(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'timestamp' => now()->toISOString(),
            ]);

            abort(422, 'File type not allowed for security reasons.');
        }

        // Check if extension is in allowed list
        if (!in_array($extension, $allowedExtensions)) {
            Log::info('Disallowed file extension upload attempt', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'mime_type' => $mimeType,
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'timestamp' => now()->toISOString(),
            ]);

            abort(422, 'File type not allowed.');
        }

        // Validate MIME type matches extension
        $this->validateMimeType($file, $extension, $request);
    }

    /**
     * Validate MIME type matches file extension.
     */
    protected function validateMimeType($file, string $extension, Request $request): void
    {
        $mimeTypeMap = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'txt' => ['text/plain'],
            'csv' => ['text/csv', 'application/csv'],
            'zip' => ['application/zip'],
        ];

        $mimeType = $file->getMimeType();
        $allowedMimes = $mimeTypeMap[$extension] ?? [];

        if (!empty($allowedMimes) && !in_array($mimeType, $allowedMimes)) {
            Log::warning('MIME type mismatch in file upload', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'expected_mime' => $allowedMimes,
                'actual_mime' => $mimeType,
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'timestamp' => now()->toISOString(),
            ]);

            abort(422, 'File type validation failed.');
        }
    }

    /**
     * Validate file size.
     */
    protected function validateFileSize($file, Request $request): void
    {
        $maxSize = 50 * 1024 * 1024; // 50MB
        $fileSize = $file->getSize();

        if ($fileSize > $maxSize) {
            Log::info('Large file upload attempt', [
                'filename' => $file->getClientOriginalName(),
                'size' => $fileSize,
                'max_allowed' => $maxSize,
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'timestamp' => now()->toISOString(),
            ]);

            abort(422, 'File size exceeds maximum allowed size.');
        }
    }

    /**
     * Validate file content for malicious patterns.
     */
    protected function validateFileContent($file, Request $request): void
    {
        // Only scan text-based files
        $textExtensions = ['txt', 'csv', 'html', 'htm', 'xml', 'json'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $textExtensions)) {
            return;
        }

        $content = file_get_contents($file->getRealPath());
        
        // Check for malicious patterns
        $maliciousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('Malicious content detected in file upload', [
                    'filename' => $file->getClientOriginalName(),
                    'pattern' => $pattern,
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id,
                    'timestamp' => now()->toISOString(),
                ]);

                abort(422, 'File contains potentially malicious content.');
            }
        }
    }
}
