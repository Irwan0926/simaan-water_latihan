<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalisasi label baris product_thresholds bertipe 'stok' agar konsisten
 * dengan Rule::STOK_OPTIONS = ['Sedikit', 'Aman', 'Banyak'].
 *
 * Latar belakang: seeder awal menulis label stok level-2 yang tidak seragam
 * (mis. 'Cukup' untuk 330ml, 'Sedang' untuk 600ml/1L/2L, 'Rendah' untuk 19L).
 * Akibatnya RuleBasedService::getKondisiStok() mengembalikan label non-kanonik
 * yang tidak pernah cocok dengan kolom rules.stok di forward chaining
 * → "Belum ada saran yang cocok".
 *
 * Idempotent: hanya mengubah baris yang labelnya masih salah.
 */
return new class extends Migration
{
    public function up(): void
    {
        $canonical = [
            1 => 'Sedikit',
            2 => 'Aman',
            3 => 'Banyak',
        ];

        $updated = 0;
        foreach ($canonical as $level => $target) {
            $count = DB::table('product_thresholds')
                ->where('tipe', 'stok')
                ->where('level', $level)
                ->where('label', '!=', $target)
                ->update(['label' => $target, 'updated_at' => now()]);

            $updated += $count;
        }

        if ($updated > 0) {
            \Illuminate\Support\Facades\Log::info(
                "[normalize_stock_threshold_labels] Diperbarui {$updated} baris product_thresholds bertipe 'stok' ke label kanonik (Sedikit/Aman/Banyak)."
            );
        }
    }

    public function down(): void
    {
        // Tidak ada rollback — label asli tidak diketahui dan akan di-reset
        // oleh ProductThresholdSeeder pada seed ulang.
    }
};
