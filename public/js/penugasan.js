/**
 * Pengajuan Penugasan JavaScript — SPA Pattern (Header-first flow)
 * Flow:
 *   1. Select existing header from dropdown OR
 *   2. Click "+" to create new header → fill CC → Simpan Header
 *   3. Then manage detail rows (Tambah, Edit, Lihat Detail, Upload)
 */

/* ───────── SweetAlert2 helper ───────── */
function swalFront(title, text, icon) {
    return Swal.fire({ title, text, icon, customClass: { container: 'swal-on-top' } });
}

/* ───────── Global state ───────── */
let currentHeaderData = null;
let currentPage = 1;
let currentPerPage = 10;
let currentSearch = '';
let deleteTargetId = null;

// Caches (populated once on page load)
let cachedCostCenters = [];
let cachedKaryawan = [];
let cachedHeaders = [];
let lastLoadedData = [];

// Abort controller for stale AJAX
let activeDataXHR = null;

/* ───────── Bootstrap ───────── */
$(document).ready(function () {
    bindEvents();
    prefetchInitialData();
});

/**
 * Pre-fetch cost centres, karyawan & headers in a SINGLE call
 */
function prefetchInitialData() {
    $.ajax({
        url: window.routes.getInitialData,
        method: 'GET',
        dataType: 'json',
        success: function (resp) {
            cachedCostCenters = resp.cost_centers || [];
            cachedKaryawan = resp.karyawan || [];
            cachedHeaders = resp.headers || [];
            initHeaderSelect();
            initCreateCCSelect();
            initNikSelect();
        },
        error: function () {
            initHeaderSelectFallback();
            initCreateCCSelectFallback();
            initNikSelectFallback();
        }
    });
    initSearchInput();
}

/* ═══════════════════════════════════════════
   HEADER SELECT — Select2 dropdown of existing headers
   ═══════════════════════════════════════════ */
function initHeaderSelect() {
    var $sel = $('#header_select');
    $sel.empty().append('<option value=""></option>');

    $sel.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih ID Penugasan --',
        allowClear: true,
        data: cachedHeaders,
        minimumInputLength: 0,
    });

    $sel.on('change', onHeaderChange);
}

function initHeaderSelectFallback() {
    var $sel = $('#header_select');
    $sel.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih ID Penugasan --',
        allowClear: true,
        ajax: {
            url: window.routes.getHeaders,
            dataType: 'json',
            delay: 200,
            data: function (params) { return { search: params.term || '' }; },
            processResults: function (data) {
                cachedHeaders = data;
                return { results: data };
            },
            cache: true
        },
        minimumInputLength: 0,
    });
    $sel.on('change', onHeaderChange);
}

function onHeaderChange() {
    var data = $('#header_select').select2('data')[0];
    if (data && data.id) {
        currentHeaderData = {
            IDPenugasan: data.IDPenugasan || data.id,
            cost_center: data.cost_center || '',
            id_project: data.id_project || '',
            no_urut: data.no_urut || 0,
            NoSurat: data.NoSurat || '-',
            namaproject: data.namaproject || '-',
            dokumen_io: data.dokumen_io || '-',
        };
        showHeaderInfo();
        currentPage = 1;
        currentSearch = '';
        $('#searchInput').val('');
        $('#btnClearSearch').hide();
        loadData();
    } else {
        currentHeaderData = null;
        hideHeaderInfo();
        showEmptyState();
        $('#paginationControls').hide();
    }
    $('#btnGenerateId').prop('disabled', false);
}

function showHeaderInfo() {
    if (!currentHeaderData) return;
    $('#info_nosurat').val(currentHeaderData.NoSurat);
    $('#info_costcenter').val(currentHeaderData.cost_center);
    $('#info_namaproject').val(currentHeaderData.namaproject);
    $('#headerInfoSection').show();
    $('#searchBarSection').removeClass('d-none').addClass('d-flex');
    $('#tableSection').removeClass('d-none');
}

