<x-layout title="RAB Detail">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Spesifikasi RAB Detail</h4>
                <p class="mb-0">Kelola data pemetaan cost element terhadap spesifikasi RAB</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari spesifikasi RAB..." autocomplete="off">
                            <div class="loading-spinner">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add Button -->
                    <button type="button" class="btn btn-primary" id="btnAddNew">
                        <i class="bx bx-plus me-1"></i> Tambah
                    </button>
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
    <div class="card" style="overflow: visible;">
        <div class="table-responsive specrabdetail-table-container" style="overflow: visible;">
            <table class="table table-striped table-hover specrabdetail-table">
                <thead>
                    <tr>
                        <th class="fw-bold">No</th>
                        <th class="fw-bold">Spesifikasi</th>
                        <th class="fw-bold">Cost Element</th>
                        <th class="fw-bold">Deskripsi Cost Element</th>
                        <th class="fw-bold">Status</th>
                        <th class="fw-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="specRabDetailTableBody">
                    @forelse($details as $index => $item)
                    <tr class="editable-row" 
                        ondblclick="editSpecRabDetail('{{ $item->cost_element }}')" 
                        title="Double-click untuk edit" 
                        style="cursor: pointer;">
                        <td>{{ $details->firstItem() + $index }}</td>
                        <td>
                            <div class="truncate-text" title="{{ $item->spesifikasiRab?->spec_rab ?? '-' }}">
                                <strong>{{ $item->id_spec }}</strong> - {{ $item->spesifikasiRab?->spec_rab ?? '-' }}
                            </div>
                        </td>
                        <td>{{ $item->cost_element }}</td>
                        <td>
                            <div class="truncate-text" title="{{ $item->description_ce ?? '-' }}">
                                {{ $item->description_ce ?? '-' }}
                            </div>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <span class="badge {{ $item->status === 'A' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $item->status === 'A' ? 'Aktif' : 'Non Aktif' }}
                            </span>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewSpecRabDetail('{{ $item->cost_element }}')">
                                        <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="editSpecRabDetail('{{ $item->cost_element }}')">
                                        <i class="bx bx-edit me-1"></i> Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteSpecRabDetail('{{ $item->cost_element }}')">
                                        <i class="bx bx-trash me-1"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                                <p class="mb-0 empty-state-text">Tidak ada data spesifikasi RAB detail</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2" id="paginationControls">
        <div class="pagination-info">
            <span class="text-muted medium">
                Menampilkan <span id="entriesFrom">{{ $details->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $details->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $details->total() }}</span> data
            </span>
        </div>
        <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center justify-content-md-end">
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="firstPageBtn" title="Halaman Pertama">
                <i class="bx bx-chevrons-left"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="prevPageBtn" title="Halaman Sebelumnya">
                <i class="bx bx-chevron-left"></i>
            </button>
            <div class="d-flex align-items-center gap-1 mx-1 mx-md-2" id="pageNumbersContainer"></div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="nextPageBtn" title="Halaman Selanjutnya">
                <i class="bx bx-chevron-right"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="lastPageBtn" title="Halaman Terakhir">
                <i class="bx bx-chevrons-right"></i>
            </button>
        </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">Tambah Spesifikasi RAB Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="specRabDetailForm">
                    <div class="modal-body">
                        <input type="hidden" id="formMode" value="add">
                        <input type="hidden" id="originalCostElement" value="">
                        
                        <!-- ID Spec Dropdown -->
                        <div class="mb-3">
                            <label for="id_spec" class="form-label">ID Spec <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_spec" name="id_spec" required>
                                <option value="">-- Pilih Spesifikasi RAB --</option>
                            </select>
                            <div class="invalid-feedback" id="id_spec-error"></div>
                        </div>

                        <!-- Cost Element -->
                        <div class="mb-3">
                            <label for="cost_element" class="form-label">Cost Element/COA <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cost_element" name="cost_element" maxlength="10" required placeholder="Masukkan cost element">
                            <div class="invalid-feedback" id="cost_element-error"></div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description_ce" class="form-label">Deskripsi Cost Element</label>
                            <textarea class="form-control" id="description_ce" name="description_ce" rows="3" placeholder="Masukkan deskripsi cost element"></textarea>
                            <div class="invalid-feedback" id="description_ce-error"></div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="A" selected>Aktif</option>
                                <option value="N">Non Aktif</option>
                            </select>
                            <div class="invalid-feedback" id="status-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                            <i class="bx bx-save" id="submitIcon"></i>
                            <span id="submitText">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Spesifikasi RAB Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalContent">
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
    <script src="{{ asset('js/specrabdetail.js') }}?v={{ time() }}"></script>
    <script>
    $(document).ready(function() {
        window.specRabDetailManager = new SpecRabDetailManager();
        window.specRabDetailManager.init({
            pageType: 'index',
            currentPage: {{ $details->currentPage() }},
            totalPages: {{ $details->lastPage() }},
            perPage: {{ request('per_page', 10) }},
            currentSearch: '{{ request('search') }}'
        });
    });
    </script>
    @endpush
</x-layout>
