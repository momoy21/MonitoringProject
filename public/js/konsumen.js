class KonsumenManager {
    constructor() {
        this.allCities = [];
        this.isSubmitting = false;
        this.searchTimeout = null;
        this.deleteTargetId = null;

        // Pagination variables (for index page)
        this.currentPage = 1;
        this.totalPages = 1;
        this.perPage = 10;
        this.currentSearch = '';

        // Form validation variables (for create/edit)
        this.originalEmail = '';
        this.currentKonsumenId = '';
    }

    // ========================================
    // INITIALIZATION METHODS
    // ========================================

    init(config = {}) {
        this.setConfig(config);
        this.loadAllCities();
        this.initializeEventHandlers();

        // Initialize page-specific functions
        if (config.pageType === 'index') {
            this.updatePaginationButtons();
        }
    }

    setConfig(config) {
        this.originalEmail = config.originalEmail || '';
        this.currentKonsumenId = config.currentKonsumenId || '';
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
    }

    // ========================================
    // DATA LOADING METHODS
    // ========================================

    loadAllCities() {
        const citiesRoute = window.Laravel?.routes?.allCities || '/api/all-cities';

        $.ajax({
            url: citiesRoute,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    this.allCities = response.data;

                    // Auto-populate cities if province is already selected
                    const selectedProvinceId = $('#provinsi_id').val();
                    if (selectedProvinceId) {
                        this.populateCitiesForProvince(selectedProvinceId);
                    }
                }
            },
            error: (xhr) => {
                console.error('Error loading cities:', xhr);
            }
        });
    }

    // ========================================
    // EVENT HANDLERS
    // ========================================

    initializeEventHandlers() {
        // Province change handler
        $('#provinsi_id').on('change', (e) => {
            const provinceId = $(e.target).val();
            this.populateCitiesForProvince(provinceId);

            // Check if current city is still valid (for edit page)
            const currentCityId = $('#kota_id').val();
            if (currentCityId && this.allCities.length > 0) {
                const cityStillValid = this.allCities.some(city =>
                    city.id == currentCityId && city.provinsi_id == provinceId
                );
                if (!cityStillValid) {
                    $('#kota_id').val('');
                }
            }
            this.clearFieldError('provinsi_id');
        });

        // City change handler
        $('#kota_id').on('change', (e) => {
            const cityId = $(e.target).val();
            if (cityId && !$('#provinsi_id').val()) {
                this.autoSelectProvinceFromCity(cityId);
            }
            this.clearFieldError('kota_id');
        });

        // Form submit handler (for create/edit pages)
        $('#konsumenForm').on('submit', (e) => {
            e.preventDefault();
            if (!this.isSubmitting) {
                this.submitForm();
            }
        });

        // Real-time validation
        this.initializeFieldValidation();

        // Search handler (for index page)
        this.initializeSearchHandler();

        // Pagination handlers (for index page)
        this.initializePaginationHandlers();

        // Modal handlers (for index page)
        this.initializeModalHandlers();
    }

    initializeFieldValidation() {
        // Konsumen name validation
        $('#konsumen').on('input blur', (e) => {
            this.validateField('konsumen', $(e.target).val());
        });

        // Email validation
        $('#email').on('input blur', () => {
            this.validateEmail();
        });

        // Kode pos validation
        $('#kode_pos').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            window.konsumenManager.validateField('kode_pos', this.value);
        });

        // Telp kantor validation
        $('#telp_kantor').on('input', function() {
            this.value = this.value.replace(/[^0-9\-]/g, '');
            window.konsumenManager.validateField('telp_kantor', this.value);
        });

        // Fax validation
        $('#fax').on('input', function() {
            this.value = this.value.replace(/[^0-9\-]/g, '');
            window.konsumenManager.validateField('fax', this.value);
        });


        // Clear errors on input
        $('input, select').on('input change', (e) => {
            const fieldName = $(e.target).attr('name');
            this.clearFieldError(fieldName);
            $(e.target).removeClass('is-invalid is-valid');
        });
    }

    initializeSearchHandler() {
        $('#searchInput').on('input', (e) => {
            const searchValue = $(e.target).val().trim();
            this.currentSearch = searchValue;
            this.currentPage = 1;

            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.loadKonsumenData();
            }, 300);
        });

        // Per page selector handler
        $('#perPageSelect').on('change', (e) => {
            this.perPage = $(e.target).val();
            this.currentPage = 1;
            this.loadKonsumenData();
        });
    }

    initializePaginationHandlers() {
        // First page handler
        $('#firstPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage = 1;
                this.loadKonsumenData();
            }
        });

        // Previous page handler
        $('#prevPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadKonsumenData();
            }
        });

        // Next page handler
        $('#nextPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadKonsumenData();
            }
        });

        // Last page handler
        $('#lastPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage = this.totalPages;
                this.loadKonsumenData();
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
    // CITY/PROVINCE METHODS
    // ========================================

    populateCitiesForProvince(provinceId) {
        const citySelect = $('#kota_id');
        const currentCityId = citySelect.val();

        citySelect.empty().append('<option value="">-- Pilih Kota --</option>');

        if (provinceId && this.allCities.length > 0) {
            const filteredCities = this.allCities.filter(city => city.provinsi_id == provinceId);
            filteredCities.forEach(city => {
                const selected = city.id == currentCityId ? 'selected' : '';
                citySelect.append(`<option value="${city.id}" ${selected}>${city.nama}</option>`);
            });
        }
    }

    autoSelectProvinceFromCity(cityId) {
        const selectedCity = this.allCities.find(city => city.id == cityId);
        if (selectedCity && selectedCity.provinsi_id) {
            $('#provinsi_id').val(selectedCity.provinsi_id);
        }
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
            case 'konsumen':
                if (!value.trim()) {
                    isValid = false;
                    errorMessage = 'Nama konsumen harus diisi.';
                } else if (value.length > 150) {
                    isValid = false;
                    errorMessage = 'Nama konsumen maksimal 150 karakter.';
                }
                break;
            case 'kode_pos':
                if (value && (value.length !== 5 || !/^[0-9]{5}$/.test(value))) {
                    isValid = false;
                    errorMessage = 'Kode pos harus berupa 5 digit angka.';
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

    validateEmail() {
        const email = $('#email').val().trim();
        const field = $('#email');

        this.clearFieldError('email');
        this.clearFieldSuccess('email');

        if (!email) {
            field.removeClass('is-invalid is-valid');
            return true;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showFieldError('email', 'Format email tidak valid.');
            field.addClass('is-invalid');
            return false;
        }

        if (email !== this.originalEmail) {
            this.checkEmailUniqueness(email);
        } else {
            field.addClass('is-valid');
        }
        return true;
    }

    checkEmailUniqueness(email) {
        const konsumenIndexRoute = window.Laravel?.routes?.konsumenIndex || '/konsumen';

        $.ajax({
            url: konsumenIndexRoute,
            type: 'GET',
            data: { search: email },
            success: (response) => {
                const field = $('#email');
                const existingKonsumen = response.data.find(k =>
                    k.email === email && k.id_konsumen !== this.currentKonsumenId
                );

                if (existingKonsumen) {
                    this.showFieldError('email', 'Email sudah terdaftar.');
                    field.addClass('is-invalid');
                } else {
                    this.showFieldSuccess('email', 'Email tersedia.');
                    field.addClass('is-valid');
                }
            },
            error: () => {
                $('#email').addClass('is-valid');
            }
        });
    }

    // ========================================
    // FORM SUBMISSION METHODS
    // ========================================

    submitForm() {
        this.clearAllErrors();

        let isFormValid = true;
        isFormValid &= this.validateField('konsumen', $('#konsumen').val());

        if (!isFormValid) {
            this.showAlert('Mohon perbaiki kesalahan pada form.', 'error');
            return;
        }

        this.setSubmitLoading(true);

        const formData = new FormData($('#konsumenForm')[0]);

        $.ajax({
            url: $('#konsumenForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    this.showAlert(response.message, 'success');
                    setTimeout(() => {
                        const konsumenIndexRoute = window.Laravel?.routes?.konsumenIndex || '/konsumen';
                        window.location.href = konsumenIndexRoute;
                    }, 1500);
                } else {
                    this.showAlert(response.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
                    this.setSubmitLoading(false);
                }
            },
            error: (xhr) => {
                this.setSubmitLoading(false);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        this.showFieldError(field, errors[field][0]);
                        $(`#${field}`).addClass('is-invalid');
                    });
                    this.showAlert('Mohon perbaiki kesalahan pada form.', 'error');
                } else {
                    this.showAlert('Terjadi kesalahan saat menyimpan data.', 'error');
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
    // INDEX PAGE METHODS
    // ========================================

    loadKonsumenData() {
        this.showLoadingSpinner(true);

        const params = {
            search: this.currentSearch,
            per_page: this.perPage,
            page: this.currentPage
        };

        const konsumenIndexRoute = window.Laravel?.routes?.konsumenIndex || '/konsumen';

        $.ajax({
            url: konsumenIndexRoute,
            type: 'GET',
            data: params,
            success: (response) => {
                if (response.success) {
                    this.updateTableContent(response.data);
                    this.updatePaginationInfo(response.pagination);
                    this.updatePaginationButtons();
                }
            },
            error: (xhr) => {
                console.error('Error loading data:', xhr);
            },
            complete: () => {
                this.showLoadingSpinner(false);
            }
        });
    }

    updateTableContent(data) {
        const tbody = $('#konsumenTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data konsumen</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(item => {
            const alamat = this.buildAlamatString(item);
            const kontak = this.buildKontakString(item);
            const statusBadge = item.status === 'A' ?
                '<span class="badge bg-success">Aktif</span>' :
                '<span class="badge bg-secondary">Non Aktif</span>';

            tbody.append(`
                <tr>
                    <td>
                        <span class="konsumen-id" data-id="${item.id_konsumen}" ondblclick="window.konsumenManager.editKonsumen('${item.id_konsumen}')">
                            ${item.id_konsumen}
                        </span>
                    </td>
                    <td class="fw-semibold">
                        <div class="truncate-text" title="${this.escapeHtml(item.konsumen)}">
                            ${this.escapeHtml(item.konsumen)}
                        </div>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.provinsi?.nama || '-')}">
                            ${this.escapeHtml(item.provinsi?.nama || '-')}
                        </div>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.kota?.nama || '-')}">
                            ${this.escapeHtml(item.kota?.nama || '-')}
                        </div>
                    </td>
                    <td>
                        <small class="multiline-text" title="${this.escapeHtml(alamat)}">
                            ${this.escapeHtml(alamat)}
                        </small>
                    </td>
                    <td><small class="multiline-text">${kontak}</small></td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="window.konsumenManager.viewKonsumen('${item.id_konsumen}')">
                                    <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
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

        $('#entriesFrom').text(pagination.from || 0);
        $('#entriesTo').text(pagination.to || 0);
        $('#entriesTotal').text(pagination.total || 0);
    }

    updatePaginationButtons() {
        $('#firstPageBtn').prop('disabled', this.currentPage <= 1);
        $('#prevPageBtn').prop('disabled', this.currentPage <= 1);
        $('#nextPageBtn').prop('disabled', this.currentPage >= this.totalPages);
        $('#lastPageBtn').prop('disabled', this.currentPage >= this.totalPages);

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
                this.loadKonsumenData();
            }
        });
    }

    // ========================================
    // MODAL & ACTION METHODS
    // ========================================

    editKonsumen(id) {
        const konsumenIndexRoute = window.Laravel?.routes?.konsumenIndex || '/konsumen';
        window.location.href = `${konsumenIndexRoute}/${id}/edit`;
    }

    viewKonsumen(id) {
        const konsumenIndexRoute = window.Laravel?.routes?.konsumenIndex || '/konsumen';

        $.ajax({
            url: `${konsumenIndexRoute}/${id}`,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    this.displayKonsumenDetails(response.data);
                    $('#viewKonsumenModal').modal('show');
                }
            },
            error: (xhr) => {
                console.error('Error loading konsumen details:', xhr);
            }
        });
    }

    displayKonsumenDetails(data) {
        const alamat = this.buildAlamatString(data);
        const statusBadge = data.status === 'A' ?
            '<span class="badge bg-success">Aktif</span>' :
            '<span class="badge bg-secondary">Non Aktif</span>';

        const content = `
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="modal-info-section">
                            <h6>Informasi Dasar</h6>
                            <table class="table table-sm">
                                <tr><td>ID Konsumen:</td><td><strong>${data.id_konsumen}</strong></td></tr>
                                <tr><td>Nama:</td><td><strong>${data.konsumen}</strong></td></tr>
                                <tr><td>Status:</td><td>${statusBadge}</td></tr>
                                <tr><td>Provinsi:</td><td>${data.provinsi?.nama || '-'}</td></tr>
                                <tr><td>Kota:</td><td>${data.kota?.nama || '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="modal-info-section">
                            <h6>Informasi Kontak</h6>
                            <table class="table table-sm">
                                <tr><td>Telp Kantor:</td><td>${data.telp_kantor || '-'}</td></tr>
                                <tr><td>Fax:</td><td>${data.fax || '-'}</td></tr>
                                <tr><td>Email:</td><td>${data.email || '-'}</td></tr>
                                <tr><td>Kode Pos:</td><td>${data.kode_pos || '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-info-section">
                <h6>Alamat Lengkap</h6>
                <div class="alamat-lengkap">
                    ${alamat}
                </div>
            </div>
        `;
        $('#viewKonsumenContent').html(content);
    }

    deleteKonsumen(id) {
        this.deleteTargetId = id;
        $('#deleteConfirmModal').modal('show');
    }

    performDelete(id) {
        const konsumenIndexRoute = window.Laravel?.routes?.konsumenIndex || '/konsumen';

        $.ajax({
            url: `${konsumenIndexRoute}/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                $('#deleteConfirmModal').modal('hide');
                if (response.success) {
                    this.showAlert(response.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showAlert(response.message || 'Terjadi kesalahan saat menghapus data', 'error');
                }
            },
            error: (xhr) => {
                $('#deleteConfirmModal').modal('hide');
                this.showAlert('Terjadi kesalahan saat menghapus data', 'error');
                console.error('Error deleting konsumen:', xhr);
            }
        });
        this.deleteTargetId = null;
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

    buildAlamatString(item) {
        const alamatParts = [
            item.alamat1,
            item.alamat2,
            item.kota?.nama,
            item.provinsi?.nama,
            item.kode_pos
        ].filter(part => part && part.trim() !== '');

        return alamatParts.length > 0 ? alamatParts.join(', ') : '-';
    }

    buildKontakString(item) {
        const kontakParts = [];
        if (item.telp_kantor) kontakParts.push(`<div>Telp: ${item.telp_kantor}</div>`);
        if (item.email) kontakParts.push(`<div>Email: ${item.email}</div>`);

        return kontakParts.length > 0 ? kontakParts.join('') : '-';
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
        $('.invalid-feedback').text('');
        $('.valid-feedback').text('');
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        this.hideAlert();
    }

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
}

// ========================================
// GLOBAL FUNCTIONS (for onclick handlers)
// ========================================

// Global functions for onclick handlers
window.editKonsumen = function(id) {
    if (window.konsumenManager) {
        window.konsumenManager.editKonsumen(id);
    }
};

window.viewKonsumen = function(id) {
    if (window.konsumenManager) {
        window.konsumenManager.viewKonsumen(id);
    }
};

window.deleteKonsumen = function(id) {
    if (window.konsumenManager) {
        window.konsumenManager.deleteKonsumen(id);
    }
};

window.resetForm = function() {
    if (window.konsumenManager) {
        const originalData = window.originalFormData || {};
        window.konsumenManager.resetForm(originalData);
    }
};
