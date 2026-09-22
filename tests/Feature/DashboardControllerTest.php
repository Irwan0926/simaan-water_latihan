<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
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

    public function test_default_tanpa_param_periode_adalah_seluruh_periode(): void
    {
        $admin = $this->admin();

        Sale::create([
            'invoice' => 'INV-TEST-1',
            'user_id' => $admin->id,
            'tanggal' => now()->subMonths(2),
            'total_harga' => 50000,
        ]);
        Sale::create([
            'invoice' => 'INV-TEST-2',
            'user_id' => $admin->id,
            'tanggal' => now(),
            'total_harga' => 25000,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('salesFilter', function ($filter) {
            return ($filter['periode'] ?? null) === '';
        });
        $response->assertViewHas('salesLabelPeriode', 'Seluruh periode');
        $response->assertViewHas('salesTotalTransaksi', 2);
        $response->assertViewHas('salesTotalPendapatan', 75000.0);
    }

    public function test_tidak_ada_tombol_terapkan_filter(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Terapkan filter', false);
        $response->assertSee('Seluruh periode', false);
    }

    public function test_filter_periode_hari_ini_membatasi_data(): void
    {
        $admin = $this->admin();

        Sale::create([
            'invoice' => 'INV-OLD',
            'user_id' => $admin->id,
            'tanggal' => now()->subDays(5),
            'total_harga' => 10000,
        ]);
        Sale::create([
            'invoice' => 'INV-TODAY',
            'user_id' => $admin->id,
            'tanggal' => now(),
            'total_harga' => 20000,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard', ['periode' => 'hari_ini']));

        $response->assertOk();
        $response->assertViewHas('salesFilter', function ($filter) {
            return ($filter['periode'] ?? null) === 'hari_ini';
        });
        $response->assertViewHas('salesTotalTransaksi', 1);
        $response->assertViewHas('salesTotalPendapatan', 20000.0);
    }
}
