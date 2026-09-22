<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\AppTimezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Memastikan kontrak zona waktu terjaga:
 *  - penyimpanan selalu UTC,
 *  - agregasi memakai fungsi zona waktu bawaan database,
 *  - tampilan memakai waktu pengguna.
 */
class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $override = []): User
    {
        return User::factory()->admin()->create(array_merge([
            'email_verified_at' => now(),
            'is_active' => true,
        ], $override));
    }

    private function pegawai(array $override = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'pegawai',
            'email_verified_at' => now(),
            'is_active' => true,
        ], $override));
    }

    public function test_aplikasi_berjalan_pada_utc(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('UTC', date_default_timezone_get());
    }

    public function test_koneksi_database_dikonfigurasi_utc(): void
    {
        // Semua koneksi utama harus memaksa sesi database ke UTC.
        // MySQL/MariaDB menerima offset "+00:00"; PostgreSQL menerima "UTC".
        // Keduanya ekuivalen — yang penting bukan zona waktu lokal manapun.
        $konfigurasiUtc = [
            config('database.connections.mysql.timezone'),
            config('database.connections.mariadb.timezone'),
            config('database.connections.pgsql.timezone'),
        ];

        foreach ($konfigurasiUtc as $tz) {
            $this->assertTrue(
                $tz === '+00:00' || $tz === 'UTC' || $tz === '+00:00',
                "Koneksi database harus memakai sesi UTC, didapat: {$tz}"
            );
        }
    }

    public function test_tanggal_penjualan_disimpan_dalam_utc(): void
    {
        $this->travelTo('2026-07-25 18:30:00'); // UTC

        $sale = Sale::create([
            'invoice' => 'INV-TEST-0001',
            'user_id' => $this->admin()->id,
            'tanggal' => now(),
            'total_harga' => 1000,
        ]);

        // Nilai mentah di database harus UTC, bukan waktu lokal.
        $raw = DB::table('sales')->where('id', $sale->id)->value('tanggal');

        $this->assertStringStartsWith('2026-07-25 18:30:00', (string) $raw);
    }

    public function test_konversi_tampilan_memakai_zona_waktu_pengguna(): void
    {
        $timezone = app(AppTimezone::class);
        $timezone->use('Asia/Jakarta');

        // 2026-07-25 18:30 UTC = 2026-07-26 01:30 WIB (sudah ganti hari)
        $lokal = $timezone->forDisplay('2026-07-25 18:30:00');

        $this->assertSame('2026-07-26 01:30', $lokal->format('Y-m-d H:i'));
        $this->assertSame('Asia/Jakarta', $lokal->timezone->getName());
    }

    public function test_batas_hari_lokal_dikonversi_ke_utc(): void
    {
        $timezone = app(AppTimezone::class);

        $awal = $timezone->startOfDayUtc('2026-07-25', 'Asia/Jakarta');
        $akhir = $timezone->endOfDayUtc('2026-07-25', 'Asia/Jakarta');

        $this->assertSame('2026-07-24 17:00:00', $awal->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-25 16:59:59', $akhir->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $awal->timezone->getName());
    }

    public function test_offset_numerik_dihitung_bukan_hardcode(): void
    {
        $timezone = app(AppTimezone::class);

        $this->assertSame('+07:00', $timezone->numericOffset('Asia/Jakarta'));
        $this->assertSame('+08:00', $timezone->numericOffset('Asia/Makassar'));
        $this->assertSame('+09:00', $timezone->numericOffset('Asia/Jayapura'));
        $this->assertSame('+00:00', $timezone->numericOffset('UTC'));
    }

    public function test_ekspresi_sql_memakai_fungsi_bawaan_database(): void
    {
        $timezone = app(AppTimezone::class);
        $sql = $timezone->rawLocalDate('tanggal', 'Asia/Jakarta');

        // Tidak boleh ada aritmetika jam manual pada logika bisnis.
        $this->assertStringNotContainsString('INTERVAL', strtoupper($sql));

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->assertStringContainsString('CONVERT_TZ', $sql);
        } elseif ($driver === 'pgsql') {
            $this->assertStringContainsString('AT TIME ZONE', $sql);
        } else {
            // SQLite tidak punya basis data zona waktu; fallback terdokumentasi.
            $this->assertStringContainsString('datetime', $sql);
        }
    }

    /**
     * Inti requirement: transaksi pada 2026-07-25 18:30 UTC adalah
     * 2026-07-26 di WIB, jadi harus masuk ember tanggal 26 — bukan 25.
     */
    public function test_agregasi_harian_mengelompokkan_menurut_hari_lokal(): void
    {
        $admin = $this->admin();
        $timezone = app(AppTimezone::class);

        Sale::create([
            'invoice' => 'INV-TZ-0001',
            'user_id' => $admin->id,
            'tanggal' => '2026-07-25 18:30:00', // UTC
            'total_harga' => 5000,
        ]);

        $perHariUtc = Sale::query()
            ->selectRaw($timezone->rawLocalDate('tanggal', 'UTC') . ' as tgl')
            ->selectRaw('SUM(total_harga) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $perHariWib = Sale::query()
            ->selectRaw($timezone->rawLocalDate('tanggal', 'Asia/Jakarta') . ' as tgl')
            ->selectRaw('SUM(total_harga) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $this->assertSame('2026-07-25', (string) $perHariUtc->keys()->first());
        $this->assertSame('2026-07-26', (string) $perHariWib->keys()->first());
    }

    public function test_preferensi_zona_waktu_pengguna_dipakai(): void
    {
        $admin = $this->admin(['timezone' => 'Asia/Jayapura']);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertSame('Asia/Jayapura', $admin->effectiveTimezone());
    }

    public function test_pengguna_tanpa_preferensi_memakai_fallback_konfigurasi(): void
    {
        $admin = $this->admin(['timezone' => null]);

        $this->assertSame(config('app.display_timezone'), $admin->effectiveTimezone());
    }

    public function test_zona_waktu_perangkat_dari_cookie_dipakai(): void
    {
        $admin = $this->admin(['timezone' => null]);

        app(AppTimezone::class)->use(null);

        // Send unencrypted cookie header as a real browser would.
        $this->actingAs($admin)
            ->withUnencryptedCookie('tz', 'Asia/Makassar')
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertSame('Asia/Makassar', session(AppTimezone::SESSION_KEY));
        $this->assertSame('Asia/Makassar', app(AppTimezone::class)->current());
    }

    public function test_zona_waktu_tidak_valid_dari_cookie_diabaikan(): void
    {
        $admin = $this->admin(['timezone' => null]);

        $this->actingAs($admin)
            ->withCookie('tz', 'Tidak/Valid')
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertSame(
            config('app.display_timezone'),
            app(AppTimezone::class)->current()
        );
    }

    public function test_preferensi_akun_mengalahkan_cookie_perangkat(): void
    {
        $admin = $this->admin(['timezone' => 'Asia/Jayapura']);

        $this->actingAs($admin)
            ->withCookie('tz', 'Asia/Jakarta')
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertSame('Asia/Jayapura', app(AppTimezone::class)->current());
    }

    public function test_riwayat_transaksi_menampilkan_jam_lokal(): void
    {
        $admin = $this->admin(['timezone' => 'Asia/Jakarta']);

        Sale::create([
            'invoice' => 'INV-TZ-0002',
            'user_id' => $admin->id,
            'tanggal' => '2026-07-25 18:30:00', // UTC
            'total_harga' => 7500,
        ]);

        // 18:30 UTC = 26-07-2026 01:30 WIB
        $this->actingAs($admin)
            ->get(route('sales.history'))
            ->assertOk()
            ->assertSee('26-07-2026 01:30')
            ->assertDontSee('25-07-2026 18:30');
    }

    public function test_invoice_memakai_tanggal_lokal_kasir(): void
    {
        $this->travelTo('2026-07-25 18:30:00'); // = 26 Juli WIB

        $kasir = $this->pegawai(['timezone' => 'Asia/Jakarta']);
        $product = Product::create([
            'nama_produk' => 'Galon Aqua 19L',
            'harga' => 5000,
            'stok' => 100,
        ]);

        $response = $this->actingAs($kasir)->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.create'));
        $response->assertSessionHas('success');
        $response->assertSessionHasNoErrors();

        $sale = Sale::firstOrFail();

        // Nomor invoice harus mengikuti tanggal lokal kasir (26), bukan UTC (25).
        $this->assertStringContainsString('INV-20260726', $sale->invoice);
        // Namun penyimpanannya tetap UTC.
        $this->assertSame(
            '2026-07-25 18:30:00',
            DB::table('sales')->where('id', $sale->id)->value('tanggal')
        );
    }
}
