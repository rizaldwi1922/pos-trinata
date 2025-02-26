<div>
    @include('livewire.partials.pos_header')

    <head>
        <script src="{{ asset('assets/vendor/js/sweetalert.all.js') }}"></script>
    </head>

    <div class="contentPOS">
        <div class="container-fluid">
            @php
                $sub_total = 0;
            @endphp

            <div id="loading" wire:loading
                wire:target="change_category,add_product,change_price,update_item_product,remove_product,submit_retur_item,submit_deposit">
                <div id="status">
                    <i class="mdi mdi-timer-sand-complete"></i>
                </div>
            </div>

            <div class="content-pos-full">
                <div class="container-fluid no-gutters">
                    <div class="row no-gutters">
                        <div class="col-6 col-lg-6 col-xl-7" style="position: relative">
                            <div class="camera-scann-preview" id="reader"></div>
                            <div class="inventory">
                                <div class="category-sale">
                                    {{-- @desktop
                                    @elsedesktop
                                        <div class="camera-scanner" id="camera-scanner" data-isOpen="false"
                                            onclick="location.href='opencamera:'">
                                            <i class="mdi mdi-barcode-scan"></i>
                                        </div>
                                    @enddesktop --}}
                                    <div class="wrap-category-sale">
                                        <div wire:click="change_category(null)"
                                            class="list-category {{ $category_id == null ? 'active' : '' }}">Semua</div>
                                        @foreach ($categories as $key => $category)
                                            <div wire:click="change_category('{{ $category->id }}')"
                                                class="list-category {{ $category->id == $category_id ? 'active' : '' }}">
                                                {{ $category->name }}</div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="search-sale">
                                    <div class="wrap-search-sale"
                                        style="display: flex; justify-content: center; align-items: center;">
                                        <div class="search-product" style="width: 100%; display: flex;">
                                            <input type="text" class="form-control" placeholder="Cari Produk"
                                                wire:model.live..debounce.500ms="search"
                                                style="background-color: white;">

                                            <div wire:click="add_custom_product" class="text-center button"
                                                style="background: #2469a5; color:white; border-radius: 2px;">
                                                <i class="mdi mdi-plus"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="item-sell-wrap">
                                    <div class="item-sell">
                                        <div class="row no-gutters">
                                            @foreach ($products as $key => $product)
                                                <div class="col-4 col-lg-4 col-xl-3"
                                                    wire:click="add_product({{ json_encode($product) }}, '{{ !empty($product->variant_id) ? 1 : 0 }}')">
                                                    <div class="box-item-sell">
                                                        <div class="thumbnail-product">
                                                            @php
                                                                $is_url = false;
                                                                $image_url = $product->image;

                                                                if ($image_url) {
                                                                    if (
                                                                        strpos($image_url, 'http') !== false ||
                                                                        strpos($image_url, 'https') !== false
                                                                    ) {
                                                                        $is_url = true;
                                                                    }
                                                                }
                                                            @endphp
                                                            <img src="@if ($product->image) @if ($is_url) {{ asset($image_url) }} @else
                                                                @viewfile($product->image) @endif
