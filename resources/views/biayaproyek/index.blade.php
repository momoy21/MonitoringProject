<x-layout title="Biaya Proyek">
    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Biaya Proyek</h4>
                <p class="mb-0">Monitoring Rencana vs Aktual untuk Pendapatan dan HPP</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="biayaproyek-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Form Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="cost_center_select" class="form-label fw-bold">
                                    Pilih Cost Center <span class="text-danger">*</span>
                                </label>
                                <select id="cost_center_select" class="form-select" style="width: 100%;">
                                    <option value="">-- Pilih Cost Center --</option>
                                </select>
                                <small class="text-muted">Hanya menampilkan proyek yang sudah memiliki Header RAB dengan Mulai & Lama</small>
                            </div>
                        </div>

                        <!-- Info Section (hidden by default) -->
                        <div id="projectInfoSection" style="display: none;">
                            <!-- Baris 1: Cost Center, Nama Proyek, Konsumen -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Cost Center</label>
                                    <input type="text" class="form-control" id="info_cost_center" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Proyek</label>
                                    <input type="text" class="form-control" id="info_namaproject" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Konsumen</label>
                                    <input type="text" class="form-control" id="info_konsumen" readonly>
                                </div>
                            </div>

                            <!-- Baris 2: Mulai, Lama, No Kontrak -->
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
                                    <label class="form-label">No. Kontrak</label>
                                    <input type="text" class="form-control" id="info_no_kontrak" readonly>
                                </div>
                            </div>

                            <!-- Baris 3: Nilai Proyek, Tanggal Kontrak, Akhir Kontrak -->
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendapatan Section -->
        <div class="row mt-4" id="pendapatanSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>Pendapatan</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="pendapatanTable">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" class="text-center align-middle" style="width: 50px;">No</th>
                                        <th rowspan="2" class="text-center align-middle">Keterangan</th>
                                        <th colspan="2" class="text-center" id="bulanIniHeader">Bulan Ini</th>
                                        <th colspan="2" class="text-center">S.D. Bulan Ini</th>
                                        <th colspan="2" class="text-center">Total</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Rencana</th>
                                        <th class="text-center">Aktual</th>
                                        <th class="text-center">Rencana</th>
                                        <th class="text-center">Aktual</th>
                                        <th class="text-center">Rencana</th>
                                        <th class="text-center">Aktual</th>
                                    </tr>
                                </thead>
                                <tbody id="pendapatanTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bx bx-loader-alt bx-spin" style="font-size: 24px;"></i>
                                            <p class="mb-0 mt-2">Loading...</p>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-secondary fw-bold" id="pendapatanTableFoot">
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HPP Section -->
        <div class="row mt-4" id="hppSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bx bx-trending-down me-2"></i>Harga Pokok Penjualan (HPP)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="hppTable">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" class="text-center align-middle" style="width: 50px;">No</th>
                                        <th rowspan="2" class="text-center align-middle">Keterangan</th>
                                        <th colspan="2" class="text-center">Bulan Ini</th>
                                        <th colspan="2" class="text-center">S.D. Bulan Ini</th>
                                        <th colspan="2" class="text-center">Total</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Rencana</th>
                                        <th class="text-center">Aktual</th>
                                        <th class="text-center">Rencana</th>
                                        <th class="text-center">Aktual</th>
                                        <th class="text-center">Rencana</th>
                                        <th class="text-center">Aktual</th>
                                    </tr>
                                </thead>
                                <tbody id="hppTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bx bx-loader-alt bx-spin" style="font-size: 24px;"></i>
                                            <p class="mb-0 mt-2">Loading...</p>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-secondary fw-bold" id="hppTableFoot">
                                </tfoot>
                            </table>
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
    .biayaproyek-container .table th,
    .biayaproyek-container .table td {
        vertical-align: middle;
    }
    .biayaproyek-container .table thead th {
        background-color: #f8f9fa;
    }
    .biayaproyek-container .currency-value {
        text-align: right;
        font-family: 'Courier New', monospace;
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
<script src="{{ asset('js/biayaproyek.js') }}"></script>
@endpush
</x-layout>
