function initStateManagement(manager, pageType) {
    if (pageType === 'index' && manager.stateManager) {
        const savedState = manager.stateManager.getState();

        if (savedState && manager.stateManager.shouldRestoreState()) {
            // Restore state
            manager.currentPage = savedState.currentPage || 1;
            manager.currentSearch = savedState.currentSearch || '';
            manager.perPage = savedState.perPage || 10;

            // Set UI
            $('#searchInput').val(manager.currentSearch);
            $('#perPageSelect').val(manager.perPage);

            manager.stateManager.clearRestoreFlag();
            console.log('[StateManagement] State restored:', savedState);

            // Return true to indicate should reload data
            return true;
        }
    }
    return false;
}

// Save state before loading - call at start of loadData() method
function saveStateBeforeLoad(manager) {
    if (manager.stateManager) {
        manager.stateManager.saveState({
            currentPage: manager.currentPage,
            currentSearch: manager.currentSearch,
            perPage: manager.perPage
        });
    }
}

// Mark for restore on edit - call in form submit success (only for edit)
function markForRestoreOnEdit(manager, isEdit) {
    if (isEdit && manager.stateManager) {
        manager.stateManager.markForRestore();
        console.log('[StateManagement] Marked for restore (edit operation)');
    }
}

// Clear state when creating - call when clicking Tambah button
function clearStateOnCreate(stateManagerName) {
    if (window.StateManagers && window.StateManagers[stateManagerName]) {
        window.StateManagers[stateManagerName].clearState();
        console.log('[StateManagement] State cleared (create operation)');
    }
}

// Mark for restore when going back - call on Kembali/Batal buttons
function markForRestoreOnBack(stateManagerName) {
    if (window.StateManagers && window.StateManagers[stateManagerName]) {
        window.StateManagers[stateManagerName].markForRestore();
        console.log('[StateManagement] Marked for restore (back operation)');
    }
}
