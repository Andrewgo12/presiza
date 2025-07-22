<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | application including rate limiting, file upload restrictions,
    | and security monitoring settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for different types of requests.
    |
    */
    'rate_limiting' => [
        'auth' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'upload' => [
            'max_attempts' => 10,
            'decay_minutes' => 5,
        ],
        'search' => [
            'max_attempts' => 30,
            'decay_minutes' => 1,
        ],
        'global' => [
            'max_attempts' => 100,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configure file upload security settings including allowed file types,
    | maximum file sizes, and content validation.
    |
    */
    'file_upload' => [
        'max_size' => 50 * 1024 * 1024, // 50MB
        'allowed_extensions' => [
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'],
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
            'videos' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
            'audio' => ['mp3', 'wav', 'ogg', 'aac'],
            'archives' => ['zip', 'rar', '7z', 'tar', 'gz'],
        ],
        'dangerous_extensions' => [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js',
            'jar', 'php', 'asp', 'aspx', 'jsp', 'py', 'rb', 'pl',
            'sh', 'bash', 'ps1', 'psm1',
        ],
        'scan_content' => true,
        'quarantine_suspicious' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers that will be added to all responses.
    |
    */
    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
        'hsts' => [
            'max_age' => 31536000,
            'include_subdomains' => true,
            'preload' => true,
        ],
        'csp' => [
            'default_src' => "'self'",
            'script_src' => "'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.jsdelivr.net",
            'style_src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com",
            'font_src' => "'self' https://fonts.gstatic.com",
            'img_src' => "'self' data: https: blob:",
            'connect_src' => "'self'",
            'media_src' => "'self'",
            'object_src' => "'none'",
            'child_src' => "'self'",
            'frame_ancestors' => "'none'",
            'form_action' => "'self'",
            'base_uri' => "'self'",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure security event monitoring and logging.
    |
    */
    'monitoring' => [
        'log_failed_logins' => true,
        'log_admin_access' => true,
        'log_suspicious_activity' => true,
        'log_file_uploads' => true,
        'alert_on_multiple_failures' => true,
        'failure_threshold' => 5,
        'alert_email' => env('SECURITY_ALERT_EMAIL'),
        'block_suspicious_ips' => false,
        'suspicious_ip_threshold' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    |
    | Configure password security requirements.
    |
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'prevent_common_passwords' => true,
        'prevent_personal_info' => true,
        'history_limit' => 5, // Prevent reusing last 5 passwords
        'expiry_days' => 90, // Force password change every 90 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configure session security settings.
    |
    */
    'session' => [
        'timeout_minutes' => 120, // 2 hours
        'regenerate_on_login' => true,
        'invalidate_on_logout' => true,
        'concurrent_sessions' => 3, // Max concurrent sessions per user
        'track_ip_changes' => true,
        'track_user_agent_changes' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Configure API security settings.
    |
    */
    'api' => [
        'require_https' => env('APP_ENV') === 'production',
        'rate_limit_per_minute' => 60,
        'require_authentication' => true,
        'allowed_origins' => [
            env('APP_URL'),
        ],
        'token_expiry_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    |
    | Configure encryption settings for sensitive data.
    |
    */
    'encryption' => [
        'encrypt_files' => false,
        'encrypt_database_fields' => [
            'users.ssn',
            'users.phone',
            'evidences.sensitive_data',
        ],
        'key_rotation_days' => 365,
    ],

];
