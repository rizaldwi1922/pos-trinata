@extends('layouts.dashboard.app')

@section('title', 'Dashboard Laporan Keuangan')

@section('content')
    {{-- Header --}}
    <div class="header bg-white p-6 rounded-xl shadow mb-6 flex items-center justify-between">
        {{-- Tombol Keluar di sebelah kiri --}}
        <div class="flex items-center gap-4">
            {{-- 🔹 Opsi A: Keluar ke halaman utama --}}
            <a href="{{ url('/admin/landing') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg shadow transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Keluar
            </a>

            {{-- Judul Dashboard --}}
            <div>
                <h1 id="dashboard-title" class="text-2xl font-semibold mb-2">Dashboard Laporan Keuangan</h1>
                <p id="company-name" class="text-gray-500">Agung Mandiri</p>
            </div>
        </div>
    </div>

    {{-- Filter --}}

    {{-- Statistik --}}
    @include('layouts.dashboard.partials.stats')

    {{-- Grafik --}}
    @include('layouts.dashboard.partials.charts')

@endsection
