/**
 * CIS Intelligence Dashboard - Main Application JS
 * Core functionality and initialization
 * Version: 2.0.0
 */

(function() {
  'use strict';

  // ============================================================================
  // MAIN APP OBJECT
  // ============================================================================

  const DashboardApp = {
    init() {
      this.initSidebar();
      this.initTooltips();
      this.initModals();
      this.initAlerts();
      this.initTables();
      this.initForms();
      this.initSearch();
      this.initMobileNav();

      console.log('✓ Dashboard App initialized');
    },

    // ============================================================================
    // SIDEBAR NAVIGATION
    // ============================================================================

    initSidebar() {
      const currentPage = window.location.search.match(/page=([^&]*)/)?.[1];
      if (currentPage) {
        const activeLink = document.querySelector(`.sidebar__link[href*="page=${currentPage}"]`);
        if (activeLink) {
          // Remove other active states
          document.querySelectorAll('.sidebar__link--active').forEach(link => {
            link.classList.remove('sidebar__link--active');
          });
          // Add active state to current link
          activeLink.classList.add('sidebar__link--active');
        }
      }
    },

    // ============================================================================
    // TOOLTIPS
    // ============================================================================

    initTooltips() {
      const tooltipElements = document.querySelectorAll('[data-tooltip]');

      tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
          const text = e.target.getAttribute('data-tooltip');
          const tooltip = this.createTooltip(text);
          document.body.appendChild(tooltip);
          this.positionTooltip(tooltip, e.target);

          // Store tooltip reference for cleanup
          e.target._tooltip = tooltip;
        });

        element.addEventListener('mouseleave', (e) => {
          if (e.target._tooltip) {
            e.target._tooltip.remove();
            delete e.target._tooltip;
          }
        });
      });
    },

    createTooltip(text) {
      const tooltip = document.createElement('div');
      tooltip.className = 'tooltip';
      tooltip.textContent = text;
      tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        z-index: 9999;
        pointer-events: none;
        white-space: nowrap;
      `;
      return tooltip;
    },

    positionTooltip(tooltip, target) {
      const rect = target.getBoundingClientRect();
      tooltip.style.top = `${rect.top - tooltip.offsetHeight - 8}px`;
      tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
    },

    // ============================================================================
    // MODALS
    // ============================================================================

    initModals() {
      // Open modal buttons
      document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          const modalId = e.currentTarget.getAttribute('data-modal-target');
          this.openModal(modalId);
        });
      });

      // Close modal buttons
      document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', (e) => {
          const modal = e.currentTarget.closest('.modal-backdrop');
          if (modal) {
            this.closeModal(modal);
          }
        });
      });

      // Close on backdrop click
      document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
          if (e.target === backdrop) {
            this.closeModal(backdrop);
          }
        });
      });

      // Close on Escape key
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          const openModal = document.querySelector('.modal-backdrop:not([style*="display: none"])');
          if (openModal) {
            this.closeModal(openModal);
          }
        }
      });
    },

    openModal(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }
    },

    closeModal(modal) {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    },

    // ============================================================================
    // ALERTS
    // ============================================================================

    initAlerts() {
      document.querySelectorAll('[data-alert-close]').forEach(button => {
        button.addEventListener('click', (e) => {
          const alert = e.currentTarget.closest('.alert');
          if (alert) {
            alert.style.animation = 'fadeOut 200ms ease-in-out';
            setTimeout(() => alert.remove(), 200);
          }
        });
      });
    },

    showAlert(message, type = 'info', duration = 5000) {
      const alertContainer = document.getElementById('alert-container') || this.createAlertContainer();

      const alert = document.createElement('div');
      alert.className = `alert alert--${type} animate-slideInDown`;
      alert.innerHTML = `
        <div class="alert__icon">
          <i class="fas fa-${this.getAlertIcon(type)}"></i>
        </div>
        <div class="alert__content">
          <div class="alert__message">${message}</div>
        </div>
        <button data-alert-close class="btn btn--ghost btn--sm">
          <i class="fas fa-times"></i>
        </button>
      `;

      alertContainer.appendChild(alert);

      // Auto-remove after duration
      if (duration > 0) {
        setTimeout(() => {
          alert.style.animation = 'fadeOut 200ms ease-in-out';
          setTimeout(() => alert.remove(), 200);
        }, duration);
      }

      // Re-init alert close buttons
      this.initAlerts();

      return alert;
    },

    createAlertContainer() {
      const container = document.createElement('div');
      container.id = 'alert-container';
      container.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-width: 400px;
      `;
      document.body.appendChild(container);
      return container;
    },

    getAlertIcon(type) {
      const icons = {
        success: 'check-circle',
        warning: 'exclamation-triangle',
        danger: 'exclamation-circle',
        info: 'info-circle'
      };
      return icons[type] || 'info-circle';
    },

    // ============================================================================
    // TABLES
    // ============================================================================

    initTables() {
      // Sortable tables
      document.querySelectorAll('.table--sortable th').forEach(header => {
        if (!header.classList.contains('no-sort')) {
          header.addEventListener('click', () => this.sortTable(header));
        }
      });

      // Row selection
      document.querySelectorAll('.table__select-all').forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
          const table = e.target.closest('table');
          const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
          checkboxes.forEach(cb => cb.checked = e.target.checked);
        });
      });
    },

    sortTable(header) {
      const table = header.closest('table');
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      const columnIndex = Array.from(header.parentElement.children).indexOf(header);

      // Determine sort direction
      const currentSort = header.classList.contains('sorted-asc') ? 'asc' :
                         header.classList.contains('sorted-desc') ? 'desc' : null;
      const newSort = currentSort === 'asc' ? 'desc' : 'asc';

      // Clear all sort indicators
      table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sorted-asc', 'sorted-desc');
      });

      // Add new sort indicator
      header.classList.add(`sorted-${newSort}`);

      // Sort rows
      rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();

        // Try numeric comparison first
        const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));

        if (!isNaN(aNum) && !isNaN(bNum)) {
          return newSort === 'asc' ? aNum - bNum : bNum - aNum;
        }

        // Fallback to string comparison
        return newSort === 'asc' ?
          aValue.localeCompare(bValue) :
          bValue.localeCompare(aValue);
      });

      // Re-append sorted rows
      rows.forEach(row => tbody.appendChild(row));
    },

    // ============================================================================
    // FORMS
    // ============================================================================

    initForms() {
      // Form validation
      document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
          if (!this.validateForm(form)) {
            e.preventDefault();
          }
        });
      });

      // Auto-save forms
      document.querySelectorAll('form[data-autosave]').forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
          input.addEventListener('change', () => {
            this.autoSaveForm(form);
          });
        });
      });

      // Character counters
      document.querySelectorAll('[data-maxlength]').forEach(input => {
        const maxLength = parseInt(input.getAttribute('data-maxlength'));
        const counter = this.createCharacterCounter(input, maxLength);
        input.parentElement.appendChild(counter);

        input.addEventListener('input', () => {
          this.updateCharacterCounter(input, counter, maxLength);
        });

        // Initial count
        this.updateCharacterCounter(input, counter, maxLength);
      });
    },

    validateForm(form) {
      let isValid = true;

      // Clear previous errors
      form.querySelectorAll('.form-error').forEach(error => error.remove());
      form.querySelectorAll('.form-input--error').forEach(input => {
        input.classList.remove('form-input--error');
      });

      // Check required fields
      form.querySelectorAll('[required]').forEach(input => {
        if (!input.value.trim()) {
          this.showFieldError(input, 'This field is required');
          isValid = false;
        }
      });

      // Check email fields
      form.querySelectorAll('input[type="email"]').forEach(input => {
        if (input.value && !this.isValidEmail(input.value)) {
          this.showFieldError(input, 'Please enter a valid email address');
          isValid = false;
        }
      });

      // Check URL fields
      form.querySelectorAll('input[type="url"]').forEach(input => {
        if (input.value && !this.isValidUrl(input.value)) {
          this.showFieldError(input, 'Please enter a valid URL');
          isValid = false;
        }
      });

      return isValid;
    },

    showFieldError(input, message) {
      input.classList.add('form-input--error');
      const error = document.createElement('div');
      error.className = 'form-error';
      error.textContent = message;
      input.parentElement.appendChild(error);
    },

    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    isValidUrl(url) {
      try {
        new URL(url);
        return true;
      } catch {
        return false;
      }
    },

    autoSaveForm(form) {
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      // Store in localStorage
      const formId = form.getAttribute('data-autosave');
      localStorage.setItem(`autosave_${formId}`, JSON.stringify(data));

      // Show save indicator
      this.showAlert('Changes saved automatically', 'success', 2000);
    },

    createCharacterCounter(input, maxLength) {
      const counter = document.createElement('div');
      counter.className = 'character-counter';
      counter.style.cssText = `
        font-size: 0.75rem;
        color: var(--color-text-muted);
        margin-top: 0.25rem;
        text-align: right;
      `;
      return counter;
    },

    updateCharacterCounter(input, counter, maxLength) {
      const length = input.value.length;
      counter.textContent = `${length} / ${maxLength}`;

      if (length > maxLength) {
        counter.style.color = 'var(--color-danger)';
      } else if (length > maxLength * 0.9) {
        counter.style.color = 'var(--color-warning)';
      } else {
        counter.style.color = 'var(--color-text-muted)';
      }
    },

    // ============================================================================
    // SEARCH
    // ============================================================================

    initSearch() {
      document.querySelectorAll('[data-search-target]').forEach(input => {
        const targetSelector = input.getAttribute('data-search-target');

        input.addEventListener('input', (e) => {
          const query = e.target.value.toLowerCase();
          const targets = document.querySelectorAll(targetSelector);

          targets.forEach(target => {
            const text = target.textContent.toLowerCase();
            const matches = text.includes(query);
            target.style.display = matches ? '' : 'none';
          });
        });
      });
    },

    // ============================================================================
    // MOBILE NAVIGATION
    // ============================================================================

    initMobileNav() {
      const toggleBtn = document.querySelector('[data-mobile-toggle]');
      const sidebar = document.querySelector('.sidebar');

      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
          sidebar.classList.toggle('sidebar--mobile-open');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
          if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('sidebar--mobile-open');
          }
        });
      }
    },

    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================

    formatNumber(num, decimals = 0) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
      }).format(num);
    },

    formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    formatDate(date, format = 'short') {
      const d = new Date(date);
      const options = format === 'short' ?
        { year: 'numeric', month: 'short', day: 'numeric' } :
        { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
      return d.toLocaleDateString('en-US', options);
    },

    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }
  };

  // ============================================================================
  // INITIALIZATION
  // ============================================================================

  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => DashboardApp.init());
  } else {
    DashboardApp.init();
  }

  // Expose to window for external access
  window.DashboardApp = DashboardApp;

})();
