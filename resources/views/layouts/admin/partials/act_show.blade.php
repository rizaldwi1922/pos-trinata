@php
    $prefix = $blade_user->hasRole('web-admin') ? 'webmin' : 'admin';
@endphp
@if (!isset($permission) || isset($permission) && $blade_user->hasPermissionTo('can_view_' . $prefix . '_' . $permission))
    <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
        title="Detail {{ $title }}">
        <a href="{{ $href }}" class="text-muted d-inline-block">
            <i class="bx bx-show"></i>
        </a>
    </li>
@endif
