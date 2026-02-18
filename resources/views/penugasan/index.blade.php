
<x-layout title="Pengajuan Penugasan">

    {{-- HEADER --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white py-1">
            <span class="small fw-bold">Pengajuan Penugasan</span>
        </div>

        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="row mb-2">
                        <label class="col-sm-4 small">ID Penugasan</label>
                        <div class="col-sm-8">
                            <input class="form-control form-control-sm bg-light" value="Otomatis" readonly>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-4 small">No Surat</label>
                        <div class="col-sm-8">
                            <input class="form-control form-control-sm bg-light" value="Otomatis" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-sm-4 small">Cost Center</label>
                        <div class="col-sm-8">
                            <div class="input-group input-group-sm">
                                <input class="form-control bg-light" placeholder="Cost Center" readonly>
                                <input class="form-control bg-light w-50" placeholder="Nama Proyek" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEARCH & ACTION --}}
                <div class="col-md-7 text-end">
                    <form action="{{ route('penugasan.index') }}" method="GET" class="d-inline-block me-2">
                        <div class="input-group input-group-sm" style="width:260px">
                            <span class="input-group-text bg-white">
                                <i class="bx bx-search text-muted"></i>
                            </span>
                            <input type="text" name="search"
                                   class="form-control border-start-0"
                                   placeholder="Cari Cost Center / NIK"
                                   value="{{ request('search') }}">
                        </div>
                    </form>

                    <div class="mt-2">
                        <a href="{{ route('penugasan.template') }}" target="_blank"
                           class="text-decoration-none small text-dark me-3">
                            Template <i class="bx bxs-file-doc text-success"></i>
                        </a>

                        <form action="{{ route('penugasan.upload') }}"
                              method="POST" enctype="multipart/form-data"
                              class="d-inline">
                            @csrf
                            <input type="file" name="file_excel" id="upload_file"
                                   class="d-none"
                                   onchange="this.form.submit()">
                            <button type="button"
                                    onclick="document.getElementById('upload_file').click()"
                                    class="btn btn-link p-0 small text-dark me-3">
                                Upload Penugasan <i class="bx bx-upload"></i>
                            </button>
                        </form>

                        <a href="{{ route('penugasan.create') }}"
                           class="btn btn-sm btn-primary px-4 shadow-sm">
                            <i class="bx bx-plus me-1"></i> Tambah
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

  {{-- TABLE --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-1">
        <span class="small fw-bold">Tim Proyek</span>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm text-center align-middle small mb-0">
            <thead class="bg-light text-secondary">
                <tr>
                    <th rowspan="2">NIK</th>
                    <th rowspan="2">Nama</th>
                    <th colspan="2">Periode Penugasan</th>
                    <th rowspan="2">Jabatan</th>
                    <th rowspan="2">Bobot</th>
                    <th rowspan="2">Aksi</th>
                </tr>
                <tr>
                    <th>Awal</th>
                    <th>Akhir</th>
                </tr>
            </thead>

            <tbody>
                @forelse($penugasan as $item)
                {{-- Double Click untuk Edit --}}
                <tr class="row-clickable" 
                    data-edit-url="{{ route('penugasan.edit', $item->IDPenugasan) }}"
                    style="cursor:pointer">
                    
                    <td>{{ $item->NIK }}</td>
                    <td class="text-start ps-2">{{ $item->karyawan->Nama ?? '-' }}</td>
                    <td>{{ $item->Periodeawal ? \Carbon\Carbon::parse($item->Periodeawal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->Periodeakhir ? \Carbon\Carbon::parse($item->Periodeakhir)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->Jabatan }}</td>
                    <td>{{ $item->Bobot }}%</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle py-0 px-2" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu shadow-sm">
                                <li>
                                    <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $item->id }}">
                                        <i class="bx bx-show text-primary me-2"></i> Lihat Detail
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>

                {{-- MODAL DETAIL (Disesuaikan agar mirip Master Divisi) --}}
                <div class="modal fade" id="modalDetail{{ $item->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-bottom-0 pb-0">
                                <h5 class="modal-title fw-bold text-primary">Detail Penugasan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 text-start">
                                <h6 class="text-primary fw-bold mb-4 border-bottom pb-2">Informasi Penugasan</h6>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4 text-muted small fw-bold">ID PENUGASAN:</div>
                                    <div class="col-md-8 fw-bold text-dark">{{ $item->IDPenugasan }}</div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4 text-muted small fw-bold">NO SURAT:</div>
                                    <div class="col-md-8 fw-bold text-dark">{{ $item->NoSurat }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-muted small fw-bold">NIK / NAMA:</div>
                                    <div class="col-md-8 fw-bold text-dark">{{ $item->NIK }} - {{ $item->karyawan->Nama ?? '-' }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-muted small fw-bold">JABATAN:</div>
                                    <div class="col-md-8 fw-bold text-dark">{{ $item->Jabatan }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-muted small fw-bold">PERIODE:</div>
                                    <div class="col-md-8 fw-bold text-dark">
                                        {{ $item->Periodeawal ? \Carbon\Carbon::parse($item->Periodeawal)->format('d/m/Y') : '-' }} s/d 
                                        {{ $item->Periodeakhir ? \Carbon\Carbon::parse($item->Periodeakhir)->format('d/m/Y') : '-' }}
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-4 text-muted small fw-bold">BOBOT:</div>
                                    <div class="col-md-8">
                                        <span class="badge bg-primary px-4" style="font-size: 0.85rem;">{{ $item->Bobot }}%</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end border-top pt-3">
                                    <button class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="py-4 text-muted small">Data tim proyek tidak ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.row-clickable').forEach(row => {
                row.addEventListener('dblclick', function(e) {
                    if (!e.target.closest('.dropdown')) {
                        window.location.href = this.dataset.editUrl;
                    }
                });
            });
        });
    </script>
</x-layout>