<x-layout title="Edit Spesifikasi RAB {{ $spesifikasirab->id_spec }}">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Spesifikasi RAB', 'url' => route('spesifikasirab.index')],
            ['name' => 'Edit Spesifikasi RAB']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Spesifikasi RAB</h4>
                <p class="mb-0">Perbarui informasi spesifikasi RAB {{ $spesifikasirab->id_spec }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('spesifikasirab.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.spesifikasiRAB) window.StateManagers.spesifikasiRAB.markForRestore();">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="card">
        <div class="card-body">
            <form id="spesifikasiRABForm" method="POST" action="{{ route('spesifikasirab.update', $spesifikasirab->id_spec) }}">
                @csrf
                @method('PUT')

                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Dasar</h6>
                    <div class="row">
                        <!-- ID Spec (Read-only) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_spec_display" class="form-label">ID Spec <span class="text-danger">*</span></label>
                                <div class="id_spec-display">{{ $spesifikasirab->id_spec }}</div>
                                <small class="text-muted">ID tidak dapat diubah</small>
                            </div>
                        </div>

                        <!-- Nomor Urut -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="norutspec" class="form-label">Nomor Urut <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('norutspec') is-invalid @enderror"
                                       id="norutspec" name="norutspec"
                                       placeholder="Masukkan nomor urut (01-99)"
                                       value="{{ old('norutspec', $spesifikasirab->norutspec) }}"
                                       maxlength="2"
                                       required>
                                <div class="invalid-feedback" id="norutspec-error"></div>
                                <small class="text-muted">Nomor urut untuk pengurutan tampilan (2 digit)</small>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('kategori') is-invalid @enderror"
                                        id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="PDP" {{ old('kategori', $spesifikasirab->kategori) == 'PDP' ? 'selected' : '' }}>PDP - Pendapatan</option>
                                    <option value="HPP" {{ old('kategori', $spesifikasirab->kategori) == 'HPP' ? 'selected' : '' }}>HPP - Harga Pokok Penjualan</option>
                                </select>
                                <div class="invalid-feedback" id="kategori-error"></div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="A" {{ old('status', $spesifikasirab->status) == 'A' ? 'selected' : '' }}>Aktif</option>
                                    <option value="N" {{ old('status', $spesifikasirab->status) == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                </select>
                                <div class="invalid-feedback" id="status-error">
                                    @error('status'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">Hanya spesifikasi RAB dengan status aktif yang dapat digunakan dalam sistem lain</small>
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
                                          required>{{ old('spec_rab', $spesifikasirab->spec_rab) }}</textarea>
                                <div class="invalid-feedback" id="spec_rab-error"></div>
                                <small class="text-muted">Maksimal 100 karakter</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('spesifikasirab.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.spesifikasiRAB) window.StateManagers.spesifikasiRAB.markForRestore();">
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
        // Set data original untuk reset function
        window.originalFormData = {
            norutspec: '{{ $spesifikasirab->norutspec }}',
            spec_rab: '{{ $spesifikasirab->spec_rab }}',
            kategori: '{{ $spesifikasirab->kategori }}',
            status: '{{ $spesifikasirab->status }}'
        };

        // Initialize spesifikasi RAB manager untuk halaman edit
        window.spesifikasiRABManager = new SpesifikasiRABManager();

        window.spesifikasiRABManager.init({
            pageType: 'edit',
            currentid_spec: '{{ $spesifikasirab->id_spec }}'
        });

        // Set button text untuk edit
        $('#submitBtn')
            .data('default-text', 'Update')
            .data('loading-text', 'Memperbarui...');
    });
    </script>
    @endpush
</x-layout>
