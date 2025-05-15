<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and is an admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            // If not authenticated or not an admin, redirect to login
            if ($this->isAdminDomain($request)) {
                return redirect()->route('login');
            }
        }

        // If the user is an admin and trying to access admin routes via main domain
        // redirect to admin domain
        if (Auth::check() && Auth::user()->role === 'admin' && !$this->isAdminDomain($request)) {
            // Only redirect admin routes, not all routes
            if ($request->is('admin*')) {
                return redirect()->to($this->getAdminUrl($request->path()));
            }
        }

        return $next($request);
    }

    /**
     * Check if the request is coming from the admin domain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isAdminDomain(Request $request)
    {
        $host = $request->getHost();
        return str_starts_with($host, 'admin.') || $host === config('app.admin_domain');
    }

    /**
     * Get the admin domain URL for a given path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getAdminUrl($path)
    {
        $adminDomain = config('app.admin_domain') ?? 'admin.' . config('app.domain');
        $scheme = request()->secure() ? 'https://' : 'http://';

        return $scheme . $adminDomain . '/' . $path;
    }
}
