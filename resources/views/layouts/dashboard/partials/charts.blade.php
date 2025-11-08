<div class="chart-card bg-white p-4 rounded-xl shadow w-full">
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h3 class="font-semibold text-lg">Grafik Penjualan, HPP & Laba</h3>
        <div class="flex flex-wrap items-center gap-2">
            <input type="date" id="start-date" class="p-2 border rounded text-sm" />
            <span class="text-gray-500">s/d</span>
            <input type="date" id="end-date" class="p-2 border rounded text-sm" />

            <button id="filter-date" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                Filter
            </button>

            <button id="prev-period" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                ← Sebelumnya
            </button>
            <button id="next-period" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                Berikutnya →
            </button>

            <select id="summary-chart-period" class="p-2 border rounded text-sm">
                <option value="daily" selected>Harian</option>
                <option value="weekly">Mingguan</option>
                <option value="monthly">Bulanan</option>
                <option value="yearly">Tahunan</option>
            </select>
        </div>
    </div>

    <div class="chart-container w-full h-[400px]">
        <canvas id="summarySalesChart" class="w-full"></canvas>
    </div>
</div>

<div class="chart-card bg-white p-4 rounded-xl shadow w-full mt-6">
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h3 class="font-semibold text-lg">Grafik Pendapatan Bersih</h3>
        <div class="flex flex-wrap items-center gap-2">
            <input type="date" id="net-start-date" class="p-2 border rounded text-sm" />
            <span class="text-gray-500">s/d</span>
            <input type="date" id="net-end-date" class="p-2 border rounded text-sm" />

            <button id="net-filter" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                Filter
            </button>

            <button id="net-prev" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">← Sebelumnya</button>
            <button id="net-next" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Berikutnya →</button>

            <select id="net-mode" class="p-2 border rounded text-sm">
                <option value="daily" selected>Harian</option>
                <option value="weekly">Mingguan</option>
                <option value="monthly">Bulanan</option>
                <option value="yearly">Tahunan</option>
            </select>
        </div>
    </div>

    <div class="chart-container w-full h-[400px]">
        <canvas id="netIncomeChart" class="w-full"></canvas>
    </div>
</div>
