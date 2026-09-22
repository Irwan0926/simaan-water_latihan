<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Pembantu tampilan waktu untuk Blade.
 *
 * Semua nilai datetime yang masuk dianggap UTC (sesuai penyimpanan) dan
 * dikeluarkan dalam zona waktu pengguna. Tidak ada aritmetika jam manual.
 */
class Waktu
{
    public static function zona(): string
    {
        return app(AppTimezone::class)->current();
    }

    /** Objek tanggal dalam zona waktu pengguna, atau null. */
    public static function lokal(mixed $value): ?CarbonImmutable
    {
        return app(AppTimezone::class)->forDisplay($value);
    }

    /** Tanggal + jam, mis. "11-08-2026 21:39". */
    public static function tampil(mixed $value, string $format = 'd-m-Y H:i', string $kosong = '-'): string
    {
        return self::lokal($value)?->format($format) ?? $kosong;
    }

    /** Hanya tanggal, mis. "11-08-2026". */
    public static function tanggal(mixed $value, string $format = 'd-m-Y', string $kosong = '-'): string
    {
        return self::lokal($value)?->format($format) ?? $kosong;
    }

    /** Tanggal dengan nama bulan terlokalisasi, mis. "11 Agu 2026". */
    public static function tanggalPanjang(mixed $value, string $format = 'd M Y', string $kosong = '-'): string
    {
        return self::lokal($value)?->translatedFormat($format) ?? $kosong;
    }

    /** Hanya jam, mis. "21:39". */
    public static function jam(mixed $value, string $format = 'H:i', string $kosong = '-'): string
    {
        return self::lokal($value)?->format($format) ?? $kosong;
    }

    /** Waktu sekarang menurut zona waktu pengguna. */
    public static function sekarang(string $format = 'd-m-Y H:i'): string
    {
        return CarbonImmutable::now(self::zona())->format($format);
    }

    /** Label singkat zona waktu aktif, mis. "WIB" atau "Asia/Jakarta". */
    public static function labelZona(): string
    {
        $tz = self::zona();
        $abbr = CarbonImmutable::now($tz)->format('T');

        // Offset numerik (mis. "+07") kurang informatif; pakai nama kota.
        if (preg_match('/^[+-]/', $abbr) === 1) {
            return str_contains($tz, '/')
                ? str_replace('_', ' ', substr($tz, strrpos($tz, '/') + 1))
                : $tz;
        }

        return $abbr;
    }

    /**
     * Daftar zona waktu untuk dropdown. Zona Indonesia didahulukan,
     * disusul seluruh zona lain agar pengguna luar negeri tetap terlayani.
     *
     * @return array<string, string>
     */
    public static function pilihanZona(): array
    {
        $utama = [
            'Asia/Jakarta' => 'WIB — Jakarta (UTC+7)',
            'Asia/Makassar' => 'WITA — Makassar (UTC+8)',
            'Asia/Jayapura' => 'WIT — Jayapura (UTC+9)',
        ];

        $lainnya = [];

        foreach (timezone_identifiers_list() as $zona) {
            if (isset($utama[$zona])) {
                continue;
            }

            $lainnya[$zona] = str_replace('_', ' ', $zona);
        }

        return $utama + $lainnya;
    }
}
