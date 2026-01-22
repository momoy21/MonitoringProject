<x-layout>
    <x-slot:title>Import Data SAP</x-slot:title>

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">
                    <span class="text-muted fw-light">SAP /</span> Import Data
                </h4>
            </div>
            <div>
                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#logsModal">
                    <i class="bx bx-file me-1"></i> Lihat Logs
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="fw-semibold d-block mb-1">Total Records</span>
                                <h3 class="card-title mb-0" id="statTotalRecords">{{ number_format($stats['total_records']) }}</h3>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-data"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="fw-semibold d-block mb-1">Total Amount</span>
                                <h5 class="card-title mb-0" id="statTotalAmount">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</h5>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-money"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="fw-semibold d-block mb-1">Unique Projects</span>
                                <h3 class="card-title mb-0" id="statUniqueProjects">{{ number_format($stats['unique_projects']) }}</h3>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-folder"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="fw-semibold d-block mb-1">Last Import</span>
                                <h6 class="card-title mb-0" id="statLastImport">
                                    {{ $stats['last_import'] ? \Carbon\Carbon::parse($stats['last_import'])->format('d/m/Y H:i') : '-' }}
                                </h6>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-time"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FTP Import Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bx bx-server me-2"></i>Import dari FTP Server</h5>
                    <small class="text-muted" id="ftpConnectionInfo">Checking connection...</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshFtp">
                        <i class="bx bx-refresh me-1"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" id="btnTestFtp">
                        <i class="bx bx-plug me-1"></i> Test Koneksi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex gap-2 align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ftpForceImport">
                        <label class="form-check-label" for="ftpForceImport">
                            <strong>Force Import</strong> (import ulang jika sudah ada)
                        </label>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="ftpFilesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Nama File</th>
                                <th>Ukuran</th>
                                <th>Terakhir Diubah</th>
                                <th>Status</th>
                                <th style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ftpFilesBody">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bx bx-loader bx-spin" style="font-size: 24px;"></i>
                                    <p class="text-muted mt-2 mb-0">Memuat daftar file dari FTP...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Source Files -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-file me-2"></i>File yang Sudah Diimport</h5>
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnTruncateAll">
                    <i class="bx bx-trash me-1"></i> Hapus Semua Data
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="sourceFilesTable">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Jumlah Record</th>
                                <th>Total Amount</th>
                                <th>Waktu Import</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sourceFilesBody">
                            <tr>
                                <td colspan="5" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-table me-2"></i>Data SAP</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="searchInput"
                           placeholder="Cari..." style="width: 200px;">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Internal Order</th>
                                <th>CC Projek</th>
                                <th>Description IO</th>
                                <th>Cost Element</th>
                                <th>Description CE</th>
                                <th class="text-end">Amount</th>
                                <th>Posting Date</th>
                                <th>Profit Center</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            @forelse($data as $item)
                            <tr>
                                <td>{{ $item->internal_order }}</td>
                                <td><strong>{{ $item->cc_projek }}</strong></td>
                                <td>{{ $item->description_io }}</td>
                                <td>{{ $item->cost_element }}</td>
                                <td>{{ $item->description_ce }}</td>
                                <td class="text-end">{{ number_format($item->amount_local, 0, ',', '.') }}</td>
                                <td>{{ $item->posting_date ? $item->posting_date->format('d/m/Y') : '-' }}</td>
                                <td>{{ $item->profit_center }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bx bx-data" style="font-size: 48px; color: #d9dee3;"></i>
                                    <p class="text-muted mt-2">Belum ada data SAP</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($data->hasPages())
            <div class="card-footer">
                {{ $data->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Logs Modal -->
    <div class="modal fade" id="logsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import & Error Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#importLogsTab">Import Logs</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#errorLogsTab">Error Logs</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="importLogsTab">
                            <pre id="importLogsContent" class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 12px;">Loading...</pre>
                        </div>
                        <div class="tab-pane fade" id="errorLogsTab">
                            <pre id="errorLogsContent" class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 12px;">Loading...</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        loadSourceFiles();
        loadFtpFiles();
        checkFtpConnection();

        // ================================================================
        // FTP FUNCTIONS
        // ================================================================
        
        // Check FTP connection on load
        function checkFtpConnection() {
            $.get('{{ route("sap.ftp.info") }}', function(response) {
                if (response.success) {
                    const info = response.info;
                    const statusClass = response.status === 'connected' ? 'text-success' : 'text-danger';
                    const statusIcon = response.status === 'connected' ? 'bx-check-circle' : 'bx-x-circle';
                    $('#ftpConnectionInfo').html(`
                        <i class="bx ${statusIcon} ${statusClass}"></i> 
                        ${info.host}:${info.port} - ${response.status_message}
                    `);
                }
            }).fail(function() {
                $('#ftpConnectionInfo').html('<i class="bx bx-x-circle text-danger"></i> Tidak dapat memeriksa koneksi FTP');
            });
        }

        // Load FTP files
        function loadFtpFiles() {
            $('#ftpFilesBody').html(`
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="bx bx-loader bx-spin" style="font-size: 24px;"></i>
                        <p class="text-muted mt-2 mb-0">Memuat daftar file dari FTP...</p>
                    </td>
                </tr>
            `);

            $.get('{{ route("sap.ftp.files") }}', function(response) {
                if (response.success && response.files && response.files.length > 0) {
                    let html = '';
                    response.files.forEach(function(file) {
                        const statusBadge = file.already_imported 
                            ? '<span class="badge bg-label-success">Sudah Diimport</span>'
                            : '<span class="badge bg-label-warning">Belum Diimport</span>';
                        
                        const importBtnClass = file.already_imported ? 'btn-outline-secondary' : 'btn-primary';
                        const importBtnText = file.already_imported ? 'Re-import' : 'Import';
                        
                        html += `
                            <tr>
                                <td>
                                    <i class="bx bx-file text-primary me-1"></i>
                                    <strong>${file.name}</strong>
                                </td>
                                <td>${file.size_formatted}</td>
                                <td>${file.last_modified || '-'}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <button type="button" class="btn ${importBtnClass} btn-sm btn-ftp-import"
                                            data-path="${file.path}" data-name="${file.name}" data-imported="${file.already_imported}">
                                        <i class="bx bx-import me-1"></i> ${importBtnText}
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#ftpFilesBody').html(html);
                    bindFtpImportButtons();
                } else {
                    let message = response.message || 'Tidak ada file CSV/TXT di folder FTP';
                    $('#ftpFilesBody').html(`
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bx bx-folder-open" style="font-size: 48px; color: #d9dee3;"></i>
                                <p class="text-muted mt-2 mb-0">${message}</p>
                            </td>
                        </tr>
                    `);
                }
            }).fail(function(xhr) {
                const response = xhr.responseJSON || {};
                $('#ftpFilesBody').html(`
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bx bx-error-circle text-danger" style="font-size: 48px;"></i>
                            <p class="text-danger mt-2 mb-0">Gagal koneksi ke FTP Server</p>
                            <small class="text-muted">${response.message || 'Periksa konfigurasi FTP'}</small>
                        </td>
                    </tr>
                `);
            });
        }

        // Bind import buttons
        function bindFtpImportButtons() {
            $('.btn-ftp-import').off('click').on('click', function() {
                const btn = $(this);
                const ftpPath = btn.data('path');
                const fileName = btn.data('name');
                const alreadyImported = btn.data('imported');
                const forceImport = $('#ftpForceImport').is(':checked');

                // Confirm jika sudah diimport dan tidak force
                if (alreadyImported && !forceImport) {
                    if (!confirm(`File "${fileName}" sudah pernah diimport. Lanjutkan import ulang?`)) {
                        return;
                    }
                }

                const originalText = btn.html();
                btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

                $.ajax({
                    url: '{{ route("sap.ftp.import") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ftp_path: ftpPath,
                        force: forceImport || alreadyImported
                    },
                    success: function(response) {
                        let message = response.message;
                        if (response.ftp_moved) {
                            message += '<br><small class="text-success">File dipindahkan ke: ' + response.ftp_new_path + '</small>';
                        }
                        showAlert(message, 'success');
                        loadFtpFiles();
                        loadSourceFiles();
                        setTimeout(() => location.reload(), 2000);
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        let alertType = 'danger';
                        let message = response.message || 'Gagal import dari FTP';

                        if (response.error_type === 'DUPLICATE_FILE') {
                            alertType = 'warning';
                            message += '<br><small>Centang "Force Import" untuk import ulang.</small>';
                        }

                        showAlert(message, alertType);
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        }

        // Refresh FTP files
        $('#btnRefreshFtp').on('click', function() {
            loadFtpFiles();
            checkFtpConnection();
        });

        // Test FTP connection
        $('#btnTestFtp').on('click', function() {
            const btn = $(this);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Testing...');

            $.get('{{ route("sap.ftp.test") }}', function(response) {
                if (response.success) {
                    showAlert('Koneksi FTP berhasil! ' + (response.file_count || 0) + ' file ditemukan.', 'success');
                } else {
                    showAlert('Koneksi FTP gagal: ' + response.message, 'danger');
                }
                checkFtpConnection();
            }).fail(function(xhr) {
                const response = xhr.responseJSON || {};
                showAlert('Koneksi FTP gagal: ' + (response.message || 'Unknown error'), 'danger');
            }).always(function() {
                btn.prop('disabled', false).html(originalText);
            });
        });

        // ================================================================
        // OTHER FUNCTIONS
        // ================================================================

        // Truncate all
        $('#btnTruncateAll').on('click', function() {
            if (!confirm('Apakah Anda yakin ingin menghapus SEMUA data SAP?')) return;

            $.ajax({
                url: '{{ route("sap.truncate") }}',
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    showAlert(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    showAlert('Gagal menghapus: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
                }
            });
        });

        // Load source files
        function loadSourceFiles() {
            $.get('{{ route("sap.sourceFiles") }}', function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(file) {
                        html += `
                            <tr>
                                <td>${file.source_file || '-'}</td>
                                <td>${Number(file.record_count).toLocaleString('id-ID')}</td>
                                <td>Rp ${Number(file.total_amount).toLocaleString('id-ID')}</td>
                                <td>${file.imported_at ? new Date(file.imported_at).toLocaleString('id-ID') : '-'}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-source"
                                            data-source="${file.source_file}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#sourceFilesBody').html(html);

                    $('.btn-delete-source').on('click', function() {
                        const sourceFile = $(this).data('source');
                        if (confirm(`Hapus semua data dari file "${sourceFile}"?`)) {
                            deleteBySource(sourceFile);
                        }
                    });
                } else {
                    $('#sourceFilesBody').html('<tr><td colspan="5" class="text-center text-muted">Belum ada file yang diimport</td></tr>');
                }
            });
        }

        function deleteBySource(sourceFile) {
            $.ajax({
                url: '{{ route("sap.deleteBySource") }}',
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}', source_file: sourceFile },
                success: function(response) {
                    showAlert(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    showAlert('Gagal menghapus: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
                }
            });
        }

        // Load logs when modal opens
        $('#logsModal').on('show.bs.modal', function() {
            $.get('{{ route("sap.importLogs") }}', function(response) {
                $('#importLogsContent').text(response.logs || 'Tidak ada log');
            });
            $.get('{{ route("sap.errorLogs") }}', function(response) {
                $('#errorLogsContent').text(response.logs || 'Tidak ada error');
            });
        });

        function showAlert(message, type) {
            $('#alertContainer').html(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            setTimeout(() => $('#alertContainer .alert').fadeOut(), 5000);
        }
    });
    </script>
    @endpush
</x-layout>