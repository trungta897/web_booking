<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Available languages
        $availableLocales = ['en', 'vi'];

        // Start session if not already started
        if (!$request->hasSession()) {
            $request->setLaravelSession(app('session'));
        }

        // Get locale from various sources with priority order:
        // 1. URL parameter (for language switching)
        // 2. Session (for persistence)
        // 3. Default from config
        $locale = $request->get('lang') ??
                 Session::get('locale') ??
                 Config::get('app.locale', 'vi');

        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            $locale = 'vi';
        }

        // Debug logging (temporarily)
        Log::info('SetLocale Middleware Debug', [
            'requested_locale' => $request->get('lang'),
            'session_locale' => Session::get('locale'),
            'final_locale' => $locale,
            'current_locale_before' => App::getLocale(),
        ]);

        // Set application locale
        App::setLocale($locale);

        // Store in session for persistence
        Session::put('locale', $locale);

        // Debug after setting
        Log::info('SetLocale After Setting', [
            'current_locale_after' => App::getLocale(),
            'session_stored' => Session::get('locale'),
        ]);

        return $next($request);
    }
}
