<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOwner()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya owner yang dapat mengakses halaman ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Hanya owner yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}