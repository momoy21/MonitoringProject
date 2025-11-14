<x-layout title="Bidang Jasa">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Bidang Jasa</h4>
                <p class="mb-0">Kelola data bidang jasa sistem</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari bidang jasa..." autocomplete="off">
                            <div class="loading-spinner">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add Button -->
                    <a href="{{ route('bidangjasa.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah
                    </a>
                </div>
            </div>
        </div>

        <!-- Per Page & Info -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <label for="perPageSelect" class="form-label mb-0">Tampilkan:</label>
                    <select id="perPageSelect" class="form-select per-page-selector">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data per halaman</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card">
        <div class="table-responsive bidangjasa-table-container">
            <table class="table table-striped table-hover bidangjasa-table">
                <thead>
                    <tr>
                        <th class="fw-bold">ID Bidang Jasa</th>
                        <th class="fw-bold">Deskripsi Bidang Jasa</th>
                        <th class="fw-bold">Status</th>
                    </tr>
                </thead>
                <tbody id="bidangJasaTableBody">
                    @forelse($bidangjasa as $item)
                    <tr>
                        <td>
                            <span class="bidangjasa-id fw-bold" data-id="{{ $item->id_bidjasa }}">
                                {{ $item->id_bidjasa }}
                            </span>
                        </td>
                        <td>
                            <div class="truncate-text" title="{{ $item->desc_bidjasa }}">
                                {{ $item->desc_bidjasa }}
                            </div>
                        </td>
                        <td>
                            @if($item->status === 'A')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Non Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                                <p class="mb-0 empty-state-text">Tidak ada data bidang jasa</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls - Responsive -->
    <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2" id="paginationControls">
        <!-- Left: Showing entries info -->
        <div class="pagination-info">
            <span class="text-muted medium">
                Menampilkan <span id="entriesFrom">{{ $bidangjasa->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $bidangjasa->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $bidangjasa->total() }}</span> data
            </span>
        </div>

        <!-- Right: Navigation buttons -->
        <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center justify-content-md-end">
            <!-- First Page - Hidden on small screens -->
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="firstPageBtn" title="Halaman Pertama">
                <i class="bx bx-chevrons-left"></i>
            </button>

            <!-- Previous Page -->
            <button type="button" class="btn btn-outline-secondary btn-sm" id="prevPageBtn" title="Halaman Sebelumnya">
                <i class="bx bx-chevron-left"></i>
            </button>

            <!-- Page Numbers Container -->
            <div class="d-flex align-items-center gap-1 mx-1 mx-md-2" id="pageNumbersContainer">
                <!-- Page numbers will be generated here -->
            </div>

            <!-- Next Page -->
            <button type="button" class="btn btn-outline-secondary btn-sm" id="nextPageBtn" title="Halaman Selanjutnya">
                <i class="bx bx-chevron-right"></i>
            </button>

            <!-- Last Page - Hidden on small screens -->
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="lastPageBtn" title="Halaman Terakhir">
                <i class="bx bx-chevrons-right"></i>
            </button>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/bidangjasa.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Initialize bidang jasa manager dengan konfigurasi untuk halaman index
        window.bidangJasaManager = new BidangJasaManager();

        window.bidangJasaManager.init({
            pageType: 'index',
            currentPage: {{ $bidangjasa->currentPage() }},
            totalPages: {{ $bidangjasa->lastPage() }},
            perPage: {{ request('per_page', 10) }},
            currentSearch: '{{ request('search') }}'
        });
    });
    </script>
    @endpush
</x-layout>
