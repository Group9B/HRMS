/**
 * Pro Skeleton Loading System - Component Factory
 * 
 * A powerful factory class for creating skeleton loading placeholders
 * using a template registry pattern for maximum reusability.`
 * 
 * @version 2.0.0
 * @author HRMS Team
 */

class SkeletonFactory {
    /**
     * Configuration options
     */
    static config = {
        minDuration: 500,           // Minimum time to show skeleton (ms)
        fadeOutDuration: 200,       // Fade out animation duration (ms)
        fadeInDuration: 300,        // Fade in animation duration (ms)
        defaultAnimation: 'shimmer' // 'shimmer', 'pulse', or 'static'
    };

    /**
     * Store for tracking skeleton state
     */
    static _state = new Map();

    /**
     * Template registry - stores blueprint functions
     */
    static blueprints = {
        /**
         * Dashboard stat card skeleton
         */
        'stat-card': (options = {}) => {
            const { animation = SkeletonFactory.config.defaultAnimation } = options;
            return `
                <div class="col-xl-3 col-md-6 mb-3" aria-hidden="true" role="presentation">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="sk-stat-card">
                                <div class="sk-stat-content">
                                    <div class="skeleton-${animation} sk-text sk-text-xs sk-w-66 sk-mb-2"></div>
                                    <div class="skeleton-${animation} sk-text sk-text-2xl sk-w-33"></div>
                                </div>
                                <div class="skeleton-${animation} sk-stat-icon"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Table row skeleton
         * @param {Object} options - { columns: number }
         */
        'table-row': (options = {}) => {
            const { 
                columns = 5, 
                animation = SkeletonFactory.config.defaultAnimation 
            } = options;
            
            const widths = ['sk-w-75', 'sk-w-random-2', 'sk-w-random-1', 'sk-w-random-3', 'sk-w-50'];
            let cells = '';
            
            for (let i = 0; i < columns; i++) {
                const width = widths[i % widths.length];
                cells += `
                    <td>
                        <div class="skeleton-${animation} sk-text ${width}"></div>
                    </td>
                `;
            }
            
            return `<tr aria-hidden="true" role="presentation">${cells}</tr>`;
        },

        /**
         * List item skeleton (with avatar)
         */
        'list-item': (options = {}) => {
            const { animation = SkeletonFactory.config.defaultAnimation } = options;
            return `
                <div class="sk-list-item" aria-hidden="true" role="presentation">
                    <div class="skeleton-${animation} sk-circle"></div>
                    <div class="sk-flex-grow sk-flex-col" style="gap: 0.375rem;">
                        <div class="skeleton-${animation} sk-text sk-w-66"></div>
                        <div class="skeleton-${animation} sk-text sk-text-xs sk-w-50"></div>
                    </div>
                </div>
            `;
        },

        /**
         * Generic card skeleton
         */
        'card': (options = {}) => {
            const { animation = SkeletonFactory.config.defaultAnimation } = options;
            return `
                <div class="card shadow-sm sk-card" aria-hidden="true" role="presentation">
                    <div class="sk-card-body">
                        <div class="skeleton-${animation} sk-text sk-text-lg sk-w-50 sk-mb-4"></div>
                        <div class="skeleton-${animation} sk-text sk-w-100 sk-mb-2"></div>
                        <div class="skeleton-${animation} sk-text sk-w-random-1 sk-mb-2"></div>
                        <div class="skeleton-${animation} sk-text sk-w-75"></div>
                    </div>
                </div>
            `;
        },

        /**
         * Paragraph skeleton
         * @param {Object} options - { lines: number }
         */
        'paragraph': (options = {}) => {
            const { 
                lines = 3, 
                animation = SkeletonFactory.config.defaultAnimation 
            } = options;
            
            const widths = ['sk-w-100', 'sk-w-random-1', 'sk-w-random-2', 'sk-w-random-3', 'sk-w-75'];
            let html = '<div aria-hidden="true" role="presentation">';
            
            for (let i = 0; i < lines; i++) {
                const width = i === lines - 1 ? 'sk-w-66' : widths[i % widths.length];
                const margin = i < lines - 1 ? 'sk-mb-2' : '';
                html += `<div class="skeleton-${animation} sk-text ${width} ${margin}"></div>`;
            }
            
            html += '</div>';
            return html;
        },

        /**
         * Text line skeleton
         * @param {Object} options - { width: string, size: string }
         */
        'text': (options = {}) => {
            const { 
                width = 'sk-w-100',
                size = '',
                animation = SkeletonFactory.config.defaultAnimation 
            } = options;
            
            return `<div class="skeleton-${animation} sk-text ${size} ${width}" aria-hidden="true" role="presentation"></div>`;
        },

        /**
         * Circle skeleton (avatar)
         * @param {Object} options - { size: string }
         */
        'circle': (options = {}) => {
            const { 
                size = '',
                animation = SkeletonFactory.config.defaultAnimation 
            } = options;
            
            return `<div class="skeleton-${animation} sk-circle ${size}" aria-hidden="true" role="presentation"></div>`;
        },

        /**
         * Rectangle skeleton (image placeholder)
         * @param {Object} options - { size: string }
         */
        'rect': (options = {}) => {
            const { 
                size = '',
                animation = SkeletonFactory.config.defaultAnimation 
            } = options;
            
            return `<div class="skeleton-${animation} sk-rect ${size}" aria-hidden="true" role="presentation"></div>`;
        },

        /**
         * Button skeleton
         * @param {Object} options - { size: string }
         */
        'button': (options = {}) => {
            const { 
                size = '',
                animation = SkeletonFactory.config.defaultAnimation 
            } = options;
            
            return `<div class="skeleton-${animation} sk-button ${size}" aria-hidden="true" role="presentation"></div>`;
        },

        /**
         * DataTable skeleton
         * @param {Object} options - { rows: number, columns: number }
         */
        'datatable': (options = {}) => {
            const { 
                rows = 5, 
                columns = 5,
                animation = SkeletonFactory.config.defaultAnimation 
            } = options;
            
            let html = '<table class="table"><tbody>';
            for (let i = 0; i < rows; i++) {
                html += SkeletonFactory.blueprints['table-row']({ columns, animation });
            }
            html += '</tbody></table>';
            return html;
        },

        /**
         * Profile header skeleton
         */
        'profile-header': (options = {}) => {
            const { animation = SkeletonFactory.config.defaultAnimation } = options;
            return `
                <div class="sk-flex" aria-hidden="true" role="presentation">
                    <div class="skeleton-${animation} sk-circle sk-circle-xl"></div>
                    <div class="sk-flex-grow sk-flex-col" style="gap: 0.5rem;">
                        <div class="skeleton-${animation} sk-text sk-text-lg sk-w-50"></div>
                        <div class="skeleton-${animation} sk-text sk-text-sm sk-w-33"></div>
                    </div>
                </div>
            `;
        },

        /**
         * Notification item skeleton
         */
        'notification': (options = {}) => {
            const { animation = SkeletonFactory.config.defaultAnimation } = options;
            return `
                <div class="sk-list-item" aria-hidden="true" role="presentation">
                    <div class="skeleton-${animation} sk-circle sk-circle-sm"></div>
                    <div class="sk-flex-grow sk-flex-col" style="gap: 0.25rem;">
                        <div class="skeleton-${animation} sk-text sk-text-sm sk-w-75"></div>
                        <div class="skeleton-${animation} sk-text sk-text-xs sk-w-33"></div>
                    </div>
                </div>
            `;
        }
    };

    /**
     * Register a custom blueprint
     * @param {string} name - Blueprint name
     * @param {Function} templateFn - Template function that returns HTML
     */
    static register(name, templateFn) {
        if (typeof templateFn !== 'function') {
            console.error(`SkeletonFactory: Blueprint "${name}" must be a function.`);
            return;
        }
        this.blueprints[name] = templateFn;
    }

    /**
     * Create skeleton HTML
     * @param {string} blueprint - Blueprint name
     * @param {number} count - Number of items to create
     * @param {Object} options - Options passed to blueprint
     * @returns {string} HTML string
     */
    static create(blueprint, count = 1, options = {}) {
        const templateFn = this.blueprints[blueprint];
        
        if (!templateFn) {
            console.warn(`SkeletonFactory: Blueprint "${blueprint}" not found. Using text.`);
            return this.blueprints['text'](options).repeat(count);
        }
        
        let html = '';
        for (let i = 0; i < count; i++) {
            html += templateFn(options);
        }
        
        return html;
    }

    /**
     * Show skeleton in a container
     * @param {string|Element} selector - Container selector or element
     * @param {string} blueprint - Blueprint name
     * @param {number} count - Number of items
     * @param {Object} options - Blueprint options
     */
    static show(selector, blueprint, count = 1, options = {}) {
        const container = typeof selector === 'string' 
            ? document.querySelector(selector) 
            : selector;
        
        if (!container) {
            console.warn(`SkeletonFactory: Container "${selector}" not found.`);
            return;
        }
        
        const key = typeof selector === 'string' ? selector : container;
        
        // Store current state
        this._state.set(key, {
            startTime: Date.now(),
            originalContent: container.innerHTML,
            originalHeight: container.offsetHeight
        });
        
        // Generate and inject skeleton HTML
        const html = this.create(blueprint, count, options);
        container.innerHTML = html;
        container.classList.add('sk-loading');
        
        // Add screen reader announcement
        const srAnnounce = document.createElement('span');
        srAnnounce.className = 'sk-sr-only';
        srAnnounce.setAttribute('role', 'status');
        srAnnounce.setAttribute('aria-live', 'polite');
        srAnnounce.textContent = 'Loading content...';
        srAnnounce.id = `sk-announce-${Date.now()}`;
        container.prepend(srAnnounce);
    }

    /**
     * Hide skeleton and optionally show new content
     * @param {string|Element} selector - Container selector or element
     * @param {string|null} newContent - Optional new content HTML
     * @returns {Promise} Resolves when skeleton is hidden
     */
    static async hide(selector, newContent = null) {
        const container = typeof selector === 'string' 
            ? document.querySelector(selector) 
            : selector;
        
        if (!container) {
            console.warn(`SkeletonFactory: Container "${selector}" not found.`);
            return;
        }
        
        const key = typeof selector === 'string' ? selector : container;
        const state = this._state.get(key);
        
        if (!state) {
            // No skeleton shown, just update content if provided
            if (newContent !== null) {
                container.innerHTML = newContent;
            }
            return;
        }
        
        // Calculate remaining time for minimum duration
        const elapsed = Date.now() - state.startTime;
        const remaining = Math.max(0, this.config.minDuration - elapsed);
        
        // Wait for minimum duration
        if (remaining > 0) {
            await this._sleep(remaining);
        }
        
        // Perform hide with optional fade transition
        const contentToShow = newContent !== null ? newContent : state.originalContent;
        
        // Remove screen reader announcement
        const srAnnounce = container.querySelector('[role="status"]');
        if (srAnnounce) {
            srAnnounce.remove();
        }
        
        // Update content
        container.innerHTML = contentToShow;
        container.classList.remove('sk-loading');
        
        // Clean up state
        this._state.delete(key);
        
        // Announce to screen readers
        this._announceLoaded();
    }

    /**
     * Helper: Sleep for specified milliseconds
     * @private
     */
    static _sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Helper: Announce content loaded to screen readers
     * @private
     */
    static _announceLoaded() {
        const announcement = document.createElement('div');
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', 'polite');
        announcement.className = 'sk-sr-only';
        announcement.textContent = 'Content loaded';
        document.body.appendChild(announcement);
        
        // Remove after announcement
        setTimeout(() => announcement.remove(), 1000);
    }

    /**
     * Show skeleton in a DataTable container
     * @param {string} tableId - Table ID (without #)
     * @param {number} rows - Number of rows
     * @param {number} columns - Number of columns
     */
    static showTable(tableId, rows = 5, columns = 5) {
        const table = document.getElementById(tableId);
        if (!table) {
            console.warn(`SkeletonFactory: Table "#${tableId}" not found.`);
            return;
        }
        
        // Store state
        this._state.set(tableId, {
            startTime: Date.now(),
            tableDisplay: table.style.display
        });
        
        // Hide actual table
        table.style.display = 'none';
        
        // Create skeleton container
        const skeletonDiv = document.createElement('div');
        skeletonDiv.id = `${tableId}-skeleton`;
        skeletonDiv.innerHTML = this.create('datatable', 1, { rows, columns });
        
        // Insert after table
        table.parentNode.insertBefore(skeletonDiv, table.nextSibling);
    }

    /**
     * Hide DataTable skeleton
     * @param {string} tableId - Table ID (without #)
     * @returns {Promise} Resolves when skeleton is hidden
     */
    static async hideTable(tableId) {
        const table = document.getElementById(tableId);
        const skeleton = document.getElementById(`${tableId}-skeleton`);
        const state = this._state.get(tableId);
        
        if (!skeleton || !table) return;
        
        // Calculate remaining time for minimum duration
        if (state) {
            const elapsed = Date.now() - state.startTime;
            const remaining = Math.max(0, this.config.minDuration - elapsed);
            
            if (remaining > 0) {
                await this._sleep(remaining);
            }
        }
        
        // Remove skeleton and show table
        skeleton.remove();
        table.style.display = state?.tableDisplay || '';
        
        // Clean up
        this._state.delete(tableId);
    }

    /**
     * Replace element content with skeleton temporarily
     * @param {string|Element} selector - Element selector
     * @param {string} blueprint - Blueprint name
     * @param {Object} options - Blueprint options
     */
    static replace(selector, blueprint = 'text', options = {}) {
        this.show(selector, blueprint, 1, options);
    }

    /**
     * Restore original content
     * @param {string|Element} selector - Element selector
     * @returns {Promise}
     */
    static async restore(selector) {
        return this.hide(selector);
    }

    /**
     * Check if a container is currently showing a skeleton
     * @param {string|Element} selector - Container selector
     * @returns {boolean}
     */
    static isLoading(selector) {
        const key = typeof selector === 'string' ? selector : selector;
        return this._state.has(key);
    }

    /**
     * Update configuration
     * @param {Object} newConfig - Configuration options to update
     */
    static configure(newConfig) {
        Object.assign(this.config, newConfig);
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SkeletonFactory;
}

/**
 * Configure DataTables globally to use skeleton loading
 * This runs immediately when the script loads (after jQuery and DataTables)
 */
(function() {
    // Wait for jQuery to be ready
    if (typeof $ === 'undefined' || typeof $.fn.dataTable === 'undefined') {
        console.warn('SkeletonFactory: jQuery DataTables not found. Skeleton loading for tables will not work.');
        return;
    }

    // Create skeleton loading HTML for DataTables
    const createTableSkeletonRows = (columns = 6, rows = 5) => {
        let html = '';
        const widths = ['75%', '60%', '80%', '50%', '70%', '40%'];
        
        for (let r = 0; r < rows; r++) {
            let cells = '';
            for (let c = 0; c < columns; c++) {
                const width = widths[c % widths.length];
                cells += `<td><div class="skeleton-shimmer sk-text" style="width: ${width}; height: 1em;"></div></td>`;
            }
            html += `<tr>${cells}</tr>`;
        }
        return html;
    };

    // Set global DataTables defaults for loading states
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            loadingRecords: createTableSkeletonRows(6, 5),
            processing: `
                <div class="d-flex justify-content-center align-items-center p-4">
                    <div class="skeleton-shimmer sk-rect" style="width: 100%; height: 150px;"></div>
                </div>
            `,
            emptyTable: `
                <div class="text-center text-muted py-4">
                    <i class="ti ti-database-off fs-1 mb-2 d-block"></i>
                    No data available
                </div>
            `,
            zeroRecords: `
                <div class="text-center text-muted py-4">
                    <i class="ti ti-search-off fs-1 mb-2 d-block"></i>
                    No matching records found
                </div>
            `
        }
    });

    console.log('SkeletonFactory: DataTables skeleton loading configured.');
})();
