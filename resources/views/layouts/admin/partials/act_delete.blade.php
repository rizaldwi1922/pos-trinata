@php
    $prefix = $blade_user->hasRole('web-admin') ? 'webmin' : 'admin';
@endphp
@if (!isset($permission) || isset($permission) && $blade_user->hasPermissionTo('can_delete_' . $prefix . '_' . $permission))
    <li class="list-inline-item delete" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
        title="Hapus {{ $title }}" data-confirm-delete="true">
        <a href="{{ $href }}" data-confirm-delete="true" class="text-muted d-inline-block">
            <i class="bx bx-trash-alt"></i>
        </a>
    </li>
@endif
