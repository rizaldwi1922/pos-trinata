@extends('layouts.admin.app')

@section('title', (isset($supplier) ? 'Edit' : 'Tambah') . ' Supplier')

@section('content')

    <div class="card mt-4">
        <form action="{{ isset($supplier) ? route('admin.suppliers.update', $supplier->id) : route('admin.suppliers.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($supplier))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ isset($supplier) ? 'Edit' : 'Tambah' }} Supplier</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ $supplier->phone ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="email" name="email" value="{{ $supplier->email ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="address" name="address" required>{{ $supplier->address ?? '' }}</textarea>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
