/**
 * Pendapatan Proyek JavaScript Module
 */

$(document).ready(function() {
    let currentBAData = null;
    let pendapatanData = [];
    let isReadOnly = false;
    let deleteNoPendapatan = null;

    checkUserRole();

    // Initialize Select2 for BA selection
    $('#berita_acara_select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik untuk mencari Informasi Proyek...',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: window.routes.getApprovedBA,
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

    // Handle BA selection
    $('#berita_acara_select').on('select2:select', function (e) {
        const data = e.params.data;
        currentBAData = data;
        fillBAInfo(data);
        loadPendapatanData();
        $('#baInfoSection').slideDown();
        $('#pendapatanSection').slideDown();
    });

    // Handle BA clear
    $('#berita_acara_select').on('select2:clear', function (e) {
        currentBAData = null;
        $('#baInfoSection').slideUp();
        $('#pendapatanSection').slideUp();
        clearBAInfo();
    });

    // Add Pendapatan button
    $('#btnAddPendapatan').on('click', function() {
        showPendapatanModal();
    });

    // Confirm delete
    $('#confirmDeletePendapatanBtn').on('click', function() {
        performDelete();
    });

    // Modal cleanup
    $(document).on('hidden.bs.modal', '#pendapatanModal, #viewPendapatanModal, #deletePendapatanConfirmModal', function() {
        $(this).next('.modal-backdrop').remove();
        if ($('.modal.show').length === 0) {
            $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
            $('.modal-backdrop').remove();
        }
        $(this).removeData('bs.modal');
    });

    function checkUserRole() {
        isReadOnly = window.userRole !== 'Super Admin';
    }

    function fillBAInfo(data) {
        // Baris 1: Cost Center - Nama Proyek, Konsumen, No Kontrak
        $('#info_namaproject').val(`${data.cost_center} - ${data.namaproject}`);
        $('#info_konsumen').val(data.konsumen_nama || '-');
        $('#info_no_kontrak').val(data.no_kontrak || '-');

        // Baris 2: Nilai Proyek, Tanggal Kontrak, Akhir Kontrak
        const nilaiProyek = data.nilai_proyek ? formatCurrencyNoDecimal(data.nilai_proyek) : '-';
        $('#info_nilai_proyek').val(nilaiProyek);
        $('#info_tanggal_kontrak').val(data.start_kontrak || '-');
        $('#info_akhir_kontrak').val(data.finish_kontrak || '-');

        // Baris 3: Mulai (dari Header RAB), Lama, Akhir
        $('#info_mulai').val(data.mulai || '-');
        $('#info_lama').val(data.lama || '-');
        $('#info_akhir').val(data.akhir || '-');

        // Baris 4: Periode BA dan Nilai BA
        const periodBA = (data.periode_mulai && data.periode_akhir) ?
            `${data.periode_mulai} - ${data.periode_akhir}` : '-';
        $('#info_periode_ba').val(periodBA);

        const nilaiBA = data.nilai_ba ? formatCurrencyNoDecimal(data.nilai_ba) : '-';
        $('#info_nilai_ba').val(nilaiBA);
    }

    function clearBAInfo() {
        $('#info_namaproject, #info_konsumen, #info_no_kontrak, #info_nilai_proyek, #info_tanggal_kontrak, #info_akhir_kontrak, #info_mulai, #info_lama, #info_akhir, #info_periode_ba, #info_nilai_ba').val('-');
    }

    function loadPendapatanData() {
        if (!currentBAData) return;

        $('#pendapatanTableContainer').html(`
            <div class="text-center py-5">
                <i class="bx bx-loader-alt bx-spin" style="font-size: 48px; color: #d9dee3;"></i>
                <p class="text-muted mt-3">Memuat data pendapatan...</p>
            </div>
        `);

        $.ajax({
            url: window.routes.getPendapatanByBA,
            method: 'GET',
            data: {
                id_project: currentBAData.id_project,
                norut: currentBAData.norut,
                no_ba: currentBAData.no_ba
            },
            success: function(response) {
                if (response.success) {
                    pendapatanData = response.data;
                    renderPendapatanTable();
                } else {
                    showAlert(response.message || 'Gagal memuat data', 'error');
                }
            },
            error: function(xhr) {
                console.error('Error loading pendapatan:', xhr);
                showAlert('Terjadi kesalahan saat memuat data', 'error');
            }
        });
    }

    function renderPendapatanTable() {
        const $container = $('#pendapatanTableContainer');

        if (!pendapatanData || pendapatanData.length === 0) {
            $container.html(`
                <div class="text-center py-5">
                    <i class="bx bx-file-blank" style="font-size: 64px; color: #d9dee3;"></i>
                    <p class="text-muted mt-3 mb-1" style="font-size: 1rem; font-weight: 500;">Belum ada data Pendapatan</p>
                    <p class="text-muted" style="font-size: 0.875rem;">Klik tombol "Tambah Pendapatan" untuk membuat data baru</p>
                </div>
            `);
            return;
        }

        let tableHTML = `
            <div class="table-responsive pendapatan-table-container">
                <table class="table table-hover pendapatan-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>No. Dokumen</th>
                            <th>Periode Pendapatan</th>
                            <th>Nilai Pendapatan</th>
                            <th class="text-center">Dokumen</th>
                            ${!isReadOnly ? '<th class="text-center">Aksi</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
        `;

        pendapatanData.forEach(function(pendapatan, index) {
            const nilaiFormatted = pendapatan.nilai_pendapatan ? formatCurrency(pendapatan.nilai_pendapatan) : '-';
            const tanggal = pendapatan.tanggal ? formatDate(pendapatan.tanggal) : '-';
            const periodeM = pendapatan.periode_mulai ? formatDate(pendapatan.periode_mulai) : '';
            const periodeA = pendapatan.periode_akhir ? formatDate(pendapatan.periode_akhir) : '';
            const periode = (periodeM && periodeA) ? `${periodeM} - ${periodeA}` : (periodeM || periodeA || '-');
            const noUrut = pendapatan.norut_display || (index + 1);

            console.log('Processing pendapatan row:', pendapatan.no_pendapatan, 'file_ba:', pendapatan.file_ba, 'hasFile:', !!pendapatan.file_ba);

            const hasFile = pendapatan.file_ba ? true : false;
            const fileIcon = hasFile ?
                `<div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-info" title="Preview"
                            onclick="if(window.filePreview) { window.filePreview.showPreview('${window.location.origin}/storage/${pendapatan.file_ba}', '${pendapatan.file_ba.split('/').pop()}', '${window.routes.downloadPendapatan.replace(':noPendapatan', pendapatan.no_pendapatan)}?id_project=${pendapatan.id_project}&norut=${pendapatan.norut}&no_ba=${encodeURIComponent(pendapatan.no_ba)}'); } else { alert('File preview tidak tersedia. Silakan refresh halaman.'); console.error('FilePreview not loaded'); }">
                        <i class="bx bx-show"></i>
                    </button>
                    <a href="${window.routes.downloadPendapatan.replace(':noPendapatan', pendapatan.no_pendapatan)}?id_project=${pendapatan.id_project}&norut=${pendapatan.norut}&no_ba=${encodeURIComponent(pendapatan.no_ba)}" class="btn btn-sm btn-outline-primary" title="Download">
                        <i class="bx bx-download"></i>
                    </a>
                </div>` :
                '<span class="text-muted">-</span>';

            tableHTML += `
                <tr data-no-pendapatan="${pendapatan.no_pendapatan}">
                    <td>
                        <span class="pendapatan-no-urut" data-no-pendapatan="${pendapatan.no_pendapatan}"${!isReadOnly ? ` ondblclick="editPendapatan('${pendapatan.no_pendapatan}')" title="Double klik untuk edit"` : ''}>
                            ${noUrut}
                        </span>
                    </td>
                    <td>${tanggal}</td>
                    <td>
                        <div class="truncate-text" title="${escapeHtml(pendapatan.no_dokumen || '-')}">
                            ${escapeHtml(pendapatan.no_dokumen || '-')}
                        </div>
                    </td>
                    <td>${periode}</td>
                    <td class="text-start">
                        <small class="currency-display">${nilaiFormatted}</small>
                    </td>
                    <td class="text-center">${fileIcon}</td>
                    ${!isReadOnly ? `
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item btn-detail-pendapatan" href="javascript:void(0);" data-no-pendapatan="${pendapatan.no_pendapatan}">
                                        <i class="bx bx-show me-1"></i> Lihat Detail
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger btn-delete-pendapatan" href="javascript:void(0);" data-no-pendapatan="${pendapatan.no_pendapatan}">
                                        <i class="bx bx-trash me-1"></i> Hapus
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                    ` : ''}
                </tr>
            `;
        });

        tableHTML += `
                    </tbody>
                </table>
            </div>
        `;

        $container.html(tableHTML);
        attachEventHandlers();
    }

    function attachEventHandlers() {
        $('.btn-detail-pendapatan').off('click').on('click', function() {
            const noPendapatan = $(this).data('no-pendapatan');
            viewPendapatan(noPendapatan);
        });

        $('.btn-delete-pendapatan').off('click').on('click', function() {
            const noPendapatan = $(this).data('no-pendapatan');
            deleteNoPendapatan = noPendapatan;
            $('#deletePendapatanConfirmModal').modal('show');
        });
    }

    function viewPendapatan(noPendapatan) {
        const pendapatan = pendapatanData.find(p => p.no_pendapatan === noPendapatan);
        if (!pendapatan) {
            showAlert('Data tidak ditemukan', 'error');
            return;
        }

        showPendapatanModal(pendapatan, true);
    }

    window.editPendapatan = function(noPendapatan) {
        if (isReadOnly) return;
        const pendapatan = pendapatanData.find(p => p.no_pendapatan === noPendapatan);
        if (pendapatan) {
            showPendapatanModal(pendapatan, false);
        }
    };

    function showPendapatanModal(pendapatan = null, viewMode = false) {
        const isEdit = pendapatan !== null && !viewMode;
        const modalTitle = viewMode ? 'Detail Pendapatan' : (isEdit ? 'Edit Pendapatan' : 'Tambah Pendapatan');

        // Format dates for display
        let tanggal_display = '';
        let periodeM_display = '';
        let periodeA_display = '';
        let tanggal_value = '';
        let periodeM_value = '';
        let periodeA_value = '';

        if (pendapatan) {
            if (pendapatan.tanggal) {
                tanggal_value = pendapatan.tanggal;
                tanggal_display = formatDate(pendapatan.tanggal);
            }
            if (pendapatan.periode_mulai) {
                periodeM_value = pendapatan.periode_mulai;
                periodeM_display = formatDate(pendapatan.periode_mulai);
            }
            if (pendapatan.periode_akhir) {
                periodeA_value = pendapatan.periode_akhir;
                periodeA_display = formatDate(pendapatan.periode_akhir);
            }
        } else {
            // Default to today for new entry
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            tanggal_value = `${yyyy}-${mm}-${dd}`;
            tanggal_display = `${dd}/${mm}/${yyyy}`;
        }

        const modalHTML = `
            <div class="modal fade" id="pendapatanModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${modalTitle}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <form id="pendapatanForm" enctype="multipart/form-data">
                                <input type="hidden" id="pend_no_pendapatan" value="${pendapatan ? pendapatan.no_pendapatan : ''}">

                                <div class="form-section mb-4">
                                    <h6 class="mb-3" style="font-size: 0.95rem;"><i class="bx bx-info-circle me-2"></i>Informasi Dasar</h6>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="pend_tanggal" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.4rem;">Tanggal <span class="text-danger">*</span></label>
                                            ${viewMode || isEdit ?
                                                `<input type="text" class="form-control readonly-field" style="font-size: 0.875rem;" value="${tanggal_display}" readonly>` :
                                                `<div class="input-group date-input-group">
                                                    <input type="text" class="form-control" style="font-size: 0.875rem;" id="pend_tanggal" placeholder="dd/mm/yyyy" maxlength="10" value="${tanggal_display}" required>
                                                    <input type="date" class="date-picker-hidden" id="pend_tanggal_date" value="${tanggal_value}">
                                                    <button type="button" class="btn btn-outline-secondary date-picker-btn" data-field="pend_tanggal" tabindex="-1" title="Pilih tanggal">
                                                        <i class="bx bx-calendar"></i>
                                                    </button>
                                                </div>`
                                            }
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="pend_no_dokumen" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.4rem;">No. Dokumen <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control ${viewMode ? 'readonly-field' : ''}" style="font-size: 0.875rem;" id="pend_no_dokumen" value="${pendapatan ? escapeHtml(pendapatan.no_dokumen || '') : ''}" maxlength="100" required${viewMode ? ' readonly' : ''}>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section mb-4">
                                    <h6 class="mb-3" style="font-size: 0.95rem;"><i class="bx bx-calendar me-2"></i>Periode & Nilai</h6>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="pend_periode_mulai" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.4rem;">Periode Mulai</label>
                                            ${viewMode ?
                                                `<input type="text" class="form-control readonly-field" style="font-size: 0.875rem;" value="${periodeM_display}" readonly>` :
                                                `<div class="input-group date-input-group">
                                                    <input type="text" class="form-control" style="font-size: 0.875rem;" id="pend_periode_mulai" placeholder="dd/mm/yyyy" maxlength="10" value="${periodeM_display}">
                                                    <input type="date" class="date-picker-hidden" id="pend_periode_mulai_date" value="${periodeM_value}">
                                                    <button type="button" class="btn btn-outline-secondary date-picker-btn" data-field="pend_periode_mulai" tabindex="-1" title="Pilih tanggal">
                                                        <i class="bx bx-calendar"></i>
                                                    </button>
                                                </div>`
                                            }
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="pend_periode_akhir" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.4rem;">Periode Akhir</label>
                                            ${viewMode ?
                                                `<input type="text" class="form-control readonly-field" style="font-size: 0.875rem;" value="${periodeA_display}" readonly>` :
                                                `<div class="input-group date-input-group">
                                                    <input type="text" class="form-control" style="font-size: 0.875rem;" id="pend_periode_akhir" placeholder="dd/mm/yyyy" maxlength="10" value="${periodeA_display}">
                                                    <input type="date" class="date-picker-hidden" id="pend_periode_akhir_date" value="${periodeA_value}">
                                                    <button type="button" class="btn btn-outline-secondary date-picker-btn" data-field="pend_periode_akhir" tabindex="-1" title="Pilih tanggal">
                                                        <i class="bx bx-calendar"></i>
                                                    </button>
                                                </div>`
                                            }
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="pend_nilai_pendapatan" class="form-label" style="font-size: 0.875rem; margin-bottom: 0.4rem;">Nilai Pendapatan</label>
                                            <input type="text" class="form-control ${viewMode ? 'readonly-field' : ''}" style="font-size: 0.875rem;" id="pend_nilai_pendapatan" value="${pendapatan && pendapatan.nilai_pendapatan ? formatNumberNoDecimal(pendapatan.nilai_pendapatan) : ''}" placeholder="0"${viewMode ? ' readonly' : ''}>
                                            ${!viewMode ? '<small class="text-muted" style="font-size: 0.75rem;">Format: #.###.###.###</small>' : ''}
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="pend_file_ba" class="form-label ms-2" style="font-size: 0.875rem; margin-bottom: 0.4rem;">Upload Dokumen BA</label>
                                            ${viewMode && pendapatan && pendapatan.file_ba ?
                                                `<div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-info btn-sm ms-2" style="font-size: 0.875rem;"
                                                            onclick="if(window.filePreview) { window.filePreview.showPreview('${window.location.origin}/storage/${pendapatan.file_ba}', '${pendapatan.file_ba.split('/').pop()}', '${window.routes.downloadPendapatan.replace(':noPendapatan', pendapatan.no_pendapatan)}?id_project=${pendapatan.id_project}&norut=${pendapatan.norut}&no_ba=${encodeURIComponent(pendapatan.no_ba)}'); } else { alert('File preview tidak tersedia. Silakan refresh halaman.'); console.error('FilePreview not loaded'); }">
                                                        <i class="bx bx-show me-1"></i>Preview
                                                    </button>
                                                    <a href="${window.routes.downloadPendapatan.replace(':noPendapatan', pendapatan.no_pendapatan)}?id_project=${pendapatan.id_project}&norut=${pendapatan.norut}&no_ba=${encodeURIComponent(pendapatan.no_ba)}" class="btn btn-outline-primary btn-sm" style="font-size: 0.875rem;">
                                                        <i class="bx bx-download me-1"></i>Download
                                                    </a>
                                                </div>` :
                                                !viewMode ?
                                                `<input type="file" class="form-control" style="font-size: 0.875rem;" id="pend_file_ba" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                                <small class="text-muted" style="font-size: 0.75rem;">Max 10MB (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG)</small>` :
                                                '<p class="text-muted" style="font-size: 0.875rem;">Tidak ada dokumen</p>'
                                            }
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i> ${viewMode ? 'Tutup' : 'Batal'}
                            </button>
                            ${!viewMode ? `<button type="button" class="btn btn-primary" id="btnSavePendapatan">
                                <i class="bx bx-check me-1"></i> ${isEdit ? 'Update' : 'Simpan'}
                            </button>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        $('#pendapatanModal').remove();
        $('.modal-backdrop').remove();

        $('body').append(modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('pendapatanModal'));

        $('#pendapatanModal').on('shown.bs.modal', function() {
            if (!viewMode) {
                initializeDatePickers();

                // Currency formatting untuk nilai pendapatan (tanpa desimal, sama seperti ba_nilai_ba)
                $('#pend_nilai_pendapatan').on('keyup', function() {
                    let val = $(this).val().replace(/\D/g, '');
                    if (val) {
                        $(this).val(formatNumberNoDecimal(val));
                    }
                });

                $('#btnSavePendapatan').off('click').on('click', function() {
                    savePendapatan(isEdit);
                });

                if (!isEdit) {
                    $('#pend_no_dokumen').focus();
                }
            }
        });

        modal.show();
    }

    function initializeDatePickers() {
        const dateFields = ['pend_tanggal', 'pend_periode_mulai', 'pend_periode_akhir'];

        dateFields.forEach(function(fieldId) {
            const $textInput = $('#' + fieldId);
            const $dateInput = $('#' + fieldId + '_date');
            const $calendarBtn = $textInput.closest('.date-input-group').find('.date-picker-btn');

            $textInput.on('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0, 5) + '/' + value.substring(5, 9);
                }
                this.value = value;

                if (value.length === 10) {
                    const parts = value.split('/');
                    const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    $dateInput.val(isoDate);
                }
            });

            $calendarBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

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

                $dateInput.focus();

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

    function savePendapatan(isEdit) {
        const tanggal_display = $('#pend_tanggal').val();
        const noDokumen = $('#pend_no_dokumen').val();
        const periodeM_display = $('#pend_periode_mulai').val();
        const periodeA_display = $('#pend_periode_akhir').val();
        const nilaiRaw = $('#pend_nilai_pendapatan').val();
        const fileInput = $('#pend_file_ba')[0];

        if (!tanggal_display && !isEdit) {
            showAlert('Tanggal harus diisi', 'error');
            return;
        }

        if (!noDokumen) {
            showAlert('No. Dokumen harus diisi', 'error');
            return;
        }

        // Convert dates to ISO format
        let tanggal = '';
        let periodeM = '';
        let periodeA = '';

        if (tanggal_display && tanggal_display.length === 10) {
            const parts = tanggal_display.split('/');
            tanggal = `${parts[2]}-${parts[1]}-${parts[0]}`;
        }

        if (periodeM_display && periodeM_display.length === 10) {
            const partsM = periodeM_display.split('/');
            periodeM = `${partsM[2]}-${partsM[1]}-${partsM[0]}`;
        }

        if (periodeA_display && periodeA_display.length === 10) {
            const partsA = periodeA_display.split('/');
            periodeA = `${partsA[2]}-${partsA[1]}-${partsA[0]}`;
        }

        // Clean nilai_pendapatan (remove dots only, sama seperti ba_nilai_ba)
        const nilaiCleaned = nilaiRaw.replace(/\D/g, '');

        const formData = new FormData();
        formData.append('_token', window.csrfToken);
        formData.append('id_project', currentBAData.id_project);
        formData.append('norut', currentBAData.norut);
        formData.append('no_ba', currentBAData.no_ba);
        formData.append('no_dokumen', noDokumen);

        if (!isEdit && tanggal) {
            formData.append('tanggal', tanggal);
        }

        if (periodeM) formData.append('periode_mulai', periodeM);
        if (periodeA) formData.append('periode_akhir', periodeA);
        if (nilaiCleaned) formData.append('nilai_pendapatan', nilaiCleaned);

        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('file_ba', fileInput.files[0]);
        }

        const url = isEdit ?
            window.routes.updatePendapatan.replace(':noPendapatan', $('#pend_no_pendapatan').val()) :
            window.routes.storePendapatan;

        const method = isEdit ? 'POST' : 'POST';

        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#pendapatanModal').modal('hide');
                    loadPendapatanData();
                } else {
                    showAlert(response.message || 'Gagal menyimpan data', 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showAlert(errorMsg, 'error');
            }
        });
    }

    function performDelete() {
        if (!deleteNoPendapatan || !currentBAData) return;

        const pendapatan = pendapatanData.find(p => p.no_pendapatan === deleteNoPendapatan);
        if (!pendapatan) {
            showAlert('Data tidak ditemukan', 'error');
            return;
        }

        $.ajax({
            url: window.routes.deletePendapatan.replace(':noPendapatan', deleteNoPendapatan),
            method: 'DELETE',
            data: {
                _token: window.csrfToken,
                id_project: currentBAData.id_project,
                norut: currentBAData.norut,
                no_ba: currentBAData.no_ba
            },
            success: function(response) {
                $('#deletePendapatanConfirmModal').modal('hide');
                if (response.success) {
                    showAlert('Pendapatan berhasil dihapus', 'success');
                    loadPendapatanData();
                } else {
                    showAlert(response.message || 'Gagal menghapus data', 'error');
                }
                deleteNoPendapatan = null;
            },
            error: function(xhr) {
                $('#deletePendapatanConfirmModal').modal('hide');
                showAlert('Terjadi kesalahan saat menghapus data', 'error');
                deleteNoPendapatan = null;
            }
        });
    }

    // Helper functions
    function formatCurrency(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatCurrencyNoDecimal(amount) {
        return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function formatNumberNoDecimal(num) {
        const intValue = Math.floor(parseFloat(num));
        return intValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }

    function showAlert(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' : 'alert-info';
        const icon = type === 'success' ? 'bx-check-circle' :
                     type === 'error' ? 'bx-error-circle' : 'bx-info-circle';

        const alertHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show alert-custom" role="alert">
                <i class="bx ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('.alert-custom').remove();

        if ($('.card').length) {
            $('.card').first().before(alertHTML);
        }

        if (type === 'success') {
            setTimeout(() => $('.alert-custom').fadeOut(), 5000);
        }

        $('html, body').animate({
            scrollTop: $('.alert-custom').offset().top - 100
        }, 300);
    }
});
