
 //SpecRabDetailManager - Manager class for Spesifikasi RAB Detail module
 
class SpecRabDetailManager {
    constructor() {
        this.stateManager = window.StateManagers?.specRabDetail;
        this.isSubmitting = false;
        this.searchTimeout = null;
        this.deleteTarget = null;

        // Pagination variables
        this.currentPage = 1;
        this.totalPages = 1;
        this.perPage = 10;
        this.currentSearch = '';

        // Configuration
        this.config = {
            pageType: 'index'
        };

        // Elements cache
        this.elements = {};

        // URL configuration
        this.urls = {
            index: '/specrabdetail',
            store: '/specrabdetail',
            show: '/specrabdetail',   // /{cost_element}
            update: '/specrabdetail', // /{cost_element}
            destroy: '/specrabdetail', // /{cost_element}
            activeSpecs: '/api/specrabdetail/active-specs'
        };

        // Loaded specs for dropdown
        this.specsData = [];
    }

    init(config = {}) {
        this.setConfig(config);
        this.bindElements();
        this.initializeEventHandlers();
        this.loadActiveSpecs();

        if (config.pageType === 'index') {
            this.updatePaginationButtons();
        }

        console.log('SpecRabDetail Manager initialized:', this.config);
    }

    setConfig(config) {
        this.config = { ...this.config, ...config };
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
    }

    bindElements() {
        this.elements.form = $('#specRabDetailForm');
        this.elements.formModal = $('#formModal');
        this.elements.viewModal = $('#viewModal');
        this.elements.deleteModal = $('#deleteConfirmModal');
        this.elements.searchInput = $('#searchInput');
        this.elements.perPageSelect = $('#perPageSelect');
        this.elements.tableBody = $('#specRabDetailTableBody');
        this.elements.pageButtons = {
            first: $('#firstPageBtn'),
            prev: $('#prevPageBtn'),
            next: $('#nextPageBtn'),
            last: $('#lastPageBtn')
        };
        this.elements.entriesInfo = {
            from: $('#entriesFrom'),
            to: $('#entriesTo'),
            total: $('#entriesTotal')
        };
    }

    // ========================================
    // EVENT HANDLERS
    // ========================================

    initializeEventHandlers() {
        // Add new button
        $('#btnAddNew').on('click', () => this.openAddModal());

        // Form submit
        this.elements.form.on('submit', (e) => {
            e.preventDefault();
            this.submitForm();
        });

        // Search with debounce
        this.elements.searchInput.on('input', (e) => {
            clearTimeout(this.searchTimeout);
            const searchValue = $(e.target).val();
            this.showLoadingSpinner(true);

            this.searchTimeout = setTimeout(() => {
                this.currentSearch = searchValue;
                this.currentPage = 1;
                this.loadData();
            }, 500);
        });

        // Per page change
        this.elements.perPageSelect.on('change', (e) => {
            this.perPage = $(e.target).val();
            this.currentPage = 1;
            this.loadData();
        });

        // Pagination handlers
        this.initializePaginationHandlers();

        // Delete confirmation
        $('#confirmDeleteBtn').on('click', () => {
            if (this.deleteTarget) {
                this.performDelete(this.deleteTarget.cost_element);
            }
        });

        // Modal events
        this.elements.formModal.on('hidden.bs.modal', () => {
            this.resetForm();
        });
    }

