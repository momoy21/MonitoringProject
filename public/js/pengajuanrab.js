/**
 * Pengajuan RAB Manager
 * Handles all Pengajuan RAB related operations
 */
class PengajuanRABManager {
    constructor() {
        this.config = {
            baseUrl: '/pengajuanrab',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            pageType: 'index',
            currentPage: 1,
            totalPages: 1,
            perPage: 10,
            currentSearch: ''
        };

        this.searchTimeout = null;
        this.isLoading = false;
    }

    /**
     * Initialize the manager with configuration
     */
    init(options = {}) {
        Object.assign(this.config, options);

        if (this.config.pageType === 'index') {
            this.initIndexPage();
        } else if (this.config.pageType === 'create' || this.config.pageType === 'edit') {
            this.initFormPage();
        }
    }

    /**
     * Initialize index page
     */
    initIndexPage() {
        this.bindSearchEvents();
        this.bindPaginationEvents();
        this.bindPerPageEvents();
        this.updatePaginationUI();
    }

    /**
     * Initialize form page (create/edit)
     */
    initFormPage() {
        this.bindFormEvents();
    }

    /**
     * Bind search events
     */
    bindSearchEvents() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput) return;

        searchInput.value = this.config.currentSearch;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            const spinner = document.querySelector('.loading-spinner');
            if (spinner) spinner.style.display = 'block';

            this.searchTimeout = setTimeout(() => {
                this.config.currentSearch = e.target.value;
                this.config.currentPage = 1;
                this.loadData();
            }, 300);
        });
    }

    /**
     * Bind pagination events
     */
    bindPaginationEvents() {
        // First page
        document.getElementById('firstPageBtn')?.addEventListener('click', () => {
            if (this.config.currentPage > 1) {
                this.config.currentPage = 1;
                this.loadData();
            }
        });

        // Previous page
        document.getElementById('prevPageBtn')?.addEventListener('click', () => {
            if (this.config.currentPage > 1) {
                this.config.currentPage--;
                this.loadData();
            }
        });

        // Next page
        document.getElementById('nextPageBtn')?.addEventListener('click', () => {
            if (this.config.currentPage < this.config.totalPages) {
                this.config.currentPage++;
                this.loadData();
            }
        });

        // Last page
        document.getElementById('lastPageBtn')?.addEventListener('click', () => {
            if (this.config.currentPage < this.config.totalPages) {
                this.config.currentPage = this.config.totalPages;
                this.loadData();
            }
        });

        // Page number buttons
        document.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.dataset.page);
                if (page !== this.config.currentPage) {
                    this.config.currentPage = page;
                    this.loadData();
                }
            });
        });
    }

    /**
     * Bind per page select events
     */
    bindPerPageEvents() {
        const perPageSelect = document.getElementById('perPageSelect');
        if (!perPageSelect) return;

        perPageSelect.addEventListener('change', (e) => {
            this.config.perPage = parseInt(e.target.value);
            this.config.currentPage = 1;
            this.loadData();
        });
    }

    /**
     * Load data via AJAX
     */
    loadData() {
        if (this.isLoading) return;
        this.isLoading = true;

        const params = new URLSearchParams({
            page: this.config.currentPage,
            per_page: this.config.perPage,
            search: this.config.currentSearch
        });

        fetch(`${this.config.baseUrl}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.renderTable(data.data);
                this.updatePagination(data.pagination);
            }
        })
        .catch(error => {
            console.error('Error loading data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memuat data.'
            });
        })
        .finally(() => {
            this.isLoading = false;
            const spinner = document.querySelector('.loading-spinner');
            if (spinner) spinner.style.display = 'none';
        });
    }

    /**
     * Render table with data
     */
    renderTable(data) {
        const tbody = document.getElementById('pengajuanRabTableBody');
        if (!tbody) return;

        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-folder-open mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data pengajuan RAB</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map(item => this.renderRow(item)).join('');
    }

    /**
     * Render single row
     */
    renderRow(item) {
        const tglInput = item.tgl_input ? new Date(item.tgl_input).toLocaleDateString('id-ID') : '-';
        const namaProject = item.nama_project || '-';
        const truncatedProject = namaProject.length > 40 ? namaProject.substring(0, 40) + '...' : namaProject;
        const divisiNama = item.master_divisi?.nama_divisi || '-';
        const konsumenNama = item.konsumen?.konsumen || '-';

        return `
            <tr class="editable-row" ondblclick="editPengajuanRab('${item.nopengajuan}')" title="Double-click untuk edit" style="cursor: pointer;">
                <td>${tglInput}</td>
                <td><span class="fw-bold text-primary">${item.nopengajuan}</span></td>
                <td>${item.dokumen_io || '-'}</td>
                <td><span class="fw-bold">${item.cost_center}</span></td>
                <td>
                    <div class="truncate-text" title="${namaProject}" style="max-width: 200px;">
                        ${truncatedProject}
                    </div>
                </td>
                <td>${divisiNama}</td>
                <td>${konsumenNama}</td>
                <td onclick="event.stopPropagation();">${this.getProgressBadge(item.progress)}</td>
                <td onclick="event.stopPropagation();">${this.getHasilPlenoBadge(item.hasil_pleno)}</td>
                <td onclick="event.stopPropagation();" class="text-center">
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="${this.config.baseUrl}/${item.nopengajuan}">
                                <i class="bx bx-show-alt me-1"></i> Detail
                            </a>
                            <a class="dropdown-item" href="${this.config.baseUrl}/${item.nopengajuan}/edit">
                                <i class="bx bx-edit-alt me-1"></i> Edit
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deletePengajuanRab('${item.nopengajuan}')">
                                <i class="bx bx-trash me-1"></i> Hapus
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Get progress badge HTML
     */
    getProgressBadge(progress) {
        const badges = {
            '01': '<span class="badge bg-warning">Dokumen belum diterima</span>',
            '02': '<span class="badge bg-info">Proses TTD BOD</span>',
            '03': '<span class="badge bg-primary">Revisi RAB</span>',
            '04': '<span class="badge bg-success">Done</span>'
        };
        return badges[progress] || '<span class="badge bg-secondary">-</span>';
    }

    /**
     * Get hasil pleno badge HTML
     */
    getHasilPlenoBadge(hasilPleno) {
        const badges = {
            'TT': '<span class="badge bg-danger">Tidak Tercapai RKAP</span>',
            'TR': '<span class="badge bg-success">Tercapai RKAP</span>'
        };
        return badges[hasilPleno] || '<span class="badge bg-secondary">-</span>';
    }

    /**
     * Update pagination info and buttons
     */
    updatePagination(pagination) {
        this.config.currentPage = pagination.current_page;
        this.config.totalPages = pagination.last_page;

        // Update entries info
        document.getElementById('entriesFrom').textContent = pagination.from || 0;
        document.getElementById('entriesTo').textContent = pagination.to || 0;
        document.getElementById('entriesTotal').textContent = pagination.total;

        this.updatePaginationUI();
        this.renderPageNumbers();
    }

    /**
     * Update pagination UI state
     */
    updatePaginationUI() {
        const firstBtn = document.getElementById('firstPageBtn');
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        const lastBtn = document.getElementById('lastPageBtn');

        if (firstBtn) firstBtn.disabled = this.config.currentPage <= 1;
        if (prevBtn) prevBtn.disabled = this.config.currentPage <= 1;
        if (nextBtn) nextBtn.disabled = this.config.currentPage >= this.config.totalPages;
        if (lastBtn) lastBtn.disabled = this.config.currentPage >= this.config.totalPages;
    }

    /**
     * Render page number buttons
     */
    renderPageNumbers() {
        const container = document.getElementById('pageNumbersContainer');
        if (!container) return;

        let html = '';
        const maxVisible = 5;
        let start = Math.max(1, this.config.currentPage - Math.floor(maxVisible / 2));
        let end = Math.min(this.config.totalPages, start + maxVisible - 1);

        if (end - start < maxVisible - 1) {
            start = Math.max(1, end - maxVisible + 1);
        }

        for (let i = start; i <= end; i++) {
            const activeClass = i === this.config.currentPage ? 'btn-primary' : 'btn-outline-secondary';
            html += `<button type="button" class="btn btn-sm ${activeClass} page-btn" data-page="${i}">${i}</button>`;
        }

        container.innerHTML = html;

        // Re-bind page button events
        container.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.dataset.page);
                if (page !== this.config.currentPage) {
                    this.config.currentPage = page;
                    this.loadData();
                }
            });
        });
    }

    /**
     * Bind form events
     */
    bindFormEvents() {
        const form = document.getElementById('pengajuanRabForm');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            // Clean currency format before submitting (remove thousand separators)
            const nilaiProyekInput = document.getElementById('nilai_proyek');
            if (nilaiProyekInput && nilaiProyekInput.value) {
                nilaiProyekInput.value = nilaiProyekInput.value.replace(/\./g, '');
            }

            const submitBtn = document.getElementById('submitBtn');
            const submitSpinner = document.getElementById('submitSpinner');
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');

            if (submitBtn) submitBtn.disabled = true;
            if (submitSpinner) submitSpinner.classList.remove('d-none');
            if (submitIcon) submitIcon.classList.add('d-none');
            if (submitText) submitText.textContent = this.config.pageType === 'create' ? 'Menyimpan...' : 'Memperbarui...';
        });
    }
}

// Export for global access
window.PengajuanRABManager = PengajuanRABManager;
