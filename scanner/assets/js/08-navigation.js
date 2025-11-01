/**
 * 08-navigation.js - Navigation and page routing
 */

const Nav = {
    /**
     * Get current page
     */
    getCurrentPage() {
        const params = new URLSearchParams(window.location.search);
        return params.get('page') || 'overview';
    },

    /**
     * Navigate to page
     */
    goToPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.history.pushState({}, '', url);
        window.location.reload();
    },

    /**
     * Update active nav link
     */
    updateActive(activeClass = 'active') {
        const currentPage = this.getCurrentPage();
        const navLinks = document.querySelectorAll('[data-page]');

        navLinks.forEach(link => {
            if (link.dataset.page === currentPage) {
                link.classList.add(activeClass);
            } else {
                link.classList.remove(activeClass);
            }
        });
    },

    /**
     * Initialize navigation
     */
    init() {
        this.updateActive();

        document.querySelectorAll('[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.goToPage(link.dataset.page);
            });
        });
    },

    /**
     * Breadcrumb update
     */
    setBreadcrumb(breadcrumbs) {
        const container = document.querySelector('.breadcrumb');
        if (!container) return;

        let html = '<li class="breadcrumb-item"><a href="?page=overview">Home</a></li>';
        breadcrumbs.forEach((item, index) => {
            const isLast = index === breadcrumbs.length - 1;
            if (isLast) {
                html += `<li class="breadcrumb-item active">${item}</li>`;
            } else {
                html += `<li class="breadcrumb-item"><a href="#">${item}</a></li>`;
            }
        });

        container.innerHTML = html;
    }
};

console.log('✓ Navigation module loaded');
