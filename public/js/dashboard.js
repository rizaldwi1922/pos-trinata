let offset = 0;
let chart;
const ctx = document.getElementById('summarySalesChart').getContext('2d');
const periodSelect = document.getElementById('summary-chart-period');

async function renderChart(mode, startDate = '', endDate = '') {
    let url = `/admin/dashboard/sales-chart?mode=${mode}&offset=${offset}`;
    if (startDate && endDate) {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }

    const res = await fetch(url);
    const data = await res.json();

    const labels = data.map(d => d.periode);
    const penjualan = data.map(d => d.total_penjualan ?? 0);
    const hpp = data.map(d => d.total_hpp ?? 0);
    const laba = data.map(d => d.total_laba ?? 0);

    if (chart) chart.destroy();

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Penjualan', data: penjualan, borderColor: 'rgba(54,162,235,1)', backgroundColor: 'rgba(54,162,235,0.1)', fill: true },
                { label: 'HPP', data: hpp, borderColor: 'rgba(255,206,86,1)', backgroundColor: 'rgba(255,206,86,0.1)', fill: true },
                { label: 'Laba', data: laba, borderColor: 'rgba(75,192,192,1)', backgroundColor: 'rgba(75,192,192,0.1)', fill: false },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { right: 20 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: true,
                    text: `Grafik Penjualan, HPP & Laba (${mode.toUpperCase()})`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'Rp' + v.toLocaleString('id-ID')
                    }
                }
            }
        }
    });
}

// tombol navigasi
document.getElementById('prev-period').onclick = () => { offset++; renderChart(periodSelect.value); };
document.getElementById('next-period').onclick = () => { if (offset > 0) offset--; renderChart(periodSelect.value); };
periodSelect.onchange = () => { offset = 0; renderChart(periodSelect.value); };

// tombol filter tanggal
document.getElementById('filter-date').onclick = () => {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    if (!startDate || !endDate) {
        alert('Pilih tanggal awal dan akhir terlebih dahulu!');
        return;
    }
    renderChart('daily', startDate, endDate);
};

// load awal
renderChart('daily');

let netOffset = 0;
let netChart;
const netCtx = document.getElementById('netIncomeChart').getContext('2d');

async function renderNetChart(mode, startDate = '', endDate = '') {
    let url = `/admin/dashboard/net-income?mode=${mode}&offset=${netOffset}`;
    if (startDate && endDate) {
        url += `&start_date=${startDate}&end_date=${endDate}`;
    }

    const res = await fetch(url);
    const data = await res.json();

    const labels = data.map(d => d.periode);
    const labaKotor = data.map(d => d.laba_kotor ?? 0);
    const pengeluaran = data.map(d => d.pengeluaran ?? 0);
    const labaBersih = data.map(d => d.laba_bersih ?? 0);

    if (netChart) netChart.destroy();

    netChart = new Chart(netCtx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Laba Kotor',
                    data: labaKotor,
                    borderColor: 'rgba(54,162,235,1)',
                    backgroundColor: 'rgba(54,162,235,0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Pengeluaran',
                    data: pengeluaran,
                    borderColor: 'rgba(255,99,132,1)',
                    backgroundColor: 'rgba(255,99,132,0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Laba Bersih',
                    data: labaBersih,
                    borderColor: 'rgba(75,192,192,1)',
                    backgroundColor: 'rgba(75,192,192,0.1)',
                    tension: 0.3,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { right: 20 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: true,
                    text: `Grafik Pendapatan Bersih (${mode.toUpperCase()})`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'Rp' + v.toLocaleString('id-ID')
                    }
                }
            }
        }
    });
}

// tombol navigasi
document.getElementById('net-prev').onclick = () => { netOffset++; renderNetChart(netMode.value); };
document.getElementById('net-next').onclick = () => { if (netOffset > 0) netOffset--; renderNetChart(netMode.value); };
const netMode = document.getElementById('net-mode');
netMode.onchange = () => { netOffset = 0; renderNetChart(netMode.value); };

// tombol filter tanggal
document.getElementById('net-filter').onclick = () => {
    const s = document.getElementById('net-start-date').value;
    const e = document.getElementById('net-end-date').value;
    if (!s || !e) {
        alert('Pilih tanggal awal dan akhir!');
        return;
    }
    renderNetChart('daily', s, e);
};

// load awal
renderNetChart('daily');
