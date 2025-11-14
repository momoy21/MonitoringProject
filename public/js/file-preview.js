/**
 * File Preview Handler
 *
 * This component provides file preview functionality for uploaded documents
 * Supports: PDF, Images, and basic text preview for other formats
 */

class FilePreview {
    constructor() {
        this.supportedPreviewTypes = {
            pdf: ['pdf'],
            image: ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
            office: ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']
        };

        this.currentPreviewModal = null;
        this.initializeStyles();
    }

    /**
     * Initialize required CSS styles
     */
    initializeStyles() {
        if (!document.getElementById('file-preview-styles')) {
            const styles = `
                <style id="file-preview-styles">
                    .file-preview-modal .modal-dialog {
                        max-width: 90vw;
                        max-height: 90vh;
                    }

                    .file-preview-content {
                        width: 100%;
                        height: 70vh;
                        border: none;
                        border-radius: 8px;
                    }

                    .file-preview-image {
                        max-width: 100%;
                        max-height: 70vh;
                        object-fit: contain;
                        border-radius: 8px;
                    }

                    .file-preview-office {
                        width: 100%;
                        height: 70vh;
                        border: none;
                        border-radius: 8px;
                        background: #f8f9fa;
                    }

                    .file-preview-info {
                        background: #f8f9fa;
                        padding: 20px;
                        border-radius: 8px;
                        text-align: center;
                    }

                    .file-preview-actions {
                        display: flex;
                        gap: 10px;
                        justify-content: center;
                        margin-top: 15px;
                        flex-wrap: wrap;
                    }

                    .file-preview-error {
                        color: #dc3545;
                        text-align: center;
                        padding: 30px;
                    }

                    .file-preview-loading {
                        text-align: center;
                        padding: 50px;
                    }

                    @media (max-width: 768px) {
                        .file-preview-modal .modal-dialog {
                            max-width: 95vw;
                            margin: 10px;
                        }

                        .file-preview-content {
                            height: 60vh;
                        }

                        .file-preview-actions {
                            flex-direction: column;
                        }
                    }
                </style>
            `;
            document.head.insertAdjacentHTML('beforeend', styles);
        }
    }

    /**
     * Show file preview modal
     * @param {string} fileUrl - URL to the file
     * @param {string} fileName - Name of the file
     * @param {string} downloadUrl - Download URL
     */
    showPreview(fileUrl, fileName, downloadUrl = null) {
        console.log('FilePreview showPreview called:', { fileUrl, fileName, downloadUrl });

        const fileExtension = this.getFileExtension(fileName);
        const fileType = this.getFileType(fileExtension);

        console.log('File type detected:', fileType, 'Extension:', fileExtension);

        this.createPreviewModal(fileName, downloadUrl);
        this.loadFileContent(fileUrl, fileType, fileName);

        $('#filePreviewModal').modal('show');
    }

