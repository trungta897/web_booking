<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

trait HandlesControllerErrors
{
    /**
     * Handle exceptions for web requests with fallback view data.
     */
    protected function handleWebException(
        Exception $e,
        string $viewName,
        array $fallbackData = [],
        string $context = ''
    ): View {
        $this->logControllerError($e, $context);

        $errorData = array_merge($fallbackData, [
            'error' => __('An error occurred while loading the page. Please try again.'),
        ]);

        return view($viewName, $errorData);
    }

    /**
     * Handle exceptions for redirect responses.
     */
    protected function handleRedirectException(
        Exception $e,
        string $context = '',
        ?string $redirectRoute = null
    ): RedirectResponse {
        $this->logControllerError($e, $context);

        $redirect = $redirectRoute ? redirect()->route($redirectRoute) : back();

        return $redirect->withErrors(['error' => $e->getMessage()]);
    }

    /**
     * Handle exceptions for API/AJAX requests.
     */
    protected function handleJsonException(
        Exception $e,
        string $context = '',
        int $statusCode = 400
    ): JsonResponse {
        $this->logControllerError($e, $context);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $statusCode);
    }

    /**
     * Handle exceptions with success redirect.
     */
    protected function handleSuccessRedirect(
        string $route,
        string $message,
        array $with = []
    ): RedirectResponse {
        return redirect()
            ->route($route)
            ->with('success', $message)
            ->with($with);
    }

    /**
     * Log controller errors with context.
     */
    private function logControllerError(Exception $e, string $context = ''): void
    {
        Log::error(static::class . ' Error: ' . $context, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => Auth::id(),
            'url' => request()->url(),
            'method' => request()->method(),
        ]);
    }
}
