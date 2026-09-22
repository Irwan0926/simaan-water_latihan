<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_plan_users_and_sales(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseHas('users', [
            'name' => 'Suherlan',
            'email' => 'suherlan@simaan-watter.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        foreach ([
            'rivansyah@simaan-watter.com',
            'dini@simaan-watter.com',
            'hamdan@simaan-watter.com',
            'ayin@simaan-watter.com',
        ] as $email) {
            $this->assertDatabaseHas('users', [
                'email' => $email,
                'role' => 'pegawai',
                'is_active' => true,
            ]);
        }

        $this->assertTrue(Hash::check(
            'password',
            User::query()->where('email', 'suherlan@simaan-watter.com')->value('password')
        ));
        $this->assertSame(113, Sale::query()->count());
        $this->assertSame(113, SaleDetail::query()->count());
        $this->assertSame(0, Sale::query()->whereHas('user', fn ($query) => $query->where('role', 'admin'))->count());

        $firstSale = Sale::query()->with(['user', 'details.product'])->where('invoice', 'INV-20260612-00001')->firstOrFail();
        $this->assertSame('rivansyah@simaan-watter.com', $firstSale->user->email);
        $this->assertSame('2026-06-12 03:00:00', $firstSale->tanggal->format('Y-m-d H:i:s'));
        $this->assertSame(85000.0, (float) $firstSale->total_harga);
        $this->assertSame('Botol Ukuran 600 ml', $firstSale->details->sole()->product->nama_produk);
        $this->assertSame(17, $firstSale->details->sole()->qty);
        $this->assertSame(85000.0, (float) $firstSale->details->sole()->subtotal);

        $this->assertDatabaseHas('sales', [
            'invoice' => 'INV-20260730-00113',
            'total_harga' => 234000,
        ]);
        $this->assertDatabaseHas('sale_details', [
            'qty' => 4,
            'harga' => 15000,
            'subtotal' => 60000,
        ]);
    }

    public function test_database_seeder_is_repeatable_and_keeps_employee_assignments(): void
    {
        $this->seed(DatabaseSeeder::class);

        $assignments = Sale::query()
            ->with('user:id,email')
            ->orderBy('invoice')
            ->get()
            ->mapWithKeys(fn (Sale $sale): array => [$sale->invoice => $sale->user->email])
            ->all();

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('sales', 113);
        $this->assertDatabaseCount('sale_details', 113);
        $this->assertSame(
            $assignments,
            Sale::query()
                ->with('user:id,email')
                ->orderBy('invoice')
                ->get()
                ->mapWithKeys(fn (Sale $sale): array => [$sale->invoice => $sale->user->email])
                ->all()
        );
    }
}
