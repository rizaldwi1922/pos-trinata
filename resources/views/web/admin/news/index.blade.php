@extends('layouts.admin.app')

@section('title', 'Daftar Blog')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Blog</h4>
                </div>
                @if ($blade_user->hasPermissionTo('can_create_webmin_news'))
                    <div class="col-md-6 text-end">
                        <a href="{{ route('web.admin.news.create') }}" class="btn btn-default"><i class="bx bx-plus"></i> &nbsp;Tambah blog</a>
                    </div>
                @endif
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
                        <th scope="col">Judul</th>
                        <th scope="col">Gambar</th>
                        <th scope="col">Konten</th>
                        <th scope="col">Terakhir diperbaharui</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $item->title }}</td>
                            <td><img src="@viewfile($item->image)" onerror="this.src='{{ asset($item->image) }}'" width="50px" alt="">
                            <td style="word-wrap: break-word; white-space: pre-wrap;">{{ str()->limit($item->content, 40) }}</td>
                            <td>{{ Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}
                                <small class="text-muted">{{ Carbon\Carbon::parse($item->created_at)->format('H:i') }}
                                    WIB</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    @include('layouts.admin.partials.act_edit', ['title' => 'Blog', 'href' => route('web.admin.news.edit', $item->id), 'permission' => 'news'])
                                    @include('layouts.admin.partials.act_delete', ['title' => 'Blog', 'href' => route('web.admin.news.destroy', $item->id), 'permission' => 'news'])
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
