<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configurePasswordRules();
        $this->configureCustomValidationRules();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Authentication rate limiting
        RateLimiter::for('auth', function (Request $request) {
            $config = config('security.rate_limiting.auth');
            return Limit::perMinutes(
                $config['decay_minutes'],
                $config['max_attempts']
            )->by($request->ip());
        });

        // API rate limiting
        RateLimiter::for('api', function (Request $request) {
            $config = config('security.rate_limiting.api');
            return Limit::perMinute($config['max_attempts'])
                ->by($request->user()?->id ?: $request->ip());
        });

        // File upload rate limiting
        RateLimiter::for('upload', function (Request $request) {
            $config = config('security.rate_limiting.upload');
            return Limit::perMinutes(
                $config['decay_minutes'],
                $config['max_attempts']
            )->by($request->user()?->id ?: $request->ip());
        });

        // Search rate limiting
        RateLimiter::for('search', function (Request $request) {
            $config = config('security.rate_limiting.search');
            return Limit::perMinute($config['max_attempts'])
                ->by($request->user()?->id ?: $request->ip());
        });

        // Global rate limiting
        RateLimiter::for('global', function (Request $request) {
            $config = config('security.rate_limiting.global');
            return Limit::perMinute($config['max_attempts'])
                ->by($request->ip());
        });

        // Admin actions rate limiting
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Password reset rate limiting
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });
    }

    /**
     * Configure password validation rules.
     */
    protected function configurePasswordRules(): void
    {
        $config = config('security.password');

        Password::defaults(function () use ($config) {
            $rule = Password::min($config['min_length']);

            if ($config['require_uppercase']) {
                $rule->mixedCase();
            }

            if ($config['require_numbers']) {
                $rule->numbers();
            }

            if ($config['require_symbols']) {
                $rule->symbols();
            }

            return $rule->uncompromised();
        });
    }

    /**
     * Configure custom validation rules.
     */
    protected function configureCustomValidationRules(): void
    {
        // Validate file security
        Validator::extend('secure_file', function ($attribute, $value, $parameters, $validator) {
            if (!$value || !$value->isValid()) {
                return false;
            }

            $allowedExtensions = collect(config('security.file_upload.allowed_extensions'))
                ->flatten()
                ->toArray();

            $dangerousExtensions = config('security.file_upload.dangerous_extensions');

            $extension = strtolower($value->getClientOriginalExtension());

            // Check for dangerous extensions
            if (in_array($extension, $dangerousExtensions)) {
                return false;
            }

            // Check if extension is allowed
            return in_array($extension, $allowedExtensions);
        });

        // Validate IP address is not blacklisted
        Validator::extend('not_blacklisted_ip', function ($attribute, $value, $parameters, $validator) {
            // This would check against a blacklist of IPs
            // For now, just return true
            return true;
        });

        // Validate content doesn't contain malicious patterns
        Validator::extend('safe_content', function ($attribute, $value, $parameters, $validator) {
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
                if (preg_match($pattern, $value)) {
                    return false;
                }
            }

            return true;
        });

        // Validate strong password
        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            $config = config('security.password');

            // Check minimum length
            if (strlen($value) < $config['min_length']) {
                return false;
            }

            // Check for uppercase
            if ($config['require_uppercase'] && !preg_match('/[A-Z]/', $value)) {
                return false;
            }

            // Check for lowercase
            if ($config['require_lowercase'] && !preg_match('/[a-z]/', $value)) {
                return false;
            }

            // Check for numbers
            if ($config['require_numbers'] && !preg_match('/[0-9]/', $value)) {
                return false;
            }

            // Check for symbols
            if ($config['require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $value)) {
                return false;
            }

            return true;
        });

        // Custom validation messages
        Validator::replacer('secure_file', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' must be a secure file type.';
        });

        Validator::replacer('not_blacklisted_ip', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' is from a blacklisted IP address.';
        });

        Validator::replacer('safe_content', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' contains potentially malicious content.';
        });

        Validator::replacer('strong_password', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' must meet security requirements.';
        });
    }
}
