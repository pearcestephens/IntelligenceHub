/**
 * 04-modals.js - Modal dialog management
 */

const Modal = {
    /**
     * Show modal
     */
    show(modalSelector) {
        const modal = document.querySelector(modalSelector);
        if (!modal) return;

        modal.classList.add('show');
        modal.style.display = 'block';

        if (modal.querySelector('.modal-backdrop')) return;

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    },

    /**
     * Hide modal
     */
    hide(modalSelector) {
        const modal = document.querySelector(modalSelector);
        if (!modal) return;

        modal.classList.remove('show');
        modal.style.display = 'none';

        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
    },

    /**
     * Show alert dialog
     */
    alert(title, message, onConfirm = null) {
        const html = `
            <div class="modal fade show modal-alert" style="display: block;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" onclick="this.closest('.modal').remove();"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="this.closest('.modal').remove(); ${onConfirm ? onConfirm : ''}">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const modal = document.createElement('div');
        modal.innerHTML = html;
        document.body.appendChild(modal.firstElementChild);
    },

    /**
     * Show confirm dialog
     */
    confirm(title, message, onConfirm, onCancel = null) {
        const html = `
            <div class="modal fade show" style="display: block;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const modal = document.createElement('div');
        modal.innerHTML = html;
        const element = modal.firstElementChild;

        element.querySelector('.btn-primary').addEventListener('click', () => {
            onConfirm();
            element.remove();
        });

        element.querySelector('.btn-secondary').addEventListener('click', () => {
            if (onCancel) onCancel();
            element.remove();
        });

        document.body.appendChild(element);
    },

    /**
     * Show loading modal
     */
    loading(message = 'Loading...') {
        const html = `
            <div class="modal fade show modal-loading" style="display: block;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center py-5">
                            <div class="spinner mb-3"></div>
                            <p>${message}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const modal = document.createElement('div');
        modal.innerHTML = html;
        const element = modal.firstElementChild;
        element.id = 'loading-modal';
        document.body.appendChild(element);

        return {
            close: () => element.remove()
        };
    }
};

console.log('✓ Modal module loaded');
