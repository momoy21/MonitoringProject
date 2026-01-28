<x-layout title="Master Divisi">
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Master Divisi</h4>
                <p class="mb-0 text-muted">Kelola data divisi perusahaan</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <div class="position-relative">
                        <form action="{{ route('masterdivisi.index') }}" method="GET">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Cari Kode atau Nama..." value="{{ request('search') }}" autocomplete="off">
                            </div>
                        </form>
                    </div>
                    <a href="{{ route('masterdivisi.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah
                    </a>
                </div>
            </div>
        </div>

        <div class="row mt-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 small fw-bold text-muted">TAMPILKAN:</label>
                    <select class="form-select per-page-selector" style="width: auto;" onchange="window.location.href = '{{ route('masterdivisi.index') }}?per_page=' + this.value + '&search={{ request('search') }}'">
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
                        <th class="fw-bold text-muted small uppercase">Kode Divisi</th>
                        <th class="fw-bold text-muted small uppercase">Nama Divisi</th>
                        <th class="fw-bold text-muted small uppercase text-center">Status</th>
                        <th class="fw-bold text-muted small uppercase text-end px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisi as $item)
                    <tr ondblclick="window.location='{{ route('masterdivisi.edit', $item->kode_divisi) }}'" style="cursor: pointer;">
                        <td class="fw-semibold text-primary">{{ $item->kode_divisi }}</td>
                        <td>{{ $item->nama_divisi }}</td>
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
                                        <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#viewDivisiModal{{ $item->kode_divisi }}">
                                            <i class="bx bx-show me-2 text-primary"></i> Lihat Detail
                                        </button>
                                    </li>
                                    {{-- Tombol Edit dihapus karena sudah ada Double Click --}}
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="viewDivisiModal{{ $item->kode_divisi }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-bottom-0 pb-0">
                                    <h5 class="modal-title fw-bold text-primary">Detail Divisi</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-start">
                                    <h6 class="text-primary fw-bold mb-4 border-bottom pb-2">Informasi Divisi</h6>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4 text-muted small fw-bold">KODE DIVISI:</div>
                                        <div class="col-md-8 fw-bold text-dark">{{ $item->kode_divisi }}</div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4 text-muted small fw-bold">NAMA DIVISI:</div>
                                        <div class="col-md-8 fw-bold text-dark">{{ $item->nama_divisi }}</div>
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
            Menampilkan {{ $divisi->firstItem() ?? 0 }} hingga {{ $divisi->lastItem() ?? 0 }} dari {{ $divisi->total() }} data
        </div>
        <div class="d-flex align-items-center gap-1">
            <a href="{{ $divisi->url(1) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-chevrons-left"></i></a>
            <a href="{{ $divisi->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm {{ $divisi->onFirstPage() ? 'disabled' : '' }}"><i class="bx bx-chevron-left"></i></a>
            <span class="btn btn-primary btn-sm px-3 fw-bold mx-1">{{ $divisi->currentPage() }}</span>
            <a href="{{ $divisi->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm {{ !$divisi->hasMorePages() ? 'disabled' : '' }}"><i class="bx bx-chevron-right"></i></a>
            <a href="{{ $divisi->url($divisi->lastPage()) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-chevrons-right"></i></a>
        </div>
    </div>
</x-layout>