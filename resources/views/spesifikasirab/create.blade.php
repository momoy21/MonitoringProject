<x-layout title="Tambah Spesifikasi RAB">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Spesifikasi RAB', 'url' => route('spesifikasirab.index')],
            ['name' => 'Tambah Spesifikasi RAB']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Spesifikasi RAB Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah spesifikasi RAB baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('spesifikasirab.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="spesifikasiRABForm" method="POST" action="{{ route('spesifikasirab.store') }}">
                @csrf

                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Dasar</h6>
                    <div class="row">
                        <!-- ID Spec (Auto-generated, display only) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_spec_display" class="form-label">ID Spec <span class="text-danger">*</span></label>
                                <div class="id_spec-display">{{ $nextid_spec }}</div>
                                <small class="text-muted">ID akan digenerate otomatis</small>
                            </div>
                        </div>

                        <!-- Nomor Urut -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="norutspec" class="form-label">Nomor Urut <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('norutspec') is-invalid @enderror"
                                       id="norutspec" name="norutspec"
                                       placeholder="Masukkan nomor urut (01-99)"
                                       value="{{ old('norutspec', $nextNorutSpec) }}"
                                       maxlength="2"
                                       required>
                                <div class="invalid-feedback" id="norutspec-error"></div>
                                <small class="text-muted">Auto-generated berdasarkan jumlah data (dapat diedit)</small>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('kategori') is-invalid @enderror"
                                        id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="PDP" {{ old('kategori') == 'PDP' ? 'selected' : '' }}>PDP - Pendapatan</option>
                                    <option value="HPP" {{ old('kategori') == 'HPP' ? 'selected' : '' }}>HPP - Harga Pokok Penjualan</option>
                                </select>
                                <div class="invalid-feedback" id="kategori-error"></div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="A" {{ old('status', 'A') == 'A' ? 'selected' : '' }}>Aktif</option>
                                    <option value="N" {{ old('status') == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                </select>
                                <div class="invalid-feedback" id="status-error">
                                    @error('status'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">Status opsional. Hanya spesifikasi RAB dengan status aktif yang dapat digunakan dalam sistem lain</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Spesifikasi RAB -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="spec_rab" class="form-label">Spesifikasi RAB <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('spec_rab') is-invalid @enderror"
                                          id="spec_rab" name="spec_rab"
                                          rows="3"
                                          placeholder="Masukkan spesifikasi RAB"
                                          maxlength="100"
                                          required>{{ old('spec_rab') }}</textarea>
                                <div class="invalid-feedback" id="spec_rab-error"></div>
                                <small class="text-muted">Maksimal 100 karakter</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('spesifikasirab.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Batal
                    </a>
                    <button type="reset" class="btn btn-outline-warning" onclick="resetForm()">
                        <i class="bx bx-reset me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
                        <i class="bx bx-check me-1" id="submitIcon"></i>
                        <span id="submitText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/spesifikasirab.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        // Clear state when creating new spesifikasi RAB
        if (window.StateManagers?.spesifikasiRAB) {
            window.StateManagers.spesifikasiRAB.clearState();
            console.log('State cleared on create page load');
        }

        // Initialize spesifikasi RAB manager untuk halaman create
        window.spesifikasiRABManager = new SpesifikasiRABManager();

        window.spesifikasiRABManager.init({
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
