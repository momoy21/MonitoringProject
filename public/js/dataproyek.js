class DataProyekManager {
        constructor() {
        this.isSubmitting = false;
        this.searchTimeout = null;
        this.deleteTargetId = null;

        // Pagination variables (for index page)
        this.currentPage = 1;
        this.totalPages = 1;
        this.perPage = 15;
        this.currentSearch = '';
        this.pageType = 'index'; // Default page type
        this.idProject = null; // For show page

        // Form validation variables (for create/edit)
        this.currentDataProyekId = '';

        // Initialization flags
        this.dateFieldsInitialized = false;
        this.eventHandlersInitialized = false;
    }

    // ========================================
    // INITIALIZATION METHODS
    // ========================================

    init(config = {}) {
        this.setConfig(config);
        this.initializeEventHandlers();

        // Initialize page-specific functions
        if (config.pageType === 'index' || config.pageType === 'show') {
            this.updatePaginationButtons();
        } else if (config.pageType === 'create' || config.pageType === 'edit') {
            this.initializeFormFields();
        }
    }

    setConfig(config) {
        this.currentProyekId = config.currentProyekId || '';
        this.currentPage = config.currentPage || 1;
        this.totalPages = config.totalPages || 1;
        this.perPage = config.perPage || 10;
        this.currentSearch = config.currentSearch || '';
        this.pageType = config.pageType || 'index';
        this.idProject = config.idProject || null; // Store idProject for show page
    }

    initializeFormFields() {
        // Check if this is create-history form
        const isCreateHistory = $('#proyekForm').data('add-to-history') === true || $('#proyekForm').data('add-to-history') === 'true';

        if ($('#proyekForm').length && !$('#proyekForm').data('is-edit')) {
            if (isCreateHistory) {
                // For create-history, use parent project ID (already set in hidden field)
                // Just update the display
                const parentProjectId = $('#id_project').val();
                if (parentProjectId) {
                    $('#id-project-display').text(parentProjectId);
                }
            } else {
                // For regular create, auto-generate new ID
                if (!$('#id_project').val()) {
                    this.generateIdProject();
                }
            }
        }
    }

    // ========================================
    // EVENT HANDLERS
    // ========================================

    initializeEventHandlers() {
        // Prevent multiple initialization
        if (this.eventHandlersInitialized) {
            return;
        }
        this.eventHandlersInitialized = true;

        // Form submit handler (for create/edit pages)
        $('#proyekForm').on('submit', (e) => {
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

        // File upload handler
        this.initializeFileUpload();

        // Cost center check
        this.initializeCostCenterCheck();

        // Data peluang auto-fill (only for regular create form)
        this.initializeDataPeluangAutoFill();
    }

    initializeFieldValidation() {
        // Cost Center validation (alphanumeric only)
        $('#cost_center').on('input', function() {
            // Remove non-alphanumeric characters
            this.value = this.value.replace(/[^A-Za-z0-9]/g, '');

            // Limit to 9 characters
            if (this.value.length > 9) {
                this.value = this.value.substring(0, 9);
            }

            window.dataProyekManager.validateField('cost_center', this.value);
        });

        // Nama proyek validation
        $('#namaproject').on('input blur', (e) => {
            this.validateField('namaproject', $(e.target).val());
        });

        // Konsumen validation
        $('#id_konsumen').on('change', (e) => {
            this.validateField('id_konsumen', $(e.target).val());
        });

        // Bidang Jasa validation
        $('#id_bidjasa').on('change', (e) => {
            this.validateField('id_bidjasa', $(e.target).val());
        });

        // Kondisi Proyek validation
        $('#id_kondisi_proyek').on('change', (e) => {
            this.validateField('id_kondisi_proyek', $(e.target).val());
        });

        // Status validation
        $('#status').on('change', (e) => {
            this.validateField('status', $(e.target).val());
        });

        // Keterangan validation
        // Keterangan field is now auto-filled via hidden input

        // Date validation
        $('#start_kontrak, #finish_kontrak').on('change', (e) => {
            this.validateDates();
        });

        // Clear errors on input
        $('input, select, textarea').on('input change', (e) => {
            const fieldName = $(e.target).attr('name') || $(e.target).attr('id');
            this.clearFieldError(fieldName);
            $(e.target).removeClass('is-invalid is-valid');
        });
    }

    initializeCurrencyFields() {
        // Check if using separated display field (edit-history form)
        const hasDisplayField = $('#nilai_proyek_display').length > 0;

        if (hasDisplayField) {
            // For edit-history form: handle display field and sync with hidden field
            $('#nilai_proyek_display').on('input', function() {
                let value = this.value;

                // Remove all non-digit characters
                const cleanValue = value.replace(/[^\d]/g, '');

                if (cleanValue) {
                    // Format with thousand separator for display
                    const formatted = new Intl.NumberFormat('id-ID').format(cleanValue);
                    this.value = formatted;

                    // Update hidden field with clean value
                    $('#nilai_proyek').val(cleanValue);
                } else {
                    this.value = '';
                    $('#nilai_proyek').val('');
                }
            });

            // Initialize on page load
            if ($('#nilai_proyek_display').val()) {
                const displayValue = $('#nilai_proyek_display').val();
                const cleanValue = displayValue.replace(/[^\d]/g, '');
                $('#nilai_proyek').val(cleanValue);
            }
        } else {
            // For regular forms: handle nilai_proyek directly
            $('#nilai_proyek').on('input', function() {
                let value = this.value;

                // Remove all non-digit characters
                value = value.replace(/[^\d]/g, '');

                if (value) {
                    // Format dengan thousand separator
                    const formatted = new Intl.NumberFormat('id-ID').format(value);
                    this.value = formatted;

                    // Store clean value for form submission
                    $(this).attr('data-clean-value', value);
                } else {
                    this.value = '';
                    $(this).attr('data-clean-value', '');
                }
            });

            // On blur, ensure proper formatting
            $('#nilai_proyek').on('blur', function() {
                const cleanValue = $(this).attr('data-clean-value') || '';
                if (cleanValue) {
                    const formatted = new Intl.NumberFormat('id-ID').format(cleanValue);
                    this.value = formatted;
                }
            });
        }
    }

    initializeDateFields() {
        // Prevent multiple initialization
        if (this.dateFieldsInitialized) {
            return;
        }
        this.dateFieldsInitialized = true;

        // Setup date fields with manual input and date picker icon
        $('#tgl_pengakuan, #tgl_kontrak, #tgl_expire, #start_kontrak, #finish_kontrak').each(function() {
            const $input = $(this);
            const $container = $input.parent();
            const fieldId = $input.attr('id');

            // Check if already initialized (has input-group wrapper)
            if ($container.hasClass('input-group') || $container.hasClass('date-input-group')) {
                return; // Skip if already initialized
            }

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
                let value = this.value.replace(/\D/g, ''); // Remove non angka

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
                    const isValid = window.dataProyekManager.validateDateField(value);
                    if (isValid) {
                        // Convert to yyyy-mm-dd for date input
                        const parts = value.split('/');
                        const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        $dateInput.val(isoDate);
                    }
                }

                // Trigger validation for required fields
                if (fieldId === 'start_kontrak' || fieldId === 'finish_kontrak') {
                    window.dataProyekManager.validateField(fieldId, value);
                }
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

                // Try multiple methods to open date picker
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

                    // Trigger validation for required fields
                    if (fieldId === 'start_kontrak' || fieldId === 'finish_kontrak') {
                        window.dataProyekManager.validateField(fieldId, displayValue);
                    }
                }
            });

            // Handle blur validation
            $textInput.on('blur', function() {
                if (fieldId === 'start_kontrak' || fieldId === 'finish_kontrak') {
                    window.dataProyekManager.validateField(fieldId, this.value);
                }
            });
        });

        // Date validation on change for date comparison
        $('#start_kontrak, #finish_kontrak').on('change', (e) => {
            this.validateDates();
        });
    }

    initializeFileUpload() {
        $('#dokumen_kontrak').on('change', function() {
            const file = this.files[0];
            const allowedTypes = [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
                'application/msword', // doc
                'application/pdf', // pdf
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
                'application/vnd.ms-excel', // xls
                'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
                'application/vnd.ms-powerpoint', // ppt
                'image/jpeg', // jpg, jpeg
                'image/png' // png
            ];
            const maxSize = 10 * 1024 * 1024; // 10MB

            if (file) {
                // Check file type
                if (!allowedTypes.includes(file.type)) {
                    window.dataProyekManager.showAlert('File yang diperbolehkan: docx, doc, pdf, xlsx, xls, pptx, ppt, jpg, jpeg, png', 'error');
                    $(this).val('');
                    return;
                }

                // Check file size
                if (file.size > maxSize) {
                    window.dataProyekManager.showAlert('Ukuran file maksimal 10MB', 'error');
                    $(this).val('');
                    return;
                }

                // Show file name
                $('#fileName').text(file.name);
                $('#fileInfo').show();
            } else {
                $('#fileInfo').hide();
            }
        });

        // Remove file
        $('#removeFile').on('click', function() {
            $('#dokumen_kontrak').val('');
            $('#fileInfo').hide();
        });
    }

    initializeCostCenterCheck() {
        // Cost center check removed - now allowing duplicate cost centers with different keterangan
        // Validation is handled on server side during form submission
    }

    initializeDataPeluangAutoFill() {
        // Only for regular create form, not create-history
        const isCreateHistory = $('#proyekForm').data('add-to-history') === true ||
                               $('#proyekForm').data('add-to-history') === 'true';

        if (!isCreateHistory) {
            $('#id_datapeluang').on('change', function() {
                const peluangId = $(this).val();
                if (peluangId) {
                    window.dataProyekManager.loadDataPeluang(peluangId);
                } else {
                    // Clear auto-filled fields when no peluang selected
                    window.dataProyekManager.clearAutoFilledFields();
                }
            });
        }
    }

    loadDataPeluang(peluangId) {
        $.ajax({
            url: `/dataproyek/get-peluang/${peluangId}`,
            type: 'GET',
            success: (data) => {
                // Auto-fill konsumen only
                if (data.id_konsumen) {
                    $('#id_konsumen').val(data.id_konsumen).trigger('change');
                }

                // Show success feedback
                this.showAlert('Konsumen berhasil diisi otomatis dari Data Peluang', 'success');
            },
            error: (xhr) => {
                console.error('Error loading data peluang:', xhr);
                this.showAlert('Gagal memuat data peluang', 'error');
            }
        });
    }

    clearAutoFilledFields() {
        $('#id_konsumen').val('');
    }

    initializeSearchHandler() {
        // Index page search handler - using AJAX like history page
        $('#searchInput').on('input', (e) => {
            const searchValue = $(e.target).val().trim();
            this.currentSearch = searchValue;
            this.currentPage = 1;

            clearTimeout(this.searchTimeout);

            this.searchTimeout = setTimeout(() => {
                if (this.pageType === 'index') {
                    this.loadProyekData();
                }
            }, 300); // 300ms debounce
        });

        // Show page (history) search handler
        $('#historySearchInput').on('input', (e) => {
            const searchValue = $(e.target).val().trim();
            this.currentSearch = searchValue;
            this.currentPage = 1;

            clearTimeout(this.searchTimeout);

            this.searchTimeout = setTimeout(() => {
                if (this.pageType === 'show') {
                    this.loadHistoryProyekData(this.idProject);
                }
            }, 300); // 300ms debounce
        });

        // Per page selector handler
        $('#perPageSelect').on('change', (e) => {
            const perPage = $(e.target).val();

            if (this.pageType === 'index') {
                // For index page, use AJAX like show page
                this.perPage = perPage;
                this.currentPage = 1;
                this.loadProyekData();
            } else if (this.pageType === 'show') {
                // For show page, use AJAX
                this.perPage = perPage;
                this.currentPage = 1;
                this.loadHistoryProyekData(this.idProject);
            } else {
                // Keep the old logic for other pages
                this.perPage = perPage;
                this.currentPage = 1;
                this.loadProyekData();
            }
        });
    }

    initializePaginationHandlers() {
        // First page handler
        $('#firstPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                if (this.pageType === 'index') {
                    this.currentPage = 1;
                    this.loadProyekData();
                } else if (this.pageType === 'show') {
                    this.currentPage = 1;
                    this.loadHistoryProyekData(this.idProject);
                } else {
                    this.currentPage = 1;
                    this.loadProyekData();
                }
            }
        });

        // Previous page handler
        $('#prevPageBtn').on('click', () => {
            if (this.currentPage > 1) {
                if (this.pageType === 'index') {
                    this.currentPage--;
                    this.loadProyekData();
                } else if (this.pageType === 'show') {
                    this.currentPage--;
                    this.loadHistoryProyekData(this.idProject);
                } else {
                    this.currentPage--;
                    this.loadProyekData();
                }
            }
        });

        // Next page handler
        $('#nextPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                if (this.pageType === 'index') {
                    this.currentPage++;
                    this.loadProyekData();
                } else if (this.pageType === 'show') {
                    this.currentPage++;
                    this.loadHistoryProyekData(this.idProject);
                } else {
                    this.currentPage++;
                    this.loadProyekData();
                }
            }
        });

        // Last page handler
        $('#lastPageBtn').on('click', () => {
            if (this.currentPage < this.totalPages) {
                if (this.pageType === 'index') {
                    this.currentPage = this.totalPages;
                    this.loadProyekData();
                } else if (this.pageType === 'show') {
                    this.currentPage = this.totalPages;
                    this.loadHistoryProyekData(this.idProject);
                } else {
                    this.currentPage = this.totalPages;
                    this.loadProyekData();
                }
            }
        });
    }

    initializeModalHandlers() {
        // Detail modal handler - menggunakan event delegation
        $(document).off('click', '.btn-detail').on('click', '.btn-detail', function(e) {
            e.preventDefault();
            const idProject = $(this).data('id');
            const fromHistory = $(this).data('from-history');
            const norut = $(this).data('norut');
            console.log('Detail button clicked:', { idProject, fromHistory, norut });
            window.dataProyekManager.showProjectDetail(idProject, fromHistory, norut);
        });

        // Delete confirmation handler - menggunakan modal
        $(document).off('click', '.btn-delete-history').on('click', '.btn-delete-history', function(e) {
            e.preventDefault();
            const idProject = $(this).data('id');
            const norut = $(this).data('norut');
            const namaProject = $(this).data('nama') || 'proyek ini';

            // Set data ke modal
            $('#deleteProjectName').text(namaProject);
            $('#deleteProjectId').val(idProject);
            $('#deleteProjectNorut').val(norut);

            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });

        // Confirm delete button handler
        $('#confirmDeleteBtn').off('click').on('click', function() {
            const idProject = $('#deleteProjectId').val();
            const norut = $('#deleteProjectNorut').val();

            if (idProject && norut) {
                deleteHistoryProyek(idProject, norut);
            }
        });

        // File preview handler untuk modal
        $(document).on('click', '.btn-preview-file', function(e) {
            e.preventDefault();
            const fileUrl = $(this).data('file-url');
            const fileName = $(this).data('file-name');
            const downloadUrl = $(this).data('download-url');

            if (window.filePreview) {
                window.filePreview.showPreview(fileUrl, fileName, downloadUrl);
            } else {
                console.error('FilePreview not loaded');
                alert('File preview tidak tersedia');
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
            case 'cost_center':
                if (!value.trim()) {
                    isValid = false;
                    errorMessage = 'Cost Center harus diisi.';
                } else if (!/^[A-Za-z0-9]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Cost Center hanya boleh berisi huruf dan angka.';
                }
                break;
            case 'namaproject':
                if (!value.trim()) {
                    isValid = false;
                    errorMessage = 'Nama proyek harus diisi.';
                }
                break;
            case 'id_konsumen':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Konsumen harus dipilih.';
                }
                break;
            case 'id_bidjasa':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Bidang jasa harus dipilih.';
                }
                break;
            case 'id_kondisi_proyek':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Kondisi proyek harus dipilih.';
                }
                break;
            case 'start_kontrak':
            case 'finish_kontrak':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Tanggal harus diisi.';
                } else if (!this.validateDateField(value) && !this.validateISODateField(value)) {
                    isValid = false;
                    errorMessage = 'Format tanggal tidak valid.';
                }
                break;
            case 'status':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Status harus dipilih.';
                }
                break;
            case 'keterangan':
                // Keterangan field is now auto-filled, no validation needed
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

    validateISODateField(dateString) {
        // Check format YYYY-MM-DD from type="date" inputs
        const isoPattern = /^\d{4}-\d{2}-\d{2}$/;
        if (!isoPattern.test(dateString)) {
            return false;
        }

        // Parse and validate the date
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date.getTime());
    }

    validateDates() {
        const startKontrak = $('#start_kontrak').val();
        const finishKontrak = $('#finish_kontrak').val();

        this.clearFieldError('finish_kontrak');

        if (startKontrak && finishKontrak) {
            // Parse dates from dd/mm/yyyy format
            const start = this.parseDate(startKontrak);
            const finish = this.parseDate(finishKontrak);

            if (start && finish && finish <= start) {
                this.showFieldError('finish_kontrak', 'Tanggal Finish Kontrak harus setelah Tanggal Start Kontrak.');
                $('#finish_kontrak').addClass('is-invalid');
                return false;
            } else if (finish) {
                $('#finish_kontrak').addClass('is-valid');
            }
        }

        return true;
    }

    parseDate(dateString) {
        if (!dateString) {
            return null;
        }

        // Handle ISO date format (YYYY-MM-DD) from type="date" inputs
        if (dateString.includes('-') && dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return new Date(dateString);
        }

        // Handle dd/mm/yyyy format from text inputs
        if (dateString.includes('/') && this.validateDateField(dateString)) {
            const [day, month, year] = dateString.split('/').map(num => parseInt(num, 10));
            return new Date(year, month - 1, day);
        }

        return null;
    }

    // ========================================
    // FORM SUBMISSION METHODS
    // ========================================

    submitForm() {
        try {
            this.clearAllErrors();

            // Check if this is create-history or edit-history form (fields are hidden/readonly)
            const isCreateHistory = $('#proyekForm').data('add-to-history') === true || $('#proyekForm').data('add-to-history') === 'true';
            const isEditHistory = $('#proyekForm').data('is-edit') === true && $('input[name="cost_center"][type="hidden"]').length > 0;
            const isHistoryForm = isCreateHistory || isEditHistory;

            console.log('Form type detection:', {
                isCreateHistory: isCreateHistory,
                isEditHistory: isEditHistory,
                isHistoryForm: isHistoryForm
            });

            let isFormValid = true;
            isFormValid &= this.validateField('cost_center', $('#cost_center').val() || $('#cost_center_display').val());
            isFormValid &= this.validateField('namaproject', $('#namaproject').val());

            // For history forms (create-history and edit-history), these fields are hidden and auto-filled, so skip validation
            if (!isHistoryForm) {
                console.log('Validating non-history form fields...');
                isFormValid &= this.validateField('id_konsumen', $('#id_konsumen').val());
                isFormValid &= this.validateField('id_bidjasa', $('#id_bidjasa').val());
                isFormValid &= this.validateField('id_kondisi_proyek', $('#id_kondisi_proyek').val());
                // Keterangan tidak perlu validasi, sudah auto-filled
            } else {
                console.log('Skipping validation for hidden fields (history form)');
            }

            isFormValid &= this.validateField('start_kontrak', $('#start_kontrak').val());
            isFormValid &= this.validateField('finish_kontrak', $('#finish_kontrak').val());
            isFormValid &= this.validateField('status', $('#status').val());
            isFormValid &= this.validateDates();            if (!isFormValid) {
                this.showAlert('Mohon perbaiki kesalahan pada form.', 'error');
                return;
            }

            this.setSubmitLoading(true);

            // Create FormData and clean currency values
              const formData = new FormData($('#proyekForm')[0]);

              // Remove empty file input to prevent validation error
              const dokumenInput = $('#dokumen_kontrak')[0];
              if (dokumenInput && (!dokumenInput.files || !dokumenInput.files[0] || dokumenInput.files[0].size === 0)) {
                  formData.delete('dokumen_kontrak');
                  console.log('Removed empty dokumen_kontrak from FormData');
              }

              // Clean currency value
              // Check if using separated display field (edit-history form)
              const hasDisplayField = $('#nilai_proyek_display').length > 0;

              if (hasDisplayField) {
                  // For edit-history: hidden field already contains clean value
                  const nilaiValue = $('#nilai_proyek').val();
                  if (nilaiValue) {
                      formData.set('nilai_proyek', nilaiValue);
                  }
              } else {
                  // For regular forms: get from data-clean-value or clean manually
                  const nilaiCleanValue = $('#nilai_proyek').attr('data-clean-value');
                  if (nilaiCleanValue && nilaiCleanValue !== '') {
                      formData.set('nilai_proyek', nilaiCleanValue);
                  } else {
                      // Fallback: clean manual
                      const nilaiValue = $('#nilai_proyek').val();
                      if (nilaiValue) {
                          const cleanedValue = nilaiValue.replace(/[^\d]/g, '');
                          if (cleanedValue) {
                              formData.set('nilai_proyek', cleanedValue);
                          }
                    }
                }
            }



            // Convert date values from dd/mm/yyyy to Y-m-d
            this.convertDateFieldsForSubmission(formData);

            console.log('Submitting AJAX request to:', $('#proyekForm').attr('action'));
            console.log('Method:', $('#proyekForm').find('input[name="_method"]').val() || 'POST');

            $.ajax({
                url: $('#proyekForm').attr('action'),
                type: $('#proyekForm').find('input[name="_method"]').length ? 'POST' : 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                beforeSend: function() {
                    console.log('=== AJAX REQUEST STARTING ===');
                },
                success: (response) => {
                    console.log('=== AJAX SUCCESS ===');
                    console.log('Response:', response);
                    this.showAlert('Data proyek berhasil disimpan.', 'success');
                    setTimeout(() => {
                        // Check if server provided redirect URL
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else {
                            // Detect form types
                            const isCreateHistory = $('#proyekForm').data('add-to-history') === true ||
                                                  $('#proyekForm').data('add-to-history') === 'true';
                            const isEditHistory = $('#proyekForm').data('is-edit') === true &&
                                                 $('input[name="cost_center"][type="hidden"]').length > 0;
                            const isEdit = $('#proyekForm').data('is-edit') === true && !isEditHistory;

                            if (isCreateHistory || isEditHistory) {
                                // For create-history and edit-history, redirect back to the history page
                                const idProject = $('input[name="id_project"]').val();
                                if (idProject) {
                                    window.location.href = `/dataproyek/${idProject}`;
                                } else {
                                    window.location.href = '/dataproyek';
                                }
                            } else if (isEdit) {
                                // For regular edit, redirect to index
                                const dataProyekIndexRoute = window.Laravel?.routes?.dataProyekIndex || '/dataproyek';
                                window.location.href = dataProyekIndexRoute;
                            } else {
                                // For regular create, redirect to index
                                const dataProyekIndexRoute = window.Laravel?.routes?.dataProyekIndex || '/dataproyek';
                                window.location.href = dataProyekIndexRoute;
                            }
                        }
                    }, 1500);
                },
                error: (xhr) => {
                    this.setSubmitLoading(false);

                    console.error('=== AJAX ERROR ===');
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseJSON);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        console.error('Validation Errors:', errors);

                        Object.keys(errors).forEach(field => {
                            console.error(`Field "${field}" error:`, errors[field]);
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

    convertDateFieldsForSubmission(formData) {
        const dateFields = ['tgl_pengakuan', 'tgl_kontrak', 'tgl_expire', 'start_kontrak', 'finish_kontrak'];

        dateFields.forEach(field => {
            const dateValue = $(`#${field}`).val();
            if (dateValue && dateValue.includes('/')) {
                const parts = dateValue.split('/');
                const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                formData.set(field, isoDate);
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
            if (spinner.length) {
                spinner.removeClass('d-none');
                icon.addClass('d-none');
            }
            if (text.length) {
                text.text('Menyimpan...');
            } else {
                btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            }
        } else {
            btn.prop('disabled', false);
            if (spinner.length) {
                spinner.addClass('d-none');
                icon.removeClass('d-none');
            }
            if (text.length) {
                text.text('Simpan');
            } else {
                btn.html('<i class="bx bx-check me-1"></i> Simpan');
            }
        }
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    generateIdProject() {
        // Show loading state
        $('#id-project-display').text('Generating...');

        $.ajax({
            url: '/dataproyek/generate-id',
            type: 'POST',
            data: {
                '_token': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#id_project').val(response.id_project);
                    $('#id-project-display').text(response.id_project);
                } else {
                    $('#id-project-display').text('Error generating ID');
                    console.error('Failed to generate ID Project:', response.message);
                }
            },
            error: function(xhr, status, error) {
                $('#id-project-display').text('Error generating ID');
                console.error('AJAX Error generating ID Project:', error);

                const now = new Date();
                const year = now.getFullYear().toString();
                const day = now.getDate().toString().padStart(2, '0');
                const sequenceNumber = '0001';
                const fallbackId = year + day + sequenceNumber;

                $('#id_project').val(fallbackId);
                $('#id-project-display').text(fallbackId + ' (fallback)');
            }
        });
    }

    // checkCostCenterExists() method removed - validation now handled on server side only

    formatCurrency(value) {
        if (!value) return '-';
        // Handle both string and numeric values
        let number;
        if (typeof value === 'string') {
            // Remove all non-digit characters for display
            number = parseFloat(value.replace(/[^\d]/g, ''));
        } else {
            number = parseFloat(value);
        }

        if (isNaN(number) || number === 0) return '-';
        // Use thousands separator = '.', decimal separator = ','
        return 'Rp ' + number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    formatCurrencyInput(value) {
        if (!value) return '';
        const number = typeof value === 'string' ? parseFloat(value.replace(/[^\d]/g, '')) : value;
        if (isNaN(number) || number === 0) return '';
        return new Intl.NumberFormat('id-ID').format(number);
    }

    formatDate(dateString) {
        if (!dateString || dateString === '-' || dateString === null) return '-';

        try {
            // Handle different date formats
            let date;

            // If it's already in dd/mm/yyyy format, return as is
            if (typeof dateString === 'string' && /^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
                return dateString;
            }

            // Try to parse as Date object
            date = new Date(dateString);

            // Check if date is valid
            if (isNaN(date.getTime())) {
                console.warn('Invalid date:', dateString);
                return '-';
            }

            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } catch (error) {
            console.error('Error formatting date:', dateString, error);
            return '-';
        }
    }

    showProjectDetail(idProject, fromHistory = false, norut = null) {
        $('#detailContent').hide();
        $('#detailModal').modal('show');

        let url = `/dataproyek/get-data/${idProject}`;
        let params = {};

        if (fromHistory && norut) {
            params.from_history = 'true';
            params.norut = norut;
        }

        // Add query parameters if any
        if (Object.keys(params).length > 0) {
            const queryString = new URLSearchParams(params).toString();
            url += `?${queryString}`;
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: (data) => {
                this.renderProjectDetail(data, fromHistory);
            },
            error: (xhr) => {
                console.error('Error loading project detail:', xhr);
                $('#detailContent').html('<div class="alert alert-danger">Gagal memuat data proyek</div>').show();
            }
        });
    }



     renderProjectDetail(project, fromHistory = false) {
        // Hide loading spinner
        $('#detailLoadingSpinner').hide();

        // Get status badge and label
        const statusBadge = project.status_badge || this.getStatusBadge(project.status);
        const statusLabel = project.status_text || this.getStatusLabel(project.status);
        const nilaiFormatted = project.nilai_proyek_formatted || (project.nilai_proyek ? this.formatCurrency(project.nilai_proyek) : '-');
        const keteranganText = project.keterangan || '-';

        if (fromHistory) {
            const content = `
                <div class="modal-info-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="modal-info-section">
                                <h6>Informasi Proyek</h6>
                                <table class="table table-sm">
                                    <tr><td>Nomor Urut:</td><td><strong>${project.norut || '-'}</strong></td></tr>
                                    <tr><td>Dokumen IO:</td><td>${project.dokumen_io || '-'}</td></tr>
                                    <tr><td>Nama Proyek:</td><td>${project.namaproject || '-'}</td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="modal-info-section">
                                <h6>Informasi Kontrak</h6>
                                <table class="table table-sm">
                                    <tr><td>No. Kontrak:</td><td>${project.no_kontrak || '-'}</td></tr>
                                    <tr><td>Nilai Proyek:</td><td><strong class="text-success">${nilaiFormatted}</strong></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-info-section">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Timeline Kontrak</h6>
                            <table class="table table-sm">
                                <tr><td>Tgl Pengakuan:</td><td>${this.formatDate(project.tgl_pengakuan)}</td></tr>
                                <tr><td>Tgl Kontrak:</td><td>${this.formatDate(project.tgl_kontrak)}</td></tr>
                                <tr><td>Tgl Expire:</td><td>${this.formatDate(project.tgl_expire)}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Durasi Kontrak</h6>
                            <table class="table table-sm">
                                <tr><td>Start Kontrak:</td><td>${this.formatDate(project.start_kontrak)}</td></tr>
                                <tr><td>Finish Kontrak:</td><td>${this.formatDate(project.finish_kontrak)}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-info-section">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Penanggung Jawab</h6>
                            <table class="table table-sm">
                                <tr><td>Penanggung Jawab:</td><td>${project.manager_nama || project.penanggung_jawab || '-'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Status & Keterangan</h6>
                            <table class="table table-sm">
                                <tr><td>Status:</td><td><span class="${statusBadge}">${statusLabel}</span></td></tr>
                                <tr><td>Keterangan:</td><td>${keteranganText}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                ${project.dokumen_path ? `
                <div class="modal-info-section">
                    <div class="row">
                        <div class="col-12">
                            <h6>Dokumen Kontrak</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-info btn-sm"
                                        onclick="if(window.filePreview) { window.filePreview.showPreview('${window.location.origin}/storage/${project.dokumen_path}', '${project.dokumen_path.split('/').pop()}', '/dataproyek/download/${project.id_project}?norut=${project.norut}&v=${Date.now()}'); } else { alert('File preview tidak tersedia. Silakan refresh halaman.'); console.error('FilePreview not loaded'); }">
                                    <i class="bx bx-show me-1"></i> Preview
                                </button>
                                <a href="/dataproyek/download/${project.id_project}?norut=${project.norut}&v=${Date.now()}" class="btn btn-outline-primary btn-sm" target="_blank">
                                    <i class="bx bx-download me-1"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
            `;

            $('#detailContent').html(content);
            $('#detailContent').show();
            return;
        }

        // [kode untuk tampilan proyek utama tetap sama...]
        const content = `
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="modal-info-section">
                            <h6>Informasi Proyek</h6>
                            <table class="table table-sm">
                                <tr><td>ID Project:</td><td><strong>${project.id_project || '-'}</strong></td></tr>
                                <tr><td>Dokumen IO:</td><td>${project.dokumen_io || '-'}</td></tr>
                                <tr><td>Cost Center:</td><td><strong>${project.cost_center || '-'}</strong></td></tr>
                                <tr><td>Nama Proyek:</td><td>${project.namaproject || '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="modal-info-section">
                            <h6>Konsumen & Bidang</h6>
                            <table class="table table-sm">
                                <tr><td>Konsumen:</td><td>${project.konsumen_nama || '-'}</td></tr>
                                <tr><td>Data Peluang:</td><td>${project.peluang_nama || '-'}</td></tr>
                                <tr><td>Bidang Jasa:</td><td>${project.bidang_jasa || '-'}</td></tr>
                                <tr><td>Kondisi Proyek:</td><td>${project.kondisi_proyek || '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Kontak</h6>
                        <table class="table table-sm">
                            <tr><td>Penanggung Jawab:</td><td>${project.penanggung_jawab || '-'}</td></tr>
                            <tr><td>NIK Penanggung Jawab:</td><td>${project.nik || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Lokasi & Jarak</h6>
                        <table class="table table-sm">
                            <tr><td>Lokasi Proyek:</td><td>${project.lokasi_proyek || '-'}</td></tr>
                            <tr><td>Jarak Lokasi:</td><td>${project.jarak_lokasi_text || project.jarak_lokasi || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Kontrak</h6>
                        <table class="table table-sm">
                            <tr><td>No. Kontrak:</td><td>${project.no_kontrak || '-'}</td></tr>
                            <tr><td>Nilai Proyek:</td><td><strong class="text-success">${nilaiFormatted}</strong></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Timeline Kontrak</h6>
                        <table class="table table-sm">
                            <tr><td>Tgl Pengakuan:</td><td>${this.formatDate(project.tgl_pengakuan)}</td></tr>
                            <tr><td>Tgl Kontrak:</td><td>${this.formatDate(project.tgl_kontrak)}</td></tr>
                            <tr><td>Tgl Expire:</td><td>${this.formatDate(project.tgl_expire)}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Durasi Kontrak</h6>
                        <table class="table table-sm">
                            <tr><td>Start Kontrak:</td><td>${this.formatDate(project.start_kontrak)}</td></tr>
                            <tr><td>Finish Kontrak:</td><td>${this.formatDate(project.finish_kontrak)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Status & Keterangan</h6>
                        <table class="table table-sm">
                            <tr><td>Status:</td><td><span class="${statusBadge}">${statusLabel}</span></td></tr>
                            <tr><td>Keterangan:</td><td>${keteranganText}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            ${project.dokumen_path ? `
            <div class="modal-info-section">
                <div class="row">
                    <div class="col-12">
                        <h6>Dokumen Kontrak</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-info btn-sm"
                                    onclick="if(window.filePreview) { window.filePreview.showPreview('${window.location.origin}/storage/${project.dokumen_path}', '${project.dokumen_path.split('/').pop()}', '/dataproyek/download/${project.id_project}?v=${Date.now()}'); } else { alert('File preview tidak tersedia. Silakan refresh halaman.'); console.error('FilePreview not loaded'); }">
                                <i class="bx bx-show me-1"></i> Preview
                            </button>
                            <a href="/dataproyek/download/${project.id_project}?v=${Date.now()}" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="bx bx-download me-1"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
        `;

        $('#detailContent').html(content);
        $('#detailContent').show();
    }

    // ========================================
    // INDEX PAGE METHODS
    // ========================================

    loadProyekData() {
        this.showLoadingSpinner(true);

        const params = {
            search: this.currentSearch,
            per_page: this.perPage,
            page: this.currentPage
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
                    this.updateTableContent(response.data);
                    this.updatePaginationInfo(response.pagination);
                    this.updatePaginationButtons();

                    // Kembalikan nilai search ke input field dan focus
                    const searchInput = $('#searchInput');
                    if (searchInput.length) {
                        searchInput.val(this.currentSearch);

                        // Kembalikan posisi cursor ke akhir teks
                        if (this.currentSearch) {
                            searchInput.focus();
                            const input = searchInput[0];
                            const length = this.currentSearch.length;
                            input.setSelectionRange(length, length);
                        }
                    }
                }
            },
            error: (xhr) => {
                console.error('Error loading data:', xhr);
                this.showAlert('Terjadi kesalahan saat memuat data', 'error');

                // Handle error case - tetap kembalikan nilai search
                const searchInput = $('#searchInput');
                if (searchInput.length) {
                    searchInput.val(this.currentSearch);
                    if (this.currentSearch) {
                        searchInput.focus();
                        const input = searchInput[0];
                        const length = this.currentSearch.length;
                        input.setSelectionRange(length, length);
                    }
                }
            },
            complete: () => {
                this.showLoadingSpinner(false);
            }
        });
    }    updateTableContent(data) {
        const tbody = $('#dataProyekTable tbody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data proyek</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        // Setiap item adalah proyek individual (tidak di-group)
        data.forEach(item => {
            const nilaiFormatted = item.nilai_proyek_formatted || '-';
            const konsumenNama = item.konsumen ? item.konsumen.konsumen : '-';

            // Use status badge and text from server response
            const statusBadge = item.status_badge || 'badge bg-secondary';
            const statusText = item.status_text || item.status;

            tbody.append(`
                <tr>
                    <td ondblclick="editDataProyek('${item.id_project}')" style="cursor: pointer;" title="Double-click untuk edit">
                        <div class="costcenter-id">
                            ${item.cost_center}
                        </div>
                        <a href="/dataproyek/${item.id_project}" class="small" style="color: grey; margin-left: 15px;" onclick="event.stopPropagation();">
                            Detail
                        </a>
                    </td>
                    <td ondblclick="editDataProyek('${item.id_project}')" style="cursor: pointer;" title="Double-click untuk edit">
                        <div class="truncate-text" title="${this.escapeHtml(item.namaproject)}">
                            ${this.escapeHtml(item.namaproject)}
                        </div>
                    </td>
                    <td ondblclick="editDataProyek('${item.id_project}')" style="cursor: pointer;" title="Double-click untuk edit">
                        <div class="truncate-text" title="${this.escapeHtml(konsumenNama)}">
                            ${this.escapeHtml(konsumenNama)}
                        </div>
                    </td>
                    <td ondblclick="editDataProyek('${item.id_project}')" style="cursor: pointer;" title="Double-click untuk edit">${item.no_kontrak || '-'}</td>
                    <td class="text-start" ondblclick="editDataProyek('${item.id_project}')" style="cursor: pointer;" title="Double-click untuk edit">
                        ${nilaiFormatted !== '-' ? `<small class="currency-display">${nilaiFormatted}</small>` : '<span class="text-muted">-</span>'}
                    </td>
                    <td ondblclick="editDataProyek('${item.id_project}')" style="cursor: pointer;" title="Double-click untuk edit">
                        <div class="small">
                            ${item.tgl_kontrak ? `<div><strong>Kontrak:</strong> ${this.formatDate(item.tgl_kontrak)}</div>` : ''}
                            ${item.start_kontrak && item.finish_kontrak ? `<div><strong>Periode:</strong> ${this.formatDate(item.start_kontrak)} - ${this.formatDate(item.finish_kontrak)}</div>` : ''}
                        </div>
                    </td>
                    <td>
                        <span class="${statusBadge}">${statusText}</span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button type="button" class="dropdown-item btn-detail" data-id="${item.id_project}">
                                    <i class="bx bx-info-circle me-1"></i> Lihat Detail</button></li>
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

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === this.currentPage;
            container.append(`
                <button type="button" class="btn btn-sm page-number-btn ${isActive ? 'btn-primary' : 'btn-outline-secondary'}"
                        data-page="${i}" ${isActive ? 'disabled' : ''}>${i}</button>
            `);
        }

        // Add click handlers for page numbers
        $('.page-number-btn').off('click').on('click', (e) => {
            const page = parseInt($(e.target).data('page'));
            if (page !== this.currentPage && !isNaN(page)) {
                if (this.pageType === 'index') {
                    this.currentPage = page;
                    this.loadProyekData();
                } else if (this.pageType === 'show') {
                    this.currentPage = page;
                    this.loadHistoryProyekData(this.idProject);
                } else {
                    this.currentPage = page;
                    this.loadProyekData();
                }
            }
        });
    }

    getStatusLabel(status) {
        return {
            'O': 'Open',
            'I': 'In Progress',
            'C': 'Close',
            'P': 'Pending',
            'F': 'Finish Pekerjaan'
        }[status] || 'Unknown';
    }

    getStatusBadge(status) {
        return {
            'O': 'badge bg-info',
            'I': 'badge bg-primary',
            'C': 'badge bg-success',
            'P': 'badge bg-secondary',
            'F': 'badge bg-warning'
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
    // SHOW PAGE METHODS (History Proyek)
    // ========================================

    loadHistoryProyekData(idProject) {
        this.showLoadingSpinner(true);

        const params = {
            search: this.currentSearch,
            per_page: this.perPage,
            page: this.currentPage
        };

        $.ajax({
            url: `/dataproyek/${idProject}`,
            type: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                if (response.success) {
                    this.updateHistoryTableContent(response.data);
                    this.updatePaginationInfo(response.pagination);
                    this.updatePaginationButtons();

                    // Kembalikan nilai search ke input field dan focus
                    const searchInput = $('#historySearchInput');
                    if (searchInput.length) {
                        searchInput.val(this.currentSearch);

                        // Kembalikan posisi cursor ke akhir teks
                        if (this.currentSearch) {
                            searchInput.focus();
                            const input = searchInput[0];
                            const length = this.currentSearch.length;
                            input.setSelectionRange(length, length);
                        }
                    }
                }
            },
            error: (xhr) => {
                console.error('Error loading history data:', xhr);
                this.showAlert('Terjadi kesalahan saat memuat data', 'error');

                // Handle error case - tetap kembalikan nilai search
                const searchInput = $('#historySearchInput');
                if (searchInput.length) {
                    searchInput.val(this.currentSearch);
                    if (this.currentSearch) {
                        searchInput.focus();
                        const input = searchInput[0];
                        const length = this.currentSearch.length;
                        input.setSelectionRange(length, length);
                    }
                }
            },
            complete: () => {
                this.showLoadingSpinner(false);
            }
        });
    }

     updateHistoryTableContent(data) {
        const tbody = $('#historyProyekTable tbody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bx bx-search-alt-2 mb-2 empty-state-icon" style="font-size: 48px;"></i>
                            <p class="mb-0 empty-state-text">Tidak ada data history proyek</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(project => {
            const nilaiFormatted = project.nilai_proyek_formatted || '-';
            const statusBadge = project.status_badge || 'badge bg-secondary';
            const statusText = project.status_text || project.status;

            // FIX: Update ondblclick to use composite key (id_project + norut)
            tbody.append(`
                <tr>
                    <td class="text-center" ondblclick="editHistoryProyek('${project.id_project}', ${project.norut})" style="cursor: pointer;" title="Double-click untuk edit">
                        <span class="norut-edit">${project.norut}</span>
                    </td>
                    <td ondblclick="editHistoryProyek('${project.id_project}', ${project.norut})" style="cursor: pointer;" title="Double-click untuk edit">
                        <div class="truncate-text" style="max-width: 250px;" title="${this.escapeHtml(project.namaproject)}">
                            ${this.escapeHtml(project.namaproject)}
                        </div>
                    </td>
                    <td ondblclick="editHistoryProyek('${project.id_project}', ${project.norut})" style="cursor: pointer;" title="Double-click untuk edit">${project.no_kontrak || '-'}</td>
                    <td class="text-start" ondblclick="editHistoryProyek('${project.id_project}', ${project.norut})" style="cursor: pointer;" title="Double-click untuk edit">
                        ${nilaiFormatted !== '-' ? `<small class="currency-display">${nilaiFormatted}</small>` : '<span class="text-muted">-</span>'}
                    </td>
                    <td ondblclick="editHistoryProyek('${project.id_project}', ${project.norut})" style="cursor: pointer;" title="Double-click untuk edit">
                        <div class="small">
                            ${project.tgl_kontrak ? `<div><strong>Kontrak:</strong> ${project.tgl_kontrak}</div>` : ''}
                            ${project.start_kontrak && project.finish_kontrak ? `<div><strong>Periode:</strong> ${project.start_kontrak} - ${project.finish_kontrak}</div>` : ''}
                        </div>
                    </td>
                    <td>
                        <span class="${statusBadge}">${statusText}</span>
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button type="button" class="dropdown-item btn-detail"
                                    data-id="${project.id_project}"
                                    data-norut="${project.norut}"
                                    data-from-history="true">
                                    <i class="bx bx-info-circle me-1"></i> Lihat Detail</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item text-danger btn-delete-history"
                                    data-id="${project.id_project}"
                                    data-norut="${project.norut}"
                                    data-nama="${this.escapeHtml(project.namaproject)}">
                                    <i class="bx bx-trash me-1"></i> Hapus</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `);
        });
    }


    // ========================================
    // ERROR/SUCCESS MESSAGE METHODS
    // ========================================

    showFieldError(fieldName, message) {
        $(`#${fieldName}-error`).text(message);
        // Show error for date input groups
        const field = $(`#${fieldName}`);
        const dateGroup = field.closest('.date-input-group');

        if (dateGroup.length > 0) {
            // For date input groups
            dateGroup.addClass('has-error');
            field.addClass('is-invalid');

            if (dateGroup.find('.invalid-feedback').length === 0) {
                dateGroup.append(`<div class="invalid-feedback">${message}</div>`);
            } else {
                dateGroup.find('.invalid-feedback').text(message);
            }
        } else {
            // For regular inputs
            if (field.next('.invalid-feedback').length === 0) {
                field.after(`<div class="invalid-feedback">${message}</div>`);
            } else {
                field.next('.invalid-feedback').text(message);
            }
        }
    }

    clearFieldError(fieldName) {
        $(`#${fieldName}-error`).text('');
        const field = $(`#${fieldName}`);
        const dateGroup = field.closest('.date-input-group');

        if (dateGroup.length > 0) {
            dateGroup.removeClass('has-error');
            dateGroup.find('.invalid-feedback').text('');
        } else {
            field.next('.invalid-feedback').text('');
        }
    }

    clearAllErrors() {
        $('.error-message').text('');
        $('.invalid-feedback').text('');
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
// GLOBAL FUNCTIONS - Add these after the class definition
// ========================================

/**
 * Edit history proyek using composite key (idProject + norut)
 * Called from double-click on norut column in history table
 */
function editHistoryProyek(idProject, norut) {
    window.location.href = `/dataproyek/history/${idProject}/${norut}/edit`;
}

/**
 * Edit main data proyek
 * Called from double-click on cost_center in index table
 */
function editDataProyek(idProject) {
    window.location.href = `/dataproyek/${idProject}/edit`;
}

/**
 * Delete history proyek using composite key
 */
function deleteHistoryProyek(idProject, norut) {
    // Create form and submit (modal confirmation handled by initializeModalHandlers)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/dataproyek/history/${idProject}/${norut}`;

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

    // Add method override for DELETE
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}

// ========================================
// GLOBAL INITIALIZATION
// ========================================

$(document).ready(function() {
    // Initialize data proyek manager
    window.dataProyekManager = new DataProyekManager();

    // Initialize based on page
    const isIndexPage = $('#dataProyekTableBody').length > 0;
    const isFormPage = $('#proyekForm').length > 0;

    if (isIndexPage) {
        window.dataProyekManager.init({
            pageType: 'index'
        });
    } else if (isFormPage) {
        const isEdit = $('#proyekForm').data('is-edit');
        const isAddToHistory = $('#proyekForm').data('add-to-history') === true || $('#proyekForm').data('add-to-history') === 'true';

        window.dataProyekManager.init({
            pageType: isEdit ? 'edit' : 'create',
            addToHistory: isAddToHistory
        });
    }

    // Global AJAX error handler
    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 419) {
            window.dataProyekManager?.showAlert('Session expired. Please refresh the page.', 'error');
        }
    });
});
