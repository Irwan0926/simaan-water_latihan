<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisResult extends Model
{
    public const SUMBER_OTOMATIS = 'otomatis';

    public const SUMBER_MANUAL = 'manual';

    protected $fillable = [
        'product_id',
        'user_id',
        'periode',
        'sumber',
        'kondisi_penjualan',
        'kondisi_stok',
        'tren',
        'qty_saat_ini',
        'qty_sebelumnya',
        'stok_saat_analisis',
        'rule_terpakai',
        'rekomendasi',
        'inference_trace',
    ];

    protected function casts(): array
    {
        return [
            'qty_saat_ini' => 'integer',
            'qty_sebelumnya' => 'integer',
            'stok_saat_analisis' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeManual($query)
    {
        return $query->where('sumber', self::SUMBER_MANUAL);
    }

    public function scopeOtomatis($query)
    {
        return $query->where('sumber', self::SUMBER_OTOMATIS);
    }

    public function periodeLabel(): string
    {
        return match ($this->periode) {
            'harian' => '1 Hari',
            '3hari' => '3 Hari',
            'mingguan' => '1 Minggu',
            'bulanan' => '1 Bulan',
            default => $this->periode,
        };
    }

    public function sumberLabel(): string
    {
        return $this->sumber === self::SUMBER_MANUAL
            ? 'Dibuat admin'
            : 'Otomatis sistem';
    }
}