function hideHeaderInfo() {
    $('#info_nosurat').val('');
    $('#info_costcenter').val('');
    $('#info_namaproject').val('');
    $('#headerInfoSection').hide();
    $('#searchBarSection').removeClass('d-flex').addClass('d-none');
    $('#tableSection').addClass('d-none');
    $('#paginationControls').hide();
}

/* ═══════════════════════════════════════════
   CREATE HEADER — Generate ID + CC + Simpan Header
   ═══════════════════════════════════════════ */
function initCreateCCSelect() {
    var $sel = $('#new_cost_center');
    $sel.empty().append('<option value=""></option>');

    $sel.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Cost Centre --',
        allowClear: true,
        data: cachedCostCenters,
        minimumInputLength: 0,
        dropdownParent: $('#createHeaderSection'),
    });

    $sel.on('change', function () {
        var data = $(this).select2('data')[0];
        if (data && data.id) {
            $('#new_namaproject').val(data.namaproject || '-');
            $('#new_id_project').val(data.id_project || '');
            $('#new_no_urut').val(data.no_urut || 0);
            $('#newNamaProyekRow').show();
        } else {
            $('#new_namaproject').val('');
            $('#new_id_project').val('');
            $('#new_no_urut').val('');
            $('#newNamaProyekRow').hide();
        }
    });
}

function initCreateCCSelectFallback() {
    var $sel = $('#new_cost_center');
    $sel.select2({
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
        dropdownParent: $('#createHeaderSection'),
    });

    $sel.on('change', function () {
        var data = $(this).select2('data')[0];
        if (data && data.id) {
            $('#new_namaproject').val(data.namaproject || '-');
            $('#new_id_project').val(data.id_project || '');
            $('#new_no_urut').val(data.no_urut || 0);
            $('#newNamaProyekRow').show();
        } else {
            $('#new_namaproject').val('');
            $('#new_id_project').val('');
            $('#new_no_urut').val('');
            $('#newNamaProyekRow').hide();
        }
    });
}

function showCreateHeaderSection() {
    $('#header_select').prop('disabled', true);
    $('#btnGenerateId').prop('disabled', true);

    // Generate new ID
    $.ajax({
        url: window.routes.generateId,
        method: 'GET',
        dataType: 'json',
        success: function (resp) {
            $('#new_idpenugasan').val(resp.IDPenugasan || '');
            $('#new_nosurat').val(resp.NoSurat || '');
            $('#createHeaderSection').slideDown(200);
        },
        error: function () {
            swalFront('Gagal', 'Gagal menghasilkan ID Penugasan', 'error');
            $('#header_select').prop('disabled', false);
            $('#btnGenerateId').prop('disabled', false);
        }
    });
}

function hideCreateHeaderSection() {
    $('#createHeaderSection').slideUp(200, function () {
        $('#new_idpenugasan').val('');
        $('#new_nosurat').val('');
        $('#new_cost_center').val(null).trigger('change');
        $('#new_namaproject').val('');
        $('#new_id_project').val('');
        $('#new_no_urut').val('');
        $('#newNamaProyekRow').hide();
    });
    $('#header_select').prop('disabled', false);
    $('#btnGenerateId').prop('disabled', false);
}

