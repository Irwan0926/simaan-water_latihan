<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Services\RuleBasedService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

// Link dashboard
Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)->middleware(['auth', 'auth.active', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'auth.active'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */



    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | KASIR (hanya pegawai)
    |--------------------------------------------------------------------------
    */

    Route::middleware('pegawai')->group(function () {
        Route::get(
            '/sales/create',
            [SaleController::class, 'create']
        )->name('sales.create');

        Route::post(
            '/sales/store',
            [SaleController::class, 'store']
        )->name('sales.store');

        Route::get(
            '/sales/{sale}/edit',
            [SaleController::class, 'edit']
        )->name('sales.edit');

        Route::put(
            '/sales/{sale}',
            [SaleController::class, 'update']
        )->name('sales.update');

        Route::delete(
            '/sales/{sale}',
            [SaleController::class, 'destroy']
        )->name('sales.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | RIWAYAT TRANSAKSI (pegawai: miliknya sendiri; admin: seluruhnya)
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales/history',
        [SaleController::class, 'history']
    )->name('sales.history');

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | RULE BASE INSIGHT
        |--------------------------------------------------------------------------
        */

        // Halaman memilih produk & periode
        Route::get(
            '/analysis/create',
            [AnalysisController::class, 'create']
        )->name('analysis.create');

        // Proses generate analisis
        Route::post(
            '/analysis/generate',
            [AnalysisController::class, 'generate']
        )->name('analysis.generate');

        // Halaman hasil analisis
        Route::get(
            '/analysis',
            [AnalysisController::class, 'index']
        )->name('analysis.index');

        // Riwayat saran AI (generate manual)
        Route::get(
            '/analysis/history',
            [AnalysisController::class, 'history']
        )->name('analysis.history');

        /*
        |--------------------------------------------------------------------------
        | KELOLA RULE BASE (Knowledge Base)
        |--------------------------------------------------------------------------
        */

        Route::resource('rules', RuleController::class)
            ->except(['create', 'edit']);

        /*
        |--------------------------------------------------------------------------
        | CRUD PRODUK + AMBANG BATAS KONDISI (di halaman detail produk)
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'products',
            ProductController::class
        );

        // Simpan ambang batas penjualan & stok per produk
        Route::put(
            '/products/{product}/thresholds',
            [ProductController::class, 'updateThresholds']
        )->name('products.thresholds');

        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN PENGGUNA
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'users',
            UserController::class
        )->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | LAPORAN PENJUALAN
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports',
            [ReportController::class, 'index']
        )->name('reports.index');

        Route::get(
            '/reports/export-pdf',
            [ReportController::class, 'exportPdf']
        )->name('reports.exportPdf');

        Route::get(
            '/reports/export-combined-pdf',
            [ReportController::class, 'exportCombinedPdf']
        )->name('reports.exportCombinedPdf');

    });

    Route::get(
        '/reports/{sale}',
        [ReportController::class, 'show']
    )->name('reports.show');

});

/*
|--------------------------------------------------------------------------
| TEST RULE
|--------------------------------------------------------------------------
*/

Route::get('/test-rule', function () {
    $service = new RuleBasedService;

    return response()->json(
        $service->analisis('Botol Ukuran 330 ml', 50, 30, 5)
    );
});

require __DIR__.'/auth.php';
