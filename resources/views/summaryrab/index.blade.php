<x-layout title="Summary RAB">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Summary RAB</h4>
                <p class="mb-0">Kelola data summary RAB sistem</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari summary RAB..." autocomplete="off">
                            <div class="loading-spinner">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add Button -->
                    <a href="{{ route('summaryrab.create') }}" class="btn btn-primary">
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
        <div class="table-responsive summaryrab-table-container">
            <table class="table table-striped table-hover summaryrab-table">
                <thead>
                    <tr>
                        <th class="fw-bold">ID Sum</th>
                        <th class="fw-bold">Keterangan Summary RAB</th>
                        <th class="fw-bold">Status</th>
                        <th class="fw-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="summaryRABTableBody">
                    @forelse($summaryrab as $item)
                    <tr>
                        <td>
                            <span class="norutsummary-value" data-idsummary="{{ $item->idsummary }}" ondblclick="editSummaryRAB('{{ $item->idsummary }}')" title="Double-click untuk edit">
                                {{ $item->norutsummary }}
                            </span>
                        </td>
                        <td>
                            <div class="truncate-text" title="{{ $item->ketsummaryrab }}">
                                {{ $item->ketsummaryrab }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $item->status === 'A' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $item->status === 'A' ? 'Aktif' : 'Non Aktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewSummaryRAB('{{ $item->idsummary }}')">
                                        <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                                    {{-- <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteSummaryRAB('{{ $item->idsummary }}')">
                                        <i class="bx bx-trash me-1"></i> Hapus</a></li>
                                </ul> --}}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                                <p class="mb-0 empty-state-text">Tidak ada data summary RAB</p>
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
               Menampilkan <span id="entriesFrom">{{ $summaryrab->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $summaryrab->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $summaryrab->total() }}</span> data
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

    <!-- Modals -->
    <!-- View Modal -->
    <div class="modal fade" id="viewSummaryRABModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Summary RAB</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewSummaryRABContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body border-top border-bottom">
                    <p>Yakin akan dihapus?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/summaryrab.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Initialize summary RAB manager dengan konfigurasi untuk halaman index
        window.summaryRABManager = new SummaryRABManager();

        window.summaryRABManager.init({
            pageType: 'index',
            currentPage: {{ $summaryrab->currentPage() }},
            totalPages: {{ $summaryrab->lastPage() }},
            perPage: {{ request('per_page', 10) }},
            currentSearch: '{{ request('search') }}'
        });
    });
    </script>
    @endpush
</x-layout>
