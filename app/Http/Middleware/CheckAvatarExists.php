<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckAvatarExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users and avoid checking during AJAX uploads
        if (Auth::check() && !$request->isXmlHttpRequest() && !$request->is('debug-*')) {
            try {
                $user = Auth::user();

                // If user has avatar but file doesn't exist, clear it
                if ($user->avatar && !Storage::exists('public/avatars/' . $user->avatar)) {
                    $oldAvatar = $user->avatar;
                    $user->avatar = null;
                    $user->save();

                    // Log the auto-clear action
                    \Illuminate\Support\Facades\Log::info('Auto-cleared missing avatar', [
                        'user_id' => $user->id,
                        'old_avatar' => $oldAvatar,
                        'route' => $request->route() ? $request->route()->getName() : $request->path(),
                    ]);
                }
            } catch (\Exception $e) {
                // Don't break the request if avatar check fails
                \Illuminate\Support\Facades\Log::warning('Avatar check middleware failed', [
                    'error' => $e->getMessage(),
                    'route' => $request->path(),
                ]);
            }
        }

        return $next($request);
    }
}
