<?php

namespace Tests\Feature;

use App\Models\Rule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_store_menolak_tren_kosong(): void
    {
        Rule::query()->delete();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('rules.store'), [
            'kode_rule' => 'R99',
            'penjualan' => 'Sedang',
            'stok' => 'Aman',
            'tren' => '',
            'rekomendasi' => 'Tes',
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('tren');
        $this->assertDatabaseMissing('rules', ['kode_rule' => 'R99']);
    }

    public function test_store_menolak_penjualan_kosong(): void
    {
        Rule::query()->delete();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('rules.store'), [
            'kode_rule' => 'R98',
            'penjualan' => '',
            'stok' => 'Aman',
            'tren' => 'Turun',
            'rekomendasi' => 'Tes',
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('penjualan');
    }

    public function test_store_rule_lengkap_berhasil(): void
    {
        Rule::query()->delete();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('rules.store'), [
            'kode_rule' => 'R99',
            'penjualan' => 'Sedang',
            'stok' => 'Aman',
            'tren' => 'Turun',
            'rekomendasi' => 'Pantau Penjualan',
            'kategori' => 'pantau',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('rules.index'));
        $this->assertDatabaseHas('rules', [
            'kode_rule' => 'R99',
            'penjualan' => 'Sedang',
            'stok' => 'Aman',
            'tren' => 'Turun',
        ]);
    }

    public function test_seeder_tidak_punya_tren_null(): void
    {
        $this->seed(\Database\Seeders\RuleSeeder::class);

        $this->assertSame(27, Rule::count());
        $this->assertSame(0, Rule::whereNull('tren')->count());
        $this->assertSame(0, Rule::whereNull('penjualan')->count());
        $this->assertSame(0, Rule::whereNull('stok')->count());
    }

    public function test_store_tetap_bisa_walau_sudah_27_rule(): void
    {
        $this->seed(\Database\Seeders\RuleSeeder::class);
        $this->assertSame(27, Rule::count());
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('rules.store'), [
            'kode_rule' => 'R99',
            'penjualan' => 'Sedang',
            'stok' => 'Aman',
            'tren' => 'Turun',
            'rekomendasi' => 'Tes',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('rules.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('rules', ['kode_rule' => 'R99']);
        $this->assertSame(28, Rule::count());
    }
}
