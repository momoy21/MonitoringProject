<x-layout title="Edit Data Proyek">
    @push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Data Proyek', 'url' => route('dataproyek.index')],
            ['name' => 'Edit Data Proyek']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Data Proyek</h4>
                <p class="mb-0">ID Proyek: <strong>{{ $project->id_project }}</strong></p>
                @if($isHistory)
                    <span class="badge bg-info">History Proyek</span>
                @endif
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    @if($isHistory)
                        <a href="{{ route('dataproyek.show', $project->id_project) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali ke History
                        </a>
                    @else
                        <a href="{{ route('dataproyek.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <x-flash-messages />

    <!-- Form Section -->
    <div class="card">
        <div class="card-body">
            <form id="proyekForm" method="POST" action="{{ route('dataproyek.update', $project->id_project) }}"
                  enctype="multipart/form-data" data-is-edit="true">
                @csrf
                @method('PUT')

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
                                <label for="cost_center" class="form-label">Cost Center <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cost_center') is-invalid @enderror"
                                       id="cost_center" name="cost_center"
                                       value="{{ old('cost_center', $project->cost_center) }}"
                                       placeholder="Huruf dan angka, max 9 karakter" maxlength="9" required readonly>
                                <small class="form-text text-muted">Cost Center tidak dapat diubah setelah proyek dibuat.</small>
                                @error('cost_center')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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

                <!-- Informasi Konsumen & Bidang -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-buildings me-2"></i>Informasi Konsumen & Bidang</h6>
                    <div class="row">
                        <!-- Konsumen -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_konsumen" class="form-label">Konsumen <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_konsumen') is-invalid @enderror"
                                        id="id_konsumen" name="id_konsumen" required>
                                    <option value="">-- Pilih Konsumen --</option>
                                    @foreach($konsumen as $k)
                                        <option value="{{ $k->id_konsumen }}"
                                                {{ old('id_konsumen', $project->id_konsumen) == $k->id_konsumen ? 'selected' : '' }}>
                                            {{ $k->konsumen }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_konsumen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Data Peluang -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_datapeluang" class="form-label">Data Peluang</label>
                                <select class="form-select @error('id_datapeluang') is-invalid @enderror"
                                        id="id_datapeluang" name="id_datapeluang">
                                    <option value="">-- Pilih Data Peluang --</option>
                                    @foreach($dataPeluang as $dp)
                                        <option value="{{ $dp->id_datapeluang }}"
                                                {{ old('id_datapeluang', $project->id_datapeluang) == $dp->id_datapeluang ? 'selected' : '' }}>
                                            {{ Str::limit($dp->peluang, 60) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_datapeluang')
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
                                        <option value="{{ $bj->id_bidjasa }}"
                                                {{ old('id_bidjasa', $project->id_bidjasa) == $bj->id_bidjasa ? 'selected' : '' }}>
                                            {{ $bj->desc_bidjasa }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_bidjasa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Kondisi Proyek -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_kondisi_proyek" class="form-label">Kondisi Proyek <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_kondisi_proyek') is-invalid @enderror"
                                        id="id_kondisi_proyek" name="id_kondisi_proyek" required>
                                    <option value="">-- Pilih Kondisi Proyek --</option>
                                    @foreach($kondisiProyek as $kp)
                                        <option value="{{ $kp->id_kondisi_proyek }}"
                                                {{ old('id_kondisi_proyek', $project->id_kondisi_proyek) == $kp->id_kondisi_proyek ? 'selected' : '' }}>
                                            {{ $kp->desc_kondisi_proyek }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_kondisi_proyek')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi & Jarak -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-map me-2"></i>Lokasi & Jarak</h6>
                    <div class="row">
                        <!-- Lokasi Proyek -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="lokasi_proyek" class="form-label">Lokasi Proyek</label>
                                <input type="text" class="form-control @error('lokasi_proyek') is-invalid @enderror"
                                       id="lokasi_proyek" name="lokasi_proyek"
                                       value="{{ old('lokasi_proyek', $project->lokasi_proyek) }}"
                                       placeholder="Masukkan lokasi proyek" maxlength="100">
                                @error('lokasi_proyek')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Jarak Lokasi -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="jarak_lokasi" class="form-label">Jarak Lokasi</label>
                                <select class="form-select @error('jarak_lokasi') is-invalid @enderror"
                                        id="jarak_lokasi" name="jarak_lokasi">
                                    <option value="">-- Pilih Jarak --</option>
                                    @foreach($jarakOptions as $key => $value)
                                        <option value="{{ $key }}"
                                                {{ old('jarak_lokasi', $project->jarak_lokasi) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jarak_lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <label for="nilai_proyek" class="form-label">Nilai Proyek</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control currency-input @error('nilai_proyek') is-invalid @enderror"
                                           id="nilai_proyek" name="nilai_proyek"
                                           value="{{ old('nilai_proyek', $project->nilai_proyek ? number_format($project->nilai_proyek, 0, ',', '.') : '') }}"
                                           placeholder="0">
                                </div>
                                @error('nilai_proyek')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tanggal Pengakuan -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tgl_pengakuan" class="form-label">Tanggal Pengakuan</label>
                                <input type="text" class="form-control date-picker @error('tgl_pengakuan') is-invalid @enderror"
                                       id="tgl_pengakuan" name="tgl_pengakuan"
                                       value="{{ old('tgl_pengakuan', $project->tgl_pengakuan ? $project->tgl_pengakuan->format('d/m/Y') : '') }}"
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
                                       value="{{ old('tgl_kontrak', $project->tgl_kontrak ? $project->tgl_kontrak->format('d/m/Y') : '') }}"
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
                                       value="{{ old('tgl_expire', $project->tgl_expire ? $project->tgl_expire->format('d/m/Y') : '') }}"
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
                                       value="{{ old('start_kontrak', $project->start_kontrak ? $project->start_kontrak->format('d/m/Y') : '') }}"
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
                                       value="{{ old('finish_kontrak', $project->finish_kontrak ? $project->finish_kontrak->format('d/m/Y') : '') }}"
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                <select class="form-select @error('penanggung_jawab') is-invalid @enderror"
                                        id="penanggung_jawab" name="penanggung_jawab">
                                    <option value="">-- Pilih Manager --</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->nik }}"
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

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    @foreach($statusOptions as $key => $value)
                                        <option value="{{ $key }}"
                                                {{ old('status', $project->status) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Keterangan (Dropdown for data_proyek table) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="keterangan_display" class="form-label">Keterangan</label>
                                <select class="form-select" id="keterangan_display" disabled>
                                    @foreach($keteranganOptions as $key => $value)
                                        <option value="{{ $key }}"
                                                {{ old('keterangan', $project->keterangan) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <!-- Hidden input to preserve keterangan value on update -->
                                <input type="hidden" name="keterangan" value="{{ $project->keterangan }}">
                                <small class="form-text text-muted">Keterangan tidak dapat diubah saat edit</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-cloud-upload me-2"></i>Upload Dokumen</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Current Document -->
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
                                                    onclick="window.filePreview.showPreview('{{ asset('storage/' . $project->dokumen_path) }}', '{{ basename($project->dokumen_path) }}', '{{ route('dataproyek.download', $project->id_project) }}?v={{ time() }}')">
                                                <i class="bx bx-show me-1"></i> Preview
                                            </button>
                                            <a href="{{ route('dataproyek.download', $project->id_project) }}?v={{ time() }}"
                                               class="btn btn-sm btn-outline-primary"
                                               target="_blank">
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
                                    Format: docx, doc, pdf, xlsx, xls, pptx, ppt, jpg, jpeg, png (Max: 10MB)
                                </div>
                                @error('dokumen_kontrak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- New File Info -->
                                <div id="fileInfo" class="mt-2" style="display: none;">
                                    <div class="alert alert-success d-flex justify-content-between align-items-center">
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
                            @if($isHistory)
                                <a href="{{ route('dataproyek.show', $project->id_project) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            @else
                                <a href="{{ route('dataproyek.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            @endif
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
    @endpush
</x-layout>
