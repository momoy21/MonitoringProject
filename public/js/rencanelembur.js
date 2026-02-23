/**
 * Rencana Lembur (Kuota Lembur) JavaScript — Optimized
 * - Pre-loads all dropdown data in one call (no per-keystroke AJAX)
 * - Client-side filtering for dropdowns
 * - Optimistic UI updates after delete (no full reload)
 * - Request deduplication & abort for stale searches
 * - Minimal DOM manipulation with DocumentFragment
 */

/* ───────── SweetAlert2 helper ───────── */
function swalFront(title, text, icon) {
    return Swal.fire({ title, text, icon, customClass: { container: 'swal-on-top' } });
}

/* ───────── Global state ───────── */
let currentCostCenter = null;
let currentNamaProject = '';
let currentDokIo = '';
let currentPage = 1;
let currentPerPage = 10;
let currentSearch = '';
let deleteTargetId = null;

// Caches (populated once on page load)
let cachedCostCenters = [];
let cachedKaryawan = [];
let lastLoadedData = []; // keep last loaded rows for optimistic updates

// Abort controller for stale AJAX
let activeDataXHR = null;

/* ───────── Bootstrap ───────── */
$(document).ready(function () {
    bindEvents();
    prefetchInitialData();
});

/**
 * Pre-fetch cost centres & karyawan in a SINGLE call, then init dropdowns locally
 */
function prefetchInitialData() {
    $.ajax({
        url: window.routes.getInitialData,
        method: 'GET',
        dataType: 'json',
        success: function (resp) {
            cachedCostCenters = resp.cost_centers || [];
            cachedKaryawan = resp.karyawan || [];
            initCostCenterSelect();
            initNikSelect();
        },
        error: function () {
            // Fallback: init with AJAX-based Select2 (original behaviour)
            initCostCenterSelectFallback();
            initNikSelectFallback();
        }
    });
    initSearchInput();
}

/* ═══════════════════════════════════════════
   COST CENTRE SELECT — client-side filtering
   ═══════════════════════════════════════════ */
function initCostCenterSelect() {
    var $sel = $('#cost_center_select');

    // Clear existing options to avoid conflict with data option
    $sel.empty().append('<option value=""></option>');

    $sel.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Cost Centre --',
        allowClear: true,
        data: cachedCostCenters,
        minimumInputLength: 0,
    });

    $sel.on('change', onCostCenterChange);
}

// Fallback if prefetch fails
function initCostCenterSelectFallback() {
    $('#cost_center_select').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Cost Centre --',
        allowClear: true,
        ajax: {
            url: window.routes.getCostCenter,
            dataType: 'json',
            delay: 200,
            data: function (params) { return { search: params.term || '' }; },
            processResults: function (data) {
                cachedCostCenters = data;
                return { results: data };
            },
            cache: true
        },
        minimumInputLength: 0,
    });
    $('#cost_center_select').on('change', onCostCenterChange);
}

function onCostCenterChange() {
    var data = $('#cost_center_select').select2('data')[0];
    if (data && data.id) {
        currentCostCenter = data.cost_center || data.id;
        currentNamaProject = data.namaproject || '-';
        currentDokIo = data.dokumen_io || '-';
        $('#info_namaproject').val(currentNamaProject);
        $('#searchBarSection').show();
        currentPage = 1;
        currentSearch = '';
        $('#searchInput').val('');
        $('#btnClearSearch').hide();
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
}

/* ═══════════════════════════════════════════
   NIK SELECT — local data, client-side filter
   ═══════════════════════════════════════════ */
function initNikSelect() {
    var $nik = $('#form_nik');

    // Clear existing options to avoid conflict with data option
    $nik.empty().append('<option value=""></option>');

    $nik.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih NIK --',
        allowClear: true,
        dropdownParent: $('#kuotaLemburModal'),
        data: cachedKaryawan,
        minimumInputLength: 0,
    });

    $nik.on('change', onNikChange);
}

