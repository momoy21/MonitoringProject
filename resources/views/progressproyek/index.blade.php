<x-layout title="Progress Project">
    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Progress Project</h4>
                <p class="mb-0">Kelola Berita Acara dan Issue</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="progressproyek-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="progressProyekForm" class="progressproyek-form">
                            @csrf

                            <!-- Baris 1: Cost Center, Konsumen, No Kontrak -->
                            <div class="row">
                                <!-- Cost Center - Nama Proyek -->
                                <div class="col-md-4 mb-3">
                                    <label for="cost_center_proyek" class="form-label">Cost Center - Nama Proyek <span class="text-danger">*</span></label>
                                    <select class="form-select" id="cost_center_proyek" name="id_rab" required>
                                        <option value="">Pilih Cost Center - Nama Proyek</option>
                                    </select>
                                    <div class="text-left mb-2">
                                        <small class="text-muted">Hanya menampilkan data yang sudah memiliki Header RAB dengan Mulai & Lama</small>
                                    </div>
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

                            <!-- Baris 3: Mulai, Lama, Akhir -->
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

                                <!-- Akhir (dihitung otomatis dari Mulai + Lama) -->
                                <div class="col-md-4 mb-3">
                                    <label for="akhir" class="form-label">Akhir</label>
                                    <input type="text" class="form-control" id="akhir" name="akhir" readonly>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="row mt-4" id="tabsSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="progressTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-ba" data-bs-toggle="tab" data-bs-target="#content-ba"
                                        type="button" role="tab" aria-controls="content-ba" aria-selected="false">
                                    <i class="bx bx-file me-1"></i> Berita Acara Project
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-issue" data-bs-toggle="tab" data-bs-target="#content-issue"
                                        type="button" role="tab" aria-controls="content-issue" aria-selected="false">
                                    <i class="bx bx-error me-1"></i> Issue Progress
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="progressTabContent">
                            <!-- Tab Berita Acara -->
                            <div class="tab-pane fade" id="content-ba" role="tabpanel" aria-labelledby="tab-ba">
                                <div class="d-flex justify-content-between align-items-center mb-3" id="baHeaderControls" style="display: none !important;">
                                    <h5 class="mb-0">Daftar Berita Acara</h5>
                                    @if(Auth::user()->hasRole('Super Admin'))
                                    <button type="button" class="btn btn-primary btn-sm" id="btnAddBA">
                                        <i class="bx bx-plus me-1"></i>Tambah Berita Acara
                                    </button>
                                    @endif
                                </div>
                                <div id="beritaAcaraTableContainer">
                                    <div class="text-center py-5">
                                        <i class="bx bx-file" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="text-muted mt-3">Loading...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Issue -->
                            <div class="tab-pane fade" id="content-issue" role="tabpanel" aria-labelledby="tab-issue">
                                <div class="d-flex justify-content-between align-items-center mb-3" id="issueHeaderControls" style="display: none !important;">
                                    <h5 class="mb-0">Daftar Issue Project</h5>
                                    @if(Auth::user()->hasAnyRole(['Super Admin', 'Project Manager']))
                                    <button type="button" class="btn btn-primary btn-sm" id="btnAddIssue">
                                        <i class="bx bx-plus me-1"></i>Tambah Issue
                                    </button>
                                    @endif
                                </div>
                                <div id="issueTableContainer">
                                    <div class="text-center py-5">
                                        <i class="bx bx-error-circle" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="text-muted mt-3">Loading...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Default message when no tab is selected -->
                            <div id="default-message" class="text-center py-5">
                                <i class="bx bx-mouse" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-3">Pilih tab di atas untuk melihat data</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete BA Confirmation Modal -->
    <div class="modal fade" id="deleteBAConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin akan dihapus?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBABtn">Ya</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Modal -->
    <div class="modal fade" id="issueModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="issueModalLabel">Tambah Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="issueForm">
                        <div class="mb-3">
                            <label for="issueTanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="issueTanggal" required>
                        </div>
                        <div class="mb-3">
                            <label for="issueText" class="form-label">Issue</label>
                            <textarea class="form-control" id="issueText" rows="4" style="resize: vertical;" placeholder="Masukkan deskripsi issue (kosongkan jika tidak ada)"></textarea>
                            <small class="text-muted">Jika kosong akan tersimpan sebagai "Tidak ada issue"</small>
                        </div>
                        <div class="mb-3">
                            <label for="mitigasiText" class="form-label">Mitigasi</label>
                            <textarea class="form-control" id="mitigasiText" rows="4" style="resize: vertical;" placeholder="Masukkan rencana mitigasi (kosongkan jika tidak ada)"></textarea>
                            <small class="text-muted" id="mitigasiHelpText">Jika kosong akan tersimpan sebagai "Tidak ada mitigasi"</small>
                        </div>
                        <div class="mb-3">
                            <label for="issueStatus" class="form-label">Status</label>
                            <select class="form-select" id="issueStatus">
                                <option value="O">Open</option>
                                <option value="C">Close</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>Batal</button>
                    <button type="submit" class="btn btn-primary" form="issueForm">
                        <i class="bx bx-check me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Issue Confirmation Modal -->
    <div class="modal fade" id="deleteIssueConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin akan dihapus Issue ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteIssueBtn">Ya</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Setup global variables for the progressproyek.js module
        window.routes = {
            getHeaderRAB: '{{ route("progressproyek.getheaderrab") }}',
            checkHeaderProgress: '{{ route("progressproyek.checkheaderprogress") }}',
            createHeaderProgress: '{{ route("progressproyek.store") }}',
            getBeritaAcara: '{{ route("beritaacara.getbyproject") }}',
            storeBeritaAcara: '{{ route("beritaacara.store") }}',
            updateBeritaAcara: '{{ route("beritaacara.update", ["noBA" => ":noBA"]) }}'.replace(':noBA', '{noBA}'),
            updateStatusBeritaAcara: '{{ route("beritaacara.updatestatus") }}',
            deleteBeritaAcara: '{{ route("beritaacara.destroy", ["noBA" => ":noBA"]) }}'.replace(':noBA', '{noBA}'),
            getIssue: '{{ route("issue.getbyproject") }}',
            storeIssue: '{{ route("issue.store") }}',
            updateIssue: '{{ route("issue.update", ["noIssue" => ":noIssue"]) }}'.replace(':noIssue', '{noIssue}'),
            updateStatusIssue: '{{ route("issue.updatestatus") }}',
            deleteIssue: '{{ route("issue.destroy", ["noIssue" => ":noIssue"]) }}'.replace(':noIssue', '{noIssue}')
        };
        window.csrfToken = '{{ csrf_token() }}';
        window.userRole = '{{ auth()->user()->getRoleNames()->first() ?? "Guest" }}';
    </script>
    <script src="{{ asset('js/progressproyek.js') }}"></script>
    <script src="{{ asset('js/beritaacara.js') }}"></script>
    <script src="{{ asset('js/issue.js') }}"></script>
    @endpush
</x-layout>
