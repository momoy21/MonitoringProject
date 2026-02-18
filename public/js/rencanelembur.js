/**
 * Rencana Lembur (Kuota Lembur) JavaScript
 * Handles Cost Center dropdown, data table, CRUD modals, upload, and download
 */

/**
 * SweetAlert2 wrapper that renders in front of Bootstrap modals
 */
function swalFront(title, text, icon) {
    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        customClass: { container: 'swal-on-top' }
    });
}

// Global state
let currentCostCenter = null;
let currentNamaProject = '';
let currentDokIo = '';
let currentPage = 1;
let currentPerPage = 10;
let currentSearch = '';
let deleteTargetId = null;

$(document).ready(function () {
    initCostCenterSelect();
    initNikSelect();
    bindEvents();
});

/**
 * Initialize Select2 for Cost Center dropdown
 */
function initCostCenterSelect() {
    $('#cost_center_select').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Cost Centre --',
        allowClear: true,
        ajax: {
            url: window.routes.getCostCenter,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { search: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return { id: item.id, text: item.text, ...item };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    $('#cost_center_select').on('change', function () {
        const data = $(this).select2('data')[0];
        if (data && data.id) {
            currentCostCenter = data.cost_center || data.id;
            currentNamaProject = data.namaproject || '-';
            currentDokIo = data.dokumen_io || '-';
            $('#info_namaproject').val(currentNamaProject);
            $('#searchBarSection').show();
            currentPage = 1;
            loadData();
        } else {
            currentCostCenter = null;
            currentNamaProject = '';
            currentDokIo = '';
            $('#info_namaproject').val('');
            $('#searchBarSection').hide();
            $('#searchInput').val('');
            currentSearch = '';
            showEmptyState();
        }
    });
}

/**
 * Initialize Select2 for NIK dropdown in modal
 */
function initNikSelect() {
    $('#form_nik').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih NIK --',
        allowClear: true,
        dropdownParent: $('#kuotaLemburModal'),
        ajax: {
            url: window.routes.getKaryawan,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { search: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return { id: item.id, text: item.text, ...item };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // When NIK changes, auto-calculate bulan
    $('#form_nik').on('change', function () {
        const nik = $(this).val();
        if (nik && currentCostCenter) {
            $.get(window.routes.getNextBulan, {
                cost_center: currentCostCenter,
                nik: nik
            }, function (response) {
                $('#form_bulan').val(response.bulan);
            });
        } else {
            $('#form_bulan').val('');
        }
    });
}

/**
 * Bind all event handlers
 */
function bindEvents() {
    // Per page change
    $('#perPageSelect').on('change', function () {
        currentPerPage = parseInt($(this).val());
        currentPage = 1;
        loadData();
    });

    // Search
    let searchTimer;
    $('#searchInput').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            currentSearch = $('#searchInput').val();
            currentPage = 1;
            loadData();
        }, 400);
    });

    // Tambah button
    $('#btnTambah').on('click', function () {
        if (!currentCostCenter) {
            Swal.fire('Peringatan', 'Pilih Cost Centre terlebih dahulu', 'warning');
            return;
        }
        openAddModal();
    });

    // Simpan button
    $('#btnSimpan').on('click', function () {
        saveData();
    });

    // Upload button
    $('#btnUpload').on('click', function () {
        $('#uploadFile').val('');
        $('#uploadModal').modal('show');
    });

    // Do upload
    $('#btnDoUpload').on('click', function () {
        doUpload();
    });

    // Download template
    $('#btnDownloadTemplate, #btnDownloadTemplateModal').on('click', function () {
        window.location.href = window.routes.downloadTemplate;
    });

    // Delete confirm
    $('#confirmDeleteBtn').on('click', function () {
        doDelete();
    });

    // Pagination
    $('#firstPageBtn').on('click', function () { currentPage = 1; loadData(); });
    $('#prevPageBtn').on('click', function () { if (currentPage > 1) { currentPage--; loadData(); } });
    $('#nextPageBtn').on('click', function () { currentPage++; loadData(); });
    $('#lastPageBtn').on('click', function () { /* set by loadData */ });
}