function initNikSelectFallback() {
    $('#form_nik').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih NIK --',
        allowClear: true,
        dropdownParent: $('#kuotaLemburModal'),
        ajax: {
            url: window.routes.getKaryawan,
            dataType: 'json',
            delay: 200,
            data: function (params) { return { search: params.term || '' }; },
            processResults: function (data) {
                cachedKaryawan = data;
                return { results: data };
            },
            cache: true
        },
        minimumInputLength: 0,
    });
    $('#form_nik').on('change', onNikChange);
}

function onNikChange() {
    if ($('#form_mode').val() === 'edit') return;
    var nik = $(this).val();
    if (nik && currentCostCenter) {
        // Calculate bulan from already-loaded data (client-side) — no AJAX needed
        var maxBulan = calcMaxBulanFromCache(currentCostCenter, nik);
        $('#form_bulan').val(maxBulan + 1);
    } else {
        $('#form_bulan').val('');
    }
}

/**
 * Calculate max bulan for cost_center+nik from cached table data
 */
function calcMaxBulanFromCache(costCenter, nik) {
    var max = 0;
    lastLoadedData.forEach(function (item) {
        if (item.cost_center === costCenter && item.nik === nik) {
            var b = parseInt(item.bulan) || 0;
            if (b > max) max = b;
        }
    });
    return max;
}

/* ═══════════════════════════════════════════
   SEARCH — debounced with abort for stale
   ═══════════════════════════════════════════ */
var searchDebounceTimer = null;

function initSearchInput() {
    var $input = $('#searchInput');
    var $clearBtn = $('#btnClearSearch');

    $input.on('input', function () {
        var val = $(this).val().trim();
        $clearBtn.toggle(val.length > 0);

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () {
            currentSearch = val;
            currentPage = 1;
            loadData();
        }, 250);
    });

    $input.on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchDebounceTimer);
            currentSearch = $(this).val().trim();
            currentPage = 1;
            loadData();
        }
    });

    $clearBtn.on('click', function () {
        $input.val('').focus();
        $clearBtn.hide();
        clearTimeout(searchDebounceTimer);
        currentSearch = '';
        currentPage = 1;
        loadData();
    });
}

/* ═══════════════════════════════════════════
   EVENT BINDINGS
   ═══════════════════════════════════════════ */
function bindEvents() {
    $('#perPageSelect').on('change', function () {
        currentPerPage = parseInt($(this).val());
        currentPage = 1;
        loadData();
    });

    $('#btnTambah').on('click', function () {
        if (!currentCostCenter) {
            Swal.fire('Peringatan', 'Pilih Cost Centre terlebih dahulu', 'warning');
            return;
        }
        openAddModal();
    });

    $('#btnSimpan').on('click', saveData);
    $('#btnUpload').on('click', function () {
        $('#uploadFile').val('');
        $('#uploadModal').modal('show');
    });
    $('#btnDoUpload').on('click', doUpload);
    $('#btnDownloadTemplate, #btnDownloadTemplateModal').on('click', function () {
        window.location.href = window.routes.downloadTemplate;
    });
    $('#confirmDeleteBtn').on('click', doDelete);

    // Pagination
    $('#firstPageBtn').on('click', function () { currentPage = 1; loadData(); });
    $('#prevPageBtn').on('click', function () { if (currentPage > 1) { currentPage--; loadData(); } });
    $('#nextPageBtn').on('click', function () { currentPage++; loadData(); });
    $('#lastPageBtn').on('click', function () { /* set in renderPagination */ });
}

/* ═══════════════════════════════════════════
   DATA LOADING — with request abort
   ═══════════════════════════════════════════ */
