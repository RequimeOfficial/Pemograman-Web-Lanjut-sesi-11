<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memaksa semua request /api/* diperlakukan sebagai request JSON, sehingga error
 * (401, 404, 422) selalu dibalas JSON walau klien lupa mengirim header Accept.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
