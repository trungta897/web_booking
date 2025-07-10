<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GzipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only compress if the browser supports gzip and content is compressible
        if ($this->shouldCompress($request, $response)) {
            $this->compressResponse($response);
        }

        return $response;
    }

    /**
     * Determine if the response should be compressed.
     */
    private function shouldCompress(Request $request, Response $response): bool
    {
        // Check if browser supports gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (strpos($acceptEncoding, 'gzip') === false) {
            return false;
        }

        // Check if content is already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        // Check content type
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'text/xml',
            'application/xml',
            'text/plain',
        ];

        foreach ($compressibleTypes as $type) {
            if (strpos($contentType, $type) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compress the response content.
     */
    private function compressResponse(Response $response): void
    {
        $content = $response->getContent();

        if ($content && strlen($content) > 1024) { // Only compress if content > 1KB
            $compressed = gzencode($content, 6); // Compression level 6 (good balance)

            if ($compressed !== false) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Content-Length', strlen($compressed));
                $response->headers->set('Vary', 'Accept-Encoding');
            }
        }
    }
}