function loadData() {
    if (!currentCostCenter) { showEmptyState(); return; }

    // Abort any in-flight request to avoid stale responses
    if (activeDataXHR && activeDataXHR.readyState !== 4) {
        activeDataXHR.abort();
    }

    showLoadingState();

    activeDataXHR = $.ajax({
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
                lastLoadedData = response.data || [];
                renderTable(response.data);
                renderPagination(response.pagination);
            } else {
                showErrorState(response.message || 'Gagal memuat data');
            }
        },
        error: function (xhr) {
            if (xhr.statusText === 'abort') return; // intentional abort
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat memuat data';
            showErrorState(msg || 'Terjadi kesalahan saat memuat data');
        }
    });
}

/* ═══════════════════════════════════════════
   TABLE RENDERING — DocumentFragment for speed
   ═══════════════════════════════════════════ */
function renderTable(data) {
    var tbody = document.getElementById('kuotaLemburTableBody');
    tbody.innerHTML = '';

    if (!data || data.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="9" class="text-center py-4">' +
            '<div class="d-flex flex-column align-items-center">' +
            '<i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>' +
            '<p class="mb-0 text-muted">Tidak ada data kuota lembur</p></div></td></tr>';
        return;
    }

    var startNo = ((currentPage - 1) * currentPerPage) + 1;
    var frag = document.createDocumentFragment();

    data.forEach(function (item, index) {
        var tr = document.createElement('tr');
        tr.className = 'editable-row';
        tr.title = 'Double-click untuk edit';
        var rowId = item.cost_center + '-' + item.nik + '-' + item.bulan;
        tr.setAttribute('data-id', rowId);
        tr.setAttribute('data-cost-center', item.cost_center);
        tr.setAttribute('data-nik', item.nik || '');
        tr.setAttribute('data-nama', item.nama_karyawan || '');
        tr.setAttribute('data-bulan', item.bulan);
        tr.setAttribute('data-periode-awal', item.periode_awal || '');
        tr.setAttribute('data-periode-akhir', item.periode_akhir || '');
        var jmlWd = parseFloat(item.jml_wd) || 0;
        var jmlWe = parseFloat(item.jml_we) || 0;
        var jmlHn = parseFloat(item.jml_hn) || 0;
        tr.setAttribute('data-jml-wd', jmlWd);
        tr.setAttribute('data-jml-we', jmlWe);
        tr.setAttribute('data-jml-hn', jmlHn);
        tr.setAttribute('data-status', item.status || '');
        tr.ondblclick = (function(id) { return function() { editKuotaLembur(id); }; })(rowId);

        tr.innerHTML =
            '<td class="text-center">' + (startNo + index) + '</td>' +
            '<td class="fw-semibold text-primary">' + highlightMatch(escapeHtml(item.nik)) + '</td>' +
            '<td>' + highlightMatch(escapeHtml(item.nama_karyawan || '-')) + '</td>' +
            '<td class="text-center">' + formatDate(item.periode_awal) + '</td>' +
            '<td class="text-center">' + formatDate(item.periode_akhir) + '</td>' +
            '<td class="text-center">' + formatDecimal(jmlWd) + '</td>' +
            '<td class="text-center">' + formatDecimal(jmlWe) + '</td>' +
            '<td class="text-center">' + formatDecimal(jmlHn) + '</td>' +
            '<td class="text-center">' +
                '<div class="dropdown position-static">' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end shadow border-0">' +
                        '<li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="editKuotaLembur(\'' + rowId + '\')"><i class="bx bx-edit me-2 text-warning"></i> Edit</a></li>' +
                        '<li><hr class="dropdown-divider"></li>' +
                        '<li><a class="dropdown-item py-2 text-danger" href="javascript:void(0);" onclick="deleteKuotaLembur(\'' + rowId + '\')"><i class="bx bx-trash me-2"></i> Hapus</a></li>' +
                    '</ul>' +
                '</div>' +
            '</td>';
        frag.appendChild(tr);
    });

    tbody.appendChild(frag);
}

/* ═══════════════════════════════════════════
   PAGINATION
   ═══════════════════════════════════════════ */
