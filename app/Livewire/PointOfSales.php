<?php

namespace App\Livewire;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\IngredientStock;
use App\Models\PaymentMethod;
use App\Models\ProductIngredient;
use App\Models\ProductIngredientRelation;
use App\Models\ProductIngredientStock;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PointOfSales extends Component
{
    public $type_product = 1;
    public $search = null;
    public $user = null;
    public $category_id = null;
    public $customer_id = null;
    public $payment_method_id = null;
    public $amount_received = null;
    public $product = null;
    public $list_product = [];
    public $item_scanner_amount = null;
    public $list_history_transaction = [];
    public $list_history_preorder = [];
    public $debt_transaction = null;
    public $debt_amount = 0;
    public $list_customer_debt = [];
    public $model_customer_debt = null;
    public $all_products = [];
    public $retur_item = null;
    public $payload_retur = [];
    public $retur_item_transactions = [];
    public $discount_type = null;
    public $discount_value = null;
    public $transaction_type = 1;
    public $isFirstLoad = true;
    public $isUseCustomerBalance = false;
    public $listIngredients = [];
    public $listPaymentMethod = [];
    public $stockMinimum = null;
    public $form_customer_name = null;
    public $form_customer_phone = null;
    public $form_customer_email = null;
    public $wrap_header_status = "close";

    public function render()
    {
        $this->user = auth()->user();

        $user = $this->user;

        if (!$user->hasPermissionTo('can_access_pos')) {
            return abort(403);
        }

        $query_category = ProductCategory::query();
        $query_user = User::query();
        $query_supplier = Supplier::query();
        $query_customer = Customer::query();

        $query_supplier->select('id', 'name');
        $query_customer->select('id', 'name', 'balance');

        $query_category->where('store_id', $user->store_id);
        $query_category->orderBy('name', 'ASC');

        $query_user->where('store_id', $user->store_id);
        $query_user->orderBy('name', 'ASC');

        $query_supplier->where('store_id', $user->store_id);
        $query_supplier->orderBy('name', 'ASC');

        $query_customer->where('store_id', $user->store_id);
        $query_customer->orderBy('name', 'ASC');

        // query for product variants
        $query_product = DB::table('product_variants as pv')
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
            ->where('p.store_id', $user->store_id)
            ->where('p.buy_price', null)
            ->leftJoin('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('product_units as u', 'u.id', '=', 'pv.unit_id');

        if ($this->category_id != null) {
            $query_product->where('p.category_id', $this->category_id);
        }

        if ($this->search != null) {
            $query_product->where(function ($query) {
                $query->where('p.name', env('DB_SEARCH_OPERATOR'), '%' . $this->search . '%')
                    ->orWhere('pv.code', env('DB_SEARCH_OPERATOR'), '%' . $this->search . '%');
            });
        }

        $query_product->union(
            DB::table('products as p')
                ->select([
                    DB::raw('NULL as variant_id'),
                    'p.id as product_id',
                    'p.buy_price as buy_price',
                    'p.sell_price as sell_price',
                    DB::raw('NULL as sell_retail_price'),
                    DB::raw('NULL as measurement'),
                    DB::raw('(SELECT SUM(amount_available) FROM product_ingredient_stocks WHERE product_id = p.id AND (expired_at IS NULL OR expired_at > NOW())) as amount_available'),
                    'p.name',
                    'p.image',
                    'p.code',
                    DB::raw('NULL as unit_name'),
                ])
                ->where('p.store_id', $user->store_id)
                ->where('p.buy_price', '!=', null)
                ->where(function ($query) {
                    if ($this->category_id != null) {
                        $query->where('p.category_id', $this->category_id);
                    }

                    if ($this->search != null) {
                        $query->where('p.name', env('DB_SEARCH_OPERATOR'), '%' . $this->search . '%')
                            ->orWhere('p.code', env('DB_SEARCH_OPERATOR'), '%' . $this->search . '%');
                    }
                })
        );
        $query_product->orderBy('name', 'ASC');
        $products = $query_product->get();

        if (empty($this->all_products)) {
            $this->all_products = $products;
        }

        $this->listIngredients = ProductIngredient::where('store_id', $user->store_id)->get()->map(function ($item) {
            $obj = new \stdClass();
            $obj->id = $item->id;
            $obj->name = $item->name . ' (' . $item->unit->name . ')';
            $obj->stock = IngredientStock::where('ingredient_id', $item->id)
                ->where(function ($query) {
                    $query->whereNull('expired_at')
                        ->orWhere('expired_at', '>', now());
                })
                ->sum('amount_available');
            $obj->price = $item->price;
            return $obj;
        });

        $employee = User::where('email', $user->email)->first();

        $shift = Shift::where('user_id', $employee->id)
            ->where('end_shift_at', null)
            ->first();

        $this->listPaymentMethod = PaymentMethod::where('store_id', $user->store_id)->orderBy('name', 'ASC')->get();

        $this->payment_method_id = isset($this->listPaymentMethod[0]) ? $this->listPaymentMethod[0]->id : null;

        $this->stockMinimum = (int) Setting::where('key', 'alert_stock_minimum')->where('store_id', $user->store_id)->value('value');

        return view('livewire.point-of-sales', [
            'categories' => $query_category->get(),
            'products' => $products,
            'users' => $query_user->get(),
            'suppliers' => $query_supplier->get(),
            'customers' => $query_customer->get(),
            'shift' => $shift
        ]);
    }

    public function submitPayment()
    {
        $user = $this->user;

        $discountValue = (int) str_replace('.', '', $this->discount_value);

        // check for discount
        if (!empty($this->discount_type) && empty($discountValue)) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Diskon Gagal Ditambahkan',
                'message' => 'Nilai diskon tidak boleh kosong'
            ]);
            return;
        }

        // check for payment method
        if (empty($this->payment_method_id)) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Pembayaran Gagal',
                'message' => 'Metode pembayaran tidak boleh kosong'
            ]);
            return;
        }

        $shift = Shift::where('user_id', $user->id)->where('end_shift_at', null)->first();

        $payload['store_id'] = $user->store_id;
        $payload['shift_id'] = $shift->id;
        $payload['customer_id'] = !empty($this->customer_id) ? $this->customer_id : null;
        $payload['payment_method_id'] = $this->payment_method_id;
        $payload['type'] = $this->transaction_type;
        $payload['amount_profit'] = 0;
        $last_transction = Transaction::where('store_id', $user->store_id)->orderBy('id', 'DESC')->first();
        $transaction_id = $last_transction ? $last_transction->id + 1 : 1;
        $payload['trx_id'] = ($this->transaction_type == 1 ? 'TRX-' : 'PRD-') . str_pad($transaction_id, 8, "0", STR_PAD_LEFT);
        $payload['total_items'] = 0;

        // if pre-order set to pending
        if ($this->transaction_type == 2) {
            $payload['status'] = 0;
        }

        $data_product = $this->list_product;

        DB::beginTransaction();

        $is_success = true;
        $txt_error = '';

        $total_price = 0;
        $total_discount = 0;
        for ($i = 0; $i < sizeof($data_product); $i++) {
            $payload['total_items'] += $data_product[$i]['amount'];

            $total_price += $data_product[$i]['active_price'] * $data_product[$i]['amount'];

            $dStock = [];
            if (!empty($data_product[$i]['variant_id'])) {
                $variant = ProductVariant::find($data_product[$i]['variant_id']);
                $unit = ProductUnit::find($variant->unit_id);
                $name = $data_product[$i]['name'] . ' (' . $variant->measurement . ' ' . $unit->name . ')';

                $payload['data'][$i] = [
                    'product_id' => $data_product[$i]['product_id'],
                    'variant_id' => $data_product[$i]['variant_id'],
                    'name' => $name,
                    'buy_price' => $data_product[$i]['buy_price'],
                    'sell_price' => $data_product[$i]['active_price'],
                    'discount_type' => $data_product[$i]['discount_type'],
                    'discount_value' => $data_product[$i]['discount_value'],
                    'amount' => $data_product[$i]['amount'],
                    'amount_retur' => 0,
                ];

                if (!empty($data_product[$i]['discount_type']) && !empty($data_product[$i]['discount_value'])) {
                    $discount = 0;
                    if ($data_product[$i]['discount_type'] == '1') {
                        $discount = ($data_product[$i]['active_price'] * $data_product[$i]['discount_value']) / 100;
                    } else {
                        $discount = $data_product[$i]['discount_value'];
                    }

                    $total_discount += $discount;
                }

                // if transaction type is sale
                if ($this->transaction_type == 1) {
                    $is_stock_debt = $data_product[$i]['amount'];
                    $availableStock = ProductStock::where('store_id', $user->store_id)
                        ->where('product_id', $data_product[$i]['product_id'])
                        ->where('variant_id', $data_product[$i]['variant_id'])
                        ->where('amount_available', '>', 0)
                        ->where(function ($query) {
                            $query->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                        })
                        ->sum('amount_available');

                    $dStock['amount_before'] = $availableStock;
                    $dStock['amount_after'] = $availableStock - $is_stock_debt;
                    while ($is_stock_debt > 0) {
                        $stock = ProductStock::where('store_id', $user->store_id)
                            ->where('product_id', $data_product[$i]['product_id'])
                            ->where('variant_id', $data_product[$i]['variant_id'])
                            ->where('amount_available', '>', 0)
                            ->where(function ($query) {
                                $query->whereNull('expired_at')
                                    ->orWhere('expired_at', '>', now());
                            })
                            ->orderBy('expired_at', 'ASC')
                            ->first();

                        if (!$stock) {
                            $is_success = false;
                            $txt_error = 'Stock ' . $name . ' tidak mencukupi';
                            break;
                        }

                        $is_stock_debt -= $stock->amount_available;
                        $availableStock -= $stock->amount_available;

                        if ($is_stock_debt > 0) {
                            $stock->amount_available = 0;
                            $stock->update();
                        } else {
                            $stock->amount_available = abs($is_stock_debt);
                            $stock->update();
                        }
                    }
                }
            } else if (!empty($data_product[$i]['ingredient_id'])) {
                $ingredient = ProductIngredient::find($data_product[$i]['ingredient_id']);
                $unit = ProductUnit::find($ingredient->unit_id);
                $name = $data_product[$i]['name'];

                $payload['data'][$i] = [
                    'product_id' => null,
                    'variant_id' => null,
                    'ingredient_id' => $data_product[$i]['ingredient_id'],
                    'name' => $name,
                    'buy_price' => $data_product[$i]['buy_price'],
                    'sell_price' => $data_product[$i]['active_price'],
                    'discount_type' => $data_product[$i]['discount_type'],
                    'discount_value' => $data_product[$i]['discount_value'],
                    'amount' => $data_product[$i]['amount'],
                    'amount_retur' => 0,
                ];

                if (!empty($data_product[$i]['discount_type']) && !empty($data_product[$i]['discount_value'])) {
                    $discount = 0;
                    if ($data_product[$i]['discount_type'] == '1') {
                        $discount = ($data_product[$i]['active_price'] * $data_product[$i]['discount_value']) / 100;
                    } else {
                        $discount = $data_product[$i]['discount_value'];
                    }

                    $total_discount += $discount;
                }

                // if transaction type is sale
                if ($this->transaction_type == 1) {
                    $is_stock_debt = $data_product[$i]['amount'];
                    $availableStock = IngredientStock::where('store_id', $user->store_id)
                        ->where('ingredient_id', $data_product[$i]['ingredient_id'])
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
                            $stock = IngredientStock::where('store_id', $user->store_id)
                                ->where('ingredient_id', $data_product[$i]['ingredient_id'])
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
                                // $txt_error = 'Stock ' . $name . ' tidak mencukupi';
                                // break;
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
                }
            } else {
                $amount_item = $data_product[$i]['amount'];

                $payload['data'][$i] = [
                    'product_id' => $data_product[$i]['product_id'],
                    'variant_id' => null,
                    'name' => $data_product[$i]['name'],
                    'buy_price' => $data_product[$i]['buy_price'],
                    'sell_price' => $data_product[$i]['active_price'],
                    'discount_type' => $data_product[$i]['discount_type'],
                    'discount_value' => $data_product[$i]['discount_value'],
                    'amount' => $amount_item,
                    'amount_retur' => 0,
                ];

                if (!empty($data_product[$i]['discount_type']) && !empty($data_product[$i]['discount_value'])) {
                    $discount = 0;
                    if ($data_product[$i]['discount_type'] == '1') {
                        $discount = ($data_product[$i]['active_price'] * $data_product[$i]['discount_value']) / 100;
                    } else {
                        $discount = $data_product[$i]['discount_value'];
                    }

                    $total_discount += $discount;
                }

                // get ingredient relations
                $ingredientRelations = ProductIngredientRelation::where('product_id', $data_product[$i]['product_id'])->get()->map(function ($item) {
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

                // if transaction type is sale
                if ($this->transaction_type == 1) {
                    $productIngredientStock = ProductIngredientStock::where('store_id', $user->store_id)
                        ->where('product_id', $data_product[$i]['product_id'])
                        ->where('amount_available', '>', 0)
                        ->where(function ($query) {
                            $query->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                        })
                        ->sum('amount_available');

                    $dStock['amount_before'] = $productIngredientStock;
                    $dStock['amount_after'] = $productIngredientStock - $amount_item;

                    $is_stock_debt = $amount_item;
                    while ($is_stock_debt > 0) {
                        $stock = ProductIngredientStock::where('store_id', $user->store_id)
                            ->where('product_id', $data_product[$i]['product_id'])
                            ->where('amount_available', '>', 0)
                            ->where(function ($query) {
                                $query->whereNull('expired_at')
                                    ->orWhere('expired_at', '>', now());
                            })
                            ->orderBy('expired_at', 'ASC')
                            ->first();

                        if (!$stock) {
                            // $is_success = false;
                            // $txt_error = 'Stock ' . $data_product[$i]['name'] . ' tidak mencukupi';
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

                    foreach ($ingredientRelations as $ingredient) {
                        $is_stock_debt = $ingredient->amount * $data_product[$i]['amount'];

                        $amount_before = IngredientStock::where('store_id', $user->store_id)
                            ->where('ingredient_id', $ingredient->id)
                            ->where('amount_available', '>', 0)
                            ->where(function ($query) {
                                $query->whereNull('expired_at')
                                    ->orWhere('expired_at', '>', now());
                            })
                            ->sum('amount_available');

                        $amount_after = $amount_before - $is_stock_debt;

                        // transaction logs
                        DB::table('transaction_logs')->insert([
                            'store_id' => $user->store_id,
                            'transaction_id' => $transaction_id,
                            'ingredient_id' => $ingredient->id,
                            'amount_before' => $amount_before ?? 0,
                            'amount_after' => $amount_after ?? 0,
                            'amount' => $is_stock_debt,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        while ($is_stock_debt > 0) {
                            $stock = IngredientStock::where('store_id', $user->store_id)
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

                        if (!$is_success) {
                            break;
                        }
                    }
                }
            }

            // transaction logs
            DB::table('transaction_logs')->insert([
                'store_id' => $user->store_id,
                'transaction_id' => $transaction_id,
                'product_id' => $data_product[$i]['product_id'],
                'variant_id' => $data_product[$i]['variant_id'],
                'ingredient_id' => $data_product[$i]['ingredient_id'],
                'amount_before' => $dStock['amount_before'] ?? 0,
                'amount_after' => $dStock['amount_after'] ?? 0,
                'amount' => $data_product[$i]['amount'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $payload['amount_profit'] += ($data_product[$i]['active_price'] - $data_product[$i]['buy_price']) * $data_product[$i]['amount'];
        }

        // discount all
        if (!empty($this->discount_type) && !empty($discountValue)) {
            $discount = 0;
            if ($this->discount_type == '1') {
                $discount = ($total_price * $discountValue) / 100;
            } else {
                $discount = $discountValue;
            }

            $total_discount += $discount;
        }

        // update format amount received
        $this->amount_received = (int) str_replace('.', '', $this->amount_received);

        $customer = null;
        if (!empty($this->customer_id)) {
            $customer = Customer::find($this->customer_id);
        }

        // get amount received
        $amount_received = ($this->amount_received ?? 0);

        // check for empty data
        if (empty($payload['data'] ?? [])) {
            $is_success = false;
            $txt_error = 'Item pembelian tidak boleh kosong';
        }

        // check for payment
        if ($amount_received < ($total_price - $total_discount)) {
            // check for customer
            if (empty($this->customer_id)) {
                $is_success = false;
                $txt_error = 'Pelanggan tidak boleh kosong, jika pembayaran kurang dari total';
            }

            // for now, only allow pre-order
            if ($this->transaction_type == 1) {
                $is_success = false;
                $txt_error = 'Hutang hanya bisa dilakukan untuk transaksi pre-order';
            }
        }

        if (!$is_success) {
            DB::rollBack();
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Transaksi Gagal',
                'message' => $txt_error
            ]);
            return;
        }

        // check for customer balance
        $customer_balance_log = null;
        if ($this->isUseCustomerBalance) {
            if ($this->amount_received < ($total_price - $total_discount)) {
                $less = ($total_price - $total_discount) - $this->amount_received;

                if ($customer->balance < $less) {
                    $less = $customer->balance;
                }

                $amount_received += $less;

                // insert log
                $customer_balance_log = [
                    'customer_id' => $customer->id,
                    'balance_before' => $customer->balance,
                    'balance_after' => $customer->balance - $less,
                    'balance_change' => $less,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        $payload['amount_discount'] = $total_discount;
        $payload['amount_received'] = $amount_received;
        $payload['amount_total'] = $total_price - $total_discount;

        // check amount less
        $isLess = false;
        $amountLess = $total_price - ($payload['amount_received'] + $total_discount);
        if ($amountLess > 0) {
            $isLess = true;
            $payload['amount_less'] = $amountLess - $total_discount;
        }

        // check discount
        $payload['amount_profit'] = $payload['amount_profit'] - $total_discount;

        $transaction = Transaction::create($payload);

        // update for customer balance
        if ($customer_balance_log) {
            $customer_balance_log['transaction_id'] = $transaction->id;
            DB::table('customer_balance_logs')->insert($customer_balance_log);
            $customer->balance = $customer_balance_log['balance_after'];
            $customer->update();
        }

        DB::commit();

        // check for deposit
        $withDeposit = (!empty($this->customer_id) && $amount_received > ($total_price - $total_discount))
            && $this->transaction_type == 1 && !$isLess;
        $depositBalance = ($amount_received - $total_price) + $total_discount;

        // check for deposit second
        if ($this->isUseCustomerBalance && $this->amount_received < ($total_price - $total_discount)) {
            $withDeposit = false;
        }

        // Reset value
        $this->amount_received = null;
        $this->payment_method_id = null;
        $this->customer_id = null;
        $this->discount_type = null;
        $this->discount_value = null;
        $this->transaction_type = 1;
        $this->isUseCustomerBalance = false;
        $this->clear_list();

        $this->dispatch('renderTransaction', [
            'route' => route('admin.transactions.show', $transaction->id) . '?from=pos',
            'afterDeposit' => false,
            'withDeposit' => $withDeposit,
            'depositBalance' => $depositBalance,
            'depositPayload' => $withDeposit ? ($transaction->id . '/' . $payload['customer_id'] . '/' . $depositBalance) : null,
        ]);
    }

    public function submit_deposit($payload)
    {
        list($transaction_id, $customer_id, $amount) = explode('/', $payload);

        $transaction = Transaction::find($transaction_id);

        $customer = Customer::find($customer_id);

        $customer->balance = $customer->balance + $amount;

        $transaction->amount_deposit = $amount;

        $transaction->update();

        $customer->update();

        $this->dispatch('renderTransaction', [
            'route' => route('admin.transactions.show', $transaction->id) . '?from=pos',
            'afterDeposit' => true,
            'depositBalance' => $amount,
            'customerName' => $customer->name,
        ]);
    }

    public function search_product($code)
    {
        $this->product = Product::select('products.*')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.code', env('DB_SEARCH_OPERATOR'), $code)
            ->first();
    }

    public function get_customer_debt()
    {
        $this->list_customer_debt = Customer::whereIn('id', Transaction::where('store_id', auth()->user()->store_id)
            ->where('amount_less', '>', 0)
            ->pluck('customer_id'))
            ->get();

        $this->dispatch('renderCustomerDebt');
    }

    public function get_customer_detail($id)
    {
        $this->model_customer_debt = Customer::find($id);

        $this->model_customer_debt->debts = Transaction::where('store_id`', auth()->user()->store_id)
            ->where('customer_id', $id)
            ->where('amount_less', '>', 0)
            ->get();

        $this->dispatch('renderCustomerDetail');
    }

    public function add_product($item, $is_variant)
    {
        $is_has = false;
        for ($i = 0; $i < sizeof($this->list_product); $i++) {
            if ($is_variant && $this->list_product[$i]['variant_id'] == $item['variant_id']) {
                $is_has = true;
                $this->list_product[$i]['amount']++;
            } else if (!$is_variant && $this->list_product[$i]['product_id'] == $item['product_id']) {
                $is_has = true;
                $this->list_product[$i]['amount']++;
            }
        }
        if (!$is_has) {
            // check stock if not null or 0)
            if (empty($item['amount_available']) && $is_variant && $this->transaction_type == 1) {
                $this->dispatch('renderAlert', [
                    'icon' => 'error',
                    'title' => 'Stock Kosong',
                    'message' => 'Stock ' . $item['name'] . ' kosong'
                ]);
                return;
            }

            $listStockNotEnough = [];

            // check stock for ingredient
            $item['ingredient_stock_not_enough'] = [];
            if (!$is_variant && $this->transaction_type == 1) {
                $ingredientRelations = ProductIngredientRelation::where('product_id', $item['product_id'])->get()->map(function ($item) {
                    $obj = new \stdClass();
                    $obj->id = $item->ingredient_id;
                    $obj->amount = $item->amount;
                    return $obj;
                });

                foreach ($ingredientRelations as $ingredient) {
                    $totalStock = IngredientStock::where('store_id', auth()->user()->store_id)
                        ->where('ingredient_id', $ingredient->id)
                        ->where('amount_available', '>', 0)
                        ->where(function ($query) {
                            $query->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                        })
                        ->sum('amount_available');

                    if ($totalStock < $ingredient->amount) {
                        $listStockNotEnough[$ingredient->id] = $totalStock;
                    }
                }

                $item['ingredient_stock_not_enough'] = $listStockNotEnough;
            }

            $item['ingredient_id'] = null;
            $item['amount'] = 1;
            $item['active_price'] = $item['sell_price'];
            $item['discount_type'] = null;
            $item['discount_value'] = null;
            $this->list_product[] = $item;
        }
    }

    public function change_price($variant_id, $product_id, $price)
    {
        for ($i = 0; $i < sizeof($this->list_product); $i++) {
            if (!empty($variant_id) && $this->list_product[$i]['variant_id'] == $variant_id) {
                $this->list_product[$i]['active_price'] = $price;
            } else if (empty($variant_id) && $this->list_product[$i]['product_id'] == $product_id) {
                $this->list_product[$i]['active_price'] = $price;
            }
        }
    }

    public function remove_product($item)
    {
        for ($i = 0; $i < sizeof($this->list_product); $i++) {
            if (!empty($item['variant_id']) && $this->list_product[$i]['variant_id'] == $item['variant_id']) {
                $this->list_product[$i] = null;
            } else if (!empty($item['ingredient_id']) && $this->list_product[$i]['ingredient_id'] == $item['ingredient_id']) {
                $this->list_product[$i] = null;
            } else {
                if (empty($item['variant_id']) && $this->list_product[$i]['product_id'] == $item['product_id']) {
                    $this->list_product[$i] = null;
                }
            }
        }

        $raw = array();

        for ($i = 0; $i < sizeof($this->list_product); $i++) {
            if ($this->list_product[$i] !== null) {
                $raw[] = $this->list_product[$i];
            }
        }

        $this->list_product = $raw;
    }

    public function change_category($id = null)
    {
        if ($this->category_id == $id) {
            $this->category_id = null;
        } else {
            $this->category_id = $id;
        }
    }

    public function clear_list()
    {
        $this->list_product = [];
    }

    public function update_item_product($variant_id, $product_id, $ingredient_id, $value)
    {
        if (!empty($variant_id)) {
            $variant_id = (int)$variant_id;
        }
        for ($i = 0; $i < sizeof($this->list_product); $i++) {
            if (
                !empty($variant_id) && $this->list_product[$i]['variant_id'] == $variant_id
                || empty($variant_id) && $this->list_product[$i]['product_id'] == $product_id
                || !empty($ingredient_id) && $this->list_product[$i]['ingredient_id'] == $ingredient_id
            ) {
                if ($value == 0 || $value == null) {
                    $this->remove_product($this->list_product[$i]);
                } else {
                    $this->list_product[$i]['amount'] = (int)$value;
                }
            }
        }
    }

    public function add_item_from_scanner($data)
    {
        list($type, $id) = explode(',', $data);
        $product = [];
        foreach ($this->all_products as $item) {
            if ($type == 'variant_id' && $item->variant_id == $id) {
                $product = $item;
                break;
            } else if ($type == 'product_id' && $item->product_id == $id) {
                $product = $item;
                break;
            }
        }
        $res = [];
        foreach ($product as $key => $value) {
            $res[$key] = $value;
        }
        $this->add_product($res, $type == 'variant_id' ? true : false);
        $this->update_item_product($res['variant_id'], $res['product_id'], null, ($this->item_scanner_amount ?? 1));
        $this->item_scanner_amount = 1;
    }

    public function add_amount_from_scanner($value)
    {
        $this->item_scanner_amount = (int)$value;
    }

    public function change_customer($id)
    {
        $this->customer_id = $id;
    }

    public function repaid_transaction($id, $amount)
    {
        $transaction = Transaction::find($id);

        $transaction->repaid_at = now();

        DB::table('debts')->insert([
            'store_id' => $transaction->store_id,
            'shift_id' => $transaction->shift_id,
            'transaction_id' => $transaction->id,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $transaction->update();
    }

    public function shift_out()
    {
        $user = auth()->user();

        $employee = User::where('email', $user->email)->first();

        $shift = Shift::where('user_id', $employee->id)
            ->where('end_shift_at', null)
            ->first();

        $amount_total = Transaction::query()
            ->join('payment_methods as pm', 'transactions.payment_method_id', '=', 'pm.id')
            ->where('pm.is_cash', true)
            ->where('transactions.shift_id', $shift->id)
            ->sum('transactions.amount_total');

        $amount_debt = Transaction::query()
            ->where('shift_id', $shift->id)
            ->sum('amount_less');

        $amount_retur = Transaction::query()
            ->where('shift_id', $shift->id)
            ->sum('amount_retur');

        $amount_shift_end = ($amount_total + $shift->amount_start) - $amount_retur - $amount_debt;

        $this->dispatch('renderModalShiftOut', [
            'amount_shift_end' => $amount_shift_end
        ]);
    }

    public function get_history_transaction()
    {
        $query_transaction = DB::table('transactions as ts')
            ->select([
                'ts.*',
                'cs.name as customer_name'
            ])
            ->where('ts.store_id', $this->user->store_id)
            ->leftjoin('customers as cs', 'ts.customer_id', '=', 'cs.id')
            ->orderBy('ts.id', 'DESC')
            ->limit(10)
            ->get();

        $this->list_history_transaction = json_decode($query_transaction, true);

        $isLoad = $this->isFirstLoad;

        if ($isLoad) {
            $this->isFirstLoad = false;
        }

        $this->dispatch('renderModalHistoryTransaction', [
            'isFirstLoad' => $isLoad
        ]);
    }

    public function search_history_transaction($value)
    {
        $query_transaction = DB::table('transactions as ts')
            ->select([
                'ts.*',
                'cs.name as customer_name'
            ])
            ->where('ts.store_id', $this->user->store_id)
            ->when($value, function ($query) use ($value) {
                $query->where('ts.trx_id', env('DB_SEARCH_OPERATOR'), '%' . $value . '%')
                    ->orWhere('cs.name', env('DB_SEARCH_OPERATOR'), '%' . $value . '%');
            })
            ->leftjoin('customers as cs', 'ts.customer_id', '=', 'cs.id')
            ->orderBy('ts.id', 'DESC')
            ->limit(10)
            ->get();

        $this->list_history_transaction = json_decode($query_transaction, true);

        $this->dispatch('renderModalHistoryTransaction', [
            'isFirstLoad' => false
        ]);
    }

    public function search_history_preorder($value)
    {
        $query_transaction = DB::table('transactions as ts')
            ->select([
                'ts.*',
                'cs.name as customer_name'
            ])
            ->where('ts.store_id', $this->user->store_id)
            ->where('ts.status', 0)
            ->where('ts.type', 2)
            ->when($value, function ($query) use ($value) {
                $query->where('ts.trx_id', env('DB_SEARCH_OPERATOR'), '%' . $value . '%')
                    ->orWhere('cs.name', env('DB_SEARCH_OPERATOR'), '%' . $value . '%');
            })
            ->leftjoin('customers as cs', 'ts.customer_id', '=', 'cs.id')
            ->orderBy('ts.id', 'DESC')
            ->limit(10)
            ->get();

        $this->list_history_preorder = json_decode($query_transaction, true);

        $this->dispatch('renderModalHistoryPreorder', [
            'isFirstLoad' => false
        ]);
    }

    public function get_debt_transaction($id, $close_detail = false)
    {
        $debt_transaction = DB::table('transactions as ts')
            ->select([
                'ts.id',
                'ts.amount_less',
                'ts.repaid_at',
                'ts.customer_id',
                'cs.name'
            ])
            ->where('ts.id', $id)
            ->leftJoin('customers as cs', 'ts.customer_id', '=', 'cs.id')
            ->get();

        if ($close_detail) {
            $this->dispatch('closeDetailCustomerDebt');
        }

        $this->debt_transaction = json_decode($debt_transaction, true);

        $this->dispatch('renderModalDebtTransaction');
    }

    public function open_modal_return($id)
    {
        $transaction = Transaction::find($id);

        $isValidRetur = false;
        foreach ($transaction->data as $item) {
            if ($item['amount'] > 0) {
                $isValidRetur = true;
                break;
            }
        }

        if (!$isValidRetur) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Tidak ada item yang bisa diretur'
            ]);
            return;
        }

        $this->retur_item = $transaction;
        $this->retur_item_transactions = $transaction->data;

        $this->dispatch('renderModalReturTransaction');
    }

    public function update_retur_item($index, $value)
    {
        $this->payload_retur[$index] = $value;
    }

    public function submit_form_customer()
    {
        $payload = [
            'store_id' => $this->user->store_id,
            'name' => $this->form_customer_name,
            'phone' => $this->form_customer_phone,
            'email' => $this->form_customer_email,
        ];

        // check if name is empty
        if (empty($payload['name'])) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Nama Pelanggan tidak boleh kosong'
            ]);
            return;
        }

        Customer::create($payload);

        $this->form_customer_name = null;
        $this->form_customer_phone = null;
        $this->form_customer_email = null;

        $this->dispatch('renderAlert', [
            'icon' => 'success',
            'title' => 'Pelanggan Berhasil Ditambahkan'
        ]);
    }

    public function submit_retur_item()
    {
        $arrData = array_filter($this->payload_retur, function ($item) {
            return !empty($item);
        });

        if (empty($arrData)) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Jumlah retur tidak boleh kosong'
            ]);
            return;
        }

        $payload = [];
        $amountTotalRetur = 0;
        foreach ($this->retur_item->data as $index => $obj) {
            if ($obj['amount'] == 0) {
                $payload[] = $obj;
                continue;
            }

            if (isset($arrData[$index + 1])) {
                if ($arrData[$index + 1] > $obj['amount']) {
                    $this->dispatch('renderAlert', [
                        'icon' => 'error',
                        'title' => 'Jumlah retur melebihi jumlah item'
                    ]);
                    return;
                }

                $obj['amount'] = (int) $obj['amount'] - $arrData[$index + 1];
                $obj['amount_retur'] = (int) $arrData[$index + 1];
                $payload[] = $obj;

                $amountTotalRetur += $obj['amount_retur'] * $obj['sell_price'];
            } else {
                $payload[] = $obj;
            }
        }

        $this->retur_item->data = $payload;
        $this->retur_item->amount_retur += $amountTotalRetur;
        $this->retur_item->update();

        $this->dispatch('renderAlert', [
            'icon' => 'success',
            'title' => 'Retur Item Berhasil'
        ]);
    }

    public function get_history_preorder()
    {
        $query_transaction = DB::table('transactions as ts')
            ->select([
                'ts.*',
                'cs.name as customer_name'
            ])
            ->where('ts.store_id', $this->user->store_id)
            ->where('ts.status', 0)
            ->where('ts.type', 2)
            ->leftjoin('customers as cs', 'ts.customer_id', '=', 'cs.id')
            ->orderBy('ts.id', 'DESC')
            ->limit(10)
            ->get();

        $this->list_history_preorder = json_decode($query_transaction, true);

        $isLoad = $this->isFirstLoad;

        if ($isLoad) {
            $this->isFirstLoad = false;
        }

        $this->dispatch('renderModalHistoryPreorder', [
            'isFirstLoad' => $isLoad
        ]);
    }

    public function open_modal_payment_preorder($id)
    {
        $transaction = Transaction::find($id);

        $this->dispatch('renderModalPaymentPreorder', [
            'id' => $transaction->id,
            'transactionRef' => $transaction->trx_id,
            'amountLess' => $transaction->amount_less,
        ]);
    }

    public function submit_payment_preorder($payload)
    {
        list($id, $amount) = explode('/', $payload);

        $amount = (int) str_replace('.', '', $amount);

        $transaction = Transaction::find($id);

        $transaction->amount_received += $amount;

        $amount_less = $transaction->amount_less - $amount;
        if ($amount_less <= 0) {
            $transaction->amount_less = 0;
            $transaction->status = 1;
        } else {
            $transaction->amount_less = $amount_less;
        }

        $transaction->update();

        $this->dispatch('renderAlert', [
            'icon' => 'success',
            'title' => 'Pembayaran Preorder Berhasil'
        ]);
    }

    public function start_shift($amount)
    {
        $amount = (int) str_replace('.', '', $amount);

        $payload['user_id'] = $this->user->id;
        $payload['store_id'] = $this->user->store_id;
        $payload['start_shift_at'] = now();
        $payload['amount_start'] = $amount;

        Shift::create($payload);

        return redirect()->route('app.point_of_sale');
    }

    public function end_shift($amount)
    {
        $amount = (int) str_replace('.', '', $amount);

        $shift = Shift::where('user_id', $this->user->id)
            ->where('end_shift_at', null)
            ->first();

        $shift->end_shift_at = now();
        $shift->amount_end = $amount;
        $shift->amount_total = $shift->amount_end - $shift->amount_start;
        $shift->update();

        return redirect()->route('app.point_of_sale');
    }

    public function set_debt_amount($amount)
    {
        $this->debt_amount  = $amount;
    }

    public function open_navbar()
    {
        if ($this->wrap_header_status == "") {
            $this->wrap_header_status = "close";
        } else {
            $this->wrap_header_status = "";
        }
    }

    public $item_discount = null;
    public $item_discount_payload = [];

    public function open_discount_modal($index)
    {
        $this->item_discount = $this->list_product[$index];

        $this->item_discount_payload = [
            'index' => $index,
            'discount_type' => $this->item_discount['discount_type'],
            'discount_value' => $this->item_discount['discount_value']
        ];

        $this->dispatch('renderModalItemDiscount', [
            'withReset' => empty($this->item_discount['discount_type'])
        ]);
    }

    public function update_item_discount_type($value)
    {
        $this->item_discount_payload['discount_type'] = $value;
    }

    public function update_item_discount_value($value)
    {
        $value = (int) str_replace('.', '', $value);

        $this->item_discount_payload['discount_value'] = $value;
    }

    public function submit_item_discount()
    {
        $index = $this->item_discount_payload['index'];

        // check if discount value or type is empty
        if (empty($this->item_discount_payload['discount_type']) || empty($this->item_discount_payload['discount_value'])) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Diskon Gagal Ditambahkan',
                'message' => 'Tipe atau nilai diskon tidak boleh kosong'
            ]);
            return;
        }

        $this->list_product[$index]['discount_type'] = $this->item_discount_payload['discount_type'];
        $this->list_product[$index]['discount_value'] = $this->item_discount_payload['discount_value'];

        $this->dispatch('renderAlert', [
            'icon' => 'success',
            'title' => 'Diskon Berhasil Ditambahkan'
        ]);
    }

    public function add_custom_product()
    {
        $isLoad = $this->isFirstLoad;

        if ($isLoad) {
            $this->isFirstLoad = false;
        }

        $this->dispatch('renderModalCustomProduct', [
            'isFirstLoad' => $isLoad
        ]);
    }

    public $item_custom_payload = [];

    public function update_item_custom($id)
    {
        $item = ProductIngredient::find($id);

        $this->item_custom_payload = [
            'id' => $id,
            'name' => $item->name . ' (' . $item->unit->name . ')',
            'price' => $item->price,
            'amount' => $this->item_custom_payload['amount'] ?? 0
        ];

        $this->dispatch('renderModalItemCustomPrice', [
            'price' => $this->item_custom_payload['price'],
        ]);
    }

    public function update_item_custom_price($value)
    {
        $value = (int) str_replace('.', '', $value);

        $this->item_custom_payload['price'] = $value;
    }

    public function update_item_custom_amount($value)
    {
        $this->item_custom_payload['amount'] = $value;
    }

    public function submit_item_custom()
    {
        $ingredient = ProductIngredient::find($this->item_custom_payload['id']);
        $availableStock = IngredientStock::where('ingredient_id', $ingredient->id)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->sum('amount_available');

        // check if amount is empty
        if (empty($this->item_custom_payload['amount'])) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Item Gagal Ditambahkan',
                'message' => 'Jumlah item tidak boleh kosong'
            ]);
            return;
        }

        // check if price is empty
        if (empty($this->item_custom_payload['price'])) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Item Gagal Ditambahkan',
                'message' => 'Harga item tidak boleh kosong'
            ]);
            return;
        }

        // check stock
        if ($availableStock < $this->item_custom_payload['amount'] && $this->transaction_type == 1) {
            $this->dispatch('renderAlert', [
                'icon' => 'error',
                'title' => 'Item Gagal Ditambahkan',
                'message' => 'Stock ' . $ingredient->name . ' tidak mencukupi'
            ]);
            return;
        }

        $this->list_product[] = [
            'ingredient_id' => $ingredient->id,
            'product_id' => null,
            'variant_id' => null,
            'name' => $this->item_custom_payload['name'],
            'buy_price' => (int) $ingredient->price,
            'sell_price' => (int) $this->item_custom_payload['price'],
            'active_price' => (int) $this->item_custom_payload['price'],
            'sell_retail_price' => null,
            'amount' => (int) $this->item_custom_payload['amount'],
            'code' => null,
            'discount_type' => null,
            'discount_value' => null
        ];

        $this->item_custom_payload = [];

        $this->dispatch('renderAlert', [
            'icon' => 'success',
            'title' => 'Item Berhasil Ditambahkan'
        ]);
    }

    public $titleStockNotEnough = '';
    public $listStockNotEnough = [];

    public function showModalStockDetail($index)
    {
        $this->titleStockNotEnough = $this->list_product[$index]['name'];

        $listIngredients = ProductIngredient::whereIn('id', array_keys($this->list_product[$index]['ingredient_stock_not_enough']))->pluck('name', 'id')->toArray();
        $this->listStockNotEnough = [];
        foreach ($listIngredients as $id => $name) {
            $this->listStockNotEnough[] = [
                'name' => $name,
                'amount' => $this->list_product[$index]['ingredient_stock_not_enough'][$id]
            ];
        }

        $this->dispatch('renderModalStockDetail');
    }
}
