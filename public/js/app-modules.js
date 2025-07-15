/**
 * JavaScript Module Loader for Web Booking Application
 * This file provides utilities for managing page-specific JavaScript files
 */

class JavaScriptManager {
    constructor() {
        this.loadedModules = new Set();
        this.pageModules = {
            // Admin pages
            'admin.payouts.index': 'admin-payouts-index.js',
            'admin.payouts.show': 'admin-payouts-show.js',
            'admin.refunds': 'admin-refunds.js',
            'admin.refund-details': 'admin-refund-details.js',
            'admin.tutors.show': 'admin-tutors-show.js',

            // Booking pages
            'bookings.create': 'booking-create.js',
            'bookings.payment': 'bookings-payment.js',
            'bookings.show': 'bookings-show.js',
            'bookings.refund-confirm': 'bookings-refund-confirm.js',

            // Tutor pages
            'tutors.show': 'tutors-show.js',
            'tutors.availability': 'tutors-availability.js',
            'tutors.earnings.index': 'tutors-earnings-index.js',
            'tutors.earnings.create-payout': 'tutors-earnings-create-payout.js',
            'tutors.earnings.history': 'tutors-earnings-history.js',
            'tutors.earnings.payout-details': 'tutors-earnings-payout-details.js',

            // Message pages
            'messages.show': 'messages-show.js',

            // Profile pages
            'profile.image-modal': 'profile-partials-image-modal-and-scripts.js',

            // Component modules
            'components.language-switcher': 'components-language-switcher.js',
            'components.language-notification': 'components-language-notification.js',
            'components.tutor-calendar': 'components-tutor-calendar.js',

            // Other pages
            'vnpay.result': 'vnpay-result.js',
            'debug.upload': 'debug-upload.js'
        };
    }

    /**
     * Load a specific module dynamically
     * @param {string} moduleName - The name of the module to load
     * @returns {Promise} - Promise that resolves when module is loaded
     */
    async loadModule(moduleName) {
        if (this.loadedModules.has(moduleName)) {
            return Promise.resolve();
        }

        const fileName = this.pageModules[moduleName];
        if (!fileName) {
            console.warn(`Module ${moduleName} not found`);
            return Promise.reject(new Error(`Module ${moduleName} not found`));
        }

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = `/js/pages/${fileName}`;
            script.onload = () => {
                this.loadedModules.add(moduleName);
                resolve();
            };
            script.onerror = () => {
                reject(new Error(`Failed to load module: ${moduleName}`));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * Load multiple modules
     * @param {Array} moduleNames - Array of module names to load
     * @returns {Promise} - Promise that resolves when all modules are loaded
     */
    async loadModules(moduleNames) {
        const promises = moduleNames.map(name => this.loadModule(name));
        return Promise.all(promises);
    }

    /**
     * Get the current page module based on route or data attribute
     * @returns {string|null} - The module name for current page
     */
    getCurrentPageModule() {
        // Try to get from data attribute first
        const body = document.body;
        const pageModule = body.dataset.pageModule;
        if (pageModule && this.pageModules[pageModule]) {
            return pageModule;
        }

        // Try to infer from URL path
        const path = window.location.pathname;
        for (const [moduleName, fileName] of Object.entries(this.pageModules)) {
            const routeParts = moduleName.split('.');
            if (path.includes(routeParts.join('/'))) {
                return moduleName;
            }
        }

        return null;
    }

    /**
     * Auto-load the current page module
     */
    autoLoad() {
        const currentModule = this.getCurrentPageModule();
        if (currentModule) {
            this.loadModule(currentModule).catch(error => {
                console.error('Failed to auto-load page module:', error);
            });
        }
    }

    /**
     * Get all available modules
     * @returns {Object} - Object containing all module mappings
     */
    getAllModules() {
        return { ...this.pageModules };
    }

    /**
     * Check if a module is loaded
     * @param {string} moduleName - The module name to check
     * @returns {boolean} - True if module is loaded
     */
    isLoaded(moduleName) {
        return this.loadedModules.has(moduleName);
    }
}

// Create global instance
window.JSManager = new JavaScriptManager();

// Auto-load on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure page is fully ready
    setTimeout(() => {
        window.JSManager.autoLoad();
    }, 100);
});