function renderPagination(pagination) {
    if (!pagination || pagination.total === 0) {
        $('#paginationControls').hide();
        return;
    }

    $('#paginationControls').show().css('display', '');
    $('#entriesFrom').text(pagination.from);
    $('#entriesTo').text(pagination.to);
    $('#entriesTotal').text(pagination.total);

    var totalPages = pagination.last_page;
    currentPage = pagination.current_page;

    $('#firstPageBtn, #prevPageBtn').prop('disabled', currentPage <= 1);
    $('#nextPageBtn, #lastPageBtn').prop('disabled', currentPage >= totalPages);

    $('#lastPageBtn').off('click').on('click', function () { currentPage = totalPages; loadData(); });

    var $container = $('#pageNumbersContainer');
    $container.empty();

    var maxVisible = 5;
    var startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    var endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    for (var i = startPage; i <= endPage; i++) {
        var cls = i === currentPage ? 'btn btn-primary btn-sm px-3 fw-bold' : 'btn btn-outline-secondary btn-sm';
        var $btn = $('<button type="button" class="' + cls + '">' + i + '</button>');
        if (i !== currentPage) {
            (function (page) { $btn.on('click', function () { currentPage = page; loadData(); }); })(i);
        }
        $container.append($btn);
    }
}

/* ═══════════════════════════════════════════
   ADD / EDIT MODAL
   ═══════════════════════════════════════════ */
function openAddModal() {
    $('#form_mode').val('add');
    $('#form_id').val('');
    $('#modalTitle').text('Tambah Kuota Lembur');

    $('#form_cost_center').val(currentCostCenter);
    $('#form_dok_io').val(currentDokIo);
    $('#form_namaproject').val(currentNamaProject);

    var $nik = $('#form_nik');
    $nik.prop('disabled', false);
    $nik.val(null).trigger('change');
    $('#form_bulan').val('');
    $('#form_periode_awal').val('');
    $('#form_periode_akhir').val('');
    $('#form_jml_wd').val(0);
    $('#form_jml_we').val(0);
    $('#form_jml_hn').val(0);

    $('#kuotaLemburModal').modal('show');
}

function editKuotaLembur(id) {
    var parts = id.split('-');
    if (parts.length !== 3) return;

    var $row = $('tr[data-id="' + id + '"]');
    if (!$row.length) return;

    $('#form_mode').val('edit');
    $('#form_id').val(id);
    $('#modalTitle').text('Edit Kuota Lembur');

    $('#form_cost_center').val(currentCostCenter);
    $('#form_dok_io').val(currentDokIo);
    $('#form_namaproject').val(currentNamaProject);

    var nik = $row.attr('data-nik');
    var nama = $row.attr('data-nama');
    var nikOption = new Option(nik + ' - ' + nama, nik, true, true);
    $('#form_nik').empty().append(nikOption).trigger('change');
    $('#form_nik').prop('disabled', true);

    $('#form_bulan').val($row.attr('data-bulan'));

    var periodeAwal = $row.attr('data-periode-awal') || '';
    var periodeAkhir = $row.attr('data-periode-akhir') || '';
    $('#form_periode_awal').val(periodeAwal ? periodeAwal.substring(0, 10) : '');
    $('#form_periode_akhir').val(periodeAkhir ? periodeAkhir.substring(0, 10) : '');
    $('#form_jml_wd').val($row.attr('data-jml-wd') || 0);
    $('#form_jml_we').val($row.attr('data-jml-we') || 0);
    $('#form_jml_hn').val($row.attr('data-jml-hn') || 0);

    $('#kuotaLemburModal').modal('show');
}

/* ═══════════════════════════════════════════
   SAVE (CREATE / UPDATE)
   ═══════════════════════════════════════════ */
