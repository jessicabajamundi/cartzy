<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * Prevents browser cache and bfcache from displaying sensitive, authenticated
     * account pages when the user presses the Back or Forward buttons after logging out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (Throwable $e) {
            // Report before rendering so server errors still reach the log.
            app(ExceptionHandler::class)->report($e);
            $response = app(ExceptionHandler::class)->render($request, $e);
        }

        // Instruct browser to never cache HTML web pages on disk or in memory
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