    initializePaginationHandlers() {
        $('#firstPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage = 1;
                this.loadData();
            }
        });

        $('#prevPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadData();
            }
        });

        $('#nextPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadData();
            }
        });

        $('#lastPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage = this.totalPages;
                this.loadData();
            }
        });
    }

    // ========================================
    // DATA LOADING
    // ========================================

    loadActiveSpecs() {
        $.ajax({
            url: this.urls.activeSpecs,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    this.specsData = response.data;
                    this.populateSpecsDropdown();
                }
            },
            error: (xhr) => {
                console.error('Error loading specs:', xhr);
            }
        });
    }

    populateSpecsDropdown() {
        const select = $('#id_spec');
        select.empty();
        select.append('<option value="">-- Pilih Spesifikasi RAB --</option>');

        this.specsData.forEach(spec => {
            const kategoriLabel = spec.kategori === 'PDP' ? 'Pendapatan' : 'HPP';
            select.append(`<option value="${spec.id_spec}">${spec.id_spec} - ${this.escapeHtml(spec.spec_rab)} (${kategoriLabel})</option>`);
        });
    }

    loadData() {
        this.showLoadingSpinner(true);

        const params = {
            search: this.currentSearch,
            per_page: this.perPage,
            page: this.currentPage
        };

        $.ajax({
            url: this.urls.index,
            type: 'GET',
            data: params,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: (response) => {
                this.updateTableContent(response.data);
                this.updatePaginationInfo(response.pagination);
                this.updatePaginationButtons();
            },
            error: (xhr) => {
                console.error('Error loading data:', xhr);
                this.showAlert('Gagal memuat data.', 'danger');
            },
            complete: () => {
                this.showLoadingSpinner(false);
            }
        });
    }

    // ========================================
    // TABLE UPDATE
    // ========================================

    updateTableContent(data) {
        const tbody = $('#specRabDetailTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data spesifikasi RAB detail</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        const startIndex = (this.currentPage - 1) * this.perPage;
        data.forEach((item, index) => {
            const specDescription = item.spesifikasi_rab?.spec_rab ?? '-';
            const statusBadge = item.status === 'A'
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-secondary">Non Aktif</span>';

            const row = `
                <tr class="editable-row" ondblclick="editSpecRabDetail('${item.cost_element}')" title="Double-click untuk edit" style="cursor: pointer;">
                    <td>${startIndex + index + 1}</td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(specDescription)}">
                            <strong>${this.escapeHtml(item.id_spec)}</strong> - ${this.escapeHtml(specDescription)}
                        </div>
                    </td>
                    <td>${this.escapeHtml(item.cost_element)}</td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.description_ce ?? '-')}">
                            ${this.escapeHtml(item.description_ce ?? '-')}
                        </div>
                    </td>
                    <td onclick="event.stopPropagation();">
                        ${statusBadge}
                    </td>
                    <td onclick="event.stopPropagation();">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewSpecRabDetail('${item.cost_element}')">
                                    <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="editSpecRabDetail('${item.cost_element}')">
                                    <i class="bx bx-edit me-1"></i> Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteSpecRabDetail('${item.cost_element}')">
                                    <i class="bx bx-trash me-1"></i> Hapus</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    updatePaginationInfo(pagination) {
        this.currentPage = pagination.current_page;
        this.totalPages = pagination.last_page;

        this.elements.entriesInfo.from.text(pagination.from || 0);
        this.elements.entriesInfo.to.text(pagination.to || 0);
        this.elements.entriesInfo.total.text(pagination.total);
    }

    updatePaginationButtons() {
        this.elements.pageButtons.first.prop('disabled', this.currentPage <= 1);
        this.elements.pageButtons.prev.prop('disabled', this.currentPage <= 1);
        this.elements.pageButtons.next.prop('disabled', this.currentPage >= this.totalPages);
        this.elements.pageButtons.last.prop('disabled', this.currentPage >= this.totalPages);

        this.generatePageNumbers();

        if (this.totalPages <= 0) {
            $('#paginationControls').hide();
        } else {
            $('#paginationControls').show();
        }
    }

    generatePageNumbers() {
        const container = $('#pageNumbersContainer');
        container.empty();

        let startPage, endPage;
        const isMobile = window.innerWidth <= 576;
        const maxVisiblePages = isMobile ? 3 : 5;

        if (this.totalPages <= maxVisiblePages) {
            startPage = 1;
            endPage = this.totalPages;
        } else {
            const halfVisible = Math.floor(maxVisiblePages / 2);
            startPage = Math.max(1, this.currentPage - halfVisible);
            endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
        }

        if (startPage > 1 && !isMobile) {
            container.append(`<button type="button" class="btn btn-outline-secondary btn-sm" disabled>...</button>`);
        }

        for (let i = startPage; i <= endPage; i++) {
            const buttonClass = i === this.currentPage
                ? 'btn btn-primary btn-sm page-number-btn'
                : 'btn btn-outline-secondary btn-sm page-number-btn';
            container.append(`<button type="button" class="${buttonClass}" data-page="${i}">${i}</button>`);
        }

        if (endPage < this.totalPages && !isMobile) {
            container.append(`<button type="button" class="btn btn-outline-secondary btn-sm" disabled>...</button>`);
        }

        $('.page-number-btn').off('click').on('click', (e) => {
            const page = parseInt($(e.currentTarget).data('page'));
            if (page !== this.currentPage) {
                this.currentPage = page;
                this.loadData();
            }
        });
    }

    // ========================================
    // MODAL OPERATIONS
    // ========================================

    openAddModal() {
        $('#formMode').val('add');
        $('#formModalLabel').text('Tambah Spesifikasi RAB Detail');
        $('#id_spec').prop('disabled', false); // Can select group
        $('#cost_element').prop('readonly', false); // Can set cost element
        this.resetForm();
        // Ensure any other modal is closed so only one detail modal is open
        $('.modal.show').modal('hide');
        this.elements.formModal.modal('show');
    }

    openEditModal(cost_element) {
        $('#formMode').val('edit');
        $('#formModalLabel').text('Edit Spesifikasi RAB Detail');
        $('#originalCostElement').val(cost_element);

        // Make ONLY cost_element readonly (PK), but id_spec (group) editable
        $('#cost_element').prop('readonly', true);
        $('#id_spec').prop('disabled', false);

        // Load data
        $.ajax({
            url: `${this.urls.show}/${cost_element}`,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    const data = response.data;
                    $('#id_spec').val(data.id_spec);
                    $('#cost_element').val(data.cost_element);
                    $('#description_ce').val(data.description_ce || '');
                    $('#status').val(data.status || 'A');
                    // Close any open modal first so only this one opens
                    $('.modal.show').modal('hide');
                    this.elements.formModal.modal('show');
                }
            },
            error: (xhr) => {
                console.error('Error loading detail:', xhr);
                this.showAlert('Gagal memuat data.', 'danger');
            }
        });
    }

    viewSpecRabDetail(cost_element) {
        $.ajax({
            url: `${this.urls.show}/${cost_element}`,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    this.displayViewModal(response.data);
                    // Ensure only one modal is visible at a time
                    $('.modal.show').modal('hide');
                    this.elements.viewModal.modal('show');
                }
            },
            error: (xhr) => {
                console.error('Error loading detail:', xhr);
                this.showAlert('Gagal memuat detail.', 'danger');
            }
        });
    }

    displayViewModal(data) {
        const specDescription = data.spesifikasi_rab?.spec_rab ?? '-';
        const kategori = data.spesifikasi_rab?.kategori ?? '-';
        const kategoriLabel = kategori === 'PDP' ? 'Pendapatan' : kategori === 'HPP' ? 'Harga Pokok Penjualan' : '-';

        const content = `
            <div class="row">
                <div class="col-md-6">
                    <div class="modal-info-section">
                        <h6>Informasi Spesifikasi</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">ID Spec:</td>
                                <td><strong>${this.escapeHtml(data.id_spec)}</strong></td>
                            </tr>
                            <tr>
                                <td>Spesifikasi RAB:</td>
                                <td>${this.escapeHtml(specDescription)}</td>
                            </tr>
                            <tr>
                                <td>Kategori:</td>
                                <td><span class="badge ${kategori === 'PDP' ? 'bg-success' : 'bg-info'}">${kategoriLabel}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="modal-info-section">
                        <h6>Detail Cost Element</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%">Cost Element:</td>
                                <td><strong>${this.escapeHtml(data.cost_element)}</strong></td>
                            </tr>
                            <tr>
                                <td>Deskripsi:</td>
                                <td>${this.escapeHtml(data.description_ce ?? '-')}</td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td><span class="badge ${data.status === 'A' ? 'bg-success' : 'bg-secondary'}">${data.status === 'A' ? 'Aktif' : 'Non Aktif'}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        `;

        $('#viewModalContent').html(content);
    }

    deleteSpecRabDetail(cost_element) {
        this.deleteTarget = { cost_element };
        // Close any other open modal before showing delete confirmation
        $('.modal.show').modal('hide');
        this.elements.deleteModal.modal('show');
    }

    performDelete(cost_element) {
        $.ajax({
            url: `${this.urls.destroy}/${cost_element}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                this.elements.deleteModal.modal('hide');
                if (response.success) {
                    this.showAlert(response.message, 'success');
                    this.loadData();
                }
            },
            error: (xhr) => {
                this.elements.deleteModal.modal('hide');
                console.error('Error deleting:', xhr);
                this.showAlert('Gagal menghapus data.', 'danger');
            }
        });
    }

    // ========================================
    // FORM SUBMISSION
    // ========================================

    submitForm() {
        this.clearAllErrors();

        const mode = $('#formMode').val();
        const formData = {
            id_spec: $('#id_spec').val(),
            cost_element: $('#cost_element').val(),
            description_ce: $('#description_ce').val(),
            status: $('#status').val()
        };

        // Validation
        let isValid = true;
        if (!formData.id_spec) {
            this.showFieldError('id_spec', 'ID Spec harus dipilih.');
            isValid = false;
        }
        if (!formData.cost_element) {
            this.showFieldError('cost_element', 'Cost Element harus diisi.');
            isValid = false;
        }

        if (!isValid) return;

        this.setSubmitLoading(true);

        let url, method;
        if (mode === 'add') {
            url = this.urls.store;
            method = 'POST';
        } else {
            const origCostElement = $('#originalCostElement').val();
            url = `${this.urls.update}/${origCostElement}`;
            method = 'PUT';
        }

        $.ajax({
            url: url,
            type: method,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                if (response.success) {
                    this.elements.formModal.modal('hide');
                    this.showAlert(response.message, 'success');
                    this.loadData();
                }
            },
            error: (xhr) => {
                this.setSubmitLoading(false);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        Object.keys(errors).forEach(key => {
                            this.showFieldError(key, errors[key][0]);
                        });
                    }
                } else {
                    this.showAlert('Terjadi kesalahan saat menyimpan data.', 'danger');
                }
            },
            complete: () => {
                this.setSubmitLoading(false);
            }
        });
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    resetForm() {
        this.elements.form[0].reset();
        this.clearAllErrors();
        $('#id_spec').prop('disabled', false).val('');
        $('#cost_element').prop('readonly', false).val('');
        $('#status').val('A');
    }

    setSubmitLoading(loading) {
        this.isSubmitting = loading;
        const btn = $('#submitBtn');
        const spinner = $('#submitSpinner');
        const icon = $('#submitIcon');
        const text = $('#submitText');

        if (loading) {
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            icon.addClass('d-none');
            text.text('Menyimpan...');
        } else {
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            icon.removeClass('d-none');
            text.text('Simpan');
        }
    }

    showLoadingSpinner(show) {
        const spinner = $('.loading-spinner');
        if (show) {
            spinner.addClass('active');
        } else {
            spinner.removeClass('active');
        }
    }

    escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="z-index: 9999; position: relative; margin-bottom: 20px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('.alert').remove();

        // Check if modal is open - prepend to modal body's container or before form
        if ($('.modal.show').length > 0) {
            // Find the visible modal
            const $modal = $('.modal.show');
            const $body = $modal.find('.modal-body');

            // Prepend to top of modal body
            $body.prepend(alertHtml);

            // Scroll to top of modal
            $modal.animate({ scrollTop: 0 }, 300);
        } else {
            // Normal behavior
            if ($('.sticky-header').length) {
                $('.sticky-header').after(alertHtml);
            } else {
                $('main').prepend(alertHtml);
            }
            $('html, body').animate({ scrollTop: 0 }, 300);
        }

        setTimeout(() => {
            $('.alert').fadeOut(300, function () {
                $(this).remove();
            });
        }, 5000);
    }

    showFieldError(fieldName, message) {
        $(`#${fieldName}`).addClass('is-invalid');
        $(`#${fieldName}-error`).text(message);
    }

    clearFieldError(fieldName) {
        $(`#${fieldName}`).removeClass('is-invalid');
        $(`#${fieldName}-error`).text('');
    }

    clearAllErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
}

// ========================================
// GLOBAL FUNCTIONS (for onclick handlers)
// ========================================

window.editSpecRabDetail = function (cost_element) {
    if (window.specRabDetailManager) {
        window.specRabDetailManager.openEditModal(cost_element);
    }
};

window.viewSpecRabDetail = function (cost_element) {
    if (window.specRabDetailManager) {
        window.specRabDetailManager.viewSpecRabDetail(cost_element);
    }
};

window.deleteSpecRabDetail = function (cost_element) {
    if (window.specRabDetailManager) {
        window.specRabDetailManager.deleteSpecRabDetail(cost_element);
    }
};
