class SpesifikasiRABManager {
    constructor() {
        this.isSubmitting = false;
        this.searchTimeout = null;
        this.deleteTargetId = null;

        // Pagination variables (for index page)
        this.currentPage = 1;
        this.totalPages = 1;
        this.perPage = 10;
        this.currentSearch = '';

        // Form validation variables (for create/edit)
        this.currentid_spec = '';

        // Configuration
        this.config = {
            pageType: 'index',
            debounceTimer: null
        };

        // Elements cache
        this.elements = {};

        // URL configuration
        this.urls = {
            index: window.routes?.spesifikasirab?.index || '/spesifikasirab',
            store: window.routes?.spesifikasirab?.store || '/spesifikasirab',
            show: window.routes?.spesifikasirab?.show || '/spesifikasirab',
            update: window.routes?.spesifikasirab?.update || '/spesifikasirab',
            destroy: window.routes?.spesifikasirab?.destroy || '/spesifikasirab'
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

        console.log('SpesifikasiRAB Manager initialized:', this.config);
    }

    setConfig(config) {
        this.config = { ...this.config, ...config };
        this.currentid_spec = config.currentid_spec || '';
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
    }

    bindElements() {
        // Common elements
        this.elements.form = $('#spesifikasiRABForm');
        this.elements.submitBtn = $('#submitBtn');
        this.elements.submitSpinner = $('#submitSpinner');
        this.elements.submitIcon = $('#submitIcon');
        this.elements.submitText = $('#submitText');

        // Index page elements
        if (this.config.pageType === 'index') {
            this.elements.searchInput = $('#searchInput');
            this.elements.perPageSelect = $('#perPageSelect');
            this.elements.tableBody = $('#spesifikasiRABTableBody');
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
    }

    // ========================================
    // EVENT HANDLERS
    // ========================================

    initializeEventHandlers() {
        if (this.config.pageType === 'index') {
            this.initializeIndexHandlers();
        } else {
            this.initializeFormHandlers();
        }
    }

    initializeIndexHandlers() {
        // Search functionality with proper debounce
        if (this.elements.searchInput.length) {
            this.elements.searchInput.on('input', (e) => {
                clearTimeout(this.searchTimeout);
                const searchValue = $(e.target).val();

                this.searchTimeout = setTimeout(() => {
                    this.currentSearch = searchValue;
                    this.currentPage = 1;
                    this.loadSpesifikasiRABData();
                }, 500);
            });

            // Show spinner on input
            this.elements.searchInput.on('input', () => {
                this.showLoadingSpinner(true);
            });
        }

        // Per page selector handler
        if (this.elements.perPageSelect.length) {
            this.elements.perPageSelect.on('change', (e) => {
                this.perPage = $(e.target).val();
                this.currentPage = 1;
                this.loadSpesifikasiRABData();
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
                this.submitForm();
            });
        }

        // Real-time validation
        this.initializeFieldValidation();
    }

    initializeFieldValidation() {
        // Nomor urut validation
        $('#norutspec').on('input blur', (e) => {
            this.validateField('norutspec', $(e.target).val());
        });

        // Spesifikasi RAB validation
        $('#spec_rab').on('input blur', (e) => {
            this.validateField('spec_rab', $(e.target).val());
        });

        // Kategori validation
        $('#kategori').on('change', (e) => {
            this.validateField('kategori', $(e.target).val());
        });

        // Status validation
        $('#status').on('change blur', (e) => {
            this.validateField('status', $(e.target).val());
        });

        // Clear errors on input
        $('input, select, textarea').on('input change', (e) => {
            const fieldName = $(e.target).attr('name');
            this.clearFieldError(fieldName);
        });
    }

    initializePaginationHandlers() {
        // First page handler
        $('#firstPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage = 1;
                this.loadSpesifikasiRABData();
            }
        });

        // Previous page handler
        $('#prevPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadSpesifikasiRABData();
            }
        });

        // Next page handler
        $('#nextPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadSpesifikasiRABData();
            }
        });

        // Last page handler
        $('#lastPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage = this.totalPages;
                this.loadSpesifikasiRABData();
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

    loadSpesifikasiRABData() {
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
                this.updateTableContent(response.data);
                this.updatePaginationInfo(response.pagination);
                this.updatePaginationButtons();
            },
            error: (xhr) => {
                console.error('Error loading data:', xhr);
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
        const tbody = $('#spesifikasiRABTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data spesifikasi RAB</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(item => {
            const kategoriLabel = item.kategori === 'PDP' ? 'Pendapatan' : 'Harga Pokok Penjualan';
            const kategoriClass = item.kategori === 'PDP' ? 'bg-success' : 'bg-info';
            const statusBadge = item.status === 'A'
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-secondary">Non Aktif</span>';

            const row = `
                <tr>
                    <td>
                        <span class="norutspec-value" data-id_spec="${item.id_spec}" ondblclick="editSpesifikasiRAB('${item.id_spec}')" title="Double-click untuk edit">
                            ${this.escapeHtml(item.norutspec)}
                        </span>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.spec_rab)}">
                            ${this.escapeHtml(item.spec_rab)}
                        </div>
                    </td>
                    <td>
                        <span class="badge ${kategoriClass}">${kategoriLabel}</span>
                    </td>
                    <td>
                        ${statusBadge}
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewSpesifikasiRAB('${item.id_spec}')">
                                    <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
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

        if (this.elements.entriesInfo.from.length) {
            this.elements.entriesInfo.from.text(pagination.from || 0);
        }
        if (this.elements.entriesInfo.to.length) {
            this.elements.entriesInfo.to.text(pagination.to || 0);
        }
        if (this.elements.entriesInfo.total.length) {
            this.elements.entriesInfo.total.text(pagination.total);
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
            startPage = Math.max(1, this.currentPage - halfVisible);
            endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
        }

        // Add ellipsis before if needed
        if (startPage > 1 && !isMobile) {
            container.append(`
                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                    ...
                </button>
            `);
        }

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            const buttonClass = i === this.currentPage
                ? 'btn btn-primary btn-sm page-number-btn'
                : 'btn btn-outline-secondary btn-sm page-number-btn';
            container.append(`
                <button type="button" class="${buttonClass}" data-page="${i}">
                    ${i}
                </button>
            `);
        }

        // Add ellipsis after if needed
        if (endPage < this.totalPages && !isMobile) {
            container.append(`
                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                    ...
                </button>
            `);
        }

        // Add click handlers for page numbers
        $('.page-number-btn').off('click').on('click', (e) => {
            const page = parseInt($(e.currentTarget).data('page'));
            if (page !== this.currentPage) {
                this.currentPage = page;
                this.loadSpesifikasiRABData();
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
            case 'norutspec':
                if (!value || value.trim() === '') {
                    isValid = false;
                    errorMessage = 'Nomor urut harus diisi.';
                } else if (value.length > 2) {
                    isValid = false;
                    errorMessage = 'Nomor urut maksimal 2 karakter.';
                }
                break;

            case 'spec_rab':
                if (!value || value.trim() === '') {
                    isValid = false;
                    errorMessage = 'Spesifikasi RAB harus diisi.';
                } else if (value.length > 100) {
                    isValid = false;
                    errorMessage = 'Spesifikasi RAB maksimal 100 karakter.';
                }
                break;

            case 'kategori':
                if (!value || value === '') {
                    isValid = false;
                    errorMessage = 'Kategori harus dipilih.';
                }
                break;

            case 'status':
                // Status is optional, but if provided must be A or N
                if (value && !['A', 'N'].includes(value)) {
                    isValid = false;
                    errorMessage = 'Status harus berupa Aktif atau Non Aktif.';
                }
                break;
        }

        if (!isValid) {
            this.showFieldError(fieldName, errorMessage);
        } else {
            field.removeClass('is-invalid').addClass('is-valid');
        }

        return isValid;
    }

    // ========================================
    // FORM SUBMISSION METHODS
    // ========================================

    submitForm() {
        this.clearAllErrors();

        let isFormValid = true;
        isFormValid &= this.validateField('norutspec', $('#norutspec').val());
        isFormValid &= this.validateField('spec_rab', $('#spec_rab').val());
        isFormValid &= this.validateField('kategori', $('#kategori').val());
        // Validate status if it has a value
        const statusValue = $('#status').val();
        if (statusValue) {
            isFormValid &= this.validateField('status', statusValue);
        }

        if (!isFormValid) {
            return;
        }

        this.setSubmitLoading(true);

        const formData = new FormData($('#spesifikasiRABForm')[0]);

        $.ajax({
            url: $('#spesifikasiRABForm').attr('action'),
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
                }
            },
            error: (xhr) => {
                this.setSubmitLoading(false);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        this.showFieldError(key, errors[key][0]);
                    });
                } else {
                    this.showAlert('Terjadi kesalahan saat menyimpan data.', 'danger');
                }
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

    editSpesifikasiRAB(id_spec) {
        window.location.href = `${this.urls.index}/${id_spec}/edit`;
    }

    viewSpesifikasiRAB(id_spec) {
        $.ajax({
            url: `${this.urls.show}/${id_spec}`,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    this.displaySpesifikasiRABDetails(response.data);
                    $('#viewSpesifikasiRABModal').modal('show');
                }
            },
            error: (xhr) => {
                console.error('Error loading details:', xhr);
                this.showAlert('Gagal memuat detail spesifikasi RAB.', 'danger');
            }
        });
    }

    displaySpesifikasiRABDetails(data) {
        const kategoriLabel = data.kategori === 'PDP' ? 'PDP - Pendapatan' : 'HPP - Harga Pokok Penjualan';
        const kategoriClass = data.kategori === 'PDP' ? 'bg-success' : 'bg-info';

        const content = `
            <div class="modal-info-section">
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
                                    <td>Nomor Urut:</td>
                                    <td><strong>${this.escapeHtml(data.norutspec)}</strong></td>
                                </tr>
                                <tr>
                                    <td>Kategori:</td>
                                    <td><span class="badge ${kategoriClass}">${kategoriLabel}</span></td>
                                </tr>
                                <tr>
                                    <td>Status:</td>
                                    <td><span class="badge ${data.status === 'A' ? 'bg-success' : 'bg-secondary'}">${data.status === 'A' ? 'Aktif' : 'Non Aktif'}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="modal-info-section">
                            <h6>Detail Spesifikasi RAB</h6>
                            <div class="p-3 bg-light rounded">
                                <p class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">${this.escapeHtml(data.spec_rab)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#viewSpesifikasiRABContent').html(content);
    }

    deleteSpesifikasiRAB(id_spec) {
        this.deleteTargetId = id_spec;
        $('#deleteConfirmModal').modal('show');
    }

    performDelete(id_spec) {
        $.ajax({
            url: `${this.urls.destroy}/${id_spec}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                $('#deleteConfirmModal').modal('hide');
                if (response.success) {
                    this.showAlert(response.message, 'success');
                    this.loadSpesifikasiRABData();
                }
            },
            error: (xhr) => {
                $('#deleteConfirmModal').modal('hide');
                console.error('Error deleting:', xhr);
                this.showAlert('Gagal menghapus data.', 'danger');
            }
        });
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    resetForm(originalData = {}) {
        if (window.originalFormData) {
            Object.keys(window.originalFormData).forEach(key => {
                $(`#${key}`).val(window.originalFormData[key]);
            });
        }
        this.clearAllErrors();
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
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // ========================================
    // ERROR/SUCCESS MESSAGE METHODS
    // ========================================

    showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        $('.alert').remove();

        // Add new alert at the top of the page
        if ($('.sticky-header').length) {
            $('.sticky-header').after(alertHtml);
        } else if ($('.nonsticky-header').length) {
            $('.nonsticky-header').after(alertHtml);
        } else {
            $('main').prepend(alertHtml);
        }

        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 300);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    hideAlert() {
        $('.alert').fadeOut(300, function() {
            $(this).remove();
        });
    }

    showFieldError(fieldName, message) {
        $(`#${fieldName}`).addClass('is-invalid').removeClass('is-valid');
        $(`#${fieldName}-error`).text(message);
    }

    clearFieldError(fieldName) {
        $(`#${fieldName}`).removeClass('is-invalid');
        $(`#${fieldName}-error`).text('');
    }

    showFieldSuccess(fieldName, message) {
        $(`#${fieldName}`).addClass('is-valid').removeClass('is-invalid');
    }

    clearFieldSuccess(fieldName) {
        $(`#${fieldName}`).removeClass('is-valid');
    }

    clearAllErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('.is-valid').removeClass('is-valid');
    }
}

// ========================================
// GLOBAL FUNCTIONS (for onclick handlers)
// ========================================

window.editSpesifikasiRAB = function(id_spec) {
    if (window.spesifikasiRABManager) {
        window.spesifikasiRABManager.editSpesifikasiRAB(id_spec);
    }
};

window.viewSpesifikasiRAB = function(id_spec) {
    if (window.spesifikasiRABManager) {
        window.spesifikasiRABManager.viewSpesifikasiRAB(id_spec);
    }
};

window.deleteSpesifikasiRAB = function(id_spec) {
    if (window.spesifikasiRABManager) {
        window.spesifikasiRABManager.deleteSpesifikasiRAB(id_spec);
    }
};

window.resetForm = function() {
    if (window.spesifikasiRABManager) {
        window.spesifikasiRABManager.resetForm(window.originalFormData || {});
    }
};
