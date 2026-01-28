<x-layout title="Master Divisi">
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Master Divisi</h4>
                <p class="mb-0">Kelola data divisi perusahaan</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <form action="{{ route('masterdivisi.index') }}" method="GET" class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari Kode atau Nama..." value="{{ request('search') }}">
                        </div>
                    </form>
                    <a href="{{ route('masterdivisi.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah
                    </a>
                </div>
            </div>
        </div>

        <div class="row mt-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <label for="perPageSelect" class="form-label mb-0">TAMPILKAN:</label>
                    <select id="perPageSelect" class="form-select" style="width: auto;" onchange="window.location.href = '{{ route('masterdivisi.index') }}?per_page=' + this.value + '&search={{ request('search') }}'">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span>data per halaman</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="fw-bold">KODE DIVISI</th>
                        <th class="fw-bold">NAMA DIVISI</th>
                        <th class="fw-bold">STATUS</th>
                        <th class="fw-bold text-end">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisi as $item)
                    <tr ondblclick="window.location='{{ route('masterdivisi.edit', $item->kode_divisi) }}'" style="cursor: pointer;">
                        <td class="fw-bold">{{ $item->kode_divisi }}</td>
                        <td>{{ $item->nama_divisi }}</td>
                        <td>
                            <span class="badge {{ $item->status == 'A' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $item->status == 'A' ? 'Aktif' : 'Non Aktif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('masterdivisi.edit', $item->kode_divisi) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-edit-alt"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2 text-muted" style="font-size: 48px;"></i>
                                <p class="mb-0 text-muted">Tidak ada data divisi</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
        <div class="pagination-info">
            <span class="text-muted small">
                Menampilkan {{ $divisi->firstItem() ?? 0 }} hingga {{ $divisi->lastItem() ?? 0 }} dari {{ $divisi->total() }} data
            </span>
        </div>

        <div class="d-flex align-items-center gap-1">
            <a href="{{ $divisi->url(1) }}" class="btn btn-sm p-1 {{ $divisi->onFirstPage() ? 'text-muted disabled' : 'text-primary' }}" title="Halaman Pertama">
                <i class="bx bx-chevrons-left" style="font-size: 20px;"></i>
            </a>

            <a href="{{ $divisi->previousPageUrl() }}" class="btn btn-sm p-1 {{ $divisi->onFirstPage() ? 'text-muted disabled' : 'text-primary' }}" title="Halaman Sebelumnya">
                <i class="bx bx-chevron-left" style="font-size: 20px;"></i>
            </a>

            <div class="mx-1">
                <span class="btn btn-primary btn-sm px-3 fw-bold" style="border-radius: 8px;">{{ $divisi->currentPage() }}</span>
            </div>

            <a href="{{ $divisi->nextPageUrl() }}" class="btn btn-sm p-1 {{ !$divisi->hasMorePages() ? 'text-muted disabled' : 'text-primary' }}" title="Halaman Selanjutnya">
                <i class="bx bx-chevron-right" style="font-size: 20px;"></i>
            </a>

            <a href="{{ $divisi->url($divisi->lastPage()) }}" class="btn btn-sm p-1 {{ !$divisi->hasMorePages() ? 'text-muted disabled' : 'text-primary' }}" title="Halaman Terakhir">
                <i class="bx bx-chevrons-right" style="font-size: 20px;"></i>
            </a>
        </div>
    </div>
</x-layout>