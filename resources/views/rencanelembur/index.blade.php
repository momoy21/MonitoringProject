<x-layout title="Input Rencana Lembur">
    <!-- Header Section - Sticky (same as datapeluang) -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Input Rencana Lembur</h4>
                <p class="mb-0">Kelola rencana kuota lembur karyawan berdasarkan RAB Proyek</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <!-- Download Template -->
                    <button type="button" class="btn btn-outline-success" id="btnDownloadTemplate" title="Download Template Excel">
                        <i class="bx bx-download me-1"></i> Template
                    </button>
                    <!-- Upload -->
                    <button type="button" class="btn btn-outline-info" id="btnUpload" title="Upload Excel">
                        <i class="bx bx-upload me-1"></i> Upload
                    </button>
                    <!-- Tambah -->
                    <button type="button" class="btn btn-primary" id="btnTambah">
                        <i class="bx bx-plus me-1"></i> Tambah
                    </button>
                </div>
            </div>
        </div>

        <!-- Cost Center Selection & Per Page -->
        <div class="row mt-3 align-items-end">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-2">
                    <label for="cost_center_select" class="form-label mb-0 fw-bold" style="white-space: nowrap;">Cost Centre:</label>
                    <select id="cost_center_select" class="form-select" style="width: 100%; max-width: 500px;">
                        <option value="">-- Pilih Cost Centre --</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 justify-content-end">
                    <label for="perPageSelect" class="form-label mb-0" style="white-space: nowrap;">Tampilkan:</label>
                    <select id="perPageSelect" class="form-select per-page-selector" style="width: auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-muted small" style="white-space: nowrap;">per halaman</span>
                </div>
            </div>
        </div>
        <!-- Nama Proyek (full width below Cost Centre) -->
        <div class="row mt-2 align-items-center">
            <div class="col-md-12">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-bold" style="white-space: nowrap;">Nama Proyek:</label>
                    <input type="text" class="form-control form-control-sm" id="info_namaproject" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar (hidden until Cost Centre is selected) -->
    <div class="mt-3" id="searchBarSection" style="display: none;">
        <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Ketik NIK atau Nama untuk mencari..." autocomplete="off">
            <button type="button" class="btn btn-outline-secondary" id="btnClearSearch" style="display: none;" title="Hapus pencarian">
                <i class="bx bx-x"></i>
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card mt-3">
        <div class="table-responsive" style="overflow: visible;">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small" style="width: 50px;">No</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">NIK</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">Nama</th>
                        <th colspan="2" class="text-center fw-bold text-muted small">Periode</th>
                        <th colspan="2" class="text-center fw-bold text-muted small">Jumlah Lembur</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">Libur Nasional/Kalender</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small" style="width: 100px;">Aksi</th>
                    </tr>
                    <tr>
                        <th class="text-center fw-bold text-muted small">Awal</th>
                        <th class="text-center fw-bold text-muted small">Akhir</th>
                        <th class="text-center fw-bold text-muted small">WeekDay</th>
                        <th class="text-center fw-bold text-muted small">WeekEnd</th>
                    </tr>
                </thead>
                <tbody id="kuotaLemburTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>
                                <p class="mb-0 text-muted">Pilih Cost Centre untuk melihat data</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls (same as datapeluang) -->
    <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2" id="paginationControls" style="display: none !important;">
        <div class="pagination-info">
            <span class="text-muted medium">
                Menampilkan <span id="entriesFrom">0</span> hingga <span id="entriesTo">0</span> dari <span id="entriesTotal">0</span> data
            </span>
        </div>
        <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center justify-content-md-end">
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="firstPageBtn" title="Halaman Pertama">
                <i class="bx bx-chevrons-left"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="prevPageBtn" title="Halaman Sebelumnya">
                <i class="bx bx-chevron-left"></i>
            </button>
            <div class="d-flex align-items-center gap-1 mx-1 mx-md-2" id="pageNumbersContainer"></div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="nextPageBtn" title="Halaman Selanjutnya">
                <i class="bx bx-chevron-right"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="lastPageBtn" title="Halaman Terakhir">
                <i class="bx bx-chevrons-right"></i>
            </button>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="kuotaLemburModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Kuota Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="kuotaLemburForm">
                        <input type="hidden" id="form_id" value="">
                        <input type="hidden" id="form_mode" value="add">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="form_cost_center" class="form-label">Cost Centre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="form_cost_center" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="form_dok_io" class="form-label">Dokumen IO</label>
                                <input type="text" class="form-control" id="form_dok_io" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="form_namaproject" class="form-label">Nama Proyek</label>
                                <input type="text" class="form-control" id="form_namaproject" readonly>
                            </div>
                            <div class="col-md-5">
                                <label for="form_nik" class="form-label">NIK <span class="text-danger">*</span></label>
                                <select id="form_nik" class="form-select" style="width: 100%;">
                                    <option value="">-- Pilih NIK --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="form_bulan" class="form-label">Bulan Ke</label>
                                <input type="number" class="form-control" id="form_bulan" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="form_periode_awal" class="form-label">Periode Awal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="form_periode_awal">
                            </div>
                            <div class="col-md-6">
                                <label for="form_periode_akhir" class="form-label">Periode Akhir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="form_periode_akhir">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="form_jml_wd" class="form-label">Jumlah Week Day</label>
                                <input type="number" class="form-control" id="form_jml_wd" min="0" step="0.01" value="0">
                                <small class="text-muted">Boleh diisi desimal yang dipisah dengan titik.</small>
                            </div>
                            <div class="col-md-4">
                                <label for="form_jml_we" class="form-label">Jumlah Week End</label>
                                <input type="number" class="form-control" id="form_jml_we" min="0" step="0.01" value="0">
                                <small class="text-muted">Boleh diisi desimal yang dipisah dengan titik.</small>
                            </div>
                            <div class="col-md-4">
                                <label for="form_jml_hn" class="form-label">Jumlah Hari Libur Nasional</label>
                                <input type="number" class="form-control" id="form_jml_hn" min="0" step="0.01" value="0">
                                <small class="text-muted">Boleh diisi desimal yang dipisah dengan titik.</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSimpan">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="simpanSpinner"></span>
                        <i class="bx bx-check me-1" id="simpanIcon"></i>
                        <span id="simpanText">Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Data Kuota Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="uploadFile" class="form-label">Pilih File Excel <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="uploadFile" name="file" accept=".xlsx,.xls,.csv">
                            <small class="text-muted">Format: xlsx, xls, csv. Maksimal 5MB.</small>
                        </div>
                        <div class="alert alert-info small">
                            <i class="bx bx-info-circle me-1"></i>
                            Pastikan file sesuai dengan template yang disediakan. 
                            <a href="javascript:void(0)" id="btnDownloadTemplateModal">Download template disini</a>.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnDoUpload">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="uploadSpinner"></span>
                        <i class="bx bx-upload me-1"></i> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body border-top border-bottom">
                    <p>Apakah Anda yakin ingin menghapus data kuota lembur ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .swal-on-top { z-index: 99999 !important; }
    .modal { z-index: 1055; }
    .modal-backdrop { z-index: 1050; }

    /* Penyesuaian Table Header Bertingkat */
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6 !important;
        vertical-align: middle;
        padding: 8px 4px;
    }
    .table td {
        vertical-align: middle;
        padding: 8px 10px;
        font-size: 13px;
    }

    /* Search highlight */
    .search-highlight { background-color: #fff3cd; font-weight: bold; border-radius: 2px; padding: 0 1px; }
    #searchInput:focus { border-color: #86b7fe; box-shadow: 0 0 0 .2rem rgba(13,110,253,.15); }

    /* Hover effect */
    .table-hover tbody tr:hover {
        background-color: #f0f4ff !important;
        cursor: pointer;
    }

    /* Hilangkan Panah Input Number */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.routes = {
        getInitialData: "{{ route('rencanelembur.getInitialData') }}",
        getCostCenter: "{{ route('rencanelembur.getCostCenter') }}",
        getData: "{{ route('rencanelembur.getData') }}",
        getNextBulan: "{{ route('rencanelembur.getNextBulan') }}",
        store: "{{ route('rencanelembur.store') }}",
        update: "{{ route('rencanelembur.update') }}",
        destroy: "{{ route('rencanelembur.destroy') }}",
        upload: "{{ route('rencanelembur.upload') }}",
        downloadTemplate: "{{ route('rencanelembur.downloadTemplate') }}",
        getKaryawan: "{{ route('rencanelembur.getKaryawan') }}",
    };
    window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/rencanelembur.js') }}?v={{ time() }}"></script>
@endpush
</x-layout>
