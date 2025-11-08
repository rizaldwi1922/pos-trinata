@extends('layouts.dashboard.app')

@section('title', 'Dashboard Laporan Keuangan')

@section('content')
    {{-- Header --}}
    <div class="header bg-white p-6 rounded-xl shadow mb-6">
        <h1 id="dashboard-title" class="text-2xl font-semibold mb-2">Dashboard Laporan Keuangan</h1>
        <p id="company-name" class="text-gray-500">Agung Mandiri</p>
    </div>

    {{-- Filter --}}

    {{-- Statistik --}}
    @include('layouts.dashboard.partials.stats')

    {{-- Grafik --}}
    @include('layouts.dashboard.partials.charts')

    {{-- Tabel --}}
    @include('layouts.dashboard.partials.tables')

    {{-- Biaya Operasional --}}
    @include('layouts.dashboard.partials.expenses')
@endsection