function saveData() {
    var mode = $('#form_mode').val();
    var costCenter = $('#form_cost_center').val();
    var nik = $('#form_nik').val();
    var periodeAwal = $('#form_periode_awal').val();
    var periodeAkhir = $('#form_periode_akhir').val();
    var jmlWD = parseFloat($('#form_jml_wd').val()) || 0;
    var jmlWE = parseFloat($('#form_jml_we').val()) || 0;
    var jmlHN = parseFloat($('#form_jml_hn').val()) || 0;

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

    showSimpanLoading();

    var data = {
        _token: window.csrfToken,
        cost_center: costCenter,
        nik: nik,
        periode_awal: periodeAwal,
        periode_akhir: periodeAkhir,
        jml_wd: jmlWD,
        jml_we: jmlWE,
        jml_hn: jmlHN,
    };

    var url;
    if (mode === 'edit') {
        var idParts = $('#form_id').val().split('-');
        data.cost_center = idParts[0];
        data.nik = idParts[1];
        data.bulan = parseInt(idParts[2]);
        data._method = 'PUT';
        url = window.routes.update;
    } else {
        url = window.routes.store;
    }

    doSaveAjax(url, data, false);
}

function doSaveAjax(url, data, isReplace) {
    if (isReplace) data.replace = 1;

    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        success: function (response) {
            if (response.success) {
                $('#kuotaLemburModal').modal('hide');
                Swal.fire({ title: 'Berhasil', text: response.message || 'Data tersimpan', icon: 'success', timer: 1500, showConfirmButton: false });
                loadData();
            } else {
                Swal.fire('Gagal', response.message || 'Gagal menyimpan data', 'error');
            }
        },
        error: function (xhr) {
            if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.duplicate) {
                resetSimpanButton();
                showDuplicateConfirmSingle(xhr.responseJSON, url, data);
                return;
            }
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan data';
            Swal.fire('Gagal', msg || 'Terjadi kesalahan saat menyimpan data', 'error');
        },
        complete: resetSimpanButton
    });
}

function showDuplicateConfirmSingle(respData, url, data) {
    var ex = respData.existing;
    var periodeAwalF = ex.periode_awal ? formatDate(ex.periode_awal) : '-';
    var periodeAkhirF = ex.periode_akhir ? formatDate(ex.periode_akhir) : '-';

    Swal.fire({
        icon: 'warning',
        title: 'Data Duplikat Ditemukan',
        html: '<div style="text-align:left;font-size:13px;">' +
            '<p style="margin-bottom:12px;">Data dengan <b>NIK</b> dan <b>Periode Awal</b> yang sama sudah ada.</p>' +
            '<table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:12px;">' +
            '<thead><tr style="background:#f8f9fa;border-bottom:2px solid #dee2e6;">' +
            '<th style="padding:8px 10px;text-align:left;color:#6c757d;">Keterangan</th>' +
            '<th style="padding:8px 10px;text-align:left;color:#6c757d;">Detail</th>' +
            '</tr></thead><tbody>' +
            '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:8px 10px;color:#6c757d;">NIK</td>' +
            '<td style="padding:8px 10px;font-weight:600;color:#0d6efd;">' + escapeHtml(ex.nik) + ' - ' + escapeHtml(ex.nama) + '</td></tr>' +
            '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:8px 10px;color:#6c757d;">Bulan Ke</td>' +
            '<td style="padding:8px 10px;">' + ex.bulan + '</td></tr>' +
            '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:8px 10px;color:#6c757d;">Periode</td>' +
            '<td style="padding:8px 10px;">' + periodeAwalF + ' s/d ' + periodeAkhirF + '</td></tr>' +
            '</tbody></table>' +
            '<div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:10px 12px;margin-bottom:12px;">' +
            '<div style="font-weight:600;color:#6c757d;margin-bottom:6px;"><i class="bx bx-data" style="margin-right:4px;"></i>Data Lama:</div>' +
            '<table style="width:100%;font-size:12px;">' +
            '<tr><td style="padding:2px 0;color:#6c757d;">WeekDay</td><td style="padding:2px 8px;">:</td><td>' + formatDecimal(ex.jml_wd) + '</td></tr>' +
            '<tr><td style="padding:2px 0;color:#6c757d;">WeekEnd</td><td style="padding:2px 8px;">:</td><td>' + formatDecimal(ex.jml_we) + '</td></tr>' +
            '<tr><td style="padding:2px 0;color:#6c757d;">Hari Libur</td><td style="padding:2px 8px;">:</td><td>' + formatDecimal(ex.jml_hn) + '</td></tr>' +
            '</table></div>' +
            '<p style="margin:0;color:#dc3545;"><i class="bx bx-error-circle" style="margin-right:4px;"></i>Ganti data lama dengan data baru?</p></div>',
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-check"></i> Ya, Ganti',
        cancelButtonText: '<i class="bx bx-x"></i> Batal',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        width: '520px',
        customClass: { container: 'swal-on-top' }
    }).then(function (result) {
        if (result.isConfirmed) {
            showSimpanLoading();
            doSaveAjax(url, data, true);
        }
    });
}

