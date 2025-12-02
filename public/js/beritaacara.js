/**
 * Berita Acara Project JavaScript Module
 * Handles CRUD operations for Berita Acara tab in Progress Project
 */

$(document).ready(function() {
    let currentProjectData = null;
    let beritaAcaraData = [];
    let isReadOnly = false;
    let deleteNoBA = null;

    checkUserRole();

    // CRITICAL FIX: Cleanup HANYA modal BA, bukan semua modal
    $(document).on('hidden.bs.modal', '#baModal, #viewBAModal, #deleteBAConfirmModal', function() {
        console.log('BA modal closed - cleaning up:', this.id);

        // Hanya hapus backdrop yang terkait dengan modal BA ini
        $(this).next('.modal-backdrop').remove();

        // Reset body hanya jika tidak ada modal lain yang terbuka
        if ($('.modal.show').length === 0) {
            $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
            $('.modal-backdrop').remove(); // Cleanup any remaining backdrops
        }

        $(this).removeData('bs.modal');
    });

    // CRITICAL FIX: Tab cleanup lebih selektif
    $('#tab-ba').on('show.bs.tab', function() {
        console.log('Tab BA about to show - selective cleanup');

        // Hanya tutup modal Issue jika masih terbuka
        $('#issueModal, #deleteIssueConfirmModal').each(function() {
            if ($(this).hasClass('show')) {
                console.log('Closing Issue modal:', this.id);
                $(this).modal('hide');
            }
        });

        // Cleanup backdrop yang tersisa setelah tab switch
        setTimeout(function() {
            if ($('.modal.show').length === 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
            }
        }, 350);
    });

    // Load Berita Acara when tab is shown
    $('#tab-ba').on('shown.bs.tab', function() {
        console.log('Tab BA shown, currentProjectData:', currentProjectData);
        if (currentProjectData && currentProjectData.id_project && currentProjectData.norut) {
            console.log('Loading BA data because tab was shown');
            loadBeritaAcaraData();
        } else {
            console.warn('No currentProjectData available or incomplete:', currentProjectData);
        }
    });

    // Initialize function to be called from parent
    window.initBeritaAcara = function(projectData) {
        console.log('initBeritaAcara called with:', projectData);
        console.log('PROJECT DATA DETAILS:');
        console.log('- id_project:', projectData.id_project);
        console.log('- norut:', projectData.norut);
        console.log('- id_rab:', projectData.id_rab);
        currentProjectData = projectData;

        const baTabActive = $('#tab-ba').hasClass('active');
        console.log('BA tab active:', baTabActive);

        loadBeritaAcaraData();
    };

    function checkUserRole() {
        // Project Manager dan Super Admin dapat melakukan tambah, edit, hapus
        const allowedRoles = ['Super Admin', 'Project Manager'];
        isReadOnly = !allowedRoles.includes(window.userRole);
    }

    function loadBeritaAcaraData() {
        console.log('loadBeritaAcaraData called with currentProjectData:', currentProjectData);
        if (!currentProjectData || !currentProjectData.id_project || !currentProjectData.norut) {
            console.error('Missing project data:', currentProjectData);
            showAlert('Data proyek tidak lengkap', 'error');
            return;
        }

        console.log('Fetching BA data with:', {
            id_project: currentProjectData.id_project,
            norut: currentProjectData.norut
        });

        $.ajax({
            url: window.routes.getBeritaAcara,
            method: 'GET',
            data: {
                id_project: currentProjectData.id_project,
                norut: currentProjectData.norut
            },
            success: function(response) {
                console.log('BA data response:', response);
                if (response.success) {
                    beritaAcaraData = response.data;
                    console.log('BA data loaded, count:', beritaAcaraData.length);
                    renderBeritaAcaraTable();
                } else {
                    showAlert(response.message || 'Gagal memuat data', 'error');
                }
            },
            error: function(xhr) {
                console.error('Error loading berita acara:', xhr);
                showAlert('Terjadi kesalahan saat memuat data', 'error');
            }
        });
    }

    function renderBeritaAcaraTable() {
        console.log('renderBeritaAcaraTable called, data length:', beritaAcaraData ? beritaAcaraData.length : 0);
        const $container = $('#beritaAcaraTableContainer');
        const $headerControls = $('#baHeaderControls');

        $headerControls.show().css('display', 'flex');
        console.log('Header controls shown');

        if (!beritaAcaraData || beritaAcaraData.length === 0) {
            console.log('No BA data to display - showing empty message');
            $container.html(`
                <div class="text-center py-5">
                    <i class="bx bx-file-blank" style="font-size: 64px; color: #d9dee3;"></i>
                    <p class="text-muted mt-3 mb-1" style="font-size: 1rem; font-weight: 500;">Belum ada data Berita Acara</p>
                    <p class="text-muted" style="font-size: 0.875rem;">Klik tombol "Tambah Berita Acara" untuk membuat data baru</p>
                </div>
            `);
            attachEventHandlers();
            return;
        }

        console.log('Rendering table with', beritaAcaraData.length, 'rows');

        let tableHTML = `
            <div class="table-responsive beritaacara-table-container">
                <table class="table table-hover beritaacara-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Keterangan</th>
                            <th>Periode Berita Acara</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            ${!isReadOnly ? '<th>Aksi</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
        `;

        beritaAcaraData.forEach(function(ba, index) {
            const nilaiBA = ba.nilai_ba ? formatCurrency(ba.nilai_ba) : '-';
            const periodeM = ba.periode_mulai ? formatDate(ba.periode_mulai) : '';
            const periodeA = ba.periode_akhir ? formatDate(ba.periode_akhir) : '';
            const periodeBA = (periodeM && periodeA) ? `${periodeM} - ${periodeA}` : (periodeM || periodeA || '-');
            const statusBadge = getStatusBadge(ba.status);
            const noUrut = ba.norut_display || (index + 1);

            tableHTML += `
                <tr data-no-ba="${ba.no_ba}" data-norut="${ba.norut}" data-id-project="${ba.id_project}" class="editable-row"${!isReadOnly ? ` ondblclick="editBeritaAcara('${ba.no_ba}')" title="Double-click untuk edit" style="cursor: pointer;"` : ''}>
                    <td>
                        <span class="ba-no-urut" data-no-ba="${ba.no_ba}">
                            ${noUrut}
                        </span>
                    </td>
                    <td>
                        <div class="ba-desc-cell">
                            ${escapeHtml(ba.desc || '-')}
                        </div>
                    </td>
                    <td>${periodeBA}</td>
                    <td class="text-start"><small class="currency-display">${nilaiBA}</small></td>
                    <td onclick="event.stopPropagation();">
                        ${isReadOnly ? statusBadge : getStatusDropdown(ba)}
                    </td>
                    ${!isReadOnly ? `
                    <td onclick="event.stopPropagation();">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item btn-detail-ba" href="javascript:void(0);" data-no-ba="${ba.no_ba}">
                                        <i class="bx bx-show me-1"></i> Lihat Detail
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger btn-delete-ba" href="javascript:void(0);" data-no-ba="${ba.no_ba}">
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

    function getStatusBadge(status) {
        const statuses = {
            '01': { label: 'Draft', class: 'bg-secondary' },
            '02': { label: 'Review', class: 'bg-warning' },
            '03': { label: 'Approve', class: 'bg-success' },
            '04': { label: 'Pending', class: 'bg-info' }
        };

        const s = statuses[status] || statuses['01'];
        return `<span class="badge ${s.class}">${s.label}</span>`;
    }

    function getStatusDropdown(ba) {
        return `
            <select class="form-select form-select-sm status-dropdown" data-no-ba="${ba.no_ba}" style="width: auto;">
                <option value="01" ${ba.status === '01' ? 'selected' : ''}>Draft</option>
                <option value="02" ${ba.status === '02' ? 'selected' : ''}>Review</option>
                <option value="03" ${ba.status === '03' ? 'selected' : ''}>Approve</option>
                <option value="04" ${ba.status === '04' ? 'selected' : ''}>Pending</option>
            </select>
        `;
    }

    function attachEventHandlers() {
        console.log('attachEventHandlers called, isReadOnly:', isReadOnly);

        $('#btnAddBA').off('click').on('click', function() {
            console.log('Add BA button clicked');
            showBAModal();
        });

        $('.btn-detail-ba').off('click').on('click', function() {
            const noBA = $(this).data('no-ba');
            viewBeritaAcara(noBA);
        });

        $('.btn-delete-ba').off('click').on('click', function() {
            const noBA = $(this).data('no-ba');
            deleteNoBA = noBA;
            $('#deleteBAConfirmModal').modal('show');
        });

        $('#confirmDeleteBABtn').off('click').on('click', function() {
            performDeleteBA();
        });

        $('.status-dropdown').off('change').on('change', function() {
            const noBA = $(this).data('no-ba');
            const newStatus = $(this).val();
            updateStatus(noBA, newStatus);
        });
    }

    function viewBeritaAcara(noBA) {
        const ba = beritaAcaraData.find(b => b.no_ba === noBA);
        if (!ba) {
            showAlert('Data tidak ditemukan', 'error');
            return;
        }

        const nilaiBA = ba.nilai_ba ? formatCurrency(ba.nilai_ba) : '-';
        const periodeM = ba.periode_mulai ? formatDate(ba.periode_mulai) : '-';
        const periodeA = ba.periode_akhir ? formatDate(ba.periode_akhir) : '-';
        const statusLabel = getStatusLabel(ba.status);
        const statusBadge = getStatusBadge(ba.status);

        const modalHTML = `
            <div class="modal fade" id="viewBAModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Berita Acara</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="modal-info-section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="modal-info-section">
                                            <h6>Informasi Berita Acara</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td style="width: 200px;">No. BA:</td>
                                                    <td><strong>${ba.no_ba}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Keterangan:</td>
                                                    <td><strong>${escapeHtml(ba.desc || '-')}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Periode Mulai:</td>
                                                    <td><strong>${periodeM}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Periode Akhir:</td>
                                                    <td><strong>${periodeA}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Nilai BA:</td>
                                                    <td><strong>${nilaiBA}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Status:</td>
                                                    <td>${statusBadge}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#viewBAModal').remove();

        $('body').append(modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('viewBAModal'));
        modal.show();
    }

    function getStatusLabel(status) {
        const statuses = {
            '01': 'Draft',
            '02': 'Review',
            '03': 'Approve',
            '04': 'Pending'
        };
        return statuses[status] || 'Unknown';
    }

    function initializeBADatePickers() {
        const dateFields = ['ba_periode_mulai', 'ba_periode_akhir'];

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
                    const day = parseInt(parts[0]);
                    const month = parseInt(parts[1]);
                    const year = parseInt(parts[2]);

                    if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= 2100) {
                        const isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        $dateInput.val(isoDate);
                    }
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

    // CRITICAL FIX: Lebih reliable modal handling
    function showBAModal(ba = null, viewMode = false) {
        console.log('=== showBAModal START ===');
        console.log('showBAModal called with ba:', ba, 'viewMode:', viewMode);

        const isEdit = ba !== null && !viewMode;
        const modalTitle = viewMode ? 'Detail Berita Acara' : (isEdit ? 'Edit Berita Acara' : 'Tambah Berita Acara');

        // Tutup modal Issue yang mungkin masih terbuka
        $('#issueModal').each(function() {
            if ($(this).hasClass('show')) {
                console.log('Closing Issue modal before showing BA modal');
                $(this).modal('hide');
            }
        });

        // Wait untuk cleanup Issue selesai
        setTimeout(function() {
            console.log('Opening BA modal...');

            let periodeM_display = '';
            let periodeA_display = '';
            let periodeM_value = '';
            let periodeA_value = '';

            if (ba) {
                if (ba.periode_mulai) {
                    periodeM_value = ba.periode_mulai;
                    const dateM = new Date(ba.periode_mulai);
                    const dayM = String(dateM.getDate()).padStart(2, '0');
                    const monthM = String(dateM.getMonth() + 1).padStart(2, '0');
                    const yearM = dateM.getFullYear();
                    periodeM_display = `${dayM}/${monthM}/${yearM}`;
                }
                if (ba.periode_akhir) {
                    periodeA_value = ba.periode_akhir;
                    const dateA = new Date(ba.periode_akhir);
                    const dayA = String(dateA.getDate()).padStart(2, '0');
                    const monthA = String(dateA.getMonth() + 1).padStart(2, '0');
                    const yearA = dateA.getFullYear();
                    periodeA_display = `${dayA}/${monthA}/${yearA}`;
                }
            }

            const modalHTML = `
                <div class="modal fade header-rab-modal" id="baModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${modalTitle}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="baForm">
                                    <input type="hidden" id="ba_no_ba" value="${ba ? ba.no_ba : ''}">

                                    <div class="form-section">
                                        <h6 class="mb-3"><i class="bx bx-file-blank me-2"></i>Informasi Berita Acara</h6>

                                        <div class="mb-3">
                                            <label for="ba_desc" class="form-label">Deskripsi / Keterangan <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="ba_desc" rows="5" required style="resize: vertical;"${viewMode ? ' readonly' : ''}>${ba ? escapeHtml(ba.desc || '') : ''}</textarea>
                                            ${!viewMode ? '<small class="text-muted">Masukkan milestone atau nomor berita acara</small>' : ''}
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h6 class="mb-3"><i class="bx bx-calendar me-2"></i>Periode & Nilai</h6>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="ba_periode_mulai" class="form-label">Periode Mulai</label>
                                                ${viewMode ?
                                                    `<input type="text" class="form-control readonly-field" value="${periodeM_display}" readonly>` :
                                                    `<div class="input-group date-input-group">
                                                        <input type="text" class="form-control" id="ba_periode_mulai" placeholder="dd/mm/yyyy" maxlength="10" value="${periodeM_display}">
                                                        <input type="date" class="date-picker-hidden" id="ba_periode_mulai_date" value="${periodeM_value}">
                                                        <button type="button" class="btn btn-outline-secondary date-picker-btn" data-field="ba_periode_mulai" tabindex="-1" title="Pilih tanggal">
                                                            <i class="bx bx-calendar"></i>
                                                        </button>
                                                    </div>`
                                                }
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="ba_periode_akhir" class="form-label">Periode Akhir</label>
                                                ${viewMode ?
                                                    `<input type="text" class="form-control readonly-field" value="${periodeA_display}" readonly>` :
                                                    `<div class="input-group date-input-group">
                                                        <input type="text" class="form-control" id="ba_periode_akhir" placeholder="dd/mm/yyyy" maxlength="10" value="${periodeA_display}">
                                                        <input type="date" class="date-picker-hidden" id="ba_periode_akhir_date" value="${periodeA_value}">
                                                        <button type="button" class="btn btn-outline-secondary date-picker-btn" data-field="ba_periode_akhir" tabindex="-1" title="Pilih tanggal">
                                                            <i class="bx bx-calendar"></i>
                                                        </button>
                                                    </div>`
                                                }
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="ba_nilai_ba" class="form-label">Nilai BA</label>
                                                <input type="text" class="form-control ${viewMode ? 'readonly-field' : ''}" id="ba_nilai_ba" value="${ba && ba.nilai_ba ? formatNumberNoDecimal(ba.nilai_ba) : ''}" placeholder="0"${viewMode ? ' readonly' : ''}>
                                                ${!viewMode ? '<small class="text-muted">Format: #.###.###.###</small>' : ''}
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="ba_status" class="form-label">Status</label>
                                                <select class="form-select ${viewMode ? 'readonly-field' : ''}" id="ba_status"${viewMode ? ' disabled' : ''}>
                                                    <option value="01" ${ba && ba.status === '01' ? 'selected' : ''}>Draft</option>
                                                    <option value="02" ${ba && ba.status === '02' ? 'selected' : ''}>Review</option>
                                                    <option value="03" ${ba && ba.status === '03' ? 'selected' : ''}>Approve</option>
                                                    <option value="04" ${ba && ba.status === '04' ? 'selected' : ''}>Pending</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i> ${viewMode ? 'Tutup' : 'Batal'}
                                </button>
                                ${!viewMode ? `<button type="button" class="btn btn-primary" id="btnSaveBA">
                                    <i class="bx bx-check me-1"></i> ${isEdit ? 'Update' : 'Simpan'}
                                </button>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Check if modal already exists
            if ($('#baModal').length > 0) {
                console.log('BA Modal already exists, closing first...');
                $('#baModal').modal('hide');
                $('#baModal').remove();
            }

            // Remove any leftover backdrops
            $('.modal-backdrop').remove();

            console.log('Modal cleanup complete. Creating new BA modal...');

            $('body').append(modalHTML);
            console.log('BA Modal HTML appended. Modal count now:', $('#baModal').length);

            const modal = new bootstrap.Modal(document.getElementById('baModal'));

            $('#baModal').on('shown.bs.modal', function() {
                console.log('BA Modal fully shown. ViewMode:', viewMode);

                if (!viewMode) {
                    console.log('Setting up edit/create mode handlers');

                    const textarea = document.getElementById('ba_desc');
                    const nilaiInput = document.getElementById('ba_nilai_ba');
                    const statusSelect = document.getElementById('ba_status');

                    console.log('Form field status check:');
                    console.log('- Textarea readonly:', textarea.readOnly, 'disabled:', textarea.disabled);
                    console.log('- Nilai input readonly:', nilaiInput.readOnly, 'disabled:', nilaiInput.disabled);
                    console.log('- Status select disabled:', statusSelect.disabled);

                    textarea.readOnly = false;
                    textarea.disabled = false;
                    nilaiInput.readOnly = false;
                    nilaiInput.disabled = false;
                    statusSelect.disabled = false;

                    console.log('All fields enabled for editing');

                    initializeBADatePickers();

                    $('#ba_nilai_ba').on('keyup', function() {
                        let val = $(this).val().replace(/\D/g, '');
                        if (val) {
                            $(this).val(formatNumberNoDecimal(val));
                        }
                    });

                    $('#btnSaveBA').off('click').on('click', function() {
                        console.log('Save button clicked, isEdit:', isEdit);
                        saveBA(isEdit);
                    });

                    setTimeout(() => {
                        textarea.focus();
                    }, 100);
                } else {
                    console.log('View mode - no handlers needed');
                }
            });

            modal.show();
        }, 400); // Wait 400ms untuk Issue modal cleanup selesai
    }

    function saveBA(isEdit) {
        console.log('saveBA called with isEdit:', isEdit);
        const desc = $('#ba_desc').val();
        const periodeM_display = $('#ba_periode_mulai').val();
        const periodeA_display = $('#ba_periode_akhir').val();

        const nilaiBAraw = $('#ba_nilai_ba').val();
        console.log('Nilai BA RAW dari input:', nilaiBAraw);

        const nilaiBA = nilaiBAraw.replace(/\D/g, '');
        console.log('Nilai BA setelah dibersihkan:', nilaiBA);

        const status = $('#ba_status').val();

        if (!desc) {
            showAlert('Deskripsi harus diisi', 'error');
            return;
        }

        let periodeM = '';
        let periodeA = '';

        if (periodeM_display && periodeM_display.length === 10) {
            const partsM = periodeM_display.split('/');
            periodeM = `${partsM[2]}-${partsM[1]}-${partsM[0]}`;
        }

        if (periodeA_display && periodeA_display.length === 10) {
            const partsA = periodeA_display.split('/');
            periodeA = `${partsA[2]}-${partsA[1]}-${partsA[0]}`;
        }

        const data = {
            _token: window.csrfToken,
            id_project: currentProjectData.id_project,
            norut: parseInt(currentProjectData.norut),
            desc: desc,
            periode_mulai: periodeM || null,
            periode_akhir: periodeA || null,
            nilai_ba: nilaiBA || null,
            status: status
        };

        const url = isEdit ?
            window.routes.updateBeritaAcara.replace('{noBA}', $('#ba_no_ba').val()) :
            window.routes.storeBeritaAcara;

        const method = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#baModal').modal('hide');
                    loadBeritaAcaraData();
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

    function updateStatus(noBA, newStatus) {
        $.ajax({
            url: window.routes.updateStatusBeritaAcara,
            method: 'POST',
            data: {
                _token: window.csrfToken,
                no_ba: noBA,
                id_project: currentProjectData.id_project,
                norut: parseInt(currentProjectData.norut),
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Status berhasil diperbarui', 'success');
                    loadBeritaAcaraData();
                } else {
                    showAlert(response.message || 'Gagal memperbarui status', 'error');
                }
            },
            error: function(xhr) {
                showAlert('Terjadi kesalahan saat memperbarui status', 'error');
                loadBeritaAcaraData();
            }
        });
    }

    function performDeleteBA() {
        if (!deleteNoBA) {
            return;
        }

        $.ajax({
            url: window.routes.deleteBeritaAcara.replace('{noBA}', deleteNoBA),
            method: 'DELETE',
            data: {
                _token: window.csrfToken,
                id_project: currentProjectData.id_project,
                norut: parseInt(currentProjectData.norut)
            },
            success: function(response) {
                $('#deleteBAConfirmModal').modal('hide');
                if (response.success) {
                    showAlert('Berita Acara berhasil dihapus', 'success');
                    loadBeritaAcaraData();
                } else {
                    showAlert(response.message || 'Gagal menghapus data', 'error');
                }
                deleteNoBA = null;
            },
            error: function(xhr) {
                $('#deleteBAConfirmModal').modal('hide');
                showAlert('Terjadi kesalahan saat menghapus data', 'error');
                deleteNoBA = null;
            }
        });
    }

    window.editBeritaAcara = function(noBA) {
        console.log('editBeritaAcara called with noBA:', noBA, 'isReadOnly:', isReadOnly);
        if (isReadOnly) {
            console.log('User is readonly, edit blocked');
            return;
        }
        const ba = beritaAcaraData.find(b => b.no_ba === noBA);
        console.log('Found BA data for edit:', ba);
        if (ba) {
            console.log('Calling showBAModal for edit mode...');
            showBAModal(ba);
        } else {
            console.error('BA data not found for noBA:', noBA);
        }
    };

    function formatCurrency(amount) {
        if (!amount) return '<div class="text-center text-muted">-</div>';
        const number = parseFloat(amount);
        if (isNaN(number) || number === 0) return '<div class="text-center text-muted">-</div>';
        const formattedNumber = number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        return `<div class="d-flex justify-content-between align-items-center" style="gap: 0.5rem;"><span>Rp</span><span>${formattedNumber}</span></div>`;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
