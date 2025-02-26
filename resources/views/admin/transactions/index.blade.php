@extends('layouts.admin.app')

@section('title', 'Riwayat Transaksi')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Transaksi</h4>
                </div>
                @if ($blade_user->hasPermissionTo('can_delete_admin_products'))
                    <div class="col-md-6 text-end">
                        <a href="javascript::void(0)" onclick="deleteSelectedItem()" id="deleteAllBtn"
                            class="btn d-none btn-danger"><i class="bx bx-trash"></i> &nbsp;Hapus
                            Transaksi</a>
                    </div>
                @endif
            </div>
            <form class="mt-2" action="" method="GET">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group d-flex align-items-center">
                            <input type="text" class="form-control" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Pencarian ...">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">
                            <div class="form-check form-switch">
                                <input class="form-check
                                    form-check-switch"
                                    type="checkbox" id="select_all_table">
                            </div>
                        </th>
                        <th scope="col">No</th>
                        <th scope="col">Kode Transaksi</th>
                        <th scope="col">Pelanggan</th>
                        <th scope="col">Pembayaran</th>
                        <th scope="col">Hutang</th>
                        <th scope="col">Jumlah Total</th>
                        <th scope="col">Tanggal Waktu</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td>
                                <div class="form-check
                                    form-check-switch">
                                    <input class="form-check
                                        form-check-switch"
                                        type="checkbox" id="select_all" data-value="{{ $item->id }}">
                                </div>
                            </td>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $item->trx_id }}</td>
                            <td>{{ $item->customer->name ?? '-' }}</td>
                            <td>{{ $item->paymentMethod->name }}</td>
                            <td>@currency($item->amount_less)</td>
                            <td>@currency($item->amount_total)</td>
                            <td>
                                {{ Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }} <small
                                    class="text-muted">{{ Carbon\Carbon::parse($item->created_at)->format('H:i') }}
                                    WIB</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Print">
                                        <a href="{{ route('admin.transactions.show', $item->id) }}?from=dashboard"
                                            target="_blank" class="text-muted d-inline-block">
                                            <i class="bx bx-printer"></i>
                                        </a>
                                    </li>
                                    @include('layouts.admin.partials.act_delete', [
                                        'title' => 'Transaksi',
                                        'href' => route('admin.transactions.destroy', $item->id),
                                        'permission' => 'transactions',
                                    ])
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <form action="{{ route('admin.transactions.delete.multiple') }}" id="delete_all_form" method="POST">
            @csrf
            <input type="hidden" name="ids" id="ids">
        </form>
        <script>
            document.getElementById('select_all_table').addEventListener('change', function() {
                var checkboxes = document.querySelectorAll('.form-check-switch');
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });

                if (this.checked) {
                    document.getElementById('deleteAllBtn').classList.remove('d-none');
                } else {
                    document.getElementById('deleteAllBtn').classList.add('d-none');
                }
            });

            document.querySelectorAll('.form-check-switch').forEach((checkbox) => {
                checkbox.addEventListener('change', function() {
                    var checkboxes = document.querySelectorAll('.form-check-switch');
                    var selected = [];
                    checkboxes.forEach((checkbox) => {
                        if (checkbox.checked) {
                            selected.push(checkbox.getAttribute('data-value'));
                        }
                    });

                    selected = selected.filter(function(el) {
                        return el != null;
                    });

                    if (selected.length > 0) {
                        document.getElementById('deleteAllBtn').classList.remove('d-none');
                    } else {
                        document.getElementById('deleteAllBtn').classList.add('d-none');
                    }
                });
            });

            function deleteSelectedItem() {
                var checkboxes = document.querySelectorAll('.form-check-switch');
                var selected = [];
                checkboxes.forEach((checkbox) => {
                    if (checkbox.checked) {
                        selected.push(checkbox.getAttribute('data-value'));
                    }
                });

                selected = selected.filter(function(el) {
                    return el != null;
                });

                if (selected.length > 0) {
                    Swal.fire({
                        title: 'Hapus Transaksi',
                        text: 'Apakah Anda yakin ingin menghapus transaksi yang dipilih?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('ids').value = selected.join(',');
                            document.getElementById('delete_all_form').submit();
                        }
                    });
                }
            }
        </script>
        <div class="footer">
            @include('layouts.admin.partials.pagination', ['data' => $data])
        </div>
    </div>

@endsection