function saveHeader() {
    var idPenugasan = $('#new_idpenugasan').val();
    var noSurat = $('#new_nosurat').val();
    var ccData = $('#new_cost_center').select2('data')[0];
    var costCenter = ccData ? (ccData.cost_center || '') : '';
    var idProject = ccData ? (ccData.id_project || '') : '';
    var noUrut = ccData ? (ccData.no_urut || 0) : 0;

    if (!idPenugasan) {
        swalFront('Peringatan', 'ID Penugasan belum dihasilkan', 'warning');
        return;
    }
    if (!costCenter) {
        swalFront('Peringatan', 'Pilih Cost Centre terlebih dahulu', 'warning');
        return;
    }

    $('#btnSimpanHeader').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

    $.ajax({
        url: window.routes.storeHeader,
        method: 'POST',
        data: {
            _token: window.csrfToken,
            IDPenugasan: idPenugasan,
            cost_center: costCenter,
            id_project: idProject,
            no_urut: noUrut,
            NoSurat: noSurat,
        },
        success: function (response) {
            if (response.success) {
                var headerData = response.data;

                cachedHeaders.unshift(headerData);

                var $sel = $('#header_select');
                $sel.select2('destroy');
                $sel.empty().append('<option value=""></option>');
                $sel.select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Pilih ID Penugasan --',
                    allowClear: true,
                    data: cachedHeaders,
                    minimumInputLength: 0,
                });
                $sel.val(headerData.id).trigger('change');

                hideCreateHeaderSection();

                Swal.fire({
                    title: 'Berhasil',
                    text: response.message || 'Header berhasil disimpan',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Gagal', response.message || 'Gagal menyimpan header', 'error');
            }
        },
        error: function (xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan header';
            Swal.fire('Gagal', msg || 'Terjadi kesalahan saat menyimpan header', 'error');
        },
        complete: function () {
            $('#btnSimpanHeader').prop('disabled', false).html('<i class="bx bx-save me-1"></i> Simpan Header');
        }
    });
}

/* ═══════════════════════════════════════════
   NIK SELECT — local data, client-side filter
   ═══════════════════════════════════════════ */
function initNikSelect() {
    var $nik = $('#form_nik');
    $nik.empty().append('<option value=""></option>');

    $nik.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih NIK --',
        allowClear: true,
        dropdownParent: $('#penugasanModal'),
        data: cachedKaryawan,
        minimumInputLength: 0,
        templateResult: function (data) {
            if (data.loading || !data.id) return data.text;
            return $('<span>').text((data.nik || data.id) + ' - ' + (data.nama || ''));
        },
        templateSelection: function (data) {
            if (!data.id) return data.text;
            return data.nik || data.id;
        }
    });

    $nik.on('change', onNikChange);
}

/**
 * Re-initialize NIK Select2 with full cached data (fixes data loss after edit)
 */
function reinitNikSelect() {
    var $nik = $('#form_nik');
    try { $nik.select2('destroy'); } catch (e) { }
    $nik.off('change', onNikChange);
    $nik.empty().append('<option value=""></option>');
    $nik.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih NIK --',
        allowClear: true,
        dropdownParent: $('#penugasanModal'),
        data: cachedKaryawan,
        minimumInputLength: 0,
        templateResult: function (data) {
            if (data.loading || !data.id) return data.text;
            return $('<span>').text((data.nik || data.id) + ' - ' + (data.nama || ''));
        },
        templateSelection: function (data) {
            if (!data.id) return data.text;
            return data.nik || data.id;
        }
    });
    $nik.on('change', onNikChange);
}

function initNikSelectFallback() {
    $('#form_nik').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih NIK --',
        allowClear: true,
        dropdownParent: $('#penugasanModal'),
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
    var nikVal = $('#form_nik').val();
    if (nikVal) {
        var found = cachedKaryawan.find(function (k) { return k.id === nikVal || k.nik === nikVal; });
        if (found) {
            $('#form_nama_karyawan').val(found.nama || '');
        } else {
            var s2data = $('#form_nik').select2('data')[0];
            if (s2data && s2data.nama) {
                $('#form_nama_karyawan').val(s2data.nama);
            } else {
                $('#form_nama_karyawan').val('');
            }
        }
    } else {
        $('#form_nama_karyawan').val('');
    }
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
        if (currentHeaderData) loadData();
    });

    // Generate / create header
    $('#btnGenerateId').on('click', showCreateHeaderSection);
    $('#btnSimpanHeader').on('click', saveHeader);
    $('#btnCancelHeader').on('click', hideCreateHeaderSection);

    // Detail CRUD buttons
    $('#btnTambah').on('click', function () {
        if (!currentHeaderData) {
            Swal.fire('Peringatan', 'Pilih atau buat Header Penugasan terlebih dahulu', 'warning');
            return;
        }
        openAddModal();
    });

    $('#btnSimpan').on('click', saveData);

    // Upload
    $('#btnUpload').on('click', function () {
        if (!currentHeaderData) {
            Swal.fire('Peringatan', 'Pilih ID Penugasan terlebih dahulu', 'warning');
            return;
        }
        $('#uploadFile').val('');
        $('#uploadModal').modal('show');
    });
    $('#btnDoUpload').on('click', doUpload);

    // Download template
    $('#btnDownloadTemplate, #btnDownloadTemplateModal').on('click', function () {
        if (!currentHeaderData) {
            Swal.fire('Peringatan', 'Pilih ID Penugasan terlebih dahulu', 'warning');
            return;
        }
        window.location.href = window.routes.downloadTemplate;
    });

    // Delete confirmation
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
    if (!currentHeaderData) { showEmptyState(); return; }

    if (activeDataXHR && activeDataXHR.readyState !== 4) {
        activeDataXHR.abort();
    }

    showLoadingState();

    activeDataXHR = $.ajax({
        url: window.routes.getData,
        method: 'GET',
        data: {
            id_penugasan: currentHeaderData.IDPenugasan,
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
            if (xhr.statusText === 'abort') return;
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat memuat data';
            showErrorState(msg || 'Terjadi kesalahan saat memuat data');
        }
    });
}

