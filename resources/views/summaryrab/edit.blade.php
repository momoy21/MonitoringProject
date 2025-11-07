<x-layout title="Edit Summary RAB">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Summary RAB', 'url' => route('summaryrab.index')],
            ['name' => 'Edit Summary RAB']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Summary RAB</h4>
                <p class="mb-0">Perbarui informasi summary RAB</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('summaryrab.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="summaryRABForm" method="POST" action="{{ route('summaryrab.update', $summaryrab->idsummary) }}">
                @csrf
                @method('PUT')

                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Dasar</h6>
                    <div class="row">
                        <!-- ID Summary (Read-only) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="idsummary_display" class="form-label">ID Summary <span class="text-danger">*</span></label>
                                <div class="idsummary-display">{{ $summaryrab->idsummary }}</div>
                                <small class="text-muted">ID tidak dapat diubah</small>
                            </div>
                        </div>

                        <!-- Nomor Urut -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="norutsummary" class="form-label">Nomor Urut <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('norutsummary') is-invalid @enderror"
                                       id="norutsummary" name="norutsummary"
                                       placeholder="Masukkan nomor urut (01-99)"
                                       value="{{ old('norutsummary', $summaryrab->norutsummary) }}"
                                       maxlength="2"
                                       required>
                                <div class="invalid-feedback" id="norutsummary-error"></div>
                                <small class="text-muted">Nomor urut untuk pengurutan tampilan (2 digit)</small>
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
                                    <option value="A" {{ old('status', $summaryrab->status) == 'A' ? 'selected' : '' }}>Aktif</option>
                                    <option value="N" {{ old('status', $summaryrab->status) == 'N' ? 'selected' : '' }}>Non Aktif</option>
                                </select>
                                <div class="invalid-feedback" id="status-error">
                                    @error('status'){{ $message }}@enderror
                                </div>
                                <small class="form-text text-muted">Status opsional. Hanya summary RAB dengan status aktif yang dapat digunakan dalam sistem lain</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Keterangan Summary RAB -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="ketsummaryrab" class="form-label">Keterangan Summary RAB <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('ketsummaryrab') is-invalid @enderror"
                                          id="ketsummaryrab" name="ketsummaryrab"
                                          rows="3"
                                          placeholder="Masukkan keterangan summary RAB"
                                          maxlength="100"
                                          required>{{ old('ketsummaryrab', $summaryrab->ketsummaryrab) }}</textarea>
                                <div class="invalid-feedback" id="ketsummaryrab-error"></div>
                                <small class="text-muted">Maksimal 100 karakter</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('summaryrab.index') }}" class="btn btn-outline-secondary">
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
    <script src="{{ asset('js/summaryrab.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Initialize summary RAB manager untuk halaman edit
        window.summaryRABManager = new SummaryRABManager();

        window.summaryRABManager.init({
            pageType: 'edit',
            currentIdSummary: '{{ $summaryrab->idsummary }}'
        });

        // Set button text untuk edit
        $('#submitBtn')
            .data('default-text', 'Perbarui')
            .data('loading-text', 'Memperbarui...');

        // Store original form data
        window.originalFormData = {
            norutsummary: '{{ $summaryrab->norutsummary }}',
            ketsummaryrab: '{{ $summaryrab->ketsummaryrab }}',
            status: '{{ $summaryrab->status }}'
        };
    });
    </script>
    @endpush
</x-layout>