/**
 * Load data from server
 */
function loadData() {
    if (!currentCostCenter) {
        showEmptyState();
        return;
    }

    showLoadingState();

    $.ajax({
        url: window.routes.getData,
        method: 'GET',
        data: {
            cost_center: currentCostCenter,
            search: currentSearch,
            per_page: currentPerPage,
            page: currentPage,
        },
        success: function (response) {
            if (response.success) {
                renderTable(response.data);
                renderPagination(response.pagination);
            } else {
                showErrorState(response.message || 'Gagal memuat data');
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat data';
            showErrorState(msg);
        }
    });
}

/**
 * Render table rows
 */
function renderTable(data) {
    const $tbody = $('#kuotaLemburTableBody');
    $tbody.empty();

    if (!data || data.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="10" class="text-center py-4">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mb-0 text-muted">Tidak ada data kuota lembur</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }

    data.forEach(function (item) {
        const periodeAwal = item.periode_awal ? formatDate(item.periode_awal) : '-';
        const periodeAkhir = item.periode_akhir ? formatDate(item.periode_akhir) : '-';
        const statusBadge = item.status === 'F'
            ? '<span class="badge bg-success px-3">Sudah Terkirim</span>'
            : '<span class="badge bg-warning px-3">Belum Terkirim</span>';

        const row = `
            <tr class="editable-row" ondblclick="editKuotaLembur(${item.id})" title="Double-click untuk edit" style="cursor: pointer;"
                data-id="${item.id}"
                data-nik="${escapeAttr(item.nik)}"
                data-nama="${escapeAttr(item.nama_karyawan || '')}"
                data-bulan="${item.bulan}"
                data-periode-awal="${item.periode_awal || ''}"
                data-periode-akhir="${item.periode_akhir || ''}"
                data-jml-wd="${item.jml_wd}"
                data-jml-we="${item.jml_we}"
                data-jml-hn="${item.jml_hn}"
                data-status="${item.status}">
                <td class="fw-semibold text-primary">${escapeHtml(item.nik)}</td>
                <td>${escapeHtml(item.nama_karyawan || '-')}</td>
                <td class="text-center">${item.bulan}</td>
                <td>${periodeAwal}</td>
                <td>${periodeAkhir}</td>
                <td class="text-center">${item.jml_wd ?? 0}</td>
                <td class="text-center">${item.jml_we ?? 0}</td>
                <td class="text-center">${item.jml_hn ?? 0}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-end px-4">
                    <div class="dropdown position-static">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item py-2" href="javascript:void(0);" onclick="editKuotaLembur(${item.id})">
                                    <i class="bx bx-edit me-2 text-warning"></i> Edit
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="javascript:void(0);" onclick="deleteKuotaLembur(${item.id})">
                                    <i class="bx bx-trash me-2"></i> Hapus
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        `;
        $tbody.append(row);
    });
}

/**
 * Render pagination controls
 */
function renderPagination(pagination) {
    if (!pagination || pagination.total === 0) {
        $('#paginationControls').hide();
        return;
    }

    $('#paginationControls').show().css('display', '');

    $('#entriesFrom').text(pagination.from);
    $('#entriesTo').text(pagination.to);
    $('#entriesTotal').text(pagination.total);

    const totalPages = pagination.last_page;
    currentPage = pagination.current_page;

    // Update button states
    $('#firstPageBtn, #prevPageBtn').prop('disabled', currentPage <= 1);
    $('#nextPageBtn, #lastPageBtn').prop('disabled', currentPage >= totalPages);

    // Last page button
    $('#lastPageBtn').off('click').on('click', function () {
        currentPage = totalPages;
        loadData();
    });

    // Render page numbers
    const $container = $('#pageNumbersContainer');
    $container.empty();

    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const btnClass = i === currentPage ? 'btn btn-primary btn-sm px-3 fw-bold' : 'btn btn-outline-secondary btn-sm';
        const $btn = $(`<button type="button" class="${btnClass}">${i}</button>`);
        if (i !== currentPage) {
            $btn.on('click', function () {
                currentPage = i;
                loadData();
            });
        }
        $container.append($btn);
    }
}

/**
 * Open Add modal
 */
function openAddModal() {
    $('#form_mode').val('add');
    $('#form_id').val('');
    $('#modalTitle').text('Tambah Kuota Lembur');

    // Set Cost Center info
    $('#form_cost_center').val(currentCostCenter);
    $('#form_dok_io').val(currentDokIo);
    $('#form_namaproject').val(currentNamaProject);

    // Enable NIK dropdown
    $('#form_nik').prop('disabled', false).val(null).trigger('change');
    $('#form_bulan').val('');
    $('#form_periode_awal').val('');
    $('#form_periode_akhir').val('');
    $('#form_jml_wd').val(0);
    $('#form_jml_we').val(0);
    $('#form_jml_hn').val(0);

    $('#kuotaLemburModal').modal('show');
}

/**
 * Open Edit modal
 */
function editKuotaLembur(id) {
    const $row = $(`tr[data-id="${id}"]`);
    if (!$row.length) return;

    $('#form_mode').val('edit');
    $('#form_id').val(id);
    $('#modalTitle').text('Edit Kuota Lembur');

    // Set Cost Center info (readonly)
    $('#form_cost_center').val(currentCostCenter);
    $('#form_dok_io').val(currentDokIo);
    $('#form_namaproject').val(currentNamaProject);

    // Set NIK (disabled for edit)
    const nik = $row.data('nik');
    const nama = $row.data('nama');
    const nikOption = new Option(`${nik} - ${nama}`, nik, true, true);
    $('#form_nik').empty().append(nikOption).trigger('change');
    $('#form_nik').prop('disabled', true);

    // Set Bulan Ke (readonly)
    $('#form_bulan').val($row.data('bulan'));

    // Set editable fields
    const periodeAwal = $row.data('periode-awal');
    const periodeAkhir = $row.data('periode-akhir');
    $('#form_periode_awal').val(periodeAwal ? periodeAwal.substring(0, 10) : '');
    $('#form_periode_akhir').val(periodeAkhir ? periodeAkhir.substring(0, 10) : '');
    $('#form_jml_wd').val($row.data('jml-wd') || 0);
    $('#form_jml_we').val($row.data('jml-we') || 0);
    $('#form_jml_hn').val($row.data('jml-hn') || 0);

    $('#kuotaLemburModal').modal('show');
}

/**
 * Save data (add or update)
 */
function saveData() {
    const mode = $('#form_mode').val();
    const costCenter = $('#form_cost_center').val();
    const nik = $('#form_nik').val();
    const periodeAwal = $('#form_periode_awal').val();
    const periodeAkhir = $('#form_periode_akhir').val();
    const jmlWD = parseInt($('#form_jml_wd').val()) || 0;
    const jmlWE = parseInt($('#form_jml_we').val()) || 0;
    const jmlHN = parseInt($('#form_jml_hn').val()) || 0;

    // Validation
    if (!costCenter || !nik || !periodeAwal || !periodeAkhir) {
        swalFront('Peringatan', 'Data wajib belum lengkap', 'warning');
        return;
    }

    if (new Date(periodeAwal) > new Date(periodeAkhir)) {
        swalFront('Peringatan', 'Periode tidak valid', 'warning');
        return;
    }

    if (jmlWD < 0 || jmlWE < 0 || jmlHN < 0) {
        swalFront('Peringatan', 'Jumlah lembur tidak valid', 'warning');
        return;
    }

    // Show loading
    $('#simpanSpinner').removeClass('d-none');
    $('#simpanIcon').addClass('d-none');
    $('#simpanText').text('Menyimpan...');
    $('#btnSimpan').prop('disabled', true);

    const data = {
        _token: window.csrfToken,
        cost_center: costCenter,
        nik: nik,
        periode_awal: periodeAwal,
        periode_akhir: periodeAkhir,
        jml_wd: jmlWD,
        jml_we: jmlWE,
        jml_hn: jmlHN,
    };

    let url, method;
    if (mode === 'edit') {
        url = window.routes.update + '/' + $('#form_id').val();
        method = 'PUT';
        data._method = 'PUT';
    } else {
        url = window.routes.store;
        method = 'POST';
    }

    $.ajax({
        url: url,
        method: 'POST', // Always POST, use _method for PUT
        data: data,
        success: function (response) {
            if (response.success) {
                $('#kuotaLemburModal').modal('hide');
                Swal.fire('Berhasil', response.message || 'Data tersimpan di database tabel kuotalembur', 'success');
                loadData();
            } else {
                Swal.fire('Gagal', response.message || 'Gagal menyimpan data', 'error');
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data';
            Swal.fire('Gagal', msg, 'error');
        },
        complete: function () {
            $('#simpanSpinner').addClass('d-none');
            $('#simpanIcon').removeClass('d-none');
            $('#simpanText').text('Simpan');
            $('#btnSimpan').prop('disabled', false);
        }
    });
}

/**
 * Delete kuota lembur
 */
function deleteKuotaLembur(id) {
    deleteTargetId = id;
    $('#deleteConfirmModal').modal('show');
}

function doDelete() {
    if (!deleteTargetId) return;

    $.ajax({
        url: window.routes.destroy + '/' + deleteTargetId,
        method: 'POST',
        data: {
            _token: window.csrfToken,
            _method: 'DELETE'
        },
        success: function (response) {
            $('#deleteConfirmModal').modal('hide');
            if (response.success) {
                Swal.fire('Berhasil', response.message || 'Data berhasil dihapus', 'success');
                loadData();
            } else {
                Swal.fire('Gagal', response.message || 'Gagal menghapus data', 'error');
            }
        },
        error: function (xhr) {
            $('#deleteConfirmModal').modal('hide');
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data';
            Swal.fire('Gagal', msg, 'error');
        }
    });
}

/**
 * Upload Excel file
 */
function doUpload() {
    const fileInput = document.getElementById('uploadFile');
    if (!fileInput.files || fileInput.files.length === 0) {
        swalFront('Peringatan', 'Pilih file terlebih dahulu', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('_token', window.csrfToken);

    // Show loading
    $('#uploadSpinner').removeClass('d-none');
    $('#btnDoUpload').prop('disabled', true);

    $.ajax({
        url: window.routes.upload,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#uploadModal').modal('hide');
            if (response.success) {
                let msg = response.message;
                if (response.errors && response.errors.length > 0) {
                    msg += '\n\nDetail error:\n' + response.errors.join('\n');
                }
                Swal.fire('Berhasil', msg, 'success');
                loadData();
            } else {
                Swal.fire('Gagal', response.message || 'Gagal mengimpor data', 'error');
            }
        },
        error: function (xhr) {
            $('#uploadModal').modal('hide');
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengimpor data';
            Swal.fire('Gagal', msg, 'error');
        },
        complete: function () {
            $('#uploadSpinner').addClass('d-none');
            $('#btnDoUpload').prop('disabled', false);
        }
    });
}

/**
 * Show loading state in table
 */
function showLoadingState() {
    $('#kuotaLemburTableBody').html(`
        <tr>
            <td colspan="10" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0 mt-2 text-muted">Memuat data...</p>
            </td>
        </tr>
    `);
}

/**
 * Show empty state
 */
function showEmptyState() {
    $('#kuotaLemburTableBody').html(`
        <tr>
            <td colspan="10" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>
                    <p class="mb-0 text-muted">Pilih Cost Centre untuk melihat data</p>
                </div>
            </td>
        </tr>
    `);
    $('#paginationControls').hide();
}

/**
 * Show error state
 */
function showErrorState(message) {
    $('#kuotaLemburTableBody').html(`
        <tr>
            <td colspan="10" class="text-center py-4 text-danger">
                <i class="bx bx-error-circle" style="font-size: 24px;"></i>
                <p class="mb-0 mt-2">${escapeHtml(message)}</p>
            </td>
        </tr>
    `);
}

/**
 * Format date from YYYY-MM-DD to DD/MM/YYYY
 */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Escape for HTML attributes
 */
function escapeAttr(text) {
    if (!text) return '';
    return String(text).replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
