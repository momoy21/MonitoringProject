<x-layout>
    <x-slot:title>Dashboard Analisis Proyek</x-slot:title>

    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Dashboard Analisis Proyek', 'url' => '#', 'active' => true]
        ];
    @endphp

    <style>
        /* ── Header ── */
        .da-header {
            background: linear-gradient(135deg, #3b7ddd 0%, #5dade2 100%);
            color: #fff;
            padding: 20px 24px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .da-header h4 { margin: 0; font-weight: 700; font-size: 1.35rem; }
        .da-header .bx { font-size: 1.6rem; vertical-align: middle; margin-right: 8px; }
        .da-selector {
            background: #fff;
            color: #3b7ddd;
            border: 2px solid #fff;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            min-width: 180px;
        }
        .da-selector:focus { outline: none; }

        /* ── KPI Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 768px) { .kpi-grid { grid-template-columns: repeat(4, 1fr); } }
        .kpi-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e8ecef;
            padding: 18px 20px;
            transition: box-shadow .25s;
        }
        .kpi-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); }
        .kpi-label { font-size: 12px; color: #8592a3; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
        .kpi-value { font-size: 1.55rem; font-weight: 700; color: #2b2f32; }
        .kpi-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 10px; }

        .kpi-icon.blue   { background: #e7f1ff; color: #3b7ddd; }
        .kpi-icon.green  { background: #e0f7e9; color: #28a745; }
        .kpi-icon.orange { background: #fff3e0; color: #ff9800; }
        .kpi-icon.red    { background: #fce4ec; color: #e53935; }

        /* ── Chart Section ── */
        .chart-section {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e8ecef;
            padding: 20px;
            margin-bottom: 1.5rem;
        }
        .chart-section .section-title {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 16px;
            color: #2b2f32;
        }
        .chart-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .chart-search {
            border: 1px solid #d9dee3;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
            width: 200px;
        }
        .chart-container-wrapper {
            position: relative;
            min-height: 320px;
        }

        /* ── Detail Table ── */
        .table-section {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e8ecef;
            padding: 20px;
        }
        .table-section .section-title {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 12px;
            color: #2b2f32;
        }
        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .table-toolbar .btn-group .btn { font-size: 13px; padding: 5px 10px; }
        .da-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .da-table thead th {
            background: #f5f5f9;
            padding: 10px 12px;
            font-weight: 600;
            color: #566a7f;
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
            border-bottom: 2px solid #d9dee3;
        }
        .da-table thead th:hover { background: #eceef2; }
        .da-table thead th .sort-icon { font-size: 10px; margin-left: 4px; opacity: .4; }
        .da-table thead th.active-sort .sort-icon { opacity: 1; color: #3b7ddd; }
        .da-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e8ecef;
            vertical-align: middle;
        }
        .da-table tbody tr:hover { background: #f8fafc; }
        .text-positive { color: #28a745; font-weight: 600; }
        .text-negative { color: #e53935; font-weight: 600; }

        /* ── Pagination ── */
        .da-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            font-size: 13px;
            color: #8592a3;
            flex-wrap: wrap;
            gap: 8px;
        }
        .da-pagination .page-btns { display: flex; gap: 4px; align-items: center; }
        .da-pagination .page-btns button {
            border: 1px solid #d9dee3;
            background: #fff;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 13px;
            cursor: pointer;
        }
        .da-pagination .page-btns button:hover { background: #f5f5f9; }
        .da-pagination .page-btns button.active { background: #3b7ddd; color: #fff; border-color: #3b7ddd; }
        .da-pagination .page-btns button:disabled { opacity: .5; cursor: not-allowed; }

        /* ── Loading ── */
        .da-loading {
            position: absolute; inset: 0;
            background: rgba(255,255,255,.85);
            display: flex; align-items: center; justify-content: center;
            z-index: 5; border-radius: 10px;
        }
        .da-loading.hidden { display: none; }

        /* ── Legend ── */
        .chart-legend {
            display: flex; gap: 20px; justify-content: center; margin-top: 12px; font-size: 13px;
        }
        .chart-legend span { display: flex; align-items: center; gap: 6px; }
        .legend-dot { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }

        /* ── Autocomplete ── */
        .search-wrapper { position: relative; }
        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #d9dee3;
            border-top: none;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
            z-index: 20;
            display: none;
            overflow: hidden;
        }
        .autocomplete-dropdown.show { display: block; }
        .autocomplete-item {
            padding: 8px 12px;
            font-size: 13px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .autocomplete-item:last-child { border-bottom: none; }
        .autocomplete-item:hover { background: #f0f4ff; }
        .autocomplete-item .ac-cc {
            font-weight: 600;
            color: #3b7ddd;
            white-space: nowrap;
        }
        .autocomplete-item .ac-name {
            color: #566a7f;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>

    {{-- ═══════════ HEADER ═══════════ --}}
    <div class="da-header">
        <h4><i class="bx bx-bar-chart-square"></i> <span id="headerTitle">Dashboard Analisis Proyek</span></h4>
        <select class="da-selector" id="dashboardSelector">
            <option value="deviasi">Deviasi Biaya</option>
            <option value="margin">Deviasi Margin</option>
        </select>
    </div>

    {{-- ═══════════ KPI CARDS ═══════════ --}}
    <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card" id="kpi1">
            <div class="kpi-icon blue"><i class="bx bx-buildings"></i></div>
            <div class="kpi-label" id="kpi1Label">Total Nilai Proyek</div>
            <div class="kpi-value" id="kpi1Value">-</div>
        </div>
        <div class="kpi-card" id="kpi2">
            <div class="kpi-icon green"><i class="bx bx-dollar-circle"></i></div>
            <div class="kpi-label" id="kpi2Label">Total Nilai Aktual</div>
            <div class="kpi-value" id="kpi2Value">-</div>
        </div>
        <div class="kpi-card" id="kpi3">
            <div class="kpi-icon orange"><i class="bx bx-trending-up"></i></div>
            <div class="kpi-label" id="kpi3Label">Total Deviasi</div>
            <div class="kpi-value" id="kpi3Value">-</div>
        </div>
        <div class="kpi-card" id="kpi4">
            <div class="kpi-icon red"><i class="bx bx-error-alt"></i></div>
            <div class="kpi-label" id="kpi4Label">Project Overbudget</div>
            <div class="kpi-value" id="kpi4Value">-</div>
        </div>
    </div>

    {{-- ═══════════ CHART ═══════════ --}}
    <div class="chart-section">
        <div class="chart-toolbar">
            <div class="section-title" id="chartTitle">Deviasi Biaya per Project</div>
            <div style="display:flex;gap:8px;align-items:center;">
                <div class="search-wrapper">
                    <input type="text" class="chart-search" id="chartSearch" placeholder="Search..." autocomplete="off">
                    <div class="autocomplete-dropdown" id="acDropdown"></div>
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleChartOrientation()" title="Toggle orientation">
                    <i class="bx bx-rotate-right"></i>
                </button>
            </div>
        </div>
        <div class="chart-container-wrapper" id="chartWrapper">
            <canvas id="mainChart"></canvas>
            <div class="da-loading hidden" id="chartLoading">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
        <div class="chart-legend" id="chartLegend"></div>
    </div>

    {{-- ═══════════ DETAIL TABLE ═══════════ --}}
    <div class="table-section">
        <div class="table-toolbar">
            <div class="section-title">Detail Proyek</div>
            <div style="display:flex;gap:8px;align-items:center;">
                <div class="btn-group">
                    <button class="btn btn-outline-secondary" onclick="exportCSV()" title="Export CSV"><i class="bx bx-download"></i></button>
                    <button class="btn btn-outline-secondary" onclick="window.print()" title="Print"><i class="bx bx-printer"></i></button>
                </div>
                <span class="badge bg-label-primary" id="menuLabel">≡ Menu</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="da-table" id="detailTable">
                <thead id="tableHead"></thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
        <div class="da-pagination">
            <div id="paginationInfo">-</div>
            <div class="page-btns" id="paginationBtns"></div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function() {
        // ── State ──
        let currentDashboard = 'deviasi';
        let mainChart = null;
        let currentPage = 1;
        let currentSort = { by: 'cost_center', dir: 'asc' };
        let searchTerm = '';
        let searchTimeout = null;

        const URLS = {
            deviasi: '{{ route("dashboardanalisis.deviasi-biaya") }}',
            margin:  '{{ route("dashboardanalisis.margin-proyek") }}',
            suggest: '{{ route("dashboardanalisis.suggestions") }}'
        };

        // ── Init ──
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('dashboardSelector').addEventListener('change', function() {
                currentDashboard = this.value;
                currentPage = 1;
                currentSort = { by: 'cost_center', dir: 'asc' };
                searchTerm = '';
                document.getElementById('chartSearch').value = '';
                loadDashboard();
            });

            const searchInput = document.getElementById('chartSearch');
            const acDropdown = document.getElementById('acDropdown');

            searchInput.addEventListener('input', function() {
                const val = this.value.trim();
                clearTimeout(searchTimeout);

                // Autocomplete suggestions
                if (val.length >= 1) {
                    searchTimeout = setTimeout(() => {
                        fetchSuggestions(val);
                    }, 250);
                } else {
                    acDropdown.classList.remove('show');
                    acDropdown.innerHTML = '';
                    // If cleared, reload unfiltered
                    searchTerm = '';
                    currentPage = 1;
                    loadDashboard();
                }
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-wrapper')) {
                    acDropdown.classList.remove('show');
                }
            });

            // Submit search on Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    acDropdown.classList.remove('show');
                    searchTerm = this.value.trim();
                    currentPage = 1;
                    loadDashboard();
                }
            });

            loadDashboard();
        });

        // ── Fetch Autocomplete Suggestions ──
        function fetchSuggestions(query) {
            fetch(`${URLS.suggest}?q=${encodeURIComponent(query)}&type=${currentDashboard}`)
                .then(r => r.json())
                .then(suggestions => {
                    renderSuggestions(suggestions);
                })
                .catch(err => console.error('Autocomplete error:', err));
        }

        // ── Render Autocomplete Suggestions ──
        function renderSuggestions(suggestions) {
            const acDropdown = document.getElementById('acDropdown');
            acDropdown.innerHTML = '';

            if (suggestions.length > 0) {
                suggestions.forEach(item => {
                    const div = document.createElement('div');
                    div.classList.add('autocomplete-item');
                    div.innerHTML = `<span class="ac-cc">${item.cost_center}</span> <span class="ac-name">${item.nama_project || ''}</span>`;
                    div.addEventListener('click', () => selectSuggestion(item.cost_center));
                    acDropdown.appendChild(div);
                });
                acDropdown.classList.add('show');
            } else {
                acDropdown.classList.remove('show');
            }
        }

        // ── Select Autocomplete Suggestion ──
        function selectSuggestion(costCenter) {
            const searchInput = document.getElementById('chartSearch');
            searchInput.value = costCenter;
            document.getElementById('acDropdown').classList.remove('show');
            searchTerm = searchInput.value.trim();
            currentPage = 1;
            loadDashboard();
        }

        // ── Load Dashboard ──
        function loadDashboard() {
            const url = URLS[currentDashboard];
            const params = new URLSearchParams({
                page: currentPage,
                per_page: 10,
                search: searchTerm,
                sort_by: currentSort.by,
                sort_dir: currentSort.dir,
            });

            showLoading(true);
            updateHeader();

            fetch(`${url}?${params}`)
                .then(r => r.json())
                .then(data => {
                    showLoading(false);
                    if (currentDashboard === 'deviasi') {
                        renderDeviasi(data);
                    } else {
                        renderMargin(data);
                    }
                })
                .catch(err => {
                    showLoading(false);
                    console.error('Dashboard error:', err);
                });
        }

        // ── Format helpers ──
        function fmtCurrency(val) {
            if (val === null || val === undefined) return '-';
            const abs = Math.abs(val);
            let formatted;
            if (abs >= 1e9) formatted = (val / 1e9).toFixed(2) + 'B';
            else if (abs >= 1e6) formatted = (val / 1e6).toFixed(2) + 'M';
            else if (abs >= 1e3) formatted = (val / 1e3).toFixed(1) + 'K';
            else formatted = val.toFixed(0);
            return formatted;
        }
        function fmtPersen(val) { return (val ?? 0).toFixed(1) + '%'; }

        // ── Update Header ──
        function updateHeader() {
            const title = currentDashboard === 'deviasi' ? 'Dashboard Analisis Proyek' : 'Dashboard Margin Proyek';
            document.getElementById('headerTitle').textContent = title;
        }

        // ── Render Deviasi Biaya ──
        function renderDeviasi(data) {
            // KPI
            setKPI(1, 'bx-buildings', 'blue',  'Total Nilai Proyek',  fmtCurrency(data.kpi.total_nilai_proyek));
            setKPI(2, 'bx-dollar-circle', 'green', 'Total Nilai Aktual',  fmtCurrency(data.kpi.total_aktual_biaya));
            setKPI(3, 'bx-trending-up', 'orange', 'Total Deviasi', fmtCurrency(data.kpi.total_deviasi));
            setKPI(4, 'bx-error-alt', 'red', 'Project Overbudget', data.kpi.project_overbudget);

            // Chart
            document.getElementById('chartTitle').textContent = 'Deviasi Biaya per Project';
            renderDeviasiChart(data.chart);

            // Table
            renderDeviasiTable(data.table);
        }

        // ── Render Margin Proyek ──
        function renderMargin(data) {
            setKPI(1, 'bx-line-chart', 'blue',  'Rata-rata Margin',  fmtPersen(data.kpi.rata_rata_margin));
            setKPI(2, 'bx-trophy', 'green', 'Margin Tertinggi',  fmtPersen(data.kpi.margin_tertinggi));
            setKPI(3, 'bx-down-arrow-circle', 'orange', 'Margin Terendah', fmtPersen(data.kpi.margin_terendah));
            setKPI(4, 'bx-error-alt', 'red', 'Project Rugi', data.kpi.proyek_rugi);

            document.getElementById('chartTitle').textContent = 'Deviasi Margin per Project';
            renderMarginChart(data.chart);
            renderMarginTable(data.table);
        }

        // ── Set KPI Card ──
        function setKPI(n, icon, color, label, value) {
            document.getElementById(`kpi${n}Label`).textContent = label;
            document.getElementById(`kpi${n}Value`).textContent = value;
            const iconEl = document.querySelector(`#kpi${n} .kpi-icon`);
            iconEl.className = `kpi-icon ${color}`;
            iconEl.innerHTML = `<i class="bx ${icon}"></i>`;
        }

        // ── Deviasi Chart (horizontal bar) ──
        function renderDeviasiChart(chart) {
            if (mainChart) mainChart.destroy();
            const ctx = document.getElementById('mainChart').getContext('2d');

            const colors = chart.deviasi_biaya.map(v => v >= 0 ? '#28a745' : '#e53935');

            mainChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chart.labels,
                    datasets: [{
                        label: 'Deviasi Biaya',
                        data: chart.deviasi_biaya,
                        backgroundColor: colors,
                        borderRadius: 4,
                        barThickness: 22,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => `Deviasi: ${fmtCurrency(ctx.raw)}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { callback: v => fmtCurrency(v) },
                            grid: { color: '#f0f0f0' }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });

            // Legend
            document.getElementById('chartLegend').innerHTML = `
                <span><span class="legend-dot" style="background:#28a745"></span> Deviasi Normal</span>
                <span><span class="legend-dot" style="background:#e53935"></span> Overbudget</span>
            `;
        }

        // ── Margin Chart (horizontal bar) ──
        function renderMarginChart(chart) {
            if (mainChart) mainChart.destroy();
            const ctx = document.getElementById('mainChart').getContext('2d');

            const colors = chart.deviasi_margin.map(v => v >= 0 ? '#28a745' : '#e53935');

            mainChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chart.labels,
                    datasets: [{
                        label: 'Deviasi Margin',
                        data: chart.deviasi_margin,
                        backgroundColor: colors,
                        borderRadius: 4,
                        barThickness: 22,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => `Deviasi: ${fmtPersen(ctx.raw)}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { callback: v => fmtPersen(v) },
                            grid: { color: '#f0f0f0' }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });

            document.getElementById('chartLegend').innerHTML = `
                <span><span class="legend-dot" style="background:#28a745"></span> Deviasi Positif</span>
                <span><span class="legend-dot" style="background:#e53935"></span> Deviasi Negatif</span>
            `;
        }

        // ── Deviasi Table ──
        function renderDeviasiTable(table) {
            const cols = [
                { key: 'cost_center', label: 'Cost Center' },
                { key: 'namaproject', label: 'Nama Proyek' },
                { key: 'nilai_proyek', label: 'Nilai Proyek' },
                { key: 'total_aktual_biaya', label: 'Nilai Aktual' },
                { key: 'deviasi_biaya', label: 'Deviasi Biaya' },
                { key: 'margin_persen', label: 'Margin (%)' },
            ];
            renderTable(cols, table, row => `
                <td>${row.cost_center}</td>
                <td>${row.namaproject || '-'}</td>
                <td>${fmtCurrency(row.nilai_proyek)}</td>
                <td>${fmtCurrency(row.total_aktual_biaya)}</td>
                <td class="${row.deviasi_biaya >= 0 ? 'text-positive' : 'text-negative'}">${row.deviasi_biaya >= 0 ? '+' : ''}${fmtCurrency(row.deviasi_biaya)}</td>
                <td>${fmtPersen(row.margin_persen)}</td>
            `);
        }

        // ── Margin Table ──
        function renderMarginTable(table) {
            const cols = [
                { key: 'cost_center', label: 'Cost Center' },
                { key: 'nama_project', label: 'Nama Proyek' },
                { key: 'margin_rkap', label: 'Margin RKAP' },
                { key: 'margin_pleno', label: 'Margin Pleno' },
                { key: 'deviasi_margin', label: 'Deviasi Margin' },
                { key: 'persen_margin', label: 'Persen Margin (%)' },
            ];
            renderTable(cols, table, row => `
                <td>${row.cost_center}</td>
                <td>${row.nama_project || '-'}</td>
                <td>${fmtPersen(row.margin_rkap)}</td>
                <td>${fmtPersen(row.margin_pleno)}</td>
                <td class="${row.deviasi_margin >= 0 ? 'text-positive' : 'text-negative'}">${row.deviasi_margin >= 0 ? '+' : ''}${fmtPersen(row.deviasi_margin)}</td>
                <td>${fmtPersen(row.persen_margin)}</td>
            `);
        }

        // ── Generic Table Renderer ──
        function renderTable(cols, tableData, rowRenderer) {
            // Head
            const thead = document.getElementById('tableHead');
            thead.innerHTML = '<tr>' + cols.map(c => {
                const isActive = currentSort.by === c.key;
                const arrow = isActive ? (currentSort.dir === 'asc' ? '↑' : '↓') : '↕';
                return `<th class="${isActive ? 'active-sort' : ''}" data-col="${c.key}">
                    ${c.label} <span class="sort-icon">${arrow}</span>
                </th>`;
            }).join('') + '</tr>';

            // Attach sort handlers
            thead.querySelectorAll('th').forEach(th => {
                th.addEventListener('click', function() {
                    const col = this.dataset.col;
                    if (currentSort.by === col) {
                        currentSort.dir = currentSort.dir === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort = { by: col, dir: 'asc' };
                    }
                    currentPage = 1;
                    loadDashboard();
                });
            });

            // Body
            const tbody = document.getElementById('tableBody');
            if (!tableData.data || tableData.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${cols.length}" class="text-center py-3" style="color:#8592a3;">Tidak ada data</td></tr>`;
            } else {
                tbody.innerHTML = tableData.data.map(row => `<tr>${rowRenderer(row)}</tr>`).join('');
            }

            // Pagination
            renderPagination(tableData);
        }

        // ── Pagination ──
        function renderPagination(t) {
            const info = document.getElementById('paginationInfo');
            const btns = document.getElementById('paginationBtns');
            const start = (t.current_page - 1) * t.per_page + 1;
            const end = Math.min(t.current_page * t.per_page, t.total);
            info.textContent = t.total > 0 ? `${start}-${end} of ${t.total} tutz/ion` : 'No data';

            let html = `<button ${t.current_page <= 1 ? 'disabled' : ''} onclick="window._daGoPage(${t.current_page - 1})">‹ Previous</button>`;
            
            const maxVisible = 5;
            let startPage, endPage;
            
            if (t.last_page <= maxVisible) {
                startPage = 1;
                endPage = t.last_page;
            } else if (t.current_page <= 3) {
                startPage = 1;
                endPage = maxVisible;
            } else if (t.current_page >= t.last_page - 2) {
                startPage = t.last_page - maxVisible + 1;
                endPage = t.last_page;
            } else {
                startPage = t.current_page - 2;
                endPage = t.current_page + 2;
            }
            
            if (startPage > 1) {
                html += `<button class="" onclick="window._daGoPage(1)">1</button>`;
                if (startPage > 2) {
                    html += `<button class="" disabled>...</button>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="${i === t.current_page ? 'active' : ''}" onclick="window._daGoPage(${i})">${i}</button>`;
            }
            
            if (endPage < t.last_page) {
                if (endPage < t.last_page - 1) {
                    html += `<button class="" disabled>...</button>`;
                }
                html += `<button class="" onclick="window._daGoPage(${t.last_page})">${t.last_page}</button>`;
            }
            
            html += `<button ${t.current_page >= t.last_page ? 'disabled' : ''} onclick="window._daGoPage(${t.current_page + 1})">Next ›</button>`;
            btns.innerHTML = html;
        }

        // ── Public API ──
        window._daGoPage = function(page) {
            currentPage = page;
            loadDashboard();
        };

        window.toggleChartOrientation = function() {
            // Toggle between horizontal and vertical (for fun interactivity)
            loadDashboard();
        };

        window.exportCSV = function() {
            const table = document.getElementById('detailTable');
            const rows = table.querySelectorAll('tr');
            let csv = '';
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const line = Array.from(cells).map(c => '"' + c.textContent.trim().replace(/"/g, '""') + '"').join(',');
                csv += line + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `dashboard_${currentDashboard}_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
        };

        function showLoading(show) {
            document.getElementById('chartLoading').classList.toggle('hidden', !show);
        }
    })();
    </script>
    @endpush
</x-layout>
