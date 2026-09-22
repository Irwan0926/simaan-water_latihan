<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductThreshold;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk
     */
    public function index()
    {
        $products = Product::latest()->get();

        $stokKondisiMap = [];
        $stokCounts = ['sedikit' => 0, 'cukup' => 0, 'banyak' => 0];
        foreach ($products as $product) {
            $kondisi = $this->kondisiStok($product);
            $stokKondisiMap[$product->id] = $kondisi;
            $stokCounts[$kondisi]++;
        }

        return view(
            'products.index',
            compact('products', 'stokCounts', 'stokKondisiMap')
        );
    }

    /**
     * Tentukan tujuan tombol "Kembali" pada halaman detail/ambang batas.
     * Halaman ini bisa dibuka dari daftar produk maupun dashboard,
     * jadi asal klik dibaca dari referer dan divalidasi ke rute internal.
     */
    private function backUrl(Request $request): string
    {
        $referer = $request->headers->get('referer');

        if ($referer) {
            $host = parse_url($referer, PHP_URL_HOST);

            if ($host === $request->getHost()) {
                $path = rtrim(parse_url($referer, PHP_URL_PATH) ?? '', '/');

                if ($path === '/dashboard') {
                    return route('dashboard');
                }
            }
        }

        return route('products.index');
    }

    /**
     * Tentukan kondisi stok produk memakai ambang batas DB per produk.
     * Mengembalikan kunci: 'sedikit' | 'cukup' | 'banyak'.
     * Dipakai juga view untuk badge & kartu ringkasan.
     */
    private function kondisiStok(Product $product): string
    {
        $thresholds = ProductThreshold::where('product_id', $product->id)
            ->where('tipe', ProductThreshold::TIPE_STOK)
            ->orderBy('level')
            ->pluck('batas', 'level');

        $sedikit = $thresholds->get(1);
        $cukup = $thresholds->get(2);

        if ($sedikit === null || $cukup === null) {
            // Fallback default bila threshold belum dibuat
            $sedikit = 40;
            $cukup = 100;
        }

        if ($product->stok <= $sedikit) {
            return 'sedikit';
        }

        if ($product->stok <= $cukup) {
            return 'cukup';
        }

        return 'banyak';
    }

    /**
     * Form tambah produk (halaman)
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Simpan produk
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
        ]);

        $product = Product::create([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        return redirect()
            ->route('products.show', $product)
            ->with(
                'success',
                'Produk berhasil ditambahkan.'
            );
    }

    /**
     * Detail produk + ambang batas kondisi (penjualan & stok).
     */
    public function show(Product $product, Request $request)
    {
        $product->load(['thresholds' => function ($q) {
            $q->orderBy('tipe')->orderBy('level');
        }]);

        $stokKondisi = $this->kondisiStok($product);

        $backUrl = $this->backUrl($request);
        $backLabel = $backUrl === route('dashboard') ? 'Dashboard' : 'Daftar Produk';

        return view('products.show', compact('product', 'stokKondisi', 'backUrl', 'backLabel'));
    }

    /**
     * Form edit produk (modal di halaman index/detail)
     */
    public function edit(Product $product)
    {
        return redirect()->route('products.index');
    }

    /**
     * Update produk
     */
    public function update(
        Request $request,
        Product $product
    ) {
        $request->validate([
            'nama_produk' => 'required',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
        ]);

        $product->update([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        return redirect()
            ->route('products.show', $product)
            ->with(
                'success',
                'Produk berhasil diperbarui.'
            );
    }

    /**
     * Hapus produk
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil dihapus.'
            );
    }

    /**
     * Simpan ambang batas kondisi (penjualan & stok) per produk.
     */
    public function updateThresholds(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'thresholds' => ['required', 'array'],
            'thresholds.penjualan' => ['required', 'array', 'size:3'],
            'thresholds.stok' => ['required', 'array', 'size:3'],
            'thresholds.penjualan.*.label' => ['required', 'string', 'max:30'],
            'thresholds.stok.*.label' => ['required', 'string', 'max:30'],
            'thresholds.penjualan.1.batas' => ['required', 'integer', 'min:0'],
            'thresholds.penjualan.2.batas' => ['required', 'integer', 'min:0'],
            'thresholds.stok.1.batas' => ['required', 'integer', 'min:0'],
            'thresholds.stok.2.batas' => ['required', 'integer', 'min:0'],
        ], [
            'thresholds.penjualan.*.label.required' => 'Label kondisi penjualan wajib diisi.',
            'thresholds.stok.*.label.required' => 'Label kondisi stok wajib diisi.',
        ]);

        $thresholds = $validated['thresholds'];

        foreach (['penjualan', 'stok'] as $tipe) {
            $batasRendah = (int) $thresholds[$tipe][1]['batas'];
            $batasSedang = (int) $thresholds[$tipe][2]['batas'];

            if ($batasSedang <= $batasRendah) {
                throw ValidationException::withMessages([
                    "thresholds.{$tipe}.2.batas" => 'Batas level sedang harus lebih besar dari batas level rendah.',
                ]);
            }

            foreach ([1, 2, 3] as $level) {
                $batas = $level == 3 ? null : (int) $thresholds[$tipe][$level]['batas'];

                ProductThreshold::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'tipe' => $tipe,
                        'level' => $level,
                    ],
                    [
                        'label' => $thresholds[$tipe][$level]['label'],
                        'batas' => $batas,
                    ]
                );
            }
        }

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Ambang batas kondisi berhasil diperbarui.');
    }
}