    /**
     * Create preview modal
     */
    createPreviewModal(fileName, downloadUrl) {
        // Remove existing modal
        $('#filePreviewModal').remove();

        const downloadButton = downloadUrl ?
            `<a href="${downloadUrl}" class="btn btn-primary" target="_blank">
                <i class="bx bx-download me-1"></i> Download
            </a>` : '';

        const modalHtml = `
            <div class="modal fade file-preview-modal" id="filePreviewModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bx bx-file me-2"></i>Preview: ${this.escapeHtml(fileName)}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div id="previewContent" class="p-3">
                                <div class="file-preview-loading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-3">Memuat preview file...</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="file-preview-actions">
                                ${downloadButton}
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i> Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);
        this.currentPreviewModal = $('#filePreviewModal');
    }

    /**
     * Load file content based on type
     */
    loadFileContent(fileUrl, fileType, fileName) {
        const container = $('#previewContent');

        switch (fileType) {
            case 'pdf':
                this.loadPdfPreview(container, fileUrl);
                break;

            case 'image':
                this.loadImagePreview(container, fileUrl, fileName);
                break;

            case 'office':
                this.loadOfficePreview(container, fileUrl, fileName);
                break;

            default:
                this.loadUnsupportedPreview(container, fileName);
                break;
        }
    }

    /**
     * Load PDF preview
     */
    loadPdfPreview(container, fileUrl) {
        console.log('Loading PDF preview for URL:', fileUrl);

        const content = `
            <iframe src="${fileUrl}#toolbar=1&navpanes=1&scrollbar=1"
                    class="file-preview-content"
                    title="PDF Preview"
                    onload="console.log('PDF iframe loaded')"
                    onerror="console.error('PDF iframe error')">
            </iframe>
        `;
        container.html(content);
    }

    /**
     * Load image preview
     */
    loadImagePreview(container, fileUrl, fileName) {
        console.log('Loading image preview for URL:', fileUrl);

        const img = new Image();

        img.onload = () => {
            console.log('Image loaded successfully');
            const content = `
                <div class="text-center">
                    <img src="${fileUrl}" alt="${this.escapeHtml(fileName)}" class="file-preview-image">
                </div>
            `;
            container.html(content);
        };

        img.onerror = () => {
            console.error('Failed to load image:', fileUrl);
            this.showPreviewError(container, 'Gagal memuat gambar. URL: ' + fileUrl);
        };

        img.src = fileUrl;
    }

    /**
     * Load Office document preview
     */
    loadOfficePreview(container, fileUrl, fileName) {
        console.log('Loading Office document preview for URL:', fileUrl);

        // Try Microsoft Office Online viewer
        const officeViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
        console.log('Office viewer URL:', officeViewerUrl);

        const content = `
            <div class="file-preview-info">
                <i class="bx bx-file-blank" style="font-size: 48px; color: #6c757d;"></i>
                <h6 class="mt-3">${this.escapeHtml(fileName)}</h6>
                <p class="text-muted mb-3">Preview dokumen Office tidak tersedia secara langsung</p>
                <div class="file-preview-actions">
                    <button type="button" class="btn btn-outline-primary" onclick="window.filePreview.tryOfficeViewer('${officeViewerUrl}')">
                        <i class="bx bx-show me-1"></i> Coba Preview Online
                    </button>
                </div>
            </div>
        `;
        container.html(content);
    }

    /**
     * Try Office online viewer in new tab
     */
    tryOfficeViewer(viewerUrl) {
        const newWindow = window.open(viewerUrl, '_blank');
        if (!newWindow) {
            this.showAlert('Pop-up diblokir. Silakan download file untuk melihat konten.', 'warning');
        }
    }

    /**
     * Load unsupported file preview
     */
    loadUnsupportedPreview(container, fileName) {
        const content = `
            <div class="file-preview-info">
                <i class="bx bx-file" style="font-size: 48px; color: #6c757d;"></i>
                <h6 class="mt-3">${this.escapeHtml(fileName)}</h6>
                <p class="text-muted">Preview tidak tersedia untuk tipe file ini</p>
                <p class="small text-muted">Silakan download file untuk melihat konten</p>
            </div>
        `;
        container.html(content);
    }

    /**
     * Show preview error
     */
    showPreviewError(container, message) {
        console.error('Preview error:', message);

        const content = `
            <div class="file-preview-error text-center">
                <i class="bx bx-error-circle" style="font-size: 48px; color: #dc3545;"></i>
                <h6 class="mt-3">Error Preview</h6>
                <p>${this.escapeHtml(message)}</p>
                <small class="text-muted">Cek console browser untuk detail error</small>
            </div>
        `;
        container.html(content);
    }

    /**
     * Get file extension from filename
     */
    getFileExtension(fileName) {
        return fileName.split('.').pop().toLowerCase();
    }

    /**
     * Determine file type for preview
     */
    getFileType(extension) {
        if (this.supportedPreviewTypes.pdf.includes(extension)) {
            return 'pdf';
        } else if (this.supportedPreviewTypes.image.includes(extension)) {
            return 'image';
        } else if (this.supportedPreviewTypes.office.includes(extension)) {
            return 'office';
        }
        return 'unsupported';
    }

    /**
     * Generate preview button HTML
     */
    generatePreviewButton(fileUrl, fileName, downloadUrl = null, buttonClass = 'btn-outline-info btn-sm') {
        return `
            <button type="button" class="btn ${buttonClass}"
                    onclick="window.filePreview.showPreview('${fileUrl}', '${this.escapeHtml(fileName)}', '${downloadUrl || ''}')">
                <i class="bx bx-show me-1"></i> Preview
            </button>
        `;
    }

    /**
     * Generate download and preview buttons
     */
    generateFileButtons(fileUrl, fileName, downloadUrl = null) {
        const previewBtn = this.generatePreviewButton(fileUrl, fileName, downloadUrl);
        const downloadBtn = downloadUrl ?
            `<a href="${downloadUrl}" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="bx bx-download me-1"></i> Download
            </a>` : '';

        return `
            <div class="btn-group" role="group">
                ${previewBtn}
                ${downloadBtn}
            </div>
        `;
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Show alert message
     */
    showAlert(message, type = 'info') {
        if (typeof window.showAlert === 'function') {
            window.showAlert(message, type);
        } else {
            alert(message);
        }
    }
}

// Create global instance
window.filePreview = new FilePreview();

// Debug log to ensure it's loaded
console.log('FilePreview loaded:', typeof window.filePreview);

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FilePreview;
}
