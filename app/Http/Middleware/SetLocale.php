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

        // Get locale from multiple sources with priority
        $locale = $this->determineLocale($request, $availableLocales);

        // Set application locale
        App::setLocale($locale);

        // Store in session for persistence if changed
        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }

    /**
     * Determine the best locale to use.
     */
    private function determineLocale(Request $request, array $availableLocales): string
    {
        // 1. Check if locale is explicitly set in request (from language switcher)
        $requestLocale = $request->get('locale');
        if ($requestLocale && in_array($requestLocale, $availableLocales)) {
            return $requestLocale;
        }

        // 2. Check session
        $sessionLocale = $request->session()->get('locale');
        if ($sessionLocale && in_array($sessionLocale, $availableLocales)) {
            return $sessionLocale;
        }

        // 3. Check current app locale (might be set by previous request)
        $currentLocale = App::getLocale();
        if ($currentLocale && in_array($currentLocale, $availableLocales)) {
            return $currentLocale;
        }

        // 4. Check browser language preferences
        $browserLocale = $this->getBrowserLocale($request, $availableLocales);
        if ($browserLocale) {
            return $browserLocale;
        }

        // 5. Fall back to config default
        $configLocale = Config::get('app.locale', 'vi');
        if (in_array($configLocale, $availableLocales)) {
            return $configLocale;
        }

        // 6. Final fallback
        return 'vi';
    }

    /**
     * Get best matching locale from browser Accept-Language header.
     */
    private function getBrowserLocale(Request $request, array $availableLocales): ?string
    {
        $acceptLanguages = $request->getLanguages();
        
        foreach ($acceptLanguages as $lang) {
            // Check exact match
            if (in_array($lang, $availableLocales)) {
                return $lang;
            }
            
            // Check language prefix (e.g., 'en-US' -> 'en')
            $prefix = substr($lang, 0, 2);
            if (in_array($prefix, $availableLocales)) {
                return $prefix;
            }
        }

        return null;
    }
}
