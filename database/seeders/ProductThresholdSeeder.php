<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductThreshold;
use Illuminate\Database\Seeder;

class ProductThresholdSeeder extends Seeder
{
    /**
     * Batas kondisi per produk (sumber: PLAN.md, basis per-minggu).
     *
     * Format: [nama_produk, tipe, level, label, batas]
     *   tipe  : 'penjualan' | 'stok'
     *   level : 1 (Rendah/Sedikit), 2 (Sedang/Aman), 3 (Tinggi/Banyak)
     *   label : label tampilan tersimpan di DB.
     *           - Penjualan: kanonik Rendah / Sedang / Tinggi (cocok Rule::PENJUALAN_OPTIONS).
     *           - Stok     : kanonik Sedikit / Aman / Banyak   (cocok Rule::STOK_OPTIONS).
     *   batas : batas atas level (null untuk level 3 = "> batas level 2").
     */
    private const DATA = [
        // ===== Botol Ukuran 330 ml — penjualan & stok per minggu =====
        ['Botol Ukuran 330 ml', 'penjualan', 1, 'Rendah',  80],
        ['Botol Ukuran 330 ml', 'penjualan', 2, 'Sedang', 150],
        ['Botol Ukuran 330 ml', 'penjualan', 3, 'Tinggi', null],
        ['Botol Ukuran 330 ml', 'stok',      1, 'Sedikit', 40],
        ['Botol Ukuran 330 ml', 'stok',      2, 'Aman',    100],
        ['Botol Ukuran 330 ml', 'stok',      3, 'Banyak',  null],

        // ===== Botol Ukuran 600 ml =====
        ['Botol Ukuran 600 ml', 'penjualan', 1, 'Rendah',  400],
        ['Botol Ukuran 600 ml', 'penjualan', 2, 'Sedang',  625],
        ['Botol Ukuran 600 ml', 'penjualan', 3, 'Tinggi', null],
        ['Botol Ukuran 600 ml', 'stok',      1, 'Sedikit', 150],
        ['Botol Ukuran 600 ml', 'stok',      2, 'Aman',    350],
        ['Botol Ukuran 600 ml', 'stok',      3, 'Banyak',  null],

        // ===== Botol Ukuran 1 Liter =====
        ['Botol Ukuran 1 Liter', 'penjualan', 1, 'Rendah',  100],
        ['Botol Ukuran 1 Liter', 'penjualan', 2, 'Sedang',  200],
        ['Botol Ukuran 1 Liter', 'penjualan', 3, 'Tinggi', null],
        ['Botol Ukuran 1 Liter', 'stok',      1, 'Sedikit', 50],
        ['Botol Ukuran 1 Liter', 'stok',      2, 'Aman',    120],
        ['Botol Ukuran 1 Liter', 'stok',      3, 'Banyak',  null],

        // ===== Isi Ulang Air Galon Ukuran 19 Liter =====
        ['Isi Ulang Air Galon Ukuran 19 Liter', 'penjualan', 1, 'Rendah',  120],
        ['Isi Ulang Air Galon Ukuran 19 Liter', 'penjualan', 2, 'Sedang',  200],
        ['Isi Ulang Air Galon Ukuran 19 Liter', 'penjualan', 3, 'Tinggi', null],
        ['Isi Ulang Air Galon Ukuran 19 Liter', 'stok',      1, 'Sedikit', 50],
        ['Isi Ulang Air Galon Ukuran 19 Liter', 'stok',      2, 'Aman',    120],
        ['Isi Ulang Air Galon Ukuran 19 Liter', 'stok',      3, 'Banyak',  null],

        // ===== Galon Ukuran 2 Liter =====
        ['Galon Ukuran 2 Liter', 'penjualan', 1, 'Rendah',  40],
        ['Galon Ukuran 2 Liter', 'penjualan', 2, 'Sedang',  80],
        ['Galon Ukuran 2 Liter', 'penjualan', 3, 'Tinggi', null],
        ['Galon Ukuran 2 Liter', 'stok',      1, 'Sedikit', 20],
        ['Galon Ukuran 2 Liter', 'stok',      2, 'Aman',    50],
        ['Galon Ukuran 2 Liter', 'stok',      3, 'Banyak',  null],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::DATA as $row) {
            [$nama, $tipe, $level, $label, $batas] = $row;

            $product = Product::where('nama_produk', $nama)->first();
            if (! $product) {
                continue;
            }

            ProductThreshold::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'tipe' => $tipe,
                    'level' => $level,
                ],
                [
                    'label' => $label,
                    'batas' => $batas,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
