/**
 * Upload RAB JavaScript Module
 * Handles Cost Center - Nama Proyek dropdown, Header RAB modal, and form management
 */

$(document).ready(function() {
    let selectedProjectData = null;

    // Initialize date pickers
    initializeDatePickers();

    // Initialize Select2 for Cost Center - Nama Proyek
    $('#cost_center_proyek').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik atau pilih Cost Center - Nama Proyek',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: window.routes.getCostCenterProyek,
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });

    // Handle Cost Center - Nama Proyek selection
    $('#cost_center_proyek').on('select2:select', function (e) {
        const data = e.params.data;
        selectedProjectData = data;

        // Check if Header RAB already exists
        checkExistingHeaderRAB(data.id);
    });

    // Handle Cost Center - Nama Proyek clear
    $('#cost_center_proyek').on('select2:clear', function (e) {
        clearForm();
        selectedProjectData = null;
    });

    // Check existing Header RAB
    function checkExistingHeaderRAB(projectId) {
        $('.loading-spinner').show();

        $.ajax({
            url: window.routes.checkHeaderRAB,
            method: 'POST',
            data: {
                project_id: projectId,
                _token: window.csrfToken
            },
            success: function(response) {
                if (response.exists) {
                    // Header RAB exists, fill form with existing data
                    fillFormWithExistingData(response.project);

                    // Store the existing ID RAB
                    if (response.project.id_rab) {
                        selectedProjectData.id_rab = response.project.id_rab;
                    }

                    $('#btnUpload').prop('disabled', false);
                } else {
                    // Header RAB doesn't exist, show modal to create new
                    showHeaderRABModal();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking header RAB:', error);
                showAlert('Terjadi kesalahan saat mengecek data Header RAB', 'error');
            },
            complete: function() {
                $('.loading-spinner').hide();
            }
        });
    }

    // Fill form with existing data
    function fillFormWithExistingData(projectData) {
        $('#konsumen').val(projectData.konsumen_nama);
        $('#no_kontrak').val(projectData.no_kontrak);
        $('#nilai_proyek').val(formatCurrency(projectData.nilai_proyek));
        $('#tanggal_kontrak').val(projectData.start_kontrak);
        $('#akhir_kontrak').val(projectData.finish_kontrak);
        $('#mulai').val(projectData.mulai);
        $('#lama').val(projectData.lama);

        // Enable buttons when data is loaded
        $('#btnUpload').prop('disabled', false);

        // Auto-load Detail RAB if Header RAB exists
        setTimeout(() => {
            loadDetailRABData();
            loadSummaryDetailRABData();
        }, 500);
    }

    // Show Header RAB Modal
    function showHeaderRABModal() {
        if (!selectedProjectData) return;

        // Set modal data
        $('#modal_project_id').val(selectedProjectData.id);
        $('#modal_cost_center_proyek').val(selectedProjectData.cost_center + ' - ' + selectedProjectData.namaproject);
        $('#modal_nilai_proyek').val(formatCurrency(selectedProjectData.nilai_proyek));

        // Generate and display ID RAB from server
        $('#modal_id_rab').val('Generating...');

        $.ajax({
            url: window.routes.generateIdRAB,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#modal_id_rab').val(response.id_rab);
                } else {
                    $('#modal_id_rab').val('Error generating ID');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error generating ID RAB:', error);
                $('#modal_id_rab').val('Error generating ID');
            }
        });

        // Clear input fields
        $('#modal_periode_rab').val('');
        $('#modal_lama').val('');

        // Show modal
        $('#headerRABModal').modal('show');
    }    // Save Header RAB
    $('#btnSaveHeaderRAB').click(function() {
        const form = $('#headerRABForm')[0];

        // Validate form
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Validate date format
        const datePattern = /^\d{2}\/\d{2}\/\d{4}$/;
        const periodeRAB = $('#modal_periode_rab').val();

        if (!datePattern.test(periodeRAB)) {
            $('#modal_periode_rab').addClass('is-invalid');
            return;
        }

        const formData = {
            project_id: $('#modal_project_id').val(),
            periode_rab: periodeRAB,
            lama: $('#modal_lama').val(),
            _token: window.csrfToken
        };

        $('#btnSaveHeaderRAB').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: window.routes.storeHeaderRAB,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');

                    // Close modal
                    $('#headerRABModal').modal('hide');

                    // Update ID RAB field in main form if needed
                    if (response.id_rab) {
                        // Store the actual ID RAB for future reference
                        selectedProjectData.id_rab = response.id_rab;
                    }

                    // Fill main form
                    fillFormWithExistingData({
                        konsumen_nama: selectedProjectData.konsumen_nama,
                        no_kontrak: selectedProjectData.no_kontrak,
                        nilai_proyek: selectedProjectData.nilai_proyek,
                        start_kontrak: selectedProjectData.start_kontrak,
                        finish_kontrak: selectedProjectData.finish_kontrak,
                        mulai: periodeRAB,
                        lama: $('#modal_lama').val()
                    });

                    $('#btnUpload').prop('disabled', false);
                } else {
                    showAlert(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showAlert(errorMessage, 'error');
            },
            complete: function() {
                $('#btnSaveHeaderRAB').prop('disabled', false).html('<i class="bx bx-check me-1"></i> Simpan');
            }
        });
    });

    // Clear form
    function clearForm() {
        $('#konsumen, #no_kontrak, #nilai_proyek, #tanggal_kontrak, #akhir_kontrak, #mulai, #lama').val('');
        $('#btnUpload').prop('disabled', true);
    }

    // Format currency
    function formatCurrency(amount) {
        if (!amount) return '';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Initialize date pickers
    function initializeDatePickers() {
        // Date fields that need date picker functionality
        const dateFields = ['#modal_periode_rab'];

        dateFields.forEach(function(fieldSelector) {
            const $textInput = $(fieldSelector);
            const $dateInput = $(fieldSelector + '_date');
            const $calendarBtn = $textInput.closest('.date-input-group').find('.date-picker-btn');

            // Text input formatting
            $textInput.on('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0, 5) + '/' + value.substring(5, 9);
                }
                this.value = value;

                // Validate and sync with hidden date input
                if (value.length === 10) {
                    const isValid = validateDateField(value);
                    if (isValid) {
                        // Convert to yyyy-mm-dd for date input
                        const parts = value.split('/');
                        const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        $dateInput.val(isoDate);
                    }
                }
            });

            // Calendar button click handler
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
                    .appendTo($textInput.closest('.input-group'));

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
                    const formattedDate = `${day}/${month}/${year}`;
                    $textInput.val(formattedDate).trigger('input');
                }
            });
        });
    }

    // Date validation function
    function validateDateField(dateString) {
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

    // Date input mask for periode RAB (legacy support - replaced by date picker)
    // Keeping for compatibility but functionality now handled by initializeDatePickers()

    // Reset form button
    $('#btnResetForm').click(function() {
        $('#cost_center_proyek').val(null).trigger('change');
        clearForm();
        selectedProjectData = null;
    });

    // Reset form event (kept for compatibility)
    $('#uploadRABForm')[0].addEventListener('reset', function() {
        $('#cost_center_proyek').val(null).trigger('change');
        clearForm();
        selectedProjectData = null;
    });

    // Upload button click
    $('#btnUpload').click(function() {
        const fileInput = $('#document_rab')[0];

        if (!fileInput.files.length) {
            showAlert('Silakan pilih file dokumen RAB terlebih dahulu', 'error');
            return;
        }

        // Validate file type
        const file = fileInput.files[0];
        const allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!['xls', 'xlsx'].includes(fileExtension)) {
            showAlert('File harus berformat Excel (.xls atau .xlsx)', 'error');
            return;
        }

        if (!selectedProjectData || !selectedProjectData.id) {
            showAlert('Silakan pilih Cost Center - Nama Proyek terlebih dahulu', 'error');
            return;
        }

        // Create form data
        const formData = new FormData();
        formData.append('project_id', selectedProjectData.id);
        formData.append('document_rab', file);
        formData.append('_token', window.csrfToken);

        // Show loading state
        const $btn = $('#btnUpload');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Mengupload...');

        $.ajax({
            url: window.routes.uploadExcel,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');

                    // Load and display Detail RAB data and Summary Detail RAB data
                    loadDetailRABData();
                    loadSummaryDetailRABData();

                    // Clear file input
                    $('#document_rab').val('');
                } else {
                    if (response.action_required === 'add_specs') {
                        showMissingSpecsModal(response.missing_specs);
                    } else {
                        showAlert(response.message || 'Terjadi kesalahan saat memproses file', 'error');
                    }
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Terjadi kesalahan saat mengupload file';

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    if (xhr.responseJSON.action_required === 'add_specs') {
                        showMissingSpecsModal(xhr.responseJSON.missing_specs);
                        return;
                    }
                }

                showAlert(errorMessage, 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Load Detail RAB data
    function loadDetailRABData() {
        if (!selectedProjectData || !selectedProjectData.id) {
            return;
        }

        $.ajax({
            url: window.routes.getDetailRAB,
            method: 'GET',
            data: {
                project_id: selectedProjectData.id
            },
            success: function(response) {
                if (response.success) {
                    displayDetailRABTable(response.data, response.bulan_headers, response.id_rab);
                } else {
                    $('#detailRABContainer').html(`
                        <div class="text-center py-4">
                            <i class="bx bx-info-circle mb-2" style="font-size: 48px; color: #6c757d;"></i>
                            <p class="mb-0 text-muted">Belum ada data Detail RAB.</p>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading Detail RAB:', error);
                $('#detailRABContainer').html(`
                    <div class="text-center py-4">
                        <i class="bx bx-error-circle mb-2" style="font-size: 48px; color: #dc3545;"></i>
                        <p class="mb-0 text-danger">Gagal memuat data Detail RAB.</p>
                    </div>
                `);
            }
        });
    }

    // Display Detail RAB table
    function displayDetailRABTable(data, bulanHeaders, idRAB) {
        $('#currentIdRAB').text(idRAB);
        $('#detailRABSection').show();

        if (!data || data.length === 0) {
            $('#detailRABContainer').html(`
                <div class="text-center py-4">
                    <i class="bx bx-info-circle mb-2" style="font-size: 48px; color: #6c757d;"></i>
                    <p class="mb-0 text-muted">Belum ada data Detail RAB. Upload file Excel untuk menampilkan data.</p>
                </div>
            `);
            return;
        }

        // Generate table headers
        // add specific classes so we can style bulan / nilai columns
        let tableHeaders = '<th class="fw-bold">No</th><th class="fw-bold">Keterangan</th>';
        bulanHeaders.forEach((bulan, index) => {
            tableHeaders += `<th class="fw-bold bulan-col text-center">Bulan ke ${index}<br><small class="text-muted">${escapeHtml(bulan)}</small></th>`;
        });

        // Generate table rows
        let tableRows = '';
        data.forEach((item, index) => {
            let row = `<tr><td>${index + 1}</td><td>${escapeHtml(item.keterangan)}</td>`;

            for (let i = 0; i < bulanHeaders.length; i++) {
                const value = item.values[i];
                if (value) {
                    // add class nama 'nilai-col' so CSS can widen this column
                    row += `<td class="text-center nilai-col">${escapeHtml(value.formatted_nilai)}</td>`;
                } else {
                    row += `<td class="text-center nilai-col text-muted">-</td>`;
                }
            }

            row += '</tr>';
            tableRows += row;
        });

        const tableHtml = `
            <div class="table-responsive rab-detail-table-container">
                <table class="table table-striped table-hover rab-detail-table">
                    <thead class="table-light">
                        <tr>${tableHeaders}</tr>
                    </thead>
                    <tbody>
                        ${tableRows}
                    </tbody>
                </table>
            </div>
        `;

        $('#detailRABContainer').html(tableHtml);
    }

    // Show missing specifications modal
    function showMissingSpecsModal(missingSpecs) {
        let specsList = '<ul class="list-unstyled">';
        missingSpecs.forEach(spec => {
            specsList += `<li><i class="bx bx-x-circle text-danger me-2"></i>${escapeHtml(spec)}</li>`;
        });
        specsList += '</ul>';

        const modalHtml = `
            <div class="modal fade" id="missingSpecsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bx bx-error-circle text-warning me-2"></i>Spesifikasi Tidak Ditemukan
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Beberapa spesifikasi dalam file Excel tidak ditemukan dalam database:</p>
                            ${specsList}
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                Silakan tambahkan spesifikasi yang hilang melalui menu <strong>Spesifikasi RAB</strong> terlebih dahulu, kemudian coba upload ulang.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <a href="/spesifikasirab/create" class="btn btn-primary" target="_blank">
                                <i class="bx bx-plus me-1"></i>Tambah Spesifikasi RAB
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#missingSpecsModal').remove();

        // Add modal to body and show
        $('body').append(modalHtml);
        $('#missingSpecsModal').modal('show');

        // Clean up when modal is hidden
        $('#missingSpecsModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Auto-load Detail RAB when form is filled
    function autoLoadDetailRAB() {
        if (selectedProjectData && selectedProjectData.id_rab) {
            loadDetailRABData();
        }
    }

    // Show alert function
    function showAlert(message, type = 'info') {
        // Remove existing alerts
        hideAlert();

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
            setTimeout(() => hideAlert(), 5000);
        }

        // Scroll to alert
        $('html, body').animate({
            scrollTop: $('.alert-custom').offset().top - 100
        }, 300);
    }

    // Hide alert function
    function hideAlert() {
        $('.alert-custom').fadeOut(300, function() {
            $(this).remove();
        });
    }

    // Load Summary Detail RAB data
// Load Summary Detail RAB data
function loadSummaryDetailRABData() {
    if (!selectedProjectData || !selectedProjectData.id) {
        return;
    }

    $.ajax({
        url: window.routes.getSummaryDetailRAB,
        method: 'GET',
        data: {
            project_id: selectedProjectData.id
        },
        success: function(response) {
            console.log('Summary Detail RAB Response:', response);

            if (response.success) {
                displaySummaryDetailRABTable(response.data, response.id_rab);
            } else {
                $('#summaryDetailRABContainer').html(`
                    <div class="text-center py-4">
                        <i class="bx bx-info-circle mb-2" style="font-size: 48px; color: #6c757d;"></i>
                        <p class="mb-0 text-muted">Belum ada data Summary Detail RAB.</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading Summary Detail RAB:', error);
            console.error('Response:', xhr.responseText);

            $('#summaryDetailRABContainer').html(`
                <div class="text-center py-4">
                    <i class="bx bx-error-circle mb-2" style="font-size: 48px; color: #dc3545;"></i>
                    <p class="mb-0 text-danger">Gagal memuat data Summary Detail RAB.</p>
                </div>
            `);
        }
    });
}

// Display Summary Detail RAB table
function displaySummaryDetailRABTable(data, idRAB) {
    $('#summaryIdRAB').text(idRAB);
    $('#summaryDetailRABSection').show();

    if (!data || data.length === 0) {
        $('#summaryDetailRABContainer').html(`
            <div class="text-center py-4">
                <i class="bx bx-info-circle mb-2" style="font-size: 48px; color: #6c757d;"></i>
                <p class="mb-0 text-muted">Belum ada data Summary Detail RAB. Upload file Excel untuk menampilkan data.</p>
            </div>
        `);
        return;
    }

    // Generate table headers: make No narrower and Keterangan closer; Nilai header left-aligned
    let tableHeaders = `
        <th class="fw-bold text-start" style="width: 5%;">No</th>
        <th class="fw-bold text-start ket-kol" style="width: 75%;">Keterangan</th>
        <th class="fw-bold text-end bulan-col" style="width: 20%;">Nilai</th>
    `;

    // Generate table rows
    let tableRows = '';
    data.forEach((item, index) => {
        console.log('Item ' + (index + 1) + ':', item);

        tableRows += `
            <tr>
                <td class="text-start">${index + 1}</td>
                <td class="text-start ket-kol">${escapeHtml(item.keterangan || '-')}</td>
                <td class="text-end nilai-col">${escapeHtml(item.formatted_nilai || '-')}</td>
            </tr>
        `;
    });

    const tableHtml = `
        <div class="table-responsive rab-detail-table-container">
            <table class="table table-striped table-hover rab-detail-table">
                <thead class="table-light">
                    <tr>${tableHeaders}</tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
        </div>
    `;

    $('#summaryDetailRABContainer').html(tableHtml);
}

});
