<x-layout title="Tambah Kondisi Proyek">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Kondisi Proyek', 'url' => route('kondisiproyek.index')],
            ['name' => 'Tambah Kondisi Proyek']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section - Consistent with Index -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Kondisi Proyek Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah kondisi proyek baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('kondisiproyek.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="kondisiProyekForm" method="POST" action="{{ route('kondisiproyek.store') }}">
                @csrf

                <!-- ID Kondisi Proyek Display -->
                <div class="form-section">
                    <h6 class="mb-3">ID Kondisi Proyek</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="kondisi-proyek-id-display">
                                {{ $nextId }}
                            </div>
                            <small class="text-muted">ID akan dibuat otomatis</small>
                        </div>
                    </div>
                </div>

                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Kondisi Proyek</h6>
                    <div class="row">
                        <x-form-field
                            name="desc_kondisi_proyek"
                            label="Kondisi Proyek"
                            :required="true"
                            maxlength="255"
                            placeholder="Masukkan deskripsi kondisi proyek"
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
                    <a href="{{ route('kondisiproyek.index') }}" class="btn btn-outline-secondary">
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
    <script src="{{ asset('js/kondisiproyek.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        // Clear state when creating new kondisi proyek
        if (window.StateManagers?.kondisiProyek) {
            window.StateManagers.kondisiProyek.clearState();
            console.log('State cleared on create page load');
        }

        // Initialize kondisi proyek manager untuk halaman create
        window.kondisiProyekManager = new KondisiProyekManager();

        window.kondisiProyekManager.init({
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
