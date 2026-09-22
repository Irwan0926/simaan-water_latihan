<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_pegawai_hanya_melihat_riwayat_transaksi_miliknya(): void
    {
        $pegawai = User::factory()->create(['name' => 'Kasir Satu']);
        $pegawaiLain = User::factory()->create(['name' => 'Kasir Dua']);

        $this->sale($pegawai, 'INV-MILIK-SENDIRI');
        $this->sale($pegawaiLain, 'INV-MILIK-ORANG-LAIN');

        $this->actingAs($pegawai)
            ->get(route('sales.history'))
            ->assertOk()
            ->assertSee('Riwayat Transaksi')
            ->assertSee('INV-MILIK-SENDIRI')
            ->assertDontSee('INV-MILIK-ORANG-LAIN')
            ->assertDontSee('Semua kasir');
    }

    public function test_admin_melihat_seluruh_riwayat_transaksi_dan_menu_navigasi(): void
    {
        $admin = User::factory()->admin()->create();
        $pegawaiSatu = User::factory()->create(['name' => 'Kasir Satu']);
        $pegawaiDua = User::factory()->create(['name' => 'Kasir Dua']);

        $this->sale($pegawaiSatu, 'INV-KASIR-SATU');
        $this->sale($pegawaiDua, 'INV-KASIR-DUA');

        $this->actingAs($admin)
            ->get(route('sales.history'))
            ->assertOk()
            ->assertSee('Riwayat Transaksi')
            ->assertSee('Semua Transaksi')
            ->assertSee('INV-KASIR-SATU')
            ->assertSee('INV-KASIR-DUA')
            ->assertSee(route('sales.history'), false)
            ->assertSee('Semua kasir');
    }

    public function test_admin_dapat_memfilter_riwayat_transaksi_berdasarkan_kasir(): void
    {
        $admin = User::factory()->admin()->create();
        $pegawaiSatu = User::factory()->create();
        $pegawaiDua = User::factory()->create();

        $this->sale($pegawaiSatu, 'INV-FILTER-SATU');
        $this->sale($pegawaiDua, 'INV-FILTER-DUA');

        $this->actingAs($admin)
            ->get(route('sales.history', ['kasir_id' => $pegawaiSatu->id]))
            ->assertOk()
            ->assertSee('INV-FILTER-SATU')
            ->assertDontSee('INV-FILTER-DUA');
    }

    public function test_pegawai_dapat_mengubah_transaksi_dan_stok_disesuaikan(): void
    {
        $pegawai = User::factory()->create();
        $product = Product::create([
            'nama_produk' => 'Air 330 ml',
            'harga' => 2500,
            'stok' => 8,
        ]);
        $sale = $this->sale($pegawai, 'INV-EDIT');
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 2,
            'harga' => 2500,
            'subtotal' => 5000,
        ]);
        $product->decrement('stok', 2);

        $this->actingAs($pegawai)
            ->put(route('sales.update', $sale), [
                'items' => [[
                    'product_id' => $product->id,
                    'qty' => 4,
                ]],
            ])
            ->assertRedirect(route('sales.history'));

        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 4,
            'subtotal' => 10000,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 4]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'total_harga' => 10000]);
    }

    public function test_pegawai_dapat_menghapus_transaksi_dan_stok_dikembalikan(): void
    {
        $pegawai = User::factory()->create();
        $product = Product::create([
            'nama_produk' => 'Air Galon',
            'harga' => 10000,
            'stok' => 5,
        ]);
        $sale = $this->sale($pegawai, 'INV-HAPUS');
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 3,
            'harga' => 10000,
            'subtotal' => 30000,
        ]);
        $product->decrement('stok', 3);

        $this->actingAs($pegawai)
            ->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.history'));

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertDatabaseMissing('sale_details', ['sale_id' => $sale->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stok' => 5]);
    }

    public function test_update_menerima_qty_kosong_sebagai_nol(): void
    {
        $pegawai = User::factory()->create();
        $dibeli = Product::create([
            'nama_produk' => 'Air A',
            'harga' => 1000,
            'stok' => 10,
        ]);
        $tidakDibeli = Product::create([
            'nama_produk' => 'Air B',
            'harga' => 2000,
            'stok' => 10,
        ]);
        $qtyKosong = Product::create([
            'nama_produk' => 'Air C',
            'harga' => 3000,
            'stok' => 10,
        ]);

        $sale = $this->sale($pegawai, 'INV-QTY-NOL');
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $dibeli->id,
            'qty' => 2,
            'harga' => 1000,
            'subtotal' => 2000,
        ]);
        $dibeli->decrement('stok', 2);

        $this->actingAs($pegawai)
            ->put(route('sales.update', $sale), [
                'items' => [
                    ['product_id' => $dibeli->id, 'qty' => 2],
                    ['product_id' => $tidakDibeli->id, 'qty' => 0],
                    ['product_id' => $qtyKosong->id, 'qty' => null],
                ],
            ])
            ->assertRedirect(route('sales.history'));

        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $sale->id,
            'product_id' => $dibeli->id,
            'qty' => 2,
        ]);
        $this->assertDatabaseMissing('sale_details', [
            'sale_id' => $sale->id,
            'product_id' => $tidakDibeli->id,
        ]);
        $this->assertDatabaseMissing('sale_details', [
            'sale_id' => $sale->id,
            'product_id' => $qtyKosong->id,
        ]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'total_harga' => 2000]);
        $this->assertDatabaseHas('products', ['id' => $dibeli->id, 'stok' => 8]);
        $this->assertDatabaseHas('products', ['id' => $tidakDibeli->id, 'stok' => 10]);
        $this->assertDatabaseHas('products', ['id' => $qtyKosong->id, 'stok' => 10]);
    }

    public function test_pegawai_tidak_dapat_mengubah_atau_menghapus_transaksi_pegawai_lain(): void
    {
        $pegawai = User::factory()->create();
        $pegawaiLain = User::factory()->create();
        $sale = $this->sale($pegawaiLain, 'INV-BUKAN-MILIK');

        $this->actingAs($pegawai)
            ->get(route('sales.edit', $sale))
            ->assertForbidden();

        $this->actingAs($pegawai)
            ->put(route('sales.update', $sale), ['items' => []])
            ->assertForbidden();

        $this->actingAs($pegawai)
            ->delete(route('sales.destroy', $sale))
            ->assertForbidden();
    }

    private function sale(User $pegawai, string $invoice): Sale
    {
        return Sale::create([
            'invoice' => $invoice,
            'user_id' => $pegawai->id,
            'tanggal' => now(),
            'total_harga' => 10000,
        ]);
    }
}
