/**
 * State Manager - Utility untuk menyimpan dan restore state terakhir
 * Digunakan untuk preserve search, pagination, dan filter state
 */

class StateManager {
    constructor(namespace) {
        this.namespace = namespace;
        this.storageKey = `app_state_${namespace}`;
    }

    /**
     * Save current state to sessionStorage
     */
    saveState(state) {
        try {
            const stateData = {
                ...state,
                timestamp: Date.now()
            };
            sessionStorage.setItem(this.storageKey, JSON.stringify(stateData));
            console.log(`[StateManager] State saved for ${this.namespace}:`, stateData);
        } catch (error) {
            console.error(`[StateManager] Error saving state:`, error);
        }
    }

    /**
     * Get saved state from sessionStorage
     */
    getState() {
        try {
            const stored = sessionStorage.getItem(this.storageKey);
            if (stored) {
                const state = JSON.parse(stored);
                console.log(`[StateManager] State loaded for ${this.namespace}:`, state);
                return state;
            }
        } catch (error) {
            console.error(`[StateManager] Error loading state:`, error);
        }
        return null;
    }

    /**
     * Clear saved state
     */
    clearState() {
        sessionStorage.removeItem(this.storageKey);
        console.log(`[StateManager] State cleared for ${this.namespace}`);
    }

    /**
     * Check if should restore state (coming from edit/create page)
     */
    shouldRestoreState() {
        // Check if there's a state and if we came from a form page
        const referrer = document.referrer;
        const hasState = this.getState() !== null;

        // Check if referrer contains /edit or /create
        const fromForm = referrer.includes('/edit') || referrer.includes('/create');

        return hasState && (fromForm || sessionStorage.getItem(`${this.storageKey}_restore`) === 'true');
    }

    /**
     * Mark that we should restore state on next page load
     */
    markForRestore() {
        sessionStorage.setItem(`${this.storageKey}_restore`, 'true');
    }

    /**
     * Clear restore flag
     */
    clearRestoreFlag() {
        sessionStorage.removeItem(`${this.storageKey}_restore`);
    }

    /**
     * Update specific state property
     */
    updateState(key, value) {
        const currentState = this.getState() || {};
        currentState[key] = value;
        this.saveState(currentState);
    }

    /**
     * Get specific state property
     */
    getStateProperty(key, defaultValue = null) {
        const state = this.getState();
        return state && state[key] !== undefined ? state[key] : defaultValue;
    }
}

/**
 * Global state manager instances
 */
window.StateManagers = {
    dataPeluang: new StateManager('data_peluang'),
    dataProyek: new StateManager('data_proyek'),
    historyProyek: new StateManager('history_proyek'),
    kelolaPM: new StateManager('kelola_pm'),
    progressProyek: new StateManager('progress_proyek'),
    pendapatan: new StateManager('pendapatan'),
    uploadRAB: new StateManager('upload_rab'),
    bidangJasa: new StateManager('bidang_jasa'),
    konsumen: new StateManager('konsumen'),
    masterManager: new StateManager('master_manager'),
    kondisiProyek: new StateManager('kondisi_proyek'),
    spesifikasiRAB: new StateManager('spesifikasi_rab'),
    summaryRAB: new StateManager('summary_rab')
};

// Helper function to get state manager by page
window.getStateManager = function() {
    const path = window.location.pathname;

    if (path.includes('/datapeluang')) return window.StateManagers.dataPeluang;
    if (path.includes('/dataproyek') && path.includes('/show/')) return window.StateManagers.historyProyek;
    if (path.includes('/dataproyek')) return window.StateManagers.dataProyek;
    if (path.includes('/register')) return window.StateManagers.kelolaPM;
    if (path.includes('/progressproyek')) return window.StateManagers.progressProyek;
    if (path.includes('/pendapatan')) return window.StateManagers.pendapatan;
    if (path.includes('/uploadrab')) return window.StateManagers.uploadRAB;
    if (path.includes('/bidangjasa')) return window.StateManagers.bidangJasa;
    if (path.includes('/konsumen')) return window.StateManagers.konsumen;
    if (path.includes('/mastermanager')) return window.StateManagers.masterManager;
    if (path.includes('/kondisiproyek')) return window.StateManagers.kondisiProyek;
    if (path.includes('/spesifikasirab')) return window.StateManagers.spesifikasiRAB;
    if (path.includes('/summaryrab')) return window.StateManagers.summaryRAB;

    return null;
};

console.log('[StateManager] Initialized');
