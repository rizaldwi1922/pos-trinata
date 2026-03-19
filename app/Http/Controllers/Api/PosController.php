<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\ProductUnit;
use App\Models\ProductStock;
use App\Models\IngredientStock;
use App\Models\ProductIngredientStock;
use App\Models\ProductIngredientRelation;
use App\Models\Transaction;
use App\Models\Receivable;
use App\Models\Shift;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PosController extends Controller
{
    // ── GET CATEGORIES ──────────────────────────────────────
    public function getCategories(Request $request)
    {
        $user = $request->user();

        $categories = ProductCategory::where('store_id', $user->store_id)
            ->orderBy('name', 'ASC')
            ->get(['id', 'name']);

        return response()->json($categories);
    }

    // ── GET PRODUCTS ─────────────────────────────────────────
    public function getProducts(Request $request)
    {
        $user        = $request->user();
        $category_id = $request->query('category_id');
        $search      = $request->query('search');
        $page        = (int) $request->query('page', 1);
        $limit       = (int) $request->query('limit', 12);
        $offset      = ($page - 1) * $limit;

        $query = DB::table('product_variants as pv')
            ->select([
                'pv.id as variant_id',
                'p.id as product_id',
                'pv.buy_price',
                'pv.sell_price',
                'pv.sell_retail_price',
                'pv.measurement',
                DB::raw('(SELECT SUM(amount_available) FROM product_stocks
                          WHERE variant_id = pv.id
                          AND (expired_at IS NULL OR expired_at > NOW())) as amount_available'),
                'p.name',
                'p.image',
                'pv.code',
                'u.name as unit_name',
            ])
            ->where('p.store_id', $user->store_id)
            ->whereNull('p.buy_price')
            ->leftJoin('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('product_units as u', 'u.id', '=', 'pv.unit_id');

        if ($category_id) {
            $query->where('p.category_id', $category_id);
        }

        if ($search) {
            $op = env('DB_SEARCH_OPERATOR', 'LIKE');
            $query->where(function ($q) use ($search, $op) {
                $q->where('p.name', $op, '%' . $search . '%')
                    ->orWhere('pv.code', $op, '%' . $search . '%');
            });
        }

        // Union: ingredient-based products
        $union = DB::table('products as p')
            ->select([
                DB::raw('NULL as variant_id'),
                'p.id as product_id',
                'p.buy_price',
                'p.sell_price',
                DB::raw('NULL as sell_retail_price'),
                DB::raw('NULL as measurement'),
                DB::raw('(SELECT SUM(amount_available) FROM product_ingredient_stocks
                          WHERE product_id = p.id
                          AND (expired_at IS NULL OR expired_at > NOW())) as amount_available'),
                'p.name',
                'p.image',
                'p.code',
                DB::raw('NULL as unit_name'),
            ])
            ->where('p.store_id', $user->store_id)
            ->whereNotNull('p.buy_price');

        if ($category_id) {
            $union->where('p.category_id', $category_id);
        }
        if ($search) {
            $op = env('DB_SEARCH_OPERATOR', 'LIKE');
            $union->where(function ($q) use ($search, $op) {
                $q->where('p.name', $op, '%' . $search . '%')
                    ->orWhere('p.code', $op, '%' . $search . '%');
            });
        }

        $products = $query->union($union)
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->offset($offset)   // ← tambahkan ini
            ->get();

        // Generate signed image URLs
        foreach ($products as $prod) {
            if ($prod->image && !str_contains($prod->image, 'upload')) {
                $prod->image = Storage::disk('r2')->temporaryUrl(
                    $prod->image,
                    now()->addMinutes(5)
                );
            }
        }

        return response()->json($products);
    }

    // ── GET PAYMENT METHODS ──────────────────────────────────
    public function getPaymentMethods(Request $request)
    {
        $user    = $request->user();
        $methods = PaymentMethod::where('store_id', $user->store_id)
            ->orderBy('name', 'ASC')
            ->get(['id', 'name', 'is_cash', 'is_default']);

        return response()->json($methods);
    }

    // ── SUBMIT PAYMENT ────────────────────────────────────────
    public function submitPayment(Request $request)
    {
        $request->validate([
            'list_product'      => 'required|array|min:1',
            'payment_method_id' => 'required',
            'transaction_type'  => 'required|in:1,2',
            'amount_received'   => 'nullable|numeric',
            'customer_id'       => 'nullable',
            'discount_type'     => 'nullable|in:1,2',
            'discount_value'    => 'nullable|numeric',
        ]);

        $user             = $request->user();
        $data_product     = $request->list_product;
        $transaction_type = $request->transaction_type;
        $discount_type    = $request->discount_type;
        $discount_value   = (int) $request->discount_value;
        $amount_received  = (int) $request->amount_received;

        // Validate discount
        if (!empty($discount_type) && empty($discount_value)) {
            return response()->json(['message' => 'Nilai diskon tidak boleh kosong'], 422);
        }

        $shift = Shift::where('user_id', $user->id)
            ->whereNull('end_shift_at')
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Shift belum dimulai'], 422);
        }

        DB::beginTransaction();

        try {
            $last_trx     = Transaction::where('store_id', $user->store_id)->orderBy('id', 'DESC')->first();
            $trx_num      = $last_trx ? $last_trx->id + 1 : 1;
            $trx_prefix   = $transaction_type == 1 ? 'TRX-' : 'PRD-';
            $trx_id       = $trx_prefix . str_pad($trx_num, 8, '0', STR_PAD_LEFT);

            $total_price    = 0;
            $total_discount = 0;
            $total_items    = 0;
            $amount_profit  = 0;
            $payload_data   = [];

            foreach ($data_product as $i => $item) {
                $total_items   += $item['amount'];
                $total_price   += $item['active_price'] * $item['amount'];

                // Per-item discount
                if (!empty($item['discount_type']) && !empty($item['discount_value'])) {
                    $d = $item['discount_type'] == '1'
                        ? ($item['active_price'] * $item['discount_value']) / 100
                        : $item['discount_value'];
                    $total_discount += $d;
                }

                $dStock = [];

                // ── Variant product ───────────────────────────────────
                if (!empty($item['variant_id'])) {
                    $variant    = ProductVariant::find($item['variant_id']);
                    $unit       = ProductUnit::find($variant->unit_id);
                    $name       = $item['name'] . ' (' . $variant->measurement . ' ' . $unit->name . ')';
                    $is_debt    = $item['amount'];
                    $available  = ProductStock::where('store_id', $user->store_id)
                        ->where('variant_id', $item['variant_id'])
                        ->where('amount_available', '>', 0)
                        ->where(fn($q) => $q->whereNull('expired_at')->orWhere('expired_at', '>', now()))
                        ->sum('amount_available');

                    if ($available < $is_debt) {
                        throw new \Exception('Stock ' . $name . ' tidak mencukupi');
                    }

                    $dStock = ['amount_before' => $available, 'amount_after' => $available - $is_debt];

                    // Deduct stock FIFO
                    while ($is_debt > 0) {
                        $stock = ProductStock::where('store_id', $user->store_id)
                            ->where('variant_id', $item['variant_id'])
                            ->where('amount_available', '>', 0)
                            ->where(fn($q) => $q->whereNull('expired_at')->orWhere('expired_at', '>', now()))
                            ->orderBy('created_at', 'ASC')
                            ->lockForUpdate()
                            ->first();

                        if (!$stock) throw new \Exception('Stock ' . $name . ' tidak mencukupi');

                        $unit_price   = (int) str_replace('.', '', number_format($stock->unit_price, 0, ',', '.'));
                        $is_debt     -= $stock->amount_available;
                        $stock->amount_available = $is_debt > 0 ? 0 : abs($is_debt);
                        $stock->save();
                    }

                    $active_price = (int) $item['active_price'];
                    $payload_data[] = [
                        'product_id'     => $item['product_id'],
                        'variant_id'     => $item['variant_id'],
                        'name'           => $name,
                        'buy_price'      => $unit_price ?? 0,
                        'sell_price'     => $active_price,
                        'discount_type'  => $item['discount_type'] ?? null,
                        'discount_value' => $item['discount_value'] ?? null,
                        'amount'         => $item['amount'],
                        'amount_retur'   => 0,
                        'grosir'         => $item['sell_price'] != $active_price,
                    ];

                    $amount_profit += ($active_price - ($unit_price ?? 0)) * $item['amount'];

                    // ── Regular/ingredient product ────────────────────────
                } else {
                    $amount_item = $item['amount'];

                    $payload_data[] = [
                        'product_id'     => $item['product_id'],
                        'variant_id'     => null,
                        'name'           => $item['name'],
                        'buy_price'      => $item['buy_price'],
                        'sell_price'     => $item['active_price'],
                        'discount_type'  => $item['discount_type'] ?? null,
                        'discount_value' => $item['discount_value'] ?? null,
                        'amount'         => $amount_item,
                        'amount_retur'   => 0,
                    ];

                    $amount_profit += ($item['active_price'] - $item['buy_price']) * $amount_item;

                    // Deduct product ingredient stock
                    if ($transaction_type == 1) {
                        $is_debt = $amount_item;
                        while ($is_debt > 0) {
                            $stock = ProductIngredientStock::where('store_id', $user->store_id)
                                ->where('product_id', $item['product_id'])
                                ->where('amount_available', '>', 0)
                                ->where(fn($q) => $q->whereNull('expired_at')->orWhere('expired_at', '>', now()))
                                ->orderBy('expired_at', 'ASC')
                                ->lockForUpdate()->first();
                            if (!$stock) break;
                            $is_debt -= $stock->amount_available;
                            $stock->amount_available = $is_debt > 0 ? 0 : abs($is_debt);
                            $stock->save();
                        }
                    }
                }

                // Log
                DB::table('transaction_logs')->insert([
                    'store_id'      => $user->store_id,
                    'transaction_id' => $trx_num,
                    'product_id'    => $item['product_id'] ?? null,
                    'variant_id'    => $item['variant_id'] ?? null,
                    'ingredient_id' => $item['ingredient_id'] ?? null,
                    'amount_before' => $dStock['amount_before'] ?? 0,
                    'amount_after'  => $dStock['amount_after'] ?? 0,
                    'amount'        => $item['amount'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Global discount
            if (!empty($discount_type) && !empty($discount_value)) {
                $d = $discount_type == '1'
                    ? ($total_price * $discount_value) / 100
                    : $discount_value;
                $total_discount += $d;
            }

            $amount_total = $total_price - $total_discount;

            // Validate payment
            if (empty($data_product)) {
                throw new \Exception('Item pembelian tidak boleh kosong');
            }
            if ($amount_received < $amount_total) {
                if (empty($request->customer_id)) {
                    throw new \Exception('Pelanggan tidak boleh kosong jika pembayaran kurang dari total');
                }
                if ($transaction_type == 1) {
                    throw new \Exception('Hutang hanya bisa dilakukan untuk transaksi kasbon');
                }
            }

            $amount_less = max(0, $amount_total - $amount_received);

            // Build final payload
            $trx_payload = [
                'store_id'          => $user->store_id,
                'shift_id'          => $shift->id,
                'customer_id'       => $request->customer_id ?? null,
                'payment_method_id' => $request->payment_method_id,
                'type'              => $transaction_type,
                'status'            => $transaction_type == 2 ? 0 : 1,
                'trx_id'            => $trx_id,
                'total_items'       => $total_items,
                'amount_total'      => $amount_total,
                'amount_received'   => $amount_received,
                'amount_discount'   => $total_discount,
                'amount_profit'     => $amount_profit - $total_discount,
                'amount_less'       => $amount_less,
                'data'              => $payload_data,
            ];

            // Journal
            $countIncome = $this->countIncome($payload_data);
            $journal     = $this->createJournal($countIncome, $trx_payload, $user->store_id);
            $trx_payload['journal_id'] = $journal->id;

            $transaction = Transaction::create($trx_payload);

            if ($transaction_type == 2) {
                Receivable::create([
                    'store_id'       => $user->store_id,
                    'transaction_id' => $transaction->id,
                    'customer_id'    => $request->customer_id,
                    'total_due'      => $amount_total,
                ]);
            }

            DB::commit();

            return response()->json([
                'message'        => 'Transaksi berhasil',
                'transaction_id' => $transaction->id,
                'trx_id'         => $trx_id,
                'amount_total'   => $amount_total,
                'amount_change'  => max(0, $amount_received - $amount_total),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ── HISTORY TRANSACTIONS ─────────────────────────────────
    public function getHistoryTransaction(Request $request)
    {
        $user = $request->user();

        $transactions = DB::table('transactions as ts')
            ->select(['ts.*', 'cs.name as customer_name'])
            ->where('ts.store_id', $user->store_id)
            ->leftJoin('customers as cs', 'ts.customer_id', '=', 'cs.id')
            ->orderBy('ts.id', 'DESC')
            ->limit(20)
            ->get();

        return response()->json($transactions);
    }

    public function searchTransaction(Request $request)
    {
        $user  = $request->user();
        $value = $request->query('q');

        $transactions = DB::table('transactions as ts')
            ->select(['ts.*', 'cs.name as customer_name'])
            ->where('ts.store_id', $user->store_id)
            ->when($value, function ($q) use ($value) {
                $op = env('DB_SEARCH_OPERATOR', 'LIKE');
                $q->where('ts.trx_id', $op, '%' . $value . '%')
                    ->orWhere('cs.name', $op, '%' . $value . '%');
            })
            ->leftJoin('customers as cs', 'ts.customer_id', '=', 'cs.id')
            ->orderBy('ts.id', 'DESC')
            ->limit(20)
            ->get();

        return response()->json($transactions);
    }

    // ── SHIFT ─────────────────────────────────────────────────
    public function getCurrentShift(Request $request)
    {
        $shift = Shift::where('user_id', $request->user()->id)
            ->whereNull('end_shift_at')
            ->first();

        return response()->json($shift);
    }

    public function startShift(Request $request)
    {
        $request->validate(['amount' => 'required|numeric']);
        $user = $request->user();

        $shift = Shift::create([
            'user_id'       => $user->id,
            'store_id'      => $user->store_id,
            'start_shift_at' => now(),
            'amount_start'  => (int) $request->amount,
        ]);

        return response()->json(['message' => 'Shift dimulai', 'shift' => $shift]);
    }

    public function endShift(Request $request)
    {
        $request->validate(['amount' => 'required|numeric']);
        $user  = $request->user();
        $shift = Shift::where('user_id', $user->id)->whereNull('end_shift_at')->first();

        if (!$shift) {
            return response()->json(['message' => 'Tidak ada shift aktif'], 422);
        }

        $shift->update([
            'end_shift_at' => now(),
            'amount_end'   => (int) $request->amount,
            'amount_total' => (int) $request->amount - $shift->amount_start,
        ]);

        return response()->json(['message' => 'Shift selesai', 'shift' => $shift]);
    }

    // ── HELPERS ───────────────────────────────────────────────
    private function countIncome(array $data): array
    {
        $hppG = $hppE = $incG = $incE = 0;
        foreach ($data as $p) {
            $isGrosir = $p['grosir'] ?? false;
            if ($isGrosir) {
                $hppG += $p['amount'] * $p['buy_price'];
                $incG += $p['amount'] * $p['sell_price'];
            } else {
                $hppE += $p['amount'] * $p['buy_price'];
                $incE += $p['amount'] * $p['sell_price'];
            }
        }
        return ['grosir' => $hppG, 'eceran' => $hppE, 'income_grosir' => $incG, 'income_eceran' => $incE];
    }

    private function createJournal(array $income, array $payload, int $store_id): JournalEntry
    {
        $head = JournalEntry::create([
            'store_id'      => $store_id,
            'related_table' => 'transactions',
            'description'   => 'Penjualan Barang ' . ($payload['type'] == 1 ? 'Lunas' : 'Kasbon'),
        ]);

        $accountDebit = $payload['type'] == 1 ? '1010' : '1020';
        JournalDetail::create(['store_id' => $store_id, 'journal_id' => $head->id, 'account_code' => $accountDebit, 'debit' => $payload['amount_total']]);

        if ($income['income_eceran'] > 0) JournalDetail::create(['store_id' => $store_id, 'journal_id' => $head->id, 'account_code' => '4010', 'credit' => $income['income_eceran']]);
        if ($income['income_grosir'] > 0) JournalDetail::create(['store_id' => $store_id, 'journal_id' => $head->id, 'account_code' => '4020', 'credit' => $income['income_grosir']]);
        if ($income['eceran'] > 0) JournalDetail::create(['store_id' => $store_id, 'journal_id' => $head->id, 'account_code' => '5010', 'debit' => $income['eceran']]);
        if ($income['grosir'] > 0) JournalDetail::create(['store_id' => $store_id, 'journal_id' => $head->id, 'account_code' => '5020', 'debit' => $income['grosir']]);
        JournalDetail::create(['store_id' => $store_id, 'journal_id' => $head->id, 'account_code' => '1030', 'credit' => $income['grosir'] + $income['eceran']]);

        return $head;
    }
}
