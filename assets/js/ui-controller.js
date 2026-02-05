/**
 * Pro Skeleton Loading System - State Orchestrator
 * 
 * A controller class that manages the complete async lifecycle:
 * Fetch → Show Skeleton → Render Data → Hide Skeleton
 * 
 * Features smooth transitions and CLS prevention.
 * 
 * @version 2.0.0
 * @author HRMS Team
 */

class UIController {
    /**
     * Configuration options
     */
    static config = {
        fadeOutDuration: 200,
        fadeInDuration: 300,
        preventCLS: true,
        showSkeletonImmediately: true
    };

    /**
     * Perform an async fetch with skeleton loading
     * 
     * @param {Object} options - Configuration options
     * @param {string|Element} options.container - Container selector or element
     * @param {string} options.blueprint - Skeleton blueprint name
     * @param {number} options.count - Number of skeleton items
     * @param {Object} options.blueprintOptions - Options for the blueprint
     * @param {string} options.url - API URL to fetch
     * @param {Object} options.fetchOptions - Options for fetch API
     * @param {Function} options.onRender - Callback to render the data (receives data, container)
     * @param {Function} options.onError - Callback for errors
     * @param {boolean} options.smoothSwap - Enable smooth fade transition
     * @returns {Promise<any>} The fetched data
     * 
     * @example
     * UIController.fetch({
     *     container: '#stats-container',
     *     blueprint: 'stat-card',
     *     count: 4,
     *     url: '/api/dashboard-stats',
     *     onRender: (data, container) => {
     *         container.innerHTML = renderStats(data);
     *     }
     * });
     */
    static async fetch(options) {
        const {
            container,
            blueprint,
            count = 1,
            blueprintOptions = {},
            url,
            fetchOptions = {},
            onRender,
            onError,
            smoothSwap = true
        } = options;

        const containerEl = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!containerEl) {
            console.error(`UIController: Container "${container}" not found.`);
            return;
        }

        try {
            // 1. Show skeleton immediately
            if (this.config.showSkeletonImmediately) {
                SkeletonFactory.show(container, blueprint, count, blueprintOptions);
            }

            // 2. Fetch data
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    ...fetchOptions.headers
                },
                ...fetchOptions
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // 3. Smooth swap: hide skeleton and render content
            if (smoothSwap) {
                await this.smoothSwap(container, () => {
                    if (onRender) {
                        onRender(data, containerEl);
                    }
                });
            } else {
                await SkeletonFactory.hide(container);
                if (onRender) {
                    onRender(data, containerEl);
                }
            }

