@extends('layouts.admin.app')

@section('title', 'Riwayat Shift')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Shift</h4>
                </div>
            </div>
            <form class="mt-2" action="" method="GET">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group d-flex align-items-center">
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Pencarian ...">
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
                        <th scope="col">Pegawai</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Saldo Awal</th>
                        <th scope="col">Saldo Akhir</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $item->user_name }}</td>
                            <td>{{ $item->start_shift_at->translatedFormat('d F Y') }} <small
                                class="text-muted">{{ $item->start_shift_at->format('H:i') }} WIB</small></td>
                            <td>{{ $item->end_shift_at ? $item->end_shift_at->translatedFormat('d F Y') : '-' }} <small
                                class="text-muted">{{ $item->end_shift_at ? ($item->end_shift_at->format('H:i') . ' WIB') : '' }}</small></td>
                            <td>@currency($item->amount_start)</td>
                            <td>@currency($item->amount_end)</td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    @include('layouts.admin.partials.act_show', ['title' => 'Shift', 'href' => route('admin.shifts.show', $item->id), 'permission' => 'shifts'])
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
