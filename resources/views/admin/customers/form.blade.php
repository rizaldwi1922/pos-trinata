@extends('layouts.admin.app')

@section('title', (isset($customer) ? 'Edit' : 'Tambah') . ' Pelanggan')

@section('content')

    <div class="card mt-4">
         <div class="card-header">
            <a href="/admin/customers" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                </svg>
                Kembali</a>
        </div>
        <form action="{{ isset($customer) ? route('admin.customers.update', $customer->id) : route('admin.customers.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($customer))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ isset($customer) ? 'Edit' : 'Tambah' }} Pelanggan</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $customer->name ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ $customer->phone ?? '' }}">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="{{ $customer->email ?? '' }}">
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
