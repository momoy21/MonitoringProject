<x-layout title="Jenis Proyek">
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Jenis Proyek</h4>
                <p class="mb-0 text-muted">Kelola data kategori dan tipe proyek</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <div class="position-relative">
                        <form action="{{ route('jenisproyek.index') }}" method="GET">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Cari ID atau Jenis..." value="{{ request('search') }}" autocomplete="off">
                            </div>
                        </form>
                    </div>
                    <a href="{{ route('jenisproyek.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah
                    </a>
                </div>
            </div>
        </div>

        <div class="row mt-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 small fw-bold text-muted">TAMPILKAN:</label>
                    <select class="form-select per-page-selector" style="width: auto;" onchange="window.location.href = '{{ route('jenisproyek.index') }}?per_page=' + this.value + '&search={{ request('search') }}'">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-muted small">data per halaman</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th class="fw-bold text-muted small uppercase">ID Jenis</th>
                        <th class="fw-bold text-muted small uppercase">Jenis Proyek</th>
                        <th class="fw-bold text-muted small uppercase text-center">Status</th>
                        <th class="fw-bold text-muted small uppercase text-end px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataJenis as $item)
                    <tr ondblclick="window.location='{{ route('jenisproyek.edit', $item->idjenisproyek) }}'" style="cursor: pointer;" title="Klik 2x untuk edit">
                        <td class="fw-semibold text-primary">{{ $item->idjenisproyek }}</td>
                        <td>{{ $item->jenisproyek }}</td>
                        <td class="text-center">
                            @if($item->status == 'A')
                                <span class="badge bg-success px-3">AKTIF</span>
                            @else
                                <span class="badge bg-secondary px-3">NON AKTIF</span>
                            @endif
                        </td>
                        <td class="text-end px-4">
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li>
                                        <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#viewJenisModal{{ $item->idjenisproyek }}">
                                            <i class="bx bx-show me-2 text-primary"></i> Lihat Detail
                                        </button>
                                    </li>
                                    {{-- Tombol Edit dihapus karena sudah ada fungsi double click pada baris --}}
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="viewJenisModal{{ $item->idjenisproyek }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-bottom-0 pb-0">
                                    <h5 class="modal-title fw-bold text-primary">Detail Jenis Proyek</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-start">
                                    <h6 class="text-primary fw-bold mb-4 border-bottom pb-2">Informasi Detail</h6>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4 text-muted small fw-bold">ID JENIS:</div>
                                        <div class="col-md-8 fw-bold text-dark">{{ $item->idjenisproyek }}</div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4 text-muted small fw-bold">NAMA JENIS PROYEK:</div>
                                        <div class="col-md-8 fw-bold text-dark">{{ $item->jenisproyek }}</div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-4 text-muted small fw-bold">STATUS:</div>
                                        <div class="col-md-8">
                                            @if($item->status == 'A')
                                                <span class="badge bg-success px-4" style="font-size: 0.85rem;">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary px-4" style="font-size: 0.85rem;">Non Aktif</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end border-top pt-3">
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="4" class="text-center py-5 text-muted small">Data tidak ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 px-2" style="opacity: 0.8;">
        <div class="pagination-info text-muted small">
            Menampilkan {{ $dataJenis->firstItem() ?? 0 }} hingga {{ $dataJenis->lastItem() ?? 0 }} dari {{ $dataJenis->total() }} data
        </div>
        <div class="d-flex align-items-center gap-1">
            <a href="{{ $dataJenis->url(1) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-chevrons-left"></i></a>
            <a href="{{ $dataJenis->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm {{ $dataJenis->onFirstPage() ? 'disabled' : '' }}"><i class="bx bx-chevron-left"></i></a>
            <span class="btn btn-primary btn-sm px-3 fw-bold mx-1">{{ $dataJenis->currentPage() }}</span>
            <a href="{{ $dataJenis->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm {{ !$dataJenis->hasMorePages() ? 'disabled' : '' }}"><i class="bx bx-chevron-right"></i></a>
            <a href="{{ $dataJenis->url($dataJenis->lastPage()) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-chevrons-right"></i></a>
        </div>
    </div>
</x-layout>