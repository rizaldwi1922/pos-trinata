<?php

namespace App\Http\Controllers\Inerta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentMethod;
use App\Models\{
    Store,
    Transaction,
    Receivable,
    ProductStock,
    ProductVariant,
    ProductUnit,
    IngredientStock,
    ProductIngredient,
    ProductIngredientRelation,
    ProductIngredientStock,
    Customer,
    JournalEntry,
    JournalDetail
};

class PosController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $categories = ProductCategory::all();
        $store = Store::find($user->store_id);
        $customers = Customer::where('store_id', $user->store_id)->get();
        $payment_methods = PaymentMethod::where('store_id', $user->store_id)->get();

        return Inertia::render('Pos', [
            'categories' => $categories,
            'store' => $store->name,
            'customers' => $customers,
            'payment_methods' => $payment_methods,
        ]);
    }

    public function getProduct(Request $request)
    {
        $user = auth()->user();

        // Ambil parameter dari query string
        $category_id = $request->query('category_id');
        $search = $request->query('search');
        $perPage = $request->query('perPage', 10); // default 10
        $page = $request->query('page', 1); // default page 1

        $query = DB::table('product_variants as pv')
            ->select([
                'pv.id as variant_id',
                'p.id as product_id',
                'pv.buy_price',
                'pv.sell_price',
                'pv.sell_retail_price',
                'pv.measurement',
                DB::raw('(SELECT SUM(amount_available) FROM product_stocks WHERE variant_id = pv.id AND (expired_at IS NULL OR expired_at > NOW())) as amount_available'),
                'p.name',
                'p.image',
                'pv.code',
                'u.name as unit_name',
            ])
            ->leftJoin('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('product_units as u', 'u.id', '=', 'pv.unit_id')
            ->where('p.store_id', $user->store_id)
            ->whereNull('p.buy_price'); // hanya produk dengan varian

        // 🔍 Filter berdasarkan kategori
        if (!empty($category_id)) {
            $query->where('p.category_id', $category_id);
        }

        // 🔍 Filter berdasarkan pencarian nama / kode
        if (!empty($search)) {
            $operator = env('DB_SEARCH_OPERATOR', 'like');
            $query->where(function ($q) use ($search, $operator) {
                $q->where('p.name', $operator, '%' . $search . '%')
                    ->orWhere('pv.code', $operator, '%' . $search . '%');
            });
        }

        // 📄 Urutkan berdasarkan nama
        $query->orderBy('p.name', 'ASC');

        // 🔢 Gunakan pagination manual untuk query string (supaya page dan perPage diikuti)
        $products = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($products);
    }

    public function submitPayment(Request $request)
    {
        $user = auth()->user();
        $store = Store::find($user->store_id);

        $request->validate([
            'transaction_type' => 'required|in:1,2', // 1 = tunai, 2 = kasbon
            'payment_method_id' => 'required|integer',
            'amount_received' => 'required|numeric|min:0',
            'list_product' => 'required|array|min:1',
            'list_product.*.product_id' => 'nullable|integer',
            'list_product.*.variant_id' => 'nullable|integer',
            'list_product.*.name' => 'required|string',
            'list_product.*.active_price' => 'required|numeric|min:0',
            'list_product.*.buy_price' => 'nullable|numeric|min:0',
            'list_product.*.amount' => 'required|numeric|min:1',
        ]);

        $transactionType = $request->transaction_type;
        $data_product = $request->list_product;

        // payload dasar
        $payload = [
            'store_id' => $user->store_id,
            'customer_id' => $request->customer_id ?: null,
            'payment_method_id' => $request->payment_method_id,
            'type' => $transactionType,
            'status' => $transactionType == 2 ? 0 : 1,
            'amount_profit' => 0,
            'total_items' => 0,
            'shift_id' => 1,
            'data' => [],
        ];

        // generate trx id
        $last = Transaction::where('store_id', $user->store_id)->latest('id')->first();
        $transaction_id = $last ? $last->id + 1 : 1;
        $payload['trx_id'] = ($transactionType == 1 ? 'TRX-' : 'PRD-') . str_pad($transaction_id, 8, '0', STR_PAD_LEFT);

        $total_price = 0;
        $is_success = true;
        $txt_error = '';

        DB::beginTransaction();

        try {
            foreach ($data_product as $p) {
                $payload['total_items'] += $p['amount'];
                $total_price += $p['active_price'] * $p['amount'];

                $buyPrice = (int)($p['buy_price'] ?? 0);
                $sellPrice = (int)$p['active_price'];

                $itemData = [
                    'product_id' => $p['product_id'],
                    'variant_id' => $p['variant_id'],
                    'name' => $p['name'],
                    'buy_price' => $buyPrice,
                    'sell_price' => $sellPrice,
                    'discount_type' => null,
                    'discount_value' => null,
                    'amount' => $p['amount'],
                    'amount_retur' => 0,
                    'grosir' => isset($p['priceType']) && $p['priceType'] === 'wholesale',
                ];

                $payload['data'][] = $itemData;

                // 🔹 Kurangi stok jika variant
                if (!empty($p['variant_id'])) {
                    $is_stock_debt = $p['amount'];
                    $availableStock = ProductStock::where('store_id', $user->store_id)
                        ->where('product_id', $p['product_id'])
                        ->where('variant_id', $p['variant_id'])
                        ->where('amount_available', '>', 0)
                        ->sum('amount_available');

                    if ($availableStock < $is_stock_debt) {
                        $is_success = false;
                        $txt_error = "Stok {$p['name']} tidak mencukupi";
                        break;
                    }

                    while ($is_stock_debt > 0) {
                        $stock = ProductStock::where('store_id', $user->store_id)
                            ->where('product_id', $p['product_id'])
                            ->where('variant_id', $p['variant_id'])
                            ->where('amount_available', '>', 0)
                            ->orderBy('created_at', 'ASC')
                            ->first();

                        if (!$stock) break;

                        $is_stock_debt -= $stock->amount_available;
                        if ($is_stock_debt > 0) {
                            $stock->amount_available = 0;
                        } else {
                            $stock->amount_available = abs($is_stock_debt);
                        }
                        $stock->save();
                    }

                    // log stok
                    DB::table('transaction_logs')->insert([
                        'store_id' => $user->store_id,
                        'transaction_id' => $transaction_id,
                        'product_id' => $p['product_id'],
                        'variant_id' => $p['variant_id'],
                        'amount_before' => $availableStock,
                        'amount_after' => max(0, $availableStock - $p['amount']),
                        'amount' => $p['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $payload['amount_profit'] += ($sellPrice - $buyPrice) * $p['amount'];
            }

            if (!$is_success) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $txt_error,
                ], 422);
            }

            // Total
            $payload['amount_received'] = (int)$request->amount_received;
            $payload['amount_total'] = $total_price;

            if ($payload['amount_received'] < $payload['amount_total'] && $transactionType == 1) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran kurang dari total. Gunakan kasbon jika ingin hutang.',
                ], 422);
            }

            // ✅ Hitung income & buat jurnal
            $countIncome = $this->countIncome($payload);
            $journal = $this->createJournal($countIncome, $payload, $user);
            $payload['journal_id'] = $journal->id;

            // Simpan transaksi
            $transaction = Transaction::create($payload);

            // Kasbon → buat receivable
            if ($transactionType == 2 && $payload['customer_id']) {
                Receivable::create([
                    'store_id' => $user->store_id,
                    'transaction_id' => $transaction->id,
                    'customer_id' => $payload['customer_id'],
                    'total_due' => $payload['amount_total'],
                ]);
            }

            DB::commit();

            $responData = [
                'store' => $store,
                'payload' => [
                    'amount_total' => $payload['amount_total'],
                    'data' => $payload['data']
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'transaction_id' => $transaction->id,
                'trx_code' => $payload['trx_id'],
                'data' => $responData,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // 🔽 Tambahkan fungsi buatanmu di bawah ini

    private function countIncome($payload)
    {
        $hppGrosir = 0;
        $hppEceran = 0;
        $incomeGrosir = 0;
        $incomeEceran = 0;

        foreach ($payload['data'] as $product) {
            $isGrosir = isset($product['grosir']) && $product['grosir'];

            if ($isGrosir) {
                $hppGrosir += $product['amount'] * $product['buy_price'];
                $incomeGrosir += $product['amount'] * $product['sell_price'];
            } else {
                $hppEceran += $product['amount'] * $product['buy_price'];
                $incomeEceran += $product['amount'] * $product['sell_price'];
            }
        }

        return [
            'grosir' => $hppGrosir,
            'income_grosir' => $incomeGrosir,
            'income_eceran' => $incomeEceran,
            'eceran' => $hppEceran,
        ];
    }

    private function createJournal($countIncome, $payload, $user)
    {
        $store_id = $user->store_id;

        $journalHead = JournalEntry::create([
            'store_id' => $store_id,
            'related_table' => 'transactions',
            'description' => 'Penjualan Barang ' . ($payload['type'] == 1 ? 'Lunas' : 'Kasbon'),
        ]);

        // Kas atau Piutang
        JournalDetail::create([
            'store_id' => $store_id,
            'journal_id' => $journalHead->id,
            'account_code' => $payload['type'] == 1 ? '1010' : '1020',
            'debit' => $payload['amount_total'],
        ]);

        if ($countIncome['income_eceran'] > 0) {
            JournalDetail::create([
                'store_id' => $store_id,
                'journal_id' => $journalHead->id,
                'account_code' => '4010',
                'credit' => $countIncome['income_eceran'],
            ]);
        }

        if ($countIncome['income_grosir'] > 0) {
            JournalDetail::create([
                'store_id' => $store_id,
                'journal_id' => $journalHead->id,
                'account_code' => '4020',
                'credit' => $countIncome['income_grosir'],
            ]);
        }

        if ($countIncome['eceran'] > 0) {
            JournalDetail::create([
                'store_id' => $store_id,
                'journal_id' => $journalHead->id,
                'account_code' => '5010',
                'debit' => $countIncome['eceran'],
            ]);
        }

        if ($countIncome['grosir'] > 0) {
            JournalDetail::create([
                'store_id' => $store_id,
                'journal_id' => $journalHead->id,
                'account_code' => '5020',
                'debit' => $countIncome['grosir'],
            ]);
        }

        // Persediaan keluar
        JournalDetail::create([
            'store_id' => $store_id,
            'journal_id' => $journalHead->id,
            'account_code' => '1030',
            'credit' => $countIncome['grosir'] + $countIncome['eceran'],
        ]);

        return $journalHead;
    }
}
