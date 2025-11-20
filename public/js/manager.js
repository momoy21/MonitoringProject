class ManagerManager {
    constructor() {
        this.currentPage = 1;
        this.totalPages = 1;
        this.perPage = 10;
        this.currentSearch = '';
        this.searchTimeout = null;
        this.deleteNik = null;
        this.isLoading = false;
        this.isSubmitting = false;
    }

    init(config = {}) {
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
        this.pageType = config.pageType || 'index';

        this.bindEvents();

        if (this.pageType === 'index') {
            this.updatePaginationInfo();
            this.generatePageNumbers();
        }

        // Initialize NIK input validation ONLY for create page
        if (this.pageType === 'create') {
            this.initNikValidation();
        }

        // Initialize form submission for create/edit pages
        if (this.pageType === 'create' || this.pageType === 'edit') {
            this.initFormSubmission();
        }
    }

    bindEvents() {
        const self = this;

        // Search functionality
        $('#searchInput').on('input', function() {
            clearTimeout(self.searchTimeout);
            self.searchTimeout = setTimeout(() => {
                self.performSearch($(this).val());
            }, 500);
        });

        // Per page selector
        $('#perPageSelect').on('change', function() {
            self.changePerPage($(this).val());
        });

        // Pagination buttons
        $(document).on('click', '#firstPageBtn', () => this.goToPage(1));
        $(document).on('click', '#lastPageBtn', () => this.goToPage(this.totalPages));
        $(document).on('click', '#prevPageBtn', () => this.goToPage(this.currentPage - 1));
        $(document).on('click', '#nextPageBtn', () => this.goToPage(this.currentPage + 1));
        $(document).on('click', '.page-number-btn', function() {
            self.goToPage(parseInt($(this).data('page')));
        });

        // Delete confirmation
        $('#confirmDeleteBtn').on('click', function() {
            self.performDelete();
        });
    }

    initFormSubmission() {
        const self = this;

        console.log('initFormSubmission called for pageType:', this.pageType);

        // Remove any existing submit handlers first
        $('#managerForm').off('submit');

        // Form submit handler
        $('#managerForm').on('submit', function(e) {
            console.log('Form submit triggered');
            e.preventDefault();
            if (!self.isSubmitting) {
                console.log('Calling submitForm()');
                self.submitForm();
            } else {
                console.log('Already submitting, skipped');
            }
        });

        // Clear errors on input change
        $('input, select').on('input change', function() {
            const fieldName = $(this).attr('name');
            if (fieldName) {
                self.clearFieldError(fieldName);
                $(this).removeClass('is-invalid is-valid');
            }
        });
    }

    submitForm() {
        this.clearAllErrors();

        // Basic form validation
        let isFormValid = true;
        const namaValue = $('#nama').val().trim();

        // Validate NIK - ONLY for create page
        if (this.pageType === 'create') {
            const nikValue = $('#nik').val();
            if (!nikValue || nikValue.length !== 7) {
                this.showFieldError('nik', 'NIK harus diisi dan terdiri dari 7 karakter.');
                isFormValid = false;
            }
        }

        // Validate Nama (required for both create and edit)
        if (!namaValue) {
            this.showFieldError('nama', 'Nama manager harus diisi.');
            isFormValid = false;
        }

        if (!isFormValid) {
            return;
        }

        this.setSubmitLoading(true);

        const formData = new FormData($('#managerForm')[0]);

        $.ajax({
            url: $('#managerForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                this.setSubmitLoading(false);

                if (response.success) {
                    this.showAlert(response.message || 'Data berhasil disimpan!', 'success');

                    // Redirect to index after short delay
                    setTimeout(() => {
                        window.location.href = '/mastermanager';
                    }, 1500);
                } else {
                    this.showAlert(response.message || 'Terjadi kesalahan', 'error');
                }
            },
            error: (xhr) => {
                this.setSubmitLoading(false);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};

                    Object.keys(errors).forEach(field => {
                        this.showFieldError(field, errors[field][0]);
                    });

                    this.showAlert('Periksa kembali data yang diisi', 'error');
                } else {
                    this.showAlert('Terjadi kesalahan saat menyimpan data', 'error');
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
            if (spinner.length) spinner.removeClass('d-none');
            if (icon.length) icon.addClass('d-none');
            if (text.length) text.text(btn.data('loading-text') || 'Menyimpan...');
        } else {
            btn.prop('disabled', false);
            if (spinner.length) spinner.addClass('d-none');
            if (icon.length) icon.removeClass('d-none');
            if (text.length) text.text(btn.data('default-text') || 'Simpan');
        }
    }

    initNikValidation() {
        const nikInput = $('#nik');

        if (nikInput.length) {
            // Only allow alphanumeric characters
            nikInput.on('input', function() {
                let value = $(this).val();
                // Remove any non-alphanumeric characters
                value = value.replace(/[^A-Za-z0-9]/g, '');
                // Limit to 7 characters
                if (value.length > 7) {
                    value = value.substring(0, 7);
                }
                $(this).val(value.toUpperCase());
            });

            // Prevent paste of non-alphanumeric content
            nikInput.on('paste', function(e) {
                setTimeout(() => {
                    let value = $(this).val();
                    value = value.replace(/[^A-Za-z0-9]/g, '');
                    if (value.length > 7) {
                        value = value.substring(0, 7);
                    }
                    $(this).val(value.toUpperCase());
                }, 1);
            });

            // Real-time validation feedback
            nikInput.on('blur', function() {
                const value = $(this).val();
                const nikInput = $(this);

                if (value.length === 0) {
                    nikInput.removeClass('is-valid is-invalid');
                    window.managerManager.clearFieldError('nik');
                    window.managerManager.clearFieldSuccess('nik');
                } else if (value.length !== 7) {
                    nikInput.removeClass('is-valid').addClass('is-invalid');
                    window.managerManager.showFieldError('nik', 'NIK harus terdiri dari 7 karakter');
                    window.managerManager.clearFieldSuccess('nik');
                } else if (!/^[A-Za-z0-9]{7}$/.test(value)) {
                    nikInput.removeClass('is-valid').addClass('is-invalid');
                    window.managerManager.showFieldError('nik', 'NIK hanya boleh berisi huruf dan angka');
                    window.managerManager.clearFieldSuccess('nik');
                } else {
                    nikInput.removeClass('is-invalid').addClass('is-valid');
                    window.managerManager.clearFieldError('nik');
                    window.managerManager.showFieldSuccess('nik', 'Format NIK sudah benar');
                }
            });
        }
    }

    performSearch(query) {
        if (this.isLoading) return;

        this.currentSearch = query;
        this.currentPage = 1;
        this.loadData();
    }

    changePerPage(perPage) {
        if (this.isLoading) return;

        this.perPage = parseInt(perPage);
        this.currentPage = 1;
        this.loadData();
    }

    goToPage(page) {
        if (this.isLoading || page < 1 || page > this.totalPages || page === this.currentPage) return;

        this.currentPage = page;
        this.loadData();
    }

    loadData() {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showTableLoading();

        const params = {
            page: this.currentPage,
            per_page: this.perPage,
            search: this.currentSearch
        };

        $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                if (response.success) {
                    this.updateTable(response.data);
                    this.updatePagination(response.pagination);
                } else {
                    this.showAlert('Gagal memuat data', 'error');
                }
            },
            error: () => {
                this.showAlert('Terjadi kesalahan saat memuat data', 'error');
            },
            complete: () => {
                this.isLoading = false;
                this.hideTableLoading();
            }
        });
    }

    updateTable(data) {
        const tbody = $('#mastermanagerTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data manager</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(item => {
            const statusBadge = item.status === 'A'
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-secondary">Non Aktif</span>';

            const namaDisplay = item.nama || '';

            const row = `
                <tr class="editable-row" ondblclick="editManager('${item.nik}')" title="Double-click untuk edit" style="cursor: pointer;">
                    <td>
                        <span class="manager-nik fw-semibold" data-nik="${item.nik}">
                            ${item.nik}
                        </span>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(namaDisplay)}">
                            ${this.escapeHtml(namaDisplay)}
                        </div>
                    </td>
                    <td onclick="event.stopPropagation();">${statusBadge}</td>
                    <td class="text-center" onclick="event.stopPropagation();">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewManager('${item.nik}')">
                                    <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    updatePagination(pagination) {
        this.currentPage = pagination.current_page;
        this.totalPages = pagination.last_page;
        this.updatePaginationInfo(pagination);
        this.generatePageNumbers();
    }

    updatePaginationInfo(pagination = null) {
        if (pagination) {
            $('#entriesFrom').text(pagination.from || 0);
            $('#entriesTo').text(pagination.to || 0);
            $('#entriesTotal').text(pagination.total || 0);
        }
    }

    generatePageNumbers() {
        const container = $('#pageNumbersContainer');
        container.empty();

        $('#paginationControls').show();

        // Enable/disable navigation buttons
        $('#firstPageBtn, #prevPageBtn').prop('disabled', this.currentPage === 1);
        $('#lastPageBtn, #nextPageBtn').prop('disabled', this.currentPage === this.totalPages);

        // Generate page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(this.totalPages, startPage + maxVisible - 1);

        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === this.currentPage;
            const btn = $(`
                <button type="button"
                        class="btn btn-sm page-number-btn ${isActive ? 'btn-primary' : 'btn-outline-secondary'}"
                        data-page="${i}">
                    ${i}
                </button>
            `);
            container.append(btn);
        }
    }

    showTableLoading() {
        $('.loading-spinner').show();
    }

    hideTableLoading() {
        $('.loading-spinner').hide();
    }

    // ========================================
    // ALERT SYSTEM
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

    // ========================================
    // ERROR/SUCCESS MESSAGE METHODS
    // ========================================

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

    escapeHtml(text) {
        if (!text) return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    performDelete() {
        if (!this.deleteNik) return;

        $.ajax({
            url: `/mastermanager/${this.deleteNik}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                $('#deleteConfirmModal').modal('hide');
                if (response.success) {
                    this.showAlert(response.message, 'success');
                    setTimeout(() => {
                        this.loadData();
                    }, 1000);
                } else {
                    this.showAlert(response.message || 'Terjadi kesalahan saat menghapus data', 'error');
                }
            },
            error: (xhr) => {
                $('#deleteConfirmModal').modal('hide');
                this.showAlert('Terjadi kesalahan saat menghapus data', 'error');
                console.error('Error deleting manager:', xhr);
            }
        });
    }
}

