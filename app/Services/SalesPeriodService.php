<?php

namespace App\Services;

use App\Support\AppTimezone;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Resolusi filter periode penjualan.
 *
 * ZONA WAKTU
 * ----------
 * Batas periode dihitung menurut kalender LOKAL pengguna, lalu diubah
 * menjadi instan UTC untuk dipakai pada klausa WHERE. Data di database
 * tetap UTC dan tidak pernah digeser secara manual.
 * Contoh: "hari ini" bagi pengguna WIB = 00:00–23:59 WIB
 *         = 17:00 (H-1) sampai 16:59:59 UTC.
 *
 * Definisi rentang (konsisten di Laporan & Dashboard):
 * - hari_ini   : hari kalender hari ini (00:00–23:59)
 * - 7_hari     : rolling 7 hari termasuk hari ini (hari ini − 6 s/d hari ini)
 * - minggu_ini : minggu kalender Senin–Minggu
 * - bulan_ini  : bulan kalender berjalan (tanggal 1 s/d akhir bulan)
 * - bulan      : bulan kalender tertentu (YYYY-MM), tanggal 1 s/d akhir
 * - kustom     : rentang tanggal bebas (dari–sampai, inklusif)
 * - (kosong)   : seluruh data penjualan
 */
class SalesPeriodService
{
    public function __construct(
        private AppTimezone $timezone
    ) {}

    public const PERIODE_OPTIONS = [
        '' => [
            'label' => 'Seluruh periode',
            'deskripsi' => 'Semua transaksi tanpa batasan tanggal.',
        ],
        'hari_ini' => [
            'label' => 'Hari ini',
            'deskripsi' => 'Mulai 00:00 sampai 23:59 hari ini.',
        ],
        '7_hari' => [
            'label' => '7 hari terakhir',
            'deskripsi' => 'Rolling 7 hari termasuk hari ini (bukan Senin–Minggu).',
        ],
        'minggu_ini' => [
            'label' => 'Minggu ini',
            'deskripsi' => 'Minggu kalender Senin–Minggu (minggu berjalan).',
        ],
        'bulan_ini' => [
            'label' => 'Bulan ini',
            'deskripsi' => 'Dari tanggal 1 sampai akhir bulan berjalan.',
        ],
        'bulan' => [
            'label' => 'Bulan tertentu',
            'deskripsi' => 'Satu bulan kalender penuh (tanggal 1–akhir bulan).',
        ],
        'kustom' => [
            'label' => 'Rentang kustom',
            'deskripsi' => 'Pilih tanggal mulai dan tanggal akhir (inklusif).',
        ],
    ];

    /**
     * Ambil & normalisasi parameter filter dari request.
     * Legacy: ?bulan=YYYY-MM tanpa periode → filter bulan tertentu.
     */
    public function resolveFilter(Request $request): array
    {
        $periode = (string) $request->get('periode', '');
        $bulan = $request->get('bulan');
        $dari = $request->get('dari');
        $sampai = $request->get('sampai');

        if ($periode === '' && $bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $periode = 'bulan';
        }

        if ($periode !== '1_bulan' && ! array_key_exists($periode, self::PERIODE_OPTIONS)) {
            $periode = '';
        }

        return [
            'periode' => $periode,
            'bulan' => is_string($bulan) ? $bulan : null,
            'dari' => is_string($dari) ? $dari : null,
            'sampai' => is_string($sampai) ? $sampai : null,
        ];
    }

