@extends('layouts.admin.app')

@section('title', (isset($user) ? 'Edit' : 'Tambah') . ' Karyawan')

@section('content')

    @php
        $route = auth()->user()->hasRole('super-admin') ? 'super.users' : 'admin.users';
    @endphp

    <div class="card mt-4">
        <form action="{{ isset($user) ? route($route . '.update', $user->id) : route($route . '.store') }}" class="form-control"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($user))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ isset($user) ? 'Edit' : 'Tambah' }}
                    Karyawan</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $user->name ?? '' }}"
                        required>
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
                    <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="email" name="email"
                        value="{{ $user->email ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password {!! isset($user) ? '(isi jika ingin mengganti password)' : '<span class="text-danger">*</span>' !!}</label>
                    <input type="password" class="form-control" id="password" name="password"
                        {{ isset($user) ? '' : 'required' }}>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
