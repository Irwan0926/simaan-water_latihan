<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\PegawaiMiddleware;
use App\Http\Middleware\SetUserTimezone;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Zona waktu tampilan ditentukan per request (setelah session siap).
        $middleware->web(append: [
            SetUserTimezone::class,
        ]);

        // Cookie `tz` ditulis oleh JavaScript (hasil deteksi perangkat),
        // jadi tidak boleh ikut dienkripsi Laravel.
        $middleware->encryptCookies(except: [
            'tz',
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'pegawai' => PegawaiMiddleware::class,
            'auth.active' => EnsureUserIsActive::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();