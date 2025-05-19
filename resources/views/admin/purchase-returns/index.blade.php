@extends('layouts.admin.app')

@section('title', 'Daftar Pembelian')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Retur Barang</h4>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-default"><i class="bx bx-plus"></i>
                        &nbsp;Tambah Retur</a>
                </div>
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
                        <th scope="col">No</th>
                        <th scope="col">Tanggal Retur</th>
                        <th scope="col">Nominal Retur</th>
                        <th scope="col">Supplier</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ Carbon\Carbon::parse($item->return_date)->translatedFormat('d F Y') }}
                            </td>
                            <td>@currency($item->total_amount)</td>
                            <td>{{$item->supplier->name}}</td>
                            <td>
                                <td>
                                    <ul class="list-inline hstack gap-2 mb-0">
                                        <li class="list-inline-item" data-bs-toggle="tooltip" title="Lihat Detail">
                                            <a href="{{ route('admin.receivables.show', $item->id) }}" 
                                               class="text-primary d-inline-block">
                                                <i class="bx bx-show-alt fs-16"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </td>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">
            @include('layouts.admin.partials.pagination', ['data' => $data])
        </div>
    </div>

@endsection