function showSimpanLoading() {
    $('#simpanSpinner').removeClass('d-none');
    $('#simpanIcon').addClass('d-none');
    $('#simpanText').text('Menyimpan...');
    $('#btnSimpan').prop('disabled', true);
}
function resetSimpanButton() {
    $('#simpanSpinner').addClass('d-none');
    $('#simpanIcon').removeClass('d-none');
    $('#simpanText').text('Simpan');
    $('#btnSimpan').prop('disabled', false);
}

/* ═══════════════════════════════════════════
   DELETE — optimistic row removal
   ═══════════════════════════════════════════ */
function deleteKuotaLembur(id) {
    deleteTargetId = id;
    $('#deleteConfirmModal').modal('show');
}

function doDelete() {
    if (!deleteTargetId) return;
    var parts = deleteTargetId.split('-');
    if (parts.length !== 3) return;

    var targetId = deleteTargetId;

    // Optimistic: hide row immediately
    var $row = $('tr[data-id="' + targetId + '"]');
    $row.fadeOut(150);

    $('#deleteConfirmModal').modal('hide');

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
            if (response.success) {
                // Remove from cache
                lastLoadedData = lastLoadedData.filter(function (item) {
                    return !(item.cost_center === parts[0] && item.nik === parts[1] && parseInt(item.bulan) === parseInt(parts[2]));
                });
                $row.remove();
                Swal.fire({ title: 'Berhasil', text: response.message || 'Data berhasil dihapus', icon: 'success', timer: 1500, showConfirmButton: false });
                // If page is now empty, go back one page
                if ($('#kuotaLemburTableBody tr').length === 0 && currentPage > 1) {
                    currentPage--;
                    loadData();
                }
            } else {
                $row.fadeIn(150); // Revert
                Swal.fire('Gagal', response.message || 'Gagal menghapus data', 'error');
            }
        },
        error: function (xhr) {
            $row.fadeIn(150); // Revert
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus data';
            Swal.fire('Gagal', msg || 'Terjadi kesalahan saat menghapus data', 'error');
        }
    });
}

/* ═══════════════════════════════════════════
   UPLOAD
   ═══════════════════════════════════════════ */
function doUpload() {
    var fileInput = document.getElementById('uploadFile');
    if (!fileInput.files || fileInput.files.length === 0) {
        swalFront('Peringatan', 'Pilih file terlebih dahulu', 'warning');
        return;
    }
    $('#uploadSpinner').removeClass('d-none');
    $('#btnDoUpload').prop('disabled', true);
    sendUpload(fileInput.files[0], false);
}

