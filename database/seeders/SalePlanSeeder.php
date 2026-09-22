<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalePlanSeeder extends Seeder
{
    private const PRODUCT_MAP = [
        'Botol 330ml' => 'Botol Ukuran 330 ml',
        'Botol 600ml' => 'Botol Ukuran 600 ml',
        'Botol 1 liter' => 'Botol Ukuran 1 Liter',
        'Galon 2 liter' => 'Galon Ukuran 2 Liter',
        'Galon 19 Liter' => 'Isi Ulang Air Galon Ukuran 19 Liter',
    ];

    private const EMPLOYEE_SEQUENCE = [
        'rivansyah@simaan-watter.com',
        'dini@simaan-watter.com',
        'hamdan@simaan-watter.com',
        'ayin@simaan-watter.com',
        'dini@simaan-watter.com',
        'ayin@simaan-watter.com',
        'rivansyah@simaan-watter.com',
        'hamdan@simaan-watter.com',
    ];

    private const DATA = [
        ['12-Jun-2026', 'Botol 600ml', 17, 85000],
        ['12-Jun-2026', 'Galon 2 liter', 8, 160000],
        ['12-Jun-2026', 'Botol 330ml', 13, 39000],
        ['13-Jun-2026', 'Botol 600ml', 20, 100000],
        ['13-Jun-2026', 'Botol 330ml', 14, 42000],
        ['14-Jun-2026', 'Galon 19 Liter', 25, 325000],
        ['14-Jun-2026', 'Galon 2 liter', 7, 140000],
        ['15-Jun-2026', 'Botol 330ml', 11, 33000],
        ['15-Jun-2026', 'Galon 2 liter', 6, 120000],
        ['15-Jun-2026', 'Botol 1 liter', 4, 32000],
        ['16-Jun-2026', 'Botol 600ml', 16, 80000],
        ['16-Jun-2026', 'Galon 2 liter', 5, 100000],
        ['17-Jun-2026', 'Galon 19 Liter', 18, 234000],
        ['17-Jun-2026', 'Botol 330ml', 11, 33000],
        ['18-Jun-2026', 'Botol 330ml', 12, 36000],
        ['18-Jun-2026', 'Botol 600ml', 20, 100000],
        ['19-Jun-2026', 'Botol 1 liter', 7, 56000],
        ['19-Jun-2026', 'Botol 600ml', 11, 55000],
        ['19-Jun-2026', 'Galon 2 liter', 5, 100000],
        ['20-Jun-2026', 'Botol 330ml', 12, 36000],
        ['20-Jun-2026', 'Galon 19 Liter', 20, 260000],
        ['21-Jun-2026', 'Botol 600ml', 17, 85000],
        ['21-Jun-2026', 'Galon 2 liter', 4, 80000],
        ['22-Jun-2026', 'Galon 19 Liter', 25, 325000],
        ['22-Jun-2026', 'Galon 2 liter', 5, 100000],
        ['23-Jun-2026', 'Botol 330ml', 13, 39000],
        ['23-Jun-2026', 'Botol 1 liter', 7, 56000],
        ['24-Jun-2026', 'Botol 600ml', 9, 45000],
        ['24-Jun-2026', 'Galon 2 liter', 4, 60000],
        ['24-Jun-2026', 'Galon 19 Liter', 20, 260000],
        ['25-Jun-2026', 'Botol 330ml', 12, 36000],
        ['26-Jun-2026', 'Botol 600ml', 17, 85000],
        ['26-Jun-2026', 'Botol 1 liter', 3, 24000],
        ['27-Jun-2026', 'Galon 2 liter', 2, 40000],
        ['27-Jun-2026', 'Botol 330ml', 13, 39000],
        ['28-Jun-2026', 'Galon 19 Liter', 19, 247000],
        ['28-Jun-2026', 'Galon 2 liter', 5, 100000],
        ['29-Jun-2026', 'Botol 600ml', 14, 70000],
        ['30-Jun-2026', 'Botol 330ml', 9, 27000],
        ['30-Jun-2026', 'Galon 19 Liter', 15, 195000],
        ['30-Jun-2026', 'Botol 600ml', 10, 50000],
        ['1-Jul-2026', 'Botol 330ml', 15, 45000],
        ['1-Jul-2026', 'Botol 600ml', 9, 45000],
        ['1-Jul-2026', 'Botol 1 liter', 4, 32000],
        ['2-Jul-2026', 'Galon 19 Liter', 13, 169000],
        ['2-Jul-2026', 'Galon 2 liter', 3, 60000],
        ['2-Jul-2026', 'Botol 330ml', 17, 221000],
        ['3-Jul-2026', 'Botol 600ml', 11, 55000],
        ['3-Jul-2026', 'Galon 19 Liter', 13, 169000],
        ['3-Jul-2026', 'Botol 330ml', 7, 21000],
        ['4-Jul-2026', 'Botol 1 liter', 2, 16000],
        ['4-Jul-2026', 'Botol 600ml', 14, 70000],
        ['5-Jul-2026', 'Botol 330ml', 10, 30000],
        ['5-Jul-2026', 'Galon 2 liter', 6, 120000],
        ['5-Jul-2026', 'Botol 600ml', 17, 85000],
        ['6-Jul-2026', 'Botol 1 liter', 4, 32000],
        ['6-Jul-2026', 'Botol 330ml', 16, 80000],
        ['6-Jul-2026', 'Galon 19 Liter', 20, 260000],
        ['7-Jul-2026', 'Galon 2 liter', 3, 60000],
        ['7-Jul-2026', 'Botol 330ml', 14, 182000],
        ['7-Jul-2026', 'Botol 600ml', 13, 65000],
        ['8-Jul-2026', 'Galon 2 liter', 2, 40000],
        ['8-Jul-2026', 'Botol 600ml', 14, 70000],
        ['9-Jul-2026', 'Galon 19 Liter', 15, 195000],
        ['9-Jul-2026', 'Galon 2 liter', 2, 40000],
        ['10-Jul-2026', 'Botol 330ml', 7, 21000],
        ['10-Jul-2026', 'Botol 1 liter', 3, 24000],
        ['11-Jul-2026', 'Botol 330ml', 16, 208000],
        ['12-Jul-2026', 'Botol 600ml', 27, 135000],
        ['12-Jul-2026', 'Botol 1 liter', 3, 24000],
        ['12-Jul-2026', 'Galon 19 Liter', 16, 208000],
        ['13-Jul-2026', 'Botol 330ml', 9, 27000],
        ['13-Jul-2026', 'Galon 2 liter', 4, 80000],
        ['14-Jul-2026', 'Botol 1 liter', 3, 24000],
        ['14-Jul-2026', 'Botol 330ml', 10, 30000],
        ['14-Jul-2026', 'Botol 600ml', 17, 85000],
        ['15-Jul-2026', 'Galon 19 Liter', 25, 325000],
        ['15-Jul-2026', 'Galon 2 liter', 2, 40000],
        ['16-Jul-2026', 'Botol 330ml', 17, 221000],
        ['16-Jul-2026', 'Botol 1 liter', 5, 40000],
        ['16-Jul-2026', 'Galon 2 liter', 2, 40000],
        ['17-Jul-2026', 'Botol 600ml', 10, 50000],
        ['17-Jul-2026', 'Botol 330ml', 13, 39000],
        ['18-Jul-2026', 'Botol 1 liter', 1, 8000],
        ['18-Jul-2026', 'Galon 19 Liter', 7, 91000],
        ['18-Jul-2026', 'Botol 330ml', 11, 33000],
        ['19-Jul-2026', 'Botol 1 liter', 3, 24000],
        ['20-Jul-2026', 'Botol 600ml', 9, 45000],
        ['20-Jul-2026', 'Botol 1 liter', 3, 24000],
        ['21-Jul-2026', 'Botol 600ml', 20, 100000],
        ['21-Jul-2026', 'Galon 2 liter', 4, 80000],
        ['22-Jul-2026', 'Botol 330ml', 13, 39000],
        ['22-Jul-2026', 'Galon 19 Liter', 18, 234000],
        ['23-Jul-2026', 'Botol 600ml', 15, 75000],
        ['23-Jul-2026', 'Galon 2 liter', 1, 20000],
        ['23-Jul-2026', 'Botol 1 liter', 2, 16000],
        ['23-Jul-2026', 'Botol 600ml', 14, 70000],
        ['24-Jul-2026', 'Botol 330ml', 9, 27000],
        ['24-Jul-2026', 'Galon 19 Liter', 17, 221000],
        ['25-Jul-2026', 'Botol 330ml', 8, 24000],
        ['25-Jul-2026', 'Botol 600ml', 18, 90000],
        ['25-Jul-2026', 'Botol 1 liter', 4, 32000],
        ['26-Jul-2026', 'Galon 2 liter', 7, 140000],
        ['26-Jul-2026', 'Botol 330ml', 15, 45000],
        ['26-Jul-2026', 'Galon 19 Liter', 20, 260000],
        ['27-Jul-2026', 'Botol 1 liter', 7, 56000],
        ['27-Jul-2026', 'Botol 600ml', 13, 65000],
        ['28-Jul-2026', 'Botol 330ml', 8, 24000],
        ['28-Jul-2026', 'Galon 2 liter', 2, 40000],
        ['29-Jul-2026', 'Botol 600ml', 16, 80000],
        ['29-Jul-2026', 'Botol 330ml', 7, 21000],
        ['30-Jul-2026', 'Galon 2 liter', 3, 60000],
        ['30-Jul-2026', 'Galon 19 Liter', 18, 234000],
    ];

    public function run(): void
    {
        $employees = User::query()
            ->where('role', 'pegawai')
            ->whereIn('email', array_unique(self::EMPLOYEE_SEQUENCE))
            ->get()
            ->keyBy('email');

        $missingEmployees = collect(self::EMPLOYEE_SEQUENCE)
            ->unique()
            ->reject(fn (string $email): bool => $employees->has($email));

        if ($missingEmployees->isNotEmpty()) {
            throw new RuntimeException('Pegawai seeder tidak ditemukan: '.$missingEmployees->implode(', '));
        }

        $products = Product::query()->get()->keyBy('nama_produk');
        $missingProducts = collect(self::PRODUCT_MAP)
            ->reject(fn (string $name): bool => $products->has($name));

        if ($missingProducts->isNotEmpty()) {
            throw new RuntimeException('Produk seeder tidak ditemukan: '.$missingProducts->implode(', '));
        }

        DB::transaction(function () use ($employees, $products): void {
            SaleDetail::query()->delete();
            Sale::query()->delete();

            foreach (self::DATA as $index => [$dateText, $productName, $qty, $subtotal]) {
                $sequence = $index + 1;
                $date = CarbonImmutable::createFromFormat('!d-M-Y', $dateText, 'UTC')
                    ->setTime(3 + ($index % 10), ($index * 7) % 60);
                $employeeEmail = self::EMPLOYEE_SEQUENCE[$index % count(self::EMPLOYEE_SEQUENCE)];
                $product = $products[self::PRODUCT_MAP[$productName]];
                $invoice = 'INV-'.$date->format('Ymd').'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);

                $sale = Sale::query()->create([
                    'invoice' => $invoice,
                    'user_id' => $employees[$employeeEmail]->id,
                    'tanggal' => $date,
                    'total_harga' => $subtotal,
                ]);

                SaleDetail::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'harga' => $subtotal / $qty,
                    'subtotal' => $subtotal,
                ]);
            }
        });

        $this->command?->info(count(self::DATA).' transaksi penjualan dari PLAN.md tersimpan.');
    }
}
