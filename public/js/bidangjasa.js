class BidangJasaManager {
    constructor() {
        this.isSubmitting = false;
        this.searchTimeout = null;

        // Pagination variables (for index page)
        this.currentPage = 1;
        this.totalPages = 1;
        this.perPage = 10;
        this.currentSearch = '';

        // Form validation variables (for create/edit)
        this.originalDesc = '';
        this.currentBidangJasaId = '';

        // Configuration
        this.config = {
            pageType: 'index',
            debounceTimer: null
        };

        // Elements cache
        this.elements = {};

        // URL configuration
        this.urls = {
            index: window.routes?.bidangjasa?.index || '/bidangjasa',
            store: window.routes?.bidangjasa?.store || '/bidangjasa'
        };
    }

    // ========================================
    // INITIALIZATION METHODS
    // ========================================

    init(config = {}) {
        this.setConfig(config);
        this.bindElements();
        this.initializeEventHandlers();

        // Initialize page-specific functions
        if (config.pageType === 'index') {
            this.updatePaginationButtons();
        }

        console.log('BidangJasa Manager initialized:', this.config);
    }

    setConfig(config) {
        this.config = { ...this.config, ...config };
        this.originalDesc = config.originalDesc || '';
        this.currentBidangJasaId = config.currentBidangJasaId || '';
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
    }

    bindElements() {
        // Common elements
        this.elements.form = $('#bidangJasaForm');
        this.elements.submitBtn = $('#submitBtn');
        this.elements.submitSpinner = $('#submitSpinner');
        this.elements.submitIcon = $('#submitIcon');
        this.elements.submitText = $('#submitText');

        // Index page elements
        if (this.config.pageType === 'index') {
            this.elements.searchInput = $('#searchInput');
            this.elements.perPageSelect = $('#perPageSelect');
            this.elements.tableBody = $('#bidangJasaTableBody');
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
                    this.loadBidangJasaData();
                }, 300);
            });

            // Handle Enter key for immediate search
            this.elements.searchInput.on('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(this.searchTimeout);
                    this.loadBidangJasaData();
                }
            });
        }

        // Per page selector handler
        if (this.elements.perPageSelect.length) {
            this.elements.perPageSelect.on('change', (e) => {
                this.perPage = parseInt($(e.target).val());
                this.currentPage = 1;
                this.loadBidangJasaData();
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

    initializeFieldValidation() {
        // Description validation
        $('#desc_bidjasa').on('input blur', (e) => {
            this.validateField('desc_bidjasa', $(e.target).val());
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
                this.loadBidangJasaData();
            }
        });

        // Previous page handler
        $('#prevPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadBidangJasaData();
            }
        });

        // Next page handler
        $('#nextPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadBidangJasaData();
            }
        });

        // Last page handler
        $('#lastPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage = this.totalPages;
                this.loadBidangJasaData();
            }
        });
    }

    initializeModalHandlers() {
        // No modal handlers needed as there are no action buttons
    }

    // ========================================
    // DATA LOADING METHODS
    // ========================================

    loadBidangJasaData() {
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
        const tbody = $('#bidangJasaTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data bidang jasa</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(item => {
            const statusBadge = item.status === 'A' ?
                '<span class="badge bg-success">Aktif</span>' :
                '<span class="badge bg-secondary">Non Aktif</span>';

            tbody.append(`
                <tr>
                    <td>
                        <span class="bidangjasa-id fw-bold" data-id="${item.id_bidjasa}" ondblclick="editBidangJasa('${item.id_bidjasa}')" title="Double-click untuk edit" style="cursor: pointer;">
                            ${item.id_bidjasa}
                        </span>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.desc_bidjasa)}">
                            ${this.escapeHtml(item.desc_bidjasa)}
                        </div>
                    </td>
                    <td>
                        ${statusBadge}
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
                this.loadBidangJasaData();
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
            case 'desc_bidjasa':
                if (!value.trim()) {
                    isValid = false;
                    errorMessage = 'Deskripsi bidang jasa harus diisi.';
                } else if (value.length > 50) {
                    isValid = false;
                    errorMessage = 'Deskripsi bidang jasa maksimal 50 karakter.';
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
        isFormValid &= this.validateField('desc_bidjasa', $('#desc_bidjasa').val());

        if (!isFormValid) {
            this.showAlert('Mohon perbaiki kesalahan pada form.', 'error');
            return;
        }

        this.setSubmitLoading(true);

        const formData = new FormData($('#bidangJasaForm')[0]);

        $.ajax({
            url: $('#bidangJasaForm').attr('action'),
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

// Reset form function
window.resetForm = function() {
    if (window.bidangJasaManager) {
        const originalData = window.originalFormData || {};
        window.bidangJasaManager.resetForm(originalData);
    }
};
