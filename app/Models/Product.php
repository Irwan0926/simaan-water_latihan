<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_produk',
        'harga',
        'stok',
    ];

    /**
     * Relasi ke detail penjualan
     */
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Relasi ke ambang batas kondisi (penjualan & stok).
     */
    public function thresholds()
    {
        return $this->hasMany(ProductThreshold::class);
    }

    /**
     * Buat threshold default untuk produk baru agar tidak terlewat
     * saat dianalisis (nilai awal 0, bisa diubah admin di halaman Ambang Batas).
     */
    protected static function booted(): void
    {
        static::created(function (Product $product) {
            ProductThreshold::createDefaultsFor($product);
        });
    }
}
