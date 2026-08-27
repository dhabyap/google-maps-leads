<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Simple API key auth: X-Api-Key header must match SCRAPER_API_KEY env.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Api-Key', '');
        $expected = config('services.scraper.api_key');

        if ($expected === null || $expected === '' || ! hash_equals($expected, $key)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
