<x-layout title="Upload RAB">
    <!-- Header Section - Sticky -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Upload RAB</h4>
                <p class="mb-0">Upload dokumen RAB dan kelola data header RAB</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="upload-rab-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="uploadRABForm" class="upload-rab-form">
                        <!-- Baris 1: Cost Center, Konsumen, No Kontrak -->
                        <div class="row">
                            <!-- Cost Center - Nama Proyek -->
                            <div class="col-md-4 mb-3">
                                <label for="cost_center_proyek" class="form-label">Cost Center - Nama Proyek <span class="text-danger">*</span></label>
                                <select class="form-select" id="cost_center_proyek" name="cost_center_proyek" required>
                                    <option value="">Pilih Cost Center - Nama Proyek</option>
                                </select>
                                <div class="invalid-feedback">
                                    Silakan pilih Cost Center - Nama Proyek
                                </div>
                            </div>

                            <!-- Konsumen -->
                            <div class="col-md-4 mb-3">
                                <label for="konsumen" class="form-label">Konsumen</label>
                                <input type="text" class="form-control" id="konsumen" name="konsumen" readonly>
                            </div>

                            <!-- No Kontrak -->
                            <div class="col-md-4 mb-3">
                                <label for="no_kontrak" class="form-label">No Kontrak</label>
                                <input type="text" class="form-control" id="no_kontrak" name="no_kontrak" readonly>
                            </div>
                        </div>

                        <!-- Baris 2: Nilai Proyek, Tanggal Kontrak, Akhir Kontrak -->
                        <div class="row">
                            <!-- Nilai Proyek -->
                            <div class="col-md-4 mb-3">
                                <label for="nilai_proyek" class="form-label">Nilai Proyek</label>
                                <input type="text" class="form-control" id="nilai_proyek" name="nilai_proyek" readonly>
                            </div>

                            <!-- Tanggal Kontrak -->
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_kontrak" class="form-label">Tanggal Kontrak</label>
                                <input type="text" class="form-control" id="tanggal_kontrak" name="tanggal_kontrak" readonly>
                            </div>

                            <!-- Akhir Kontrak -->
                            <div class="col-md-4 mb-3">
                                <label for="akhir_kontrak" class="form-label">Akhir Kontrak</label>
                                <input type="text" class="form-control" id="akhir_kontrak" name="akhir_kontrak" readonly>
                            </div>
                        </div>

                        <!-- Baris 3: Mulai, Lama, Upload Dokumen -->
                        <div class="row">
                            <!-- Mulai -->
                            <div class="col-md-4 mb-3">
                                <label for="mulai" class="form-label">Mulai</label>
                                <input type="text" class="form-control" id="mulai" name="mulai" readonly>
                            </div>

                            <!-- Lama -->
                            <div class="col-md-4 mb-3">
                                <label for="lama" class="form-label">Lama</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="lama" name="lama" readonly>
                                    <span class="input-group-text">Bulan</span>
                                </div>
                            </div>

                            <!-- Upload Dokumen RAB -->
                            <div class="col-md-4 mb-3">
                                <label for="document_rab" class="form-label">Upload Dokumen RAB</label>
                                <input type="file" class="form-control" id="document_rab" name="document_rab" accept=".pdf,.doc,.docx,.xls,.xlsx">
                                <div class="file-upload-info">Format file yang didukung: XLS, XLSX (Max: 25MB)</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row align-items-center">
                            <!-- Kiri -->
                            <div class="col d-flex justify-content-start">
                                <button type="button" id="btnEditHeaderRAB" class="btn btn-primary ms-2" style="display: none;">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </button>
                            </div>

                            <!-- Kanan -->
                            <div class="col d-flex justify-content-end">
                                <button type="button" id="btnResetForm" class="btn btn-outline-secondary me-2">
                                    <i class="bx bx-refresh me-1"></i> Reset
                                </button>
                                <button type="button" id="btnUpload" class="btn btn-primary btn-upload" disabled>
                                    <i class="bx bx-upload me-1"></i> Upload RAB
                                </button>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>

        <!-- Detail RAB Section -->
        <div class="row mt-4" id="detailRABSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-table me-2"></i>Rencana Anggaran Belanja Proyek
                        </h5>
                        <div class="card-actions">
                            <small class="text-muted" id="detailRABInfo">ID RAB: <span id="currentIdRAB">-</span></small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="detailRABContainer">
                            <div class="text-center py-4">
                                <i class="bx bx-info-circle mb-2" style="font-size: 48px; color: #6c757d;"></i>
                                <p class="mb-0 text-muted">Belum ada data Detail RAB. Upload file Excel untuk menampilkan data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Header RAB -->
    <div class="modal fade header-rab-modal" id="headerRABModal" tabindex="-1" aria-labelledby="headerRABModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="headerRABModalLabel">Input Header RAB</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="headerRABForm">
                        <input type="hidden" id="modal_project_id" name="project_id">

                        <!-- Informasi Proyek -->
                        <div class="form-section">
                            <h6 class="mb-3"><i class="bx bx-folder me-2"></i>Informasi Proyek</h6>
                            <div class="row">
                                <!-- Cost Centre - Nama Proyek -->
                                <div class="col-md-12 mb-3">
                                    <label for="modal_cost_center_proyek" class="form-label">Cost Center - Nama Proyek</label>
                                    <input type="text" class="form-control readonly-field" id="modal_cost_center_proyek" name="cost_center_proyek" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Nilai Proyek -->
                                <div class="col-md-12 mb-3">
                                    <label for="modal_nilai_proyek" class="form-label">Nilai Proyek</label>
                                    <input type="text" class="form-control readonly-field" id="modal_nilai_proyek" name="nilai_proyek" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Input Data RAB -->
                        <div class="form-section">
                            <h6 class="mb-3"><i class="bx bx-edit me-2"></i>Input Data RAB</h6>
                            <div class="row">
                                <!-- ID RAB -->
                                <div class="col-md-12 mb-3">
                                    <label for="modal_id_rab" class="form-label">ID RAB</label>
                                    <input type="text" class="form-control readonly-field" id="modal_id_rab" name="id_rab" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Periode RAB (Mulai) -->
                                <div class="col-md-12 mb-3">
                                    <label for="modal_periode_rab" class="form-label">Mulai <span class="text-danger">*</span></label>
                                    <div class="input-group date-input-group">
                                        <input type="text" class="form-control" id="modal_periode_rab" name="periode_rab"
                                               placeholder="dd/mm/yyyy" maxlength="10" required>
                                        <input type="date" class="date-picker-hidden" id="modal_periode_rab_date">
                                        <button type="button" class="btn btn-outline-secondary date-picker-btn" tabindex="-1" title="Pilih tanggal">
                                            <i class="bx bx-calendar"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Silakan masukkan tanggal mulai dengan format dd/mm/yyyy
                                    </div>
                                </div>

                                <!-- Lama -->
                                <div class="col-md-12 mb-3">
                                    <label for="modal_lama" class="form-label">Lama <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="modal_lama" name="lama"
                                               min="1" max="999" required>
                                        <span class="input-group-text">Bulan</span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Silakan masukkan lama periode dalam bulan (1-999)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Batal
                    </button>
                    <button type="button" id="btnSaveHeaderRAB" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Header RAB -->
    <div class="modal fade" id="editHeaderRABModal" tabindex="-1" aria-labelledby="editHeaderRABModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editHeaderRABModalLabel">Edit Mulai & Lama Header RAB</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editHeaderRABForm">
                        <input type="hidden" id="edit_project_id">

                        <div class="mb-3">
                            <label for="edit_mulai" class="form-label">Mulai <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_mulai" placeholder="dd/mm/yyyy" required>
                            <small class="text-muted">Format: dd/mm/yyyy (contoh: 01/01/2025)</small>
                        </div>

                        <div class="mb-3">
                            <label for="edit_lama" class="form-label">Lama <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_lama" min="1" max="999" required>
                                <span class="input-group-text">Bulan</span>
                            </div>
                            <small class="text-muted">Masukkan jumlah bulan (1-999)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Batal
                    </button>
                    <button type="button" id="btnSaveEditHeaderRAB" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Detail RAB Section - Add this after Detail RAB Section -->
    <div class="row mt-4" id="summaryDetailRABSection" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-calculator me-2"></i>Summary Detail RAB
                    </h5>
                    <div class="card-actions">
                        <small class="text-muted" id="summaryDetailRABInfo">ID RAB: <span id="summaryIdRAB">-</span></small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="summaryDetailRABContainer">
                        <div class="text-center py-4">
                            <i class="bx bx-info-circle mb-2" style="font-size: 48px; color: #6c757d;"></i>
                            <p class="mb-0 text-muted">Belum ada data Summary Detail RAB. Upload file Excel untuk menampilkan data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* Date picker styles */
        .date-picker-hidden {
            display: none !important;
        }

        .date-input-group .date-picker-btn {
            border-left: 0;
        }

        .date-input-group .form-control {
            border-right: 0;
        }

        .date-input-group .form-control:focus {
            border-color: #00a0d4;
            box-shadow: 0 0 0 0.2rem rgba(0, 160, 212, 0.25);
        }

        .date-input-group .date-picker-btn:focus {
            border-color: #00a0d4;
            box-shadow: 0 0 0 0.2rem rgba(0, 160, 212, 0.25);
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- File Preview Handler -->
    <script src="{{ asset('js/file-preview.js') }}"></script>

    <script>
        // Setup global variables for the uploadrab.js module
        window.routes = {
            getCostCenterProyek: '{{ route("rab.getCostCenterProyek") }}',
            generateIdRAB: '{{ route("rab.generateIdRAB") }}',
            checkHeaderRAB: '{{ route("rab.checkHeaderRAB") }}',
            storeHeaderRAB: '{{ route("rab.storeHeaderRAB") }}',
            updateHeaderRAB: '{{ route("rab.updateHeaderRAB") }}',
            uploadExcel: '{{ route("rab.uploadExcel") }}',
            getDetailRAB: '{{ route("rab.getDetailRAB") }}',
            getSummaryDetailRAB: '{{ route("rab.getSummaryDetailRAB") }}'
        };
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('js/uploadrab.js') }}"></script>
    @endpush
</x-layout>
