@spaceless
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Point Of Sales</title>
        <meta name="description" content="Point Of Sales" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

        <link href="{{ url('assets/app/pos/css/stylec619.css?v=1.0') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('assets/app/pos/api/pace/pace-theme-flat-top.css') }}" rel="stylesheet" type="text/css" />
        <link rel="apple-touch-icon" href="{{ url('/assets/main/images/apple-touch-icon.png') }}">
        <link rel="stylesheet" href="{{ asset('assets/app/pos/css/pos.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" type="text/css">
        <script src="{{ asset('assets/js/jquery.js') }}"></script>

        @livewireStyles
    </head>

    <body id="tc_body" class="header-fixed header-mobile-fixed subheader-enabled aside-enabled aside-fixed">

        @livewire('point-of-sales')

        <script src="{{ url('assets/app/pos/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ url('assets/app/pos/js/pos.js') }}"></script>

        @livewireScripts

    </body>

    </html>
@endspaceless
