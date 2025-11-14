<x-layout title="Edit Konsumen {{ $konsumen->konsumen }}">
    @push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Konsumen', 'url' => route('konsumen.index')],
            ['name' => 'Edit Konsumen']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header -->
     <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Konsumen</h4>
                <p class="mb-0">Perbarui informasi konsumen {{ $konsumen->konsumen }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('konsumen.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <form id="konsumenForm" method="POST" action="{{ route('konsumen.update', $konsumen->id_konsumen) }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">
                        <!-- ID Konsumen Display -->
                        <div class="form-section">
                            <h6 class="mb-3">ID Konsumen</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="konsumen-id-display">
                                        {{ $konsumen->id_konsumen }}
                                    </div>
                                    <small class="text-muted">ID tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Dasar -->
                        <div class="form-section">
                            <h6 class="mb-3">Informasi Dasar</h6>
                            <div class="row">
                                <x-form-field
                                    name="konsumen"
                                    label="Nama Konsumen"
                                    :value="$konsumen->konsumen"
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
                                            <option value="A" {{ old('status', $konsumen->status) == 'A' ? 'selected' : '' }}>Aktif</option>
                                            <option value="N" {{ old('status', $konsumen->status) == 'N' ? 'selected' : '' }}>Non Aktif</option>
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
                                    :value="$konsumen->provinsi_id"
                                    :options="$provinsi->pluck('nama', 'id')" />

                                <x-form-field
                                    name="kota_id"
                                    label="Kota"
                                    type="select"
                                    :value="$konsumen->kota_id"
                                    :options="$kota->pluck('nama', 'id')" />
                            </div>
                            <div class="row">
                                <x-form-field
                                    name="alamat1"
                                    label="Alamat 1"
                                    :value="$konsumen->alamat1"
                                    maxlength="255"
                                    placeholder="Jalan, No. Rumah, RT/RW" />

                                <x-form-field
                                    name="alamat2"
                                    label="Alamat 2"
                                    :value="$konsumen->alamat2"
                                    maxlength="255"
                                    placeholder="Kelurahan, Kecamatan" />
                            </div>
                            <div class="row">
                                <x-form-field
                                    name="kode_pos"
                                    label="Kode Pos"
                                    :value="$konsumen->kode_pos"
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
                                :value="$konsumen->telp_kantor"
                                maxlength="20"
                                pattern="[0-9\-]{7,20}"
                                placeholder="021-1234567" />

                            <x-form-field
                                name="fax"
                                label="Fax"
                                :value="$konsumen->fax"
                                maxlength="20"
                                pattern="[0-9\-]{7,20}"
                                placeholder="021-1234568" />
                            </div>
                            <div class="row">
                                <x-form-field
                                    name="email"
                                    label="Email"
                                    type="email"
                                    :value="$konsumen->email"
                                    maxlength="70"
                                    placeholder="konsumen@domain.com" />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('konsumen.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-1"></i> Batal
                            </a>
                            <button type="button" class="btn btn-outline-warning" onclick="window.resetForm()">
                                <i class="bx bx-refresh me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                                <i class="bx bx-check me-1" id="submitIcon"></i>
                                <span id="submitText">Simpan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Searchable Select Handler -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>

    <script src="{{ asset('js/konsumen.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Set data original untuk reset function
        window.originalFormData = {
            konsumen: '{{ $konsumen->konsumen }}',
            provinsi_id: '{{ $konsumen->provinsi_id }}',
            kota_id: '{{ $konsumen->kota_id }}',
            alamat1: '{{ $konsumen->alamat1 }}',
            alamat2: '{{ $konsumen->alamat2 }}',
            kode_pos: '{{ $konsumen->kode_pos }}',
            telp_kantor: '{{ $konsumen->telp_kantor }}',
            fax: '{{ $konsumen->fax }}',
            email: '{{ $konsumen->email }}',
            status: '{{ $konsumen->status }}'
        };

        // Initialize konsumen manager untuk halaman edit
        window.konsumenManager = new KonsumenManager();

        window.konsumenManager.init({
            pageType: 'edit',
            originalEmail: '{{ $konsumen->email }}',
            currentKonsumenId: '{{ $konsumen->id_konsumen }}'
        });

        // Set button text untuk edit
        $('#submitBtn')
            .data('default-text', 'Update')
            .data('loading-text', 'Memperbarui...');
    });
    </script>
    @endpush
</x-layout>
