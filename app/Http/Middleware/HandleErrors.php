<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HandleErrors
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Exception $e) {
            Log::error('Application Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => Auth::check() ? Auth::id() : null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => config('app.debug') ? $e->getMessage() : 'Please try again later',
                ], 500);
            }

            return response()->view('errors.500', [], 500);
        }
    }
}
