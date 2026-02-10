<x-layout title="Interface Lembur ke EMS">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bx bx-time-five text-primary me-2"></i>Interface Lembur ke EMS
                </h4>
                <p class="text-muted mb-0">Sinkronisasi data kuota lembur ke sistem EMS via FTP</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#logsModal">
                    <i class="bx bx-history me-1"></i> History Logs
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnTestFtp">
                    <i class="bx bx-plug me-1"></i> Test FTP
                </button>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- KPI Stats Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Pending -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-warning border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-semibold">Belum Terkirim</p>
                                <h3 class="mb-0 fw-bold text-warning" id="statPending">{{ number_format($stats['total_pending']) }}</h3>
                                <small class="text-muted">menunggu sinkronisasi</small>
                            </div>
                            <div class="avatar bg-label-warning">
                                <i class="bx bx-hourglass fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Synced -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-success border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-semibold">Sudah Terkirim</p>
                                <h3 class="mb-0 fw-bold text-success" id="statSynced">{{ number_format($stats['total_synced']) }}</h3>
                                <small class="text-muted">data berhasil terkirim</small>
                            </div>
                            <div class="avatar bg-label-success">
                                <i class="bx bx-check-double fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Logs -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-semibold">Sukses</p>
                                <h3 class="mb-0 fw-bold text-primary" id="statSuccess">{{ number_format($stats['success_logs']) }}</h3>
                                <small class="text-muted">proses berhasil</small>
                            </div>
                            <div class="avatar bg-label-primary">
                                <i class="bx bx-check-circle fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed Logs -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-start border-danger border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-semibold">Gagal</p>
                                <h3 class="mb-0 fw-bold text-danger" id="statFailed">{{ number_format($stats['failed_logs']) }}</h3>
                                <small class="text-muted">proses gagal</small>
                            </div>
                            <div class="avatar bg-label-danger">
                                <i class="bx bx-x-circle fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filter Periode</h5>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="periodeAwal" class="form-label">Periode Awal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="periodeAwal" name="periode_awal" required>
                        </div>
                        <div class="col-md-3">
                            <label for="periodeAkhir" class="form-label">Periode Akhir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="periodeAkhir" name="periode_akhir" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="btnSubmit">
                                    <i class="bx bx-search me-1"></i> Submit
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-success" id="btnSync" disabled>
                                    <i class="bx bx-sync me-1"></i> Sinkronisasi ke EMS
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card" id="dataCard" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-table me-2"></i>Data Kuota Lembur</h5>
                <span class="badge bg-primary" id="totalRecords">0 Records</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Cost Center</th>
                                <th>Dok IO</th>
                                <th>Bulan</th>
                                <th>Periode Awal</th>
                                <th>Periode Akhir</th>
                                <th class="text-center">WD</th>
                                <th class="text-center">WE</th>
                                <th class="text-center">HN</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            <!-- Data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div class="card" id="emptyCard">
            <div class="card-body text-center py-5">
                <i class="bx bx-search-alt-2 text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Pilih Periode</h5>
                <p class="text-muted">Masukkan periode awal dan periode akhir untuk menampilkan data kuota lembur.</p>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
                <div class="spinner-border text-light mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="loadingText">Memproses...</h5>
            </div>
        </div>
    </div>

    <!-- Logs Modal -->
    <div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logsModalLabel">
                        <i class="bx bx-history me-2"></i>History Interface Logs
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Periode</th>
                                    <th>Filename</th>
                                    <th class="text-center">Records</th>
                                    <th class="text-center">Status</th>
                                    <th>User</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $log->periode_awal->format('d/m/Y') }} - {{ $log->periode_akhir->format('d/m/Y') }}</td>
                                    <td><code>{{ $log->filename ?? '-' }}</code></td>
                                    <td class="text-center">{{ number_format($log->total_records) }}</td>
                                    <td class="text-center">
                                        @if($log->status === 'success')
                                            <span class="badge bg-success">Sukses</span>
                                        @else
                                            <span class="badge bg-danger">Gagal</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->user?->name ?? '-' }}</td>
                                    <td><small>{{ Str::limit($log->message, 50) }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada history logs</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnSync = document.getElementById('btnSync');
            const btnTestFtp = document.getElementById('btnTestFtp');
            const dataCard = document.getElementById('dataCard');
            const emptyCard = document.getElementById('emptyCard');
            const dataTableBody = document.getElementById('dataTableBody');
            const totalRecords = document.getElementById('totalRecords');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const loadingText = document.getElementById('loadingText');
            const alertContainer = document.getElementById('alertContainer');

            let currentPeriodeAwal = null;
            let currentPeriodeAkhir = null;
            let currentData = [];

            // Show alert
            function showAlert(type, message) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                alertContainer.innerHTML = alertHtml;
                
                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    const alert = alertContainer.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }

            // Show loading
            function showLoading(text = 'Memproses...') {
                loadingText.textContent = text;
                loadingOverlay.classList.remove('d-none');
            }

            // Hide loading
            function hideLoading() {
                loadingOverlay.classList.add('d-none');
            }

            // Submit form - Fetch data
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const periodeAwal = document.getElementById('periodeAwal').value;
                const periodeAkhir = document.getElementById('periodeAkhir').value;

                if (!periodeAwal || !periodeAkhir) {
                    showAlert('warning', 'Periode Awal dan Periode Akhir harus diisi!');
                    return;
                }

                if (periodeAwal > periodeAkhir) {
                    showAlert('danger', 'Periode Akhir harus sama atau setelah Periode Awal!');
                    return;
                }

                showLoading('Mengambil data...');

                fetch('{{ route("lembur.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        periode_awal: periodeAwal,
                        periode_akhir: periodeAkhir,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success) {
                        currentPeriodeAwal = data.periode_awal;
                        currentPeriodeAkhir = data.periode_akhir;
                        currentData = data.data;
                        
                        renderTable(data.data);
                        totalRecords.textContent = data.total + ' Records';
                        
                        dataCard.style.display = 'block';
                        emptyCard.style.display = 'none';
                        btnSync.disabled = false;

                        showAlert('success', `Ditemukan ${data.total} data untuk disinkronkan.`);
                    } else {
                        showAlert('warning', data.message);
                        dataCard.style.display = 'none';
                        emptyCard.style.display = 'block';
                        btnSync.disabled = true;
                        currentData = [];
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('danger', 'Terjadi kesalahan: ' + error.message);
                    console.error('Error:', error);
                });
            });

            // Render table
            function renderTable(data) {
                let html = '';
                data.forEach((item, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><code>${item.nik}</code></td>
                            <td>${item.nama}</td>
                            <td>${item.cost_center}</td>
                            <td>${item.dok_io}</td>
                            <td class="text-center">${item.bulan}</td>
                            <td>${item.periode_awal}</td>
                            <td>${item.periode_akhir}</td>
                            <td class="text-center">${item.jml_wd}</td>
                            <td class="text-center">${item.jml_we}</td>
                            <td class="text-center">${item.jml_hn}</td>
                            <td class="text-center">
                                <span class="badge bg-warning">${item.status}</span>
                            </td>
                        </tr>
                    `;
                });
                dataTableBody.innerHTML = html;
            }

            // Sync button - Export to CSV and upload to FTP
            btnSync.addEventListener('click', function() {
                if (currentData.length === 0) {
                    showAlert('warning', 'Tidak ada data untuk disinkronkan!');
                    return;
                }

                if (!confirm(`Anda akan mengirim ${currentData.length} data ke EMS via FTP. Lanjutkan?`)) {
                    return;
                }

                showLoading('Sinkronisasi ke EMS...');

                fetch('{{ route("lembur.sync") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        periode_awal: currentPeriodeAwal,
                        periode_akhir: currentPeriodeAkhir,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success) {
                        showAlert('success', data.message);
                        
                        // Clear table and disable sync button
                        dataCard.style.display = 'none';
                        emptyCard.style.display = 'block';
                        btnSync.disabled = true;
                        currentData = [];

                        // Reload page after 2 seconds to refresh stats
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('danger', 'Terjadi kesalahan: ' + error.message);
                    console.error('Error:', error);
                });
            });

            // Test FTP connection
            btnTestFtp.addEventListener('click', function() {
                showLoading('Menguji koneksi FTP...');

                fetch('{{ route("lembur.testFtp") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success) {
                        let message = `<strong>Koneksi FTP Berhasil!</strong><br>`;
                        message += `Host: ${data.ftp_host || '-'}<br>`;
                        message += `Target Path: ${data.target_directory}<br>`;
                        message += `Directory Exists: ${data.target_exists ? '<span class="text-success">Ya</span>' : '<span class="text-warning">Tidak (akan dibuat saat sync)</span>'}<br>`;
                        if (data.directory_created) {
                            message += `<span class="text-success">Directory berhasil dibuat!</span><br>`;
                        }
                        if (data.root_dirs && data.root_dirs.length > 0) {
                            message += `Root Directories: ${data.root_dirs.join(', ')}`;
                        }
                        showAlert('success', message);
                    } else {
                        showAlert('danger', `<strong>Koneksi FTP Gagal!</strong><br>Host: ${data.ftp_host || '-'}<br>Target: ${data.target_directory || '-'}<br>Error: ${data.message}`);
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('danger', 'Error: ' + error.message);
                });
            });
        });
    </script>
    @endpush
</x-layout>
