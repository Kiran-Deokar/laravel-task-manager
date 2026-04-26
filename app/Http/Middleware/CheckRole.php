<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ApiResponse;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Not authenticated
        if (!$request->user()) {
            return ApiResponse::error('Unauthenticated', null, 401);
        }

        // Role check
        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // Unauthorized
        return ApiResponse::error(
            'Unauthorized. Required role: ' . implode(', ', $roles),
            null,
            403
        );
    }
}