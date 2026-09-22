<?php

namespace Tests\Unit;

use App\Models\ProductThreshold;
use App\Models\Rule;
use App\Services\RuleBasedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleBasedServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedRules(): void
    {
        $this->seed(\Database\Seeders\RuleSeeder::class);
    }

    private function seedThresholds(): void
    {
        $this->seed(\Database\Seeders\ProductSeeder::class);
        $this->seed(\Database\Seeders\ProductThresholdSeeder::class);
    }

    public function test_kondisi_penjualan_botol_330ml(): void
    {
        $this->seedThresholds();
        $service = new RuleBasedService;

        // PLAN.md: Rendah <= 80, Sedang 81-150, Tinggi > 150
        $this->assertSame('Rendah', $service->getKondisiPenjualan('Botol Ukuran 330 ml', 10));
        $this->assertSame('Sedang', $service->getKondisiPenjualan('Botol Ukuran 330 ml', 100));
        $this->assertSame('Tinggi', $service->getKondisiPenjualan('Botol Ukuran 330 ml', 200));
    }

    public function test_kondisi_stok_botol_330ml(): void
    {
        $this->seedThresholds();
        $service = new RuleBasedService;

        // PLAN.md: Sedikit <= 40, Aman 41-100, Banyak > 100
        $this->assertSame('Sedikit', $service->getKondisiStok('Botol Ukuran 330 ml', 5));
        $this->assertSame('Aman', $service->getKondisiStok('Botol Ukuran 330 ml', 50));
        $this->assertSame('Banyak', $service->getKondisiStok('Botol Ukuran 330 ml', 200));
    }

    public function test_tren(): void
    {
        $service = new RuleBasedService;

        $this->assertSame('Naik', $service->getTren(50, 30)['tren']);
        $this->assertSame('Turun', $service->getTren(20, 40)['tren']);
        $this->assertSame('Stabil', $service->getTren(100, 100)['tren']);
    }

    public function test_forward_chaining_first_match_restock(): void
    {
        $this->seedRules();
        $this->seedThresholds();
        $service = new RuleBasedService;

        // PLAN.md Botol 330ml: penjualan Tinggi (>150), stok Sedikit (<=40), tren Naik
        $hasil = $service->analisis('Botol Ukuran 330 ml', 200, 100, 5);

        $this->assertSame('Tinggi', $hasil['kondisi_penjualan']);
        $this->assertSame('Sedikit', $hasil['kondisi_stok']);
        $this->assertSame('Naik', $hasil['tren']);
        $this->assertSame('R01', $hasil['kode_rule']);
        $this->assertSame('Restock Prioritas', $hasil['rekomendasi']);
        $this->assertArrayNotHasKey('cf_hasil', $hasil);
        $this->assertArrayNotHasKey('cf_detail', $hasil);
        $this->assertStringContainsString('PROSES ANALISIS RULE BASED', $hasil['inference_trace']);
        $this->assertStringContainsString('Forward Chaining', $hasil['inference_trace']);
        $this->assertStringContainsString('First Match', $hasil['inference_trace']);
        $this->assertStringNotContainsString('Certainty Factor', $hasil['inference_trace']);
    }

    public function test_promosi_agresif_rendah_banyak(): void
    {
        $this->seedRules();
        $this->seedThresholds();
        $service = new RuleBasedService;

        // PLAN.md Botol 330ml: penjualan Rendah (<=80), stok Banyak (>100), tren Stabil → R26
        $hasil = $service->analisis('Botol Ukuran 330 ml', 5, 5, 200);

        $this->assertSame('R26', $hasil['kode_rule']);
        $this->assertSame('Promosi Agresif', $hasil['rekomendasi']);
    }

    public function test_penjelasan_tren_menggunakan_label_rentang_analisis(): void
    {
        $this->seedRules();
        $this->seedThresholds();
        $service = new RuleBasedService;

        $hasil = $service->analisis(
            'Botol Ukuran 330 ml',
            50,
            25,
            5,
            '18–20 Agu 2026',
            '15–17 Agu 2026'
        );

        $this->assertStringContainsString(
            'Penjualan periode 18–20 Agu 2026 lebih besar dari periode 15–17 Agu 2026',
            $hasil['inference_trace']
        );
        $this->assertStringNotContainsString('minggu ini', $hasil['inference_trace']);
    }

    public function test_rule_seeder_cover_27_kombinasi(): void
    {
        $this->seedRules();
        $this->assertSame(27, Rule::count());
        $this->assertSame(27, Rule::TOTAL_KOMBINASI);

        // Tidak boleh ada premis null / "apa saja"
        $this->assertSame(0, Rule::whereNull('penjualan')->count());
        $this->assertSame(0, Rule::whereNull('stok')->count());
        $this->assertSame(0, Rule::whereNull('tren')->count());
    }

    public function test_sedang_aman_turun_punya_rule(): void
    {
        $this->seedRules();
        $this->seedThresholds();
        $service = new RuleBasedService;

        // Kasus bug user: 330ml jual 94 (Sedang), stok Aman, tren Turun
        $hasil = $service->analisis('Botol Ukuran 330 ml', 94, 120, 50);

        $this->assertSame('Sedang', $hasil['kondisi_penjualan']);
        $this->assertSame('Aman', $hasil['kondisi_stok']);
        $this->assertSame('Turun', $hasil['tren']);
        $this->assertSame('R15', $hasil['kode_rule']);
        $this->assertSame('Pantau Penjualan', $hasil['rekomendasi']);
        $this->assertNotSame(
            'Belum ada saran yang cocok. Periksa data penjualan atau atur ulang aturan.',
            $hasil['rekomendasi']
        );
    }

    public function test_semua_kombinasi_fakta_punya_rule_match(): void
    {
        $this->seedRules();
        $service = new RuleBasedService;

        foreach (Rule::PENJUALAN_OPTIONS as $penjualan) {
            foreach (Rule::STOK_OPTIONS as $stok) {
                foreach (Rule::TREN_OPTIONS as $tren) {
                    $hasil = $service->forwardChaining([
                        'penjualan' => $penjualan,
                        'stok' => $stok,
                        'tren' => $tren,
                    ]);
                    $this->assertNotNull(
                        $hasil['rule'],
                        "Tidak ada rule untuk {$penjualan}/{$stok}/{$tren}"
                    );
                }
            }
        }
    }

    public function test_null_tren_tidak_match_wildcard(): void
    {
        $this->seedRules();
        // Sisipkan rule rusak dengan tren null — tidak boleh fire.
        // Kode "R00" membuatnya dipindai paling awal (urutan First Match = kode_rule).
        Rule::create([
            'kode_rule' => 'R00',
            'penjualan' => 'Sedang',
            'stok' => 'Aman',
            'tren' => null,
            'rekomendasi' => 'Rule Rusak',
            'kategori' => 'pantau',
            'is_active' => true,
        ]);

        $service = new RuleBasedService;
        $hasil = $service->forwardChaining([
            'penjualan' => 'Sedang',
            'stok' => 'Aman',
            'tren' => 'Turun',
        ]);

        $this->assertNotNull($hasil['rule']);
        $this->assertNotSame('R00', $hasil['rule']->kode_rule);
        $this->assertSame('R15', $hasil['rule']->kode_rule);
    }

    public function test_evaluate_all_rules(): void
    {
        $this->seedRules();
        $service = new RuleBasedService;

        $rows = $service->evaluateAllRules([
            'penjualan' => 'Tinggi',
            'stok' => 'Sedikit',
            'tren' => 'Naik',
        ]);

        $matched = collect($rows)->firstWhere('kode_rule', 'R01');
        $this->assertTrue($matched['cocok']);
        $this->assertArrayNotHasKey('cf_hasil', $matched);
    }

    public function test_kondisi_penjualan_mengikuti_plan_md_per_minggu(): void
    {
        $this->seedThresholds();
        $service = new RuleBasedService;

        $cases = [
            // [nama, rendah_max, sedang_max]
            ['Botol Ukuran 330 ml', 80, 150],
            ['Botol Ukuran 600 ml', 400, 625],
            ['Botol Ukuran 1 Liter', 100, 200],
            ['Isi Ulang Air Galon Ukuran 19 Liter', 120, 200],
            ['Galon Ukuran 2 Liter', 40, 80],
        ];

        foreach ($cases as [$nama, $rendah, $sedang]) {
            $this->assertSame('Rendah', $service->getKondisiPenjualan($nama, 0), $nama);
            $this->assertSame('Rendah', $service->getKondisiPenjualan($nama, $rendah), $nama);
            $this->assertSame('Sedang', $service->getKondisiPenjualan($nama, $rendah + 1), $nama);
            $this->assertSame('Sedang', $service->getKondisiPenjualan($nama, $sedang), $nama);
            $this->assertSame('Tinggi', $service->getKondisiPenjualan($nama, $sedang + 1), $nama);
        }
    }

    public function test_kondisi_stok_label_selalu_kanonik(): void
    {
        $this->seedThresholds();

        $invalid = ProductThreshold::where('tipe', ProductThreshold::TIPE_STOK)
            ->whereNotIn('label', Rule::STOK_OPTIONS)
            ->count();

        $this->assertSame(0, $invalid, 'Label stok harus kanonik: Sedikit / Aman / Banyak');

        $byLevel = ProductThreshold::where('tipe', ProductThreshold::TIPE_STOK)
            ->get()
            ->groupBy('level');

        foreach ($byLevel[1] ?? [] as $row) {
            $this->assertSame('Sedikit', $row->label);
        }
        foreach ($byLevel[2] ?? [] as $row) {
            $this->assertSame('Aman', $row->label);
        }
        foreach ($byLevel[3] ?? [] as $row) {
            $this->assertSame('Banyak', $row->label);
        }
    }

    public function test_analisis_330ml_1_minggu_dengan_data_realistis(): void
    {
        $this->seedRules();
        $this->seedThresholds();
        $service = new RuleBasedService;

        // qty_week=50 (<=80 Rendah), prev=30, stok=5 (<=40 Sedikit), tren Naik → R19
        $h1 = $service->analisis('Botol Ukuran 330 ml', 50, 30, 5);
        $this->assertSame('Rendah', $h1['kondisi_penjualan']);
        $this->assertSame('Sedikit', $h1['kondisi_stok']);
        $this->assertSame('Naik', $h1['tren']);
        $this->assertSame('R19', $h1['kode_rule']);
        $this->assertNotSame(
            'Belum ada saran yang cocok. Periksa data penjualan atau atur ulang aturan.',
            $h1['rekomendasi']
        );

        // qty=200 (>150 Tinggi), prev=100, stok=5 → R01
        $h2 = $service->analisis('Botol Ukuran 330 ml', 200, 100, 5);
        $this->assertSame('R01', $h2['kode_rule']);
        $this->assertSame('Restock Prioritas', $h2['rekomendasi']);

        // qty=5, prev=5, stok=200 → R26 Promosi Agresif (Stabil)
        $h3 = $service->analisis('Botol Ukuran 330 ml', 5, 5, 200);
        $this->assertSame('R26', $h3['kode_rule']);
    }

    public function test_analisis_label_kondisi_sesuai_opsi_rule(): void
    {
        $this->seedRules();
        $this->seedThresholds();
        $service = new RuleBasedService;

        $samples = [
            [10, 5, 5],
            [100, 50, 50],
            [200, 100, 5],
            [5, 5, 200],
            [90, 90, 80],
        ];

        foreach ($samples as [$now, $prev, $stok]) {
            $hasil = $service->analisis('Botol Ukuran 330 ml', $now, $prev, $stok);
            $this->assertContains($hasil['kondisi_penjualan'], Rule::PENJUALAN_OPTIONS);
            $this->assertContains($hasil['kondisi_stok'], Rule::STOK_OPTIONS);
            $this->assertContains($hasil['tren'], Rule::TREN_OPTIONS);
        }
    }
}
