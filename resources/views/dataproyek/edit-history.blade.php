<x-layout title="Edit History Proyek">
    @push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Data Proyek', 'url' => route('dataproyek.index')],
            ['name' => 'Detail Project', 'url' => route('dataproyek.show', $project->id_project)],
            ['name' => 'Edit History']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit History Proyek</h4>
                <p class="mb-0">ID Proyek: <strong>{{ $project->id_project }}</strong> | Norut: <strong>{{ $project->norut }}</strong></p>
                <span class="badge bg-info">History Proyek</span>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('dataproyek.show', $project->id_project) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <x-flash-messages />

    <!-- Debug Errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <h5 class="alert-heading"><i class="bx bx-error-circle me-2"></i>Error Validasi</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Section -->
    <div class="card edit-history-form">
        <div class="card-body">
            <form id="proyekForm" method="POST" action="{{ route('dataproyek.updateHistory', [$project->id_project, $project->norut]) }}" enctype="multipart/form-data" data-is-edit="true">
                @csrf
                @method('PUT')

                <!-- Hidden fields for auto-filled data -->
                <input type="hidden" name="cost_center" value="{{ $project->cost_center }}">
                <input type="hidden" name="id_konsumen" value="{{ $parentProject->id_konsumen }}">
                <input type="hidden" name="id_datapeluang" value="{{ $parentProject->id_datapeluang ?? '' }}">
                <input type="hidden" name="id_bidjasa" value="{{ $parentProject->id_bidjasa }}">
                <input type="hidden" name="id_kondisi_proyek" value="{{ $parentProject->id_kondisi_proyek }}">
                <input type="hidden" name="lokasi_proyek" value="{{ $parentProject->lokasi_proyek }}">
                <input type="hidden" name="jarak_lokasi" value="{{ $parentProject->jarak_lokasi }}">

                <!-- Informasi Proyek -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-folder me-2"></i>Informasi Proyek</h6>
                    <div class="row">
                        <!-- ID Project (Read Only) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_project" class="form-label">ID Project</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="id-project-display">{{ $project->id_project }}</div>
                                    <small class="text-muted">ID tidak dapat diubah</small>
                                </div>
                                <input type="hidden" name="id_project" value="{{ $project->id_project }}">
                            </div>
                        </div>

                        <!-- Dokumen IO -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dokumen_io" class="form-label">Dokumen IO</label>
                                <input type="text" class="form-control number-only @error('dokumen_io') is-invalid @enderror"
                                       id="dokumen_io" name="dokumen_io"
                                       value="{{ old('dokumen_io', $project->dokumen_io) }}"
                                       placeholder="9 digit angka" maxlength="9">
                                @error('dokumen_io')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Cost Center -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cost_center_display" class="form-label">Cost Center <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cost_center_display"
                                       value="{{ $project->cost_center }}" readonly>
                            </div>
                        </div>

                        <!-- Nama Proyek -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="namaproject" class="form-label">Nama Proyek <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('namaproject') is-invalid @enderror"
                                          id="namaproject" name="namaproject" rows="3"
                                          placeholder="Masukkan nama proyek" required>{{ old('namaproject', $project->namaproject) }}</textarea>
                                @error('namaproject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Konsumen & Bidang (Read-only) -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-buildings me-2"></i>Informasi Konsumen & Bidang</h6>
                    <div class="row">
                        <!-- Konsumen (Display Only) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="konsumen_display" class="form-label">Konsumen</label>
                                <input type="text" class="form-control" id="konsumen_display"
                                       value="{{ $parentProject->konsumen->konsumen ?? 'N/A' }}" readonly>
                            </div>
                        </div>

                        <!-- Data Peluang (From Parent) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="data_peluang_display" class="form-label">Data Peluang</label>
                                <input type="text" class="form-control" id="data_peluang_display"
                                       value="{{ $parentProject->dataPeluang->peluang ?? '-' }}" readonly>
                                <small class="text-muted">Diambil dari project induk</small>
                            </div>
                        </div>

                        <!-- Bidang Jasa (Display Only) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bidang_jasa_display" class="form-label">Bidang Jasa</label>
                                <input type="text" class="form-control" id="bidang_jasa_display"
                                       value="{{ $parentProject->bidangJasa->desc_bidjasa ?? 'N/A' }}" readonly>
                            </div>
                        </div>

                        <!-- Kondisi Proyek (Display Only) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kondisi_proyek_display" class="form-label">Kondisi Proyek</label>
                                <input type="text" class="form-control" id="kondisi_proyek_display"
                                       value="{{ $parentProject->kondisiProyek->desc_kondisi_proyek ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi & Jarak (Read-only) -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-map me-2"></i>Lokasi & Jarak</h6>
                    <div class="row">
                        <!-- Lokasi Proyek (Display Only) -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="lokasi_proyek_display" class="form-label">Lokasi Proyek</label>
                                <input type="text" class="form-control" id="lokasi_proyek_display"
                                       value="{{ $parentProject->lokasi_proyek ?? 'Belum diisi' }}" readonly>
                            </div>
                        </div>

                        <!-- Jarak Lokasi (Display Only) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="jarak_lokasi_display" class="form-label">Jarak Lokasi</label>
                                <input type="text" class="form-control" id="jarak_lokasi_display"
                                       value="{{ $jarakDisplay }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Kontrak -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-file me-2"></i>Informasi Kontrak</h6>
                    <div class="row">
                        <!-- No Kontrak -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_kontrak" class="form-label">No Kontrak/PO/JO/SPK</label>
                                <input type="text" class="form-control @error('no_kontrak') is-invalid @enderror"
                                       id="no_kontrak" name="no_kontrak"
                                       value="{{ old('no_kontrak', $project->no_kontrak) }}"
                                       placeholder="Masukkan nomor kontrak" maxlength="100">
                                @error('no_kontrak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nilai Proyek -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nilai_proyek_display" class="form-label">Nilai Proyek</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control currency-input @error('nilai_proyek') is-invalid @enderror"
                                           id="nilai_proyek_display"
                                           value="{{ old('nilai_proyek', $project->nilai_proyek ? number_format((float)$project->nilai_proyek, 0, ',', '.') : '') }}"
                                           placeholder="0">
                                    <input type="hidden" id="nilai_proyek" name="nilai_proyek" value="{{ old('nilai_proyek', $project->nilai_proyek) }}">
                                </div>
                                @error('nilai_proyek')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Masukkan angka tanpa titik/koma, format otomatis</small>
                            </div>
                        </div>

                        <!-- Tanggal Pengakuan -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tgl_pengakuan" class="form-label">Tanggal Pengakuan</label>
                                <input type="text" class="form-control date-picker @error('tgl_pengakuan') is-invalid @enderror"
                                       id="tgl_pengakuan" name="tgl_pengakuan"
                                       value="{{ old('tgl_pengakuan', $project->tgl_pengakuan ? \Carbon\Carbon::parse($project->tgl_pengakuan)->format('d/m/Y') : '') }}"
                                       placeholder="dd/mm/yyyy">
                                @error('tgl_pengakuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tanggal Kontrak -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tgl_kontrak" class="form-label">Tanggal Kontrak</label>
                                <input type="text" class="form-control date-picker @error('tgl_kontrak') is-invalid @enderror"
                                       id="tgl_kontrak" name="tgl_kontrak"
                                       value="{{ old('tgl_kontrak', $project->tgl_kontrak ? \Carbon\Carbon::parse($project->tgl_kontrak)->format('d/m/Y') : '') }}"
                                       placeholder="dd/mm/yyyy">
                                @error('tgl_kontrak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tanggal Expire -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tgl_expire" class="form-label">Tanggal Expire Retensi</label>
                                <input type="text" class="form-control date-picker @error('tgl_expire') is-invalid @enderror"
                                       id="tgl_expire" name="tgl_expire"
                                       value="{{ old('tgl_expire', $project->tgl_expire ? \Carbon\Carbon::parse($project->tgl_expire)->format('d/m/Y') : '') }}"
                                       placeholder="dd/mm/yyyy">
                                @error('tgl_expire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Start Kontrak -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_kontrak" class="form-label">Start Kontrak <span class="text-danger">*</span></label>
                                <input type="text" class="form-control date-picker @error('start_kontrak') is-invalid @enderror"
                                       id="start_kontrak" name="start_kontrak"
                                       value="{{ old('start_kontrak', $project->start_kontrak ? \Carbon\Carbon::parse($project->start_kontrak)->format('d/m/Y') : '') }}"
                                       placeholder="dd/mm/yyyy" required>
                                @error('start_kontrak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Finish Kontrak -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="finish_kontrak" class="form-label">Finish Kontrak <span class="text-danger">*</span></label>
                                <input type="text" class="form-control date-picker @error('finish_kontrak') is-invalid @enderror"
                                       id="finish_kontrak" name="finish_kontrak"
                                       value="{{ old('finish_kontrak', $project->finish_kontrak ? \Carbon\Carbon::parse($project->finish_kontrak)->format('d/m/Y') : '') }}"
                                       placeholder="dd/mm/yyyy" required>
                                @error('finish_kontrak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Penanggung Jawab -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-user-check me-2"></i>Status & Penanggung Jawab</h6>
                    <div class="row">
                        <!-- Penanggung Jawab -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                <select class="form-select @error('penanggung_jawab') is-invalid @enderror"
                                        id="penanggung_jawab" name="penanggung_jawab">
                                    <option value="">-- Pilih Penanggung Jawab --</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->nik }}"
                                                data-kode-divisi="{{ $manager->kode_divisi }}"
                                                data-nama-divisi="{{ $manager->divisi->nama_divisi ?? '' }}"
                                                {{ old('penanggung_jawab', $project->penanggung_jawab) == $manager->nik ? 'selected' : '' }}>
                                            {{ $manager->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('penanggung_jawab')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Divisi (Auto-filled) -->
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="divisi_display" class="form-label">Divisi</label>
                                <div id="divisi_badge_container" class="form-control-plaintext">
                                    @if($project->kode_divisi)
                                        <span class="badge bg-info" title="{{ $project->divisi->nama_divisi ?? '' }}">{{ $project->kode_divisi }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                <input type="hidden" name="kode_divisi" id="kode_divisi" value="{{ old('kode_divisi', $project->kode_divisi) }}">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="">-- Pilih Status --</option>
                                    @foreach($statusOptions as $key => $value)
                                        <option value="{{ $key }}" {{ old('status', $project->status) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Keterangan (Free Text) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" rows="2"
                                          class="form-control @error('keterangan') is-invalid @enderror"
                                          placeholder="Masukkan keterangan proyek (opsional)"
                                          maxlength="255">{{ old('keterangan', $project->keterangan) }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <span id="keterangan-counter">{{ strlen(old('keterangan', $project->keterangan)) }}</span>/255 karakter
                                </small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Upload Dokumen -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-cloud-upload me-2"></i>Upload Dokumen</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Current Document Info -->
                            @if($project->dokumen_path && is_string($project->dokumen_path))
                                <div class="mb-3">
                                    <label class="form-label">Dokumen Saat Ini</label>
                                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bx bx-file me-2"></i>
                                            {{ basename($project->dokumen_path) }}
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="window.filePreview.showPreview('{{ asset('storage/' . $project->dokumen_path) }}', '{{ basename($project->dokumen_path) }}', '{{ route('dataproyek.download', $project->id_project) }}?norut={{ $project->norut }}&v={{ time() }}')">
                                                <i class="bx bx-show me-1"></i> Preview
                                            </button>
                                            <a href="{{ route('dataproyek.download', $project->id_project) }}?norut={{ $project->norut }}&v={{ time() }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bx bx-download me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Upload New Document -->
                            <div class="mb-3">
                                <label for="dokumen_kontrak" class="form-label">
                                    {{ $project->dokumen_path ? 'Ganti Dokumen' : 'Upload Dokumen' }} Kontrak/JO/SPK/PO
                                </label>
                                <input type="file" class="form-control @error('dokumen_kontrak') is-invalid @enderror"
                                       id="dokumen_kontrak" name="dokumen_kontrak"
                                       accept=".docx,.doc,.pdf,.xlsx,.xls,.pptx,.ppt,.jpg,.jpeg,.png">
                                <div class="form-text">
                                    Format: docx, doc, pdf, xlsx, xls, pptx, ppt, jpg, jpeg, png (Max: 25MB)
                                </div>
                                @error('dokumen_kontrak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- File Info -->
                                <div id="fileInfo" class="mt-2" style="display: none;">
                                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bx bx-file me-2"></i>
                                            <span id="fileName"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dataproyek.show', $project->id_project) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bx bx-check me-1"></i> Simpan
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

        <!-- File Preview Handler -->
        <script src="{{ asset('js/file-preview.js') }}"></script>

        <script src="{{ asset('js/dataproyek.js') }}"></script>
        <script>
        $(document).ready(function() {
            // Auto-fill divisi when penanggung_jawab is selected
            $('#penanggung_jawab').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const kodeDivisi = selectedOption.data('kode-divisi') || '';
                const namaDivisi = selectedOption.data('nama-divisi') || '';

                $('#kode_divisi').val(kodeDivisi);

                if (kodeDivisi) {
                    $('#divisi_badge_container').html(
                        '<span class="badge bg-info" title="' + namaDivisi + '">' + kodeDivisi + '</span>'
                    );
                } else {
                    $('#divisi_badge_container').html('<span class="text-muted">-</span>');
                }
            });

            // Initialize hidden field with clean value on page load
            const nilaiProyekDisplay = $('#nilai_proyek_display');
            if (nilaiProyekDisplay.length && nilaiProyekDisplay.val()) {
                const displayValue = nilaiProyekDisplay.val();
                const cleanValue = displayValue.replace(/[^\d]/g, '');
                $('#nilai_proyek').val(cleanValue);

                console.log('Initialized nilai_proyek:', {
                    displayed: displayValue,
                    clean: cleanValue
                });
            }


            // Character counter for keterangan field
            $('#keterangan').on('input', function() {
                const length = $(this).val().length;
                $('#keterangan-counter').text(length);
            });

            // Debug form submission
            $('#proyekForm').on('submit', function(e) {
                const displayValue = $('#nilai_proyek_display').val();
                const hiddenValue = $('#nilai_proyek').val();

                console.log('=== FORM SUBMIT DEBUG (edit-history.blade.php) ===');
                console.log('Display field value:', displayValue);
                console.log('Hidden field value:', hiddenValue);
                console.log('Form data:', {
                    action: this.action,
                    method: this.method,
                    nilai_proyek_display: displayValue,
                    nilai_proyek_hidden: hiddenValue
                });

                // Log all form data
                const formData = new FormData(this);
                console.log('All form data being sent:');
                for (let [key, value] of formData.entries()) {
                    if (key === 'nilai_proyek') {
                        console.log(`${key}: "${value}" (length: ${value.length})`);
                    } else {
                        console.log(`${key}:`, value);
                    }
                }
            });
        });
        </script>
    @endpush

</x-layout>
