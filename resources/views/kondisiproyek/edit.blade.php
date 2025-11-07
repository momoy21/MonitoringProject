<x-layout title="Edit Kondisi Proyek {{ $kondisiproyek->desc_kondisi_proyek }}">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Kondisi Proyek', 'url' => route('kondisiproyek.index')],
            ['name' => 'Edit Kondisi Proyek']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header -->
     <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Kondisi Proyek</h4>
                <p class="mb-0">Perbarui informasi kondisi proyek {{ $kondisiproyek->desc_kondisi_proyek }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('kondisiproyek.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <form id="kondisiProyekForm" method="POST" action="{{ route('kondisiproyek.update', $kondisiproyek->id_kondisi_proyek) }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">
                        <!-- ID Kondisi Proyek Display -->
                        <div class="form-section">
                            <h6 class="mb-3">ID Kondisi Proyek</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="kondisi-proyek-id-display">
                                        {{ $kondisiproyek->id_kondisi_proyek }}
                                    </div>
                                    <small class="text-muted">ID tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Dasar -->
                        <div class="form-section">
                            <h6 class="mb-3">Informasi Kondisi Proyek</h6>
                            <div class="row">
                                <x-form-field
                                    name="desc_kondisi_proyek"
                                    label="Deskripsi Kondisi Proyek"
                                    :value="$kondisiproyek->desc_kondisi_proyek"
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
                                        <option value="A" {{ $kondisiproyek->status == 'A' ? 'selected' : '' }}>Aktif</option>
                                        <option value="N" {{ $kondisiproyek->status == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('kondisiproyek.index') }}" class="btn btn-outline-secondary">
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
    <script src="{{ asset('js/kondisiproyek.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Set data original untuk reset function
        window.originalFormData = {
            desc_kondisi_proyek: '{{ $kondisiproyek->desc_kondisi_proyek }}'
        };

        // Initialize kondisi proyek manager untuk halaman edit
        window.kondisiProyekManager = new KondisiProyekManager();

        window.kondisiProyekManager.init({
            pageType: 'edit',
            currentKondisiProyekId: '{{ $kondisiproyek->id_kondisi_proyek }}'
        });

        // Set button text untuk edit
        $('#submitBtn')
            .data('default-text', 'Update')
            .data('loading-text', 'Memperbarui...');
    });
    </script>
    @endpush
</x-layout>
