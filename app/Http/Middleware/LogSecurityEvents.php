<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogSecurityEvents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log suspicious activities
        $this->logSuspiciousActivity($request);

        $response = $next($request);

        // Log security-related responses
        $this->logSecurityResponse($request, $response);

        return $response;
    }

    /**
     * Log suspicious activities.
     */
    protected function logSuspiciousActivity(Request $request): void
    {
        $suspiciousPatterns = [
            'sql_injection' => [
                '/union\s+select/i',
                '/drop\s+table/i',
                '/insert\s+into/i',
                '/delete\s+from/i',
                '/update\s+set/i',
            ],
            'xss_attempt' => [
                '/<script/i',
                '/javascript:/i',
                '/on\w+\s*=/i',
                '/<iframe/i',
            ],
            'path_traversal' => [
                '/\.\.\//',
                '/\.\.\\\/',
                '/etc\/passwd/i',
                '/windows\/system32/i',
            ],
            'command_injection' => [
                '/;\s*(cat|ls|pwd|whoami|id)/i',
                '/\|\s*(cat|ls|pwd|whoami|id)/i',
                '/&&\s*(cat|ls|pwd|whoami|id)/i',
            ],
        ];

        $requestData = $request->all();
        $requestString = json_encode($requestData) . ' ' . $request->getRequestUri();

        foreach ($suspiciousPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $requestString)) {
                    Log::warning('Suspicious activity detected', [
                        'type' => $type,
                        'pattern' => $pattern,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'user_id' => $request->user()?->id,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'data' => $requestData,
                        'timestamp' => now()->toISOString(),
                    ]);
                    break 2; // Exit both loops
                }
            }
        }

        // Log failed authentication attempts
        if ($request->routeIs('login') && $request->isMethod('POST')) {
            Log::info('Login attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'email' => $request->input('email'),
                'timestamp' => now()->toISOString(),
            ]);
        }

        // Log admin access attempts
        if ($request->is('admin/*')) {
            Log::info('Admin area access', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Log security-related responses.
     */
    protected function logSecurityResponse(Request $request, Response $response): void
    {
        // Log 401 Unauthorized responses
        if ($response->getStatusCode() === 401) {
            Log::warning('Unauthorized access attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
            ]);
        }

        // Log 403 Forbidden responses
        if ($response->getStatusCode() === 403) {
            Log::warning('Forbidden access attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
            ]);
        }

        // Log 429 Too Many Requests responses
        if ($response->getStatusCode() === 429) {
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }
}
