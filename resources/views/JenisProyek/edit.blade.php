<x-layout title="Edit Jenis Proyek">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Jenis Proyek', 'url' => route('jenisproyek.index')],
            ['name' => 'Edit Jenis Proyek']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                {{-- Menampilkan kode_jenis terbaru --}}
                <h4 class="fw-bold mb-2">Edit Jenis Proyek: {{ $item->kode_jenis }}</h4>
                <p class="mb-0">Perbarui informasi detail untuk jenis proyek ini</p>
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
            {{-- Update route menggunakan kode_jenis --}}
            <form id="editJenisProyekForm" action="{{ route('jenisproyek.update', $item->kode_jenis) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h6 class="mb-3">Informasi Jenis Proyek</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ID Jenis Proyek (Kode)</label>
                                <input type="text" class="form-control bg-light" value="{{ $item->kode_jenis }}" readonly>
                                <small class="text-muted">ID jenis proyek tidak dapat diubah.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                {{-- Penyesuaian ke nama_jenis --}}
                                <label for="nama_jenis" class="form-label">Nama Jenis Proyek <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="nama_jenis" 
                                       id="nama_jenis"
                                       class="form-control @error('nama_jenis') is-invalid @enderror" 
                                       maxlength="50" 
                                       value="{{ old('nama_jenis', $item->nama_jenis) }}" 
                                       required>
                                <div class="invalid-feedback">
                                    @error('nama_jenis'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="A" {{ old('status', $item->status) == 'A' ? 'selected' : '' }}>Aktif</option>
                                    <option value="N" {{ old('status', $item->status) == 'N' ? 'selected' : '' }}>Non Aktif</option>
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                        <i class="bx bx-save me-1" id="submitIcon"></i>
                        <span id="submitText">Update Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/jenisproyek.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        $('#submitBtn')
            .data('default-text', 'Update Data')
            .data('loading-text', 'Memperbarui...');
    });
    </script>
    @endpush
</x-layout>