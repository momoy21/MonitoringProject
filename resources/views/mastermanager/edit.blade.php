<x-layout title="Edit Manager {{ $manager->nama }}">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Master Manager', 'url' => route('mastermanager.index')],
            ['name' => 'Edit Manager']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Manager</h4>
                <p class="mb-0">Perbarui informasi manager {{ $manager->nama }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('mastermanager.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.masterManager) window.StateManagers.masterManager.markForRestore();">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <form id="managerForm" method="POST" action="{{ route('mastermanager.update', $manager->nik) }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">
                        <!-- NIK Display -->
                        <div class="form-section">
                            <h6 class="mb-3">Nomor Induk Karyawan</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="manager-nik-display">
                                        {{ $manager->nik }}
                                    </div>
                                    <small class="text-muted">Nomor Induk Karyawan tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Manager -->
                        <div class="form-section">
                            <h6 class="mb-3">Informasi Manager</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Manager <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('nama') is-invalid @enderror"
                                               id="nama"
                                               name="nama"
                                               maxlength="100"
                                               placeholder="Masukkan nama manager"
                                               value="{{ old('nama', $manager->nama) }}"
                                               required>
                                        <div class="invalid-feedback" id="nama-error">
                                            @error('nama'){{ $message }}@enderror
                                        </div>
                                        <small class="form-text text-muted">Maksimal 100 karakter</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select @error('status') is-invalid @enderror"
                                                id="status"
                                                name="status">
                                            <option value="">-- Pilih Status --</option>
                                            <option value="A" {{ old('status', $manager->status) == 'A' ? 'selected' : '' }}>Aktif</option>
                                            <option value="N" {{ old('status', $manager->status) == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                        </select>
                                        <div class="invalid-feedback" id="status-error">
                                            @error('status'){{ $message }}@enderror
                                        </div>
                                        <small class="form-text text-muted">Status opsional. Hanya manager dengan status aktif yang dapat ditugaskan ke proyek</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('mastermanager.index') }}" class="btn btn-outline-secondary" onclick="if(window.StateManagers?.masterManager) window.StateManagers.masterManager.markForRestore();">
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
    <script src="{{ asset('js/manager.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        // Set data original untuk reset function
        window.originalFormData = {
            nama: '{{ $manager->nama }}',
            status: '{{ $manager->status }}'
        };

        // Initialize manager manager untuk halaman edit
        window.managerManager = new ManagerManager();

        window.managerManager.init({
            pageType: 'edit',
            originalNik: '{{ $manager->nik }}',
            currentManagerNik: '{{ $manager->nik }}'
        });

        // Set button text untuk edit
        $('#submitBtn')
            .data('default-text', 'Update')
            .data('loading-text', 'Memperbarui...');
    });
    </script>
    @endpush
</x-layout>
