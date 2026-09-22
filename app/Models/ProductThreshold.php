<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductThreshold extends Model
{
    public const TIPE_PENJUALAN = 'penjualan';

    public const TIPE_STOK = 'stok';

    public const TIPE_OPTIONS = [self::TIPE_PENJUALAN, self::TIPE_STOK];

    /**
     * Label kanonik per (tipe, level) — dipakai rule matching.
     * Level 1 = terendah, 2 = sedang, 3 = tertinggi.
     */
    public const CANONICAL = [
        'penjualan' => [1 => 'Rendah', 2 => 'Sedang', 3 => 'Tinggi'],
        'stok' => [1 => 'Sedikit', 2 => 'Aman', 3 => 'Banyak'],
    ];

    protected $fillable = [
        'product_id',
        'tipe',
        'level',
        'label',
        'batas',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'batas' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopePenjualan($query)
    {
        return $query->where('tipe', self::TIPE_PENJUALAN);
    }

    public function scopeStok($query)
    {
        return $query->where('tipe', self::TIPE_STOK);
    }

    public function canonicalLabel(): string
    {
        return self::CANONICAL[$this->tipe][$this->level] ?? $this->label;
    }

    /**
     * Buat threshold default untuk sebuah produk (dipakai saat produk baru dibuat).
     */
    public static function createDefaultsFor(Product $product): void
    {
        $now = now();

        foreach (self::CANONICAL as $tipe => $levels) {
            foreach ($levels as $level => $label) {
                self::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'tipe' => $tipe,
                        'level' => $level,
                    ],
                    [
                        'label' => $label,
                        'batas' => $level < 3 ? 0 : null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}
