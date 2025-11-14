<x-layout title="Tambah Data Peluang">
    @push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Data Peluang', 'url' => route('datapeluang.index')],
            ['name' => 'Tambah Data Peluang']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section - Consistent with Index -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Data Peluang Baru</h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah data peluang baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('datapeluang.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="dataPeluangForm" method="POST" action="{{ route('datapeluang.store') }}">
                @csrf

                <!-- Informasi Dasar -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Peluang</h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="peluang" class="form-label">Nama Peluang <span class="text-danger">*</span></label>
                            <textarea name="peluang" id="peluang" class="form-control" rows="3"
                                placeholder="Deskripsikan peluang proyek..." required></textarea>
                            <div class="invalid-feedback" id="peluang-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_konsumen" class="form-label">Konsumen <span class="text-danger">*</span></label>
                            <select name="id_konsumen" id="id_konsumen" class="form-select" required>
                                <option value="">-- Pilih Konsumen --</option>
                                @foreach($konsumen as $item)
                                    <option value="{{ $item->id_konsumen }}">{{ $item->konsumen }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="id_konsumen-error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="N" selected>New</option>
                                <option value="I">In Progress</option>
                                <option value="D">Close</option>
                                <option value="C">Cancel</option>
                            </select>
                            <div class="invalid-feedback" id="status-error"></div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Kontak -->
                <div class="form-section">
                    <h6 class="mb-3">Informasi Kontak</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kontak_person" class="form-label">Kontak Person</label>
                            <input type="text" name="kontak_person" id="kontak_person" class="form-control"
                                maxlength="100" placeholder="Nama kontak person">
                            <div class="invalid-feedback" id="kontak_person-error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="text" name="no_hp" id="no_hp" class="form-control"
                                maxlength="25" placeholder="081234567890">
                            <div class="invalid-feedback" id="no_hp-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" id="lokasi" class="form-control"
                                maxlength="100" placeholder="Lokasi proyek">
                            <div class="invalid-feedback" id="lokasi-error"></div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="form-section">
                    <h6 class="mb-3">Timeline</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tgl_peluang" class="form-label">Tanggal Peluang <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_peluang" id="tgl_peluang" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="tgl_peluang-error"></div>
                            <small class="text-muted">Ketik manual atau klik ikon kalender</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="target_peluang" class="form-label">Target Peluang <span class="text-danger">*</span></label>
                            <input type="date" name="target_peluang" id="target_peluang" class="form-control" required>
                            <div class="invalid-feedback" id="target_peluang-error"></div>
                            <small class="text-muted">Target tidak boleh sebelum tanggal peluang</small>
                        </div>
                    </div>
                </div>

                <!-- Nilai -->
                <div class="form-section">
                    <h6 class="mb-3">Nilai Peluang</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="biaya_peluang" class="form-label">Estimasi Biaya</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="biaya_peluang" id="biaya_peluang" class="form-control"
                                    placeholder="0">
                            </div>
                            <div class="invalid-feedback" id="biaya_peluang-error"></div>
                            <small class="text-muted">Estimasi biaya pelaksanaan proyek</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pagu_peluang" class="form-label">Target Nilai</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="pagu_peluang" id="pagu_peluang" class="form-control"
                                    placeholder="0">
                            </div>
                            <div class="invalid-feedback" id="pagu_peluang-error"></div>
                            <small class="text-muted">Target nilai kontrak yang diharapkan</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('datapeluang.index') }}" class="btn btn-outline-secondary">
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
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Searchable Select Handler -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>

    <script>
        // Set Laravel routes for JavaScript
        window.Laravel = window.Laravel || {};
        window.Laravel.routes = {
            dataPeluangIndex: '{{ route("datapeluang.index") }}'
        };
    </script>
    <script src="{{ asset('js/datapeluang.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Set tanggal hari ini dalam format dd/mm/yyyy
        const today = new Date();
        const todayFormatted = today.getDate().toString().padStart(2, '0') + '/' +
                              (today.getMonth() + 1).toString().padStart(2, '0') + '/' +
                              today.getFullYear();
        $('#tgl_peluang').val(todayFormatted);

        // Initialize data peluang manager untuk halaman create
        window.dataPeluangManager = new DataPeluangManager();

        window.dataPeluangManager.init({
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
