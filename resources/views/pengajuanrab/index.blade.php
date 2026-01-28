<x-layout title="Pengajuan RAB">
    @push('styles')
    <style>
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .filter-section .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }
    </style>
    @endpush

    <!-- Header Section -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Daftar Pengajuan Pleno RAB</h4>
                <p class="mb-0">Kelola data pengajuan rencana anggaran biaya proyek</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('pengajuanrab.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form id="filterForm" method="GET" action="{{ route('pengajuanrab.index') }}">
            <div class="row align-items-end">
                <!-- Filter Cost Center -->
                <div class="col-md-3">
                    <label for="filterCostCenter" class="form-label">Cost Center</label>
                    <input type="text" class="form-control" id="filterCostCenter" name="cost_center" 
                           value="{{ request('cost_center') }}" placeholder="Cari cost center...">
                </div>

                <!-- Filter Nama Proyek -->
                <div class="col-md-3">
                    <label for="filterNamaProyek" class="form-label">Nama Proyek</label>
                    <input type="text" class="form-control" id="filterNamaProyek" name="nama_proyek" 
                           value="{{ request('nama_proyek') }}" placeholder="Cari nama proyek...">
                </div>

                <!-- Filter Konsumen -->
                <div class="col-md-3">
                    <label for="filterKonsumen" class="form-label">Konsumen</label>
                    <select class="form-select" id="filterKonsumen" name="id_konsumen">
                        <option value="">-- Semua Konsumen --</option>
                        @foreach($konsumenList as $k)
                            <option value="{{ $k->id_konsumen }}" {{ request('id_konsumen') == $k->id_konsumen ? 'selected' : '' }}>
                                {{ $k->konsumen }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-info">
                            <i class="bx bx-search me-1"></i> Cari
                        </button>
                        <a href="{{ route('pengajuanrab.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Per Page Selector -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-2">
                <label for="perPageSelect" class="form-label mb-0">Tampilkan:</label>
                <select id="perPageSelect" class="form-select per-page-selector" style="width: auto;">
                    <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span>data per halaman</span>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="fw-bold text-center" style="width: 50px;">No</th>
                        <th class="fw-bold">Tanggal Pengajuan</th>
                        <th class="fw-bold">Nomor</th>
                        <th class="fw-bold">Cost Center</th>
                        <th class="fw-bold">Nama Proyek</th>
                        <th class="fw-bold">Divisi</th>
                        <th class="fw-bold">Konsumen</th>
                        <th class="fw-bold text-center">Hasil Pleno</th>
                        <th class="fw-bold text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pengajuanRabTableBody">
                    @forelse($pengajuanRab as $index => $item)
                    <tr class="editable-row" ondblclick="window.location.href='{{ route('pengajuanrab.show', $item->nopengajuan) }}'" title="Double-click untuk lihat detail" style="cursor: pointer;">
                        <td class="text-center">{{ $pengajuanRab->firstItem() + $index }}</td>
                        <td>{{ $item->tgl_input_formatted }}</td>
                        <td>
                            <span class="fw-bold text-primary">{{ $item->nopengajuan }}</span>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $item->cost_center }}</span>
                        </td>
                        <td>
                            <div class="truncate-text" title="{{ $item->nama_project }}" style="max-width: 200px;">
                                {{ Str::limit($item->nama_project, 35) }}
                            </div>
                        </td>
                        <td>{{ $item->masterDivisi->nama_divisi ?? '-' }}</td>
                        <td>{{ $item->konsumen->konsumen ?? '-' }}</td>
                        <td class="text-center" onclick="event.stopPropagation();">
                            {!! $item->hasil_pleno_badge !!}
                        </td>
                        <td class="text-center" onclick="event.stopPropagation();">
                            <a href="{{ route('pengajuanrab.show', $item->nopengajuan) }}" 
                               class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                <i class="bx bx-show"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-folder-open mb-2" style="font-size: 48px; color: #a1a5b7;"></i>
                                <p class="mb-0 text-muted">Tidak ada data pengajuan RAB</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2">
        <!-- Left: Showing entries info -->
        <div class="pagination-info">
            <span class="text-muted">
                Menampilkan {{ $pengajuanRab->firstItem() ?? 0 }} hingga {{ $pengajuanRab->lastItem() ?? 0 }} dari {{ $pengajuanRab->total() }} data
            </span>
        </div>

        <!-- Right: Pagination links -->
        <div>
            {{ $pengajuanRab->appends(request()->query())->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        // Per page change
        $('#perPageSelect').on('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', $(this).val());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });
    });
    </script>
    @endpush
</x-layout>
