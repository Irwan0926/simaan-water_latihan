<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    /**
     * Jumlah kombinasi premis unik = 3×3×3 (penjualan × stok × tren).
     * Hanya dipakai sebagai informasi cakupan knowledge base,
     * BUKAN sebagai batas maksimal jumlah aturan.
     */
    public const TOTAL_KOMBINASI = 27;

    public const PENJUALAN_OPTIONS = ['Rendah', 'Sedang', 'Tinggi'];

    public const STOK_OPTIONS = ['Sedikit', 'Aman', 'Banyak'];

    public const TREN_OPTIONS = ['Naik', 'Stabil', 'Turun'];

    public const KATEGORI_OPTIONS = [
        'restock',
        'promosi',
        'pertahankan',
        'evaluasi',
        'pantau',
    ];

    protected $fillable = [
        'kode_rule',
        'penjualan',
        'stok',
        'tren',
        'rekomendasi',
        'kategori',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function premiseLabel(): string
    {
        $p = $this->penjualan ?? '(kosong)';
        $s = $this->stok ?? '(kosong)';
        $t = $this->tren ?? '(kosong)';

        return "Penjualan={$p} AND Stok={$s} AND Tren={$t}";
    }

    public function hasCompletePremises(): bool
    {
        return $this->penjualan !== null
            && $this->stok !== null
            && $this->tren !== null;
    }
}
