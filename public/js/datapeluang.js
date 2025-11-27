class DataPeluangManager {
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
        this.currentDataPeluangId = '';
    }

    // ========================================
    // INITIALIZATION METHODS
    // ========================================

    init(config = {}) {
        this.setConfig(config);
        this.initializeEventHandlers();

        // Initialize dropdowns and fields
        this.initializeDropdowns();

        // Initialize page-specific functions
        if (config.pageType === 'index') {
            this.updatePaginationButtons();
        }
    }

    setConfig(config) {
        this.currentDataPeluangId = config.currentDataPeluangId || '';
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
    }

    initializeDropdowns() {
        // Initialize status dropdown if not already filled
        const statusSelect = $('#status');
        if (statusSelect.length && statusSelect.find('option').length <= 1) {
            statusSelect.append('<option value="N">New</option>');
            statusSelect.append('<option value="I">In Progress</option>');
            statusSelect.append('<option value="D">Close</option>');
            statusSelect.append('<option value="C">Cancel</option>');
            statusSelect.val('N'); // Default to New
        }
    }

    // ========================================
    // EVENT HANDLERS
    // ========================================

    initializeEventHandlers() {
        // Form submit handler (for create/edit pages)
        $('#dataPeluangForm').on('submit', (e) => {
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

        // Currency formatting
        this.initializeCurrencyFields();

        // Date formatting
        this.initializeDateFields();
    }

    initializeFieldValidation() {
        // Peluang name validation
        $('#peluang').on('input blur', (e) => {
            this.validateField('peluang', $(e.target).val());
        });

        // Konsumen validation
        $('#id_konsumen').on('change', (e) => {
            this.validateField('id_konsumen', $(e.target).val());
        });

        // Date validation
        $('#tgl_peluang, #target_peluang').on('change', (e) => {
            this.validateDates();
        });

        // Phone number formatting and validation
        $('#no_hp').on('input', function() {
            // Remove non-numeric characters except +, -, and spaces
            this.value = this.value.replace(/[^0-9\+\-\s]/g, '');

            // Limit to 25 characters
            if (this.value.length > 25) {
                this.value = this.value.substring(0, 25);
            }

            window.dataPeluangManager.validateField('no_hp', this.value);
        });

        // Clear errors on input
        $('input, select, textarea').on('input change', (e) => {
            const fieldName = $(e.target).attr('name');
            this.clearFieldError(fieldName);
            $(e.target).removeClass('is-invalid is-valid');
        });
    }

    initializeCurrencyFields() {
        // Format currency inputs
        $('#biaya_peluang, #pagu_peluang').on('input', function() {
            const value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = window.dataPeluangManager.formatCurrencyInput(value);
            }
        });

        // Store original values as data attributes for form submission
        $('#biaya_peluang, #pagu_peluang').on('input', function() {
            const cleanValue = this.value.replace(/[^\d]/g, '');
            $(this).attr('data-clean-value', cleanValue);
        });
    }

    initializeDateFields() {
        // Setup date fields with manual input and date picker icon
        $('#tgl_peluang, #target_peluang').each(function() {
            const $input = $(this);
            const $container = $input.parent();
            const fieldId = $input.attr('id');

            // Create input group wrapper
            const $inputGroup = $('<div class="input-group date-input-group"></div>');

            // Create text input for manual entry
            const $textInput = $('<input>')
                .attr('type', 'text')
                .attr('name', $input.attr('name'))
                .attr('id', fieldId)
                .addClass('form-control')
                .attr('placeholder', 'dd/mm/yyyy')
                .attr('maxlength', '10');

            // Create hidden date input for picker
            const $dateInput = $('<input>')
                .attr('type', 'date')
                .addClass('date-picker-hidden');

            // Create calendar icon button
            const $calendarBtn = $('<button>')
                .attr('type', 'button')
                .addClass('btn btn-outline-secondary')
                .attr('tabindex', '-1')
                .html('<i class="bx bx-calendar"></i>')
                .attr('title', 'Pilih tanggal');
            $input.replaceWith($inputGroup);
            $inputGroup.append($textInput);
            $inputGroup.append($calendarBtn);
            $inputGroup.append($dateInput);

            // Set initial value if exists
            const initialValue = $input.val();
            if (initialValue) {
                if (initialValue.includes('-')) {
                    // Convert from yyyy-mm-dd to dd/mm/yyyy
                    const date = new Date(initialValue);
                    if (!isNaN(date)) {
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = date.getFullYear();
                        $textInput.val(`${day}/${month}/${year}`);
                        $dateInput.val(initialValue);
                    }
                } else {
                    $textInput.val(initialValue);
                }
            }

            // Handle manual input with masking
            $textInput.on('input', function() {
                let value = this.value.replace(/\D/g, ''); // Remove non-digits

                // Apply dd/mm/yyyy mask
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0, 5) + '/' + value.substring(5, 9);
                }

                this.value = value;

                // Validate and sync with hidden date input
                if (value.length === 10) {
                    const isValid = window.dataPeluangManager.validateDateField(value);
                    if (isValid) {
                        // Convert to yyyy-mm-dd for date input
                        const parts = value.split('/');
                        const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        $dateInput.val(isoDate);
                    }
                }

                // Trigger validation
                window.dataPeluangManager.validateField(fieldId, value);
            });

            // Handle calendar button click
            $calendarBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Temporarily show the date input
                $dateInput.removeClass('date-picker-hidden')
                        .css({
                            'position': 'absolute',
                            'top': '0',
                            'left': '0',
                            'width': '100%',
                            'height': '100%',
                            'opacity': '0',
                            'z-index': '9999'
                        })
                        .appendTo($inputGroup);

                // Focus and trigger date picker
                $dateInput.focus();

                // To open date picker
                setTimeout(() => {
                    try {
                        if ($dateInput[0].showPicker) {
                            $dateInput[0].showPicker();
                        } else {
                            $dateInput.trigger('click');
                        }
                    } catch (err) {
                        $dateInput[0].click();
                    }
                }, 50);

                // Hide after interaction
                $dateInput.one('change blur', function() {
                    $dateInput.addClass('date-picker-hidden')
                            .css({
                                'position': '',
                                'top': '',
                                'left': '',
                                'width': '',
                                'height': '',
                                'opacity': '',
                                'z-index': ''
                            });
                });
            });

            // Handle date picker change
            $dateInput.on('change', function() {
                const dateValue = this.value;
                if (dateValue) {
                    const date = new Date(dateValue);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    const displayValue = `${day}/${month}/${year}`;

                    $textInput.val(displayValue);

                    // Trigger validation
                    window.dataPeluangManager.validateField(fieldId, displayValue);
                }
            });

            // Handle blur validation
            $textInput.on('blur', function() {
                window.dataPeluangManager.validateField(fieldId, this.value);
            });
        });

        // Date validation on change for date comparison
        $('#tgl_peluang, #target_peluang').on('change', (e) => {
            this.validateDates();
        });
    }    initializeSearchHandler() {
        $('#searchInput').on('input', (e) => {
            const searchValue = $(e.target).val().trim();
            this.currentSearch = searchValue;
            this.currentPage = 1;

            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.loadDataPeluangData();
            }, 300);
        });

        // Per page selector handler
        $('#perPageSelect').on('change', (e) => {
            this.perPage = $(e.target).val();
            this.currentPage = 1;
            this.loadDataPeluangData();
        });
    }

    initializePaginationHandlers() {
        // First page handler
        $('#firstPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage = 1;
                this.loadDataPeluangData();
            }
        });

        // Previous page handler
        $('#prevPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadDataPeluangData();
            }
        });

        // Next page handler
        $('#nextPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadDataPeluangData();
            }
        });

        // Last page handler
        $('#lastPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage = this.totalPages;
                this.loadDataPeluangData();
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
    // VALIDATION METHODS
    // ========================================

    validateField(fieldName, value) {
        this.clearFieldError(fieldName);
        const field = $(`#${fieldName}`);
        let isValid = true;
        let errorMessage = '';

        switch (fieldName) {
            case 'peluang':
                if (!value.trim()) {
                    isValid = false;
                    errorMessage = 'Nama peluang harus diisi.';
                }
                break;
            case 'id_konsumen':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Konsumen harus dipilih.';
                }
                break;
            case 'no_hp':
                if (value && value.length < 10) {
                    isValid = false;
                    errorMessage = 'Nomor HP minimal 10 karakter.';
                }
                break;
            case 'tgl_peluang':
            case 'target_peluang':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Tanggal harus diisi.';
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

    validateCurrencyField(fieldName) {
        const field = $(`#${fieldName}`);
        const value = field.val();

        this.clearFieldError(fieldName);

        // Currency fields are optional, so empty is valid
        if (!value) {
            return true;
        }

        // Remove formatting and check if it's a valid number
        const cleanValue = value.replace(/[^\d]/g, '');

        if (cleanValue && parseInt(cleanValue) < 0) {
            this.showFieldError(fieldName, 'Nilai tidak boleh negatif.');
            field.addClass('is-invalid');
            return false;
        }

        if (value) {
            field.addClass('is-valid');
        }

        return true;
    }

    validateDateField(dateString) {
        // Check format dd/mm/yyyy
        const datePattern = /^\d{2}\/\d{2}\/\d{4}$/;
        if (!datePattern.test(dateString)) {
            return false;
        }

        // Parse date components
        const [day, month, year] = dateString.split('/').map(num => parseInt(num, 10));

        // Create date object (month is 0-indexed in JavaScript)
        const date = new Date(year, month - 1, day);

        // Verify the date is valid and matches input
        return date.getFullYear() === year &&
               date.getMonth() === month - 1 &&
               date.getDate() === day;
    }

    validateDates() {
        const tglPeluang = $('#tgl_peluang').val();
        const targetPeluang = $('#target_peluang').val();

        this.clearFieldError('target_peluang');

        if (tglPeluang && targetPeluang) {
            // Parse dates from dd/mm/yyyy format
            const tgl = this.parseDate(tglPeluang);
            const target = this.parseDate(targetPeluang);

            if (tgl && target && target < tgl) {
                this.showFieldError('target_peluang', 'Target peluang tidak boleh sebelum tanggal peluang.');
                $('#target_peluang').addClass('is-invalid');
                return false;
            } else if (target) {
                $('#target_peluang').addClass('is-valid');
            }
        }

        return true;
    }

    parseDate(dateString) {
        if (!dateString || !this.validateDateField(dateString)) {
            return null;
        }

        const [day, month, year] = dateString.split('/').map(num => parseInt(num, 10));
        return new Date(year, month - 1, day);
    }

    // ========================================
    // FORM SUBMISSION METHODS
    // ========================================

    submitForm() {
        try {
            this.clearAllErrors();

            let isFormValid = true;
            isFormValid &= this.validateField('peluang', $('#peluang').val());
            isFormValid &= this.validateField('id_konsumen', $('#id_konsumen').val());

            // Validate date fields
            isFormValid &= this.validateField('tgl_peluang', $('#tgl_peluang').val());
            isFormValid &= this.validateField('target_peluang', $('#target_peluang').val());
            isFormValid &= this.validateDates();

            // Validate currency fields
            isFormValid &= this.validateCurrencyField('biaya_peluang');
            isFormValid &= this.validateCurrencyField('pagu_peluang');

            if (!isFormValid) {
                this.showAlert('Mohon perbaiki kesalahan pada form.', 'error');
                return;
            }

            this.setSubmitLoading(true);

        // Create FormData and clean currency values
        const formData = new FormData($('#dataPeluangForm')[0]);

        // Clean currency values before sending
        const biayaValue = $('#biaya_peluang').val().replace(/[^\d]/g, '');
        const paguValue = $('#pagu_peluang').val().replace(/[^\d]/g, '');

        if (biayaValue) {
            formData.set('biaya_peluang', biayaValue);
        }
        if (paguValue) {
            formData.set('pagu_peluang', paguValue);
        }

        // Convert date values from dd/mm/yyyy to Y-m-d
        const tglPeluang = $('#tgl_peluang').val();
        const targetPeluang = $('#target_peluang').val();

        if (tglPeluang && tglPeluang.includes('/')) {
            const parts = tglPeluang.split('/');
            const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            formData.set('tgl_peluang', isoDate);
        }

        if (targetPeluang && targetPeluang.includes('/')) {
            const parts = targetPeluang.split('/');
            const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            formData.set('target_peluang', isoDate);
        }

        $.ajax({
            url: $('#dataPeluangForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    this.showAlert(response.message, 'success');
                    setTimeout(() => {
                        const dataPeluangIndexRoute = window.Laravel?.routes?.dataPeluangIndex || '/datapeluang';
                        window.location.href = dataPeluangIndexRoute;
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

        } catch (error) {
            console.error('Submit form error:', error);
            this.setSubmitLoading(false);
            this.showAlert('Terjadi kesalahan saat memproses form.', 'error');
        }
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

    loadDataPeluangData() {
        this.showLoadingSpinner(true);

        const params = {
            search: this.currentSearch,
            per_page: this.perPage,
            page: this.currentPage
        };

        const dataPeluangIndexRoute = window.Laravel?.routes?.dataPeluangIndex || '/datapeluang';

        $.ajax({
            url: dataPeluangIndexRoute,
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
        const tbody = $('#dataPeluangTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data peluang</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(item => {
            const statusBadge = item.status_badge || this.getStatusBadge(item.status);
            const statusLabel = item.status_label || this.getStatusLabel(item.status);
            const biayaFormatted = item.biaya_peluang_formatted || (item.biaya_peluang ? this.formatCurrency(item.biaya_peluang) : '-');
            const paguFormatted = item.pagu_peluang_formatted || (item.pagu_peluang ? this.formatCurrency(item.pagu_peluang) : '-');

            tbody.append(`
                <tr class="editable-row" ondblclick="editDataPeluang('${item.id_datapeluang}')" title="Double-click untuk edit" style="cursor: pointer;">
                    <td>
                        <span class="datapeluang-id" data-id="${item.id_datapeluang}">
                            ${item.id_datapeluang}
                        </span>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.peluang)}">
                            ${this.escapeHtml(item.peluang)}
                        </div>
                    </td>
                    <td>
                        <div class="truncate-text" title="${this.escapeHtml(item.konsumen?.konsumen || '-')}">
                            ${this.escapeHtml(item.konsumen?.konsumen || '-')}
                        </div>
                    </td>
                    <td class="currency-display">${biayaFormatted}</td>
                    <td class="currency-display">${paguFormatted}</td>
                    <td>
                        <div class="date-container">
                            <div class="date-row">
                                <small class="text-muted">Tgl:</small>
                                <small>${this.formatDate(item.tgl_peluang)}</small>
                            </div>
                            <div class="date-row">
                                <small class="text-muted">Target:</small>
                                <small>${this.formatDate(item.target_peluang)}</small>
                            </div>
                        </div>
                    </td>
                    <td onclick="event.stopPropagation();">
                        <span class="${statusBadge}">${statusLabel}</span>
                    </td>
                    <td onclick="event.stopPropagation();">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewDataPeluang('${item.id_datapeluang}')">
                                    <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteDataPeluang('${item.id_datapeluang}')">
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
                this.loadDataPeluangData();
            }
        });
    }

    // ========================================
    // MODAL & ACTION METHODS
    // ========================================

    editDataPeluang(id) {
        const dataPeluangIndexRoute = window.Laravel?.routes?.dataPeluangIndex || '/datapeluang';
        window.location.href = `${dataPeluangIndexRoute}/${id}/edit`;
    }

    viewDataPeluang(id) {
        const dataPeluangIndexRoute = window.Laravel?.routes?.dataPeluangIndex || '/datapeluang';

        $.ajax({
            url: `${dataPeluangIndexRoute}/${id}`,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    this.displayDataPeluangDetails(response.data);
                    $('#viewDataPeluangModal').modal('show');
                }
            },
            error: (xhr) => {
                console.error('Error loading data peluang details:', xhr);
            }
        });
    }

    displayDataPeluangDetails(data) {
        const statusBadge = data.status_badge || this.getStatusBadge(data.status);
        const statusLabel = data.status_label || this.getStatusLabel(data.status);
        const biayaFormatted = data.biaya_peluang_formatted || (data.biaya_peluang ? this.formatCurrency(data.biaya_peluang) : '-');
        const paguFormatted = data.pagu_peluang_formatted || (data.pagu_peluang ? this.formatCurrency(data.pagu_peluang) : '-');

        const content = `
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="modal-info-section">
                            <h6>Informasi Peluang</h6>
                            <table class="table table-sm">
                                <tr><td>ID Peluang:</td><td><strong>${data.id_datapeluang}</strong></td></tr>
                                <tr><td>Nama Peluang:</td><td><strong>${data.peluang}</strong></td></tr>
                                <tr><td>Konsumen:</td><td>${data.konsumen?.konsumen || '-'}</td></tr>
                                <tr><td>Status:</td><td><span class="${statusBadge}">${statusLabel}</span></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="modal-info-section">
                            <h6>Informasi Kontak</h6>
                            <table class="table table-sm">
                                <tr><td>Kontak Person:</td><td>${data.kontak_person || '-'}</td></tr>
                                <tr><td>No. HP:</td><td>${data.no_hp || '-'}</td></tr>
                                <tr><td>Lokasi:</td><td>${data.lokasi || '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Timeline</h6>
                        <table class="table table-sm">
                            <tr><td>Tanggal Peluang:</td><td>${this.formatDate(data.tgl_peluang)}</td></tr>
                            <tr><td>Target Peluang:</td><td>${this.formatDate(data.target_peluang)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Nilai</h6>
                        <table class="table table-sm">
                            <tr><td>Estimasi Biaya:</td><td>${biayaFormatted}</td></tr>
                            <tr><td>Target Nilai:</td><td>${paguFormatted}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        `;
        $('#viewDataPeluangContent').html(content);
    }

    deleteDataPeluang(id) {
        this.deleteTargetId = id;
        $('#deleteConfirmModal').modal('show');
    }

    performDelete(id) {
        const dataPeluangIndexRoute = window.Laravel?.routes?.dataPeluangIndex || '/datapeluang';

        $.ajax({
            url: `${dataPeluangIndexRoute}/${id}`,
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
                console.error('Error deleting data peluang:', xhr);
            }
        });
        this.deleteTargetId = null;
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    resetForm(originalData = {}) {
        Object.keys(originalData).forEach(key => {
            const field = $(`#${key}`);
            if (field.length) {
                if (key.includes('peluang') && typeof originalData[key] === 'number') {
                    // For currency fields, use formatCurrencyInput without "Rp" prefix
                    field.val(this.formatCurrencyInput(originalData[key]));
                } else if (key === 'tgl_peluang' || key === 'target_peluang') {
                    // For date fields, convert from Y-m-d to dd/mm/yyyy
                    const dateValue = originalData[key];
                    if (dateValue && dateValue.includes('-')) {
                        // Convert from yyyy-mm-dd to dd/mm/yyyy
                        const date = new Date(dateValue);
                        if (!isNaN(date)) {
                            const day = String(date.getDate()).padStart(2, '0');
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const year = date.getFullYear();
                            field.val(`${day}/${month}/${year}`);
                        } else {
                            field.val(dateValue);
                        }
                    } else {
                        field.val(dateValue || '');
                    }
                } else {
                    field.val(originalData[key] || '');
                }
            }
        });
        this.clearAllErrors();
    }

    formatCurrency(value) {
        if (!value) return '<div class="text-center text-muted">-</div>';
        const number = typeof value === 'string' ? parseFloat(value.replace(/[^\d]/g, '')) : value;
        if (isNaN(number) || number === 0) return '<div class="text-center text-muted">-</div>';
        const formattedNumber = number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        return `<div class="d-flex justify-content-between align-items-center" style="gap: 0.5rem;"><span>Rp</span><span>${formattedNumber}</span></div>`;
    }

    formatCurrencyInput(value) {
        if (!value) return '';
        const number = typeof value === 'string' ? parseFloat(value.replace(/[^\d]/g, '')) : value;
        if (isNaN(number) || number === 0) return '';
        return new Intl.NumberFormat('id-ID').format(number);
    }

    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    getStatusLabel(status) {
        return {
            'N': 'New',
            'I': 'In Progress',
            'D': 'Close',
            'C': 'Cancel'
        }[status] || 'Unknown';
    }

    getStatusBadge(status) {
        return {
            'N': 'badge bg-info',
            'I': 'badge bg-primary',
            'D': 'badge bg-success',
            'C': 'badge bg-danger'
        }[status] || 'badge bg-secondary';
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
        $('.error-message').text('');
        $('.success-message').text('');
        $('.form-control').removeClass('is-invalid is-valid');
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
window.editDataPeluang = function(id) {
    if (window.dataPeluangManager) {
        window.dataPeluangManager.editDataPeluang(id);
    }
};

window.viewDataPeluang = function(id) {
    if (window.dataPeluangManager) {
        window.dataPeluangManager.viewDataPeluang(id);
    }
};

window.deleteDataPeluang = function(id) {
    if (window.dataPeluangManager) {
        window.dataPeluangManager.deleteDataPeluang(id);
    }
};

window.resetDataPeluangForm = function() {
    if (window.dataPeluangManager) {
        const originalData = window.originalDataPeluangFormData || {};
        window.dataPeluangManager.resetForm(originalData);
    }
};
