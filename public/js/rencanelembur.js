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
    initSearchSelect();
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
            $('#searchInput').val(null).trigger('change');
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

    // When NIK changes, auto-calculate bulan (skip during edit mode)
    $('#form_nik').on('change', function () {
        if ($('#form_mode').val() === 'edit') return;
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
 * Initialize live search input with debounce
 */
let searchDebounceTimer = null;
function initSearchSelect() {
    const $input = $('#searchInput');
    const $clearBtn = $('#btnClearSearch');
    const $hint = $('#searchHint');

    // Debounced input handler (300ms)
    $input.on('input', function () {
        const val = $(this).val().trim();
        $clearBtn.toggle(val.length > 0);
        $hint.toggle(val.length > 0);

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () {
            currentSearch = val;
            currentPage = 1;
            loadData();
        }, 300);
    });

    // Enter key triggers immediately
    $input.on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchDebounceTimer);
            currentSearch = $(this).val().trim();
            currentPage = 1;
            loadData();
        }
    });

    // Clear button
    $clearBtn.on('click', function () {
        $input.val('').focus();
        $clearBtn.hide();
        $hint.hide();
        clearTimeout(searchDebounceTimer);
        currentSearch = '';
        currentPage = 1;
        loadData();
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
    // Handled by Select2 change event

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
                <td colspan="9" class="text-center py-4">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>
                        <p class="mb-0 text-muted">Tidak ada data kuota lembur</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }

    // Calculate starting row number based on pagination
    const startNo = ((currentPage - 1) * currentPerPage) + 1;

    data.forEach(function (item, index) {
        const periodeAwal = item.periode_awal ? formatDate(item.periode_awal) : '-';
        const periodeAkhir = item.periode_akhir ? formatDate(item.periode_akhir) : '-';

        // Parse decimals properly
        const jmlWd = parseFloat(item.jml_wd) || 0;
        const jmlWe = parseFloat(item.jml_we) || 0;
        const jmlHn = parseFloat(item.jml_hn) || 0;

        const row = `
            <tr class="editable-row" ondblclick="editKuotaLembur('${item.cost_center}-${item.nik}-${item.bulan}')" title="Double-click untuk edit"
                data-id="${item.cost_center}-${item.nik}-${item.bulan}"
                data-cost-center="${item.cost_center}"
                data-nik="${escapeAttr(item.nik)}"
                data-nama="${escapeAttr(item.nama_karyawan || '')}"
                data-bulan="${item.bulan}"
                data-periode-awal="${item.periode_awal || ''}"
                data-periode-akhir="${item.periode_akhir || ''}"
                data-jml-wd="${jmlWd}"
                data-jml-we="${jmlWe}"
                data-jml-hn="${jmlHn}"
                data-status="${item.status}">
                <td class="text-center">${startNo + index}</td>
                <td class="fw-semibold text-primary">${highlightMatch(escapeHtml(item.nik))}</td>
                <td>${highlightMatch(escapeHtml(item.nama_karyawan || '-'))}</td>
                <td class="text-center">${periodeAwal}</td>
                <td class="text-center">${periodeAkhir}</td>
                <td class="text-center">${formatDecimal(jmlWd)}</td>
                <td class="text-center">${formatDecimal(jmlWe)}</td>
                <td class="text-center">${formatDecimal(jmlHn)}</td>
                <td class="text-center">
                    <div class="dropdown position-static">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item py-2" href="javascript:void(0);" onclick="editKuotaLembur('${item.cost_center}-${item.nik}-${item.bulan}')">
                                    <i class="bx bx-edit me-2 text-warning"></i> Edit
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="javascript:void(0);" onclick="deleteKuotaLembur('${item.cost_center}-${item.nik}-${item.bulan}')">
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
    const parts = id.split('-');
    if (parts.length !== 3) return;

    const costCenter = parts[0];
    const nik = parts[1];
    const bulan = parseInt(parts[2]);

    const $row = $(`tr[data-id="${id}"]`);
    if (!$row.length) return;

    $('#form_mode').val('edit');
    $('#form_id').val(id); // Keep for compatibility, but we'll use parts
    $('#modalTitle').text('Edit Kuota Lembur');

    // Set Cost Center info (readonly)
    $('#form_cost_center').val(currentCostCenter);
    $('#form_dok_io').val(currentDokIo);
    $('#form_namaproject').val(currentNamaProject);

    // Set NIK (disabled for edit)
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
    const jmlWD = parseFloat($('#form_jml_wd').val()) || 0;
    const jmlWE = parseFloat($('#form_jml_we').val()) || 0;
    const jmlHN = parseFloat($('#form_jml_hn').val()) || 0;

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
        const id = $('#form_id').val();
        const parts = id.split('-');
        data.cost_center = parts[0];
        data.nik = parts[1];
        data.bulan = parseInt(parts[2]);
        url = window.routes.update;
        method = 'POST';
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
                currentPage = 1; // Reset ke halaman 1 agar data baru terlihat di atas
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

    const parts = deleteTargetId.split('-');
    if (parts.length !== 3) return;

    $.ajax({
        url: window.routes.destroy,
        method: 'POST',
        data: {
            _token: window.csrfToken,
            _method: 'DELETE',
            cost_center: parts[0],
            nik: parts[1],
            bulan: parseInt(parts[2])
        },
        success: function (response) {
            $('#deleteConfirmModal').modal('hide');
            if (response.success) {
                Swal.fire('Berhasil', response.message || 'Data berhasil dihapus', 'success');
                currentPage = 1;
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
                if (response.has_errors && response.errors && response.errors.length > 0) {
                    // Partial success — some rows failed
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sebagian Data Gagal',
                        html: '<p>' + escapeHtml(response.message) + '</p>' +
                              '<div style="text-align:left;max-height:200px;overflow-y:auto;font-size:13px;background:#fff3cd;padding:10px;border-radius:4px;margin-top:8px;">' +
                              response.errors.map(function(e){ return '<div style="margin-bottom:4px;">&#x26A0; ' + escapeHtml(e) + '</div>'; }).join('') +
                              '</div>',
                        customClass: { container: 'swal-on-top' }
                    });
                } else {
                    // All rows imported successfully
                    Swal.fire('Berhasil', response.message, 'success');
                }
                currentPage = 1;
                loadData();
            } else {
                Swal.fire('Gagal', response.message || 'Gagal mengimpor data', 'error');
            }
        },
        error: function (xhr) {
            $('#uploadModal').modal('hide');
            const data = xhr.responseJSON;
            if (data && data.errors && data.errors.length > 0) {
                // All rows failed — show detailed errors
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Gagal',
                    html: '<p>' + escapeHtml(data.message || 'Semua data gagal diimpor') + '</p>' +
                          '<div style="text-align:left;max-height:200px;overflow-y:auto;font-size:13px;background:#f8d7da;padding:10px;border-radius:4px;margin-top:8px;">' +
                          data.errors.map(function(e){ return '<div style="margin-bottom:4px;">&#x2716; ' + escapeHtml(e) + '</div>'; }).join('') +
                          '</div>',
                    customClass: { container: 'swal-on-top' }
                });
            } else {
                const msg = data?.message || 'Terjadi kesalahan saat mengimpor data';
                Swal.fire('Gagal', msg, 'error');
            }
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
            <td colspan="9" class="text-center py-4">
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
            <td colspan="9" class="text-center py-4">
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
            <td colspan="9" class="text-center py-4 text-danger">
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

/**
 * Highlight matching text based on current search term (wildcard, per word).
 */
function highlightMatch(text) {
    if (!currentSearch || !text) return text;
    const term = currentSearch.trim();
    if (!term) return text;
    const words = term.split(/\s+/);
    let result = text;
    words.forEach(function (word) {
        if (!word) return;
        const escaped = word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp('(' + escaped + ')', 'gi');
        result = result.replace(regex, '<span class="search-highlight">$1</span>');
    });
    return result;
}

/**
 * Format decimal value: show decimals only when needed (e.g. 5 → "5", 5.1 → "5.1")
 */
function formatDecimal(val) {
    const num = parseFloat(val);
    if (isNaN(num)) return '0';
    if (num % 1 === 0) return num.toString();
    return num.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
}
