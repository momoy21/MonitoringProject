/**
 * Searchable Select Handler using Select2
 *
 * This file provides functionality to convert regular select fields
 * into searchable dropdown with Select2 library
 */

class SearchableSelect {
    constructor() {
        this.defaultConfig = {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih atau ketik untuk mencari...',
            allowClear: true,
            language: {
                noResults: function () {
                    return "Tidak ditemukan data yang sesuai";
                },
                searching: function () {
                    return "Mencari...";
                },
                loadingMore: function () {
                    return "Memuat lebih banyak hasil...";
                }
            }
        };
    }

    /**
     * Initialize searchable select for specific fields
     * @param {string|array} selectors - CSS selector(s) for the select fields
     * @param {object} customConfig - Custom configuration to override defaults
     */
    init(selectors, customConfig = {}) {
        // Ensure selectors is an array
        if (!Array.isArray(selectors)) {
            selectors = [selectors];
        }

        const config = { ...this.defaultConfig, ...customConfig };

        // Wait for DOM to be ready
        $(document).ready(() => {
            selectors.forEach(selector => {
                this.initializeSelect(selector, config);
            });
        });
    }

    /**
     * Initialize single select field
     * @param {string} selector - CSS selector for the select field
     * @param {object} config - Select2 configuration
     */
    initializeSelect(selector, config) {
        const $select = $(selector);

        if ($select.length === 0) {
            console.warn(`Select field with selector "${selector}" not found`);
            return;
        }

        // Destroy existing Select2 if it exists
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        // Store original placeholder if exists
        const originalPlaceholder = $select.find('option[value=""]').first().text();
        if (originalPlaceholder && originalPlaceholder !== '') {
            config.placeholder = originalPlaceholder;
        }

        // Initialize Select2
        $select.select2(config);

        // Handle form validation styling
        this.handleValidationStyling($select);
    }

    /**
     * Handle Bootstrap validation styling for Select2
     * @param {jQuery} $select - The select element
     */
    handleValidationStyling($select) {
        const $container = $select.next('.select2-container');

        // Check if original select has validation classes
        if ($select.hasClass('is-invalid')) {
            $container.find('.select2-selection').addClass('is-invalid');
        }

        if ($select.hasClass('is-valid')) {
            $container.find('.select2-selection').addClass('is-valid');
        }

        // Listen for validation class changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const $selection = $container.find('.select2-selection');

                    if ($select.hasClass('is-invalid')) {
                        $selection.addClass('is-invalid').removeClass('is-valid');
                    } else if ($select.hasClass('is-valid')) {
                        $selection.addClass('is-valid').removeClass('is-invalid');
                    } else {
                        $selection.removeClass('is-invalid is-valid');
                    }
                }
            });
        });

        observer.observe($select[0], {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    /**
     * Initialize searchable select for common form fields
     * This is a convenience method for the most common use cases
     */
    initCommonFields() {
        // Konsumen fields
        this.init('#id_konsumen', {
            placeholder: 'Pilih atau ketik nama konsumen...'
        });

        // Data Peluang fields
        this.init('#id_datapeluang', {
            placeholder: 'Pilih atau ketik nama peluang...'
        });

        // Penanggung Jawab / Manager fields
        this.init('#penanggung_jawab', {
            placeholder: 'Pilih atau ketik nama penanggung jawab...'
        });

        // Bidang Jasa fields
        this.init('#id_bidjasa', {
            placeholder: 'Pilih atau ketik bidang jasa...'
        });

        // Kondisi Proyek fields
        this.init('#id_kondisi_proyek', {
            placeholder: 'Pilih atau ketik kondisi proyek...'
        });

        // Note: #cost_center_proyek excluded because it has custom AJAX configuration
        // in progressproyek.js and rab/upload.blade.php

        // Provinsi fields
        this.init('#provinsi_id', {
            placeholder: 'Pilih atau ketik nama provinsi...'
        });

        // Kota fields
        this.init('#kota_id', {
            placeholder: 'Pilih atau ketik nama kota...'
        });
    }

    /**
     * Destroy Select2 for specific fields
     * @param {string|array} selectors - CSS selector(s) for the select fields
     */
    destroy(selectors) {
        if (!Array.isArray(selectors)) {
            selectors = [selectors];
        }

        selectors.forEach(selector => {
            const $select = $(selector);
            if ($select.length > 0 && $select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
        });
    }

    /**
     * Refresh/reinitialize Select2 for specific fields
     * Useful after dynamic content loading
     * @param {string|array} selectors - CSS selector(s) for the select fields
     */
    refresh(selectors) {
        this.destroy(selectors);
        this.init(selectors);
    }
}

// Create global instance
window.searchableSelect = new SearchableSelect();

// Auto-initialize common fields when document is ready
$(document).ready(function() {
    // Check if Select2 is available
    if (typeof $.fn.select2 === 'undefined') {
        console.warn('Select2 library is not loaded. Searchable select functionality will not work.');
        return;
    }

    // Auto-initialize common form fields
    window.searchableSelect.initCommonFields();
});

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SearchableSelect;
}
