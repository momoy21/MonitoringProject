/**
 * Issue Project JavaScript Module
 * Handles CRUD operations for Issue tab in Progress Project
 * FIXED: Mengikuti logic Berita Acara - edit langsung update data, tidak membuat data baru
 */

$(document).ready(function() {
    let currentProjectData = null;
    let issueData = [];
    let isReadOnly = false;
    let isPM = false;
    let deleteNoIssue = null;

    checkUserRole();

    // CRITICAL FIX: Cleanup HANYA modal Issue, bukan semua modal
    $('#issueModal').on('hidden.bs.modal', function() {
        console.log('Issue modal hidden - cleaning up');
        $(this).next('.modal-backdrop').remove();

        if ($('.modal.show').length === 0) {
            $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
            $('.modal-backdrop').remove();
        }
        $(this).removeData('bs.modal');
    });

    // CRITICAL FIX: Tab cleanup lebih selektif
    $('#tab-issue').on('show.bs.tab', function() {
        console.log('Tab Issue about to show - selective cleanup');
        $('#baModal, #viewBAModal, #deleteBAConfirmModal').each(function() {
            if ($(this).hasClass('show')) {
                console.log('Closing BA modal:', this.id);
                $(this).modal('hide');
            }
        });

        setTimeout(function() {
            if ($('.modal.show').length === 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
            }
        }, 350);
    });

    // Load Issue when tab is shown
    $('#tab-issue').on('shown.bs.tab', function() {
        console.log('Tab Issue shown, currentProjectData:', currentProjectData);
        if (currentProjectData && currentProjectData.id_project && currentProjectData.norut) {
            console.log('Loading Issue data because tab was shown');
            loadIssueData();
        } else {
            console.warn('No currentProjectData available or incomplete:', currentProjectData);
        }
    });

    window.initIssue = function(projectData) {
        console.log('initIssue called with:', projectData);
        currentProjectData = projectData;
        loadIssueData();
    };

    function checkUserRole() {
        isReadOnly = window.userRole !== 'Super Admin' && window.userRole !== 'Project Manager';
        isPM = window.userRole === 'Project Manager';
        console.log('User role:', window.userRole, 'isReadOnly:', isReadOnly, 'isPM:', isPM);
    }

    function loadIssueData() {
        console.log('loadIssueData called with currentProjectData:', currentProjectData);
        if (!currentProjectData || !currentProjectData.id_project || !currentProjectData.norut) {
            console.error('Missing project data:', currentProjectData);
            showAlert('Data proyek tidak lengkap', 'error');
            return;
        }

        $.ajax({
            url: window.routes.getIssue,
            method: 'GET',
            data: {
                id_project: currentProjectData.id_project,
                norut: currentProjectData.norut
            },
            success: function(response) {
                console.log('Issue data response:', response);
                if (response.success) {
                    issueData = response.data;
                    console.log('Issue data loaded, count:', issueData.length);
                    renderIssueTable();
                } else {
                    showAlert(response.message || 'Gagal memuat data', 'error');
                }
            },
            error: function(xhr) {
                console.error('Error loading issue:', xhr);
                showAlert('Terjadi kesalahan saat memuat data', 'error');
            }
        });
    }

    function renderIssueTable() {
        console.log('renderIssueTable called, data length:', issueData ? issueData.length : 0);
        const $container = $('#issueTableContainer');
        const $headerControls = $('#issueHeaderControls');

        $headerControls.show().css('display', 'flex');

        if (!issueData || issueData.length === 0) {
            $container.html(`
                <div class="text-center py-5">
                    <i class="bx bx-error-circle" style="font-size: 64px; color: #d9dee3;"></i>
                    <p class="text-muted mt-3 mb-1" style="font-size: 1rem; font-weight: 500;">Belum ada data Issue</p>
                    <p class="text-muted" style="font-size: 0.875rem;">Klik tombol "Tambah Issue" untuk membuat data baru</p>
                </div>
            `);
            return;
        }

        let tableHTML = `
            <div class="table-responsive issue-table-container">
                <table class="table table-hover issue-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Issue</th>
                            <th>Mitigasi</th>
                            <th>Status</th>
                            ${(!isReadOnly && !isPM) ? '<th>Aksi</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
        `;

        const totalCount = issueData.length;

        issueData.forEach((issue, index) => {
            const noUrut = totalCount - index;

            const statusOptions = `
                <option value="O" ${issue.status === 'O' ? 'selected' : ''}>Open</option>
                <option value="C" ${issue.status === 'C' ? 'selected' : ''}>Close</option>
            `;

            const statusBadge = issue.status === 'O'
                ? '<span class="badge bg-warning">Open</span>'
                : '<span class="badge bg-success">Close</span>';

            const deleteButton = `
                <li><a class="dropdown-item text-danger" href="#" onclick="confirmDeleteIssue('${issue.no_issue}'); return false;">
                    <i class="bx bx-trash me-2"></i>Hapus
                </a></li>
            `;

            const actionColumn = (!isReadOnly && !isPM) ? `
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">
                            ${deleteButton}
                        </ul>
                    </div>
                </td>
            ` : '';

            tableHTML += `
                <tr>
                    <td${!isReadOnly ? ` ondblclick="editIssue('${issue.no_issue}')" style="cursor: pointer;" title="Double klik untuk edit"` : ''}>
                        <span class="issue-no-urut">
                            ${noUrut}
                        </span>
                    </td>
                    <td>${formatDate(issue.tanggal)}</td>
                    <td><div class="issue-desc-cell">${escapeHtml(issue.issue)}</div></td>
                    <td><div class="issue-desc-cell">${escapeHtml(issue.mitigasi)}</div></td>
                    <td>
                        ${isReadOnly ? statusBadge : `
                            <select class="form-select form-select-sm status-dropdown" onchange="handleStatusChange(this, '${issue.no_issue}')" onclick="event.stopPropagation()">
                                ${statusOptions}
                            </select>
                        `}
                    </td>
                    ${actionColumn}
                </tr>
            `;
        });

        tableHTML += `
                    </tbody>
                </table>
            </div>
        `;

        $container.html(tableHTML);
    }

    // CRITICAL FIX: Modal handling mengikuti logic BA
    function showIssueModal(issueObj = null) {
        const isEdit = issueObj !== null;
        console.log('=== showIssueModal START ===');
        console.log('showIssueModal called, isEdit:', isEdit, 'data:', issueObj);

        // Tutup modal BA yang mungkin masih terbuka
        $('#baModal, #viewBAModal').each(function() {
            if ($(this).hasClass('show')) {
                console.log('Closing BA modal before showing Issue modal');
                $(this).modal('hide');
            }
        });

        // Wait untuk cleanup BA selesai
        setTimeout(function() {
            console.log('Opening Issue modal...');

            const todayDate = new Date().toISOString().split('T')[0];
            const issueValue = isEdit ? (issueObj.issue || '') : '';
            const mitigasiValue = isEdit ? (issueObj.mitigasi || '') : '';
            const statusValue = isEdit ? (issueObj.status || 'O') : 'O';

            // Tanggal section - hanya untuk create, tidak untuk edit (SAMA SEPERTI BA)
            const tanggalSection = !isEdit ? `
                <div class="form-section">
                    <h6 class="mb-3"><i class="bx bx-calendar me-2"></i>Tanggal Issue</h6>
                    <div class="mb-3">
                        <label for="issueTanggal" class="form-label">
                            Tanggal <small class="text-muted">(otomatis tanggal hari ini)</small>
                        </label>
                        <input type="date"
                               class="form-control readonly-field"
                               id="issueTanggal"
                               value="${todayDate}"
                               readonly disabled>
                    </div>
                </div>
            ` : '';

            const modalHTML = `
                <div class="modal fade header-rab-modal" id="issueModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${isEdit ? 'Edit Issue' : 'Tambah Issue'}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="issueForm">
                                    <input type="hidden" id="issue_no_issue" value="${isEdit ? issueObj.no_issue : ''}">

                                    ${tanggalSection}

                                    <div class="form-section">
                                        <h6 class="mb-3"><i class="bx bx-error-circle me-2"></i>Detail Issue</h6>

                                        <div class="mb-3">
                                            <label for="issueText" class="form-label">Issue / Kendala</label>
                                            <textarea class="form-control"
                                                      id="issueText"
                                                      rows="4"
                                                      placeholder="Jelaskan issue atau kendala yang terjadi..."
                                                      style="resize: vertical;">${issueValue}</textarea>
                                            <small class="text-muted">Jika kosong akan tersimpan sebagai "Tidak ada issue"</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="mitigasiText" class="form-label">Mitigasi</label>
                                            <textarea class="form-control ${isPM ? 'readonly-field' : ''}"
                                                      id="mitigasiText"
                                                      rows="4"
                                                      placeholder="Jelaskan langkah mitigasi yang dilakukan..."
                                                      style="resize: vertical;"
                                                      ${isPM ? 'readonly disabled' : ''}>${mitigasiValue}</textarea>
                                            <small class="text-muted">
                                                ${isPM ? 'Hanya Super Admin yang dapat mengedit field ini' : 'Jika kosong akan tersimpan sebagai "Tidak ada mitigasi"'}
                                            </small>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="issueStatus" class="form-label">Status</label>
                                                <select class="form-select" id="issueStatus">
                                                    <option value="O" ${statusValue === 'O' ? 'selected' : ''}>Open</option>
                                                    <option value="C" ${statusValue === 'C' ? 'selected' : ''}>Close</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i> Batal
                                </button>
                                <button type="button" class="btn btn-primary" id="btnSaveIssue">
                                    <i class="bx bx-check me-1"></i> ${isEdit ? 'Update' : 'Simpan'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal
            if ($('#issueModal').length > 0) {
                $('#issueModal').modal('hide');
                $('#issueModal').remove();
            }
            $('.modal-backdrop').remove();

            $('body').append(modalHTML);

            const modal = new bootstrap.Modal(document.getElementById('issueModal'));

            $('#issueModal').on('shown.bs.modal', function() {
                console.log('Issue Modal shown, isEdit:', isEdit);

                $('#btnSaveIssue').off('click').on('click', function() {
                    console.log('Save button clicked, isEdit:', isEdit);
                    saveIssue(isEdit);
                });

                setTimeout(() => {
                    $('#issueText').focus();
                }, 100);
            });

            modal.show();
        }, 400);
    }

    // Add Issue button
    $(document).off('click', '#btnAddIssue').on('click', '#btnAddIssue', function() {
        console.log('Add Issue button clicked');
        if (isReadOnly) {
            showAlert('Anda tidak memiliki akses untuk menambah data', 'error');
            return;
        }
        showIssueModal();
    });

    // CRITICAL FIX: Save function mengikuti logic BA
    function saveIssue(isEdit) {
        console.log('saveIssue called with isEdit:', isEdit);

        const issueText = $('#issueText').val();
        const mitigasiText = $('#mitigasiText').val();
        const status = $('#issueStatus').val();

        const data = {
            _token: window.csrfToken,
            id_project: currentProjectData.id_project,
            norut: parseInt(currentProjectData.norut),
            issue: issueText,
            mitigasi: mitigasiText,
            status: status
        };

        // Tambahkan tanggal HANYA untuk mode create (SAMA SEPERTI BA)
        if (!isEdit) {
            const todayDate = new Date().toISOString().split('T')[0];
            data.tanggal = todayDate;
        }

        // CRITICAL: URL dan method mengikuti logic BA
        const url = isEdit ?
            window.routes.updateIssue.replace('{noIssue}', $('#issue_no_issue').val()) :
            window.routes.storeIssue;

        const method = isEdit ? 'PUT' : 'POST';

        console.log('Submitting to:', url, 'method:', method, 'data:', data);

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#issueModal').modal('hide');
                    loadIssueData(); // Reload untuk melihat data yang sudah diupdate
                } else {
                    showAlert(response.message || 'Gagal menyimpan data', 'error');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                let errorMsg = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showAlert(errorMsg, 'error');
            }
        });
    }

    // Delete Issue
    window.confirmDeleteIssue = function(noIssue) {
        console.log('confirmDeleteIssue called with:', noIssue);
        deleteNoIssue = noIssue;
        $('#deleteIssueConfirmModal').modal('show');
    };

    $(document).off('click', '#confirmDeleteIssueBtn').on('click', '#confirmDeleteIssueBtn', function() {
        console.log('Delete confirmed for noIssue:', deleteNoIssue);
        performDeleteIssue();
    });

    function performDeleteIssue() {
        if (!deleteNoIssue) return;

        $.ajax({
            url: window.routes.deleteIssue.replace('{noIssue}', deleteNoIssue),
            method: 'DELETE',
            data: {
                _token: window.csrfToken,
                id_project: currentProjectData.id_project,
                norut: parseInt(currentProjectData.norut)
            },
            success: function(response) {
                $('#deleteIssueConfirmModal').modal('hide');
                if (response.success) {
                    showAlert('Issue berhasil dihapus', 'success');
                    loadIssueData();
                } else {
                    showAlert(response.message || 'Gagal menghapus data', 'error');
                }
                deleteNoIssue = null;
            },
            error: function(xhr) {
                $('#deleteIssueConfirmModal').modal('hide');
                showAlert('Terjadi kesalahan saat menghapus data', 'error');
                deleteNoIssue = null;
            }
        });
    }

    // Status change
    window.handleStatusChange = function(selectElement, noIssue) {
        const newStatus = $(selectElement).val();
        console.log('Status change for noIssue:', noIssue, 'new status:', newStatus);
        updateStatus(noIssue, newStatus);
    };

    function updateStatus(noIssue, newStatus) {
        $.ajax({
            url: window.routes.updateStatusIssue,
            method: 'POST',
            data: {
                _token: window.csrfToken,
                no_issue: noIssue,
                id_project: currentProjectData.id_project,
                norut: parseInt(currentProjectData.norut),
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Status berhasil diperbarui', 'success');
                    loadIssueData();
                } else {
                    showAlert(response.message || 'Gagal memperbarui status', 'error');
                }
            },
            error: function(xhr) {
                showAlert('Terjadi kesalahan saat memperbarui status', 'error');
                loadIssueData();
            }
        });
    }

    // CRITICAL: Edit function mengikuti logic BA
    window.editIssue = function(noIssue) {
        console.log('editIssue called with noIssue:', noIssue, 'isReadOnly:', isReadOnly);
        if (isReadOnly) {
            console.log('User is readonly, edit blocked');
            return;
        }
        const issue = issueData.find(i => i.no_issue === noIssue);
        console.log('Found Issue data for edit:', issue);
        if (issue) {
            console.log('Calling showIssueModal for edit mode...');
            showIssueModal(issue); // Pass data untuk edit mode
        } else {
            console.error('Issue data not found for noIssue:', noIssue);
        }
    };

    // Utility functions
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
