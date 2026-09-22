<?php

namespace Tests\Unit;

use App\Services\SalesPeriodService;
use App\Support\AppTimezone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class SalesPeriodServiceTest extends TestCase
{
    private SalesPeriodService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SalesPeriodService::class);
    }

    public function test_default_kosong_adalah_seluruh_periode(): void
    {
        $request = Request::create('/dashboard', 'GET');
        $filter = $this->service->resolveFilter($request);

        $this->assertSame('', $filter['periode']);

        $range = $this->service->resolveRange($filter);
        $this->assertSame('Seluruh periode', $range['label']);
        $this->assertFalse($range['apply']);
        $this->assertNull($range['start']);
        $this->assertNull($range['end']);
    }

    public function test_periode_7_hari_memiliki_rentang_7_hari_inklusif(): void
    {
        $today = Carbon::parse('2026-07-25');
        $range = $this->service->resolveRange(
            ['periode' => '7_hari', 'bulan' => null, 'dari' => null, 'sampai' => null],
            $today,
            'UTC'
        );

        $this->assertTrue($range['apply']);
        $this->assertTrue($range['valid']);
        $this->assertSame('2026-07-19', $range['start']->toDateString());
        $this->assertSame('2026-07-25', $range['end']->toDateString());
        $this->assertEquals(6, (int) $range['start']->diffInDays($range['end']->copy()->startOfDay()));
    }

    public function test_periode_satu_bulan_terakhir_memakai_rentang_berjalan(): void
    {
        $range = $this->service->resolveRange(
            ['periode' => '1_bulan', 'bulan' => null, 'dari' => null, 'sampai' => null],
            Carbon::parse('2026-08-15'),
            'UTC'
        );

        $this->assertTrue($range['apply']);
        $this->assertSame('2026-07-15 00:00:00', $range['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-15 23:59:59', $range['end']->format('Y-m-d H:i:s'));
    }

    public function test_periode_kustom_dari_lebih_besar_dari_sampai_ditukar(): void
    {
        $range = $this->service->resolveRange([
            'periode' => 'kustom',
            'bulan' => null,
            'dari' => '2026-07-20',
            'sampai' => '2026-07-10',
        ], null, 'UTC');

        $this->assertTrue($range['apply']);
        $this->assertSame('2026-07-10', $range['start']->toDateString());
        $this->assertSame('2026-07-20', $range['end']->toDateString());
    }

    public function test_periode_hari_ini(): void
    {
        $today = Carbon::parse('2026-07-25');
        $range = $this->service->resolveRange(
            ['periode' => 'hari_ini', 'bulan' => null, 'dari' => null, 'sampai' => null],
            $today,
            'UTC'
        );

        $this->assertTrue($range['apply']);
        $this->assertTrue($range['start']->isSameDay($today));
        $this->assertTrue($range['end']->isSameDay($today));
    }

    /*
    |--------------------------------------------------------------------------
    | Zona waktu
    |--------------------------------------------------------------------------
    */

    public function test_hari_ini_wib_menghasilkan_batas_utc_yang_bergeser(): void
    {
        $range = $this->service->resolveRange(
            ['periode' => 'hari_ini', 'bulan' => null, 'dari' => null, 'sampai' => null],
            Carbon::parse('2026-07-25'),
            'Asia/Jakarta'
        );

        // 25 Juli 00:00 WIB = 24 Juli 17:00 UTC
        $this->assertSame('2026-07-24 17:00:00', $range['start']->utc()->format('Y-m-d H:i:s'));
        // 25 Juli 23:59:59 WIB = 25 Juli 16:59:59 UTC
        $this->assertSame('2026-07-25 16:59:59', $range['end']->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('Asia/Jakarta', $range['timezone']);
    }

    public function test_rentang_kustom_wib_inklusif_penuh_dalam_utc(): void
    {
        $range = $this->service->resolveRange([
            'periode' => 'kustom',
            'bulan' => null,
            'dari' => '2026-07-01',
            'sampai' => '2026-07-31',
        ], null, 'Asia/Jakarta');

        $this->assertSame('2026-06-30 17:00:00', $range['start']->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-31 16:59:59', $range['end']->utc()->format('Y-m-d H:i:s'));
    }

    public function test_label_rentang_memakai_tanggal_lokal_bukan_utc(): void
    {
        $range = $this->service->resolveRange(
            ['periode' => 'hari_ini', 'bulan' => null, 'dari' => null, 'sampai' => null],
            Carbon::parse('2026-07-25'),
            'Asia/Jakarta'
        );

        // Label harus tetap "25 Jul 2026" walau batas UTC mulai 24 Juli.
        $this->assertSame('25 Jul 2026', $range['rentang']);
    }

    public function test_bulan_tertentu_wib(): void
    {
        $range = $this->service->resolveRange([
            'periode' => 'bulan',
            'bulan' => '2026-02',
            'dari' => null,
            'sampai' => null,
        ], null, 'Asia/Jakarta');

        $this->assertTrue($range['apply']);
        $this->assertSame('Februari 2026', $range['label']);
        $this->assertSame('2026-01-31 17:00:00', $range['start']->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-28 16:59:59', $range['end']->utc()->format('Y-m-d H:i:s'));
    }

    public function test_zona_waktu_mengikuti_preferensi_aktif(): void
    {
        app(AppTimezone::class)->use('Asia/Jayapura');

        $range = $this->service->resolveRange(
            ['periode' => 'hari_ini', 'bulan' => null, 'dari' => null, 'sampai' => null],
            Carbon::parse('2026-07-25')
        );

        // UTC+9 → 25 Juli 00:00 WIT = 24 Juli 15:00 UTC
        $this->assertSame('Asia/Jayapura', $range['timezone']);
        $this->assertSame('2026-07-24 15:00:00', $range['start']->utc()->format('Y-m-d H:i:s'));
    }
}
