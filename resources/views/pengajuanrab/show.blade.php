<x-layout title="Detail Pengajuan RAB">
    @push('styles')
    <style>
        .detail-label {
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #697a8d;
            padding: 10px 15px;
            background-color: #f5f5f9;
            border-radius: 5px;
            min-height: 42px;
        }
        .detail-value.highlight {
            background-color: #e8e8ff;
            color: #696cff;
            font-weight: bold;
        }
        .file-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #f9f9f9;
        }
        .file-card i {
            font-size: 36px;
        }
        .file-card .file-info {
            flex-grow: 1;
        }
        .file-card .file-name {
            font-weight: 600;
            margin-bottom: 3px;
        }
        .file-card .file-type {
            font-size: 12px;
            color: #a1a5b7;
        }
    </style>
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Pengajuan RAB', 'url' => route('pengajuanrab.index')],
            ['name' => 'Detail Pengajuan RAB']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Detail Pengajuan RAB</h4>
                <p class="mb-0">Informasi lengkap pengajuan Rencana Anggaran Biaya</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('pengajuanrab.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Detail Card -->
    <div class="card">
        <div class="card-body">
            <!-- Informasi Pengajuan -->
            <div class="form-section">
                <h6 class="mb-3"><i class="bx bx-id-card me-2"></i>Informasi Pengajuan</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Tanggal Pengajuan</div>
                            <div class="detail-value">{{ $pengajuanrab->tgl_input_formatted }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Nomor Pengajuan</div>
                            <div class="detail-value highlight">{{ $pengajuanrab->nopengajuan }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Dokumen IO</div>
                            <div class="detail-value">{{ $pengajuanrab->dokumen_io ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Proyek -->
            <div class="form-section">
                <h6 class="mb-3"><i class="bx bx-folder me-2"></i>Informasi Proyek</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Cost Center</div>
                            <div class="detail-value highlight">{{ $pengajuanrab->cost_center }}</div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <div class="detail-label">Nama Proyek</div>
                            <div class="detail-value">{{ $pengajuanrab->nama_project }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Divisi</div>
                            <div class="detail-value">{{ $pengajuanrab->masterDivisi->nama_divisi ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Project Manager</div>
                            <div class="detail-value">{{ $pengajuanrab->pm ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Nilai Proyek</div>
                            <div class="detail-value">{{ $pengajuanrab->nilai_proyek_formatted }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Konsumen & Bidang Jasa -->
            <div class="form-section">
                <h6 class="mb-3"><i class="bx bx-buildings me-2"></i>Informasi Konsumen & Bidang Jasa</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="detail-label">Konsumen</div>
                            <div class="detail-value">{{ $pengajuanrab->konsumen->konsumen ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="detail-label">Bidang Jasa</div>
                            <div class="detail-value">{{ $pengajuanrab->bidangJasa->desc_bidjasa ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keterangan & Progress RAB -->
            <div class="form-section">
                <h6 class="mb-3"><i class="bx bx-info-circle me-2"></i>Keterangan & Progress RAB</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Keterangan</div>
                            <div class="detail-value">{{ $pengajuanrab->keterangan_text }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Progress</div>
                            <div class="detail-value">{!! $pengajuanrab->progress_badge !!}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Hasil Pleno</div>
                            <div class="detail-value">{!! $pengajuanrab->hasil_pleno_badge !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Margin & Catatan -->
            <div class="form-section">
                <h6 class="mb-3"><i class="bx bx-calculator me-2"></i>Margin & Catatan</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Margin RKAP (%)</div>
                            <div class="detail-value">{{ $pengajuanrab->margin_rkap ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="detail-label">Margin Pleno (%)</div>
                            <div class="detail-value">{{ $pengajuanrab->margin_pleno ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="detail-label">Catatan Hasil Pleno</div>
                            <div class="detail-value" style="min-height: 80px;">{{ $pengajuanrab->catatan ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dokumen Pendukung -->
            <div class="form-section">
                <h6 class="mb-3"><i class="bx bx-file me-2"></i>Dokumen Pendukung</h6>
                <div class="row">
                    <!-- Dokumen RAB -->
                    <div class="col-md-6 mb-3">
                        @if($pengajuanrab->rab_upload)
                            <div class="file-card">
                                <i class="bx bx-spreadsheet text-success"></i>
                                <div class="file-info">
                                    <div class="file-name">{{ basename($pengajuanrab->rab_upload) }}</div>
                                    <div class="file-type">Dokumen RAB (Excel)</div>
                                </div>
                                <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'rab']) }}" class="btn btn-primary">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </div>
                        @else
                            <div class="file-card">
                                <i class="bx bx-spreadsheet text-muted"></i>
                                <div class="file-info">
                                    <div class="file-name text-muted">Belum ada dokumen</div>
                                    <div class="file-type">Dokumen RAB (Excel)</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Dokumen Kontrak -->
                    <div class="col-md-6 mb-3">
                        @if($pengajuanrab->file_upload)
                            <div class="file-card">
                                <i class="bx bxs-file-pdf text-danger"></i>
                                <div class="file-info">
                                    <div class="file-name">{{ basename($pengajuanrab->file_upload) }}</div>
                                    <div class="file-type">Dokumen JO/PO/SPK/Kontrak (PDF)</div>
                                </div>
                                <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'kontrak']) }}" class="btn btn-primary">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </div>
                        @else
                            <div class="file-card">
                                <i class="bx bxs-file-pdf text-muted"></i>
                                <div class="file-info">
                                    <div class="file-name text-muted">Belum ada dokumen</div>
                                    <div class="file-type">Dokumen JO/PO/SPK/Kontrak (PDF)</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Dokumen Peta Risiko -->
                    <div class="col-md-6 mb-3">
                        @if($pengajuanrab->peta_risk_upload)
                            <div class="file-card">
                                <i class="bx bxs-file-pdf text-danger"></i>
                                <div class="file-info">
                                    <div class="file-name">{{ basename($pengajuanrab->peta_risk_upload) }}</div>
                                    <div class="file-type">Dokumen Peta Risiko (PDF)</div>
                                </div>
                                <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'peta_risiko']) }}" class="btn btn-primary">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </div>
                        @else
                            <div class="file-card">
                                <i class="bx bxs-file-pdf text-muted"></i>
                                <div class="file-info">
                                    <div class="file-name text-muted">Belum ada dokumen</div>
                                    <div class="file-type">Dokumen Peta Risiko (PDF)</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Dokumen Hasil Pleno -->
                    <div class="col-md-6 mb-3">
                        @if($pengajuanrab->hasil_upload)
                            <div class="file-card">
                                <i class="bx bxs-file-pdf text-danger"></i>
                                <div class="file-info">
                                    <div class="file-name">{{ basename($pengajuanrab->hasil_upload) }}</div>
                                    <div class="file-type">Dokumen Hasil Pleno (PDF)</div>
                                </div>
                                <a href="{{ route('pengajuanrab.download', [$pengajuanrab->nopengajuan, 'hasil']) }}" class="btn btn-primary">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </div>
                        @else
                            <div class="file-card">
                                <i class="bx bxs-file-pdf text-muted"></i>
                                <div class="file-info">
                                    <div class="file-name text-muted">Belum ada dokumen</div>
                                    <div class="file-type">Dokumen Hasil Pleno (PDF)</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="row mt-4">
                <div class="col-12">
                    <small class="text-muted">
                        Dibuat: {{ $pengajuanrab->created_at ? $pengajuanrab->created_at->format('d/m/Y H:i') : '-' }} |
                        Diperbarui: {{ $pengajuanrab->updated_at ? $pengajuanrab->updated_at->format('d/m/Y H:i') : '-' }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</x-layout>
