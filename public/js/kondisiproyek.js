class KondisiProyekManager {
    constructor() {
        this.stateManager = window.StateManagers?.kondisiProyek;
        this.isSubmitting = false;
        this.searchTimeout = null;
        this.deleteTargetId = null;

        // Pagination variables (for index page)
        this.currentPage = 1;
        this.totalPages = 1;
        this.perPage = 10;
        this.currentSearch = '';

        // Form validation variables (for create/edit)
        this.originalDesc = '';
        this.currentKondisiProyekId = '';

        // Configuration
        this.config = {
            pageType: 'index',
            debounceTimer: null
        };

        // Elements cache
        this.elements = {};

        // URL configuration
        this.urls = {
            index: window.routes?.kondisiproyek?.index || '/kondisiproyek',
            store: window.routes?.kondisiproyek?.store || '/kondisiproyek',
            show: window.routes?.kondisiproyek?.show || '/kondisiproyek',
            update: window.routes?.kondisiproyek?.update || '/kondisiproyek',
            destroy: window.routes?.kondisiproyek?.destroy || '/kondisiproyek'
        };
    }

    // ========================================
    // INITIALIZATION METHODS
    // ========================================

    init(config = {}) {
        this.setConfig(config);
        this.bindElements();
        this.initializeEventHandlers();

        let shouldLoadData = false;

        // Initialize page-specific functions
        if (config.pageType === 'index') {
            // Try to restore state
            if (this.stateManager) {
                const savedState = this.stateManager.getState();
                if (savedState && this.stateManager.shouldRestoreState()) {
                    this.currentPage = savedState.currentPage || this.currentPage;
                    this.currentSearch = savedState.currentSearch || this.currentSearch;
                    this.perPage = savedState.perPage || this.perPage;

                    // Update UI with restored state
                    if ($('#searchInput').length) $('#searchInput').val(this.currentSearch);
                    if ($('#perPageSelect').length) $('#perPageSelect').val(this.perPage);

                    shouldLoadData = true;
                    this.stateManager.clearRestoreFlag();
                    console.log('State restored:', savedState);
                }
            }

            this.updatePaginationButtons();
        }

        if (shouldLoadData) {
            this.loadKondisiProyekData();
        }

        console.log('KondisiProyek Manager initialized:', this.config);
    }

    setConfig(config) {
        this.config = { ...this.config, ...config };
        this.originalDesc = config.originalDesc || '';
        this.currentKondisiProyekId = config.currentKondisiProyekId || '';
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
    }

    bindElements() {
        // Common elements
        this.elements.form = $('#kondisiProyekForm');
        this.elements.submitBtn = $('#submitBtn');
        this.elements.submitSpinner = $('#submitSpinner');
        this.elements.submitIcon = $('#submitIcon');
        this.elements.submitText = $('#submitText');

        // Index page elements
        if (this.config.pageType === 'index') {
            this.elements.searchInput = $('#searchInput');
            this.elements.perPageSelect = $('#perPageSelect');
            this.elements.tableBody = $('#kondisiProyekTableBody');
            this.elements.paginationControls = $('#paginationControls');
            this.elements.entriesInfo = {
                from: $('#entriesFrom'),
                to: $('#entriesTo'),
                total: $('#entriesTotal')
            };
            this.elements.pageButtons = {
                first: $('#firstPageBtn'),
                prev: $('#prevPageBtn'),
                next: $('#nextPageBtn'),
                last: $('#lastPageBtn'),
                numbers: $('#pageNumbersContainer')
            };
            this.elements.modals = {
                view: $('#viewKondisiProyekModal'),
                delete: $('#deleteConfirmModal')
            };
            this.elements.confirmDeleteBtn = $('#confirmDeleteBtn');
        }
    }

    // ========================================
    // EVENT HANDLERS
    // ========================================

    initializeEventHandlers() {
        if (this.config.pageType === 'index') {
            this.initializeIndexHandlers();
        } else if (['create', 'edit'].includes(this.config.pageType)) {
            this.initializeFormHandlers();
        } else if (this.config.pageType === 'show') {
            this.initializeShowHandlers();
        }
    }

    initializeIndexHandlers() {
        // Search functionality with proper debounce
        if (this.elements.searchInput.length) {
            this.elements.searchInput.on('input', (e) => {
                const searchValue = $(e.target).val().trim();
                this.currentSearch = searchValue;
                this.currentPage = 1;

                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.loadKondisiProyekData();
                }, 300);
            });

            // Handle Enter key for immediate search
            this.elements.searchInput.on('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(this.searchTimeout);
                    this.loadKondisiProyekData();
                }
            });
        }

        // Per page selector handler
        if (this.elements.perPageSelect.length) {
            this.elements.perPageSelect.on('change', (e) => {
                this.perPage = parseInt($(e.target).val());
                this.currentPage = 1;
                this.loadKondisiProyekData();
            });
        }

        // Pagination handlers
        this.initializePaginationHandlers();

        // Modal handlers
        this.initializeModalHandlers();
    }

    initializeFormHandlers() {
        // Form submit handler
        if (this.elements.form.length) {
            this.elements.form.on('submit', (e) => {
                e.preventDefault();
                if (!this.isSubmitting) {
                    this.submitForm();
                }
            });
        }

        // Real-time validation
        this.initializeFieldValidation();
    }

    initializeShowHandlers() {
        // Any specific handlers for show page can be added here
        console.log('Show page handlers initialized');
    }

    initializeFieldValidation() {
        // Description validation
        $('#desc_kondisi_proyek').on('input blur', (e) => {
            this.validateField('desc_kondisi_proyek', $(e.target).val());
        });

        // Clear errors on input
        $('input, select, textarea').on('input change', (e) => {
            const fieldName = $(e.target).attr('name');
            this.clearFieldError(fieldName);
            $(e.target).removeClass('is-invalid is-valid');
        });
    }

    initializePaginationHandlers() {
        // First page handler
        $('#firstPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage = 1;
                this.loadKondisiProyekData();
            }
        });

        // Previous page handler
        $('#prevPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadKondisiProyekData();
            }
        });

        // Next page handler
        $('#nextPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadKondisiProyekData();
            }
        });

        // Last page handler
        $('#lastPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage = this.totalPages;
                this.loadKondisiProyekData();
            }
        });
    }

    initializeModalHandlers() {
        // Delete confirmation handler
        $('#confirmDeleteBtn').on('click', () => {
            if (this.deleteTargetId) {
                this.performDelete(this.deleteTargetId);
            }
        });
    }

    // ========================================
    // DATA LOADING METHODS
    // ========================================

    loadKondisiProyekData() {
        this.showLoadingSpinner(true);

        const params = {
            search: this.currentSearch,
            per_page: this.perPage,
            page: this.currentPage
        };

        // Save state
        if (this.stateManager) {
            this.stateManager.saveState({
                currentPage: this.currentPage,
                currentSearch: this.currentSearch,
                perPage: this.perPage
            });
            console.log('State saved:', this.stateManager.getState());
        }

        $.ajax({
            url: this.urls.index,
            type: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                if (response.success) {
                    this.updateTableContent(response.data);
                    this.updatePaginationInfo(response.pagination);
                    this.updatePaginationButtons();
                } else {
                    this.showAlert('Gagal memuat data', 'error');
                }
            },
            error: (xhr) => {
                console.error('Error loading data:', xhr);
                this.showAlert('Terjadi kesalahan saat memuat data', 'error');
            },
            complete: () => {
                this.showLoadingSpinner(false);
            }
        });
    }

    // ========================================
    // TABLE UPDATE METHODS
    // ========================================

    updateTableContent(data) {
        const tbody = $('#kondisiProyekTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data kondisi proyek</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(item => {
            // Determine status badge
            const statusBadge = item.status === 'A' ? 'badge bg-success' : 'badge bg-secondary';
            const statusText = item.status === 'A' ? 'Aktif' : 'Non Aktif';

            tbody.append(`
                <tr class="editable-row" ondblclick="window.kondisiProyekManager.editKondisiProyek('${item.id_kondisi_proyek}')" title="Double-click untuk edit" style="cursor: pointer;">
                    <td>
                        <span class="kondisiproyek-id fw-bold" data-id="${item.id_kondisi_proyek}">
                            ${item.id_kondisi_proyek}
                        </span>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.desc_kondisi_proyek)}">
                            ${this.escapeHtml(item.desc_kondisi_proyek)}
                        </div>
                    </td>
                    <td onclick="event.stopPropagation();">
                        <span class="${statusBadge}">${statusText}</span>
                    </td>
                    <td onclick="event.stopPropagation();">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="window.kondisiProyekManager.viewKondisiProyek('${item.id_kondisi_proyek}')">
                                    <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="window.kondisiProyekManager.deleteKondisiProyek('${item.id_kondisi_proyek}')">
                                    <i class="bx bx-trash me-1"></i> Hapus</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    updatePaginationInfo(pagination) {
        this.currentPage = pagination.current_page;
        this.totalPages = pagination.last_page;

        if (this.elements.entriesInfo.from.length) {
            this.elements.entriesInfo.from.text(pagination.from || 0);
        }
        if (this.elements.entriesInfo.to.length) {
            this.elements.entriesInfo.to.text(pagination.to || 0);
        }
        if (this.elements.entriesInfo.total.length) {
            this.elements.entriesInfo.total.text(pagination.total || 0);
        }
    }

    updatePaginationButtons() {
        if (!this.elements.pageButtons.first.length) return;

        // Update button states
        this.elements.pageButtons.first.prop('disabled', this.currentPage <= 1);
        this.elements.pageButtons.prev.prop('disabled', this.currentPage <= 1);
        this.elements.pageButtons.next.prop('disabled', this.currentPage >= this.totalPages);
        this.elements.pageButtons.last.prop('disabled', this.currentPage >= this.totalPages);

        // Update page numbers
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

        // Always show page numbers, even if only 1 page
        // if (this.totalPages <= 1) {
        //     return;
        // }

        let startPage, endPage;
        const isMobile = window.innerWidth <= 576;
        const maxVisiblePages = isMobile ? 3 : 5;

        if (this.totalPages <= maxVisiblePages) {
            startPage = 1;
            endPage = this.totalPages;
        } else {
            const halfVisible = Math.floor(maxVisiblePages / 2);
            if (this.currentPage <= halfVisible + 1) {
                startPage = 1;
                endPage = maxVisiblePages;
            } else if (this.currentPage + halfVisible >= this.totalPages) {
                startPage = this.totalPages - maxVisiblePages + 1;
                endPage = this.totalPages;
            } else {
                startPage = this.currentPage - halfVisible;
                endPage = this.currentPage + halfVisible;
            }
        }

        // Add ellipsis before if needed
        if (startPage > 1 && !isMobile) {
            container.append(`
                <button type="button" class="btn btn-outline-secondary btn-sm page-number-btn" data-page="1">1</button>
            `);
            if (startPage > 2) {
                container.append(`<span class="px-1 px-md-2">...</span>`);
            }
        }

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === this.currentPage;
            container.append(`
                <button type="button" class="btn btn-sm page-number-btn ${isActive ? 'btn-primary' : 'btn-outline-secondary'}"
                        data-page="${i}" ${isActive ? 'disabled' : ''}>${i}</button>
            `);
        }

        // Add ellipsis after if needed
        if (endPage < this.totalPages && !isMobile) {
            if (endPage < this.totalPages - 1) {
                container.append(`<span class="px-1 px-md-2">...</span>`);
            }
            container.append(`
                <button type="button" class="btn btn-outline-secondary btn-sm page-number-btn" data-page="${this.totalPages}">${this.totalPages}</button>
            `);
        }

        // Add click handlers for page numbers
        $('.page-number-btn').off('click').on('click', (e) => {
            const page = parseInt($(e.target).data('page'));
            if (page !== this.currentPage && !isNaN(page)) {
                this.currentPage = page;
                this.loadKondisiProyekData();
            }
        });
    }

    // ========================================
    // VALIDATION METHODS
    // ========================================

    validateField(fieldName, value) {
        this.clearFieldError(fieldName);
        const field = $(`#${fieldName}`);
        let isValid = true;
        let errorMessage = '';

        switch (fieldName) {
            case 'desc_kondisi_proyek':
                if (!value.trim()) {
                    isValid = false;
                    errorMessage = 'Deskripsi kondisi proyek harus diisi.';
                } else if (value.length > 255) {
                    isValid = false;
                    errorMessage = 'Deskripsi kondisi proyek maksimal 255 karakter.';
                }
                break;
        }

        if (!isValid) {
            this.showFieldError(fieldName, errorMessage);
            field.addClass('is-invalid');
        } else if (value) {
            field.addClass('is-valid');
        }

        return isValid;
    }

    // ========================================
    // FORM SUBMISSION METHODS
    // ========================================

    submitForm() {
        this.clearAllErrors();

        let isFormValid = true;
        isFormValid &= this.validateField('desc_kondisi_proyek', $('#desc_kondisi_proyek').val());

        if (!isFormValid) {
            this.showAlert('Mohon perbaiki kesalahan pada form.', 'error');
            return;
        }

        this.setSubmitLoading(true);

        const formData = new FormData($('#kondisiProyekForm')[0]);

        $.ajax({
            url: $('#kondisiProyekForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                if (response.success) {
                    this.showAlert(response.message, 'success');

                    // Mark for restore only if editing
                    const isEdit = $('#kondisiProyekForm').find('input[name="_method"]').val() === 'PUT';
                    if (isEdit && this.stateManager) {
                        this.stateManager.markForRestore();
                        console.log('Marked for restore (edit mode)');
                    }

                    setTimeout(() => {
                        window.location.href = this.urls.index;
                    }, 1500);
                } else {
                    this.showAlert(response.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
                    this.setSubmitLoading(false);
                }
            },
            error: (xhr) => {
                this.setSubmitLoading(false);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    Object.keys(errors).forEach(field => {
                        this.showFieldError(field, errors[field][0]);
                        $(`#${field}`).addClass('is-invalid');
                    });
                    this.showAlert('Mohon perbaiki kesalahan pada form.', 'error');
                } else {
                    this.showAlert('Terjadi kesalahan saat menyimpan data.', 'error');
                }
                console.error('Form submission error:', xhr);
            }
        });
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
            text.text(btn.data('loading-text') || 'Menyimpan...');
        } else {
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            icon.removeClass('d-none');
            text.text(btn.data('default-text') || 'Simpan');
        }
    }

    // ========================================
    // MODAL & ACTION METHODS
    // ========================================

    editKondisiProyek(id) {
        window.location.href = `${this.urls.index}/${id}/edit`;
    }

    viewKondisiProyek(id) {
        $.ajax({
            url: `${this.urls.show}/${id}`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                if (response.success) {
                    this.displayKondisiProyekDetails(response.data);
                    $('#viewKondisiProyekModal').modal('show');
                } else {
                    this.showAlert('Data tidak ditemukan', 'error');
                }
            },
            error: (xhr) => {
                console.error('View error:', xhr);
                this.showAlert('Terjadi kesalahan saat memuat detail', 'error');
            }
        });
    }

    displayKondisiProyekDetails(data) {
        const statusText = data.status === 'A' ? 'Aktif' : 'Non Aktif';
        const statusBadge = data.status === 'A' ? 'badge bg-success' : 'badge bg-secondary';

        const content = `
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-10">
                        <div class="modal-info-section">
                            <h6>Informasi Kondisi Proyek</h6>
                            <table class="table table-sm">
                                <tr><td width="150">ID Kondisi Proyek:</td><td><strong>${data.id_kondisi_proyek}</strong></td></tr>
                                <tr><td>Deskripsi:</td><td><strong>${data.desc_kondisi_proyek}</strong></td></tr>
                                <tr><td>Status:</td><td><span class="${statusBadge}">${statusText}</span></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#viewKondisiProyekContent').html(content);
    }

    deleteKondisiProyek(id) {
        this.deleteTargetId = id;
        $('#deleteConfirmModal').modal('show');
    }

    performDelete(id) {
        $.ajax({
            url: `${this.urls.destroy}/${id}`,
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: () => {
                $('#confirmDeleteBtn').prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Menghapus...
                `);
            },
            success: (response) => {
                $('#deleteConfirmModal').modal('hide');
                if (response.success) {
                    this.showAlert(response.message, 'success');

                    // Mark for restore after delete
                    if (this.stateManager) {
                        this.stateManager.markForRestore();
                        console.log('Marked for restore (after delete)');
                    }

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showAlert(response.message || 'Terjadi kesalahan saat menghapus data', 'error');
                }
            },
            error: (xhr) => {
                $('#deleteConfirmModal').modal('hide');
                console.error('Delete error:', xhr);
                this.showAlert('Terjadi kesalahan saat menghapus data', 'error');
            },
            complete: () => {
                $('#confirmDeleteBtn').prop('disabled', false).html('Ya');
                this.deleteTargetId = null;
            }
        });
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    resetForm(originalData = {}) {
        Object.keys(originalData).forEach(key => {
            $(`#${key}`).val(originalData[key] || '');
        });
        this.clearAllErrors();
    }

    showLoadingSpinner(show) {
        if (show) {
            $('.loading-spinner').show();
        } else {
            $('.loading-spinner').hide();
        }
    }

    escapeHtml(text) {
        if (!text) return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ========================================
    // ERROR/SUCCESS MESSAGE METHODS
    // ========================================

    showAlert(message, type = 'info') {
        // Remove existing alerts
        this.hideAlert();

        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' : 'alert-info';

        const icon = type === 'success' ? 'bx-check-circle' :
                     type === 'error' ? 'bx-error-circle' : 'bx-info-circle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show alert-custom" role="alert">
                <i class="bx ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert at the beginning of the main content
        if ($('.card').length) {
            $('.card').first().before(alertHtml);
        } else {
            $('main .container, main .container-fluid').first().prepend(alertHtml);
        }

        // Auto hide success alerts
        if (type === 'success') {
            setTimeout(() => this.hideAlert(), 5000);
        }

        // Scroll to alert
        $('html, body').animate({
            scrollTop: $('.alert-custom').offset().top - 100
        }, 300);
    }

    hideAlert() {
        $('.alert-custom').fadeOut(300, function() {
            $(this).remove();
        });
    }

    showFieldError(fieldName, message) {
        $(`#${fieldName}-error`).text(message);
    }

    clearFieldError(fieldName) {
        $(`#${fieldName}-error`).text('');
    }

    showFieldSuccess(fieldName, message) {
        $(`#${fieldName}-success`).text(message);
    }

    clearFieldSuccess(fieldName) {
        $(`#${fieldName}-success`).text('');
    }

    clearAllErrors() {
        $('.error-message').text('');
        $('.success-message').text('');
        $('.form-control').removeClass('is-invalid is-valid');
        this.hideAlert();
    }
}

// ========================================
// GLOBAL FUNCTIONS (for onclick handlers)
// ========================================

// Global functions for onclick handlers
window.editKondisiProyek = function(id) {
    if (window.kondisiProyekManager) {
        window.kondisiProyekManager.editKondisiProyek(id);
    }
};

window.viewKondisiProyek = function(id) {
    if (window.kondisiProyekManager) {
        window.kondisiProyekManager.viewKondisiProyek(id);
    }
};

window.deleteKondisiProyek = function(id) {
    if (window.kondisiProyekManager) {
        window.kondisiProyekManager.deleteKondisiProyek(id);
    }
};

window.resetForm = function() {
    if (window.kondisiProyekManager) {
        const originalData = window.originalFormData || {};
        window.kondisiProyekManager.resetForm(originalData);
    }
};
