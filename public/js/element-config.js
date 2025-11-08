// public/js/element-config.js

function onConfigChange(config) {
    const titleElement = document.getElementById('dashboard-title');
    const companyElement = document.getElementById('company-name');
    const primaryColor = config.primary_color || '#2469a5';
    const backgroundColor = config.background_color || '#ffffff';
    const textColor = config.text_color || '#1e293b';

    if (titleElement) {
        titleElement.textContent = config.dashboard_title || "Dashboard Laporan Keuangan";
        titleElement.style.color = textColor;
    }

    if (companyElement) {
        companyElement.textContent = config.company_name || "PT. Contoh Perusahaan";
        companyElement.style.color = '#64748b';
    }

    document.querySelectorAll('.stat-card, .chart-card, .table-card, .header, .filter-section')
        .forEach(card => {
            card.style.background = backgroundColor;
        });

    document.querySelectorAll('.stat-value').forEach((el, index) => {
        const colors = [primaryColor, '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'];
        el.style.color = colors[index] || primaryColor;
    });

    document.querySelectorAll('.expense-amount').forEach(el => {
        el.style.color = primaryColor;
    });

    const lastExpenseItem = document.querySelector('.expenses-list .expense-item:last-child');
    if (lastExpenseItem) {
        lastExpenseItem.style.background = primaryColor;
        lastExpenseItem.style.color = '#ffffff';
    }

    // Update chart colors dynamically
    if (window.salesPurchaseChart) {
        window.salesPurchaseChart.data.datasets[0].borderColor = primaryColor;
        window.salesPurchaseChart.data.datasets[0].backgroundColor = primaryColor + '20';
        window.salesPurchaseChart.update();
    }

    if (window.profitChart) {
        window.profitChart.data.datasets[0].backgroundColor = primaryColor;
        window.profitChart.update();
    }
}

// SDK inisialisasi (opsional)
if (window.elementSdk) {
    window.elementSdk.init({
        defaultConfig: {
            dashboard_title: "Dashboard Laporan Keuangan",
            company_name: "PT. Contoh Perusahaan",
            primary_color: "#2469a5",
            background_color: "#ffffff",
            text_color: "#1e293b"
        },
        onConfigChange,
        mapToCapabilities: config => ({
            recolorables: [
                {
                    get: () => config.primary_color,
                    set: val => window.elementSdk.setConfig({ primary_color: val })
                },
                {
                    get: () => config.background_color,
                    set: val => window.elementSdk.setConfig({ background_color: val })
                },
                {
                    get: () => config.text_color,
                    set: val => window.elementSdk.setConfig({ text_color: val })
                }
            ]
        })
    });
}
