<div class="filter-section bg-white p-5 rounded-xl shadow mb-6 flex flex-wrap gap-4">
    <div class="filter-group">
        <label for="period-filter">Periode Analisis</label>
        <select id="period-filter" class="p-2 border rounded">
            <option value="daily">Harian</option>
            <option value="weekly">Mingguan</option>
            <option value="monthly" selected>Bulanan</option>
        </select>
    </div>

    <div class="filter-group">
        <label for="date-filter">Tanggal</label>
        <select id="date-filter" class="p-2 border rounded">
            <option value="today">Hari Ini</option>
            <option value="yesterday">Kemarin</option>
            <option value="last7days" selected>7 Hari Terakhir</option>
            <option value="last30days">30 Hari Terakhir</option>
            <option value="thismonth">Bulan Ini</option>
        </select>
    </div>

    <div class="filter-group">
        <label for="year-filter">Tahun</label>
        <select id="year-filter" class="p-2 border rounded">
            @for ($y = now()->year; $y >= 2020; $y--)
                <option value="{{ $y }}" {{ $y == 2025 ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
</div>
