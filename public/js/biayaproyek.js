/**
 * Biaya Proyek JavaScript
 * Handles Cost Center dropdown, project info display, and Rencana vs Aktual tables
 */

$(document).ready(function () {
    // Global context for HPP detail modal
    window.biayaProyekContext = {
        costCenter: null,
        bulanInput: null,
        periodeAwal: null
    };
    // Initialize Select2 for Cost Center dropdown
    initCostCenterSelect();

    // Event handler for Cost Center selection change
    $('#cost_center_select').on('change', function () {
        const selectedData = $(this).select2('data')[0];
        if (selectedData && selectedData.id) {
            handleCostCenterChange(selectedData);
        } else {
            hideAllSections();
        }
    });
});

/**
 * Initialize Select2 for Cost Center dropdown
 */
function initCostCenterSelect() {
    $('#cost_center_select').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Cost Center --',
        allowClear: true,
        ajax: {
            url: window.routes.getCostCenter,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term || ''
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.text,
                            ...item
                        };
                    })
                };
            },
            cache: false
        },
        minimumInputLength: 0
    });
}

/**
 * Handle Cost Center selection change
 */
function handleCostCenterChange(data) {
    // Update project info section
    updateProjectInfo(data);

    // Show project info section
    $('#projectInfoSection').slideDown();

    // Load Pendapatan and HPP data
    loadBiayaProyekData(data.id_rab);
}

/**
 * Update project info fields
 */
function updateProjectInfo(data) {
    $('#info_cost_center').val(data.cost_center || '-');
    $('#info_namaproject').val(data.namaproject || '-');
    $('#info_konsumen').val(data.konsumen_nama || '-');
    $('#info_nilai_proyek').val(formatCurrency(data.nilai_proyek || 0));
    $('#info_no_kontrak').val(data.no_kontrak || '-');
}

/**
 * Load Biaya Proyek data (Pendapatan and HPP)
 */
function loadBiayaProyekData(idRab) {
    // Show sections with loading state
    $('#pendapatanSection').slideDown();
    $('#hppSection').slideDown();

    // Show loading in tables
    showTableLoading('pendapatanTableBody', 4);
    showTableLoading('hppTableBody', 8);

    $.ajax({
        url: window.routes.getData,
        method: 'GET',
        data: { id_rab: idRab },
        success: function (response) {
            if (response.success) {
                // Update month header for HPP
                if (response.data.current_month) {
                    $('#bulanAwalHeader').text('Bulan ' + response.data.current_month + ' ');
                }
                if (response.data.periode_awal && response.data.current_month) {
                    $('#sdBulanIniHeader').text('Bulan ' + response.data.periode_awal + ' - ' + response.data.current_month + ' ');
                }

                // Store context for HPP detail modal
                window.biayaProyekContext.costCenter = response.data.cost_center;
                window.biayaProyekContext.bulanInput = response.data.bulan_input;
                window.biayaProyekContext.periodeAwal = response.data.periode_awal_raw;

                // Render Pendapatan table (4-col list format)
                renderPendapatanTable(response.data.pendapatan);

                // Render HPP table (8-col Rencana/Aktual format)
                renderHPPTable(response.data.hpp);
            } else {
                showTableError('pendapatanTableBody', response.message || 'Gagal memuat data');
                showTableError('hppTableBody', response.message || 'Gagal memuat data');
            }
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat data';
            showTableError('pendapatanTableBody', message);
            showTableError('hppTableBody', message);
        }
    });
}

/**
 * Render Pendapatan table (4-column list: No, Keterangan/NoBA, Bulan, Nilai)
 */
