<x-layout title="Tambah Data Proyek">
    @push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Data Proyek', 'url' => route('dataproyek.index')],
            ['name' => 'Tambah Data Proyek']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">
                    @if(isset($addToHistory) && $addToHistory)
                        Tambah Proyek ke Cost Center: {{ $costCenter }}
                    @else
                        Tambah Data Proyek Baru
                    @endif
                </h4>
                <p class="mb-0">Lengkapi form di bawah untuk menambah data proyek</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('dataproyek.index') }}" class="btn btn-outline-secondary">
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
            <form id="proyekForm" method="POST" action="{{ route('dataproyek.store') }}" enctype="multipart/form-data"
                  data-add-to-history="{{ isset($addToHistory) ? 'true' : 'false' }}">
                @csrf

                @if(isset($addToHistory) && $addToHistory)
                    <input type="hidden" name="add_to_history" value="1">
                    <input type="hidden" name="cost_center" value="{{ $costCenter }}">
                @endif

                <!-- Informasi Proyek -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-folder me-2"></i>Informasi Proyek</h6>
                    <div class="row">
                        <!-- ID Project (Auto Generated) -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_project" class="form-label">ID Project</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="id-project-display" id="id-project-display">Auto-generate</div>
                                    <small class="text-muted">YYYY + DD + DDDD</small>
                                </div>
                                <input type="hidden" name="id_project" id="id_project" value="">
                            </div>
                        </div>

                        <!-- Dokumen IO -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dokumen_io" class="form-label">Dokumen IO</label>
                                <input type="text" class="form-control number-only @error('dokumen_io') is-invalid @enderror"
                                       id="dokumen_io" name="dokumen_io" value="{{ old('dokumen_io') }}"
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
                                       id="cost_center" name="cost_center" value="{{ old('cost_center', $costCenter ?? '') }}"
                                       placeholder="Huruf dan angka, max 9 karakter" maxlength="9"
                                       {{ isset($costCenter) ? 'readonly' : '' }} required>
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
                                          placeholder="Masukkan nama proyek" required>{{ old('namaproject') }}</textarea>
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
                                        <option value="{{ $k->id_konsumen }}" {{ old('id_konsumen') == $k->id_konsumen ? 'selected' : '' }}>
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
                                        <option value="{{ $dp->id_datapeluang }}" {{ old('id_datapeluang') == $dp->id_datapeluang ? 'selected' : '' }}>
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
                                        <option value="{{ $bj->id_bidjasa }}" {{ old('id_bidjasa') == $bj->id_bidjasa ? 'selected' : '' }}>
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
                                        <option value="{{ $kp->id_kondisi_proyek }}" {{ old('id_kondisi_proyek') == $kp->id_kondisi_proyek ? 'selected' : '' }}>
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
                                       id="lokasi_proyek" name="lokasi_proyek" value="{{ old('lokasi_proyek') }}"
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
                                        <option value="{{ $key }}" {{ old('jarak_lokasi') == $key ? 'selected' : '' }}>
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
                                       id="no_kontrak" name="no_kontrak" value="{{ old('no_kontrak') }}"
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
                                           id="nilai_proyek" name="nilai_proyek" value="{{ old('nilai_proyek') }}"
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
                                       id="tgl_pengakuan" name="tgl_pengakuan" value="{{ old('tgl_pengakuan') }}"
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
                                       id="tgl_kontrak" name="tgl_kontrak" value="{{ old('tgl_kontrak') }}"
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
                                       id="tgl_expire" name="tgl_expire" value="{{ old('tgl_expire') }}"
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
                                       id="start_kontrak" name="start_kontrak" value="{{ old('start_kontrak') }}"
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
                                       id="finish_kontrak" name="finish_kontrak" value="{{ old('finish_kontrak') }}"
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
                                    <option value="">-- Pilih Manager --</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->nik }}"
                                                data-kode-divisi="{{ $manager->kode_divisi }}"
                                                data-nama-divisi="{{ $manager->divisi->nama_divisi ?? '' }}"
                                                {{ old('penanggung_jawab') == $manager->nik ? 'selected' : '' }}>
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
                                    <span class="text-muted">-</span>
                                </div>
                                <input type="hidden" name="kode_divisi" id="kode_divisi" value="{{ old('kode_divisi') }}">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    @foreach($statusOptions as $key => $value)
                                        <option value="{{ $key }}" {{ old('status', 'O') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan <span class="text-danger">*</span></label>
                                <select name="keterangan" id="keterangan" class="form-select @error('keterangan') is-invalid @enderror" required>
                                    <option value="">Pilih Keterangan</option>
                                    <option value="1" {{ old('keterangan', '2') == '1' ? 'selected' : '' }}>Kontrak Induk</option>
                                    <option value="2" {{ old('keterangan', '2') == '2' ? 'selected' : '' }}>Bukan Kontrak Induk</option>
                                </select>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-cloud-upload me-2"></i>Upload Dokumen</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="dokumen_kontrak" class="form-label">Dokumen Kontrak/JO/SPK/PO</label>
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
                            <a href="{{ route('dataproyek.index') }}" class="btn btn-outline-secondary">
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
                let costCenterTimeout;

                $('#cost_center').on('input', function() {
                    const costCenter = $(this).val().trim();
                    const $field = $(this);
                    const $feedback = $field.closest('.mb-3').find('.cost-center-feedback');

                    console.log('Cost center input:', costCenter);

                    // Clear previous timeout
                    clearTimeout(costCenterTimeout);

                    // Remove existing feedback
                    $feedback.remove();
                    $field.removeClass('is-valid is-invalid');

                    if (costCenter.length === 0) {
                        return;
                    }

                    // Only check if field is not readonly (for cases where costCenter is pre-filled)
                    if ($field.prop('readonly')) {
                        return;
                    }

                    // Debounce the request
                    costCenterTimeout = setTimeout(function() {
                        console.log('Making AJAX request for:', costCenter);
                        $.ajax({
                            url: '{{ route('dataproyek.checkCostCenter') }}',
                            method: 'POST',
                            data: {
                                cost_center: costCenter,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log('AJAX response:', response);
                                if (response.exists) {
                                    $field.addClass('is-invalid');
                                    $field.data('existing-project', response.project); // Store project info
                                    $field.closest('.mb-3').append(
                                        '<div class="invalid-feedback cost-center-feedback">Cost Center sudah digunakan oleh proyek: ' + response.project.namaproject + '</div>'
                                    );
                                } else {
                                    $field.addClass('is-valid');
                                    $field.removeData('existing-project'); // Remove project info
                                    $field.closest('.mb-3').append(
                                        '<div class="valid-feedback cost-center-feedback">Cost Center tersedia.</div>'
                                    );
                                }
                            },
                            error: function() {
                                $field.addClass('is-invalid');
                                $field.closest('.mb-3').append(
                                    '<div class="invalid-feedback cost-center-feedback">Error checking cost center availability.</div>'
                                );
                            }
                        });
                    }, 500); // 500ms delay
                });

                // Prevent form submission if cost center exists
                // Use a higher priority event by binding to the submit button instead
                $('#submitBtn').on('click', function(e) {
                    const $costCenter = $('#cost_center');
                    console.log('Submit clicked, cost center invalid:', $costCenter.hasClass('is-invalid'));

                    if ($costCenter.hasClass('is-invalid') && !$costCenter.prop('readonly')) {
                        e.preventDefault();
                        e.stopPropagation();

                        const existingProject = $costCenter.data('existing-project');
                        console.log('Existing project data:', existingProject);

                        if (existingProject) {
                            showCostCenterModal(existingProject);
                        } else {
                            alert('Cost Center sudah digunakan. Silakan gunakan Cost Center yang berbeda.');
                            $costCenter.focus();
                        }
                        return false;
                    }
                });

                // Function to show modal for existing cost center
                function showCostCenterModal(project) {
                    // Create modal HTML if not exists
                    if ($('#costCenterModal').length === 0) {
                        const modalHtml = `
                            <div class="modal fade" id="costCenterModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cost Center Sudah Digunakan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Cost Center ini sudah digunakan oleh proyek:</p>
                                            <div class="alert alert-info">
                                                <strong id="existingProjectName"></strong><br>
                                                <small>ID Proyek: <span id="existingProjectId"></span></small>
                                            </div>
                                            <p>Anda dapat menambahkan history ke proyek yang sudah ada atau menggunakan cost center yang berbeda.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="bx bx-x me-1"></i> Tutup
                                            </button>
                                            <a href="#" id="addHistoryBtn" class="btn btn-primary">
                                                <i class="bx bx-plus me-1"></i> Tambah History ke Proyek Ini
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('body').append(modalHtml);
                    }

                    // Update modal content
                    $('#existingProjectName').text(project.namaproject);
                    $('#existingProjectId').text(project.id_project);
                    $('#addHistoryBtn').attr('href', project.add_history_url);

                    // Show modal
                    $('#costCenterModal').modal('show');
                }
            });
        </script>
    @endpush
</x-layout>
