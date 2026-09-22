<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PegawaiMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response
    {
        if (
            !auth()->check() ||
            auth()->user()->role !== 'pegawai'
        ) {
            abort(403, 'Akses ditolak. Kasir hanya untuk pegawai.');
        }

        return $next($request);
    }
}