@else
{{ asset('assets/app/pos/images/product-default.png') }} @endif"
                                                                onerror="this.src='{{ asset('assets/app/pos/images/product-default.png') }}'"
                                                                alt="">
                                                        </div>
                                                        <div class="detail-product">
                                                            <div class="name-product">{{ $product->name }} <br>
                                                                @if ($product->amount_available == 0)
                                                                    <span class="text-danger">(Stok Habis)</span>
                                                                @else
                                                                    <span class="text-muted">
                                                                        @if (!empty($product->variant_id))
                                                                            {{ $product->measurement }}
                                                                            {{ $product->unit_name }}
                                                                        @else
                                                                            Produk Bahan @endif
                                                                        @if ($product->amount_available)
                                                                            @if ($product->amount_available < $stockMinimum)
                                                                                <span class="text-danger">(Sisa
                                                                                    {{ $product->amount_available }})</span>
                                                                            @else
                                                                                ({{ $product->amount_available }})
                                                                            @endif
                                                                        @else
                                                                            ({{ $product->amount_available }})
                                                                        @endif
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="price-product">
                                                                @if ($product->sell_retail_price)Normal:
                                                                @endif @currency($product->sell_price)
                                                            </div>
                                                            @if ($product->sell_retail_price)
                                                                <div class="price-product" style="margin-top: 0px;">
                                                                    Grosir: @currency($product->sell_retail_price)
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-6 col-lg-6 col-xl-5">
                            <div class="selected-item">
                                <div class="selected-item-wrap" id="selected-item">
                                    @php
                                        $totalAmount = 0;
                                    @endphp
                                    @foreach ($list_product as $key => $product)
                                        @if ($key == count($list_product) - 1)
                                            <script type="text/javascript">
                                                let wrap_selected_item = null;
                                                wrap_selected_item = document.getElementById("selected-item")
                                                wrap_selected_item.scrollTop = wrap_selected_item.scrollHeight * 10;
                                            </script>
                                        @endif

                                        @if (!is_null($product))
                                            <div class="list-item-checkout">
                                                <div class="name-item-checkout">
                                                    <div class="name">{{ $product['name'] }}
                                                        @if (!empty($product['variant_id']))
                                                            ({{ $product['measurement'] }}
                                                            {{ $product['unit_name'] }})
                                                        @endif
                                                    </div>
                                                    @if ($product['code'])
                                                        <div class="code">Kode Seri: {{ $product['code'] }}</div>
                                                    @else
                                                        <div class="code">Item Kustom</div>
                                                    @endif

                                                    @if (isset($product['ingredient_stock_not_enough']) && count($product['ingredient_stock_not_enough']) > 0)
                                                        <div class="code"
                                                            style="color: red; text-decoration: underline;"
                                                            wire:click="showModalStockDetail('{{ $key }}')">
                                                            Kekurangan Stok Bahan</div>
                                                    @endif

                                                    @if ($product['sell_retail_price'])
                                                        <select style="width:100%; margin-bottom: 10px;"
                                                            wire:change="change_price('{{ $product['variant_id'] }}', {{ $product['product_id'] }}, $event.target.value)">
                                                            <option value="{{ $product['sell_price'] }}" selected>
                                                                Normal: @currency($product['sell_price'])</option>
                                                            <option value="{{ $product['sell_retail_price'] }}">Grosir:
                                                                @currency($product['sell_retail_price'])</option>
                                                        </select>
                                                    @endif
                                                </div>
                                                <div class="count-item-checkout">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;x
                                                    <input type="number" class="total-chart-item"
                                                        wire:change="update_item_product('{{ $product['variant_id'] }}', '{{ $product['product_id'] }}', '{{ $product['ingredient_id'] }}', $event.target.value)"
                                                        value="{{ $product['amount'] }}">
                                                    <div class="btn btn-discount @if (!empty($product['discount_type'])) red @endif"
                                                        wire:click="open_discount_modal({{ $key }})">
                                                        <i class="mdi mdi-percent"></i>
                                                    </div>
                                                </div>
                                                <div class="sumof-item-checkout">
                                                    @if (!empty($product['discount_type']))
                                                        <div class="price-discount">
                                                            <del>@currency($product['active_price'] * $product['amount'])</del>
                                                        </div>
                                                        <div class="price-ori">
                                                            @php
                                                                $newPrice = 0;
                                                                if ($product['discount_type'] == 1) {
                                                                    $newPrice =
                                                                        $product['active_price'] * $product['amount'] -
                                                                        ($product['active_price'] *
                                                                            $product['amount'] *
                                                                            $product['discount_value']) /
                                                                            100;
                                                                } else {
                                                                    $newPrice =
                                                                        $product['active_price'] * $product['amount'] -
                                                                        $product['discount_value'];
                                                                }

                                                                if ($newPrice < 0) {
                                                                    $newPrice = 0;
                                                                }
                                                            @endphp
                                                            <b>@currency($newPrice)</b>
                                                        </div>
                                                    @else
                                                        <div class="price-ori">
                                                            <b>@currency($product['active_price'] * $product['amount'])</b>
                                                        </div>
                                                    @endif

                                                    @php
                                                        $totalAmount += $product['active_price'] * $product['amount'];

                                                        if (!empty($product['discount_type'])) {
                                                            if ($product['discount_type'] == 1) {
                                                                $totalAmount -=
                                                                    ($product['active_price'] *
                                                                        $product['amount'] *
                                                                        $product['discount_value']) /
                                                                    100;
                                                            } else {
                                                                $totalAmount -= $product['discount_value'];
                                                            }

                                                            if ($totalAmount < 0) {
                                                                $totalAmount = 0;
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="remove"
                                                        wire:click="remove_product({{ json_encode($product) }})">
                                                        <i class="mdi mdi-minus"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="payment-wrap">
                                    <div class="sumof-all-item-checkout">
                                        <div class="label">Total</div>
                                        <div class="count"><b>@currency($totalAmount)</b></div>
                                    </div>
                                    <div class="action-trigger-web">
                                        <div class="action clear" wire:click="clear_list()" style="font-size:24px;">
                                            <i class="mdi mdi-trash-can"></i>
                                        </div>
                                        <div class="action pay"
                                            @if (!$shift) onclick="startShift()" @else data-toggle="modal" data-target="#payment-popup"
                                            onclick="calculate()" @endif>
                                            Bayar
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('livewire.partials.pos_modal_payment')

            @include('livewire.partials.pos_modal_add_product_scanner')

            @include('livewire.partials.pos_modal_history_transaction')

            @include('livewire.partials.pos_modal_history_preorder')

            @include('livewire.partials.pos_modal_return_transaction')

            @include('livewire.partials.pos_modal_discount_item')

            @include('livewire.partials.pos_modal_custom_product')

            @include('livewire.partials.pos_modal_form_customer')

            @include('livewire.partials.pos_modal_detail_stock')

            <script>
                $('#input-id-to-chart-modal').click(function() {
                    $('#confirm-product').modal('hide');
                    $('#amount-item-chart-modal').val(1);
                })

                function send(val) {
                    let selfLocation = `${location.href}?barcode=${val}`;
                    $.ajax({
                        url: selfLocation,
                        method: 'GET',
                        success: (res) => {
                            $('#title-chart-modal').html(res.name)
                            $('#code-chart-modal').html(res.code)
                            $('#price-chart-modal').html(`${currency(res.sell_price)}`)
                            $('#image-chart-modal').attr('src', res.image)

                            $('#confirm-product').modal('show');
                            $('#input-id-to-chart-modal').val(res.is_variant ? ('variant_id,' + res.variant_id) : (
                                'product_id,' + res.product_id));
                        },
                        error: (err) => {
                            Swal.fire({
                                title: 'Gagal',
                                text: 'Produk tidak ditemukan!',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            })
                        }
                    })
                }

                function currency(num) {
                    return 'Rp' + num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
                }

                let total = 0;
            </script>

            @if (!$shift)
                <script>
                    function startShift() {
                        Swal.fire({
                            title: 'Mulai Shift',
                            html: `<div class="form-group">
                                <input type="text" class="form-control number-format" id="amount" placeholder="Masukkan jumlah uang">
                                </div>`,
                            showCancelButton: false,
                            confirmButtonText: 'Mulai',
                            confirmButtonColor: '#2469a5',
                            allowOutsideClick: true,
                            showLoaderOnConfirm: true,
                            preConfirm: (login) => {
                                const amount = Swal.getPopup().querySelector('#amount').value
                                if (!amount) {
                                    Swal.showValidationMessage(`Masukkan jumlah uang`)
                                }
                                return {
                                    amount: amount
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var amount = result.value.amount;
                                @this.call('start_shift', amount);
                            }
                        })
                    }
                </script>
            @endif

            <script>
                $(document).ready(function() {
                    window.addEventListener('renderModalShiftOut', event => {
                        showShiftModal(event.detail[0].amount_shift_end);
                    })

                    window.addEventListener('renderAlert', event => {
                        // hide all modal
                        let modals = document.querySelectorAll('.modal');
                        modals.forEach(modal => {
                            $(modal).modal('hide');
                        });

                        // show alert
                        Swal.fire({
                            title: event.detail[0].title,
                            text: event.detail[0].message,
                            icon: event.detail[0].icon,
                            confirmButtonColor: '#2469a5',
                            confirmButtonText: 'OK'
                        })
                    })

                    window.addEventListener('renderModalStockDetail', event => {
                        $('#stock-detail-modal').modal('show');
                    })

                    window.addEventListener('renderModalHistoryTransaction', event => {
                        if (event.detail[0].isFirstLoad) {
                            @this.call('get_history_transaction');
                        } else {
                            $('#history-transaction-modal').modal('show');
                        }
                    })

                    window.addEventListener('renderModalHistoryPreorder', event => {
                        if (event.detail[0].isFirstLoad) {
                            @this.call('get_history_preorder');
                        } else {
                            $('#history-preorder-modal').modal('show');
                        }
                    })

                    window.addEventListener('renderModalPaymentPreorder', event => {
                        $('#history-preorder-modal').modal('hide');
                        Swal.fire({
                            title: 'Bayar Pre-Order\n' + event.detail[0].transactionRef,
                            html: `<div class="form-group">
                        <input type="text" class="form-control number-format" id="amount" placeholder="Masukkan jumlah uang" value="${event.detail[0].amountLess.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}">
                        </div>`,
                            showCancelButton: true,
                            confirmButtonText: 'Selesai',
                            confirmButtonColor: '#2469a5',
                            cancelButtonText: 'Batal',
                            showLoaderOnConfirm: true,
                            preConfirm: (login) => {
                                const amount = Swal.getPopup().querySelector('#amount').value
                                if (!amount) {
                                    Swal.showValidationMessage(`Masukkan jumlah uang`)
                                }
                                return {
                                    amount: amount
                                }
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var amount = result.value.amount;
                                @this.call('submit_payment_preorder', event.detail[0].id + '/' + amount);
                            }
                        })
                    })

                    window.addEventListener('renderModalReturTransaction', event => {
                        $('#history-transaction-modal').modal('hide');
                        $('#retur-transaction-modal').modal('show');
                    })

                    window.addEventListener('renderModalItemDiscount', event => {
                        if (event.detail[0].withReset) {
                            $('#item_discount_type').val('');
                            $('#item_discount_value').val('');
                        }
                        $('#item-discount-modal').modal('show');
                    })

                    window.addEventListener('renderModalCustomProduct', event => {
                        if (event.detail[0].isFirstLoad) {
                            @this.call('add_custom_product');
                        } else {
                            $('#item_custom_price').val('');
                            $('#item_custom_amount').val('');
                            $('#item_custom_ingredient').val('');
                            $('#item-custom-product-modal').modal('show');
                        }
                    })

                    window.addEventListener('renderModalItemCustomPrice', event => {
                        $('#item_custom_price').val(event.detail[0].price.toString().replace(
                            /\B(?=(\d{3})+(?!\d))/g, "."));
                    })

                    window.addEventListener('renderTransaction', event => {
                        $('#payment-popup').modal('hide');

                        if (event.detail[0].afterDeposit == false) {
                            if (event.detail[0].withDeposit) {
                                Swal.fire({
                                    title: 'Saldo Kembalian',
                                    text: 'Apakah anda ingin deposit saldo kembalian sebesar ' + currency(
                                        event.detail[0].depositBalance) + '?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#2469a5',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Deposit',
                                    cancelButtonText: 'Tidak'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        @this.call('submit_deposit', event.detail[0].depositPayload);
                                    } else {
                                        alertTransaction(event.detail[0].route);
                                    }
                                })
                            } else {
                                alertTransaction(event.detail[0].route);
                            }
                        } else {
                            Swal.fire({
                                title: 'Deposit Berhasil',
                                text: 'Saldo deposit sebesar ' + currency(event.detail[0].depositBalance) +
                                    ' berhasil ditambahkan ke saldo pelanggan: ' + event.detail[0]
                                    .customerName,
                                icon: 'success',
                                confirmButtonColor: '#2469a5',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    alertTransaction(event.detail[0].route);
                                }
                            })
                        }
                    })
                })

                function alertTransaction(url) {
                    Swal.fire({
                        title: 'Transaksi Berhasil',
                        text: 'Apakah anda ingin mencetak struk?',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#2469a5',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Cetak',
                        cancelButtonText: 'Tidak'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let win = window.open(url, '_blank');
                            win.focus();
                        }
                    })
                }

                function showShiftModal(amount_shift_end) {
                    Swal.fire({
                        title: 'Akhiri Shift',
                        html: `<div class="form-group">
                        <input type="text" class="form-control number-format" id="amount" placeholder="Masukkan jumlah uang" value="${amount_shift_end.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}">
                        </div>`,
                        showCancelButton: true,
                        confirmButtonText: 'Selesai',
                        confirmButtonColor: '#2469a5',
                        cancelButtonText: 'Batal',
                        showLoaderOnConfirm: true,
                        preConfirm: (login) => {
                            const amount = Swal.getPopup().querySelector('#amount').value
                            if (!amount) {
                                Swal.showValidationMessage(`Masukkan jumlah uang`)
                            }
                            return {
                                amount: amount
                            }
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var amount = result.value.amount;
                            @this.call('end_shift', amount);
                        }
                    })
                }
            </script>
        </div>
    </div>
</div>
