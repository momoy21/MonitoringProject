<x-layout title="Pengajuan Penugasan">

    <style>
        /* Fix Select2 border in modal */
        #penugasanModal .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #dee2e6 !important;
        }
        #penugasanModal .select2-container--bootstrap-5 .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
        }
    </style>

    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Pengajuan Penugasan</h4>
                <p class="mb-0">Kelola pengajuan tim penugasan proyek</p>
            </div>
        </div>

        <!-- ID Penugasan Dropdown + Generate -->
        <div class="row mt-3 align-items-end">
            <div class="col-md-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <label class="form-label mb-0 fw-bold" style="white-space: nowrap; min-width: 120px;">ID Penugasan:</label>
                    <select id="header_select" class="form-select" style="width: 100%; max-width: 400px;">
                        <option value="">-- Pilih ID Penugasan --</option>
                    </select>
                    <button type="button" class="btn btn-primary" id="btnGenerateId" title="Buat Header Baru">
                        <i class="bx bx-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Header Section (hidden by default) -->
        <div class="row mt-2 align-items-end" id="createHeaderSection" style="display: none;">
            <div class="col-md-12">
                <div class="card border-primary mb-0">
                    <div class="card-body py-3">
                        <h6 class="card-title fw-bold mb-3"><i class="bx bx-plus-circle me-1"></i> Buat Header Penugasan Baru</h6>
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1 small fw-semibold">ID Penugasan</label>
                                <input type="text" class="form-control form-control-sm" id="new_idpenugasan" readonly
                                       style="background-color: #e9ecef; font-weight: 600;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small fw-semibold">No Surat</label>
                                <input type="text" class="form-control form-control-sm" id="new_nosurat" readonly
                                       style="background-color: #e9ecef; font-weight: 600;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-1 small fw-semibold">Cost Centre <span class="text-danger">*</span></label>
                                <select id="new_cost_center" class="form-select" style="width: 100%;">
                                    <option value="">-- Pilih Cost Centre --</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <button type="button" class="btn btn-success btn-sm flex-fill" id="btnSimpanHeader">
                                    <i class="bx bx-save me-1"></i> Simpan Header
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCancelHeader" title="Batal">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-2" id="newNamaProyekRow" style="display: none;">
                            <div class="col-md-12">
                                <label class="form-label mb-1 small fw-semibold">Nama Proyek</label>
                                <input type="text" class="form-control form-control-sm" id="new_namaproject" readonly
                                       style="background-color: #e9ecef; font-weight: 600;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Info (shown when header selected) -->
        <div class="row mt-2" id="headerInfoSection" style="display: none;">
            <div class="col-md-3">
                <label class="form-label mb-1 small fw-semibold">No Surat</label>
                <input type="text" class="form-control form-control-sm" id="info_nosurat" readonly
                       style="background-color: #e9ecef; font-weight: 600;">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small fw-semibold">Cost Centre</label>
                <input type="text" class="form-control form-control-sm" id="info_costcenter" readonly
                       style="background-color: #e9ecef; font-weight: 600;">
            </div>
            <div class="col-md-6">
                <label class="form-label mb-1 small fw-semibold">Nama Proyek</label>
                <input type="text" class="form-control form-control-sm" id="info_namaproject" readonly
                       style="background-color: #e9ecef; font-weight: 600;">
            </div>
        </div>
    </div>

    <!-- Search Bar + Action Buttons + Tampilkan -->
    <div class="mt-3 justify-content-between align-items-center flex-wrap gap-2 d-none" id="searchBarSection">
        <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Ketik NIK atau Nama untuk mencari..." autocomplete="off">
            <button type="button" class="btn btn-outline-secondary" id="btnClearSearch" style="display: none;" title="Hapus pencarian">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Action Buttons -->
            <button type="button" class="btn btn-outline-success" id="btnDownloadTemplate" title="Download Template Excel">
                <i class="bx bx-download me-1"></i> Template
            </button>
            <button type="button" class="btn btn-outline-info" id="btnUpload" title="Upload Excel">
                <i class="bx bx-upload me-1"></i> Upload
            </button>
            <button type="button" class="btn btn-primary" id="btnTambah">
                <i class="bx bx-plus me-1"></i> Tambah
            </button>
            <span class="text-muted" style="margin: 0 4px;">|</span>
            <!-- Tampilkan per halaman -->
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

    <!-- Table Section (hidden until header selected) -->
    <div class="card mt-3 d-none" id="tableSection">
        <div class="table-responsive" style="overflow: visible;">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small" style="width: 50px;">No</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">NIK</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">Nama</th>
                        <th colspan="2" class="text-center fw-bold text-muted small">Periode Penugasan</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">Jabatan</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">Bobot</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small">Status</th>
                        <th rowspan="2" class="text-center align-middle fw-bold text-muted small" style="width: 100px;">Aksi</th>
                    </tr>
                    <tr>
                        <th class="text-center fw-bold text-muted small">Awal</th>
                        <th class="text-center fw-bold text-muted small">Akhir</th>
                    </tr>
                </thead>
                <tbody id="penugasanTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>
                                <p class="mb-0 text-muted">Pilih ID Penugasan untuk melihat data</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
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

    <!-- Add/Edit/View Modal -->
    <div class="modal fade" id="penugasanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Penugasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="penugasanForm">
                        <input type="hidden" id="form_id" value="">
                        <input type="hidden" id="form_mode" value="add">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="form_cost_center" class="form-label">Cost Centre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="form_cost_center" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="form_dok_io" class="form-label">Dokumen IO</label>
                                <input type="text" class="form-control" id="form_dok_io" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="form_status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="form_status">
                                    <option value="A">Aktif</option>
                                    <option value="N">Non-Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="form_namaproject" class="form-label">Nama Proyek</label>
                                <input type="text" class="form-control" id="form_namaproject" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="form_nik" class="form-label">NIK <span class="text-danger">*</span></label>
                                <select id="form_nik" class="form-select" style="width: 100%;">
                                    <option value="">-- Pilih NIK --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="form_nama_karyawan" class="form-label">Nama Karyawan</label>
                                <input type="text" class="form-control" id="form_nama_karyawan" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="form_jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="form_jabatan" maxlength="30" placeholder="Contoh: Project Manager">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="form_periode_awal" class="form-label">Periode Awal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="form_periode_awal">
                            </div>
                            <div class="col-md-4">
                                <label for="form_periode_akhir" class="form-label">Periode Akhir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="form_periode_akhir">
                            </div>
                            <div class="col-md-4">
                                <label for="form_bobot" class="form-label">Bobot (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="form_bobot" min="0.01" max="100" step="0.01" placeholder="Contoh: 33.50">
                                <small class="text-muted">Minimal 0.01, gunakan titik untuk desimal</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" id="modalFooter">
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
                    <h5 class="modal-title">Upload Data Penugasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="uploadFile" class="form-label">Pilih File Excel <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="uploadFile" name="file" accept=".xlsx,.xls,.csv">
                            <small class="text-muted">Format: xlsx, xls, csv. Maksimal 5MB.</small>
                        </div>
                        <div class="alert alert-info small mb-0">
                            <i class="bx bx-info-circle me-1"></i>
                            Pastikan file sesuai dengan template yang disediakan.
                            <a href="javascript:void(0)" id="btnDownloadTemplateModal">Download template disini</a>.
                            <hr class="my-2">
                            <ul class="mb-0 ps-3" style="font-size: 12px;">
                                <li><strong>Cost Center</strong> otomatis diambil dari ID Penugasan yang dipilih. Tidak perlu mengisi kolom Cost Center di file Excel.</li>
                                <li><strong>Status</strong> hanya boleh <strong>A</strong> (Aktif) atau <strong>N</strong> (Non-Aktif). Huruf/angka lain akan ditolak.</li>
                                <li><strong>Bobot</strong> harus berupa angka desimal <strong>0.01 - 100</strong>. Gunakan titik sebagai pemisah desimal. Bobot tidak boleh 0.</li>
                                <li><strong>Format Tanggal</strong>: dd/mm/yyyy (contoh: 01/01/2026)</li>
                                <li><strong>NIK</strong> harus terdaftar di sistem.</li>
                                <li><strong>Duplikasi</strong>: Data dianggap duplikat jika NIK, Periode Awal, Periode Akhir, dan Jabatan sama.</li>
                                <li><strong>Periode</strong>: Jabatan sama + NIK sama &mdash; periode tidak boleh bersinggungan.</li>
                            </ul>
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
                    <p>Apakah Anda yakin ingin menghapus data penugasan ini?</p>
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

    /* Table Header */
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

    /* Hide number spinners */
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
        getInitialData: "{{ route('penugasan.getInitialData') }}",
        getHeaders: "{{ route('penugasan.getHeaders') }}",
        storeHeader: "{{ route('penugasan.storeHeader') }}",
        getCostCenter: "{{ route('penugasan.getCostCenter') }}",
        getData: "{{ route('penugasan.getData') }}",
        getKaryawan: "{{ route('penugasan.getKaryawan') }}",
        generateId: "{{ route('penugasan.generateId') }}",
        store: "{{ route('penugasan.store') }}",
        update: "{{ route('penugasan.update') }}",
        destroy: "{{ route('penugasan.destroy') }}",
        upload: "{{ route('penugasan.upload') }}",
        downloadTemplate: "{{ route('penugasan.downloadTemplate') }}",
    };
    window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/penugasan.js') }}?v={{ time() }}"></script>
@endpush
</x-layout>