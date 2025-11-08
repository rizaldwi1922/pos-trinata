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


async function loadDashboardStats() {
    try {
        const res = await fetch('/admin/dashboard/stat');
        const data = await res.json();
        const fmt = v => 'Rp ' + Number(v ?? 0).toLocaleString('id-ID');

        // HARI INI
        document.querySelector('#stat-penjualan').textContent = fmt(data.total_penjualan_hari_ini);
        document.querySelector('#stat-pembelian').textContent = fmt(data.total_pembelian_hari_ini);
        document.querySelector('#stat-pendapatan').textContent = fmt(data.pendapatan_bersih_hari_ini);

        // TOTAL KESELURUHAN
        document.querySelector('#stat-piutang').textContent = fmt(data.total_piutang);
        document.querySelector('#stat-aset').textContent = fmt(data.total_aset);
        document.querySelector('#stat-kas').textContent = fmt(data.total_kas);
        document.querySelector('#stat-hutang').textContent = fmt(data.total_hutang);

        const statModal = document.querySelector('#stat-modal');
        if (statModal) statModal.textContent = fmt(data.total_modal);
    } catch (err) {
        console.error('Gagal memuat data dashboard:', err);
    }
}


async function loadTopProductsChart() {
    const res = await fetch('/admin/dashboard/top-products');
    const data = await res.json();

    // ambil nama produk & jumlah terjual
    const labels = data.map(p => p.name);
    const values = data.map(p => p.total_amount);

    const ctx = document.getElementById('topProductsChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Jumlah Terjual',
                data: values,
                borderWidth: 1,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)'
            }]
        },
        options: {
            indexAxis: 'y', // horizontal bar chart
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: '10 Barang Paling Laku',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => ` ${context.dataset.label}: ${context.formattedValue}`
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        font: {
                            size: 13,
                            family: 'Inter, sans-serif'
                        },
                        color: '#333',
                        autoSkip: false,
                        callback: function(value, index) {
                            // Tampilkan nama barang lengkap
                            const name = this.getLabelForValue(value);
                            // Jika nama terlalu panjang, potong jadi 25 karakter
                            return name.length > 25 ? name.substring(0, 25) + '…' : name;
                        }
                    },
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: '#666'
                    },
                    grid: {
                        drawBorder: false
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
loadDashboardStats();
loadTopProductsChart();
