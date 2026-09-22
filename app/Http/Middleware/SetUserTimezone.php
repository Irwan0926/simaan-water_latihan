<?php

namespace App\Http\Middleware;

use App\Support\AppTimezone;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menetapkan zona waktu tampilan untuk request ini.
 *
 * Sumber (urutan prioritas):
 *  1. Preferensi tersimpan pada akun pengguna.
 *  2. Zona waktu perangkat, dikirim frontend lewat cookie/header.
 *
 * Middleware ini TIDAK mengubah zona waktu PHP (tetap UTC). Ia hanya
 * memberi tahu aplikasi zona mana yang dipakai untuk menampilkan dan
 * mengelompokkan tanggal.
 */
class SetUserTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = app(AppTimezone::class);

        $user = $request->user();

        if ($user && is_string($user->timezone) && $timezone->isValid($user->timezone)) {
            // Preferensi akun menang atas deteksi perangkat.
            $timezone->use($user->timezone);
        } else {
            // Zona waktu perangkat pengguna (dikirim otomatis oleh frontend via cookie).
            $detected = $request->cookie('tz') ?? $request->header('X-Timezone');

            if (is_string($detected) && $timezone->isValid($detected)) {
                session([AppTimezone::SESSION_KEY => $detected]);
                $timezone->use($detected);
            } else {
                $timezone->use(
                    session(AppTimezone::SESSION_KEY) ?? config('app.display_timezone')
                );
            }
        }

        return $next($request);
    }
}
