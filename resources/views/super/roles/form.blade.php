@extends('layouts.admin.app')

@php
    $is_create = !isset($role);
@endphp

@section('title', (!$is_create ? 'Edit' : 'Tambah') . ' Role')

@section('content')
    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush
    <div class="card mt-4">
        <form action="{{ !$is_create ? route('super.roles.update', $role->id) : route('super.roles.store') }}"
            class="form-control" method="POST">
            @csrf
            @if (!$is_create)
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ !$is_create ? 'Edit' : 'Tambah' }}
                    Role Pengguna</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $role->name ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="permissions" class="form-label">Hak Akses <span class="text-danger">*</span></label>
                    <select class="form-select" id="permissions" name="permissions[]" multiple required>
                        @foreach ($permissions as $permission)
                            <option value="{{ $permission->id }}" @if (!$is_create && $role->permissions->contains($permission->id)) selected @endif>
                                {{ $permission->name }}</option>
                        @endforeach
                    </select>
                </div>

                <script>
                    $(document).ready(function() {
                        $('#permissions').select2();
                    });
                </script>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
