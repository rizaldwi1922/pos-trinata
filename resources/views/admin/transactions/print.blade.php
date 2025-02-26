<html lang="en">
<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>Struk {{ $transaction->trx_id }}</title>
    <style>
        * {
            font-size: 10pt;
        }

        body {
            background-color: white;
            font-family: serif;
        }

        #struk {
            @if ($from != 'api')
                width: 30%;
            @endif
            margin: 0px auto;
            min-height: 100px;
            padding: 10px 5px;
        }

        .header-struk {
            width: 100%;
            padding: 12px 0px;
            text-align: center;
        }

        .logo {
            font-size: 18px;
            font-weight: bold;
        }

        .alamat {
            font-size: 14px;
            margin: 16px 0px;
        }

        .informasi-header {
            width: 100%;
            margin: 12px 0px;
            justify-content: center;
            border-bottom: 2px dashed black;
            padding-bottom: 12px;
        }

        .informasi-header .info {
            width: 100%;
            text-align: center;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .body-struk {
            width: 100%;
            min-height: 10px;
            margin: 0px 0px 6px 0px;
            padding-bottom: 12px;
            border-bottom: 2px dashed black;
        }

        .list-product-struk {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            min-height: 5px;
            margin-bottom: 16px;
        }

        .list-product-struk .no {
            width: 5%;
            text-align: left;
        }

        .list-product-struk .item {
            width: 95%;
        }

        .list-product-struk .space {
            width: 35%;
        }

        .list-product-struk .amount {
            width: 15%;
            text-align: center;
        }

        .list-product-struk .price {
            width: 15%;
        }

        .list-product-struk .total {
            width: 35%;
            text-align: right;
        }

        .footer-struk {
            width: 100%;
            padding: 4px 0px;
            margin-top: 12px;
        }

        .list-footer-info {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            min-height: 5px;
            margin-bottom: 6px;
        }

        .list-footer-info .space {
            width: 10%;
        }

        .list-footer-info .label {
            width: 30%;
            text-align: right;
        }

        .list-footer-info .value {
            width: 60%;
            text-align: right;
        }

        .footer-message {
            width: 100%;
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
        }

        @media print {
            body {
                padding: 0px;
                width: 100%;
            }

            #struk {
                width: 70%;
                margin-right: 30%;
                padding: 32px 0px;
            }

            .logo {
                font-size: 12pt;
                text-align: center;
            }

            .alamat,
            .footer-message {
                font-size: 8pt;
                text-align: center;
            }

            .informasi-header .info {
                font-size: 8pt;
            }

            .list-product-struk {
                font-size: 4pt;
            }

            .list-footer-info {
                font-size: 6pt;
            }

            .no-print,
            .no-print * {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div id="struk" id="struk">
        <div class="header-struk">
            @if ($settings['receipt_logo'])
                <div class="logo" style="margin-bottom: 10px;">
                    <img @if (isset($settings['receipt_logo_image']) && $settings['receipt_logo_image']) @if ($from != 'api')
                        src="@viewfile($settings['receipt_logo_image'], public)"
                    @else
                        src="{{ $settings['receipt_logo_image'] }}" @endif
                        @endif width="{{ $settings['receipt_logo_size'] ?? 0 }}px" alt="logo">
                </div>
            @endif
            <div class="logo">{{ $store->name }}</div>
            <div class="alamat">{{ $store->address }}</div>
            <div class="informasi-header">
                <div class="info">{{ $transaction->created_at->translatedFormat('d F Y') }}</div>
                <div class="info">Pukul: {{ $transaction->created_at->format('H:i') }} WIB</div>
                <div class="info">Order ID: {{ $transaction->trx_id }}</div>
                <div class="info">Kasir: {{ $transaction->shift->user->name }}</div>
                @if ($transaction->customer_id != null)
                    <div class="info">Pelanggan: {{ App\Models\Customer::find($transaction->customer_id)->name }}
                    </div>
                @endif
            </div>
        </div>
        <div class="body-struk">
            @php
                $transaction_product = $transaction->data;
            @endphp
            @foreach ($transaction_product as $key => $product)
                <div class="list-product-struk">
                    <div class="no">{{ 1 + $key }}</div>
                    <div class="item">{{ $product['name'] }}</div>
                    @if ($product['amount'] > 0)
                        <div class="amount">{{ $product['amount'] }} x </div>
                        <div class="price">@currency($product['sell_price'])</div>
                    @else
                        <div class="amount">- Retur</div>
                        <div class="price"></div>
                    @endif
                    <div class="space"></div>
                    @if (!empty($product['discount_value']))
                        <div class="total">
                            <strike>@currency(($product['sell_price'] * $product['amount']))</strike><span>&nbsp;&nbsp;@currency(($product['sell_price'] * $product['amount']) - $product['discount_value'])</span>
                        </div>
                    @else
                        <div class="total">@currency(($product['sell_price'] * $product['amount']))</div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="footer-struk">
            <div class="footer-info">
                @if ($transaction->amount_retur > 0)
                    <div class="list-footer-info">
                        <div class="space">&nbsp;</div>
                        <div class="label">Total Retur:</div>
                        <div class="value">@currency($transaction->amount_retur)</div>
                    </div>
                @endif
                <div class="list-footer-info">
                    <div class="space">&nbsp;</div>
                    <div class="label">Total:</div>
                    <div class="value">@currency($transaction->amount_total - $transaction->amount_retur)</div>
                </div>
                @if ($transaction->amount_discount > 0)
                    <div class="list-footer-info">
                        <div class="space">&nbsp;</div>
                        <div class="label">Total Diskon:</div>
                        <div class="value">@currency($transaction->amount_discount)</div>
                    </div>
                @endif
                <div class="list-footer-info">
                    <div class="space">&nbsp;</div>
                    <div class="label">Bayar:</div>
                    <div class="value">@currency($transaction->amount_received)</div>
                </div>
                <div class="list-footer-info">
                    <div class="space">&nbsp;</div>
                    <div class="label">Metode Pembayaran:</div>
                    <div class="value">{{ $transaction->paymentMethod->name }}</div>
                </div>
                @php
                    if (request()->has('after_debt')) {
                        $amount_back = request()->get('after_debt');
                    }
                @endphp
                @if (isset($amount_back))
                    <div class="list-footer-info">
                        <div class="space">&nbsp;</div>
                        <div class="label">Cicil Hutang:</div>
                        <div class="value">@currency($amount_back)</div>
                    </div>
                @endif
                <div class="list-footer-info">
                    <div class="space">&nbsp;</div>
                    @if ($transaction->amount_deposit > 0)
                        <div class="label">Deposit:</div>
                        <div class="value">@currency($transaction->amount_deposit)</div>
                    @else
                        @php
                            $change = $transaction->amount_received - $transaction->amount_total;

                            $title = $change < 0 ? 'Kekurangan' : 'Kembalian';
                            $change = $change < 0 ? $change * -1 : $change;

                            if (isset($amount_back)) {
                                $change = $change - $amount_back;
                            }
                        @endphp
                        <div class="label">{{ $title }}:</div>
                        <div class="value">
                            @if ($title == 'Kembalian')
                                @currency($change + $transaction->amount_retur)
                            @else
                                @currency($change)
                            @endif
                        </div>
                    @endif
                </div>
                @if ($transaction->customer_id != null)
                    @php
                        $amount_less = \App\Models\Transaction::where('customer_id', $transaction->customer_id)->sum(
                            'amount_less',
                        );
                    @endphp
                    @if ($amount_less > 0)
                        <div class="list-footer-info">
                            <div class="space">&nbsp;</div>
                            <div class="label">Total Sisa Hutang:</div>
                            <div class="value">@currency(\App\Models\Transaction::where('customer_id', $transaction->customer_id)->sum('amount_less'))</div>
                        </div>
                    @endif
                @endif
            </div>
            <div class="footer-message">
                Terima kasih telah berbelanja di {{ $store->name }}, kehadiranmu akan kami tunggu kembali
            </div>

            @if ($from != 'api')
                <br>
                <div class="no-print" id="back">
                    <button style="width: 100%; height: 30px;" @if ($from == 'pos') onclick="history.go(-1)" @else onclick="location.href='{{ route('admin.transactions.index') }}'" @endif>Kembali</button>
                </div>

                <div class="no-print" style="margin-top: 10px;" id="print">
                    @mobile
                    <button style="width: 100%; height: 40px;" onclick="location.href='printbluetooth:'">Print
                        Struk</button>
                    @else
                    <button style="width: 100%; height: 40px;" onclick="window.print()">Print
                        Struk</button>
                    @endmobile
                </div>
            @endif

        </div>
    </div>
</body>

</html>
