/**
 * Progress Project JavaScript Module
 * Handles Cost Center - Nama Proyek dropdown from Header RAB and form management
 */

$(document).ready(function() {
    let selectedHeaderRABData = null;

    // Destroy any existing Select2 instance first to prevent conflicts
    if ($('#cost_center_proyek').hasClass('select2-hidden-accessible')) {
        $('#cost_center_proyek').select2('destroy');
    }

    // Initialize Select2 for Cost Center - Nama Proyek with AJAX
    $('#cost_center_proyek').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik atau pilih Cost Center - Nama Proyek',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: window.routes.getHeaderRAB,
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (data) {
                console.log('getHeaderRAB response:', data);
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
        selectedHeaderRABData = data;

        // Fill form with Header RAB data
        fillFormWithHeaderRABData(data);

        // Check if Header Progress already exists
        checkExistingHeaderProgress(data.id_rab);
    });

    // Handle Cost Center - Nama Proyek clear
    $('#cost_center_proyek').on('select2:clear', function (e) {
        clearForm();
        selectedHeaderRABData = null;
        $('#tabsSection').slideUp();
    });

    // Fill form with Header RAB data
    function fillFormWithHeaderRABData(data) {
        // Fill konsumen (from History Project via join)
        $('#konsumen').val(data.konsumen_nama || '');

        // Fill no kontrak (from History Project via join)
        $('#no_kontrak').val(data.no_kontrak || '');

        // Format and fill nilai proyek (from History Project via join)
        let nilaiProyek = '';
        if (data.nilai_proyek) {
            nilaiProyek = 'Rp ' + parseFloat(data.nilai_proyek).toLocaleString('id-ID');
        }
        $('#nilai_proyek').val(nilaiProyek);

        // Fill tanggal kontrak (from History Project via join)
        $('#tanggal_kontrak').val(data.start_kontrak || '');

        // Fill akhir kontrak (from History Project via join)
        $('#akhir_kontrak').val(data.finish_kontrak || '');

        // Fill mulai from Header RAB periode_rab
        $('#mulai').val(data.mulai || '');

        // Fill lama from Header RAB lama
        $('#lama').val(data.lama || '');

        // Fill akhir (calculated from mulai + lama in controller)
        $('#akhir').val(data.akhir || '');
    }

    // Clear form
    function clearForm() {
        $('#konsumen, #no_kontrak, #nilai_proyek, #tanggal_kontrak, #akhir_kontrak, #mulai, #lama, #akhir').val('');
    }

    // Check if Header Progress already exists
    function checkExistingHeaderProgress(idRAB) {
        $.ajax({
            url: window.routes.checkHeaderProgress,
            method: 'POST',
            data: {
                _token: window.csrfToken,
                id_rab: idRAB
            },
            success: function(response) {
                if (response.exists) {
                    // Header Progress already exists
                    // Show tabs section
                    $('#tabsSection').slideDown();
                    showAlert('Data header progress berhasil dimuat', 'success');

                    // Initialize Berita Acara if function exists
                    if (typeof window.initBeritaAcara === 'function') {
                        window.initBeritaAcara(selectedHeaderRABData);
                    }

                    // Initialize Issue if function exists
                    if (typeof window.initIssue === 'function') {
                        window.initIssue(selectedHeaderRABData);
                    }
                } else {
                    // Header Progress doesn't exist yet - create automatically
                    createHeaderProgress();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking header progress:', error);
                showAlert('Terjadi kesalahan saat memeriksa data', 'error');
            }
        });
    }

    // Create Header Progress automatically
    function createHeaderProgress() {
        if (!selectedHeaderRABData) {
            showAlert('Terjadi kesalahan: Data proyek tidak ditemukan', 'error');
            return;
        }

        // Show loading message
        showAlert('Membuat header progress...', 'info');

        // Convert date format from dd/mm/yyyy to yyyy-mm-dd for database
        const mulaiParts = selectedHeaderRABData.mulai.split('/');
        const mulaiFormatted = mulaiParts[2] + '-' + mulaiParts[1] + '-' + mulaiParts[0];

        $.ajax({
            url: window.routes.createHeaderProgress,
            method: 'POST',
            data: {
                _token: window.csrfToken,
                id_rab: selectedHeaderRABData.id_rab,
                periode_mulai: mulaiFormatted,
                lama: parseInt(selectedHeaderRABData.lama, 10)
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Header Progress berhasil dibuat!', 'success');

                    // Show tabs section
                    $('#tabsSection').slideDown();

                    // Initialize Berita Acara if function exists
                    if (typeof window.initBeritaAcara === 'function') {
                        window.initBeritaAcara(selectedHeaderRABData);
                    }

                    // Initialize Issue if function exists
                    if (typeof window.initIssue === 'function') {
                        window.initIssue(selectedHeaderRABData);
                    }
                } else {
                    showAlert(response.message || 'Gagal membuat Header Progress', 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Terjadi kesalahan saat membuat Header Progress';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert(errorMessage, 'error');
            }
        });
    }

    // Handle tab switching
    $('#progressTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Hide default message when any tab is shown
        $('#default-message').hide();
    });

    // Initially show the default message
    $('#default-message').show();

    // Hide default message when a tab is clicked
    $('#progressTabs button[data-bs-toggle="tab"]').on('click', function() {
        $('#default-message').hide();
    });

    // Format currency helper function
    function formatCurrency(amount) {
        if (!amount) return '';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
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
});
