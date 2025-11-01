/**
 * 07-forms.js - Form validation and handling
 */

const Forms = {
    /**
     * Validate required fields
     */
    validateRequired(form) {
        let valid = true;
        const fields = form.querySelectorAll('[required]');

        fields.forEach(field => {
            if (!field.value || field.value.trim() === '') {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return valid;
    },

    /**
     * Validate email
     */
    validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },

    /**
     * Validate form
     */
    validate(form) {
        if (!this.validateRequired(form)) return false;

        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.validateEmail(field.value)) {
                field.classList.add('is-invalid');
                return false;
            }
        });

        return true;
    },

    /**
     * Get form data
     */
    getData(form) {
        const formData = new FormData(form);
        const data = {};

        formData.forEach((value, key) => {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        });

        return data;
    },

    /**
     * Populate form
     */
    populate(form, data) {
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = data[key];
                } else if (field.type === 'radio') {
                    form.querySelector(`[name="${key}"][value="${data[key]}"]`).checked = true;
                } else {
                    field.value = data[key];
                }
            }
        });
    },

    /**
     * Reset form
     */
    reset(form) {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
    },

    /**
     * Disable form
     */
    disable(form, disabled = true) {
        form.querySelectorAll('input, select, textarea, button').forEach(field => {
            field.disabled = disabled;
        });
    }
};

console.log('✓ Forms module loaded');
