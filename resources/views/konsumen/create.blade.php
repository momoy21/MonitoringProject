<x-layout title="Tambah Konsumen">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Konsumen', 'url' => route('konsumen.index')],
            ['name' => 'Tambah Konsumen']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section - Consistent with Index -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Konsumen Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah konsumen baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('konsumen.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="konsumenForm" method="POST" action="{{ route('konsumen.store') }}">
                @csrf

                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Dasar</h6>
                    <div class="row">
                        <x-form-field
                            name="konsumen"
                            label="Nama Konsumen"
                            :required="true"
                            maxlength="150"
                            placeholder="Masukkan nama konsumen"
                            class="col-md-8" />

                        <div class="col-md-4">
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
                                <small class="form-text text-muted">Status opsional. Hanya konsumen dengan status aktif yang dapat digunakan dalam proyek</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Lokasi</h6>
                    <div class="row">
                        <x-form-field
                            name="provinsi_id"
                            label="Provinsi"
                            type="select"
                            :options="$provinsi->pluck('nama', 'id')" />

                        <x-form-field
                            name="kota_id"
                            label="Kota"
                            type="select"
                            :options="[]" />
                    </div>
                    <div class="row">
                        <x-form-field
                            name="alamat1"
                            label="Alamat 1"
                            maxlength="255"
                            placeholder="Jalan, No. Rumah, RT/RW" />

                        <x-form-field
                            name="alamat2"
                            label="Alamat 2"
                            maxlength="255"
                            placeholder="Kelurahan, Kecamatan" />
                    </div>
                    <div class="row">
                        <x-form-field
                            name="kode_pos"
                            label="Kode Pos"
                            maxlength="5"
                            pattern="[0-9]{5}"
                            placeholder="12345" />
                    </div>
                </div>

                <!-- Kontak -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Kontak</h6>
                    <div class="row">
                        <x-form-field
                        name="telp_kantor"
                        label="Telepon Kantor"
                        maxlength="20"
                        pattern="[0-9\-]{7,20}"
                        placeholder="021-1234567" />

                    <x-form-field
                        name="fax"
                        label="Fax"
                        maxlength="20"
                        pattern="[0-9\-]{7,20}"
                        placeholder="021-1234568" />
                    </div>
                    <div class="row">
                        <x-form-field
                            name="email"
                            label="Email"
                            type="email"
                            maxlength="70"
                            placeholder="konsumen@domain.com" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('konsumen.index') }}" class="btn btn-outline-secondary">
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

    <script src="{{ asset('js/konsumen.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Initialize konsumen manager untuk halaman create
        window.konsumenManager = new KonsumenManager();

        window.konsumenManager.init({
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
