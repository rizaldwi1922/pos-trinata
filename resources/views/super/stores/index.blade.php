@extends('layouts.admin.app')

@section('title', 'Daftar Toko')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Toko</h4>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('super.stores.create') }}" class="btn btn-default"><i class="bx bx-plus"></i>
                        &nbsp;Tambah Toko</a>
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
                        <th scope="col">Nama</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Gambar</th>
                        <th scope="col">Terakhir diperbaharui</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->code }}</td>
                            <td><img src="@viewfile($item->image)" onerror="this.src='{{ asset($item->image) }}'" width="50px"
                                    alt=""> </td>
                            <td>{{ Carbon\Carbon::parse($item->updated_at)->translatedFormat('d F Y') }}
                                <small class="text-muted">{{ Carbon\Carbon::parse($item->updated_at)->format('H:i') }}
                                    WIB</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    @include('layouts.admin.partials.act_edit', [
                                        'title' => 'Toko',
                                        'href' => route('super.stores.edit', $item->id),
                                    ])
                                    <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Login As">
                                        <a href="{{ route('super.login_as', $item->id) }}"
                                            class="text-muted d-inline-block">
                                            <i class="bx bx-key"></i>
                                        </a>
                                    </li>
                                    <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Edit Akun">
                                        <a href="{{ route('super.users.edit', App\Models\User::where('store_id', $item->id)->orderBy('id', 'ASC')->first()->id) }}"
                                            class="text-muted d-inline-block">
                                            <i class="bx bx-user"></i>
                                        </a>
                                    </li>
                                    @include('layouts.admin.partials.act_delete', [
                                        'title' => 'Toko',
                                        'href' => route('super.stores.destroy', $item->id),
                                    ])
                                </ul>
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
