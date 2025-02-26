@extends('layouts.admin.app')

@section('title', 'Barcode Produk')

@section('content')
    <style>
        @media print {
            @page {
                size: 11.7in 16.5in;
                size: landscape;
            }
        }
    </style>
    <div class="col-12 row">
        @foreach ($products as $item)
            <div class="col-2 text-center">
                <span> Loading... </span>
                <img style="max-width: 200px;"
                    src="https://barcode.tec-it.com/barcode.ashx?data={{ $item['code'] }}&code=Code128&dpi=96" alt="barcode"
                    onload="this.previousElementSibling.remove()" />
                <br>
                @if ($item['measurement'])
                    <span>{{ $item['name'] . ' (' . $item['measurement'] . ' ' . $item['unit_name'] . ')' }}</span>
                @else
                    <span>{{ $item['name'] }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <script>
        window.print();
    </script>
@endsection
