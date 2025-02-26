@extends('layouts.admin.app')

@section('title', (isset($store) ? 'Edit' : 'Tambah') . ' Toko')

@section('content')

    <div class="card mt-4">
        <form action="{{ isset($store) ? route('super.stores.update', $store->id) : route('super.stores.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($store))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ isset($store) ? 'Edit' : 'Tambah' }} Toko</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $store->name ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" value="{{ $store->code ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Gambar {!! isset($store) ? '' : '<span class="text-danger">*</span>' !!}</label>
                    <input class="form-control" type="file" id="image" name="image" accept="image/*" {{ isset($store) ? '' : 'required' }}>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Alamat </label>
                    <textarea class="form-control" id="address" name="address">{{ $store->address ?? '' }}</textarea>
                </div>

                @if (!isset($store))
                    <hr> <br>
                    <h5 class="card-title d-flex align-items-center">Tambahkan Akun</h5>
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="user_name" name="user_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">Pilih Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @if (isset($user) && $user->roles->first()->id == $role->id) selected @endif>
                                    {{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="user_email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="user_email" name="user_email" required>
                    </div>

                    <div class="mb-3">
                        <label for="user_password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="user_password" name="user_password" required>
                    </div>
                @endif

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