// Global functions for onclick events
function viewManager(nik) {
    $.ajax({
        url: `/mastermanager/${nik}`,
        type: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: (response) => {
            if (response.success) {
                const manager = response.data;
                const statusText = manager.status === 'A' ? 'Aktif' : 'Non Aktif';
                const statusBadge = manager.status === 'A' ? 'bg-success' : 'bg-secondary';
                const namaDisplay = manager.nama || '-';

                const content = `
                    <div class="modal-info-section">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="modal-info-section">
                                    <h6>Informasi Manager</h6>
                                    <table class="table table-sm">
                                        <tr><td>Nomor Induk Karyawan:</td><td><strong>${manager.nik}</strong></td></tr>
                                        <tr><td>Nama Manager:</td><td><strong>${namaDisplay}</strong></td></tr>
                                        <tr><td>Status:</td><td><span class="badge ${statusBadge}">${statusText}</span></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#viewManagerContent').html(content);
                $('#viewManagerModal').modal('show');
            } else {
                if (window.managerManager) {
                    window.managerManager.showAlert('Data tidak ditemukan', 'error');
                }
            }
        },
        error: (xhr) => {
            if (window.managerManager) {
                window.managerManager.showAlert('Terjadi kesalahan saat memuat data', 'error');
            }
            console.error('Error loading manager details:', xhr);
        }
    });
}

function editManager(nik) {
    window.location.href = `/mastermanager/${nik}/edit`;
}

function deleteManager(nik) {
    if (window.managerManager) {
        window.managerManager.deleteNik = nik;
    }
    $('#deleteConfirmModal').modal('show');
}

function resetForm() {
    if (window.originalFormData) {
        $('#nama').val(window.originalFormData.nama);
        $('#status').val(window.originalFormData.status);

        $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
        $('.error-message').text('');
        $('.success-message').text('');
    }

    if (window.managerManager) {
        window.managerManager.clearAllErrors();
    }
}
