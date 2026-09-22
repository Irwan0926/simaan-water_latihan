<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    /**
     * 27 rule domain depot air minum — cover SEMUA kombinasi
     * penjualan (3) × stok (3) × tren (3).
     * Tidak ada premis null / "apa saja" untuk tren (atau penjualan/stok).
     * Forward Chaining (First Match) memindai aturan urut kode_rule.
     */
    public function run(): void
    {
        Rule::query()->delete();

        $rules = [
            // —— Penjualan Tinggi, stok Sedikit ——
            ['R01', 'Tinggi', 'Sedikit', 'Naik',   'Restock Prioritas', 'restock', 'Penjualan tinggi, stok menipis, tren naik — restock segera.'],
            ['R02', 'Tinggi', 'Sedikit', 'Stabil', 'Segera Restock',    'restock', 'Penjualan tinggi dengan stok sedikit meski tren stabil.'],
            ['R03', 'Tinggi', 'Sedikit', 'Turun',  'Segera Restock',    'restock', 'Stok menipis tetap kritis meski tren turun.'],

            // —— Penjualan Tinggi, stok Aman ——
            ['R04', 'Tinggi', 'Aman', 'Naik',   'Tingkatkan Restock', 'restock', 'Antisipasi lonjakan: stok aman tapi tren naik.'],
            ['R05', 'Tinggi', 'Aman', 'Stabil', 'Pertahankan Stok',   'pertahankan', 'Kondisi ideal: jaga stok dan pasokan.'],
            ['R06', 'Tinggi', 'Aman', 'Turun',  'Pantau Penjualan',   'pantau', 'Penjualan masih tinggi namun tren turun — pantau.'],

            // —— Penjualan Tinggi, stok Banyak ——
            ['R07', 'Tinggi', 'Banyak', 'Naik',   'Pertahankan Stok',      'pertahankan', 'Permintaan kuat, stok berlebih — jaga ketersediaan.'],
            ['R08', 'Tinggi', 'Banyak', 'Stabil', 'Stok Aman',             'pertahankan', 'Stok berlebih dengan penjualan tinggi stabil.'],
            ['R09', 'Tinggi', 'Banyak', 'Turun',  'Promosi Turunkan Stok', 'promosi', 'Stok menumpuk sementara tren mulai turun.'],

            // —— Penjualan Sedang, stok Sedikit ——
            ['R10', 'Sedang', 'Sedikit', 'Naik',   'Restock',          'restock', 'Tren naik + stok sedikit — restock.'],
            ['R11', 'Sedang', 'Sedikit', 'Stabil', 'Restock Terbatas', 'restock', 'Restock proporsional, penjualan sedang.'],
            ['R12', 'Sedang', 'Sedikit', 'Turun',  'Pantau Stok',      'pantau', 'Stok sedikit tapi tren turun — pantau dulu.'],

            // —— Penjualan Sedang, stok Aman ——
            ['R13', 'Sedang', 'Aman', 'Naik',   'Tambah Stok',      'restock', 'Siapkan stok tambahan karena tren naik.'],
            ['R14', 'Sedang', 'Aman', 'Stabil', 'Pertahankan',      'pertahankan', 'Kondisi seimbang — pertahankan.'],
            ['R15', 'Sedang', 'Aman', 'Turun',  'Pantau Penjualan', 'pantau', 'Penjualan sedang, stok aman, tren turun — pantau tanpa restock berlebih.'],

            // —— Penjualan Sedang, stok Banyak ——
            ['R16', 'Sedang', 'Banyak', 'Naik',   'Promosi',               'promosi', 'Stok berlebih meski tren naik — dorong promosi.'],
            ['R17', 'Sedang', 'Banyak', 'Stabil', 'Promosi',               'promosi', 'Stok berlebih pada penjualan sedang stabil — dorong promosi.'],
            ['R18', 'Sedang', 'Banyak', 'Turun',  'Promosi Turunkan Stok', 'promosi', 'Stok menumpuk + tren turun — promosi turunkan stok.'],

            // —— Penjualan Rendah, stok Sedikit ——
            ['R19', 'Rendah', 'Sedikit', 'Naik',   'Pantau Stok', 'pantau', 'Penjualan rendah tapi tren naik, stok sedikit — pantau.'],
            ['R20', 'Rendah', 'Sedikit', 'Stabil', 'Pantau Stok', 'pantau', 'Penjualan rendah, stok sedikit — pantau tanpa restock berlebih.'],
            ['R21', 'Rendah', 'Sedikit', 'Turun',  'Pantau Stok', 'pantau', 'Penjualan lemah + tren turun, stok sedikit — pantau.'],

            // —— Penjualan Rendah, stok Aman ——
            ['R22', 'Rendah', 'Aman', 'Naik',   'Evaluasi', 'evaluasi', 'Penjualan lemah meski tren naik — evaluasi strategi.'],
            ['R23', 'Rendah', 'Aman', 'Stabil', 'Evaluasi', 'evaluasi', 'Penjualan lemah — evaluasi strategi produk.'],
            ['R24', 'Rendah', 'Aman', 'Turun',  'Evaluasi', 'evaluasi', 'Penjualan lemah + tren turun — evaluasi mendalam.'],

            // —— Penjualan Rendah, stok Banyak ——
            ['R25', 'Rendah', 'Banyak', 'Naik',   'Promosi Agresif', 'promosi', 'Stok menumpuk + penjualan rendah meski tren naik — promosi agresif.'],
            ['R26', 'Rendah', 'Banyak', 'Stabil', 'Promosi Agresif', 'promosi', 'Stok menumpuk + penjualan rendah — promosi agresif.'],
            ['R27', 'Rendah', 'Banyak', 'Turun',  'Promosi Agresif', 'promosi', 'Stok menumpuk + penjualan rendah + tren turun — promosi agresif.'],
        ];

        $now = now();

        foreach ($rules as $r) {
            Rule::create([
                'kode_rule' => $r[0],
                'penjualan' => $r[1],
                'stok' => $r[2],
                'tren' => $r[3],
                'rekomendasi' => $r[4],
                'kategori' => $r[5],
                'keterangan' => $r[6],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
