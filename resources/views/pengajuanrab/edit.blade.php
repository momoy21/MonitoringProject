<x-layout title="Edit Pengajuan RAB">
    @push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .file-upload-box {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }
        .file-upload-box:hover {
            border-color: #696cff;
            background-color: #f0f0ff;
        }
        .file-upload-box.dragover {
            border-color: #696cff;
            background-color: #e8e8ff;
        }
        .file-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin-top: 10px;
        }
        .id-display {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 1.1rem;
            font-weight: bold;
            color: #696cff;
        }
        .existing-file {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .existing-file i {
            font-size: 24px;
        }
    </style>
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Pengajuan RAB', 'url' => route('pengajuanrab.index')],
            ['name' => 'Edit Pengajuan RAB']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Pengajuan RAB</h4>
                <p class="mb-0">Perbarui data pengajuan Rencana Anggaran Biaya</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('pengajuanrab.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <x-flash-messages />

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="pengajuanRabForm" method="POST" action="{{ route('pengajuanrab.update', $pengajuanrab->nopengajuan) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Informasi Pengajuan (Read Only) -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-id-card me-2"></i>Informasi Pengajuan</h6>
                    <div class="row">
                        <!-- Tanggal Input (Read Only) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Pengajuan</label>
                                <div class="id-display">
                                    {{ $pengajuanrab->tgl_input_formatted }}
                                </div>
                                <small class="text-muted">Tidak dapat diubah</small>
                            </div>
                        </div>

                        <!-- No Pengajuan (Read Only) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Nomor Pengajuan</label>
                                <div class="id-display">
                                    {{ $pengajuanrab->nopengajuan }}
                                </div>
                                <small class="text-muted">Tidak dapat diubah</small>
                            </div>
                        </div>

                        <!-- Status (Read Only) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="id-display">
                                    {!! $pengajuanrab->status_badge !!}
                                </div>
                                <small class="text-muted">Tidak dapat diubah</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Dokumen IO -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dokumen_io" class="form-label">Dokumen IO</label>
                                <input type="text" class="form-control @error('dokumen_io') is-invalid @enderror"
                                       id="dokumen_io" name="dokumen_io" value="{{ old('dokumen_io', $pengajuanrab->dokumen_io) }}"
                                       placeholder="Masukkan nomor dokumen IO" maxlength="9">
                                @error('dokumen_io')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Proyek -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-folder me-2"></i>Informasi Proyek</h6>
                    <div class="row">
                        <!-- Cost Center -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cost_center" class="form-label">Cost Center <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cost_center') is-invalid @enderror"
                                       id="cost_center" name="cost_center" value="{{ old('cost_center', $pengajuanrab->cost_center) }}"
                                       placeholder="Masukkan cost center" maxlength="9" required>
                                @error('cost_center')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nama Proyek -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="nama_project" class="form-label">Nama Proyek <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('nama_project') is-invalid @enderror"
                                          id="nama_project" name="nama_project" rows="2"
                                          placeholder="Masukkan nama proyek" required>{{ old('nama_project', $pengajuanrab->nama_project) }}</textarea>
                                @error('nama_project')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Divisi -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select @error('divisi') is-invalid @enderror"
                                        id="divisi" name="divisi">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach($divisi as $d)
                                        <option value="{{ $d->kode_divisi }}" {{ old('divisi', $pengajuanrab->divisi) == $d->kode_divisi ? 'selected' : '' }}>
                                            {{ $d->nama_divisi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('divisi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Jenis Proyek -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="jenis_proyek" class="form-label">Jenis Proyek</label>
                                <select class="form-select @error('jenis_proyek') is-invalid @enderror"
                                        id="jenis_proyek" name="jenis_proyek">
                                    <option value="">-- Pilih Jenis Proyek --</option>
                                    @foreach($jenisProyek as $jp)
                                        <option value="{{ $jp->kode_jenis }}" {{ old('jenis_proyek', $pengajuanrab->jenis_proyek) == $jp->kode_jenis ? 'selected' : '' }}>
                                            [{{ $jp->kode_jenis }}] {{ $jp->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jenis_proyek')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Project Manager -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="pm" class="form-label">Project Manager</label>
                                <input type="text" class="form-control @error('pm') is-invalid @enderror"
                                       id="pm" name="pm" value="{{ old('pm', $pengajuanrab->pm) }}"
                                       placeholder="Masukkan nama project manager" maxlength="100">
                                @error('pm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nilai Proyek -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nilai_proyek" class="form-label">Nilai Proyek</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control currency-input @error('nilai_proyek') is-invalid @enderror"
                                           id="nilai_proyek" name="nilai_proyek" 
                                           value="{{ old('nilai_proyek', $pengajuanrab->nilai_proyek ? number_format($pengajuanrab->nilai_proyek, 0, ',', '.') : '') }}"
                                           placeholder="0">
                                    @error('nilai_proyek')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Konsumen & Bidang Jasa -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-buildings me-2"></i>Informasi Konsumen & Bidang Jasa</h6>
                    <div class="row">
                        <!-- Konsumen -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_konsumen" class="form-label">Konsumen <span class="text-danger">*</span></label>
                                <select class="form-select select2 @error('id_konsumen') is-invalid @enderror"
                                        id="id_konsumen" name="id_konsumen" required>
                                    <option value="">-- Pilih Konsumen --</option>
                                    @foreach($konsumen as $k)
                                        <option value="{{ $k->id_konsumen }}" {{ old('id_konsumen', $pengajuanrab->id_konsumen) == $k->id_konsumen ? 'selected' : '' }}>
                                            {{ $k->konsumen }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_konsumen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Bidang Jasa -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_bidjasa" class="form-label">Bidang Jasa <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_bidjasa') is-invalid @enderror"
                                        id="id_bidjasa" name="id_bidjasa" required>
                                    <option value="">-- Pilih Bidang Jasa --</option>
                                    @foreach($bidangJasa as $bj)
                                        <option value="{{ $bj->id_bidjasa }}" {{ old('id_bidjasa', $pengajuanrab->id_bidjasa) == $bj->id_bidjasa ? 'selected' : '' }}>
                                            {{ $bj->desc_bidjasa }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_bidjasa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan & Progress RAB (Read Only - Edit via Pencatatan Pleno) -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-info-circle me-2"></i>Keterangan & Progress RAB <small class="text-muted">(Edit melalui Pencatatan Pleno RAB)</small></h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <div class="id-display">
                                    @php
                                        $keteranganOptions = ['P' => 'Pleno', 'T' => 'Tidak Pleno', 'R' => 'Revisi RAB'];
                                    @endphp
                                    {{ $keteranganOptions[$pengajuanrab->keterangan] ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Progress</label>
                                <div class="id-display">
                                    {!! $pengajuanrab->progress_badge !!}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Hasil Pleno</label>
                                <div class="id-display">
                                    {!! $pengajuanrab->hasil_pleno_badge !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Margin & Catatan (Read Only - Edit via Pencatatan Pleno) -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-calculator me-2"></i>Margin & Catatan <small class="text-muted">(Edit melalui Pencatatan Pleno RAB)</small></h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Margin RKAP (%)</label>
                                <div class="id-display">
                                    {{ $pengajuanrab->margin_rkap ? number_format($pengajuanrab->margin_rkap, 2) . '%' : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Margin Pleno (%)</label>
                                <div class="id-display">
                                    {{ $pengajuanrab->margin_pleno ? number_format($pengajuanrab->margin_pleno, 2) . '%' : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Catatan Hasil Pleno</label>
                                <div class="id-display" style="min-height: 80px;">
                                    {{ $pengajuanrab->catatan ?: '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-upload me-2"></i>Upload Dokumen Pendukung</h6>
                    <div class="row">
                        <!-- Upload RAB -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rab_upload" class="form-label">Dokumen RAB (Excel)</label>
                                @if($pengajuanrab->rab_upload)
                                    <div class="existing-file">
                                        <i class="bx bx-spreadsheet text-success"></i>
                                        <div class="flex-grow-1">
                                            <small>File tersimpan:</small>
                                            <div>{{ basename($pengajuanrab->rab_upload) }}</div>
                                        </div>
                                        <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'rab']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                @endif
                                <div class="file-upload-box" id="rabUploadBox">
                                    <i class="bx bx-spreadsheet" style="font-size: 32px; color: #28a745;"></i>
                                    <p class="mb-1">{{ $pengajuanrab->rab_upload ? 'Ganti file' : 'Klik atau drag & drop file' }}</p>
                                    <small class="text-muted">Format: .xlsx, .xls (max 10MB)</small>
                                    <input type="file" class="d-none" id="rab_upload" name="rab_upload" accept=".xlsx,.xls">
                                </div>
                                <div class="file-info d-none" id="rabFileInfo">
                                    <span id="rabFileName"></span>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile('rab')">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Kontrak -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="file_upload" class="form-label">Dokumen JO/PO/SPK/Kontrak (PDF)</label>
                                @if($pengajuanrab->file_upload)
                                    <div class="existing-file">
                                        <i class="bx bxs-file-pdf text-danger"></i>
                                        <div class="flex-grow-1">
                                            <small>File tersimpan:</small>
                                            <div>{{ basename($pengajuanrab->file_upload) }}</div>
                                        </div>
                                        <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'kontrak']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                @endif
                                <div class="file-upload-box" id="kontrakUploadBox">
                                    <i class="bx bxs-file-pdf" style="font-size: 32px; color: #dc3545;"></i>
                                    <p class="mb-1">{{ $pengajuanrab->file_upload ? 'Ganti file' : 'Klik atau drag & drop file' }}</p>
                                    <small class="text-muted">Format: .pdf (max 10MB)</small>
                                    <input type="file" class="d-none" id="file_upload" name="file_upload" accept=".pdf">
                                </div>
                                <div class="file-info d-none" id="kontrakFileInfo">
                                    <span id="kontrakFileName"></span>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile('kontrak')">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Peta Risiko -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="peta_risk_upload" class="form-label">Dokumen Peta Risiko (Excel)</label>
                                @if($pengajuanrab->peta_risk_upload)
                                    <div class="existing-file">
                                        <i class="bx bxs-file text-success"></i>
                                        <div class="flex-grow-1">
                                            <small>File tersimpan:</small>
                                            <div>{{ basename($pengajuanrab->peta_risk_upload) }}</div>
                                        </div>
                                        <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'peta_risiko']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                @endif
                                <div class="file-upload-box" id="petaRiskUploadBox">
                                    <i class="bx bxs-file" style="font-size: 32px; color: #28a745;"></i>
                                    <p class="mb-1">{{ $pengajuanrab->peta_risk_upload ? 'Ganti file' : 'Klik atau drag & drop file' }}</p>
                                    <small class="text-muted">Format: .xlsx, .xls (max 10MB)</small>
                                    <input type="file" class="d-none" id="peta_risk_upload" name="peta_risk_upload" accept=".xlsx,.xls">
                                </div>
                                <div class="file-info d-none" id="petaRiskFileInfo">
                                    <span id="petaRiskFileName"></span>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile('petaRisk')">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Hasil Pleno -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hasil_upload" class="form-label">Dokumen Hasil Pleno (PDF)</label>
                                @if($pengajuanrab->hasil_upload)
                                    <div class="existing-file">
                                        <i class="bx bxs-file-pdf text-danger"></i>
                                        <div class="flex-grow-1">
                                            <small>File tersimpan:</small>
                                            <div>{{ basename($pengajuanrab->hasil_upload) }}</div>
                                        </div>
                                        <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'hasil']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                @endif
                                <div class="file-upload-box" id="hasilUploadBox">
                                    <i class="bx bxs-file-pdf" style="font-size: 32px; color: #dc3545;"></i>
                                    <p class="mb-1">{{ $pengajuanrab->hasil_upload ? 'Ganti file' : 'Klik atau drag & drop file' }}</p>
                                    <small class="text-muted">Format: .pdf (max 10MB)</small>
                                    <input type="file" class="d-none" id="hasil_upload" name="hasil_upload" accept=".pdf">
                                </div>
                                <div class="file-info d-none" id="hasilFileInfo">
                                    <span id="hasilFileName"></span>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile('hasil')">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('pengajuanrab.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                        <i class="bx bx-check me-1" id="submitIcon"></i>
                        <span id="submitText">Perbarui</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/pengajuanrab.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih --',
            allowClear: true
        });

        // Initialize pengajuan RAB manager
        window.pengajuanRabManager = new PengajuanRABManager();
        window.pengajuanRabManager.init({
            pageType: 'edit'
        });

        // Currency input formatting
        $('.currency-input').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
            }
            $(this).val(value);
        });

        // File upload handlers
        setupFileUpload('rab', 'rabUploadBox', 'rab_upload', 'rabFileInfo', 'rabFileName');
        setupFileUpload('kontrak', 'kontrakUploadBox', 'file_upload', 'kontrakFileInfo', 'kontrakFileName');
        setupFileUpload('petaRisk', 'petaRiskUploadBox', 'peta_risk_upload', 'petaRiskFileInfo', 'petaRiskFileName');
        setupFileUpload('hasil', 'hasilUploadBox', 'hasil_upload', 'hasilFileInfo', 'hasilFileName');
    });

    function setupFileUpload(type, boxId, inputId, infoId, nameId) {
        const box = document.getElementById(boxId);
        const input = document.getElementById(inputId);

        if (!box || !input) return;

        // Click to upload
        box.addEventListener('click', () => input.click());

        // Drag and drop
        box.addEventListener('dragover', (e) => {
            e.preventDefault();
            box.classList.add('dragover');
        });

        box.addEventListener('dragleave', () => {
            box.classList.remove('dragover');
        });

        box.addEventListener('drop', (e) => {
            e.preventDefault();
            box.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showFileInfo(type, input.files[0]);
            }
        });

        // File selected
        input.addEventListener('change', () => {
            if (input.files.length) {
                showFileInfo(type, input.files[0]);
            }
        });
    }

    function showFileInfo(type, file) {
        const infoId = type + 'FileInfo';
        const nameId = type + 'FileName';
        const boxId = type + 'UploadBox';

        document.getElementById(nameId).textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        document.getElementById(infoId).classList.remove('d-none');
        document.getElementById(boxId).style.display = 'none';
    }

    function removeFile(type) {
        const inputMap = {
            'rab': 'rab_upload',
            'kontrak': 'file_upload',
            'petaRisk': 'peta_risk_upload',
            'hasil': 'hasil_upload'
        };
        const boxMap = {
            'rab': 'rabUploadBox',
            'kontrak': 'kontrakUploadBox',
            'petaRisk': 'petaRiskUploadBox',
            'hasil': 'hasilUploadBox'
        };

        const input = document.getElementById(inputMap[type]);
        const info = document.getElementById(type + 'FileInfo');
        const box = document.getElementById(boxMap[type]);

        if (input) input.value = '';
        if (info) info.classList.add('d-none');
        if (box) box.style.display = 'block';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    </script>
    @endpush
</x-layout>
