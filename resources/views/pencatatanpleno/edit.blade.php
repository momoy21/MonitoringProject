<x-layout title="Pencatatan Hasil Pleno RAB">
    @push('styles')
    <style>
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-card .info-label {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        .info-card .info-value {
            font-size: 1.1rem;
            font-weight: bold;
        }
        .form-section {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .read-only-field {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .file-upload-box {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
            cursor: pointer;
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
        .existing-file {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Pencatatan Pleno RAB', 'url' => route('pencatatanpleno.index')],
            ['name' => 'Form Pencatatan']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Pencatatan Hasil Pleno RAB</h4>
                <p class="mb-0">Lengkapi form di bawah untuk mencatat hasil pleno RAB</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('pencatatanpleno.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <x-flash-messages />

    <!-- Info Card - Data Pengajuan RAB -->
    <div class="info-card">
        <div class="row">
            <div class="col-md-3">
                <div class="info-label">No Pengajuan</div>
                <div class="info-value">{{ $rabProyek->nopengajuan }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Tanggal Pengajuan</div>
                <div class="info-value">{{ $rabProyek->tgl_input_formatted }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Cost Center</div>
                <div class="info-value">{{ $rabProyek->cost_center }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Status</div>
                <div class="info-value">{!! $rabProyek->status_badge !!}</div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="pencatatanPlenoForm" method="POST" action="{{ route('pencatatanpleno.update', $rabProyek->nopengajuan) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Informasi Proyek -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-folder me-2"></i>Informasi Proyek</h6>
                    <div class="row">
                        <!-- Dokumen IO -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dokumen_io" class="form-label">Dokumen IO</label>
                                <input type="text" class="form-control @error('dokumen_io') is-invalid @enderror"
                                       id="dokumen_io" name="dokumen_io"
                                       value="{{ old('dokumen_io', $rabProyek->dokumen_io) }}"
                                       placeholder="Masukkan Dokumen IO" maxlength="9">
                                @error('dokumen_io')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Cost Center -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cost_center" class="form-label">Cost Center <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cost_center') is-invalid @enderror"
                                       id="cost_center" name="cost_center"
                                       value="{{ old('cost_center', $rabProyek->cost_center) }}"
                                       placeholder="Masukkan Cost Center" maxlength="9" required>
                                @error('cost_center')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nama Proyek -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nama_project" class="form-label">Nama Proyek <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_project') is-invalid @enderror"
                                       id="nama_project" name="nama_project"
                                       value="{{ old('nama_project', $rabProyek->nama_project) }}"
                                       placeholder="Masukkan Nama Proyek" required>
                                @error('nama_project')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Konsumen -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_konsumen" class="form-label">Konsumen <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_konsumen') is-invalid @enderror"
                                        id="id_konsumen" name="id_konsumen" required>
                                    <option value="">-- Pilih Konsumen --</option>
                                    @foreach($konsumenList as $konsumen)
                                        <option value="{{ $konsumen->id_konsumen }}" {{ old('id_konsumen', $rabProyek->id_konsumen) == $konsumen->id_konsumen ? 'selected' : '' }}>
                                            {{ $konsumen->konsumen }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_konsumen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Bidang Jasa -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_bidjasa" class="form-label">Bidang Jasa <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_bidjasa') is-invalid @enderror"
                                        id="id_bidjasa" name="id_bidjasa" required>
                                    <option value="">-- Pilih Bidang Jasa --</option>
                                    @foreach($bidangJasaList as $bidangJasa)
                                        <option value="{{ $bidangJasa->id_bidjasa }}" {{ old('id_bidjasa', $rabProyek->id_bidjasa) == $bidangJasa->id_bidjasa ? 'selected' : '' }}>
                                            {{ $bidangJasa->desc_bidjasa }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_bidjasa')
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
                                    @foreach($divisiList as $divisi)
                                        <option value="{{ $divisi->kode_divisi }}" {{ old('divisi', $rabProyek->divisi) == $divisi->kode_divisi ? 'selected' : '' }}>
                                            {{ $divisi->nama_divisi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('divisi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- PM -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="pm" class="form-label">Project Manager</label>
                                <input type="text" class="form-control @error('pm') is-invalid @enderror"
                                       id="pm" name="pm"
                                       value="{{ old('pm', $rabProyek->pm) }}"
                                       placeholder="Nama Project Manager" maxlength="100">
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
                                    <input type="text" class="form-control @error('nilai_proyek') is-invalid @enderror"
                                           id="nilai_proyek" name="nilai_proyek"
                                           value="{{ old('nilai_proyek', $rabProyek->nilai_proyek ? number_format($rabProyek->nilai_proyek, 0, ',', '.') : '') }}"
                                           placeholder="0">
                                    @error('nilai_proyek')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pencatatan Pleno -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-edit me-2"></i>Pencatatan Hasil Pleno</h6>
                    <div class="row">
                        <!-- Progress -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="progress" class="form-label">Progress <span class="text-danger">*</span></label>
                                <select class="form-select @error('progress') is-invalid @enderror"
                                        id="progress" name="progress" required>
                                    <option value="">-- Pilih Progress --</option>
                                    @foreach($progressOptions as $key => $value)
                                        <option value="{{ $key }}" {{ old('progress', $rabProyek->progress) == $key ? 'selected' : '' }}>
                                            [{{ $key }}] {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('progress')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <select class="form-select @error('keterangan') is-invalid @enderror"
                                        id="keterangan" name="keterangan">
                                    <option value="">-- Pilih Keterangan --</option>
                                    @foreach($keteranganOptions as $key => $value)
                                        <option value="{{ $key }}" {{ old('keterangan', $rabProyek->keterangan) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Hasil Pleno -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="hasil_pleno" class="form-label">Hasil Pleno</label>
                                <select class="form-select @error('hasil_pleno') is-invalid @enderror"
                                        id="hasil_pleno" name="hasil_pleno">
                                    <option value="">-- Pilih Hasil Pleno --</option>
                                    @foreach($hasilPlenoOptions as $key => $value)
                                        <option value="{{ $key }}" {{ old('hasil_pleno', $rabProyek->hasil_pleno) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hasil_pleno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Margin RKAP -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="margin_rkap" class="form-label">Margin RKAP (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                           class="form-control @error('margin_rkap') is-invalid @enderror"
                                           id="margin_rkap" name="margin_rkap" 
                                           value="{{ old('margin_rkap', $rabProyek->margin_rkap) }}"
                                           placeholder="0.00">
                                    <span class="input-group-text">%</span>
                                    @error('margin_rkap')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Margin Pleno -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="margin_pleno" class="form-label">Margin Pleno (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                           class="form-control @error('margin_pleno') is-invalid @enderror"
                                           id="margin_pleno" name="margin_pleno" 
                                           value="{{ old('margin_pleno', $rabProyek->margin_pleno) }}"
                                           placeholder="0.00">
                                    <span class="input-group-text">%</span>
                                    @error('margin_pleno')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status">
                                    <option value="">-- Pilih Status --</option>
                                    @foreach($statusOptions as $key => $value)
                                        <option value="{{ $key }}" {{ old('status', $rabProyek->status) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan Pleno</label>
                                <textarea class="form-control @error('catatan') is-invalid @enderror"
                                          id="catatan" name="catatan" rows="3"
                                          placeholder="Tuliskan catatan hasil evaluasi atau keputusan pleno...">{{ old('catatan', $rabProyek->catatan) }}</textarea>
                                @error('catatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maksimal 500 karakter</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen RAB Final -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-upload me-2"></i>Upload Dokumen RAB Final</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hasil_upload" class="form-label">
                                    Dokumen RAB Final (PDF)
                                    <span class="text-danger" id="uploadRequired" style="display: none;">*</span>
                                </label>
                                <div class="file-upload-box" id="hasilUploadBox">
                                    <i class="bx bxs-file-pdf" style="font-size: 32px; color: #dc3545;"></i>
                                    <p class="mb-1">Klik atau drag & drop file</p>
                                    <small class="text-muted">Format: .pdf (max 10MB)</small>
                                    <input type="file" class="d-none" id="hasil_upload" name="hasil_upload" accept=".pdf">
                                </div>
                                <div class="file-info d-none" id="hasilFileInfo">
                                    <span id="hasilFileName"></span>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile()">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                                @if($rabProyek->hasil_upload)
                                    <div class="existing-file" id="existingFile">
                                        <i class="bx bxs-file-pdf text-danger" style="font-size: 24px;"></i>
                                        <div>
                                            <strong>File tersimpan:</strong><br>
                                            <a href="{{ Storage::url($rabProyek->hasil_upload) }}" target="_blank" class="text-primary">
                                                {{ basename($rabProyek->hasil_upload) }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @error('hasil_upload')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Wajib diunggah jika Progress = Done
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('pencatatanpleno.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                        <i class="bx bx-check me-1" id="submitIcon"></i>
                        <span id="submitText">Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        // File upload handlers
        const box = document.getElementById('hasilUploadBox');
        const input = document.getElementById('hasil_upload');
        const info = document.getElementById('hasilFileInfo');
        const nameSpan = document.getElementById('hasilFileName');

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
                showFileInfo(input.files[0]);
            }
        });

        // File selected
        input.addEventListener('change', () => {
            if (input.files.length) {
                showFileInfo(input.files[0]);
            }
        });

        // Progress change - show required indicator for file upload
        $('#progress').on('change', function() {
            if ($(this).val() === '04') {
                $('#uploadRequired').show();
            } else {
                $('#uploadRequired').hide();
            }
        });

        // Trigger on load
        $('#progress').trigger('change');

        // Format nilai proyek with thousand separators
        $('#nilai_proyek').on('input', function() {
            let value = $(this).val().replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
            }
            $(this).val(value);
        });

        // Form submit - clean number format before submitting
        $('#pencatatanPlenoForm').on('submit', function() {
            // Clean nilai_proyek
            let nilaiProyek = $('#nilai_proyek').val();
            if (nilaiProyek) {
                $('#nilai_proyek').val(nilaiProyek.replace(/\./g, ''));
            }

            $('#submitSpinner').removeClass('d-none');
            $('#submitIcon').addClass('d-none');
            $('#submitText').text('Menyimpan...');
            $('#submitBtn').prop('disabled', true);
        });
    });

    function showFileInfo(file) {
        document.getElementById('hasilFileName').textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        document.getElementById('hasilFileInfo').classList.remove('d-none');
        document.getElementById('hasilUploadBox').style.display = 'none';
        
        // Hide existing file info if any
        const existingFile = document.getElementById('existingFile');
        if (existingFile) {
            existingFile.style.display = 'none';
        }
    }

    function removeFile() {
        document.getElementById('hasil_upload').value = '';
        document.getElementById('hasilFileInfo').classList.add('d-none');
        document.getElementById('hasilUploadBox').style.display = 'block';
        
        // Show existing file info if any
        const existingFile = document.getElementById('existingFile');
        if (existingFile) {
            existingFile.style.display = 'flex';
        }
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
