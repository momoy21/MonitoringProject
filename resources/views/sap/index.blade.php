<x-layout>
    <x-slot:title>Import Data SAP</x-slot:title>

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header - Enhanced -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bx bx-data text-primary me-2"></i>Import Data SAP
                </h4>
                <p class="text-muted mb-0">Kelola dan monitoring data transaksi SAP</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#logsModal">
                    <i class="bx bx-history me-1"></i> History & Logs
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnRefreshAll">
                    <i class="bx bx-refresh me-1"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Enhanced KPI Stats Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Records -->
            <div class="col-xl-4 col-md-6">
                <div class="card border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-semibold">Total Records</p>
                                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_records']) }}</h3>
                                <small class="text-muted">dari {{ $stats['unique_files'] ?? 0 }} file</small>
                            </div>
                            <div class="avatar bg-label-primary">
                                <i class="bx bx-data fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Info Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-label-info">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-folder-open fs-3 me-3"></i>
                            <div>
                                <h6 class="mb-0">{{ $stats['unique_projects'] }} Proyek Aktif</h6>
                                <small class="text-muted">Proyek dengan transaksi SAP</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-label-secondary">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-time fs-3 me-3"></i>
                            <div>
                                <h6 class="mb-0">
                                    @if($stats['last_import'])
                                        {{ \Carbon\Carbon::parse($stats['last_import'])->diffForHumans() }}
                                    @else
                                        Belum ada import
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    @if($stats['last_import'])
                                        {{ \Carbon\Carbon::parse($stats['last_import'])->format('d M Y, H:i') }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapping Status to Aktual Biaya -->
        <div class="card mb-4 border-start border-info border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-label-info me-3">
                            <i class="bx bx-transfer-alt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Mapping ke Aktual Biaya</h6>
                            <small class="text-muted">Status sinkronisasi data SAP ke tabel Aktual Biaya</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <div class="text-center">
                            <h5 class="mb-0 text-success">{{ number_format($stats['total_mapped'] ?? 0) }}</h5>
                            <small class="text-muted">Berhasil Mapping</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0 {{ ($stats['total_unmapped'] ?? 0) > 0 ? 'text-warning' : 'text-muted' }}">{{ number_format($stats['total_unmapped'] ?? 0) }}</h5>
                            <small class="text-muted">Belum Mapping</small>
                        </div>
                        @php
                            $mappingPercent = ($stats['total_records'] > 0) 
                                ? round(($stats['total_mapped'] ?? 0) / $stats['total_records'] * 100, 1)
                                : 0;
                        @endphp
                        <div class="text-center">
                            <h5 class="mb-0 {{ $mappingPercent >= 90 ? 'text-success' : ($mappingPercent >= 50 ? 'text-warning' : 'text-danger') }}">{{ $mappingPercent }}%</h5>
                            <small class="text-muted">Coverage</small>
                        </div>
                    </div>
                </div>
                @if($mappingPercent < 100 && ($stats['total_unmapped'] ?? 0) > 0)
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $mappingPercent }}%"></div>
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ 100 - $mappingPercent }}%"></div>
                </div>
                @endif
            </div>
        </div>

        <!-- FTP Monitoring Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bx bx-server me-2 text-primary"></i>File di FTP Server</h5>
                    <small class="text-muted" id="ftpConnectionInfo">
                        <i class="bx bx-loader-alt bx-spin"></i> Memeriksa koneksi...
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshFtp">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" id="btnTestFtp">
                        <i class="bx bx-plug"></i> Test
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-light border mb-3 d-flex align-items-center">
                    <i class="bx bx-info-circle text-info fs-4 me-2"></i>
                    <div>
                        <strong>Mode Otomatis Aktif</strong> - File akan diimport secara otomatis oleh scheduler.
                        Berhasil → <code class="text-success">/Processed</code> | Gagal → <code class="text-danger">/Error</code>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="ftpFilesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50%;">Nama File</th>
                                <th>Ukuran</th>
                                <th>Terakhir Diubah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="ftpFilesBody">
                            <tr>
                                <td colspan="4" class="text-center py-4">
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
                <div>
                    <h5 class="mb-0"><i class="bx bx-check-circle me-2 text-success"></i>File yang Sudah Diimport</h5>
                    <small class="text-muted">Riwayat file yang berhasil diproses</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshSourceFiles">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnTruncateAll">
                        <i class="bx bx-trash me-1"></i> Reset Data
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="sourceFilesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Nama File</th>
                                <th class="text-center">Records</th>
                                <th class="text-end">Total Biaya</th>
                                <th class="text-end">Total Pendapatan</th>
                                <th>Waktu Import</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sourceFilesBody">
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0"><i class="bx bx-table me-2 text-info"></i>Detail Data SAP</h5>
                    <small class="text-muted">{{ number_format($data->total()) }} transaksi</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari transaksi...">
                    </div>
                    <select class="form-select" id="perPageSelect" style="width: auto;">
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Internal Order</th>
                                <th>CC Projek</th>
                                <th style="max-width: 200px;">Description</th>
                                <th>Cost Element</th>
                                <th class="text-end">Amount</th>
                                <th>Posting Date</th>
                                <th>Profit Center</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            @forelse($data as $item)
                            <tr>
                                <td>
                                    <code class="text-primary">{{ $item->internal_order }}</code>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $item->cc_projek }}</span>
                                </td>
                                <td style="max-width: 200px;">
                                    <div class="text-truncate" title="{{ $item->description_io }}">
                                        {{ $item->description_io ?: '-' }}
                                    </div>
                                </td>
                                <td>
                                    <code>{{ $item->cost_element }}</code>
                                    @if($item->description_ce)
                                    <div class="small text-muted text-truncate" style="max-width: 150px;" title="{{ $item->description_ce }}">
                                        {{ $item->description_ce }}
                                    </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @php
                                        $amount = $item->amount_local;
                                        $isNegative = $amount < 0;
                                    @endphp
                                    <span class="fw-semibold {{ $isNegative ? 'text-success' : 'text-danger' }}">
                                        {{ $isNegative ? '+' : '-' }} Rp {{ number_format(abs($amount), 0, ',', '.') }}
                                    </span>
                                    <div class="small text-muted">
                                        {{ $isNegative ? 'Pendapatan' : 'Biaya' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="text-nowrap">{{ $item->posting_date ? $item->posting_date->format('d/m/Y') : '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $item->profit_center ?: '-' }}</span>
                                    @if($item->description_pca)
                                    <div class="small text-muted">{{ $item->description_pca }}</div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bx bx-data" style="font-size: 64px; color: #d9dee3;"></i>
                                    </div>
                                    <h6 class="text-muted">Belum ada data SAP</h6>
                                    <p class="text-muted small mb-0">Data akan muncul setelah proses import berjalan</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($data->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            {{-- Previous --}}
                            @if($data->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo;</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $data->previousPageUrl() }}" rel="prev">&laquo;</a>
                                </li>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $start = max(1, $data->currentPage() - 2);
                                $end = min($data->lastPage(), $data->currentPage() + 2);
                            @endphp

                            @if($start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $data->url(1) }}">1</a>
                                </li>
                                @if($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $data->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $i }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $data->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endfor

                            @if($end < $data->lastPage())
                                @if($end < $data->lastPage() - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $data->url($data->lastPage()) }}">{{ $data->lastPage() }}</a>
                                </li>
                            @endif

                            {{-- Next --}}
                            @if($data->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $data->nextPageUrl() }}" rel="next">&raquo;</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Logs Modal - Enhanced Version -->
    <div class="modal fade" id="logsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="bx bx-history me-2 text-primary"></i>Import History & Logs
                        </h5>
                        <small class="text-muted">Riwayat aktivitas import SAP</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <!-- Log Controls -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bx bx-calendar"></i></span>
                                <input type="date" class="form-control" id="logDate" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="logSearch" placeholder="Cari file atau pesan...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100" id="btnRefreshLogs">
                                <i class="bx bx-refresh me-1"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Log Stats Summary -->
                    <div class="row g-3 mb-4" id="logStats">
                        <div class="col-md-3 col-6">
                            <div class="card bg-label-primary mb-0">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-primary"><i class="bx bx-file"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="statTotal">0</h6>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-label-success mb-0">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-success"><i class="bx bx-check"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="statSuccess">0</h6>
                                            <small class="text-muted">Sukses</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-label-danger mb-0">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-danger"><i class="bx bx-x"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="statFailed">0</h6>
                                            <small class="text-muted">Gagal</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card bg-label-warning mb-0">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-warning"><i class="bx bx-copy"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="statDuplicate">0</h6>
                                            <small class="text-muted">Duplikat</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Log Type Tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabTimeline">
                                <i class="bx bx-time-five me-1"></i> Timeline
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabErrors">
                                <i class="bx bx-error-circle me-1"></i> Errors
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabScheduler">
                                <i class="bx bx-timer me-1"></i> Scheduler
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabRaw">
                                <i class="bx bx-code-alt me-1"></i> Raw
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content border border-top-0 rounded-bottom">
                        <!-- Timeline Tab -->
                        <div class="tab-pane fade show active p-3" id="tabTimeline">
                            <div id="timelineContainer" class="timeline-container">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted mt-2 mb-0">Memuat log...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Errors Tab -->
                        <div class="tab-pane fade p-3" id="tabErrors">
                            <div id="errorsContainer">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted mt-2 mb-0">Memuat error log...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Scheduler Tab -->
                        <div class="tab-pane fade p-3" id="tabScheduler">
                            <div class="alert alert-info mb-3">
                                <i class="bx bx-info-circle me-1"></i>
                                Log dari scheduler auto-import (dijalankan setiap hari)
                            </div>
                            <div id="schedulerContainer">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted mt-2 mb-0">Memuat scheduler log...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Raw Log Tab -->
                        <div class="tab-pane fade p-0" id="tabRaw">
                            <div class="raw-log-header p-2 bg-dark text-white d-flex justify-content-between align-items-center">
                                <span><i class="bx bx-terminal me-1"></i> Raw Import Log</span>
                                <button class="btn btn-sm btn-outline-light" id="btnCopyRawLog">
                                    <i class="bx bx-copy"></i> Copy
                                </button>
                            </div>
                            <pre id="rawLogContent" class="raw-log-content m-0">Loading...</pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">
                        <i class="bx bx-folder me-1"></i> storage/app/sap/LOG/
                    </small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Timeline Styles */
    .timeline-container {
        max-height: 400px;
        overflow-y: auto;
    }
    .timeline-item {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }
    
    /* Avatar Styles for KPI Cards */
    .avatar {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    .avatar.avatar-sm {
        width: 32px;
        height: 32px;
    }
    
    .timeline-item:hover {
        background-color: #f8f9fa;
    }
    .timeline-item:last-child {
        border-bottom: none;
    }
    .timeline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    .timeline-icon.success { background: #d4edda; color: #28a745; }
    .timeline-icon.failed { background: #f8d7da; color: #dc3545; }
    .timeline-icon.started { background: #cce5ff; color: #007bff; }
    .timeline-icon.duplicate { background: #fff3cd; color: #ffc107; }
    .timeline-icon.rejected { background: #f8d7da; color: #dc3545; }
    .timeline-content {
        flex: 1;
        min-width: 0;
    }
    .timeline-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }
    .timeline-file {
        font-family: 'Consolas', monospace;
        font-size: 13px;
        color: #6c757d;
        background: #f1f3f4;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-block;
        margin-bottom: 4px;
    }
    .timeline-message {
        font-size: 13px;
        color: #666;
    }
    .timeline-time {
        font-size: 12px;
        color: #999;
        white-space: nowrap;
        margin-left: 15px;
    }
    .timeline-badge {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    /* Error Cards */
    .error-card {
        border-left: 4px solid #dc3545;
        background: #fff;
        margin-bottom: 10px;
        padding: 12px 15px;
        border-radius: 0 8px 8px 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .error-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 8px;
    }
    .error-file {
        font-family: 'Consolas', monospace;
        font-size: 13px;
        color: #dc3545;
        font-weight: 600;
    }
    .error-time {
        font-size: 11px;
        color: #999;
    }
    .error-type {
        font-size: 11px;
        background: #f8d7da;
        color: #721c24;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 500;
    }
    .error-message {
        font-size: 13px;
        color: #555;
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 6px;
        font-family: 'Consolas', monospace;
    }
    .error-row {
        font-size: 12px;
        color: #6c757d;
    }

    /* Scheduler Log */
    .scheduler-session {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    .scheduler-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .scheduler-body {
        padding: 15px;
    }
    .scheduler-stat {
        display: inline-flex;
        align-items: center;
        margin-right: 20px;
        font-size: 13px;
    }
    .scheduler-stat i {
        margin-right: 5px;
    }
    .scheduler-stat.success { color: #28a745; }
    .scheduler-stat.failed { color: #dc3545; }
    .scheduler-stat.skipped { color: #6c757d; }
    .scheduler-files {
        margin-top: 10px;
    }
    .scheduler-file-item {
        display: flex;
        align-items: center;
        padding: 6px 0;
        font-size: 13px;
        border-bottom: 1px dashed #eee;
    }
    .scheduler-file-item:last-child {
        border-bottom: none;
    }
    .scheduler-file-icon {
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }

    /* Raw Log */
    .raw-log-content {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 15px;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 12px;
        line-height: 1.6;
        max-height: 350px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .raw-log-content::-webkit-scrollbar {
        width: 8px;
    }
    .raw-log-content::-webkit-scrollbar-track {
        background: #1e1e1e;
    }
    .raw-log-content::-webkit-scrollbar-thumb {
        background: #4a4a4a;
        border-radius: 4px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #dee2e6;
    }

    /* Search highlight */
    .highlight {
        background-color: #fff3cd;
        padding: 0 2px;
        border-radius: 2px;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
    }
    .nav-tabs .nav-link.active {
        font-weight: 600;
    }

    /* Pagination Fix - Prevent SVG overflow */
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link svg {
        width: 16px !important;
        height: 16px !important;
        display: inline-block;
    }
    .pagination .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0.375rem 0.75rem;
    }
    .card-footer .pagination {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    </style>

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
                            : '<span class="badge bg-label-warning">Menunggu Import</span>';
                        
                        html += `
                            <tr>
                                <td>
                                    <i class="bx bx-file text-primary me-1"></i>
                                    <strong>${file.name}</strong>
                                </td>
                                <td>${file.size_formatted}</td>
                                <td>${file.last_modified || '-'}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `;
                    });
                    $('#ftpFilesBody').html(html);
                } else {
                    let message = response.message || 'Tidak ada file CSV/TXT di folder FTP';
                    $('#ftpFilesBody').html(`
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bx bx-check-circle text-success" style="font-size: 48px;"></i>
                                <p class="text-muted mt-2 mb-0">${message}</p>
                                <small class="text-success">Semua file sudah diproses oleh scheduler</small>
                            </td>
                        </tr>
                    `);
                }
            }).fail(function(xhr) {
                const response = xhr.responseJSON || {};
                $('#ftpFilesBody').html(`
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <i class="bx bx-error-circle text-danger" style="font-size: 48px;"></i>
                            <p class="text-danger mt-2 mb-0">Gagal koneksi ke FTP Server</p>
                            <small class="text-muted">${response.message || 'Periksa konfigurasi FTP'}</small>
                        </td>
                    </tr>
                `);
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

        // Refresh Source Files
        $('#btnRefreshSourceFiles').on('click', function() {
            const btn = $(this);
            btn.find('i').addClass('bx-spin');
            loadSourceFiles();
            setTimeout(() => btn.find('i').removeClass('bx-spin'), 500);
        });

        // Refresh Data Table / Refresh All
        $('#btnRefreshData, #btnRefreshAll').on('click', function() {
            location.reload();
        });

        // Per page selector
        $('#perPageSelect').on('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', $(this).val());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });

        // Search with debounce
        let dataSearchTimeout;
        $('#searchInput').on('input', function() {
            clearTimeout(dataSearchTimeout);
            const query = $(this).val();
            dataSearchTimeout = setTimeout(() => {
                const url = new URL(window.location.href);
                if (query) {
                    url.searchParams.set('search', query);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', 1);
                window.location.href = url.toString();
            }, 500);
        });

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

        // Load source files - Enhanced with Biaya/Pendapatan split
        function loadSourceFiles() {
            $.get('{{ route("sap.sourceFiles") }}', function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(file) {
                        // Calculate biaya (positive amounts) and pendapatan (negative amounts)
                        const totalAmount = parseFloat(file.total_amount) || 0;
                        const biaya = file.total_biaya || (totalAmount > 0 ? totalAmount : 0);
                        const pendapatan = file.total_pendapatan || (totalAmount < 0 ? Math.abs(totalAmount) : 0);
                        
                        html += `
                            <tr>
                                <td>
                                    <i class="bx bx-file-blank text-success me-2"></i>
                                    <span class="fw-medium">${file.source_file || '-'}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">${Number(file.record_count).toLocaleString('id-ID')}</span>
                                </td>
                                <td class="text-end text-danger">
                                    Rp ${Number(biaya).toLocaleString('id-ID')}
                                </td>
                                <td class="text-end text-success">
                                    Rp ${Number(pendapatan).toLocaleString('id-ID')}
                                </td>
                                <td><small class="text-muted">${file.imported_at ? new Date(file.imported_at).toLocaleString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) : '-'}</small></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-source"
                                            data-source="${file.source_file}" title="Hapus data file ini">
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
                    $('#sourceFilesBody').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bx bx-file" style="font-size: 32px;"></i>
                                <p class="mb-0 mt-2">Belum ada file yang diimport</p>
                            </td>
                        </tr>
                    `);
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
            loadLogs();
        });

        // Refresh logs button
        $('#btnRefreshLogs').on('click', function() {
            loadLogs();
        });

        // Date change for logs
        $('#logDate').on('change', function() {
            loadLogs();
        });

        // Search filter
        let searchTimeout;
        $('#logSearch').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterLogs($(this).val());
            }, 300);
        });

        // Copy raw log
        $('#btnCopyRawLog').on('click', function() {
            const text = $('#rawLogContent').text();
            navigator.clipboard.writeText(text).then(() => {
                $(this).html('<i class="bx bx-check"></i> Copied!');
                setTimeout(() => {
                    $(this).html('<i class="bx bx-copy"></i> Copy');
                }, 2000);
            });
        });

        function loadLogs() {
            const selectedDate = $('#logDate').val();
            const formattedDate = selectedDate.replace(/-/g, '');

            // Reset stats
            $('#statTotal, #statSuccess, #statFailed, #statDuplicate').text('0');

            // Show loading
            $('#timelineContainer').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat log...</p>
                </div>
            `);
            $('#errorsContainer').html($('#timelineContainer').html());
            $('#schedulerContainer').html($('#timelineContainer').html());
            $('#rawLogContent').text('Loading...');

            // Load import logs for timeline
            $.get('{{ route("sap.importLogs") }}', { date: formattedDate }, function(response) {
                const logs = response.logs || '';
                renderTimeline(logs);
                $('#rawLogContent').text(logs || 'Tidak ada log untuk tanggal ini');
            }).fail(function() {
                $('#timelineContainer').html(createEmptyState('bx-error', 'Gagal memuat log'));
                $('#rawLogContent').text('Error loading logs');
            });

            // Load error logs
            $.get('{{ route("sap.errorLogs") }}', { date: formattedDate }, function(response) {
                const logs = response.logs || '';
                renderErrors(logs);
            }).fail(function() {
                $('#errorsContainer').html(createEmptyState('bx-error', 'Gagal memuat error log'));
            });

            // Load scheduler logs
            $.get('{{ route("sap.autoImportLogs") }}', function(response) {
                const logs = response.logs || '';
                renderSchedulerLogs(logs);
            }).fail(function() {
                $('#schedulerContainer').html(createEmptyState('bx-info-circle', 'Scheduler log tidak tersedia'));
            });
        }

        function renderTimeline(logText) {
            if (!logText || logText.includes('Tidak ada')) {
                $('#timelineContainer').html(createEmptyState('bx-file', 'Tidak ada aktivitas import untuk tanggal ini'));
                return;
            }

            const lines = logText.trim().split('\n').filter(line => line.trim());
            let html = '';
            let stats = { total: 0, success: 0, failed: 0, duplicate: 0 };

            lines.forEach(line => {
                const parsed = parseLogLine(line);
                if (parsed) {
                    // Hanya hitung untuk status utama (bukan STARTED, MOVED_TO_ERROR, dll)
                    if (['SUCCESS', 'FAILED', 'REJECTED', 'DUPLICATE', 'SKIPPED'].includes(parsed.status)) {
                        stats.total++;
                        if (parsed.status === 'SUCCESS') stats.success++;
                        else if (parsed.status === 'FAILED' || parsed.status === 'REJECTED') stats.failed++;
                        else if (parsed.status === 'DUPLICATE' || parsed.status === 'SKIPPED') stats.duplicate++;
                    }

                    html += createTimelineItem(parsed);
                }
            });

            // Update stats
            $('#statTotal').text(stats.total);
            $('#statSuccess').text(stats.success);
            $('#statFailed').text(stats.failed);
            $('#statDuplicate').text(stats.duplicate);

            if (html) {
                $('#timelineContainer').html(html);
            } else {
                $('#timelineContainer').html(createEmptyState('bx-file', 'Tidak ada aktivitas import'));
            }
        }

        function parseLogLine(line) {
            // Format: [2026-01-22 07:49:04] [SUCCESS] [SAPIO03122025.csv] Import berhasil: 4 record
            const regex = /\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s*\[([^\]]+)\]\s*\[([^\]]+)\]\s*(.+)/;
            const match = line.match(regex);
            
            if (match) {
                return {
                    time: match[1],
                    status: match[2].toUpperCase(),
                    file: match[3],
                    message: match[4]
                };
            }
            return null;
        }

        function createTimelineItem(data) {
            const iconClass = getStatusIcon(data.status);
            const statusClass = getStatusClass(data.status);
            const badgeClass = getBadgeClass(data.status);
            const timeOnly = data.time.split(' ')[1];
            
            return `
                <div class="timeline-item" data-searchable="${data.file.toLowerCase()} ${data.message.toLowerCase()}">
                    <div class="timeline-icon ${statusClass}">
                        <i class="bx ${iconClass}"></i>
                    </div>
                    <div class="timeline-content">
                        <span class="timeline-file">${escapeHtml(data.file)}</span>
                        <span class="badge ${badgeClass} timeline-badge ms-2">${data.status}</span>
                        <div class="timeline-message">${escapeHtml(data.message)}</div>
                    </div>
                    <div class="timeline-time">
                        <i class="bx bx-time-five me-1"></i>${timeOnly}
                    </div>
                </div>
            `;
        }

        function renderErrors(logText) {
            if (!logText || logText.includes('Tidak ada')) {
                $('#errorsContainer').html(createEmptyState('bx-check-circle', 'Tidak ada error untuk tanggal ini', 'text-success'));
                return;
            }

            const lines = logText.trim().split('\n').filter(line => line.trim());
            let html = '';

            lines.forEach(line => {
                const parsed = parseErrorLine(line);
                if (parsed) {
                    html += createErrorCard(parsed);
                }
            });

            if (html) {
                $('#errorsContainer').html(html);
            } else {
                $('#errorsContainer').html(createEmptyState('bx-check-circle', 'Tidak ada error', 'text-success'));
            }
        }

        function parseErrorLine(line) {
            // Format: [2026-01-22 07:49:04] [SAPIO.csv] [Baris 2] DATA_INCOMPLETE: Field kosong
            const regex = /\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s*\[([^\]]+)\](?:\s*\[Baris (\d+)\])?\s*([^:]+):\s*(.+)/;
            const match = line.match(regex);
            
            if (match) {
                return {
                    time: match[1],
                    file: match[2],
                    row: match[3] || null,
                    type: match[4].trim(),
                    message: match[5]
                };
            }
            return null;
        }

        function createErrorCard(data) {
            const rowInfo = data.row ? `<span class="error-row"><i class="bx bx-grid-alt me-1"></i>Baris ${data.row}</span>` : '';
            
            return `
                <div class="error-card" data-searchable="${data.file.toLowerCase()} ${data.message.toLowerCase()}">
                    <div class="error-card-header">
                        <div>
                            <span class="error-file"><i class="bx bx-file me-1"></i>${escapeHtml(data.file)}</span>
                            ${rowInfo}
                        </div>
                        <div class="text-end">
                            <span class="error-type">${escapeHtml(data.type)}</span>
                            <div class="error-time mt-1">${data.time}</div>
                        </div>
                    </div>
                    <div class="error-message">${escapeHtml(data.message)}</div>
                </div>
            `;
        }

        function renderSchedulerLogs(logText) {
            if (!logText || logText.includes('belum tersedia') || logText.includes('log kosong')) {
                $('#schedulerContainer').html(createEmptyState('bx-timer', 'Scheduler belum pernah dijalankan'));
                return;
            }

            // Parse scheduler sessions
            const sessions = parseSchedulerSessions(logText);
            
            if (sessions.length === 0) {
                $('#schedulerContainer').html(`<pre class="raw-log-content m-0" style="max-height: 300px;">${escapeHtml(logText)}</pre>`);
                return;
            }

            let html = '';
            sessions.slice(0, 10).forEach(session => { // Show last 10 sessions
                html += createSchedulerSession(session);
            });

            $('#schedulerContainer').html(html);
        }

        function parseSchedulerSessions(logText) {
            const sessions = [];
            const lines = logText.split('\n');
            let currentSession = null;

            lines.forEach(line => {
                // Detect session start
                if (line.includes('SAP Auto Import -') && line.includes('========')) {
                    if (currentSession) sessions.push(currentSession);
                    const dateMatch = line.match(/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/);
                    currentSession = {
                        date: dateMatch ? dateMatch[1] : 'Unknown',
                        files: [],
                        imported: 0,
                        skipped: 0,
                        failed: 0
                    };
                }
                // Parse file results
                else if (currentSession && line.includes('|') && !line.includes('File') && !line.includes('---')) {
                    const parts = line.split('|').map(p => p.trim()).filter(p => p);
                    if (parts.length >= 4) {
                        currentSession.files.push({
                            name: parts[0],
                            size: parts[1],
                            status: parts[2].includes('Success') ? 'success' : (parts[2].includes('Skip') ? 'skipped' : 'failed'),
                            message: parts[3]
                        });
                    }
                }
                // Parse summary
                else if (currentSession) {
                    if (line.includes('Imported:')) {
                        const match = line.match(/Imported:\s*(\d+)/);
                        if (match) currentSession.imported = parseInt(match[1]);
                    }
                    if (line.includes('Skipped:')) {
                        const match = line.match(/Skipped:\s*(\d+)/);
                        if (match) currentSession.skipped = parseInt(match[1]);
                    }
                    if (line.includes('Failed:')) {
                        const match = line.match(/Failed:\s*(\d+)/);
                        if (match) currentSession.failed = parseInt(match[1]);
                    }
                }
            });

            if (currentSession) sessions.push(currentSession);
            return sessions.reverse(); // Most recent first
        }

        function createSchedulerSession(session) {
            let filesHtml = '';
            session.files.forEach(file => {
                const icon = file.status === 'success' ? 'bx-check text-success' : 
                            (file.status === 'skipped' ? 'bx-skip-next text-muted' : 'bx-x text-danger');
                filesHtml += `
                    <div class="scheduler-file-item">
                        <span class="scheduler-file-icon"><i class="bx ${icon}"></i></span>
                        <span class="flex-grow-1">${escapeHtml(file.name)}</span>
                        <small class="text-muted">${escapeHtml(file.message)}</small>
                    </div>
                `;
            });

            return `
                <div class="scheduler-session">
                    <div class="scheduler-header">
                        <span><i class="bx bx-time-five me-1"></i>${session.date}</span>
                        <div>
                            <span class="scheduler-stat success"><i class="bx bx-check"></i>${session.imported}</span>
                            <span class="scheduler-stat skipped"><i class="bx bx-skip-next"></i>${session.skipped}</span>
                            <span class="scheduler-stat failed"><i class="bx bx-x"></i>${session.failed}</span>
                        </div>
                    </div>
                    <div class="scheduler-body">
                        ${filesHtml || '<span class="text-muted">No files processed</span>'}
                    </div>
                </div>
            `;
        }

        function filterLogs(query) {
            query = query.toLowerCase().trim();
            
            if (!query) {
                $('.timeline-item, .error-card').show();
                return;
            }

            $('.timeline-item, .error-card').each(function() {
                const searchable = $(this).data('searchable') || '';
                if (searchable.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function createEmptyState(icon, message, colorClass = '') {
            return `
                <div class="empty-state">
                    <i class="bx ${icon} ${colorClass}"></i>
                    <p class="mb-0">${message}</p>
                </div>
            `;
        }

        function getStatusIcon(status) {
            const icons = {
                'SUCCESS': 'bx-check',
                'STARTED': 'bx-play',
                'FAILED': 'bx-x',
                'REJECTED': 'bx-block',
                'DUPLICATE': 'bx-copy',
                'SKIPPED': 'bx-skip-next',
                'FORCE_DELETE': 'bx-trash',
                'MOVED_TO_ERROR': 'bx-folder-open',
                'MOVED_TO_PROCESSED': 'bx-folder-open'
            };
            return icons[status] || 'bx-info-circle';
        }

        function getStatusClass(status) {
            const classes = {
                'SUCCESS': 'success',
                'STARTED': 'started',
                'FAILED': 'failed',
                'REJECTED': 'rejected',
                'DUPLICATE': 'duplicate',
                'SKIPPED': 'duplicate',
                'FORCE_DELETE': 'failed',
                'MOVED_TO_ERROR': 'failed',
                'MOVED_TO_PROCESSED': 'success'
            };
            return classes[status] || 'started';
        }

        function getBadgeClass(status) {
            const classes = {
                'SUCCESS': 'bg-success',
                'STARTED': 'bg-primary',
                'FAILED': 'bg-danger',
                'REJECTED': 'bg-danger',
                'DUPLICATE': 'bg-warning text-dark',
                'SKIPPED': 'bg-warning text-dark',
                'FORCE_DELETE': 'bg-secondary',
                'MOVED_TO_ERROR': 'bg-secondary',
                'MOVED_TO_PROCESSED': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

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