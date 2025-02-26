<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title') | Panel</title>

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    @livewireStyles

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/sweetalert.all.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
</head>

<body>
    @if ($assetOnly ?? false)
        @yield('content')
    @else
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                @include('layouts.admin.sidebar')
                <div class="layout-page">
                    @include('layouts.admin.navbar')
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="layout-page">
            @include('layouts.admin.footer')
        </div>
    @endif

    @include('layouts.admin.partials.alert')

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('scripts')

    <script>
        function formatNumberInput(element) {
            var value = element.value.replace(/[^0-9]/g, '');
            element.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        document.addEventListener('keyup', function(event) {
            if (event.target.classList.contains('number-format')) {
                formatNumberInput(event.target);
            }
        });

        document.querySelectorAll('.number-format').forEach(function(element) {
            formatNumberInput(element);
        });
    </script>

    <script>
        function insertParam(key, value) {
            key = encodeURIComponent(key);
            value = encodeURIComponent(value);

            var kvp = document.location.search.substr(1).split('&');
            let i = 0;

            for (; i < kvp.length; i++) {
                if (kvp[i].startsWith(key + '=')) {
                    let pair = kvp[i].split('=');
                    pair[1] = value;
                    kvp[i] = pair.join('=');
                    break;
                }
            }

            if (i >= kvp.length) {
                kvp[kvp.length] = [key, value].join('=');
            }

            let params = kvp.join('&');

            document.location.search = params;
        }

        var table = document.querySelector('.table-responsive.text-nowrap');
        if (table) {
            var tbody = table.querySelector('tbody');
            if (tbody.children.length == 0) {
                var tr = document.createElement('tr');
                var td = document.createElement('td');
                td.setAttribute('style', 'text-align: center;');
                td.setAttribute('colspan', 100);
                td.textContent = 'Tidak ada data';
                tr.appendChild(td);
                tbody.appendChild(tr);
            }
        }

        function openBarcode(code) {
            Swal.fire({
                title: 'Barcode',
                html: `<span>Loading...</span> <img src="https://barcode.tec-it.com/barcode.ashx?data=${code}&code=Code128&dpi=96" alt="barcode" onload="this.previousElementSibling.remove()"/>`,
                showCloseButton: true,
                showConfirmButton: false,
            });
        }
    </script>

    @livewireScripts

</body>

</html>
