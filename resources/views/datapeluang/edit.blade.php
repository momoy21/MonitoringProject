<x-layout title="Edit Data Peluang {{ $datapeluang->peluang }}">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Data Peluang', 'url' => route('datapeluang.index')],
            ['name' => 'Edit Data Peluang']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Data Peluang</h4>
                <p class="mb-0">Perbarui informasi peluang {{ $datapeluang->peluang }}</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('datapeluang.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <form id="dataPeluangForm" method="POST" action="{{ route('datapeluang.update', $datapeluang->id_datapeluang) }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">
                        <!-- ID Peluang Display -->
                        <div class="form-section">
                            <h6 class="mb-3">ID Peluang</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="konsumen-id-display">
                                        {{ $datapeluang->id_datapeluang }}
                                    </div>
                                    <small class="text-muted">ID tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Peluang -->
                        <div class="form-section">
                            <h6 class="mb-3">Informasi Peluang</h6>
                            <div class="row">
                                <x-form-field
                                    name="peluang"
                                    label="Nama Peluang"
                                    type="textarea"
                                    :value="$datapeluang->peluang"
                                    :required="true"
                                    placeholder="Masukkan nama/deskripsi peluang bisnis"
                                    class="col-md-12"
                                    rows="3" />
                            </div>
                            <div class="row">
                                <x-form-field
                                    name="id_konsumen"
                                    label="Konsumen"
                                    type="select"
                                    :value="$datapeluang->id_konsumen"
                                    :required="true"
                                    :options="$konsumen->pluck('konsumen', 'id_konsumen')"
                                    placeholder="-- Pilih Konsumen --" />

                                <x-form-field
                                    name="status"
                                    label="Status"
                                    type="select"
                                    :value="$datapeluang->status"
                                    :required="true"
                                    :options="[
                                        'N' => 'New',
                                        'I' => 'In Progress',
                                        'D' => 'Close',
                                        'C' => 'Cancel'
                                    ]"
                                    placeholder="-- Pilih Status --" />
                            </div>
                        </div>

                        <!-- Informasi Kontak -->
                        <div class="form-section">
                            <h6 class="mb-3">Informasi Kontak & Lokasi</h6>
                            <div class="row">
                                <x-form-field
                                    name="kontak_person"
                                    label="Kontak Person"
                                    :value="$datapeluang->kontak_person"
                                    maxlength="100"
                                    placeholder="Nama kontak person" />

                                <x-form-field
                                    name="no_hp"
                                    label="No. HP"
                                    :value="$datapeluang->no_hp"
                                    maxlength="25"
                                    placeholder="081234567890" />
                            </div>
                            <div class="row">
                                <x-form-field
                                    name="lokasi"
                                    label="Lokasi"
                                    :value="$datapeluang->lokasi"
                                    maxlength="100"
                                    placeholder="Lokasi proyek/peluang"
                                    class="col-md-12" />
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="form-section">
                            <h6 class="mb-3">Timeline</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tgl_peluang" class="form-label">Tanggal Peluang <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl_peluang" id="tgl_peluang" class="form-control"
                                        value="{{ $datapeluang->tgl_peluang->format('Y-m-d') }}" required>
                                    <div class="invalid-feedback" id="tgl_peluang-error"></div>
                                    <small class="text-muted">Ketik manual atau klik ikon kalender</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="target_peluang" class="form-label">Target Peluang <span class="text-danger">*</span></label>
                                    <input type="date" name="target_peluang" id="target_peluang" class="form-control"
                                        value="{{ $datapeluang->target_peluang->format('Y-m-d') }}" required>
                                    <div class="invalid-feedback" id="target_peluang-error"></div>
                                    <small class="text-muted">Target tidak boleh sebelum tanggal peluang</small>
                                </div>
                            </div>
                        </div>

                        <!-- Nilai -->
                        <div class="form-section">
                            <h6 class="mb-3">Estimasi Nilai</h6>
                            <div class="row">
                                <x-form-field
                                    name="biaya_peluang"
                                    label="Estimasi Biaya Peluang"
                                    :value="$datapeluang->biaya_peluang ? number_format($datapeluang->biaya_peluang, 0, ',', '.') : ''"
                                    placeholder="0"
                                    class="col-md-6"
                                    inputGroupText="Rp" />

                                <x-form-field
                                    name="pagu_peluang"
                                    label="Target Nilai Peluang"
                                    :value="$datapeluang->pagu_peluang ? number_format($datapeluang->pagu_peluang, 0, ',', '.') : ''"
                                    placeholder="0"
                                    class="col-md-6"
                                    inputGroupText="Rp" />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('datapeluang.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-1"></i> Batal
                            </a>
                            <button type="button" class="btn btn-outline-warning" onclick="window.resetDataPeluangForm()">
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
    <script src="{{ asset('js/datapeluang.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Set data original untuk reset function
        window.originalDataPeluangFormData = {
            peluang: '{{ $datapeluang->peluang }}',
            id_konsumen: '{{ $datapeluang->id_konsumen }}',
            kontak_person: '{{ $datapeluang->kontak_person }}',
            no_hp: '{{ $datapeluang->no_hp }}',
            lokasi: '{{ $datapeluang->lokasi }}',
            tgl_peluang: '{{ $datapeluang->tgl_peluang->format('Y-m-d') }}',
            target_peluang: '{{ $datapeluang->target_peluang->format('Y-m-d') }}',
            biaya_peluang: {{ $datapeluang->biaya_peluang ?? 0 }},
            pagu_peluang: {{ $datapeluang->pagu_peluang ?? 0 }},
            status: '{{ $datapeluang->status }}'
        };

        // Initialize data peluang manager untuk halaman edit
        window.dataPeluangManager = new DataPeluangManager();

        window.dataPeluangManager.init({
            pageType: 'edit',
            currentDataPeluangId: '{{ $datapeluang->id_datapeluang }}'
        });

        // Set button text untuk edit
        $('#submitBtn')
            .data('default-text', 'Update')
            .data('loading-text', 'Memperbarui...');
    });
    </script>
    @endpush
</x-layout>
