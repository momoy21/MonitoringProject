<x-layout title="Edit Karyawan">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Karyawan', 'url' => route('karyawan.index')],
            ['name' => 'Edit Karyawan']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Karyawan</h4>
                <p class="mb-0">Ubah data karyawan <strong class="text-primary">{{ $karyawan->nama }}</strong></p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="karyawanForm" method="POST" action="{{ route('karyawan.update', $karyawan->nik) }}">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h6 class="mb-3 text-primary fw-bold border-bottom pb-2">Informasi Karyawan</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="nik"
                                       name="nik_display"
                                       value="{{ $karyawan->nik }}"
                                       readonly
                                       disabled>
                                <small class="form-text text-muted">NIK tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Karyawan <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('nama') is-invalid @enderror"
                                       id="nama"
                                       name="nama"
                                       maxlength="100"
                                       placeholder="Masukkan nama karyawan"
                                       value="{{ old('nama', $karyawan->nama) }}"
                                       required>
                                <div class="invalid-feedback">
                                    @error('nama'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">Maksimal 100 karakter</small>
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
                                    @foreach($statusOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $karyawan->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @error('status'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Aktif <span class="text-danger">*</span></label>
                                <div class="mt-2">
                                    @foreach($aktifOptions as $key => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('aktif') is-invalid @enderror" 
                                               type="radio" 
                                               name="aktif" 
                                               id="aktif_{{ $key }}" 
                                               value="{{ $key }}"
                                               {{ old('aktif', $karyawan->aktif) == $key ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label" for="aktif_{{ $key }}">{{ $label }}</label>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="invalid-feedback">
                                    @error('aktif'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                        <i class="bx bx-check me-1" id="submitIcon"></i>
                        <span id="submitText">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        // Form submission with loading state
        $('#karyawanForm').on('submit', function() {
            $('#submitBtn').prop('disabled', true);
            $('#submitSpinner').removeClass('d-none');
            $('#submitIcon').addClass('d-none');
            $('#submitText').text('Menyimpan...');
        });
    });
    </script>
    @endpush
</x-layout>
