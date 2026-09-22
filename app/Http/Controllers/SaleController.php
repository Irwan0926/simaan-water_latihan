<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Services\SalesPeriodService;
use App\Support\AppTimezone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(
        private SalesPeriodService $periodService,
        private AppTimezone $timezone
    ) {}

    /**
     * Halaman Kasir
     */
    public function create()
    {
        $products = Product::all();

        return view('sales.create', compact('products'));
    }

    /**
     * Simpan Transaksi
     */
    public function store(Request $request)
    {
        $items = $request->input('items', []);

        if (! is_array($items) || $items === []) {
            return back()->with('error', 'Tidak ada produk yang dipilih untuk transaksi.');
        }

        DB::beginTransaction();

        try {

            // ===========================
            // Generate Nomor Invoice
            // ===========================

            $lastSale = Sale::latest()->first();

            if ($lastSale) {

                $nomor = (int) substr($lastSale->invoice, -4) + 1;

            } else {

                $nomor = 1;

            }

            // Prefiks invoice memakai tanggal LOKAL kasir supaya nomor
            // invoice tidak "pindah hari" saat malam (UTC vs WIB).
            $invoice = 'INV-'.
                $this->timezone->today()->format('Ymd').
                '-'.
                str_pad($nomor, 4, '0', STR_PAD_LEFT);

            // ===========================

            $sale = Sale::create([
                'invoice' => $invoice,
                'user_id' => auth()->id(),
                // Disimpan dalam UTC (config app.timezone = UTC).
                'tanggal' => now(),
                'total_harga' => 0,
            ]);

            $totalHarga = 0;

            foreach ($items as $item) {

                // Lewati jika qty kosong
                if (($item['qty'] ?? 0) <= 0) {
                    continue;
                }

                $product = Product::findOrFail(
                    $item['product_id']
                );

                // Cek stok
                if ($product->stok < $item['qty']) {

                    throw new \Exception(
                        "Stok {$product->nama_produk} tidak mencukupi."
                    );

                }

                $subtotal =
                    $product->harga *
                    $item['qty'];

                SaleDetail::create([

                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'harga' => $product->harga,
                    'subtotal' => $subtotal,

                ]);

                // Update stok

                $product->stok -= $item['qty'];

                $product->save();

                $totalHarga += $subtotal;

            }

            $sale->update([

                'total_harga' => $totalHarga,

            ]);

            DB::commit();

            return redirect()
                ->route('sales.create')
                ->with(
                    'success',
                    'Transaksi berhasil disimpan. Invoice : '.$invoice
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                $e->getMessage()
            );

        }

    }

    /**
     * Form edit transaksi milik pegawai yang sedang login.
     */
    public function edit(Request $request, Sale $sale)
    {
        $this->ensureOwner($request, $sale);
        $sale->load('details.product');

        $products = Product::orderBy('nama_produk')->get();
        $quantities = $products
            ->mapWithKeys(fn (Product $product) => [$product->id => 0])
            ->replace($sale->details->pluck('qty', 'product_id'));
        $prices = $products->pluck('harga', 'id')
            ->replace($sale->details->pluck('harga', 'product_id'));

        return view('sales.edit', compact('sale', 'products', 'quantities', 'prices'));
    }

    /**
     * Update jumlah item dan stok secara atomik.
     */
    public function update(Request $request, Sale $sale)
    {
        $this->ensureOwner($request, $sale);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            // Qty kosong/null dianggap 0 (produk tidak dibeli), bukan error.
            'items.*.qty' => ['nullable', 'integer', 'min:0'],
        ]);

        $requestedItems = collect($validated['items'])
            ->mapWithKeys(fn (array $item) => [(int) $item['product_id'] => (int) ($item['qty'] ?? 0)])
            ->filter(fn (int $qty) => $qty > 0);

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Pilih setidaknya satu produk untuk transaksi.',
            ]);
        }

        DB::beginTransaction();

        try {
            $sale->load('details');
            $oldItems = $sale->details->keyBy('product_id');
            $productIds = $oldItems->keys()->merge($requestedItems->keys())->unique();
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            $totalHarga = 0;

            foreach ($productIds as $productId) {
                $oldQty = (int) ($oldItems->get($productId)?->qty ?? 0);
                $newQty = (int) ($requestedItems->get($productId) ?? 0);
                $difference = $newQty - $oldQty;
                $product = $products->get($productId);

                if ($difference > 0 && $product->stok < $difference) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->nama_produk} tidak mencukupi.",
                    ]);
                }

                $product->stok -= $difference;
                $product->save();

                if ($newQty === 0) {
                    $oldItems->get($productId)?->delete();

                    continue;
                }

                $detail = $oldItems->get($productId);
                $harga = $detail?->harga ?? $product->harga;
                $subtotal = $harga * $newQty;

                if ($detail) {
                    $detail->update([
                        'qty' => $newQty,
                        'subtotal' => $subtotal,
                    ]);
                } else {
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productId,
                        'qty' => $newQty,
                        'harga' => $harga,
                        'subtotal' => $subtotal,
                    ]);
                }

                $totalHarga += $subtotal;
            }

            $sale->update(['total_harga' => $totalHarga]);
            DB::commit();

            return redirect()
                ->route('sales.history')
                ->with('success', 'Transaksi berhasil diperbarui.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Transaksi gagal diperbarui.');
        }
    }

    /**
     * Hapus transaksi dan kembalikan stok seluruh item.
     */
    public function destroy(Request $request, Sale $sale)
    {
        $this->ensureOwner($request, $sale);

        DB::transaction(function () use ($sale) {
            $sale->load('details');
            $products = Product::whereIn('id', $sale->details->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($sale->details as $detail) {
                $product = $products->get($detail->product_id);

                if ($product) {
                    $product->increment('stok', $detail->qty);
                }
            }

            $sale->delete();
        });

        return redirect()
            ->route('sales.history')
            ->with('success', 'Transaksi berhasil dihapus dan stok dikembalikan.');
    }

    /**
     * Riwayat transaksi.
     * - Pegawai: hanya transaksi miliknya sendiri.
     * - Admin   : seluruh transaksi (dengan filter kasir & periode opsional).
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $filter = $this->periodService->resolveFilter($request);
        $range = $this->periodService->resolveRange($filter);
        $labelPeriode = $range['label'];
        $rentangLabel = $range['rentang'];
        $periodeOptions = SalesPeriodService::PERIODE_OPTIONS;

        $kasirId = $request->get('kasir_id');

        $query = Sale::with(['user', 'details.product']);

        if ($user->isPegawai()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isAdmin()) {
            if (
                is_string($kasirId)
                && $kasirId !== ''
                && ctype_digit($kasirId)
            ) {
                $query->where('user_id', (int) $kasirId);
            }
        } else {
            abort(403);
        }

        $this->periodService->applyToQuery($query, $filter);

        $sales = $query->latest('tanggal')->get();

        $totalTransaksi = $sales->count();
        $totalPendapatan = $sales->sum('total_harga');
        $totalUnitTerjual = $this->sumQty($sales);

        $kasirList = $user->isAdmin()
            ? User::where('role', 'pegawai')
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        $selectedKasirId = ($user->isAdmin() && is_string($kasirId))
            ? $kasirId
            : '';

        return view('sales.history', compact(
            'sales',
            'totalTransaksi',
            'totalPendapatan',
            'totalUnitTerjual',
            'filter',
            'labelPeriode',
            'rentangLabel',
            'periodeOptions',
            'kasirList',
            'selectedKasirId'
        ));
    }

    private function ensureOwner(Request $request, Sale $sale): void
    {
        abort_unless(
            $request->user()?->isPegawai() && $sale->user_id === $request->user()->id,
            403
        );
    }

    private function sumQty(\Illuminate\Support\Collection $sales): int|float
    {
        if ($sales->isEmpty()) {
            return 0;
        }

        return (int) SaleDetail::whereIn('sale_id', $sales->pluck('id'))->sum('qty');
    }
}
