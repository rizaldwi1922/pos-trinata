@extends('layouts.admin.app')

@section('title', 'Laporan Penjualan')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <h4 class="card-title mb-0">Laporan Penjualan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.transactions.show') }}">
                <div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                </div>
                <div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mt-4">
                    <button type="submit" class="btn btn-default btn-label right ms-auto"><i
                            class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Lihat Laporan</button>
                </div>
            </form>
        </div>
    </div>

@endsection