/* ═══════════════════════════════════════════
   TABLE RENDERING — DocumentFragment for speed
   ═══════════════════════════════════════════ */
function renderTable(data) {
    var tbody = document.getElementById('penugasanTableBody');
    tbody.innerHTML = '';

    if (!data || data.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="9" class="text-center py-4">' +
            '<div class="d-flex flex-column align-items-center">' +
            '<i class="bx bx-search-alt-2 mb-2" style="font-size: 48px; color: #ccc;"></i>' +
            '<p class="mb-0 text-muted">Tidak ada data penugasan</p></div></td></tr>';
        return;
    }

    var startNo = ((currentPage - 1) * currentPerPage) + 1;
    var frag = document.createDocumentFragment();

    data.forEach(function (item, index) {
        var tr = document.createElement('tr');
        tr.className = 'editable-row';
        tr.title = 'Double-click untuk edit';
        var rowId = item.id;
        tr.setAttribute('data-id', rowId);
        tr.setAttribute('data-nik', item.NIK || '');
        tr.setAttribute('data-nama', item.nama_karyawan || '');
        tr.setAttribute('data-jabatan', item.Jabatan || '');
        tr.setAttribute('data-periode-awal', item.Periodeawal || '');
        tr.setAttribute('data-periode-akhir', item.Periodeakhir || '');
        tr.setAttribute('data-bobot', item.Bobot || 0);
        tr.setAttribute('data-status', item.Status || 'A');
        tr.setAttribute('data-id-penugasan', item.IDPenugasan || '');
        tr.setAttribute('data-no-surat', item.NoSurat || '');
        tr.ondblclick = (function (id) { return function () { editPenugasan(id); }; })(rowId);

        var statusBadge = item.Status === 'A'
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-secondary">Non-Aktif</span>';

        tr.innerHTML =
            '<td class="text-center">' + (startNo + index) + '</td>' +
            '<td class="fw-semibold text-primary">' + highlightMatch(escapeHtml(item.NIK)) + '</td>' +
            '<td>' + highlightMatch(escapeHtml(item.nama_karyawan || '-')) + '</td>' +
            '<td class="text-center">' + formatDate(item.Periodeawal) + '</td>' +
            '<td class="text-center">' + formatDate(item.Periodeakhir) + '</td>' +
            '<td class="text-center">' + escapeHtml(item.Jabatan || '-') + '</td>' +
            '<td class="text-center">' + formatBobot(item.Bobot) + '</td>' +
            '<td class="text-center">' + statusBadge + '</td>' +
            '<td class="text-center">' +
            '<div class="dropdown position-static">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
            '<ul class="dropdown-menu dropdown-menu-end shadow border-0">' +
            '<li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="viewDetail(' + rowId + ')"><i class="bx bx-show me-2 text-info"></i> Lihat Detail</a></li>' +
            '<li><a class="dropdown-item py-2" href="javascript:void(0);" onclick="editPenugasan(' + rowId + ')"><i class="bx bx-edit me-2 text-warning"></i> Edit</a></li>' +
            '<li><hr class="dropdown-divider"></li>' +
            '<li><a class="dropdown-item py-2 text-danger" href="javascript:void(0);" onclick="deletePenugasan(' + rowId + ')"><i class="bx bx-trash me-2"></i> Hapus</a></li>' +
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
   ADD / EDIT / VIEW DETAIL MODAL
   ═══════════════════════════════════════════ */
function openAddModal() {
    $('#form_mode').val('add');
    $('#form_id').val('');
    $('#modalTitle').text('Tambah Penugasan');

    // Fill from current header
    $('#form_cost_center').val(currentHeaderData.cost_center);
    $('#form_dok_io').val(currentHeaderData.dokumen_io);
    $('#form_namaproject').val(currentHeaderData.namaproject);

    // Enable all fields
    setFormFieldsEnabled(true);

    // Re-init NIK Select2 with full cached data
    reinitNikSelect();
    var $nik = $('#form_nik');
    $nik.prop('disabled', false);
    $nik.val(null).trigger('change');
    $('#form_nama_karyawan').val('');
    $('#form_jabatan').val('');
    $('#form_periode_awal').val('');
    $('#form_periode_akhir').val('');
    $('#form_bobot').val('');
    $('#form_status').val('A');

    // Show Simpan button
    $('#btnSimpan').show();

    $('#penugasanModal').modal('show');
}

function editPenugasan(id) {
    var $row = $('tr[data-id="' + id + '"]');
    if (!$row.length) return;

    $('#form_mode').val('edit');
    $('#form_id').val(id);
    $('#modalTitle').text('Edit Penugasan');

    // Fill from current header
    $('#form_cost_center').val(currentHeaderData ? currentHeaderData.cost_center : '');
    $('#form_dok_io').val(currentHeaderData ? currentHeaderData.dokumen_io : '');
    $('#form_namaproject').val(currentHeaderData ? currentHeaderData.namaproject : '');

    // Enable all fields
    setFormFieldsEnabled(true);

    // Re-init NIK Select2 then set selected value
    var nik = $row.attr('data-nik');
    var nama = $row.attr('data-nama');
    reinitNikSelect();
    var nikOption = new Option(nik + ' - ' + nama, nik, true, true);
    $('#form_nik').append(nikOption).trigger('change');
    $('#form_nik').prop('disabled', true);
    $('#form_nama_karyawan').val(nama);

    $('#form_jabatan').val($row.attr('data-jabatan'));

    var periodeAwal = $row.attr('data-periode-awal') || '';
    var periodeAkhir = $row.attr('data-periode-akhir') || '';
    $('#form_periode_awal').val(periodeAwal ? periodeAwal.substring(0, 10) : '');
    $('#form_periode_akhir').val(periodeAkhir ? periodeAkhir.substring(0, 10) : '');
    $('#form_bobot').val($row.attr('data-bobot') || 0);
    $('#form_status').val($row.attr('data-status') || 'A');

    // Show Simpan button
    $('#btnSimpan').show();

    $('#penugasanModal').modal('show');
}

/**
 * Lihat Detail — opens modal in read-only mode (all fields disabled, no Simpan button)
 */
function viewDetail(id) {
    var $row = $('tr[data-id="' + id + '"]');
    if (!$row.length) return;

    $('#form_mode').val('view');
    $('#form_id').val(id);
    $('#modalTitle').text('Lihat Detail Penugasan');

    // Fill from current header
    $('#form_cost_center').val(currentHeaderData ? currentHeaderData.cost_center : '');
    $('#form_dok_io').val(currentHeaderData ? currentHeaderData.dokumen_io : '');
    $('#form_namaproject').val(currentHeaderData ? currentHeaderData.namaproject : '');

    // Re-init NIK Select2 then set selected value
    var nik = $row.attr('data-nik');
    var nama = $row.attr('data-nama');
    reinitNikSelect();
    var nikOption = new Option(nik + ' - ' + nama, nik, true, true);
    $('#form_nik').append(nikOption).trigger('change');
    $('#form_nama_karyawan').val(nama);

    $('#form_jabatan').val($row.attr('data-jabatan'));

    var periodeAwal = $row.attr('data-periode-awal') || '';
    var periodeAkhir = $row.attr('data-periode-akhir') || '';
    $('#form_periode_awal').val(periodeAwal ? periodeAwal.substring(0, 10) : '');
    $('#form_periode_akhir').val(periodeAkhir ? periodeAkhir.substring(0, 10) : '');
    $('#form_bobot').val($row.attr('data-bobot') || 0);
    $('#form_status').val($row.attr('data-status') || 'A');

    // Disable all fields for view mode
    setFormFieldsEnabled(false);

    // Hide Simpan button
    $('#btnSimpan').hide();

    $('#penugasanModal').modal('show');
}

/**
 * Enable/disable all form fields (for view mode toggle)
 */
function setFormFieldsEnabled(enabled) {
    var fields = ['#form_periode_awal', '#form_periode_akhir', '#form_bobot', '#form_status'];
    fields.forEach(function (sel) {
        $(sel).prop('disabled', !enabled);
    });
    $('#form_jabatan').prop('readonly', !enabled).prop('disabled', false);
    if (!enabled) {
        $('#form_jabatan').css('background-color', '#e9ecef');
    } else {
        $('#form_jabatan').css('background-color', '');
    }
    $('#form_nik').prop('disabled', !enabled);
}

/* ═══════════════════════════════════════════
   SAVE (CREATE / UPDATE)
   ═══════════════════════════════════════════ */
function saveData() {
    var mode = $('#form_mode').val();
    var costCenter = $('#form_cost_center').val();
    var nik = $('#form_nik').val();
    var jabatan = $('#form_jabatan').val();
    var periodeAwal = $('#form_periode_awal').val();
    var periodeAkhir = $('#form_periode_akhir').val();
    var bobot = parseFloat($('#form_bobot').val()) || 0;
    var status = $('#form_status').val();

    if (!costCenter || !nik || !jabatan || !periodeAwal || !periodeAkhir) {
        swalFront('Peringatan', 'Data wajib belum lengkap', 'warning');
        return;
    }
    if (new Date(periodeAwal) > new Date(periodeAkhir)) {
        swalFront('Peringatan', 'Periode tidak valid', 'warning');
        return;
    }
    if (bobot <= 0) {
        swalFront('Peringatan', 'Bobot tidak boleh 0. Minimal 0.01', 'warning');
        return;
    }
    if (bobot > 100) {
        swalFront('Peringatan', 'Bobot tidak boleh lebih dari 100', 'warning');
        return;
    }

    showSimpanLoading();

    var data = {
        _token: window.csrfToken,
        cost_center: costCenter,
        NIK: nik,
        Jabatan: jabatan,
        Periodeawal: periodeAwal,
        Periodeakhir: periodeAkhir,
        Bobot: bobot,
        Status: status,
    };

    var url;
    if (mode === 'edit') {
        data.id = $('#form_id').val();
        data._method = 'PUT';
        url = window.routes.update;
    } else {
        data.IDPenugasan = currentHeaderData.IDPenugasan;
        data.NoSurat = currentHeaderData.NoSurat;
        url = window.routes.store;
    }

    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        success: function (response) {
            if (response.success) {
                $('#penugasanModal').modal('hide');
                Swal.fire({
                    title: 'Berhasil',
                    text: response.message || 'Data tersimpan',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadData();
            } else {
                Swal.fire('Gagal', response.message || 'Gagal menyimpan data', 'error');
            }
        },
        error: function (xhr) {
            var respData = xhr.responseJSON;
            // Handle duplicate detection (409)
            if (xhr.status === 409 && respData && respData.duplicate) {
                var ex = respData.existing || {};
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Duplikat Ditemukan',
                    html: '<div style="text-align:left;font-size:13px;">' +
                        '<p>Sudah ada data penugasan dengan <b>Cost Centre</b>, <b>NIK</b>, <b>Periode</b>, dan <b>Jabatan</b> yang sama:</p>' +
                        '<table style="width:100%;font-size:12px;border-collapse:collapse;">' +
                        '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:4px 8px;color:#6c757d;">NIK</td><td style="padding:4px 8px;font-weight:600;">' + escapeHtml(ex.nik) + ' - ' + escapeHtml(ex.nama) + '</td></tr>' +
                        '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:4px 8px;color:#6c757d;">Jabatan</td><td style="padding:4px 8px;">' + escapeHtml(ex.jabatan) + '</td></tr>' +
                        '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:4px 8px;color:#6c757d;">Periode</td><td style="padding:4px 8px;">' + formatDate(ex.periode_awal) + ' s/d ' + formatDate(ex.periode_akhir) + '</td></tr>' +
                        '<tr><td style="padding:4px 8px;color:#6c757d;">Bobot Lama</td><td style="padding:4px 8px;">' + formatBobot(ex.bobot) + '</td></tr>' +
                        '</table>' +
                        '<p class="mt-2 mb-0">Apakah Anda ingin <b>mengganti bobot</b> data lama dengan data baru?</p></div>',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bx bx-check"></i> Ya, Ganti',
                    cancelButtonText: '<i class="bx bx-x"></i> Batal',
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    customClass: { container: 'swal-on-top' }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        // Re-send with replace flag
                        data.replace = 1;
                        showSimpanLoading();
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: data,
                            success: function (resp2) {
                                if (resp2.success) {
                                    $('#penugasanModal').modal('hide');
                                    Swal.fire({ title: 'Berhasil', text: resp2.message || 'Data berhasil diganti', icon: 'success', timer: 1500, showConfirmButton: false });
                                    loadData();
                                } else {
                                    Swal.fire('Gagal', resp2.message || 'Gagal mengganti data', 'error');
                                }
                            },
                            error: function (xhr2) {
                                var m = xhr2.responseJSON ? xhr2.responseJSON.message : 'Terjadi kesalahan';
                                Swal.fire('Gagal', m, 'error');
                            },
                            complete: resetSimpanButton
                        });
                    }
                });
                return;
            }
            // Handle overlap detection (422 + overlap flag)
            if (xhr.status === 422 && respData && respData.overlap) {
                var ov = respData.existing || {};
                Swal.fire({
                    icon: 'error',
                    title: 'Periode Bersinggungan',
                    html: '<div style="text-align:left;font-size:13px;">' +
                        '<p>Sudah ada data dengan <b>NIK</b>, <b>Cost Centre</b>, dan <b>Jabatan</b> yang sama pada periode yang bersinggungan:</p>' +
                        '<table style="width:100%;font-size:12px;border-collapse:collapse;">' +
                        '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:4px 8px;color:#6c757d;">Jabatan</td><td style="padding:4px 8px;font-weight:600;">' + escapeHtml(ov.jabatan) + '</td></tr>' +
                        '<tr style="border-bottom:1px solid #dee2e6;"><td style="padding:4px 8px;color:#6c757d;">Periode</td><td style="padding:4px 8px;">' + formatDate(ov.periode_awal) + ' s/d ' + formatDate(ov.periode_akhir) + '</td></tr>' +
                        '<tr><td style="padding:4px 8px;color:#6c757d;">Bobot</td><td style="padding:4px 8px;">' + formatBobot(ov.bobot) + '</td></tr>' +
                        '</table>' +
                        '<p class="mt-2 mb-0 text-muted">Gunakan periode yang tidak bersinggungan untuk jabatan ini.</p></div>',
                    customClass: { container: 'swal-on-top' }
                });
                return;
            }
            var msg = respData ? respData.message : 'Terjadi kesalahan saat menyimpan data';
            Swal.fire('Gagal', msg || 'Terjadi kesalahan saat menyimpan data', 'error');
        },
        complete: resetSimpanButton
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
function deletePenugasan(id) {
    deleteTargetId = id;
    $('#deleteConfirmModal').modal('show');
}

function doDelete() {
    if (!deleteTargetId) return;

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
            id: targetId
        },
        success: function (response) {
            if (response.success) {
                lastLoadedData = lastLoadedData.filter(function (item) {
                    return item.id !== parseInt(targetId);
                });
                $row.remove();
                Swal.fire({
                    title: 'Berhasil',
                    text: response.message || 'Data berhasil dihapus',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                // If page is now empty, go back one page
                if ($('#penugasanTableBody tr').length === 0 && currentPage > 1) {
                    currentPage--;
                    loadData();
                }
            } else {
                $row.fadeIn(150);
                Swal.fire('Gagal', response.message || 'Gagal menghapus data', 'error');
            }
        },
        error: function (xhr) {
            $row.fadeIn(150);
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
    formData.append('id_penugasan', currentHeaderData.IDPenugasan);
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
    dupHtml += '<th style="padding:8px 10px;text-align:left;color:#6c757d;">Jabatan</th>';
    dupHtml += '<th style="padding:8px 10px;text-align:center;color:#6c757d;">Periode</th>';
    dupHtml += '<th style="padding:8px 10px;text-align:center;color:#6c757d;">Bobot Lama</th>';
    dupHtml += '</tr></thead><tbody>';

    data.duplicates.forEach(function (dup, i) {
        var bg = i % 2 === 0 ? '#ffffff' : '#f8f9fa';
        dupHtml += '<tr style="background:' + bg + ';border-bottom:1px solid #dee2e6;">' +
            '<td style="padding:8px 10px;"><span style="font-weight:600;color:#0d6efd;">' + escapeHtml(dup.nik) + '</span><br><span style="color:#6c757d;font-size:11px;">' + escapeHtml(dup.nama) + '</span></td>' +
            '<td style="padding:8px 10px;">' + escapeHtml(dup.jabatan || '-') + '</td>' +
            '<td style="padding:8px 10px;text-align:center;font-size:11px;">' + escapeHtml(dup.periode_awal) + '<br>s/d ' + escapeHtml(dup.periode_akhir) + '</td>' +
            '<td style="padding:8px 10px;text-align:center;">' + formatBobot(dup.existing_bobot) + '</td></tr>';
    });

    dupHtml += '</tbody></table></div>';
    dupHtml += '<div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:10px 12px;margin-bottom:12px;">';
    dupHtml += '<span style="color:#198754;"><b>' + data.new_rows + '</b> data baru</span> &nbsp;|&nbsp; ';
    dupHtml += '<span style="color:#dc3545;"><b>' + data.duplicate_count + '</b> data duplikat (bobot akan diganti)</span></div>';

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
    document.getElementById('penugasanTableBody').innerHTML =
        '<tr><td colspan="9" class="text-center py-4">' +
        '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>' +
        '<span class="ms-2 text-muted">Memuat data...</span></td></tr>';
}

function showEmptyState() {
    document.getElementById('penugasanTableBody').innerHTML =
        '<tr><td colspan="9" class="text-center py-4">' +
        '<div class="d-flex flex-column align-items-center">' +
        '<i class="bx bx-search-alt-2 mb-2" style="font-size:48px;color:#ccc;"></i>' +
        '<p class="mb-0 text-muted">Pilih ID Penugasan untuk melihat data</p></div></td></tr>';
    $('#paginationControls').hide();
}

function showErrorState(message) {
    document.getElementById('penugasanTableBody').innerHTML =
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

/**
 * Format bobot value for display.
 * - Whole numbers: "100%"
 * - Decimals: "33.5%"
 */
function formatBobot(value) {
    var num = parseFloat(value) || 0;
    if (num % 1 === 0) {
        return num + '%';
    }
    // Show up to 2 decimal places, trim trailing zeros
    var formatted = num.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
    return formatted + '%';
}
