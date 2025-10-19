// resources/js/OfflineManager.js
class OfflineManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.callbacks = {
            onOnline: [],
            onOffline: []
        };

        this.init();
    }

    async init() {
        // Set up event listeners
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }

    // Event handlers
    handleOnline() {
        console.log('App is back online');
        this.isOnline = true;
        this.callbacks.onOnline.forEach(callback => callback());
    }

    handleOffline() {
        console.log('App is offline');
        this.isOnline = false;
        this.callbacks.onOffline.forEach(callback => callback());
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
            isOnline: this.isOnline
        };
    }
}

// Create global instance
const offlineManager = new OfflineManager();

export default offlineManager;