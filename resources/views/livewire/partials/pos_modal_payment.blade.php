<div class="modal fade text-left" id="payment-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel11"
    style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel11">
                    <b>Pembayaran </b>
                </h3>
                <button type="button" class="close rounded-pill btn btn-sm btn-icon btn-light btn-hover-primary m-0"
                    data-dismiss="modal" aria-label="Close">
                    <svg width="20px" height="20px" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <table class="table right-table">
                    <tbody>
                        <tr class="align-items-center">
                            <th class="border-0 px-0 font-size-lg mb-0 font-size-bold text-primary">
                                Jumlah
                                Yang
                                Harus Diterima : </th>
                            <td class="border-0 justify-content-end d-flex text-primary font-size-lg font-size-bold px-0 font-size-lg mb-0 text-primary"
                                id="total_final_dialog" data-amount="{{ $totalAmount }}" style="font-size: 20px;">
                                @currency($totalAmount)
                            </td>
                        </tr>

                        <tr class="align-items-center">
                            <th class="border-0 px-0 font-size-lg mb-0 font-size-bold text-primary">
                                Pilih Jenis Transaksi: </th>
                            <td
                                class="border-0 justify-content-end d-flex text-primary font-size-lg font-size-bold px-0 font-size-lg mb-0 text-primary">
                                <select wire:model="transaction_type" id="transaction-type" onchange="calculate()"
                                    style="width:100%;border:none;padding:10px">
                                    <option value="1" @if ($transaction_type == '1') selected @endif>Penjualan
                                    </option>
                                    <option value="2" @if ($transaction_type == '2') selected @endif>Pre-Order
                                    </option>
                                </select>
                            </td>
                        </tr>

                        <tr class="align-items-center">
                            <th class="border-0 px-0 font-size-lg mb-0 font-size-bold text-primary">
                                Pilih
                                Pelanggan
                                : <br><a href="javascript::void(0)" data-toggle="modal" data-target="#form-customer-modal" onclick="$('#payment-popup').modal('hide');">+ Tambah Pelanggan</a>
                            </th>
                            <td
                                class="border-0 justify-content-end text-primary font-size-lg font-size-bold px-0 font-size-lg mb-0 font-size-bold text-primary">
                                <select name="customer_id" wire:model="customer_id"
                                    onchange="renderCustomerBalance(this)" style="width:100%;border:none;padding:10px">
                                    <option value="" selected>-- Pilih Pelanggan --</option>
                                    @foreach ($customers as $key => $customer)
                                        <option value="{{ $customer->id }}" data-balance="{{ $customer->balance }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <div id="balance-info" style="display: none; margin-left: 13px;">
                                    <div class="balance-info">
                                        <span>Saldo: <span id="balance-customer">Rp0</span></span>
                                    </div>
                                    <div style="margin-top: 3px;">
                                        <input type="checkbox" id="use-balance" name="use_balance" value="1"
                                            wire:model="isUseCustomerBalance" onchange="calculate()">
                                        <label for="use-balance" style="margin-left: 3px;">Gunakan Saldo</label>
                                    </div>
                                </div>

                                <script>
                                    function renderCustomerBalance(el) {
                                        let balance = el.options[el.selectedIndex].getAttribute('data-balance')

                                        if (balance > 0) {
                                            $('#balance-info').show()
                                            $('#balance-customer').html(currency(balance))
                                        } else {
                                            $('#balance-info').hide()
                                        }
                                    }
                                </script>
                            </td>
                        </tr>

                        <tr class=" align-items-center">
                            <th class="border-0 px-0 font-size-lg mb-0 font-size-bold text-primary">
                                Pilih Metode
                                Pembayaran
                                : </th>
                            <td class="border-0 justify-content-end d-flex text-primary font-size-lg font-size-bold px-0 font-size-lg mb-0 font-size-bold text-primary"
                                id="payment_method">
                                <select name="type_payment" style="width:100%;border:none;padding:10px"
                                    wire:model="payment_method_id">
                                    @foreach ($listPaymentMethod as $obj)
                                        <option value="{{ $obj->id }}">{{ $obj->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>

                        <tr class="align-items-center">
                            <th class="border-0 px-0 font-size-lg mb-0 font-size-bold text-primary">
                                Pilih Diskon: </th>
                            <td
                                class="border-0 justify-content-end d-flex text-primary font-size-lg font-size-bold px-0 font-size-lg mb-0 font-size-bold text-primary">
                                <select wire:model="discount_type" id="discount-type" onchange="calculate()"
                                    style="width:100%;border:none;padding:10px">
                                    <option value="" selected>-- Pilih Tipe Diskon --</option>
                                    <option value="1" @if ($discount_type == '1') selected @endif>
                                        Persentase (%)</option>
                                    <option value="2" @if ($discount_type == '2') selected @endif>Nominal
                                        (Rp)</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="form-group row hidden" id="tr_discount_view">
                    <div class="col-md-12 wrap-barcode">
                        <label class="text-body"><b>Besaran Diskon :</b></label>
                        <fieldset class="form-group mb-3">
                            <input type="text" class="form-control number-format" wire:model="discount_value" id="discount-value"
                                onchange="calculate()" placeholder="Masukan jumlah besaran diskon">
                        </fieldset>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-12 wrap-barcode">
                        <label class="text-body"><b>Jumlah Uang Diterima :</b></label>
                        <fieldset class="form-group mb-3">
                            <input type="text" class="form-control number-format" wire:model="amount_received"
                                name="amount_received" id="money-received" onchange="calculate()"
                                style="font-size: 18px;">
                        </fieldset>

                        {{-- <div class="barcode-wrap hidden">
                            <label class="text-body">Barcode Pembayaran</label>
                            <div class="barcode-payment">
                                <img src="https://www.berkabarnews.com/foto_berita/3IMG_20210228_175025.jpg"
                                    alt="">
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex;flex-direction:wrap;justify-content:space-between;">
                <div style="width: 70%;">
                    <div class="p-3 bg-light-dark d-flex justify-content-between border-bottom "
                        style="border-radius: 4px !important;">
                        <h5 class="font-size-bold mb-0" id="txt_act_payment">Kembalian :</h5>
                        <h5 class="font-size-bold mb-0" id="return_amount" style="font-size: 20px;">Rp0</h5>
                    </div>
                </div>
                <div style="width:27%;">
                    <button type="button" class="btn float-right" wire:click="submitPayment()"
                        data-count="{{ count($list_product) }}" id="submitBtn"
                        style="background-color:#2469a5;font-weight:bold;color:white;width:100%;">Bayar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('keyup', '.number-format', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value.replace(/\B(?=(\d{3})+(?!\d))/g, "."));
    });

    function calculate() {
        let totalAmount = parseInt($('#total_final_dialog').attr('data-amount'));
        let discount_type = $('#discount-type').val();
        let discountValueRaw = $('#discount-value').val();

        if (discountValueRaw) {
            discountValueRaw = discountValueRaw.replaceAll(/\./g, '');

            if (discount_type == 1 && parseInt(discountValueRaw) > 100) {
                $('#discount-value').val('100');
                discountValueRaw = '100';
            }

            if (discount_type == 2 && parseInt(discountValueRaw) > totalAmount) {
                $('#discount-value').val(totalAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                discountValueRaw = totalAmount;
            }
        }

        let discount_value = parseInt(discountValueRaw) || 0;

        if (discount_type == 1) {
            $('#tr_discount_view').show();
            let discount = totalAmount * (discount_value / 100);
            discount = Math.round(discount);
            totalAmount -= discount;
        } else if (discount_type == 2) {
            $('#tr_discount_view').show();
            totalAmount -= discount_value;
        } else {
            $('#tr_discount_view').hide();
        }

        if (totalAmount < 0) {
            totalAmount = 0;
        }

        let uBalance = 0;
        let customerBalance = $('#balance-customer').html();

        if ($('#use-balance').is(':checked')) {
            if (customerBalance) {
                customerBalance = customerBalance.replace('Rp', '').replace(/\./g, '');
                uBalance = parseInt(customerBalance) || 0;
            }
        }

        let valAmount = parseInt($('#money-received').val().replaceAll(/\./g, '')) || 0;
        let receivedAmount = parseInt(valAmount) || 0;
        let result = (receivedAmount + uBalance) - totalAmount;

        if (result < 0) {
            $('#txt_act_payment').html('Kekurangan:');
            $('#return_amount').html(currency(result * -1));
        } else {
            if (totalAmount > receivedAmount) {
                $('#txt_act_payment').html('Kembalian:');
                $('#return_amount').html(currency(0));
            } else {
                $('#txt_act_payment').html('Kembalian:');
                $('#return_amount').html(currency(receivedAmount - totalAmount));
            }
        }

        let isNotEmpty = $('#submitBtn').attr('data-count') > 0;
        $('#submitBtn').attr('disabled', !isNotEmpty);
    }
</script>
