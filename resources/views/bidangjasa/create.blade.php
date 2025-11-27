<x-layout title="Tambah Bidang Jasa">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Bidang Jasa', 'url' => route('bidangjasa.index')],
            ['name' => 'Tambah Bidang Jasa']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section - Consistent with Index -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Bidang Jasa Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah bidang jasa baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('bidangjasa.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="bidangJasaForm" method="POST" action="{{ route('bidangjasa.store') }}">
                @csrf

                <!-- ID Bidang Jasa Display -->
                <div class="form-section">
                    <h6 class="mb-3">ID Bidang Jasa</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="bidangjasa-id-display">
                                {{ $nextId }}
                            </div>
                            <small class="text-muted">ID akan dibuat otomatis</small>
                        </div>
                    </div>
                </div>

                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Bidang Jasa</h6>
                    <div class="row">
                        <x-form-field
                            name="desc_bidjasa"
                            label="Bidang Jasa"
                            :required="true"
                            maxlength="50"
                            placeholder="Masukkan deskripsi bidang jasa"
                            class="col-md-12" />
                    </div>
                </div>

                <!-- Status -->
                <div class="form-section">
                    <h6 class="mb-3">Status</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <select name="status" class="form-select" required>
                                <option value="A" selected>Aktif</option>
                                <option value="N">Non Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('bidangjasa.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Batal
                    </a>
                    <button type="reset" class="btn btn-outline-warning">
                        <i class="bx bx-refresh me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                        <i class="bx bx-check me-1" id="submitIcon"></i>
                        <span id="submitText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/bidangjasa.js') }}?v={{ time() }}"></script>
    <script>
    // Clear any saved state when entering create page
    if (window.StateManagers?.bidangJasa) {
        window.StateManagers.bidangJasa.clearState();
    }

    $(document).ready(function() {
        // Initialize bidang jasa manager untuk halaman create
        window.bidangJasaManager = new BidangJasaManager();

        window.bidangJasaManager.init({
            pageType: 'create'
        });

        // Set button text untuk create
        $('#submitBtn')
            .data('default-text', 'Simpan')
            .data('loading-text', 'Menyimpan...');
    });
    </script>
    @endpush
</x-layout>
