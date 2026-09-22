<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression as RawExpression;
use Illuminate\Support\Facades\DB;

/**
 * Sumber tunggal kebenaran soal zona waktu.
 *
 * ATURAN DASAR
 * ------------
 * 1. Seluruh infrastruktur (PHP, database, penyimpanan) berjalan pada UTC.
 *    Tidak ada kolom datetime yang disimpan dalam waktu lokal.
 * 2. Konversi ke waktu lokal HANYA terjadi di dua tempat:
 *      a. Agregasi/pengelompokan di database, memakai fungsi bawaan
 *         (CONVERT_TZ untuk MySQL, AT TIME ZONE untuk PostgreSQL).
 *      b. Tampilan ke pengguna (Blade / frontend).
 * 3. DILARANG menambah atau mengurangi jam secara manual pada logika bisnis
 *    (mis. `tanggal + INTERVAL 7 HOUR` atau `->addHours(7)`).
 *    Penambahan offset hanya boleh muncul di dalam kelas ini sebagai
 *    fallback driver yang memang tidak punya basis data zona waktu (SQLite).
 */
class AppTimezone
{
    /** Zona waktu penyimpanan — tidak boleh diubah. */
    public const STORAGE = 'UTC';

    /** Kunci session untuk zona waktu hasil deteksi browser. */
    public const SESSION_KEY = 'app_timezone';

    private ?string $resolved = null;

    /** Cache per-koneksi: apakah tabel zona waktu MySQL sudah terisi. */
    private array $namedZoneSupport = [];

    /**
     * Zona waktu aktif untuk tampilan & agregasi.
     *
     * Prioritas: preferensi pengguna → hasil deteksi browser → konfigurasi.
     */
    public function current(): string
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $candidates = [
            auth()->user()->timezone ?? null,
            $this->sessionTimezone(),
            config('app.display_timezone'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $this->isValid($candidate)) {
                return $this->resolved = $candidate;
            }
        }

