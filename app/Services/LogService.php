<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogService
{
    /**
     * Log payment events with specific context.
     */
    public static function payment(string $message, array $context = [], string $level = 'info'): void
    {
        $enrichedContext = self::enrichContext($context, [
            'channel' => 'payment',
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);

        Log::channel('payment')->{$level}($message, $enrichedContext);
    }

    /**
     * Log security events.
     */
    public static function security(string $message, array $context = [], string $level = 'warning'): void
    {
        $enrichedContext = self::enrichContext($context, [
            'channel' => 'security',
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
        ]);

        Log::channel('security')->{$level}($message, $enrichedContext);
    }

    /**
     * Log booking events.
     */
    public static function booking(string $message, array $context = [], string $level = 'info'): void
    {
        $enrichedContext = self::enrichContext($context, [
            'channel' => 'booking',
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);

        Log::channel('booking')->{$level}($message, $enrichedContext);
    }

    /**
     * Log performance metrics.
     */
    public static function performance(string $message, array $metrics = []): void
    {
        $context = [
            'channel' => 'performance',
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - LARAVEL_START,
        ];

        $enrichedContext = self::enrichContext($metrics, $context);

        Log::channel('performance')->info($message, $enrichedContext);
    }

    /**
     * Log errors with full context.
     */
    public static function error(string $message, Throwable $exception = null, array $context = []): void
    {
        $errorContext = [
            'channel' => 'error',
            'user_id' => Auth::id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
        ];

        if ($exception) {
            $errorContext['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        $enrichedContext = self::enrichContext($context, $errorContext);

        Log::channel('error')->error($message, $enrichedContext);

        // Also log to main channel for immediate visibility
        Log::error($message, $enrichedContext);
    }

    /**
     * Log API requests/responses.
     */
    public static function api(string $message, array $context = [], string $level = 'info'): void
    {
        $apiContext = [
            'channel' => 'api',
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ];

        $enrichedContext = self::enrichContext($context, $apiContext);

        Log::{$level}($message, $enrichedContext);
    }

    /**
     * Log user activities for audit trail.
     */
    public static function activity(string $action, array $context = []): void
    {
        $activityContext = [
            'channel' => 'activity',
            'action' => $action,
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'user_role' => Auth::user()?->role,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];

        $enrichedContext = self::enrichContext($context, $activityContext);

        Log::info("User Activity: {$action}", $enrichedContext);
    }

    /**
     * Log database operations (slow queries, etc.).
     */
    public static function database(string $message, array $context = [], string $level = 'info'): void
    {
        $dbContext = [
            'channel' => 'database',
            'timestamp' => now()->toISOString(),
        ];

        $enrichedContext = self::enrichContext($context, $dbContext);

        Log::{$level}($message, $enrichedContext);
    }

    /**
     * Critical errors that need immediate attention.
     */
    public static function critical(string $message, array $context = []): void
    {
        $criticalContext = [
            'channel' => 'critical',
            'user_id' => Auth::id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
            'server' => gethostname(),
        ];

        $enrichedContext = self::enrichContext($context, $criticalContext);

        Log::critical($message, $enrichedContext);

        // Also try to send to Slack if configured
        try {
            Log::channel('slack')->critical($message, $enrichedContext);
        } catch (Exception $e) {
            // Don't fail if Slack logging fails
        }
    }

    /**
     * Log VNPay specific events.
     */
    public static function vnpay(string $message, array $context = [], string $level = 'info'): void
    {
        $vnpayContext = [
            'channel' => 'payment',
            'provider' => 'vnpay',
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];

        $enrichedContext = self::enrichContext($context, $vnpayContext);

        Log::channel('payment')->{$level}("[VNPay] {$message}", $enrichedContext);
    }

    /**
     * Enrich context with common metadata.
     */
    private static function enrichContext(array $context, array $additionalContext): array
    {
        return array_merge([
            'app_name' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp' => now()->toISOString(),
            'request_id' => request()->header('X-Request-ID', uniqid()),
        ], $additionalContext, $context);
    }

    /**
     * Log structured data for analytics.
     */
    public static function analytics(string $event, array $properties = []): void
    {
        $analyticsContext = [
            'channel' => 'analytics',
            'event' => $event,
            'properties' => $properties,
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];

        Log::info("Analytics Event: {$event}", $analyticsContext);
    }

    /**
     * Log slow operations for performance monitoring.
     */
    public static function slow(string $operation, float $duration, array $context = []): void
    {
        if ($duration > 1.0) { // Log operations slower than 1 second
            self::performance("Slow Operation: {$operation}", array_merge([
                'operation' => $operation,
                'duration_seconds' => $duration,
                'threshold_exceeded' => true,
            ], $context));
        }
    }

    /**
     * Create a log context from request.
     */
    public static function requestContext(Request $request = null): array
    {
        $request = $request ?: request();

        return [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'input' => $request->except(['password', 'password_confirmation', '_token']),
        ];
    }
}
