<x-layout title="Detail Pencatatan Pleno RAB">
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
        .detail-section {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .detail-section:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 1rem;
            font-weight: 500;
            color: #333;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .file-download {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background-color: #d4edda;
            border-radius: 5px;
        }
    </style>
    @endpush

    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Pencatatan Pleno RAB', 'url' => route('pencatatanpleno.index')],
            ['name' => 'Detail']
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Detail Pencatatan Pleno RAB</h4>
                <p class="mb-0">Informasi lengkap pengajuan dan hasil pleno RAB</p>
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

    <!-- Detail Section -->
    <div class="card">
        <div class="card-body">
            <!-- Informasi Proyek -->
            <div class="detail-section">
                <h6 class="mb-3"><i class="bx bx-folder me-2"></i>Informasi Proyek</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Dokumen IO</div>
                        <div class="detail-value">{{ $rabProyek->dokumen_io ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Cost Center</div>
                        <div class="detail-value">{{ $rabProyek->cost_center }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Nama Proyek</div>
                        <div class="detail-value">{{ $rabProyek->nama_project }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Konsumen</div>
                        <div class="detail-value">{{ $rabProyek->konsumen->konsumen ?? '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Bidang Jasa</div>
                        <div class="detail-value">{{ $rabProyek->bidangJasa->desc_bidjasa ?? '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Divisi</div>
                        <div class="detail-value">{{ $rabProyek->masterDivisi->nama_divisi ?? '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Project Manager</div>
                        <div class="detail-value">{{ $rabProyek->pm ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Nilai Proyek</div>
                        <div class="detail-value">Rp {{ number_format($rabProyek->nilai_proyek ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Jenis Proyek</div>
                        <div class="detail-value">
                            @if($rabProyek->jenisProyek)
                                [{{ $rabProyek->jenisProyek->kode_jenis }}] {{ $rabProyek->jenisProyek->nama_jenis }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hasil Pleno -->
            <div class="detail-section">
                <h6 class="mb-3"><i class="bx bx-check-circle me-2"></i>Hasil Pleno</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Progress</div>
                        <div class="detail-value">
                            @if($rabProyek->progress)
                                {!! $rabProyek->progress_badge !!}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Keterangan</div>
                        <div class="detail-value">{{ $keteranganOptions[$rabProyek->keterangan] ?? '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Hasil Pleno</div>
                        <div class="detail-value">
                            @if($rabProyek->hasil_pleno)
                                {!! $rabProyek->hasil_pleno_badge !!}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Margin RKAP</div>
                        <div class="detail-value">{{ $rabProyek->margin_rkap ? number_format($rabProyek->margin_rkap, 2) . '%' : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Margin Pleno</div>
                        <div class="detail-value">{{ $rabProyek->margin_pleno ? number_format($rabProyek->margin_pleno, 2) . '%' : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Status RAB</div>
                        <div class="detail-value">{!! $rabProyek->status_badge !!}</div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="detail-label">Catatan Pleno</div>
                        <div class="detail-value">{{ $rabProyek->catatan ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Dokumen -->
            <div class="detail-section">
                <h6 class="mb-3"><i class="bx bx-file me-2"></i>Dokumen</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Dokumen RAB</div>
                        @if($rabProyek->rab_upload)
                            <div class="file-download">
                                <i class="bx bx-spreadsheet text-success" style="font-size: 24px;"></i>
                                <div>
                                    <a href="{{ Storage::url($rabProyek->rab_upload) }}" target="_blank" class="text-primary fw-bold">
                                        <i class="bx bx-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="detail-value">-</div>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Dokumen JO/PO/SPK/Kontrak</div>
                        @if($rabProyek->file_upload)
                            <div class="file-download">
                                <i class="bx bxs-file-pdf text-danger" style="font-size: 24px;"></i>
                                <div>
                                    <a href="{{ Storage::url($rabProyek->file_upload) }}" target="_blank" class="text-primary fw-bold">
                                        <i class="bx bx-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="detail-value">-</div>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Dokumen Peta Risiko</div>
                        @if($rabProyek->peta_risk_upload)
                            <div class="file-download">
                                <i class="bx bx-spreadsheet text-success" style="font-size: 24px;"></i>
                                <div>
                                    <a href="{{ Storage::url($rabProyek->peta_risk_upload) }}" target="_blank" class="text-primary fw-bold">
                                        <i class="bx bx-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="detail-value">-</div>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-label">Dokumen RAB Final (Hasil Pleno)</div>
                        @if($rabProyek->hasil_upload)
                            <div class="file-download">
                                <i class="bx bx-spreadsheet text-success" style="font-size: 24px;"></i>
                                <div>
                                    <a href="{{ Storage::url($rabProyek->hasil_upload) }}" target="_blank" class="text-primary fw-bold">
                                        <i class="bx bx-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="detail-value">-</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="{{ route('pencatatanpleno.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</x-layout>
