<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'nama_produk' => 'Botol Ukuran 330 ml',
                'harga' => 3000,
                'stok' => 0,
            ],
            [
                'nama_produk' => 'Botol Ukuran 600 ml',
                'harga' => 5000,
                'stok' => 0,
            ],
            [
                'nama_produk' => 'Botol Ukuran 1 Liter',
                'harga' => 8000,
                'stok' => 0,
            ],
            [
                'nama_produk' => 'Galon Ukuran 2 Liter',
                'harga' => 20000,
                'stok' => 0,
            ],
            [
                'nama_produk' => 'Isi Ulang Air Galon Ukuran 19 Liter',
                'harga' => 13000,
                'stok' => 0,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['nama_produk' => $product['nama_produk']],
                [
                    'harga' => $product['harga'],
                    'stok' => $product['stok'],
                ]
            );
        }
    }
}
