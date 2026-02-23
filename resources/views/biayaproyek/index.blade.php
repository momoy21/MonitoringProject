<x-layout title="Biaya Proyek">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-12">
                <h4 class="fw-bold mb-2">Biaya Proyek</h4>
                <p class="mb-0">Monitoring Rencana vs Aktual untuk Pendapatan dan HPP</p>
            </div>
        </div>

        <!-- Cost Center Selection - Full Width -->
        <div class="row mt-3">
            <div class="col-12">
                <label for="cost_center_select" class="form-label fw-bold text-uppercase">Pilih Cost Center</label>
                <select id="cost_center_select" class="form-select" style="width: 100%;">
                    <option value="">-- Pilih Cost Center --</option>
                </select>
                <small class="text-muted">Hanya menampilkan proyek yang sudah memiliki Header RAB dengan Mulai & Lama</small>
            </div>
        </div>
    </div>

    <!-- Info Section Card (hidden by default) -->
    <div class="card" id="projectInfoSection" style="display: none;">
        <div class="card-body">
            <!-- Baris 1: Cost Center, Nama Proyek, Konsumen (3 kolom) -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">COST CENTER</label>
                    <input type="text" class="form-control" id="info_cost_center" disabled readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">NAMA PROYEK</label>
                    <input type="text" class="form-control" id="info_namaproject" disabled readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">KONSUMEN</label>
                    <input type="text" class="form-control" id="info_konsumen" disabled readonly>
                </div>
            </div>

            <!-- Baris 2: Nilai Proyek, No Kontrak (2 kolom, sejajar baris 1) -->
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label fw-bold">NILAI PROYEK</label>
                    <input type="text" class="form-control" id="info_nilai_proyek" disabled readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">NO. KONTRAK</label>
                    <input type="text" class="form-control" id="info_no_kontrak" disabled readonly>
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
                        <th colspan="4" class="fw-bold text-white">
                            <i class="bx bx-trending-up me-2"></i>Pendapatan
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center fw-bold" style="width: 50px;">No</th>
                        <th class="text-center fw-bold">Keterangan</th>
                        <th class="text-center fw-bold">Bulan</th>
                        <th class="text-center fw-bold">Total</th>
                    </tr>
                </thead>
                <tbody id="pendapatanTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-4">
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
                        <th colspan="2" class="text-center fw-bold" id="bulanAwalHeader">Periode Awal</th>
                        <th colspan="2" class="text-center fw-bold" id="sdBulanIniHeader">S.D. Periode Saat Ini</th>
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
    }
    .biayaproyek-table thead th {
        background-color: #fff;
        box-shadow: 0 1px 0 0 #dee2e6;
    }
    .biayaproyek-table tfoot {
        position: sticky;
        bottom: 0;
        z-index: 10;
    }
    .biayaproyek-table tfoot td {
        background-color: #e9ecef;
        box-shadow: 0 -1px 0 0 #dee2e6;
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
        box-shadow: none;
    }
    .biayaproyek-table .table-danger th,
    .biayaproyek-table .table-danger th * {
        background-color: #dc3545 !important;
        color: #fff !important;
        box-shadow: none;
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