function sendUpload(file, confirmReplace) {
    var formData = new FormData();
    formData.append('file', file);
    formData.append('_token', window.csrfToken);
    if (confirmReplace) formData.append('confirm_replace', '1');

    $.ajax({
        url: window.routes.upload,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#uploadModal').modal('hide');
            resetUploadButton();
            if (response.success) {
                if (response.has_errors && response.errors && response.errors.length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sebagian Data Gagal',
                        html: '<p>' + escapeHtml(response.message) + '</p>' +
                            '<div style="text-align:left;max-height:200px;overflow-y:auto;font-size:13px;background:#fff3cd;padding:10px;border-radius:4px;margin-top:8px;">' +
                            response.errors.map(function (e) { return '<div style="margin-bottom:4px;">&#x26A0; ' + escapeHtml(e) + '</div>'; }).join('') +
                            '</div>',
                        customClass: { container: 'swal-on-top' }
                    });
                } else {
                    Swal.fire('Berhasil', response.message, 'success');
                }
                currentPage = 1;
                loadData();
            } else {
                Swal.fire('Gagal', response.message || 'Gagal mengimpor data', 'error');
            }
        },
        error: function (xhr) {
            resetUploadButton();
            var data = xhr.responseJSON;
            if (xhr.status === 409 && data && data.has_duplicates) {
                $('#uploadModal').modal('hide');
                showDuplicatePreview(data, file);
                return;
            }
            $('#uploadModal').modal('hide');
            if (data && data.errors && data.errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Gagal',
                    html: '<p>' + escapeHtml(data.message || 'Semua data gagal diimpor') + '</p>' +
                        '<div style="text-align:left;max-height:200px;overflow-y:auto;font-size:13px;background:#f8d7da;padding:10px;border-radius:4px;margin-top:8px;">' +
                        data.errors.map(function (e) { return '<div style="margin-bottom:4px;">&#x2716; ' + escapeHtml(e) + '</div>'; }).join('') +
                        '</div>',
                    customClass: { container: 'swal-on-top' }
                });
            } else {
                var msg = data ? data.message : 'Terjadi kesalahan saat mengimpor data';
                Swal.fire('Gagal', msg || 'Terjadi kesalahan saat mengimpor data', 'error');
            }
        },
        complete: resetUploadButton
    });
}

function showDuplicatePreview(data, file) {
    var dupHtml = '<div style="text-align:left;font-size:13px;">';
    dupHtml += '<p style="margin-bottom:10px;">Ditemukan <b style="color:#dc3545;">' + data.duplicate_count + ' data duplikat</b> yang akan diganti:</p>';
    dupHtml += '<div style="max-height:300px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;margin-bottom:12px;">';
    dupHtml += '<table style="width:100%;border-collapse:collapse;font-size:12px;">';
    dupHtml += '<thead><tr style="background:#f8f9fa;border-bottom:2px solid #dee2e6;position:sticky;top:0;">';
    dupHtml += '<th style="padding:8px 10px;text-align:left;color:#6c757d;">NIK</th>';
    dupHtml += '<th style="padding:8px 10px;text-align:left;color:#6c757d;">Cost Centre</th>';
    dupHtml += '<th style="padding:8px 10px;text-align:center;color:#6c757d;">Periode Awal</th>';
    dupHtml += '<th style="padding:8px 10px;text-align:center;color:#6c757d;">Data Lama</th>';
    dupHtml += '</tr></thead><tbody>';

    data.duplicates.forEach(function (dup, i) {
        var bg = i % 2 === 0 ? '#ffffff' : '#f8f9fa';
        dupHtml += '<tr style="background:' + bg + ';border-bottom:1px solid #dee2e6;">' +
            '<td style="padding:8px 10px;"><span style="font-weight:600;color:#0d6efd;">' + escapeHtml(dup.nik) + '</span><br><span style="color:#6c757d;font-size:11px;">' + escapeHtml(dup.nama) + '</span></td>' +
            '<td style="padding:8px 10px;">' + escapeHtml(dup.cost_center || '-') + '</td>' +
            '<td style="padding:8px 10px;text-align:center;">' + escapeHtml(dup.periode_awal) + '</td>' +
            '<td style="padding:8px 10px;text-align:center;font-size:11px;color:#6c757d;">' +
            'Akhir: ' + escapeHtml(dup.existing_periode_akhir) + '<br>' +
            'WD: ' + formatDecimal(dup.existing_jml_wd) + ' | WE: ' + formatDecimal(dup.existing_jml_we) + ' | HN: ' + formatDecimal(dup.existing_jml_hn) +
            '</td></tr>';
    });

    dupHtml += '</tbody></table></div>';
    dupHtml += '<div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:10px 12px;margin-bottom:12px;">';
    dupHtml += '<span style="color:#198754;"><b>' + data.new_rows + '</b> data baru</span> &nbsp;|&nbsp; ';
    dupHtml += '<span style="color:#dc3545;"><b>' + data.duplicate_count + '</b> data duplikat akan diganti</span></div>';

    if (data.errors && data.errors.length > 0) {
        dupHtml += '<div style="max-height:100px;overflow-y:auto;background:#f8d7da;border:1px solid #f5c2c7;padding:8px 12px;border-radius:6px;margin-bottom:10px;font-size:12px;">';
        data.errors.forEach(function (e) {
            dupHtml += '<div style="margin-bottom:3px;"><i class="bx bx-x-circle" style="color:#dc3545;margin-right:4px;"></i>' + escapeHtml(e) + '</div>';
        });
        dupHtml += '</div>';
    }
    dupHtml += '</div>';

    Swal.fire({
        icon: 'warning',
        title: 'Data Duplikat Ditemukan',
        html: dupHtml,
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-check"></i> Ya, Ganti & Import',
        cancelButtonText: '<i class="bx bx-x"></i> Batal',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        width: '680px',
        customClass: { container: 'swal-on-top' }
    }).then(function (result) {
        if (result.isConfirmed) {
            $('#uploadSpinner').removeClass('d-none');
            $('#btnDoUpload').prop('disabled', true);
            sendUpload(file, true);
        }
    });
}

