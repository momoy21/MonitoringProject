<x-layout>
    <x-slot:title>Laporan Hasil Pleno RAB</x-slot:title>

    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Laporan Hasil Pleno RAB', 'url' => '#', 'active' => true]
        ];
    @endphp

    <style>
        .dashboard-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #d9dee3;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #5dade2 0%, #85c1e9 100%);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-body {
            padding: 20px;
        }
        .filter-card {
            background: #f5f5f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 1.5rem;
        }
        .chart-container {
            position: relative;
            height: 350px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #ebf5fb;
            border-left: 4px solid #5dade2;
            padding: 12px 16px;
            margin-bottom: 1rem;
            border-radius: 0 4px 4px 0;
            font-size: 13px;
            color: #566a7f;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .dashboard-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .dashboard-tab {
            padding: 10px 20px;
            border: 2px solid #5dade2;
            border-radius: 8px;
            background: #fff;
            color: #5dade2;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .dashboard-tab:hover {
            background: #ebf5fb;
        }
        .dashboard-tab.active {
            background: #5dade2;
            color: white;
        }
        .dashboard-panel {
            display: none;
        }
        .dashboard-panel.active {
            display: block;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .summary-table th {
            background: #ffc107;
            color: #000;
            padding: 10px;
            border: 1px solid #ddd;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }
        .summary-table th:hover {
            background: #e0a800;
        }
        .summary-table th::after {
            content: ' ↕';
            font-size: 10px;
            opacity: 0.5;
        }
        .summary-table th.asc::after {
            content: ' ↑';
            opacity: 1;
        }
        .summary-table th.desc::after {
            content: ' ↓';
            opacity: 1;
        }
        .summary-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .detail-table-container {
            margin-top: 20px;
            display: none;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .detail-table th {
            background: #343a40;
            color: #fff;
            padding: 10px;
            border: 1px solid #454d55;
            cursor: pointer;
            user-select: none;
        }
        .detail-table th:hover {
            background: #495057;
        }
        .detail-table th::after {
            content: ' ↕';
            font-size: 10px;
            opacity: 0.5;
        }
        .detail-table th.asc::after {
            content: ' ↑';
            opacity: 1;
        }
        .detail-table th.desc::after {
            content: ' ↓';
            opacity: 1;
        }
        .detail-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .detail-table tr:hover {
            background: #f5f5f5;
        }
        .detail-title {
            background: #17a2b8;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .click-hint {
            font-size: 12px;
            color: #888;
            font-style: italic;
            margin-top: 10px;
        }
        .export-tools {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .export-tools img {
            width: 28px;
            height: 28px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .export-tools img:hover {
            transform: scale(1.1);
        }
        .export-tools a {
            display: flex;
            align-items: center;
        }
    </style>

    {{-- Filter Section --}}
    <div class="filter-card">
        <form id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Periode Mulai</label>
                <input type="date" name="start_date" id="startDate" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Periode Akhir</label>
                <input type="date" name="end_date" id="endDate" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-search me-1"></i> Proses
                </button>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilter()">
                    <i class="bx bx-reset me-1"></i> Reset
                </button>
            </div>
        </form>
    </div>

    {{-- Dashboard Tab Selection --}}
    <div class="dashboard-tabs">
        <div class="dashboard-tab active" data-tab="divisi">
            <i class="bx bx-bar-chart-alt-2 me-1"></i> Per Divisi
        </div>
        <div class="dashboard-tab" data-tab="kategori">
            <i class="bx bx-pie-chart-alt-2 me-1"></i> Per Kategori
        </div>
        <div class="dashboard-tab" data-tab="divisi-kategori">
            <i class="bx bx-line-chart me-1"></i> PerDivisi & Kategori
        </div>
        <div class="dashboard-tab" data-tab="jenis-proyek">
            <i class="bx bx-category me-1"></i> PerJenis Proyek & Kategori
        </div>
    </div>

    {{-- Dashboard 1: Per Divisi --}}
    <div class="dashboard-panel active" id="panel-divisi">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <span><i class="bx bx-bar-chart-alt-2 me-2"></i>Dashboard Hasil Pleno Per Divisi periode <span id="periodLabel1">...</span></span>
                <div class="export-tools">
                    <a href="{{ route('laporanhasilplenorab.index') }}" title="Refresh"><img src="https://cdn-icons-png.flaticon.com/512/2267/2267918.png"></a>
                    <img src="https://cdn-icons-png.flaticon.com/512/446/446991.png" onclick="window.print()" title="Print">
                </div>
            </div>
            <div class="dashboard-body">
                <div class="chart-container">
                    <canvas id="chartDivisi"></canvas>
                    <div class="loading-overlay" id="loadingDivisi">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="detail-table-container" id="detailDivisi">
                    <div class="detail-title" id="detailTitleDivisi">Detail Data</div>
                    <div class="table-responsive">
                        <table class="detail-table sortable" id="detailTableDivisi">
                            <thead>
                                <tr>
                                    <th data-col="0">Tgl Pengajuan</th>
                                    <th data-col="1">IO</th>
                                    <th data-col="2">Cost Center</th>
                                    <th data-col="3">Description</th>
                                    <th data-col="4">Customer</th>
                                    <th data-col="5">Divisi</th>
                                    <th data-col="6">PM</th>
                                    <th data-col="7">Keterangan</th>
                                    <th data-col="8">Hasil Pleno</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <p class="click-hint">*Klik pada chart untuk melihat detail data</p>
                <div class="table-responsive">
                    <table class="summary-table sortable" id="summaryTableDivisi">
                        <thead>
                            <tr>
                                <th data-col="0">Bulan</th>
                                <th data-col="1">Divisi</th>
                                <th data-col="2">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Dashboard 2: Per Kategori --}}
    <div class="dashboard-panel" id="panel-kategori">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <span><i class="bx bx-pie-chart-alt-2 me-2"></i>Dashboard Hasil Pleno Per Kategori periode <span id="periodLabel2">...</span></span>
                <div class="export-tools">
                    <a href="{{ route('laporanhasilplenorab.index') }}" title="Refresh"><img src="https://cdn-icons-png.flaticon.com/512/2267/2267918.png"></a>
                    <img src="https://cdn-icons-png.flaticon.com/512/446/446991.png" onclick="window.print()" title="Print">
                </div>
            </div>
            <div class="dashboard-body">
                <div class="chart-container">
                    <canvas id="chartKategori"></canvas>
                    <div class="loading-overlay" id="loadingKategori">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="detail-table-container" id="detailKategori">
                    <div class="detail-title" id="detailTitleKategori">Detail Data</div>
                    <div class="table-responsive">
                        <table class="detail-table sortable" id="detailTableKategori">
                            <thead>
                                <tr>
                                    <th data-col="0">Tgl Pengajuan</th>
                                    <th data-col="1">IO</th>
                                    <th data-col="2">Cost Center</th>
                                    <th data-col="3">Description</th>
                                    <th data-col="4">Customer</th>
                                    <th data-col="5">Divisi</th>
                                    <th data-col="6">PM</th>
                                    <th data-col="7">Keterangan</th>
                                    <th data-col="8">Hasil Pleno</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <p class="click-hint">*Klik pada chart untuk melihat detail data</p>
                <div class="table-responsive">
                    <table class="summary-table sortable" id="summaryTableKategori">
                        <thead>
                            <tr>
                                <th data-col="0">Bulan</th>
                                <th data-col="1">Keterangan</th>
                                <th data-col="2">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Dashboard 3: Per Divisi & Kategori --}}
    <div class="dashboard-panel" id="panel-divisi-kategori">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <span><i class="bx bx-line-chart me-2"></i>Dashboard Hasil Pleno Per Kategori dan per Divisi periode <span id="periodLabel3">...</span></span>
                <div class="export-tools">
                    <a href="{{ route('laporanhasilplenorab.index') }}" title="Refresh"><img src="https://cdn-icons-png.flaticon.com/512/2267/2267918.png"></a>
                    <img src="https://cdn-icons-png.flaticon.com/512/446/446991.png" onclick="window.print()" title="Print">
                </div>
            </div>
            <div class="dashboard-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="chartDivisiKategori"></canvas>
                    <div class="loading-overlay" id="loadingDivisiKategori">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="summary-table sortable" id="summaryTableDivisiKategori">
                        <thead>
                            <tr>
                                <th data-col="0">Bulan</th>
                                <th data-col="1">Divisi</th>
                                <th data-col="2">Hasil</th>
                                <th data-col="3">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Dashboard 4: Per Jenis Proyek & Kategori --}}
    <div class="dashboard-panel" id="panel-jenis-proyek">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <span><i class="bx bx-category me-2"></i>Dashboard Hasil Pleno Per Jenis Proyek & Kategori periode <span id="periodLabel4">...</span></span>
                <div class="export-tools">
                    <a href="{{ route('laporanhasilplenorab.index') }}" title="Refresh"><img src="https://cdn-icons-png.flaticon.com/512/2267/2267918.png"></a>
                    <img src="https://cdn-icons-png.flaticon.com/512/446/446991.png" onclick="window.print()" title="Print">
                </div>
            </div>
            <div class="dashboard-body">
                <div class="chart-container">
                    <canvas id="chartJenisProyek"></canvas>
                    <div class="loading-overlay" id="loadingJenisProyek">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="detail-table-container" id="detailJenisProyek">
                    <div class="detail-title" id="detailTitleJenisProyek">Detail Data</div>
                    <div class="table-responsive">
                        <table class="detail-table sortable" id="detailTableJenisProyek">
                            <thead>
                                <tr>
                                    <th data-col="0">Tgl Pengajuan</th>
                                    <th data-col="1">IO</th>
                                    <th data-col="2">Cost Center</th>
                                    <th data-col="3">Description</th>
                                    <th data-col="4">Customer</th>
                                    <th data-col="5">Divisi</th>
                                    <th data-col="6">PM</th>
                                    <th data-col="7">Keterangan</th>
                                    <th data-col="8">Hasil Pleno</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <p class="click-hint">*Klik pada chart untuk melihat detail data</p>
                <div class="table-responsive">
                    <table class="summary-table sortable" id="summaryTableJenisProyek">
                        <thead>
                            <tr>
                                <th data-col="0">Bulan</th>
                                <th data-col="1">Keterangan</th>
                                <th data-col="2">Jenis Proyek</th>
                                <th data-col="3">Prosentase</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chartDivisi, chartKategori, chartDivisiKategori, chartJenisProyek;
        let currentTab = 'divisi';
        let divisiData = [], kategoriData = [], jenisProyekData = [];

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dashboard-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    switchTab(this.dataset.tab);
                });
            });

            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                loadCurrentChart();
            });

            // Initialize sortable tables
            initSortableTables();
            loadCurrentChart();
        });

        function initSortableTables() {
            document.querySelectorAll('.sortable th').forEach(th => {
                th.addEventListener('click', function() {
                    const table = this.closest('table');
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const colIdx = parseInt(this.dataset.col);
                    const isAsc = this.classList.contains('asc');
                    
                    // Clear other sort indicators
                    table.querySelectorAll('th').forEach(h => h.classList.remove('asc', 'desc'));
                    
                    // Set new sort direction
                    this.classList.add(isAsc ? 'desc' : 'asc');
                    
                    rows.sort((a, b) => {
                        const aVal = a.cells[colIdx]?.textContent.trim() || '';
                        const bVal = b.cells[colIdx]?.textContent.trim() || '';
                        
                        // Try numeric sort first
                        const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
                        const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
                        
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return isAsc ? bNum - aNum : aNum - bNum;
                        }
                        
                        // Fall back to string sort
                        return isAsc ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
                    });
                    
                    rows.forEach(row => tbody.appendChild(row));
                });
            });
        }

        function switchTab(tabName) {
            currentTab = tabName;
            document.querySelectorAll('.dashboard-tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.querySelectorAll('.dashboard-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(`panel-${tabName}`).classList.add('active');
            loadCurrentChart();
        }

        function getFilterParams() {
            return {
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
            };
        }

        function getPeriodLabel() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            if (start && end) {
                return `${formatDate(start)} s.d ${formatDate(end)}`;
            }
            return 'Semua Periode';
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function resetFilter() {
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            loadCurrentChart();
        }

        function loadCurrentChart() {
            const periodLabel = getPeriodLabel();
            document.querySelectorAll('[id^="periodLabel"]').forEach(el => el.textContent = periodLabel);
            
            switch(currentTab) {
                case 'divisi': loadDivisiChart(); break;
                case 'kategori': loadKategoriChart(); break;
                case 'divisi-kategori': loadDivisiKategoriChart(); break;
                case 'jenis-proyek': loadJenisProyekChart(); break;
            }
        }

        function loadDivisiChart() {
            const loading = document.getElementById('loadingDivisi');
            loading.style.display = 'flex';
            document.getElementById('detailDivisi').style.display = 'none';

            fetch('{{ route("laporanhasilplenorab.divisi-data") }}?' + new URLSearchParams(getFilterParams()))
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    divisiData = data;

                    const tbody = document.querySelector('#summaryTableDivisi tbody');
                    tbody.innerHTML = data.summary.map(row => `
                        <tr>
                            <td>${row.bulan}</td>
                            <td>${row.divisi}</td>
                            <td>${row.jumlah}</td>
                        </tr>
                    `).join('');

                    if (chartDivisi) chartDivisi.destroy();
                    const ctx = document.getElementById('chartDivisi').getContext('2d');
                    chartDivisi = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                { label: 'Tercapai Margin RKAP', data: data.tercapai, backgroundColor: '#28a745', borderRadius: 4 },
                                { label: 'Tidak Tercapai Margin RKAP', data: data.tidak_tercapai, backgroundColor: '#dc3545', borderRadius: 4 }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                            onClick: (e, elements) => {
                                if (elements.length > 0) {
                                    const idx = elements[0].index;
                                    const datasetIdx = elements[0].datasetIndex;
                                    const divisi = data.summary.find(s => s.divisi === data.labels[idx])?.kode_divisi;
                                    const hasilPleno = datasetIdx === 0 ? 'TR' : 'TT';
                                    loadDetailData('divisi', { divisi, hasil_pleno: hasilPleno }, `Detail Data: ${data.labels[idx]} - ${datasetIdx === 0 ? 'Tercapai' : 'Tidak Tercapai'} RKAP`);
                                }
                            }
                        }
                    });
                });
        }

        function loadKategoriChart() {
            const loading = document.getElementById('loadingKategori');
            loading.style.display = 'flex';
            document.getElementById('detailKategori').style.display = 'none';

            fetch('{{ route("laporanhasilplenorab.kategori-data") }}?' + new URLSearchParams(getFilterParams()))
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    kategoriData = data;

                    const tbody = document.querySelector('#summaryTableKategori tbody');
                    tbody.innerHTML = data.summary.map(row => `
                        <tr>
                            <td>${row.bulan}</td>
                            <td>${row.keterangan}</td>
                            <td>${row.jumlah}</td>
                        </tr>
                    `).join('');

                    if (chartKategori) chartKategori.destroy();
                    const ctx = document.getElementById('chartKategori').getContext('2d');
                    chartKategori = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{ data: data.data, backgroundColor: data.colors, borderWidth: 2, borderColor: '#fff' }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const value = context.raw;
                                            const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return `${context.label}: ${value} (${pct}%)`;
                                        }
                                    }
                                }
                            },
                            onClick: (e, elements) => {
                                if (elements.length > 0) {
                                    const idx = elements[0].index;
                                    const hasilPleno = idx === 0 ? 'TR' : 'TT';
                                    loadDetailData('kategori', { hasil_pleno: hasilPleno }, `Detail Data: ${data.labels[idx]}`);
                                }
                            }
                        }
                    });
                });
        }

        function loadDivisiKategoriChart() {
            const loading = document.getElementById('loadingDivisiKategori');
            loading.style.display = 'flex';

            fetch('{{ route("laporanhasilplenorab.divisi-kategori-data") }}?' + new URLSearchParams(getFilterParams()))
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';

                    const tbody = document.querySelector('#summaryTableDivisiKategori tbody');
                    tbody.innerHTML = data.summary.map(row => `
                        <tr>
                            <td>${row.bulan}</td>
                            <td>${row.divisi}</td>
                            <td>${row.hasil}</td>
                            <td>${row.jumlah}</td>
                        </tr>
                    `).join('');

                    if (chartDivisiKategori) chartDivisiKategori.destroy();
                    const ctx = document.getElementById('chartDivisiKategori').getContext('2d');
                    chartDivisiKategori = new Chart(ctx, {
                        type: 'bar',
                        data: { labels: data.labels, datasets: data.datasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            datasets: {
                                bar: { categoryPercentage: 0.7, barPercentage: 0.9 }
                            },
                            scales: {
                                x: { stacked: false },
                                y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                            }
                        }   
                    });
                });
        }

        function loadJenisProyekChart() {
            const loading = document.getElementById('loadingJenisProyek');
            loading.style.display = 'flex';
            document.getElementById('detailJenisProyek').style.display = 'none';

            fetch('{{ route("laporanhasilplenorab.jenis-proyek-data") }}?' + new URLSearchParams(getFilterParams()))
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    jenisProyekData = data;

                    const tbody = document.querySelector('#summaryTableJenisProyek tbody');
                    tbody.innerHTML = data.summary.map(row => `
                        <tr>
                            <td>${row.bulan}</td>
                            <td>${row.keterangan}</td>
                            <td>${row.jenis_proyek}</td>
                            <td>${row.prosentase}</td>
                        </tr>
                    `).join('');

                    if (chartJenisProyek) chartJenisProyek.destroy();
                    const ctx = document.getElementById('chartJenisProyek').getContext('2d');
                    chartJenisProyek = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                { label: 'Tercapai Margin RKAP', data: data.tercapai, backgroundColor: '#28a745', borderRadius: 4 },
                                { label: 'Tidak Tercapai Margin RKAP', data: data.tidak_tercapai, backgroundColor: '#dc3545', borderRadius: 4 }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                            onClick: (e, elements) => {
                                if (elements.length > 0) {
                                    const idx = elements[0].index;
                                    const datasetIdx = elements[0].datasetIndex;
                                    const jenisProyek = data.summary.find(s => s.jenis_proyek === data.labels[idx])?.kode_jenis;
                                    const hasilPleno = datasetIdx === 0 ? 'TR' : 'TT';
                                    loadDetailData('jenis-proyek', { jenis_proyek: jenisProyek, hasil_pleno: hasilPleno }, `Detail Data: ${data.labels[idx]} - ${datasetIdx === 0 ? 'Tercapai' : 'Tidak Tercapai'} RKAP`);
                                }
                            }
                        }
                    });
                });
        }

        function loadDetailData(type, filters, title) {
            const params = { ...getFilterParams(), ...filters };
            const containerId = type === 'divisi' ? 'detailDivisi' : 
                               type === 'kategori' ? 'detailKategori' : 'detailJenisProyek';
            const tableId = type === 'divisi' ? 'detailTableDivisi' : 
                           type === 'kategori' ? 'detailTableKategori' : 'detailTableJenisProyek';
            const titleId = type === 'divisi' ? 'detailTitleDivisi' : 
                           type === 'kategori' ? 'detailTitleKategori' : 'detailTitleJenisProyek';

            document.getElementById(titleId).textContent = title;
            document.getElementById(containerId).style.display = 'block';
            const tbody = document.querySelector(`#${tableId} tbody`);
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">Loading...</td></tr>';

            fetch('{{ route("laporanhasilplenorab.detail-data") }}?' + new URLSearchParams(params))
                .then(response => response.json())
                .then(data => {
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>';
                    } else {
                        tbody.innerHTML = data.data.map(row => `
                            <tr>
                                <td>${row.tgl_pengajuan}</td>
                                <td>${row.io}</td>
                                <td>${row.cost_center}</td>
                                <td>${row.description}</td>
                                <td>${row.customer}</td>
                                <td>${row.divisi}</td>
                                <td>${row.pm}</td>
                                <td>${row.keterangan}</td>
                                <td>${row.hasil_pleno}</td>
                            </tr>
                        `).join('');
                    }
                });
        }
    </script>
    @endpush
</x-layout>
