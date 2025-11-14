<x-layout title="Data Pendapatan Proyek">
    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Data Pendapatan Proyek</h4>
                <p class="mb-0">Kelola data pendapatan berdasarkan Berita Acara yang disetujui</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pendapatan-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Form Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="berita_acara_select" class="form-label fw-bold">
                                    Pilih Berita Acara yang Disetujui <span class="text-danger">*</span>
                                </label>
                                <select id="berita_acara_select" class="form-select" style="width: 100%;">
                                    <option value="">-- Pilih Berita Acara --</option>
                                </select>
                                <small class="text-muted">Hanya menampilkan Berita Acara dengan status Approve</small>
                            </div>
                        </div>

                        <!-- Info Section (hidden by default) -->
                        <div id="baInfoSection" style="display: none;">
                            <!-- Baris 1: Cost Center - Nama Proyek, Konsumen, No Kontrak -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Cost Center - Nama Proyek</label>
                                    <input type="text" class="form-control" id="info_namaproject" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Konsumen</label>
                                    <input type="text" class="form-control" id="info_konsumen" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No. Kontrak</label>
                                    <input type="text" class="form-control" id="info_no_kontrak" readonly>
                                </div>
                            </div>

                            <!-- Baris 2: Nilai Proyek, Tanggal Kontrak, Akhir Kontrak -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nilai Proyek</label>
                                    <input type="text" class="form-control" id="info_nilai_proyek" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Kontrak</label>
                                    <input type="text" class="form-control" id="info_tanggal_kontrak" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Akhir Kontrak</label>
                                    <input type="text" class="form-control" id="info_akhir_kontrak" readonly>
                                </div>
                            </div>

                            <!-- Baris 3: Mulai, Lama, Akhir -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Mulai</label>
                                    <input type="text" class="form-control" id="info_mulai" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lama</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="info_lama" readonly>
                                        <span class="input-group-text">Bulan</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Akhir</label>
                                    <input type="text" class="form-control" id="info_akhir" readonly>
                                </div>
                            </div>

                            <!-- Baris 4: Periode BA dan Nilai BA -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Periode Berita Acara</label>
                                    <input type="text" class="form-control" id="info_periode_ba" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nilai Berita Acara</label>
                                    <input type="text" class="form-control currency-display-pd fw-bold" id="info_nilai_ba" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Pendapatan Section -->
        <div class="row mt-4" id="pendapatanSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Header Controls -->
                        <div id="pendapatanHeaderControls" class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="bx bx-money me-2"></i>Data Pendapatan</h5>
                            @if(Auth::user()->hasRole('Super Admin'))
                            <button type="button" class="btn btn-primary btn-sm" id="btnAddPendapatan">
                                <i class="bx bx-plus me-1"></i>Tambah Pendapatan
                            </button>
                            @endif
                        </div>

                        <!-- Table Container -->
                        <div id="pendapatanTableContainer">
                            <div class="text-center py-5">
                                <i class="bx bx-money" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-3">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Pendapatan Confirmation Modal -->
    <div class="modal fade" id="deletePendapatanConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body border-top border-bottom">
                    <p>Apakah Anda yakin ingin menghapus data pendapatan ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeletePendapatanBtn">Hapus</button>
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
<script src="{{ asset('js/file-preview.js') }}"></script>
<script>
    window.routes = {
        getApprovedBA: "{{ route('pendapatan.getApprovedBA') }}",
        getPendapatanByBA: "{{ route('pendapatan.getByBA') }}",
        storePendapatan: "{{ route('pendapatan.store') }}",
        updatePendapatan: "{{ route('pendapatan.update', ['noPendapatan' => ':noPendapatan']) }}",
        deletePendapatan: "{{ route('pendapatan.destroy', ['noPendapatan' => ':noPendapatan']) }}",
        downloadPendapatan: "{{ route('pendapatan.download', ['noPendapatan' => ':noPendapatan']) }}"
    };
    window.csrfToken = "{{ csrf_token() }}";
    window.userRole = "{{ auth()->user()->roles->first()->name ?? 'User' }}";
</script>
<script src="{{ asset('js/pendapatan.js') }}"></script>
@endpush
</x-layout>
