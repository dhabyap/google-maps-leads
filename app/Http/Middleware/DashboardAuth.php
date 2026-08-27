<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardAuth
{
    /**
     * Proteksi dashboard: cek flag session 'dashboard_authed'.
     * Login di-handle AuthController. API scraper tetap pakai X-Api-Key (api.key).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('dashboard_authed')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
