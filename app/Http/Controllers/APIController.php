<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductIngredientRelation;
use App\Models\ProductIngredientStock;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class APIController extends Controller
{
    /**
     * API response
     * $status
     * $message
     * $data
     */
    public function apiResponse($status = 200, $message = null, $data = [], $pagination = null)
    {
        $res = [
            'status' => $status,
            'message' => $status == 200 ? ($message ?? 'Success') : ($message ?? 'Failed'),
            'data' => $data,
        ];

        $json = array_merge($res, $pagination ? ['pagination' => $pagination] : []);

        return response()->json($json);
    }

    /**
     * API validation
     * $request
     * $rules
     */
    public function apiValidation($request, $rules)
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return abort(self::apiResponse(400, $validator->errors()->first(), null));
        }
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        DB::beginTransaction();
    }

    /**
     * Check for commit transaction
     * $p_err
     */
    public function commitTransaction($p_err)
    {
        if (!$p_err) {
            DB::commit();
        } else {
            DB::rollback();
        }
    }

    /**
     * Validate data
     * $data
     */
    public function validateData($data, $upload_columns = [])
    {
        // if data is not array, then convert to array
        $temp = [];
        if (!is_array($data)) {
            $temp['data'] = [$data];

            // set data to temp
            $data = $temp;
            unset($temp);
        }

        foreach ($data['data'] as $obj) {
            foreach ($obj as $key => $value) {
                // if value is numeric, then convert to integer
                if (is_numeric($value)) {
                    $obj->$key = (int) $value;
                }

                if (in_array(($key . '::public'), $upload_columns) || in_array(($key . '::private'), $upload_columns)) {
                    // if value has https or http, then skip
                    if (empty($value)) {
                        $obj->$key = asset('assets/app/pos/images/product-default.png');
                        continue;
                    }

                    if (strpos($value, 'http://') !== false || strpos($value, 'https://') !== false) {
                        continue;
                    }


                    // get type
                    $type = in_array(($key . '::public'), $upload_columns) ? 'public' : 'private';

                    // convert to file url
                    $path = explode('/', $value);
                    $file = end($path);
                    array_pop($path);
                    $path = implode('/', $path);

                    // get file url
                    $url = route('web.view.file', [
                        'filename' => $file,
                    ]) . '?type=' . $type . '&path=' . urlencode($path);

                    // add temp key for public file
                    if ($type == 'public') {
                        $url .= '&temp_key=' . encrypt(md5($path));
                    }

                    $obj->$key = $url;
                }
            }
        }

        return $data;
    }

    /**
     * Get products
     * $request->page (required)
     * $request->limit (required)
     * $request->store_id (required)
     * $request->search (optional)
     * $request->category_id (optional)
     */
    public function getProducts(Request $request)
    {
        $this->apiValidation($request, [
            'store_id' => 'required',
            'page' => 'required',
            'limit' => 'required',
        ]);

        $search = $request->search;
        $store_id = $request->store_id;
        $page = $request->page;
        $limit = $request->limit;

        // query product variant
        $products = DB::table('products')
            ->select(
                'products.id',
                'products.name',
                'products.image',
                'product_variants.id as variant_id',
                'product_variants.sell_price as price',
                'product_variants.sell_retail_price as retail_price',
                'product_categories.id as category_id',
                'product_categories.name as category',
                'product_units.id as unit_id',
                'product_units.name as unit',
                DB::raw('COALESCE((SELECT SUM(amount_available) FROM product_stocks WHERE variant_id = product_variants.id AND (expired_at IS NULL OR expired_at > NOW())), 0) as stock'),
            )
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('product_stocks', 'product_variants.id', '=', 'product_stocks.variant_id')
            ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
            ->where('products.store_id', $store_id);

        if ($search) {
            $products->where('products.name', env('DB_SEARCH_OPERATOR'), "%$search%");
        }

        if ($request->category_id) {
            $products->where('products.category_id', $request->category_id);
        }

        $categoryId = $request->category_id;

        // query for product ingredient
        $products->union(
            DB::table('products as p')
                ->select([
                    'p.id',
                    'p.name',
                    'p.image',
                    DB::raw('NULL as variant_id'),
                    'p.sell_price as price',
                    DB::raw('NULL as retail_price'),
                    'product_categories.id as category_id',
                    'product_categories.name as category',
                    DB::raw('NULL as unit_id'),
                    DB::raw('NULL as unit'),
                    DB::raw('NULL as stock'),

                ])
                ->join('product_categories', 'p.category_id', '=', 'product_categories.id')
                ->where('p.store_id', $store_id)
                ->where('p.buy_price', '!=', null)
                ->where(function ($query) use ($categoryId, $search) {
                    if ($categoryId != null) {
                        $query->where('p.category_id', $categoryId);
                    }

                    if ($search != null) {
                        $query->where('p.name', env('DB_SEARCH_OPERATOR'), '%' . $search . '%');
                    }
                })
        );

        $products = $products->groupBy([
            'products.id',
            'products.name',
            'products.image',
            'product_variants.id',
            'product_variants.sell_price',
            'product_variants.sell_retail_price',
            'product_categories.id',
            'product_categories.name',
            'product_units.id',
            'product_units.name',
        ]);

        $products = self::validateData($products->paginate($limit, ['*'], 'page', $page)->toArray(), ['image::public']);

        return self::apiResponse(data: $products['data'], pagination: [
            'page' => (int) $page,
            'limit' => (int) $limit,
            'total_pages' => $products['last_page'],
            'is_last_page' => $products['last_page'] == $page,
        ]);
    }

    /**
     * Get product by barcode
     * $request->store_id (required)
     * $request->barcode (required)
     */
    public function getProductByBarcode(Request $request)
    {
        $this->apiValidation($request, [
            'store_id' => 'required',
            'barcode' => 'required',
        ]);

        $barcode = $request->barcode;

        $type = null;
        if (Product::where('code', $barcode)->exists()) {
            $type = 1;
        }
        if (ProductVariant::where('code', $barcode)->exists()) {
            $type = 2;
        }

        if (!$type) {
            return self::apiResponse(400, 'Produk tidak ditemukan', null);
        }

        $query = null;
        if ($type == 2) {
            $query = DB::table('products')
                ->select(
                    'products.id',
                    'products.name',
                    'products.image',
                    'product_categories.id as category_id',
                    'product_categories.name as category',
                    'product_units.id as unit_id',
                    'product_units.name as unit',
                    'product_variants.id as variant_id',
                    'product_variants.sell_price as price',
                    'product_variants.sell_retail_price as retail_price',
                    DB::raw('COALESCE((SELECT SUM(amount_available) FROM product_stocks WHERE variant_id = product_variants.id AND (expired_at IS NULL OR expired_at > NOW())), 0) as stock'),
                )
                ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
                ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
                ->join('product_stocks', 'product_variants.id', '=', 'product_stocks.variant_id')
                ->where('product_variants.code', $barcode);
        } else {
            $query = DB::table('products')
                ->select([
                    'products.id',
                    'products.name',
                    'products.image',
                    DB::raw('NULL as variant_id'),
                    'products.sell_price as price',
                    DB::raw('NULL as retail_price'),
                    'product_categories.id as category_id',
                    'product_categories.name as category',
                    DB::raw('NULL as unit_id'),
                    DB::raw('NULL as unit'),
                    DB::raw('NULL as stock'),

                ])
                ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
                ->where('products.code', $barcode);
        }

        $product = $query->first();

        $data = self::validateData($product, ['image::public']);

        return self::apiResponse(data: $data['data'][0]);
    }

    /**
     * Get transactions
     * $request->store_id (required)
     * $request->shift_id (required)
     * $request->page (required)
     * $request->limit (required)
     * $request->search (optional)
     */

    public function getTransactions(Request $request)
    {
        $this->apiValidation($request, [
            'store_id' => 'required',
            'shift_id' => 'required',
            'page' => 'required',
            'limit' => 'required',
        ]);

        $search = $request->search;
        $store_id = $request->store_id;
        $shift_id = $request->shift_id;
        $page = $request->page;
        $limit = $request->limit;

        $transactions = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.trx_id',
                'transactions.amount_total',
                'transactions.created_at',
                'customers.id as customer_id',
                'customers.name as customer',
                'payment_methods.id as payment_method_id',
                'payment_methods.name as payment_method',
            )
            ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
            ->leftJoin('customers', 'transactions.customer_id', '=', 'customers.id')
            ->where(['transactions.store_id' => $store_id, 'transactions.shift_id' => $shift_id]);

        if ($search) {
            $transactions->where(function ($query) use ($search) {
                $query->where('customers.name', env('DB_SEARCH_OPERATOR'), "%$search%")
                    ->orWhere('transactions.trx_id', env('DB_SEARCH_OPERATOR'), "%$search%");
            });
        }

        $transactions->orderBy('transactions.created_at', 'desc');

        $transactions = self::validateData($transactions->paginate($limit, ['*'], 'page', $page)->toArray());

        foreach ($transactions['data'] as $key => $transaction) {
            $transactions['data'][$key]->created_at = date('d M Y H:i', strtotime($transaction->created_at)) . ' WIB';
            $transactions['data'][$key]->html = view('admin.transactions.print', [
                'store' => Store::find($store_id),
                'transaction' => Transaction::find($transaction->id),
                'settings' => Setting::whereIn('key', ['receipt_logo', 'receipt_logo_image', 'receipt_logo_size'])->where('store_id', $store_id)->pluck('value', 'key')->toArray(),
                'from' => 'api',
            ])->render();
        }

        return self::apiResponse(data: $transactions['data'], pagination: [
            'page' => (int) $page,
            'limit' => (int) $limit,
            'total_pages' => $transactions['last_page'],
            'is_last_page' => $transactions['last_page'] == $page,
        ]);
    }

    /**
     * Post login
     * $request->email (required)
     * $request->password (required)
     */
    public function postLogin(Request $request)
    {
        $this->apiValidation($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        $user = DB::table('users')
            ->select('id as user_id', 'store_id as user_store_id', 'name as user_name', 'email as user_email', 'password')
            ->where('email', $email)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return self::apiResponse(400, 'Email atau password salah', null);
        }

        unset($user->password);

        $shift = DB::table('shifts')
            ->select('id as shift_id', 'user_id', 'start_shift_at', 'end_shift_at')
            ->where(['user_id' => $user->user_id])
            ->whereNull('end_shift_at')
            ->first();

        $user->shift_id = $shift ? $shift->shift_id : null;

        return self::apiResponse(data: $user);
    }

    /**
     * Post pos start shift
     * $request->store_id (required)
     * $request->user_id (required)
     */
    public function postPOSStartShift(Request $request)
    {
        $this->apiValidation($request, [
            'store_id' => 'required',
            'user_id' => 'required',
            'balance' => 'nullable',
        ]);

        $store_id = $request->store_id;
        $user_id = $request->user_id;

        $shift = DB::table('shifts')
            ->select('id as shift_id', 'user_id', 'start_shift_at', 'end_shift_at')
            ->where(['user_id' => $user_id])
            ->whereNull('end_shift_at')
            ->first();

        if ($shift) {
            return self::apiResponse(400, 'Shift sudah dimulai', null);
        }

        $shiftId = DB::table('shifts')
            ->insertGetId([
                'store_id' => $store_id,
                'user_id' => $user_id,
                'amount_start' => $request->balance ?? 0,
                'start_shift_at' => now(),
            ]);

        return self::apiResponse(message: 'Shift berhasil dimulai', data: ['shift_id' => $shiftId]);
    }

    /**
     * Post pos end shift
     * $request->shift_id (required)
     * $request->balance (required)
     */
    public function postPOSEndShift(Request $request)
    {
        $this->apiValidation($request, [
            'shift_id' => 'required',
            'balance' => 'required',
        ]);

        $shift_id = $request->shift_id;
        $balance = $request->balance;

        $shift = DB::table('shifts')
            ->select('id as shift_id', 'user_id', 'amount_start', 'amount_end', 'end_shift_at')
            ->where(['id' => $shift_id])
            ->whereNull('end_shift_at')
            ->first();

        if (!$shift) {
            return self::apiResponse(400, 'Shift tidak ditemukan', null);
        }

        DB::table('shifts')
            ->where('id', $shift_id)
            ->update([
                'amount_end' => $balance,
                'amount_total' => $balance - $shift->amount_start,
                'end_shift_at' => now(),
            ]);

        return self::apiResponse(message: 'Shift berhasil diakhiri');
    }

    /**
     * Get checkout data
     * $request->store_id (required)
     */
    public function getCheckoutData(Request $request)
    {
        $this->apiValidation($request, [
            'store_id' => 'required',
        ]);

        $store_id = $request->store_id;

        $store = Store::find($store_id);

        if (!$store) {
            return self::apiResponse(400, 'Toko tidak ditemukan', null);
        }

        $payment_methods = DB::table('payment_methods')
            ->select('id', 'name')
            ->where('store_id', $store_id)
            ->get();

        $customers = DB::table('customers')
            ->select('id', 'name')
            ->where('store_id', $store_id)
            ->get();

        $response = [
            'store' => $store,
            'payment_methods' => $payment_methods,
            'customers' => $customers,
        ];

        return self::apiResponse(data: $response);
    }

    /**
     * Post checkout
     * $request->store_id (required)
     * $request->shift_id (required)
     * $request->payment_method_id (required)
     * $request->product_ids (required)
     * $request->variant_ids (optional)
     * $request->amounts (required)
     * $request->amount_received (required)
     * $request->customer_id (optional)
     */
    public function postCheckout(Request $request)
    {
        $shift = DB::table('shifts')
            ->where([
                'id' => $request->shift_id,
                'store_id' => $request->store_id,
            ])
            ->first();

        if (!$shift) {
            return self::apiResponse(400, 'Shift tidak ditemukan', null);
        }

        $payload['store_id'] = $shift->store_id;
        $payload['shift_id'] = $shift->id;
        $payload['customer_id'] = $request->customer_id ?? null;
        $payload['payment_method_id'] = $request->payment_method_id;
        $payload['amount_profit'] = 0;
        $last_transction = Transaction::where('store_id', $shift->store_id)->orderBy('id', 'DESC')->first();
        $transaction_id = $last_transction ? $last_transction->id + 1 : 1;
        $payload['trx_id'] = "TRX-" . str_pad($transaction_id, 8, "0", STR_PAD_LEFT);
        $payload['total_items'] = 0;

        $a_productids = explode(',', $request->product_ids);
        $a_variantids = explode(',', $request->variant_ids);
        $a_amounts = explode(',', $request->amounts);

        // remove empty data
        $a_productids = array_filter($a_productids);
        $a_variantids = array_filter($a_variantids);
        $a_amounts = array_filter($a_amounts);

        $a_ids = [];

        // get product ids
        foreach ($a_variantids as $key => $variantId) {
            if ($variantId == 'null') {
                $a_ids[] = $a_productids[$key] . '::';
            } else {
                $a_ids[] = $a_variantids[$key];
            }
        }

        // FIXME: unsupport productids
        // foreach ($a_amounts as $key => $amount) {
        //     if (empty($amount)) {
        //         unset($a_amounts[$key]);
        //     }

        //     if (empty($a_variantids[$key])) {
        //         unset($a_variantids[$key]);
        //     }
        // }

        // if (count($a_variantids) != count($a_amounts)) {
        //     return self::apiResponse(400, 'Jumlah payload varian dan amount harus sama', null);
        // }

        $a_data = [];
        foreach ($a_ids as $id) {
            $type = strpos($id, '::') ? 1 : 2;

            $data = [];
            if ($type == 1) {
                $a_id = explode('::', $id);
                $product = Product::find($a_id[0]);

                $data['product_id'] = $product->id;
                $data['variant_id'] = null;
                $data['name'] = $product->name;
                $data['buy_price'] = $product->buy_price;
                $data['sell_price'] = $product->sell_price;
                $data['amount_retur'] = 0;
            } else {
                $variant = ProductVariant::find($id);

                $product = Product::find($variant->product_id);

                $unit = DB::table('product_units')
                    ->where('id', $variant->unit_id)
                    ->first();

                $data['product_id'] = $product->id;
                $data['variant_id'] = $variant->id;
                $data['name'] = $product->name . ' (' . $unit->name . ')';
                $data['buy_price'] = $variant->buy_price;
                $data['sell_price'] = $variant->sell_price;
                $data['amount_retur'] = 0;
            }

            $a_data[] = $data;
        }

        foreach ($a_data as $key => $obj) {
            $a_data[$key]['amount'] = (int) $a_amounts[$key];
        }

        DB::beginTransaction();

        $is_success = true;
        $txt_error = '';

        $total_price = 0;
        for ($i = 0; $i < sizeof($a_data); $i++) {
            $payload['data'][$i] = [
                'product_id' => $a_data[$i]['product_id'],
                'variant_id' => $a_data[$i]['variant_id'],
                'name' => $a_data[$i]['name'],
                'buy_price' => $a_data[$i]['buy_price'],
                'sell_price' => $a_data[$i]['sell_price'],
                'amount' => $a_data[$i]['amount'],
                'amount_retur' => 0,
            ];

            $amount_item = $a_data[$i]['amount'];

            $payload['total_items'] += $amount_item;

            $total_price += $a_data[$i]['sell_price'] * $a_data[$i]['amount'];

            $dStock = [];
            if (!empty($a_data[$i]['variant_id'])) {
                $is_stock_debt = $a_data[$i]['amount'];
                $availableStock = ProductStock::where('store_id', $shift->store_id)
                    ->where('product_id', $a_data[$i]['product_id'])
                    ->where('variant_id', $a_data[$i]['variant_id'])
                    ->where('amount_available', '>', 0)
                    ->where(function ($query) {
                        $query->whereNull('expired_at')
                            ->orWhere('expired_at', '>', now());
                    })
                    ->sum('amount_available');

                $dStock['amount_before'] = $availableStock;
                $dStock['amount_after'] = $availableStock - $is_stock_debt;
                while ($is_stock_debt > 0) {
                    $stock = ProductStock::where('store_id', $shift->store_id)
                        ->where('product_id', $a_data[$i]['product_id'])
                        ->where('variant_id', $a_data[$i]['variant_id'])
                        ->where('amount_available', '>', 0)
                        ->where(function ($query) {
                            $query->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                        })
                        ->orderBy('expired_at', 'ASC')
                        ->first();

                    if (!$stock) {
                        $is_success = false;
                        $txt_error = 'Stock ' . $a_data[$i]['name'] . ' tidak mencukupi';
                        break;
                    }

                    $is_stock_debt -= $stock->amount_available;

                    if ($is_stock_debt > 0) {
                        $stock->amount_available = 0;
                        $stock->update();
                    } else {
                        $stock->amount_available = abs($is_stock_debt);
                        $stock->update();
                    }
                }

                // transactions logs
                DB::table('transaction_logs')->insert([
                    'store_id' => $shift->store_id,
                    'transaction_id' => $transaction_id,
                    'product_id' => $a_data[$i]['product_id'],
                    'variant_id' => $a_data[$i]['variant_id'],
                    'amount_before' => $dStock['amount_before'] ?? 0,
                    'amount_after' => $dStock['amount_after'] ?? 0,
                    'item_amount' => $amount_item,
                    'amount' => $a_data[$i]['amount'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // get ingredient relations
                $ingredientRelations = ProductIngredientRelation::where('product_id', $a_data[$i]['product_id'])->get()->map(function ($item) {
                    $obj = new \stdClass();
                    $obj->id = $item->ingredient_id;
                    $obj->name = $item->ingredient->name;
                    $obj->amount = $item->amount;
                    return $obj;
                });

                $payload['data'][$i]['ingredients'] = ProductIngredient::whereIn('id', $ingredientRelations->pluck('id'))->get()->map(function ($item) use ($ingredientRelations, $amount_item) {
                    $relation = $ingredientRelations->where('id', $item->id)->first();
                    $obj = new \stdClass();
                    $obj->name = $item->name;
                    $obj->amount = $relation->amount * $amount_item;
                    $obj->unit = $item->unit->name;
                    return $obj;
                })->toArray();

                foreach ($ingredientRelations as $ingredient) {
                    $is_stock_debt = $ingredient->amount * $amount_item;
                    $availableStock = ProductIngredientStock::where('store_id', $shift->store_id)
                        ->where('ingredient_id', $ingredient->id)
                        ->where('amount_available', '>', 0)
                        ->where(function ($query) {
                            $query->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                        })
                        ->sum('amount_available');

                    $dStock['amount_before'] = $availableStock;
                    $dStock['amount_after'] = $availableStock - $is_stock_debt;
                    if ($availableStock > 0) {
                        while ($is_stock_debt > 0) {
                            $stock = ProductIngredientStock::where('store_id', $shift->store_id)
                                ->where('ingredient_id', $ingredient->id)
                                ->where('amount_available', '>', 0)
                                ->where(function ($query) {
                                    $query->whereNull('expired_at')
                                        ->orWhere('expired_at', '>', now());
                                })
                                ->orderBy('expired_at', 'ASC')
                                ->first();

                            if (!$stock) {
                                // disable temporary
                                // $is_success = false;
                                // $txt_error = 'Bahan ' . $product->name . ' tidak mencukupi (' . $ingredient->name . ')';
                                break;
                            }

                            $is_stock_debt -= $stock->amount_available;

                            if ($is_stock_debt > 0) {
                                $stock->amount_available = 0;
                                $stock->update();
                            } else {
                                $stock->amount_available = abs($is_stock_debt);
                                $stock->update();
                            }
                        }
                    }

                    $isLast = $ingredientRelations->last() == $ingredient;

                    // transactions logs
                    DB::table('transaction_logs')->insert([
                        'store_id' => $shift->store_id,
                        'transaction_id' => $transaction_id,
                        'product_id' => $a_data[$i]['product_id'],
                        'ingredient_id' => $ingredient->id,
                        'amount_before' => $dStock['amount_before'] ?? 0,
                        'amount_after' => $dStock['amount_after'] ?? 0,
                        'item_amount' => $isLast ? $amount_item : null,
                        'amount' => $a_data[$i]['amount'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if (!$is_success) {
                        break;
                    }
                }
            }

            $payload['amount_profit'] += ($a_data[$i]['sell_price'] - $a_data[$i]['buy_price']) * $a_data[$i]['amount'];
        }

        $payload['amount_received'] = $request->amount_received;
        $payload['amount_total'] = $total_price;
        $payload['paid'] = true;

        $d_status = Transaction::create($payload);

        if (!$is_success || !$d_status) {
            DB::rollBack();
            return self::apiResponse(400, $txt_error, null);
        }

        DB::commit();

        return self::apiResponse(message: 'Transaksi berhasil', data: ['trx_id' => $payload['trx_id']]);
    }

    /**
     * Post user register
     * $request->name (required)
     * $request->email (required)
     * $request->phone (required)
     * $request->reason (required)
     */
    public function postUserRegister(Request $request)
    {
        $this->apiValidation($request, [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'reason' => 'required',
        ]);

        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $reason = $request->reason;

        $user = DB::table('user_register_manuals')
            ->where('email', $email)
            ->first();

        if ($user) {
            return self::apiResponse(400, 'Email sudah terdaftar', null);
        }

        DB::table('user_register_manuals')->insert([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'description' => $reason,
        ]);

        return self::apiResponse(message: 'Pendaftaran berhasil', data: $user);
    }
}
