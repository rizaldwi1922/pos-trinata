@php
    use App\Models\Menu;

    $user = auth()->user();

    $menus = $user->hasRole('super-admin')
        ? Menu::getSuperAdminMenus()
        : ($user->hasRole('web-admin')
            ? Menu::getWebAdminMenus()
            : Menu::getAdminMenus());

    $prefix = $user->hasRole('web-admin') ? 'webmin' : 'admin';
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="" class="app-brand-link col-12 d-flex align-items-center justify-content-center">
            <span class="app-brand-text demo menu-text fw-bolder" style="text-transform: capitalize !important;">
                <img src="{{ asset('assets/img/ic_logo_new.png') }}" style="width: 120px; image-orientation: none;" alt="">
            </span>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menus as $menu)
            @php
                // check for menu permission
                if (isset($menu['permission'])) {
                    $key = str_replace('-', '_', $menu['permission']);
                    if (!$user->hasPermissionTo('can_view_' . $prefix . '_' . $key)) {
                        continue;
                    }
                }
            @endphp
            @if (isset($menu['separator']) && $menu['separator'])
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $menu['label'] }}</span>
                </li>
            @elseif (isset($menu['child']))
                <li class="menu-item {{ $menu['name'] }}">
                    <a href="javascript::void(0)" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons {{ $menu['icon'] }}"></i>
                        <div>{{ $menu['name'] }}</div>
                    </a>
                    <ul class="menu-sub">
                        @foreach ($menu['child'] as $childMenu)
                            @php
                                // check for child menu permission
                                if (isset($childMenu['permission'])) {
                                    $key = str_replace('-', '_', $childMenu['permission']);
                                    if (!$user->hasPermissionTo('can_view_' . $prefix . '_' . $key)) {
                                        continue;
                                    }
                                }
                            @endphp
                            <li class="menu-item">
                                <a href="{{ $childMenu['route'] }}" class="menu-link">
                                    <div>{{ $childMenu['name'] }}</div>
                                    @if (isset($childMenu['badge']) && $childMenu['badge'])
                                        @php
                                            $badge = $childMenu['badge'];
                                            $badgeClass = 'badge bg-label-' . ($badge['color'] ?? 'primary') . ' fs-tiny rounded-pill ms-auto';
                                        @endphp
                                        <div class="{{ $badgeClass }}">{{ $badge['name'] }}</div>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @else
                <li class="menu-item {{ $menu['name'] }}" style="text-transform: capitalize">
                    <a href="{{ isset($menu['route']) ? $menu['route'] : rand() }}" class="menu-link">
                        <i class="menu-icon tf-icons {{ $menu['icon'] }}"></i>
                        <div>{{ $menu['name'] }}</div>
                        @if (isset($menu['badge']) && $menu['badge'])
                            @php
                                $badge = $menu['badge'];
                                $badgeClass = 'badge bg-label-' . ($badge['color'] ?? 'primary') . ' fs-tiny rounded-pill ms-auto';
                            @endphp
                            <div class="{{ $badgeClass }}">{{ $badge['name'] }}</div>
                        @endif
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</aside>

<script>
    const sidebarMenus = document.querySelectorAll('.menu-item');
    sidebarMenus.forEach((item) => {
        let currentUrl = window.location.href;
        currentUrl = currentUrl.split('?')[0];
        if (item.querySelector('a').href == currentUrl) {
            item.classList.add('active');
        }

        if (item.querySelector('ul')) {
            item.querySelector('ul').querySelectorAll('li').forEach((childItem) => {
                if (childItem.querySelector('a').href == currentUrl) {
                    item.classList.add('active');
                    item.classList.add('open');
                }
            });
        }
    });

    // if menu-header-text is not have next <li> then remove it
    const menuHeaders = document.querySelectorAll('.menu-header');
    menuHeaders.forEach((item) => {
        if (!item.nextElementSibling || item.nextElementSibling.classList.contains('menu-header')) {
            item.remove();
        }
    });
</script>