        return $this->resolved = self::STORAGE;
    }

    /** Paksa zona waktu tertentu (dipakai middleware & pengujian). */
    public function use(?string $timezone): void
    {
        $this->resolved = ($timezone !== null && $this->isValid($timezone))
            ? $timezone
            : null;
    }

    public function isValid(string $timezone): bool
    {
        if ($timezone === '') {
            return false;
        }

        return in_array($timezone, timezone_identifiers_list(), true);
    }

    private function sessionTimezone(): ?string
    {
        if (! app()->bound('session') || ! app('session')->isStarted()) {
            return null;
        }

        $tz = session(self::SESSION_KEY);

        return is_string($tz) ? $tz : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Konversi tanggal (PHP)
    |--------------------------------------------------------------------------
    */

    /** "Hari ini" menurut zona waktu pengguna, sebagai tanggal polos. */
    public function today(?string $timezone = null): CarbonImmutable
    {
        return CarbonImmutable::now($timezone ?? $this->current())->startOfDay();
    }

    /**
     * Awal hari lokal → instan UTC yang setara.
     * Dipakai untuk membangun batas WHERE tanpa aritmetika jam manual.
     */
    public function startOfDayUtc(CarbonImmutable|string $date, ?string $timezone = null): CarbonImmutable
    {
        return $this->localToUtc($date, $timezone)->startOfDay()
            ->setTimezone(self::STORAGE);
    }

    /** Akhir hari lokal (23:59:59.999999) → instan UTC yang setara. */
    public function endOfDayUtc(CarbonImmutable|string $date, ?string $timezone = null): CarbonImmutable
    {
        return $this->localToUtc($date, $timezone)->endOfDay()
            ->setTimezone(self::STORAGE);
    }

    private function localToUtc(CarbonImmutable|string $date, ?string $timezone): CarbonImmutable
    {
        $tz = $timezone ?? $this->current();

        if ($date instanceof CarbonImmutable) {
            return CarbonImmutable::parse($date->format('Y-m-d H:i:s'), $tz);
        }

        return CarbonImmutable::parse($date, $tz);
    }

    /** Instan UTC dari database → waktu lokal pengguna untuk ditampilkan. */
    public function forDisplay(mixed $value, ?string $timezone = null): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::parse($value, self::STORAGE)
            ->setTimezone($timezone ?? $this->current());
    }

    /*
    |--------------------------------------------------------------------------
    | Ekspresi SQL (agregasi di database)
    |--------------------------------------------------------------------------
    */

    /**
     * Ekspresi SQL yang mengubah kolom datetime UTC menjadi waktu lokal
     * memakai fungsi bawaan database.
     */
    public function sqlLocalDateTime(string $column, ?string $timezone = null, ?Connection $connection = null): Expression
    {
        $connection ??= DB::connection();
        $tz = $timezone ?? $this->current();
        $wrapped = $connection->getQueryGrammar()->wrap($column);

        return new RawExpression(
            $this->localDateTimeSql($wrapped, $tz, $connection)
        );
    }

    /**
     * Ekspresi SQL untuk tanggal kalender lokal (YYYY-MM-DD).
     * Inilah yang dipakai untuk GROUP BY harian.
     */
    public function sqlLocalDate(string $column, ?string $timezone = null, ?Connection $connection = null): Expression
    {
        $connection ??= DB::connection();
        $tz = $timezone ?? $this->current();
        $wrapped = $connection->getQueryGrammar()->wrap($column);
        $local = $this->localDateTimeSql($wrapped, $tz, $connection);

        $sql = match ($connection->getDriverName()) {
            'pgsql' => "({$local})::date",
            'sqlsrv' => "CONVERT(date, {$local})",
            'sqlite' => "date({$local})",
            default => "DATE({$local})",
        };

        return new RawExpression($sql);
    }

    /**
     * Versi string mentah dari sqlLocalDate(), untuk dipakai pada
     * selectRaw()/groupByRaw() yang menerima string.
     */
    public function rawLocalDate(string $column, ?string $timezone = null, ?Connection $connection = null): string
    {
        $connection ??= DB::connection();

        return (string) $this->sqlLocalDate($column, $timezone, $connection)
            ->getValue($connection->getQueryGrammar());
    }

    private function localDateTimeSql(string $wrappedColumn, string $tz, Connection $connection): string
    {
        if ($tz === self::STORAGE) {
            return $wrappedColumn;
        }

        return match ($connection->getDriverName()) {
            'pgsql' => sprintf(
                "(%s AT TIME ZONE 'UTC' AT TIME ZONE %s)",
                $wrappedColumn,
                $this->quote($tz, $connection)
            ),

            'mysql', 'mariadb' => sprintf(
                'CONVERT_TZ(%s, %s, %s)',
                $wrappedColumn,
                $this->quote('+00:00', $connection),
                $this->quote($this->mysqlZone($tz, $connection), $connection)
            ),

            'sqlsrv' => sprintf(
                '(%s AT TIME ZONE %s AT TIME ZONE %s)',
                $wrappedColumn,
                $this->quote('UTC', $connection),
                $this->quote($tz, $connection)
            ),

            // SQLite tidak punya basis data zona waktu sama sekali, jadi
            // satu-satunya jalan adalah modifier offset. Dipakai pada
            // pengujian; offset dihitung, bukan di-hardcode.
            'sqlite' => sprintf(
                'datetime(%s, %s)',
                $wrappedColumn,
                $this->quote($this->sqliteModifier($tz), $connection)
            ),

            default => $wrappedColumn,
        };
    }

    /**
     * MySQL hanya mengenal nama zona ('Asia/Jakarta') bila tabel
     * mysql.time_zone_name sudah diisi (mysql_tzinfo_to_sql). Jika belum,
     * turun ke offset numerik agar CONVERT_TZ tidak mengembalikan NULL.
     */
    private function mysqlZone(string $tz, Connection $connection): string
    {
        if ($this->supportsNamedZones($connection)) {
            return $tz;
        }

        return $this->numericOffset($tz);
    }

    private function supportsNamedZones(Connection $connection): bool
    {
        $name = $connection->getName() ?? 'default';

        if (array_key_exists($name, $this->namedZoneSupport)) {
            return $this->namedZoneSupport[$name];
        }

        try {
            $row = $connection->selectOne(
                "select CONVERT_TZ('2000-01-01 00:00:00', '+00:00', 'UTC') as probe"
            );
            $supported = $row !== null && ($row->probe ?? null) !== null;
        } catch (\Throwable) {
            $supported = false;
        }

        return $this->namedZoneSupport[$name] = $supported;
    }

    /** Offset "+07:00" untuk zona waktu tertentu pada saat ini. */
    public function numericOffset(string $tz): string
    {
        $offset = (new \DateTimeZone($tz))
            ->getOffset(new \DateTimeImmutable('now', new \DateTimeZone(self::STORAGE)));

        $sign = $offset < 0 ? '-' : '+';
        $offset = abs($offset);

        return sprintf('%s%02d:%02d', $sign, intdiv($offset, 3600), intdiv($offset % 3600, 60));
    }

    private function sqliteModifier(string $tz): string
    {
        $offset = (new \DateTimeZone($tz))
            ->getOffset(new \DateTimeImmutable('now', new \DateTimeZone(self::STORAGE)));

        $minutes = (int) round($offset / 60);

        return sprintf('%+d minutes', $minutes);
    }

    private function quote(string $value, Connection $connection): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }
}
