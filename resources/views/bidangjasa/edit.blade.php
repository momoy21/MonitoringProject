<x-layout title="Edit Bidang Jasa {{ $bidangjasa->desc_bidjasa }}">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Bidang Jasa', 'url' => route('bidangjasa.index')],
            ['name' => 'Edit Bidang Jasa']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header -->
     <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Bidang Jasa</h4>
                <p class="mb-0">Perbarui informasi bidang jasa {{ $bidangjasa->desc_bidjasa }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('bidangjasa.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.bidangJasa) window.StateManagers.bidangJasa.markForRestore();">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <form id="bidangJasaForm" method="POST" action="{{ route('bidangjasa.update', $bidangjasa->id_bidjasa) }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">
                        <!-- ID Bidang Jasa Display -->
                        <div class="form-section">
                            <h6 class="mb-3">ID Bidang Jasa</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="bidangjasa-id-display">
                                        {{ $bidangjasa->id_bidjasa }}
                                    </div>
                                    <small class="text-muted">ID tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Dasar -->
                        <div class="form-section">
                            <h6 class="mb-3">Informasi Bidang Jasa</h6>
                            <div class="row">
                                <x-form-field
                                    name="desc_bidjasa"
                                    label="Deskripsi Bidang Jasa"
                                    :value="$bidangjasa->desc_bidjasa"
                                    :required="true"
                                    maxlength="50"
                                    placeholder="Masukkan deskripsi bidang jasa"
                                    class="col-md-12" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-section">
                            <h6 class="mb-3">Status</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="status" class="form-select" required>
                                        <option value="A" {{ $bidangjasa->status == 'A' ? 'selected' : '' }}>Aktif</option>
                                        <option value="N" {{ $bidangjasa->status == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('bidangjasa.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.bidangJasa) window.StateManagers.bidangJasa.markForRestore();">
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
    <script src="{{ asset('js/bidangjasa.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        // Set data original untuk reset function
        window.originalFormData = {
            desc_bidjasa: '{{ $bidangjasa->desc_bidjasa }}'
        };

        // Initialize bidang jasa manager untuk halaman edit
        window.bidangJasaManager = new BidangJasaManager();

        window.bidangJasaManager.init({
            pageType: 'edit',
            currentBidangJasaId: '{{ $bidangjasa->id_bidjasa }}'
        });

        // Set button text untuk edit
        $('#submitBtn')
            .data('default-text', 'Update')
            .data('loading-text', 'Memperbarui...');
    });
    </script>
    @endpush
</x-layout>
