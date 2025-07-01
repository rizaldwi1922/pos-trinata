@extends('layouts.admin.app')

@section('title', 'Tambah Supplier')

@section('content')

    <div class="card mt-4">
        <form action="{{ route('admin.purchase-returns.store') }}" class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">Tambah Retur</h4>
            </div>

            <div class="card-body invoice-padding pb-0">
                <div class="col-12">
                    <div class="row">
                        
                        <div class="col-6">
                            <div class="mb-1">
                                <label class="form-label">
                                    Supplier <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-md"
                                    name="supplier" required>
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliers as $val)
                                        <option value="{{ $val->id }}">
                                            {{ $val->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-1">
                                <label class="form-label">
                                    Tanggal Retur
                                </label>
                                <input type="date" class="form-control" name="retur_date" placeholder="Tanggal Retur">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body invoice-padding invoice-product-details">
                <div data-repeater-list="group-a" class="card-wrapper-content">
                    <div class="card-body card-body-invoice p-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="card mt-1 mb-0">
                                    <div class="card-body p-0">
                                        <div class="table-responsive mt-0">
                                            <table class="table table-striped" id="table-product">
                                                <thead>
                                                    <tr>
                                                        <th>Varian / Bahan <span class="text-danger">*</span></th>
                                                        <th>Harga</th>
                                                        <th>Jumlah <span class="text-danger">*</span></th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr style="">
                                                        <td>
                                                            <select class="form-select select2 form-select-sm mt-50"
                                                                style="width: 250px;" name="items[]" required>
                                                                <option value="">Pilih Varian / Bahan
                                                                </option>
                                                                @foreach ($variants as $value)
                                                                    <option value="{{ $value->id }}" data-price="{{ $value->unit_price }}">
                                                                        {{ $value->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        {{-- <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                placeholder="Kode" name="codes[]">
                                                        </td> --}}
                                                        <td>
                                                            <input type="text"
                                                                class="number-format form-control form-control-sm"
                                                                name="price[]" placeholder="ex. 120000" required>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex text-nowrap align-items-center gap-1">
                                                                x
                                                                <input type="number" min="0"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Jumlah" name="amounts[]" value="1"
                                                                    required>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                style="background: red; color:white; width: 30px; height: 30px; margin-left: 10px;">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="col-12 mt-2">
                                                <div class="w-100">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100"
                                                        onclick="dupplicateRow()">
                                                        <i class="bx bx-plus"> </i> Tambah Item
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body invoice-padding">
                <div class="row invoice-sales-total-wrapper">
                    <div class="col-md-6 order-md-1 order-2 mt-md-0 mt-3">
                    </div>
                    <div class="col-md-6 d-flex justify-content-end order-md-2 order-1">
                        <div class="invoice-total-wrapper">
                            <div class="invoice-total-item">
                                <p class="invoice-total-title">Total: <span class="text-danger">*</span></p>
                                <div class="form-group">
                                    <h3 id="grand-total">0</h3>
                                    <input type="hidden" class="form-control number-format" name="total" id="total"
                                        placeholder="ex. 120000" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" onclick="submitForm()" class="btn w-100 btn-default">Simpan</button>
            </div>
        </form>
    </div>

    <script>
        // Fungsi untuk menghitung total per baris dan total keseluruhan
        function calculateTotal() {
            let totalPrice = 0;

            document.querySelectorAll("#table-product tbody tr").forEach(row => {
                let priceInput = row.querySelector("input[name='price[]']");
                let amountInput = row.querySelector("input[name='amounts[]']");
                let price = parseFloat(priceInput.value.replace(/[^0-9]/g, "")) || 0;
                let amount = parseInt(amountInput.value) || 0;

                let rowTotal = price * amount;
                console.log(price);
                totalPrice += rowTotal;
            });

            document.getElementById("grand-total").textContent = formatRupiah(totalPrice);
            document.getElementById("total").value = totalPrice;
        }

        // Event listener untuk input harga dan jumlah
        document.addEventListener("input", function(event) {
            if (event.target.matches("input[name='price[]'], input[name='amounts[]']")) {
                calculateTotal();
            }
        });

        // Event listener untuk hapus baris
        document.addEventListener("click", function(event) {
            if (event.target.closest(".btn-danger")) {
                event.target.closest("tr").remove();
                calculateTotal(); // Hitung ulang setelah baris dihapus
            }
        });

        // Format angka menjadi rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(angka);
        }

        $('.select2').select2();

        function dupplicateRow() {
            var table = document.getElementById("table-product").getElementsByTagName('tbody')[0];
            var row = table.rows[0].cloneNode(true);

            // Reset nilai input pada baris baru
            row.querySelectorAll('input').forEach(input => {
                input.value = input.name === "amounts[]" ? 1 : null;
            });

            // Pastikan select2 berfungsi di baris baru
            row.querySelectorAll('.select2-container').forEach(select => select.remove());
            table.appendChild(row);
            $('.select2').select2();

            calculateTotal(); // Hitung ulang total setelah menambah baris
        }

        function submitForm() {
            // var grandTotal = document.querySelector('input[name="total"]').value;
            // if (invoiceNumber == '' || grandTotal == '') {
            //     Swal.fire({
            //         icon: 'info',
            //         title: 'Perhatian!',
            //         text: 'Field tidak boleh kosong!',
            //     });
            //     return;
            // }

            document.querySelector('form').submit();
        }

        // Add after $('.select2').select2();
        $(document).on('change', 'select[name="items[]"]', function() {
            const selectedOption = $(this).find(':selected');
            const price = selectedOption.data('price');
            const priceInput = $(this).closest('tr').find('input[name="price[]"]');
            
            if(price) {
                priceInput.val(price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                calculateTotal();
            }
        });
    </script>

@endsection
