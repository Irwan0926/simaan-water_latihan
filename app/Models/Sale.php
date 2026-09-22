<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice',
        'user_id',
        'tanggal',
        'total_harga',
    ];

    /**
     * `tanggal` disimpan dalam UTC. Cast ini membuatnya jadi instans
     * tanggal ber-zona UTC; konversi ke waktu pengguna dilakukan saat
     * tampilan (App\Support\Waktu) atau agregasi (AppTimezone::sqlLocalDate).
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'immutable_datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
