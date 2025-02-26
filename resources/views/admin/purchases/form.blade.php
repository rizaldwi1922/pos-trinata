@extends('layouts.admin.app')

@section('title', 'Tambah Supplier')

@section('content')

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="card mt-4">
        <form action="{{ route('admin.purchases.store') }}" class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">Tambah Pembelian</h4>
            </div>

            <div class="card-body invoice-padding pb-0">
                <div class="col-12">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-1">
                                <label class="form-label">
                                    Invoice ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="invoice_number" placeholder="Invoice ID"
                                    required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-1">
                                <label class="form-label">
                                    Jatuh Tempo
                                </label>
                                <input type="date" class="form-control" name="due_date" placeholder="Jatuh Tempo">
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
                                                        <th>Supplier <span class="text-danger">*</span></th>
                                                        <th>Kode</th>
                                                        <th>Jumlah <span class="text-danger">*</span></th>
                                                        <th>Kadaluwarsa</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr style="display: none">
                                                        <td>
                                                            <select class="form-select select2 form-select-sm mt-50"
                                                                style="width: 250px;" name="items[]" required>
                                                                <option value="">Pilih Varian / Bahan
                                                                </option>
                                                                @foreach ($variants as $key => $value)
                                                                    <option value="{{ $key }}">
                                                                        V - {{ $value }}
                                                                    </option>
                                                                @endforeach
                                                                @foreach ($ingredients as $key => $value)
                                                                    <option value="{{ $key }}">
                                                                        I - {{ $value }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select select2 form-select-sm mt-50"
                                                                style="width: 250px;" name="suppliers[]" required>
                                                                <option value="">Pilih Supplier</option>
                                                                @foreach ($suppliers as $key => $value)
                                                                    <option value="{{ $key }}">
                                                                        {{ $value }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                placeholder="Kode" name="codes[]">
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
                                                            <input type="date" class="form-control form-control-sm"
                                                                placeholder="Kadaluwarsa" name="expiry_dates[]">
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                style="background: red; color:white; width: 30px; height: 30px; margin-left: 10px;"
                                                                onclick="this.closest('tr').remove()">
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
                                    <input type="text" class="form-control number-format" name="total" placeholder="ex. 120000"
                                        required>
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
        $('.select2').select2();

        function dupplicateRow() {
            var table = document.getElementById("table-product");
            var row = table.rows[1].cloneNode(true);

            // remove select2
            var select2 = row.querySelectorAll('.select2-container--default');
            select2.forEach(function(item) {
                item.remove();
            });

            row.style.display = "";
            table.appendChild(row);
            $('.select2').select2();
        }

        function submitForm() {
            var invoiceNumber = document.querySelector('input[name="invoice_number"]').value;
            var grandTotal = document.querySelector('input[name="total"]').value;
            if (invoiceNumber == '' || grandTotal == '') {
                Swal.fire({
                    icon: 'info',
                    title: 'Perhatian!',
                    text: 'Field tidak boleh kosong!',
                });
                return;
            }

            document.querySelector('form').submit();
        }
    </script>

@endsection
