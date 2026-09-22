<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_halaman_laporan_default_satu_bulan_terakhir(): void
    {
        $this->travelTo('2026-08-15 12:00:00');
        $admin = $this->admin();

        Sale::create([
            'invoice' => 'INV-DALAM-PERIODE',
            'user_id' => $admin->id,
            'tanggal' => '2026-08-01 12:00:00',
            'total_harga' => 15000,
        ]);
        Sale::create([
            'invoice' => 'INV-LUAR-PERIODE',
            'user_id' => $admin->id,
            'tanggal' => '2026-07-01 12:00:00',
            'total_harga' => 50000,
        ]);

        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Periode penjualan')
            ->assertSee('1 bulan terakhir')
            ->assertSee('Rp 15.000')
            ->assertDontSee('Rp 65.000')
            ->assertSee('action="'.route('reports.index').'"', false)
            ->assertSee('formaction="'.route('reports.exportPdf').'"', false)
            ->assertDontSee('Export Cepat')
            ->assertDontSee('Riwayat Penjualan')
            ->assertDontSee('INV-DALAM-PERIODE')
            ->assertDontSee('INV-LUAR-PERIODE')
            ->assertDontSee('reportTableBody');
    }

    public function test_card_laporan_berubah_sesuai_filter(): void
    {
        $this->travelTo('2026-08-15 12:00:00');
        $admin = $this->admin();
        $product = Product::create([
            'nama_produk' => 'Air Mineral',
            'stok' => 100,
            'harga' => 5000,
        ]);

        $saleJuli = Sale::create([
            'invoice' => 'INV-JULI',
            'user_id' => $admin->id,
            'tanggal' => '2026-07-10 12:00:00',
            'total_harga' => 10000,
        ]);
        SaleDetail::create([
            'sale_id' => $saleJuli->id,
            'product_id' => $product->id,
            'qty' => 2,
            'harga' => 5000,
            'subtotal' => 10000,
        ]);

        $saleAgustus = Sale::create([
            'invoice' => 'INV-AGUSTUS',
            'user_id' => $admin->id,
            'tanggal' => '2026-08-10 12:00:00',
            'total_harga' => 35000,
        ]);
        SaleDetail::create([
            'sale_id' => $saleAgustus->id,
            'product_id' => $product->id,
            'qty' => 7,
            'harga' => 5000,
            'subtotal' => 35000,
        ]);

        $this->actingAs($admin)
            ->get(route('reports.index', [
                'periode' => 'bulan',
                'bulan' => '2026-07',
            ]))
            ->assertOk()
            ->assertSee('<p class="stat-value mt-2">1</p>', false)
            ->assertSee('Rp 10.000')
            ->assertSee('<p class="stat-value mt-2">2</p>', false)
            ->assertDontSee('Rp 35.000');
    }

    public function test_laporan_dapat_difilter_berdasarkan_produk(): void
    {
        $admin = $this->admin();
        $airMineral = Product::create([
            'nama_produk' => 'Air Mineral',
            'stok' => 100,
            'harga' => 5000,
        ]);
        $airGalon = Product::create([
            'nama_produk' => 'Air Galon',
            'stok' => 100,
            'harga' => 15000,
        ]);

        $sale = Sale::create([
            'invoice' => 'INV-PRODUK-CAMPURAN',
            'user_id' => $admin->id,
            'tanggal' => now(),
            'total_harga' => 25000,
        ]);
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $airMineral->id,
            'qty' => 2,
            'harga' => 5000,
            'subtotal' => 10000,
        ]);
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $airGalon->id,
            'qty' => 1,
            'harga' => 15000,
            'subtotal' => 15000,
        ]);

        $this->actingAs($admin)
            ->get(route('reports.index', ['product_id' => $airMineral->id]))
            ->assertOk()
            ->assertSee('<option value="">Semua</option>', false)
            ->assertSee('value="'.$airMineral->id.'" selected', false)
            ->assertSee('<p class="stat-value mt-2">1</p>', false)
            ->assertSee('Rp 10.000')
            ->assertSee('<p class="stat-value mt-2">2</p>', false)
            ->assertDontSee('Rp 25.000');
    }

    public function test_export_pdf_mendukung_filter_produk_dan_ringkasan_semua_produk(): void
    {
        $admin = $this->admin();
        $product = Product::create([
            'nama_produk' => 'Air Mineral',
            'stok' => 100,
            'harga' => 5000,
        ]);
        $sale = Sale::create([
            'invoice' => 'INV-RINGKASAN-PRODUK',
            'user_id' => $admin->id,
            'tanggal' => now(),
            'total_harga' => 15000,
        ]);
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 3,
            'harga' => 5000,
            'subtotal' => 15000,
        ]);

        $allProductsResponse = $this->actingAs($admin)->get(route('reports.exportPdf'));
        $filteredResponse = $this->actingAs($admin)->get(route('reports.exportPdf', [
            'product_id' => $product->id,
        ]));

        $allProductsResponse->assertOk();
        $filteredResponse->assertOk();
        $this->assertStringStartsWith('%PDF', $allProductsResponse->getContent());
        $this->assertStringStartsWith('%PDF', $filteredResponse->getContent());
    }

    public function test_export_pdf_gabungan_route_tersedia(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('reports.exportCombinedPdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'laporan-gabungan-',
            $response->headers->get('content-disposition') ?? ''
        );
    }

    public function test_export_pdf_gabungan_kosong_jika_tidak_ada_transaksi(): void
    {
        $admin = $this->admin();

        // DomPDF sukses meski data kosong
        $response = $this->actingAs($admin)->get(route('reports.exportCombinedPdf'));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_export_pdf_gabungan_dengan_transaksi(): void
    {
        $admin = $this->admin();
        $this->seed(\Database\Seeders\ProductSeeder::class);
        $product = Product::first();

        $sale = Sale::create([
            'invoice' => 'INV-RPT-1',
            'user_id' => $admin->id,
            'tanggal' => now(),
            'total_harga' => 15000,
        ]);
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 5,
            'harga' => 3000,
            'subtotal' => 15000,
        ]);

        $response = $this->actingAs($admin)->get(route('reports.exportCombinedPdf'));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_export_pdf_menggunakan_periode_yang_dipilih(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('reports.exportPdf', [
            'periode' => 'bulan',
            'bulan' => '2026-07',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'laporan-penjualan-2026-07.pdf',
            $response->headers->get('content-disposition') ?? ''
        );
    }

    public function test_export_pdf_harian_masih_berfungsi(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('reports.exportPdf', [
            'periode' => 'hari_ini',
        ]));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
