<x-layout title="Tambah Manager">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Manager', 'url' => route('mastermanager.index')],
            ['name' => 'Tambah Manager']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section - Consistent with Index -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Manager Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah manager baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('mastermanager.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="managerForm" method="POST" action="{{ route('mastermanager.store') }}">
                @csrf

                <!-- Informasi Manager -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Manager</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nik" class="form-label">Nomor Induk Karyawan <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('nik') is-invalid @enderror"
                                       id="nik"
                                       name="nik"
                                       maxlength="7"
                                       placeholder="Masukkan NIK (7 karakter)"
                                       value="{{ old('nik') }}"
                                       pattern="[A-Za-z0-9]{7}"
                                       title="NIK harus berupa 7 karakter huruf dan angka"
                                       required>
                                <div class="invalid-feedback" id="nik-error">
                                    @error('nik'){{ $message }}@enderror
                                </div>
                                <div class="valid-feedback" id="nik-success"></div>
                                <small class="form-text text-muted">NIK harus unik dan terdiri dari 7 karakter (huruf dan angka)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Manager <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('nama') is-invalid @enderror"
                                       id="nama"
                                       name="nama"
                                       maxlength="100"
                                       placeholder="Masukkan nama manager"
                                       value="{{ old('nama') }}"
                                       required>
                                <div class="invalid-feedback" id="nama-error">
                                    @error('nama'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">Maksimal 100 karakter</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="A" {{ old('status') == 'A' ? 'selected' : '' }}>Aktif</option>
                                    <option value="N" {{ old('status') == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                </select>
                                <div class="invalid-feedback" id="status-error">
                                    @error('status'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">Status opsional. Hanya manager dengan status aktif yang dapat ditugaskan ke proyek</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('mastermanager.index') }}" class="btn btn-outline-secondary">
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
    <script src="{{ asset('js/manager.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Initialize manager manager untuk halaman create
        window.managerManager = new ManagerManager();

        window.managerManager.init({
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
