<?php
/**
 * Dashboard - Smart Latex Quality Monitoring System
 * Realtime via AJAX polling (3 detik)
 */
// Base URL API: selalu pakai path absolut dari origin agar fetch tidak ERR_NAME_NOT_RESOLVED
$host = ($_SERVER['HTTP_HOST'] ?? 'localhost');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$apiBase = $scheme . '://' . $host . '/api';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    <title>Smart Latex Quality Monitoring System</title>
    <!-- Untuk production: build Tailwind dengan CLI/PostCSS dan ganti dengan file CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        panel: '#1e293b',
                        card: '#334155',
                        accent: '#0ea5e9',
                        'mutu-prima': '#22c55e',
                        'mutu-rendah': '#ef4444',
                        terawetkan: '#eab308',
                        oplos: '#3b82f6',
                        kontaminasi: '#a855f7',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .pulse-dot { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-slate-800/90 border-b border-slate-700 sticky top-0 z-50 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <h1 class="text-lg font-semibold text-sky-400">Smart Latex Quality Monitoring System</h1>
                <div class="flex items-center gap-4">
                    <div id="mqtt-indicator" class="flex items-center gap-2 text-sm">
                        <span class="w-2 h-2 rounded-full bg-slate-500 pulse-dot" id="mqtt-dot"></span>
                        <span id="mqtt-label">MQTT: --</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-400">Dari</label>
                        <input type="date" id="filter-date-from" class="bg-slate-700 border border-slate-600 rounded-lg px-2 py-1 text-sm">
                        <label class="text-sm text-slate-400">Sampai</label>
                        <input type="date" id="filter-date-to" class="bg-slate-700 border border-slate-600 rounded-lg px-2 py-1 text-sm">
                        <button type="button" id="btn-apply-filter" class="bg-sky-600 hover:bg-sky-500 px-3 py-1 rounded-lg text-sm">Terapkan</button>
                    </div>
                    <a id="btn-export-csv" href="#" class="bg-emerald-600 hover:bg-emerald-500 px-3 py-1.5 rounded-lg text-sm inline-flex items-center gap-1">Export CSV</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <p class="text-slate-400 text-sm font-medium">Current pH</p>
                <p class="text-2xl font-bold text-sky-400 mt-1" id="card-ph">--</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <p class="text-slate-400 text-sm font-medium">Current TDS (ppm)</p>
                <p class="text-2xl font-bold text-amber-400 mt-1" id="card-tds">--</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <p class="text-slate-400 text-sm font-medium">Suhu (°C)</p>
                <p class="text-2xl font-bold text-rose-400 mt-1" id="card-suhu">--</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <p class="text-slate-400 text-sm font-medium">Status Mutu</p>
                <p class="text-lg font-bold mt-1 px-3 py-1 rounded-lg inline-block" id="card-status">--</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <h2 class="text-slate-300 font-medium mb-4">Grafik pH Realtime</h2>
                <div class="h-64"><canvas id="chart-ph"></canvas></div>
            </div>
            <div class="bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <h2 class="text-slate-300 font-medium mb-4">Grafik TDS Realtime</h2>
                <div class="h-64"><canvas id="chart-tds"></canvas></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <h2 class="text-slate-300 font-medium mb-4">10 Data Terakhir</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-600">
                                <th class="text-left py-2">Waktu</th>
                                <th class="text-right py-2">pH</th>
                                <th class="text-right py-2">TDS</th>
                                <th class="text-right py-2">Suhu</th>
                                <th class="text-left py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <tr><td colspan="5" class="py-4 text-center text-slate-500">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-slate-800 rounded-xl p-5 shadow-lg border border-slate-700">
                <h2 class="text-slate-300 font-medium mb-4">Distribusi Status Mutu</h2>
                <div class="h-64"><canvas id="chart-status"></canvas></div>
            </div>
        </div>
    </main>

    <script>
(function() {
    const API_BASE = '<?= addslashes($apiBase) ?>';
    const POLL_INTERVAL = 3000;

    const statusColors = {
        'Mutu Prima':     { bg: 'bg-mutu-prima', text: 'text-slate-900' },
        'Mutu Rendah':    { bg: 'bg-mutu-rendah', text: 'text-white' },
        'Terawetkan':     { bg: 'bg-terawetkan', text: 'text-slate-900' },
        'Oplos Air':      { bg: 'bg-oplos', text: 'text-white' },
        'Kontaminasi':    { bg: 'bg-kontaminasi', text: 'text-white' }
    };
    const statusChartColors = {
        'Mutu Prima':   '#22c55e',
        'Mutu Rendah':  '#ef4444',
        'Terawetkan':   '#eab308',
        'Oplos Air':    '#3b82f6',
        'Kontaminasi':  '#a855f7'
    };

    function getParams() {
        const from = document.getElementById('filter-date-from').value || '';
        const to = document.getElementById('filter-date-to').value || '';
        return { date_from: from, date_to: to };
    }

    function cardStatusClass(status) {
        const s = (status || '').trim();
        const c = statusColors[s] || { bg: 'bg-slate-500', text: 'text-white' };
        return c.bg + ' ' + c.text;
    }

    let chartPh, chartTds, chartStatus;
    const maxPoints = 30;

    function initCharts() {
        const gridColor = 'rgba(148, 163, 184, 0.2)';
        const textColor = '#94a3b8';

        chartPh = new Chart(document.getElementById('chart-ph'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{ label: 'pH', data: [], borderColor: '#0ea5e9', backgroundColor: 'rgba(14, 165, 233, 0.1)', fill: true, tension: 0.3 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor, maxTicksLimit: 8 } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor } }
                }
            }
        });

        chartTds = new Chart(document.getElementById('chart-tds'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{ label: 'TDS', data: [], borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true, tension: 0.3 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor, maxTicksLimit: 8 } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor } }
                }
            }
        });

        chartStatus = new Chart(document.getElementById('chart-status'), {
            type: 'doughnut',
            data: { labels: [], datasets: [{ data: [], backgroundColor: Object.values(statusChartColors), borderColor: '#1e293b', borderWidth: 2 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: textColor } } }
            }
        });
    }

    function updateCards(data) {
        if (!data) return;
        document.getElementById('card-ph').textContent = Number(data.ph).toFixed(2);
        document.getElementById('card-tds').textContent = Number(data.tds).toFixed(0);
        document.getElementById('card-suhu').textContent = Number(data.suhu).toFixed(1);
        const statusEl = document.getElementById('card-status');
        statusEl.textContent = data.status || '--';
        statusEl.className = 'text-lg font-bold mt-1 px-3 py-1 rounded-lg inline-block ' + cardStatusClass(data.status);
    }

    function updateTable(rows) {
        const tbody = document.getElementById('table-body');
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-500">Belum ada data</td></tr>';
            return;
        }
        tbody.innerHTML = rows.slice(0, 10).map(r => {
            const statusClass = cardStatusClass(r.status);
            const time = (r.created_at || '').replace(' ', '<br>');
            return `<tr class="border-b border-slate-700/50">
                <td class="py-2 text-slate-300">${time}</td>
                <td class="text-right py-2">${Number(r.ph).toFixed(2)}</td>
                <td class="text-right py-2">${Number(r.tds).toFixed(0)}</td>
                <td class="text-right py-2">${Number(r.suhu).toFixed(1)}</td>
                <td><span class="px-2 py-0.5 rounded ${statusClass}">${(r.status || '').escapeHtml()}</span></td>
            </tr>`;
        }).join('');
    }

    function updateCharts(history, stats) {
        if (history && history.length) {
            const labels = history.map(r => (r.created_at || '').slice(11, 19));
            const phData = history.map(r => Number(r.ph));
            const tdsData = history.map(r => Number(r.tds));
            chartPh.data.labels = labels;
            chartPh.data.datasets[0].data = phData;
            chartTds.data.labels = labels;
            chartTds.data.datasets[0].data = tdsData;
            chartPh.update('none');
            chartTds.update('none');
        }
        if (stats && stats.by_status && stats.by_status.length) {
            chartStatus.data.labels = stats.by_status.map(s => s.status);
            chartStatus.data.datasets[0].data = stats.by_status.map(s => Number(s.count));
            chartStatus.data.datasets[0].backgroundColor = chartStatus.data.labels.map(l => statusChartColors[l] || '#64748b');
            chartStatus.update('none');
        }
    }

    function setMqttStatus(active) {
        const dot = document.getElementById('mqtt-dot');
        const label = document.getElementById('mqtt-label');
        dot.className = 'w-2 h-2 rounded-full ' + (active ? 'bg-emerald-500' : 'bg-slate-500');
        label.textContent = 'MQTT: ' + (active ? 'Aktif' : 'Tidak aktif');
    }

    String.prototype.escapeHtml = function() {
        const div = document.createElement('div');
        div.textContent = this;
        return div.innerHTML;
    };

    async function fetchJson(url) {
        const res = await fetch(url);
        const data = await res.json();
        return data;
    }

    async function poll() {
        const q = new URLSearchParams(getParams());
        try {
            const [latestRes, historyRes, statsRes, mqttRes] = await Promise.all([
                fetch(API_BASE + '/latest.php').then(r => r.json()),
                fetch(API_BASE + '/history.php?' + new URLSearchParams({ ...getParams(), limit: 50 })).then(r => r.json()),
                fetch(API_BASE + '/stats.php?' + q).then(r => r.json()),
                fetch(API_BASE + '/mqtt_status.php').then(r => r.json())
            ]);

            if (latestRes.success && latestRes.data) updateCards(latestRes.data);
            if (historyRes.success && historyRes.data) {
                updateTable(historyRes.data.slice().reverse());
                updateCharts(historyRes.data, statsRes.success ? statsRes.data : null);
            } else if (statsRes.success && statsRes.data) {
                updateCharts(null, statsRes.data);
            }
            if (mqttRes.success) setMqttStatus(mqttRes.mqtt_active);
        } catch (e) {
            console.warn('Poll error', e);
        }
    }

    function setExportLink() {
        const q = new URLSearchParams(getParams());
        document.getElementById('btn-export-csv').href = API_BASE + '/export.php?' + q;
    }

    document.getElementById('btn-apply-filter').addEventListener('click', function() {
        poll();
        setExportLink();
    });
    document.getElementById('filter-date-from').addEventListener('change', setExportLink);
    document.getElementById('filter-date-to').addEventListener('change', setExportLink);

    initCharts();
    setExportLink();
    poll();
    setInterval(poll, POLL_INTERVAL);
})();
    </script>
</body>
</html>
