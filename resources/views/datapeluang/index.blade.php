<x-layout title="Data Peluang">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Data Peluang</h4>
                <p class="mb-0">Kelola data peluang bisnis sistem</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari peluang atau konsumen..." autocomplete="off">
                            <div class="loading-spinner">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add Button -->
                    <a href="{{ route('datapeluang.create') }}" class="btn btn-primary">
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
        <div class="table-responsive datapeluang-table-container">
            <table class="table table-striped table-hover datapeluang-table">
                <thead>
                    <tr>
                        <th class="fw-bold">ID Peluang</th>
                        <th class="fw-bold">Nama Peluang</th>
                        <th class="fw-bold">Konsumen</th>
                        <th class="fw-bold">Estimate<br>Biaya Peluang</th>
                        <th class="fw-bold">Nilai Peluang</th>
                        <th class="fw-bold">Tanggal Peluang / Target</th>
                        <th class="fw-bold">Status</th>
                        <th class="fw-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="dataPeluangTableBody">
                    @forelse($dataPeluang as $item)
                    <tr class="editable-row" ondblclick="editDataPeluang('{{ $item->id_datapeluang }}')" title="Double-click untuk edit" style="cursor: pointer;">
                        <td>
                            <span class="datapeluang-id" data-id="{{ $item->id_datapeluang }}">
                                {{ $item->id_datapeluang }}
                            </span>
                        </td>
                        <td>
                            <div class="truncate-text" title="{{ $item->peluang }}">
                                {{ $item->peluang }}
                            </div>
                        </td>
                        <td>
                            <div class="truncate-text" title="{{ $item->konsumen->konsumen ?? '-' }}">
                                {{ $item->konsumen->konsumen ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <small class="currency-display">{!! $item->biaya_peluang_formatted !!}</small>
                        </td>
                        <td>
                            <small class="currency-display">{!! $item->pagu_peluang_formatted !!}</small>
                        </td>
                        <td>
                            <div class="date-container">
                                <div class="date-row">
                                    <small class="text-muted">Tgl:</small>
                                    <small>{{ $item->tgl_peluang->format('d/m/Y') }}</small>
                                </div>
                                <div class="date-row">
                                    <small class="text-muted">Target:</small>
                                    <small>{{ $item->target_peluang->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="{{ $item->status_badge }}">{{ $item->status_label }}</span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewDataPeluang('{{ $item->id_datapeluang }}')">
                                        <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteDataPeluang('{{ $item->id_datapeluang }}')">
                                        <i class="bx bx-trash me-1"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                                <p class="mb-0 empty-state-text">Tidak ada data peluang</p>
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
                Menampilkan <span id="entriesFrom">{{ $dataPeluang->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $dataPeluang->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $dataPeluang->total() }}</span> data
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
    <div class="modal fade" id="viewDataPeluangModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Data Peluang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewDataPeluangContent">
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
                    <hr />
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body border-top border-bottom">
                    <p>Apakah Anda yakin ingin menghapus data peluang ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/datapeluang.js') }}"></script>
    <script>
    $(document).ready(function() {
        // Initialize data peluang manager dengan konfigurasi untuk halaman index
        window.dataPeluangManager = new DataPeluangManager();

        window.dataPeluangManager.init({
            pageType: 'index',
            currentPage: {{ $dataPeluang->currentPage() }},
            totalPages: {{ $dataPeluang->lastPage() }},
            perPage: {{ request('per_page', 10) }},
            currentSearch: '{{ request('search') }}'
        });
    });
    </script>
    @endpush
</x-layout>
