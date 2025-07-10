<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
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

        // Get locale from session first, then fall back to config default
        $locale = $request->session()->get('locale', Config::get('app.locale', 'vi'));

        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            $locale = 'vi';
        }

        // Set application locale
        App::setLocale($locale);

        // Store in session for persistence if not already set
        if (!$request->session()->has('locale')) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
