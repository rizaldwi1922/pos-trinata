<div class="container-fluid app-navbar mt-3">
    <div class="card"
        style="background: none; border: none; box-shadow: none; margin-bottom: -30px; margin-top: -10px;">
        <div class="card-header d-flex justify-content-between">
            <div>
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none ">
                    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                        <i class="bx bx-menu bx-sm"></i>
                    </a>
                </div>
                @php
                    $breadcrumbs = explode('/', Request::path());
                @endphp
                @foreach ($breadcrumbs as $key => $breadcrumb)
                    @if ($key == 0)
                        <a href="{{ route('admin.dashboard') }}"
                            class="text-primary text-decoration-none">{{ ucfirst($breadcrumb) }}</a>
                    @else
                        <span
                            class="text-muted
                        text-decoration-none">{{ ucfirst(str_replace('-', ' ', $breadcrumb)) }}</span>
                    @endif
                    @if ($key != count($breadcrumbs) - 1)
                        <span class="text-muted
                        text-decoration-none">&nbsp;/&nbsp;</span>
                    @endif
                @endforeach
            </div>

            <div>
                <button onclick="location.reload();" class="btn btn-primary rounded-pill text-white">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>