            return data;

        } catch (error) {
            console.error('UIController fetch error:', error);
            
            // Hide skeleton even on error
            await SkeletonFactory.hide(container);
            
            if (onError) {
                onError(error, containerEl);
            } else {
                // Default error display
                containerEl.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>
                        <div>Failed to load content. Please try again.</div>
                    </div>
                `;
            }
            
            throw error;
        }
    }

    /**
     * Perform a smooth swap transition
     * 
     * @param {string|Element} container - Container selector or element
     * @param {Function} renderFn - Function that renders the new content
     * @returns {Promise} Resolves when transition is complete
     */
    static async smoothSwap(container, renderFn) {
        const containerEl = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!containerEl) return;

        // Capture current height to prevent CLS
        const currentHeight = containerEl.offsetHeight;
        
        if (this.config.preventCLS) {
            containerEl.style.height = `${currentHeight}px`;
            containerEl.style.overflow = 'hidden';
        }

        // Add fade container class
        containerEl.classList.add('sk-fade-container');

        // Fade out
        containerEl.classList.add('sk-fade-out');
        await this._sleep(this.config.fadeOutDuration);

        // Wait for skeleton minimum duration and hide
        await SkeletonFactory.hide(container);

        // Render new content
        if (renderFn) {
            renderFn();
        }

        // Fade in
        containerEl.classList.remove('sk-fade-out');
        containerEl.classList.add('sk-fade-in');

        // Animate to new height if preventing CLS
        if (this.config.preventCLS) {
            const newHeight = containerEl.scrollHeight;
            containerEl.style.height = `${newHeight}px`;
            
            await this._sleep(this.config.fadeInDuration);
            
            // Remove fixed height
            containerEl.style.height = '';
            containerEl.style.overflow = '';
        } else {
            await this._sleep(this.config.fadeInDuration);
        }

        // Cleanup
        containerEl.classList.remove('sk-fade-container', 'sk-fade-in');
    }

    /**
     * Show loading state for form submission
     * 
     * @param {string|Element} button - Button selector or element
     * @param {string} loadingText - Text to show while loading
     * @returns {Function} Function to restore button state
     */
    static showButtonLoading(button, loadingText = 'Loading...') {
        const buttonEl = typeof button === 'string'
            ? document.querySelector(button)
            : button;

        if (!buttonEl) return () => {};

        const originalContent = buttonEl.innerHTML;
        const originalDisabled = buttonEl.disabled;

        buttonEl.disabled = true;
        buttonEl.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            ${loadingText}
        `;

        // Return restore function
        return () => {
            buttonEl.disabled = originalDisabled;
            buttonEl.innerHTML = originalContent;
        };
    }

    /**
     * Fetch multiple resources in parallel with skeleton loading
     * 
     * @param {Array<Object>} requests - Array of request configurations
     * @returns {Promise<Array>} Array of results
     */
    static async fetchAll(requests) {
        // Show all skeletons immediately
        requests.forEach(req => {
            if (req.container && req.blueprint) {
                SkeletonFactory.show(
                    req.container, 
                    req.blueprint, 
                    req.count || 1, 
                    req.blueprintOptions || {}
                );
            }
        });

        // Fetch all in parallel
        const results = await Promise.allSettled(
            requests.map(async req => {
                const response = await fetch(req.url, req.fetchOptions || {});
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
        );

        // Process results
        for (let i = 0; i < requests.length; i++) {
            const req = requests[i];
            const result = results[i];

            if (req.smoothSwap !== false) {
                await this.smoothSwap(req.container, () => {
                    if (result.status === 'fulfilled' && req.onRender) {
                        req.onRender(result.value, document.querySelector(req.container));
                    } else if (result.status === 'rejected' && req.onError) {
                        req.onError(result.reason, document.querySelector(req.container));
                    }
                });
            } else {
                await SkeletonFactory.hide(req.container);
                if (result.status === 'fulfilled' && req.onRender) {
                    req.onRender(result.value, document.querySelector(req.container));
                }
            }
        }

        return results.map(r => r.status === 'fulfilled' ? r.value : null);
    }

    /**
     * Lazy load content when element becomes visible
     * 
     * @param {string|Element} container - Container to observe
     * @param {Object} options - Same options as fetch()
     * @returns {IntersectionObserver} The observer instance
     */
    static lazyLoad(container, options) {
        const containerEl = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!containerEl) return null;

        // Show placeholder skeleton
        SkeletonFactory.show(container, options.blueprint, options.count || 1, options.blueprintOptions || {});

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Element is visible, fetch content
                    this.fetch({
                        ...options,
                        container
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '100px', // Start loading slightly before visible
            threshold: 0.1
        });

        observer.observe(containerEl);
        return observer;
    }

    /**
     * Start skeleton on DOMContentLoaded for immediate visual feedback
     * 
     * @param {Object} skeletonConfig - Configuration for initial skeletons
     * 
     * @example
     * UIController.initOnLoad({
     *     '#stats-row': { blueprint: 'stat-card', count: 4 },
     *     '#data-table': { blueprint: 'table-row', count: 10, options: { columns: 6 } }
     * });
     */
    static initOnLoad(skeletonConfig) {
        const init = () => {
            Object.entries(skeletonConfig).forEach(([selector, config]) => {
                const { blueprint, count = 1, options = {} } = config;
                SkeletonFactory.show(selector, blueprint, count, options);
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    }

    /**
     * Helper: Sleep for specified milliseconds
     * @private
     */
    static _sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
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
    module.exports = UIController;
}
