<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogService
{
    public static function info($message, array $context = [])
    {
        Log::info($message, array_merge($context, [
            'user_id' => Auth::check() ? Auth::id() : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
    }

    public static function error($message, ?\Throwable $exception = null, array $context = [])
    {
        $context = array_merge($context, [
            'user_id' => Auth::check() ? Auth::id() : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($exception) {
            $context['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        Log::error($message, $context);
    }

    public static function activity($action, $model = null, array $context = [])
    {
        $context = array_merge($context, [
            'user_id' => Auth::check() ? Auth::id() : null,
            'action' => $action,
            'model' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
        ]);

        Log::info('User Activity', $context);
    }
}
