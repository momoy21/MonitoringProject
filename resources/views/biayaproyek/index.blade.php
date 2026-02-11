<x-layout title="Biaya Proyek">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Biaya Proyek</h4>
                <p class="mb-0">Monitoring Rencana vs Aktual untuk Pendapatan dan HPP</p>
            </div>
        </div>

        <!-- Cost Center Selection -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-2">
                    <label for="cost_center_select" class="form-label mb-0 fw-bold">Pilih Cost Center:</label>
                    <select id="cost_center_select" class="form-select" style="width: 100%; max-width: 600px;">
                        <option value="">-- Pilih Cost Center --</option>
                    </select>
                </div>
                <small class="text-muted">Hanya menampilkan proyek yang sudah memiliki Header RAB dengan Mulai & Lama</small>
            </div>
        </div>
    </div>

    <!-- Info Section Card (hidden by default) -->
    <div class="card" id="projectInfoSection" style="display: none;">
        <div class="card-body">
            <!-- Baris 1: Cost Center, Nama Proyek, Konsumen -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Cost Center</label>
                    <input type="text" class="form-control" id="info_cost_center" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nama Proyek</label>
                    <input type="text" class="form-control" id="info_namaproject" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Konsumen</label>
                    <input type="text" class="form-control" id="info_konsumen" readonly>
                </div>
            </div>

            <!-- Baris 2: Mulai, Lama, No Kontrak -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mulai</label>
                    <input type="text" class="form-control" id="info_mulai" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Lama</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="info_lama" readonly>
                        <span class="input-group-text">Bulan</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">No. Kontrak</label>
                    <input type="text" class="form-control" id="info_no_kontrak" readonly>
                </div>
            </div>

            <!-- Baris 3: Nilai Proyek, Tanggal Kontrak, Akhir Kontrak -->
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nilai Proyek</label>
                    <input type="text" class="form-control" id="info_nilai_proyek" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tanggal Kontrak</label>
                    <input type="text" class="form-control" id="info_tanggal_kontrak" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Akhir Kontrak</label>
                    <input type="text" class="form-control" id="info_akhir_kontrak" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendapatan Section -->
    <div class="card mt-3" id="pendapatanSection" style="display: none;">
        <div class="table-responsive biayaproyek-table-container">
            <table class="table table-striped table-hover biayaproyek-table" id="pendapatanTable">
                <thead>
                    <tr class="table-primary">
                        <th colspan="8" class="fw-bold text-white">
                            <i class="bx bx-trending-up me-2"></i>Pendapatan
                            <span class="float-end text-white" id="bulanIniLabel" style="font-weight: normal; font-size: 0.875rem; opacity: 0.9;"></span>
                        </th>
                    </tr>
                    <tr>
                        <th rowspan="2" class="text-center align-middle fw-bold" style="width: 50px;">No</th>
                        <th rowspan="2" class="text-center align-middle fw-bold">Keterangan</th>
                        <th colspan="2" class="text-center fw-bold">Bulan Ini</th>
                        <th colspan="2" class="text-center fw-bold">S.D. Bulan Ini</th>
                        <th colspan="2" class="text-center fw-bold">Total</th>
                    </tr>
                    <tr>
                        <th class="text-center fw-bold">Rencana</th>
                        <th class="text-center fw-bold">Aktual</th>
                        <th class="text-center fw-bold">Rencana</th>
                        <th class="text-center fw-bold">Aktual</th>
                        <th class="text-center fw-bold">Rencana</th>
                        <th class="text-center fw-bold">Aktual</th>
                    </tr>
                </thead>
                <tbody id="pendapatanTableBody">
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>
                                <p class="mb-0 text-muted">Pilih Cost Center untuk melihat data</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary fw-bold" id="pendapatanTableFoot">
                </tfoot>
            </table>
        </div>
    </div>

    <!-- HPP Section -->
    <div class="card mt-3" id="hppSection" style="display: none;">
        <div class="table-responsive biayaproyek-table-container">
            <table class="table table-striped table-hover biayaproyek-table" id="hppTable">
                <thead>
                    <tr class="table-danger">
                        <th colspan="8" class="fw-bold text-white">
                            <i class="bx bx-trending-down me-2"></i>Harga Pokok Penjualan (HPP)
                        </th>
                    </tr>
                    <tr>
                        <th rowspan="2" class="text-center align-middle fw-bold" style="width: 50px;">No</th>
                        <th rowspan="2" class="text-center align-middle fw-bold">Keterangan</th>
                        <th colspan="2" class="text-center fw-bold">Bulan Ini</th>
                        <th colspan="2" class="text-center fw-bold">S.D. Bulan Ini</th>
                        <th colspan="2" class="text-center fw-bold">Total</th>
                    </tr>
                    <tr>
                        <th class="text-center fw-bold">Rencana</th>
                        <th class="text-center fw-bold">Aktual</th>
                        <th class="text-center fw-bold">Rencana</th>
                        <th class="text-center fw-bold">Aktual</th>
                        <th class="text-center fw-bold">Rencana</th>
                        <th class="text-center fw-bold">Aktual</th>
                    </tr>
                </thead>
                <tbody id="hppTableBody">
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>
                                <p class="mb-0 text-muted">Pilih Cost Center untuk melihat data</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary fw-bold" id="hppTableFoot">
                </tfoot>
            </table>
        </div>
    </div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .biayaproyek-table-container {
        max-height: calc(100vh - 400px);
        overflow-y: auto;
    }
    .biayaproyek-table thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #fff;
    }
    .biayaproyek-table th,
    .biayaproyek-table td {
        vertical-align: middle;
    }
    .biayaproyek-table .currency-value {
        text-align: right;
        font-family: 'Courier New', monospace;
        white-space: nowrap;
    }
    .biayaproyek-table .table-primary th,
    .biayaproyek-table .table-primary th * {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    .biayaproyek-table .table-danger th,
    .biayaproyek-table .table-danger th * {
        background-color: #dc3545 !important;
        color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.routes = {
        getCostCenter: "{{ route('biayaproyek.getCostCenter') }}",
        getData: "{{ route('biayaproyek.getData') }}"
    };
    window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/biayaproyek.js') }}?v={{ time() }}"></script>
@endpush
</x-layout>
