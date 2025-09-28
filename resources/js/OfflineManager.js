// resources/js/OfflineManager.js
class OfflineManager {
    constructor() {
        this.db = null;
        this.isOnline = navigator.onLine;
        this.syncInProgress = false;
        this.callbacks = {
            onOnline: [],
            onOffline: [],
            onSyncComplete: []
        };

        this.init();
    }

    async init() {
        // Initialize IndexedDB
        await this.openDB();

        // Set up event listeners
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());

        // Listen for service worker messages
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data.type === 'SYNC_COMPLETE') {
                    this.handleSyncComplete();
                }
            });
        }

        // Check if we have offline data to sync
        if (this.isOnline) {
            this.syncOfflineData();
        }
    }

    async openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open('ExpenseTrackerDB', 1);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(request.result);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Create object stores for offline data
                if (!db.objectStoreNames.contains('offline_transactions')) {
                    const store = db.createObjectStore('offline_transactions', { keyPath: 'id', autoIncrement: true });
                    store.createIndex('timestamp', 'timestamp');
                    store.createIndex('synced', 'synced');
                }

                if (!db.objectStoreNames.contains('offline_categories')) {
                    const store = db.createObjectStore('offline_categories', { keyPath: 'id', autoIncrement: true });
                    store.createIndex('timestamp', 'timestamp');
                }

                if (!db.objectStoreNames.contains('app_data')) {
                    const store = db.createObjectStore('app_data', { keyPath: 'key' });
                }
            };
        });
    }

    // Store transaction offline
    async storeTransactionOffline(transactionData) {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['offline_transactions'], 'readwrite');
        const store = transaction.objectStore('offline_transactions');

        const offlineTransaction = {
            data: transactionData,
            timestamp: Date.now(),
            synced: false,
            id: `offline_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
        };

        await store.add(offlineTransaction);

        console.log('Transaction stored offline:', offlineTransaction.id);
        return offlineTransaction;
    }

    // Store category offline
    async storeCategoryOffline(categoryData) {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['offline_categories'], 'readwrite');
        const store = transaction.objectStore('offline_categories');

        const offlineCategory = {
            data: categoryData,
            timestamp: Date.now(),
            synced: false,
            id: `offline_cat_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
        };

        await store.add(offlineCategory);

        console.log('Category stored offline:', offlineCategory.id);
        return offlineCategory;
    }

    // Get all offline transactions
    async getOfflineTransactions() {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['offline_transactions'], 'readonly');
        const store = transaction.objectStore('offline_transactions');

        return new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // Get offline transaction count
    async getOfflineTransactionCount() {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['offline_transactions'], 'readonly');
        const store = transaction.objectStore('offline_transactions');

        return new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => {
                const transactions = request.result;
                const unsyncedCount = transactions.filter(t => !t.synced).length;
                resolve(unsyncedCount);
            };
            request.onerror = () => reject(request.error);
        });
    }

    // Sync offline data to server
    async syncOfflineData() {
        if (this.syncInProgress || !this.isOnline) return;

        this.syncInProgress = true;

        try {
            await this.syncOfflineTransactions();
            await this.syncOfflineCategories();

            // Notify service worker to clean up
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({ type: 'SYNC_DATA' });
            }

        } catch (error) {
            console.error('Sync failed:', error);
        } finally {
            this.syncInProgress = false;
        }
    }

    async syncOfflineTransactions() {
        const offlineTransactions = await this.getOfflineTransactions();
        const unsyncedTransactions = offlineTransactions.filter(t => !t.synced);

        // console.log(`Syncing ${unsyncedTransactions.length} offline transactions...`);

        for (const transaction of unsyncedTransactions) {
            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch('/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(transaction.data)
                });

                if (response.ok) {
                    // Mark as synced
                    await this.markTransactionSynced(transaction.id);
                    console.log('Synced transaction:', transaction.id);
                } else {
                    console.error('Failed to sync transaction:', transaction.id, response.statusText);
                }
            } catch (error) {
                console.error('Error syncing transaction:', transaction.id, error);
            }
        }
    }

    async syncOfflineCategories() {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['offline_categories'], 'readonly');
        const store = transaction.objectStore('offline_categories');

        const offlineCategories = await new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });

        const unsyncedCategories = offlineCategories.filter(c => !c.synced);

        // console.log(`Syncing ${unsyncedCategories.length} offline categories...`);

        for (const category of unsyncedCategories) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch('/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(category.data)
                });

                if (response.ok) {
                    await this.markCategorySynced(category.id);
                    console.log('Synced category:', category.id);
                }
            } catch (error) {
                console.error('Error syncing category:', category.id, error);
            }
        }
    }

    async markTransactionSynced(id) {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['offline_transactions'], 'readwrite');
        const store = transaction.objectStore('offline_transactions');

        const request = store.get(id);
        request.onsuccess = () => {
            const data = request.result;
            if (data) {
                data.synced = true;
                store.put(data);
            }
        };
    }

    async markCategorySynced(id) {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['offline_categories'], 'readwrite');
        const store = transaction.objectStore('offline_categories');

        const request = store.get(id);
        request.onsuccess = () => {
            const data = request.result;
            if (data) {
                data.synced = true;
                store.put(data);
            }
        };
    }

    // Event handlers
    handleOnline() {
        console.log('App is back online');
        this.isOnline = true;
        this.syncOfflineData();
        this.callbacks.onOnline.forEach(callback => callback());
    }

    handleOffline() {
        console.log('App is offline');
        this.isOnline = false;
        this.callbacks.onOffline.forEach(callback => callback());
    }

    handleSyncComplete() {
        console.log('Sync completed');
        this.callbacks.onSyncComplete.forEach(callback => callback());
    }

    // Public methods for components to use
    on(event, callback) {
        if (this.callbacks[event]) {
            this.callbacks[event].push(callback);
        }
    }

    off(event, callback) {
        if (this.callbacks[event]) {
            const index = this.callbacks[event].indexOf(callback);
            if (index > -1) {
                this.callbacks[event].splice(index, 1);
            }
        }
    }

    getStatus() {
        return {
            isOnline: this.isOnline,
            syncInProgress: this.syncInProgress
        };
    }

    // Store app data (like last sync time, settings, etc.)
    async storeAppData(key, value) {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['app_data'], 'readwrite');
        const store = transaction.objectStore('app_data');

        await store.put({ key, value, timestamp: Date.now() });
    }

    async getAppData(key) {
        if (!this.db) await this.openDB();

        const transaction = this.db.transaction(['app_data'], 'readonly');
        const store = transaction.objectStore('app_data');

        return new Promise((resolve, reject) => {
            const request = store.get(key);
            request.onsuccess = () => resolve(request.result?.value);
            request.onerror = () => reject(request.error);
        });
    }
}

// Create global instance
const offlineManager = new OfflineManager();

export default offlineManager;
