/**
 * 05-notifications.js - Toast and notification system
 */

const Notify = {
    container: null,

    /**
     * Initialize notification container
     */
    init() {
        if (this.container) return;
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = 'position-fixed bottom-0 end-0 p-3';
        this.container.style.zIndex = '9999';
        document.body.appendChild(this.container);
    },

    /**
     * Show notification
     */
    show(message, type = 'info', duration = 5000) {
        this.init();

        const id = 'notification-' + Date.now();
        const html = `
            <div id="${id}" class="toast show align-items-center text-white bg-${type} mb-2" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        this.container.insertAdjacentHTML('beforeend', html);

        if (duration > 0) {
            setTimeout(() => {
                const element = document.getElementById(id);
                if (element) element.remove();
            }, duration);
        }

        return id;
    },

    /**
     * Show success notification
     */
    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    },

    /**
     * Show error notification
     */
    error(message, duration = 5000) {
        return this.show(message, 'danger', duration);
    },

    /**
     * Show warning notification
     */
    warning(message, duration = 5000) {
        return this.show(message, 'warning', duration);
    },

    /**
     * Show info notification
     */
    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    },

    /**
     * Remove notification
     */
    remove(id) {
        const element = document.getElementById(id);
        if (element) element.remove();
    },

    /**
     * Clear all notifications
     */
    clear() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
};

console.log('✓ Notification module loaded');