function resetUploadButton() {
    $('#uploadSpinner').addClass('d-none');
    $('#btnDoUpload').prop('disabled', false);
}

/* ═══════════════════════════════════════════
   TABLE STATES
   ═══════════════════════════════════════════ */
function showLoadingState() {
    document.getElementById('kuotaLemburTableBody').innerHTML =
        '<tr><td colspan="9" class="text-center py-4">' +
        '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>' +
        '<span class="ms-2 text-muted">Memuat data...</span></td></tr>';
}

function showEmptyState() {
    document.getElementById('kuotaLemburTableBody').innerHTML =
        '<tr><td colspan="9" class="text-center py-4">' +
        '<div class="d-flex flex-column align-items-center">' +
        '<i class="bx bx-search-alt-2 mb-2" style="font-size:48px;color:#ccc;"></i>' +
        '<p class="mb-0 text-muted">Pilih Cost Centre untuk melihat data</p></div></td></tr>';
    $('#paginationControls').hide();
}

function showErrorState(message) {
    document.getElementById('kuotaLemburTableBody').innerHTML =
        '<tr><td colspan="9" class="text-center py-4 text-danger">' +
        '<i class="bx bx-error-circle" style="font-size:24px;"></i>' +
        '<p class="mb-0 mt-2">' + escapeHtml(message) + '</p></td></tr>';
}

/* ═══════════════════════════════════════════
   UTILITY FUNCTIONS
   ═══════════════════════════════════════════ */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeAttr(text) {
    if (!text) return '';
    return String(text).replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function highlightMatch(text) {
    if (!currentSearch || !text) return text;
    var words = currentSearch.trim().split(/\s+/);
    var result = text;
    words.forEach(function (word) {
        if (!word) return;
        var escaped = word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        result = result.replace(new RegExp('(' + escaped + ')', 'gi'), '<span class="search-highlight">$1</span>');
    });
    return result;
}

function formatDecimal(val) {
    var num = parseFloat(val);
    if (isNaN(num)) return '0';
    if (num % 1 === 0) return num.toString();
    return num.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
}
