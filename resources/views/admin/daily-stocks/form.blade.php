@extends('layouts.admin.app')

@section('title', 'Edit Stok Opname')

@section('content')

    <div class="card mt-4">
        <form action="{{ route('admin.daily-stocks.update', $data->id) }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">Edit Stok Opname</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger"> (Readonly)</span></label>
                    <input type="text" class="form-control" value="{{ $data->name ?? '' }}"
                        readonly>
                </div>

                <div class="mb-3">
                    <label for="amount_start" class="form-label">Stok Terdata <span class="text-danger"> (Readonly)</span></label>
                    <input type="text" class="form-control" id="amount_start" value="{{ $data->amount_start ?? '' }}"
                        readonly>
                </div>

                <div class="mb-3">
                    <label for="amount_end" class="form-label">Stok Fisik <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="amount_end" name="amount_end" value="{{ $data->amount_end ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