    /**
     * Hitung rentang tanggal + label dari filter.
     *
     * `start` dan `end` dikembalikan sebagai instan UTC yang siap dipakai
     * pada klausa WHERE, sedangkan label memakai tanggal lokal.
     *
     * @return array{
     *   start: ?Carbon,
     *   end: ?Carbon,
     *   label: string,
     *   rentang: ?string,
     *   valid: bool,
     *   apply: bool,
     *   timezone: string
     * }
     */
    public function resolveRange(array $filter, ?Carbon $today = null, ?string $timezone = null): array
    {
        $tz = $timezone ?? $this->timezone->current();

        // Titik acuan "hari ini" menurut kalender lokal pengguna.
        $localToday = $today
            ? CarbonImmutable::parse($today->format('Y-m-d'), $tz)->startOfDay()
            : $this->timezone->today($tz);

        $periode = $filter['periode'] ?? '';
        $label = $periode === '1_bulan'
            ? '1 bulan terakhir'
            : (self::PERIODE_OPTIONS[$periode]['label'] ?? 'Seluruh periode');
        $rentang = null;
        $localStart = null;
        $localEnd = null;
        $valid = true;
        $apply = false;

        switch ($periode) {
            case 'hari_ini':
                $localStart = $localToday;
                $localEnd = $localToday;
                $apply = true;
                break;

            case '7_hari':
                $localStart = $localToday->subDays(6);
                $localEnd = $localToday;
                $apply = true;
                break;

            case '1_bulan':
                $localStart = $localToday->subMonthNoOverflow();
                $localEnd = $localToday;
                $apply = true;
                break;

            case 'minggu_ini':
                $localStart = $localToday->startOfWeek(Carbon::MONDAY);
                $localEnd = $localToday->endOfWeek(Carbon::SUNDAY);
                $apply = true;
                break;

            case 'bulan_ini':
                $localStart = $localToday->startOfMonth();
                $localEnd = $localToday->endOfMonth();
                $apply = true;
                break;

            case 'bulan':
                $bulan = $filter['bulan'] ?? '';
                if ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
                    try {
                        $tgl = CarbonImmutable::parse($bulan . '-01', $tz);
                        $localStart = $tgl->startOfMonth();
                        $localEnd = $tgl->endOfMonth();
                        $label = $this->namaBulanIndonesia($tgl->month) . ' ' . $tgl->year;
                        $apply = true;
                    } catch (\Throwable $e) {
                        $valid = false;
                    }
                } else {
                    $valid = false;
                }
                break;

            case 'kustom':
                $dari = $filter['dari'] ?? '';
                $sampai = $filter['sampai'] ?? '';
                if (
                    $dari && $sampai
                    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)
                    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)
                ) {
                    try {
                        $localStart = CarbonImmutable::parse($dari, $tz)->startOfDay();
                        $localEnd = CarbonImmutable::parse($sampai, $tz)->startOfDay();
                        if ($localStart->gt($localEnd)) {
                            [$localStart, $localEnd] = [$localEnd, $localStart];
                        }
                        $apply = true;
                    } catch (\Throwable $e) {
                        $valid = false;
                    }
                } else {
                    $valid = false;
                }
                break;

            default:
                break;
        }

        $start = null;
        $end = null;

        if ($apply && $localStart && $localEnd) {
            $rentang = $this->formatRentang(
                Carbon::instance($localStart->toDateTime()),
                Carbon::instance($localEnd->toDateTime())
            );

            // Batas lokal → instan UTC (tanpa aritmetika jam manual).
            $start = Carbon::instance(
                $this->timezone->startOfDayUtc($localStart, $tz)->toDateTime()
            );
            $end = Carbon::instance(
                $this->timezone->endOfDayUtc($localEnd, $tz)->toDateTime()
            );
        }

        if ($periode !== '' && ! $valid) {
            $label = 'Periode tidak valid';
            $rentang = null;
            $start = null;
            $end = null;
            $apply = false;
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
            'rentang' => $rentang,
            'valid' => $valid,
            'apply' => $apply,
            'timezone' => $tz,
        ];
    }

    /**
     * Terapkan filter periode ke query Sale (kolom tanggal).
     * Jika filter tidak valid (mis. kustom tanpa tanggal), query di-force empty.
     */
    public function applyToQuery(Builder $query, array $filter, string $column = 'tanggal', ?string $timezone = null): Builder
    {
        $range = $this->resolveRange($filter, null, $timezone);

        if (($filter['periode'] ?? '') !== '' && ! $range['valid']) {
            return $query->whereRaw('0 = 1');
        }

        if ($range['apply'] && $range['start'] && $range['end']) {
            $query->whereBetween($column, [$range['start'], $range['end']]);
        }

        return $query;
    }

    public function formatRentang(Carbon $mulai, Carbon $akhir): string
    {
        if ($mulai->isSameDay($akhir)) {
            return $mulai->translatedFormat('d M Y');
        }

        if ($mulai->format('Y-m') === $akhir->format('Y-m')) {
            return $mulai->format('d') . '–' . $akhir->translatedFormat('d M Y');
        }

        if ($mulai->format('Y') === $akhir->format('Y')) {
            return $mulai->translatedFormat('d M') . ' – ' . $akhir->translatedFormat('d M Y');
        }

        return $mulai->translatedFormat('d M Y') . ' – ' . $akhir->translatedFormat('d M Y');
    }

    public function namaBulanIndonesia(int $bulan): string
    {
        $nama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $nama[$bulan] ?? '';
    }
}
