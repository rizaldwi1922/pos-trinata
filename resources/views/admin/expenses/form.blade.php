@extends('layouts.admin.app')

@section('title', (isset($expense) ? 'Edit' : 'Tambah') . ' Pengeluaran')

@section('content')

    <div class="card mt-4">
        @if (isset($expense))
            <form action="{{ route('admin.expenses.update', $expense->id) }}" class="form-control" method="POST">
                @csrf
                @method('PUT')
                <div class="card-header">
                    <h4 class="card-title
                    d-flex align-items-center">
                        Edit Pengeluaran</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Pengeluaran<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ $expense->name ?? '' }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Nominal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control number-format" id="amount" name="amount"
                            value="{{ $expense->amount ?? '' }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date"
                            value="{{ Carbon\Carbon::parse($expense->date)->format('Y-m-d') ?? '' }}" required>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-default">Simpan</button>
                    </div>
                </div>
            </form>
        @else
            <form action="{{ route('admin.expenses.store') }}" class="form-control" method="POST">
                @csrf
                <div class="card-header">
                    <h4 class="card-title
                d-flex align-items-center">Tambah Pengeluaran</h4>
                </div>

                <div class="card-body invoice-padding pb-0">
                    <div class="col-12">
                        <div class="mb-1">
                            <label class="form-label">
                                Tanggal Pengeluaran <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="date" placeholder="Tanggal" required>
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
                                                            <th>Nama Pengeluaran <span class="text-danger">*</span></th>
                                                            <th>Nominal <span class="text-danger">*</span></th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr style="display: none">
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    placeholder="Masukan nama pengeluaran" name="names[]"
                                                                    required>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex text-nowrap align-items-center gap-1">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm number-format"
                                                                        placeholder="Masukan nominal" name="amounts[]"
                                                                        required>
                                                                </div>
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
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm w-100"
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

                <div class="mb-3">
                    <button type="button" onclick="submitForm()" class="btn w-100 btn-default">Simpan</button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function dupplicateRow() {
            var table = document.getElementById("table-product");
            var row = table.rows[1].cloneNode(true);

            row.style.display = "";
            table.appendChild(row);
        }

        function submitForm() {
            var date = document.getElementsByName('date')[0];
            if (date.value == '') {
                Swal.fire({
                    icon: 'info',
                    title: 'Perhatian',
                    text: 'Tanggal tidak boleh kosong!',
                });
                return;
            }

            var names = document.getElementsByName('names[]');
            var amounts = document.getElementsByName('amounts[]');

            for (let i = 0; i < names.length; i++) {
                if (i == 0 && names.length > 1) {
                    continue;
                }

                if (names[i].value == '' || amounts[i].value == '') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Perhatian',
                        text: 'Nama pengeluaran dan nominal tidak boleh kosong!',
                    });
                    return;
                }
            }

            document.querySelector('.form-control').submit();
        }
    </script>
@endsection