function renderPendapatanTable(data) {
    const $tbody = $('#pendapatanTableBody');
    const $tfoot = $('#pendapatanTableFoot');

    $tbody.empty();
    $tfoot.empty();

    if (!data || !data.items || data.items.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                    <i class="bx bx-info-circle" style="font-size: 24px;"></i>
                    <p class="mb-0 mt-2">Tidak ada data pendapatan</p>
                </td>
            </tr>
        `);
        return;
    }

    // Render data rows
    data.items.forEach(function (item) {
        const row = `
            <tr>
                <td class="text-center">${item.no}</td>
                <td>${escapeHtml(item.keterangan)}</td>
                <td class="text-center">${escapeHtml(item.bulan)}</td>
                <td class="currency-value">${formatCurrency(item.total)}</td>
            </tr>
        `;
        $tbody.append(row);
    });

    // Grand Total row
    if (data.grand_total !== undefined) {
        const footRow = `
            <tr>
                <td colspan="3" class="text-center">Grand Total</td>
                <td class="currency-value">${formatCurrency(data.grand_total)}</td>
            </tr>
        `;
        $tfoot.append(footRow);
    }
}

/**
 * Render HPP table (8-column Rencana vs Aktual format)
 */
function renderHPPTable(data) {
    const $tbody = $('#hppTableBody');
    const $tfoot = $('#hppTableFoot');

    $tbody.empty();
    $tfoot.empty();

    if (!data || !data.items || data.items.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="bx bx-info-circle" style="font-size: 24px;"></i>
                    <p class="mb-0 mt-2">Tidak ada data HPP</p>
                </td>
            </tr>
        `);
        return;
    }

    // Render data rows
    data.items.forEach(function (item) {
        const row = `
            <tr data-id-spec="${escapeHtml(item.id_spec)}">
                <td class="text-center">${item.no}</td>
                <td>${escapeHtml(item.keterangan)}</td>
                <td class="currency-value hpp-detail-cell" data-type="rencana" data-scope="bulan_ini" style="cursor:pointer" title="Double-click untuk detail">${formatCurrency(item.bulan_ini.rencana)}</td>
                <td class="currency-value hpp-detail-cell" data-type="aktual" data-scope="bulan_ini" style="cursor:pointer" title="Double-click untuk detail">${formatCurrency(item.bulan_ini.aktual)}</td>
                <td class="currency-value hpp-detail-cell" data-type="rencana" data-scope="sd_bulan_ini" style="cursor:pointer" title="Double-click untuk detail">${formatCurrency(item.sd_bulan_ini.rencana)}</td>
                <td class="currency-value hpp-detail-cell" data-type="aktual" data-scope="sd_bulan_ini" style="cursor:pointer" title="Double-click untuk detail">${formatCurrency(item.sd_bulan_ini.aktual)}</td>
                <td class="currency-value hpp-detail-cell" data-type="rencana" data-scope="total" style="cursor:pointer" title="Double-click untuk detail">${formatCurrency(item.total.rencana)}</td>
                <td class="currency-value hpp-detail-cell" data-type="aktual" data-scope="total" style="cursor:pointer" title="Double-click untuk detail">${formatCurrency(item.total.aktual)}</td>
            </tr>
        `;
        $tbody.append(row);
    });

    // Render totals row
    if (data.totals) {
        const footRow = `
            <tr>
                <td colspan="2" class="text-center">Total</td>
                <td class="currency-value">${formatCurrency(data.totals.bulan_ini.rencana)}</td>
                <td class="currency-value">${formatCurrency(data.totals.bulan_ini.aktual)}</td>
                <td class="currency-value">${formatCurrency(data.totals.sd_bulan_ini.rencana)}</td>
                <td class="currency-value">${formatCurrency(data.totals.sd_bulan_ini.aktual)}</td>
                <td class="currency-value">${formatCurrency(data.totals.total.rencana)}</td>
                <td class="currency-value">${formatCurrency(data.totals.total.aktual)}</td>
            </tr>
        `;
        $tfoot.append(footRow);
    }
}

/**
 * Show loading state in table
 */
function showTableLoading(tbodyId, colspan) {
    colspan = colspan || 8;
    $('#' + tbodyId).html(`
        <tr>
            <td colspan="${colspan}" class="text-center py-4">
                <i class="bx bx-loader-alt bx-spin" style="font-size: 24px;"></i>
                <p class="mb-0 mt-2">Loading...</p>
            </td>
        </tr>
    `);
}

