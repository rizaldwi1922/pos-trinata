@php
    $prefix = $blade_user->hasRole('web-admin') ? 'webmin' : 'admin';
@endphp
@if (!isset($permission) || isset($permission) && $blade_user->hasPermissionTo('can_update_' . $prefix . '_' . $permission))
    <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
        title="Edit {{ $title }}">
        <a href="{{ $href }}" class="text-muted d-inline-block">
            <i class="bx bx-edit"></i>
        </a>
    </li>
@endif
