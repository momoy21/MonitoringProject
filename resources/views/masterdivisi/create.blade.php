<x-layout title="Tambah Divisi">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            // Diubah ke Master Divisi
            ['name' => 'Master Divisi', 'url' => route('masterdivisi.index')],
            ['name' => 'Tambah Divisi']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Divisi Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah divisi baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('masterdivisi.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="divisiForm" method="POST" action="{{ route('masterdivisi.store') }}">
                @csrf

                <div class="form-section">
                    <h6 class="mb-3">Informasi Divisi</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kode_divisi" class="form-label">Kode Divisi <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('kode_divisi') is-invalid @enderror"
                                       id="kode_divisi"
                                       name="kode_divisi"
                                       placeholder="Masukkan kode divisi"
                                       value="{{ old('kode_divisi') }}"
                                       required>
                                <div class="invalid-feedback">
                                    @error('kode_divisi'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_divisi" class="form-label">Nama Divisi <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('nama_divisi') is-invalid @enderror"
                                       id="nama_divisi"
                                       name="nama_divisi"
                                       maxlength="100"
                                       placeholder="Masukkan nama divisi"
                                       value="{{ old('nama_divisi') }}"
                                       required>
                                <div class="invalid-feedback">
                                    @error('nama_divisi'){{ $message }}@enderror
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
                                <div class="invalid-feedback">
                                    @error('status'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
    <script src="{{ asset('js/divisi.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        // Clear state masterDivisi
        if (window.StateManagers?.masterDivisi) {
            window.StateManagers.masterDivisi.clearState();
        }

        // Initialize divisi manager
        window.divisiManager = new DivisiManager();
        window.divisiManager.init({
            pageType: 'create'
        });

        $('#submitBtn')
            .data('default-text', 'Simpan')
            .data('loading-text', 'Menyimpan...');
    });
    </script>
    @endpush
</x-layout>