/**
 * Show error state in table
 */
function showTableError(tbodyId, message, colspan) {
    colspan = colspan || 8;
    $('#' + tbodyId).html(`
        <tr>
            <td colspan="${colspan}" class="text-center py-4 text-danger">
                <i class="bx bx-error-circle" style="font-size: 24px;"></i>
                <p class="mb-0 mt-2">${escapeHtml(message)}</p>
            </td>
        </tr>
    `);
}

/**
 * Hide all data sections
 */
function hideAllSections() {
    $('#projectInfoSection').slideUp();
    $('#pendapatanSection').slideUp();
    $('#hppSection').slideUp();
}

/**
 * Format number as Indonesian currency
 */
function formatCurrency(value) {
    if (value === null || value === undefined || value === 0) {
        return '-';
    }

    const number = parseFloat(value);
    if (isNaN(number) || number === 0) {
        return '-';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
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

// =========================================================================
// HPP Detail Modal (Double-Click)
// =========================================================================

$(document).on('dblclick', '.hpp-detail-cell', function (e) {
    e.preventDefault();
    // Clear any accidental text selection from double-click
    if (window.getSelection) window.getSelection().removeAllRanges();

    const $cell = $(this);
    const $row = $cell.closest('tr');
    const idSpec = $row.data('id-spec');
    const type = $cell.data('type');
    const scope = $cell.data('scope');
    const ctx = window.biayaProyekContext;

    if (!idSpec || !ctx.costCenter) return;

    // Visual flash feedback on double-click
    $cell.addClass('hpp-cell-flash');
    setTimeout(function () { $cell.removeClass('hpp-cell-flash'); }, 400);

    // Show modal with loading
    const scopeLabels = {
        'bulan_ini': 'Bulan Ini',
        'sd_bulan_ini': 'S.D. Bulan Ini',
        'total': 'Total'
    };
    const typeLabel = type === 'aktual' ? 'Aktual' : 'Rencana';

    $('#detailSpecName').text($row.find('td:eq(1)').text());
    $('#detailType').text(typeLabel);
    $('#detailScope').text(scopeLabels[scope] || scope);
    $('#hppDetailTableHead').empty();
    $('#hppDetailTableBody').html(
        '<tr><td colspan="6" class="text-center py-4">' +
        '<div class="d-flex flex-column align-items-center">' +
        '<div class="spinner-border text-primary mb-2" role="status"></div>' +
        '<span class="text-muted">Memuat data...</span>' +
        '</div></td></tr>'
    );
    $('#hppDetailTableFoot').empty();

    const modal = new bootstrap.Modal(document.getElementById('hppDetailModal'));
    modal.show();

    // Fetch detail data
    $.ajax({
        url: window.routes.getHppDetail,
        method: 'GET',
        data: {
            cost_center: ctx.costCenter,
            id_spec: idSpec,
            type: type,
            scope: scope,
            bulan: ctx.bulanInput,
            periode_awal: ctx.periodeAwal
        },
        success: function (response) {
            if (response.success) {
                renderHPPDetailModal(response.data);
            } else {
                $('#hppDetailTableHead').html('<th>Info</th>');
                $('#hppDetailTableBody').html(
                    '<tr><td class="text-center py-4 text-danger fw-semibold">' +
                    escapeHtml(response.message || 'Gagal memuat data') +
                    '</td></tr>'
                );
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
            $('#hppDetailTableHead').html('<th>Info</th>');
            $('#hppDetailTableBody').html(
                '<tr><td class="text-center py-4 text-danger fw-semibold">' +
                escapeHtml(msg) +
                '</td></tr>'
            );
        }
    });
});

function renderHPPDetailModal(data) {
    const $thead = $('#hppDetailTableHead');
    const $tbody = $('#hppDetailTableBody');
    const $tfoot = $('#hppDetailTableFoot');

    $thead.empty();
    $tbody.empty();
    $tfoot.empty();

    if (!data.items || data.items.length === 0) {
        $thead.html('<th class="fw-semibold" style="color:#212529">No</th><th class="fw-semibold" style="color:#212529">Keterangan</th><th class="fw-semibold" style="color:#212529">Nilai</th>');
        $tbody.html(
            '<tr>' +
            '<td colspan="3" class="text-center py-4">' +
            '<div class="d-flex flex-column align-items-center">' +
            '<span class="bx bx-search-alt-2 mb-2" style="font-size:48px; color:#ccc;"></span>' +
            '<p class="mb-0 text-muted">Tidak ada data detail</p>' +
            '</div></td></tr>'
        );
        return;
    }

    if (data.type === 'aktual') {
        // Aktual columns: No, Cost Center, Tanggal Posting, Cost Element, Keterangan, Nilai
        $thead.html(
            '<th class="text-center fw-semibold detail-no" style="color:#212529">No</th>' +
            '<th class="text-center fw-semibold detail-cc" style="color:#212529">Cost Center</th>' +
            '<th class="text-center fw-semibold" style="color:#212529; width:110px;">Tanggal Posting</th>' +
            '<th class="text-center fw-semibold" style="color:#212529; width:110px;">Cost Element</th>' +
            '<th class="fw-semibold" style="color:#212529; min-width:200px;">Keterangan</th>' +
            '<th class="text-center fw-semibold detail-nilai" style="color:#212529">Nilai</th>'
        );

        data.items.forEach(function (item) {
            $tbody.append(
                '<tr>' +
                '<td class="detail-no">' + item.no + '</td>' +
                '<td class="text-center detail-cc fw-semibold text-primary">' + escapeHtml(item.cost_center) + '</td>' +
                '<td class="text-center" style="white-space:nowrap">' + escapeHtml(item.periode) + '</td>' +
                '<td class="text-center" style="white-space:nowrap">' + escapeHtml(item.cost_element) + '</td>' +
                '<td class="detail-keterangan">' + escapeHtml(item.description_ce) + '</td>' +
                '<td class="detail-nilai">' + formatCurrency(item.nilai) + '</td>' +
                '</tr>'
            );
        });

        $tfoot.html(
            '<tr>' +
            '<td colspan="5" class="text-center fw-bold">Total</td>' +
            '<td class="detail-nilai fw-bold">' + formatCurrency(data.total) + '</td>' +
            '</tr>'
        );
    } else {
        // Rencana columns: No, Cost Center, Bulan, Keterangan, Nilai
        $thead.html(
            '<th class="text-center fw-semibold detail-no" style="color:#212529">No</th>' +
            '<th class="text-center fw-semibold detail-cc" style="color:#212529">Cost Center</th>' +
            '<th class="text-center fw-semibold" style="color:#212529; width:100px;">Bulan</th>' +
            '<th class="fw-semibold" style="color:#212529; min-width:200px;">Keterangan</th>' +
            '<th class="text-center fw-semibold detail-nilai" style="color:#212529">Nilai</th>'
        );

        data.items.forEach(function (item) {
            $tbody.append(
                '<tr>' +
                '<td class="detail-no">' + item.no + '</td>' +
                '<td class="text-center detail-cc fw-semibold text-primary">' + escapeHtml(item.cost_center) + '</td>' +
                '<td class="text-center" style="white-space:nowrap">' + escapeHtml(item.periode) + '</td>' +
                '<td class="detail-keterangan">' + escapeHtml(item.keterangan) + '</td>' +
                '<td class="detail-nilai">' + formatCurrency(item.nilai) + '</td>' +
                '</tr>'
            );
        });

        $tfoot.html(
            '<tr>' +
            '<td colspan="4" class="text-center fw-bold">Total</td>' +
            '<td class="detail-nilai fw-bold">' + formatCurrency(data.total) + '</td>' +
            '</tr>'
        );
    }
}
