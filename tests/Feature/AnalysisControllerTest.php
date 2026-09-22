<?php

namespace Tests\Feature;

use App\Models\AnalysisResult;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisControllerTest extends TestCase
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

    private function seedCatalog(): void
    {
        $this->seed(\Database\Seeders\ProductSeeder::class);
        $this->seed(\Database\Seeders\ProductThresholdSeeder::class);
        $this->seed(\Database\Seeders\RuleSeeder::class);
    }

    private function makeSale(User $kasir, Product $product, int $qty, Carbon $tanggal): void
    {
        $sale = Sale::create([
            'invoice' => 'INV-'.uniqid(),
            'user_id' => $kasir->id,
            'tanggal' => $tanggal->copy()->setTime(10, 0),
            'total_harga' => $qty * $product->harga,
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => $qty,
            'harga' => $product->harga,
            'subtotal' => $qty * $product->harga,
        ]);
    }

    public function test_generate_330ml_mingguan_mengembalikan_rule(): void
    {
        $this->seedCatalog();
        $admin = $this->admin();
        $kasir = User::factory()->create(['role' => 'pegawai', 'is_active' => true]);

        $product = Product::where('nama_produk', 'Botol Ukuran 330 ml')->firstOrFail();
        $product->update(['stok' => 5]);

        $this->makeSale($kasir, $product, 30, Carbon::today()->subDays(1));
        $this->makeSale($kasir, $product, 20, Carbon::today()->subDays(3));
        $this->makeSale($kasir, $product, 30, Carbon::today()->subDays(10));

        $response = $this->actingAs($admin)->post(route('analysis.generate'), [
            'product_id' => $product->id,
            'periode' => 'mingguan',
        ]);

        $response->assertRedirect(route('analysis.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('hasilGenerate');

        $hasil = session('hasilGenerate');
        $this->assertSame('Botol Ukuran 330 ml', $hasil['produk']);
        $this->assertSame('mingguan', $hasil['periode']);
        $this->assertNotNull($hasil['rule']);
        $this->assertNotSame(
            'Belum ada saran yang cocok. Periksa data penjualan atau atur ulang aturan.',
            $hasil['rekomendasi']
        );

        $row = AnalysisResult::manual()->latest()->first();
        $this->assertNotNull($row);
        $this->assertSame('mingguan', $row->periode);
        $this->assertNotNull($row->rule_terpakai);
        $this->assertNotSame('-', $row->rule_terpakai);
    }

    public function test_generate_tersimpan_sebagai_periode_mingguan(): void
    {
        $this->seedCatalog();
        $admin = $this->admin();
        $kasir = User::factory()->create(['role' => 'pegawai', 'is_active' => true]);
        $product = Product::where('nama_produk', 'Botol Ukuran 330 ml')->firstOrFail();
        $product->update(['stok' => 10]);

        $this->makeSale($kasir, $product, 10, Carbon::today());

        $this->actingAs($admin)->post(route('analysis.generate'), [
            'product_id' => $product->id,
            'periode' => 'mingguan',
        ]);

        $this->assertDatabaseHas('analysis_results', [
            'product_id' => $product->id,
            'periode' => 'mingguan',
            'sumber' => AnalysisResult::SUMBER_MANUAL,
        ]);
    }

    public function test_index_menggunakan_snapshot_harian(): void
    {
        $this->seedCatalog();
        $admin = $this->admin();
        $kasir = User::factory()->create(['role' => 'pegawai', 'is_active' => true]);
        $product = Product::where('nama_produk', 'Botol Ukuran 330 ml')->firstOrFail();
        $product->update(['stok' => 20]);

        $this->makeSale($kasir, $product, 15, Carbon::today());
        $this->makeSale($kasir, $product, 10, Carbon::yesterday());

        $response = $this->actingAs($admin)->get(route('analysis.index'));
        $response->assertOk();
        $response->assertSee('Ringkasan otomatis (hari ini)', false);
        $response->assertSee('periode 1 hari', false);

        $this->assertDatabaseHas('analysis_results', [
            'product_id' => $product->id,
            'periode' => 'harian',
            'sumber' => AnalysisResult::SUMBER_OTOMATIS,
        ]);

        $this->assertDatabaseMissing('analysis_results', [
            'periode' => 'mingguan',
            'sumber' => AnalysisResult::SUMBER_OTOMATIS,
        ]);
    }

    public function test_generate_harian_tersimpan(): void
    {
        $this->seedCatalog();
        $admin = $this->admin();
        $kasir = User::factory()->create(['role' => 'pegawai', 'is_active' => true]);
        $product = Product::where('nama_produk', 'Botol Ukuran 330 ml')->firstOrFail();
        $product->update(['stok' => 10]);

        $this->makeSale($kasir, $product, 8, Carbon::today());
        $this->makeSale($kasir, $product, 5, Carbon::yesterday());

        $response = $this->actingAs($admin)->post(route('analysis.generate'), [
            'product_id' => $product->id,
            'periode' => 'harian',
        ]);

        $response->assertRedirect(route('analysis.index'));
        $this->assertDatabaseHas('analysis_results', [
            'product_id' => $product->id,
            'periode' => 'harian',
            'sumber' => AnalysisResult::SUMBER_MANUAL,
        ]);
    }

    public function test_history_uses_bootstrap_pagination(): void
    {
        $this->seedCatalog();
        $admin = $this->admin();
        $product = Product::where('nama_produk', 'Botol Ukuran 330 ml')->firstOrFail();

        foreach (range(1, 16) as $index) {
            AnalysisResult::create([
                'product_id' => $product->id,
                'user_id' => $admin->id,
                'periode' => 'harian',
                'sumber' => AnalysisResult::SUMBER_MANUAL,
                'kondisi_penjualan' => 'Rendah',
                'kondisi_stok' => 'Sedikit',
                'tren' => 'Stabil',
                'rule_terpakai' => 'R'.$index,
                'rekomendasi' => 'Pantau stok',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('analysis.history'));

        $response->assertOk();
        $response->assertSee('class="pagination"', false);
        $response->assertSee('class="page-item active"', false);
        $response->assertSee('href="'.route('analysis.history', ['page' => 2]).'"', false);
        $response->assertDontSee('h-5 w-5', false);
    }
}
