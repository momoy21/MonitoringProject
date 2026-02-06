/**
 * Biaya Proyek JavaScript
 * Handles Cost Center dropdown, project info display, and Rencana vs Aktual tables
 */

$(document).ready(function () {
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
            cache: true
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
    $('#info_mulai').val(data.mulai || '-');
    $('#info_lama').val(data.lama || '-');
    $('#info_no_kontrak').val(data.no_kontrak || '-');
    $('#info_nilai_proyek').val(formatCurrency(data.nilai_proyek || 0));
    $('#info_tanggal_kontrak').val(data.start_kontrak || '-');
    $('#info_akhir_kontrak').val(data.finish_kontrak || '-');
}

/**
 * Load Biaya Proyek data (Pendapatan and HPP)
 */
function loadBiayaProyekData(idRab) {
    // Show sections with loading state
    $('#pendapatanSection').slideDown();
    $('#hppSection').slideDown();

    // Show loading in tables
    showTableLoading('pendapatanTableBody');
    showTableLoading('hppTableBody');

    $.ajax({
        url: window.routes.getData,
        method: 'GET',
        data: { id_rab: idRab },
        success: function (response) {
            if (response.success) {
                // Update month header and label
                if (response.data.current_month) {
                    $('#bulanIniHeader').text('Bulan Ini (' + response.data.current_month + ')');
                    $('#bulanIniLabel').text('Periode: ' + response.data.current_month);
                }

                // Render Pendapatan table
                renderBiayaTable('pendapatan', response.data.pendapatan);

                // Render HPP table
                renderBiayaTable('hpp', response.data.hpp);
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
 * Render Biaya table (Pendapatan or HPP)
 */
function renderBiayaTable(type, data) {
    const tbodyId = type + 'TableBody';
    const tfootId = type + 'TableFoot';
    const $tbody = $('#' + tbodyId);
    const $tfoot = $('#' + tfootId);

    $tbody.empty();
    $tfoot.empty();

    if (!data || !data.items || data.items.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="bx bx-info-circle" style="font-size: 24px;"></i>
                    <p class="mb-0 mt-2">Tidak ada data</p>
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
                <td class="currency-value">${formatCurrency(item.bulan_ini.rencana)}</td>
                <td class="currency-value">${formatCurrency(item.bulan_ini.aktual)}</td>
                <td class="currency-value">${formatCurrency(item.sd_bulan_ini.rencana)}</td>
                <td class="currency-value">${formatCurrency(item.sd_bulan_ini.aktual)}</td>
                <td class="currency-value">${formatCurrency(item.total.rencana)}</td>
                <td class="currency-value">${formatCurrency(item.total.aktual)}</td>
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
function showTableLoading(tbodyId) {
    $('#' + tbodyId).html(`
        <tr>
            <td colspan="8" class="text-center py-4">
                <i class="bx bx-loader-alt bx-spin" style="font-size: 24px;"></i>
                <p class="mb-0 mt-2">Loading...</p>
            </td>
        </tr>
    `);
}

/**
 * Show error state in table
 */
function showTableError(tbodyId, message) {
    $('#' + tbodyId).html(`
        <tr>
            <td colspan="8" class="text-center py-4 text-danger">
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
