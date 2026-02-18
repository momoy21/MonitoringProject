<x-layout title="Tambah Jenis Proyek">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Jenis Proyek', 'url' => route('jenisproyek.index')],
            ['name' => 'Tambah Jenis Proyek']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Jenis Proyek Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah jenis proyek baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('jenisproyek.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="jenisProyekForm" method="POST" action="{{ route('jenisproyek.store') }}">
                @csrf

                <div class="form-section">
                    <h6 class="mb-3">Informasi Jenis Proyek</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ID Jenis Proyek (Kode)</label>
                                <input type="text" 
                                       class="form-control bg-light" 
                                       placeholder="Otomatis (Sistem)" 
                                       readonly 
                                       disabled>
                                <small class="text-muted">Akan di-generate otomatis (P1, P2, dst)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                {{-- Penyesuaian name dan id ke nama_jenis --}}
                                <label for="nama_jenis" class="form-label">Nama Jenis Proyek <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('nama_jenis') is-invalid @enderror"
                                       id="nama_jenis"
                                       name="nama_jenis"
                                       maxlength="50"
                                       placeholder="Masukkan nama jenis proyek"
                                       value="{{ old('nama_jenis') }}"
                                       required>
                                <div class="invalid-feedback">
                                    @error('nama_jenis'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">Maksimal 50 karakter</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status"
                                        required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="A" {{ old('status') == 'A' ? 'selected' : '' }}>Aktif</option>
                                    <option value="N" {{ old('status') == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                </select>
                                <div class="invalid-feedback">
                                    @error('status'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('jenisproyek.index') }}" class="btn btn-outline-secondary">
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
    <script src="{{ asset('js/jenisproyek.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        // Naming section: State management logic
        if (window.StateManagers?.jenisProyek) {
            window.StateManagers.jenisProyek.clearState();
        }

        if (typeof JenisProyekManager === 'function') {
            window.jenisProyekManager = new JenisProyekManager();
            window.jenisProyekManager.init({
                pageType: 'create'
            });
        }

        $('#submitBtn')
            .data('default-text', 'Simpan')
            .data('loading-text', 'Menyimpan...');
    });
    </script>
    @endpush
</x-layout>