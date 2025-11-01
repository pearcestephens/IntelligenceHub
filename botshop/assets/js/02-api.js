/**
 * 02-api.js - API communication helpers
 */

const API = {
    baseURL: window.location.pathname.split('/dashboard')[0] + '/api',

    /**
     * GET request
     */
    async get(endpoint) {
        try {
            const response = await fetch(this.baseURL + endpoint);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            Dashboard.toast(`Error fetching ${endpoint}`, 'danger');
            throw error;
        }
    },

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API POST Error:', error);
            Dashboard.toast(`Error posting to ${endpoint}`, 'danger');
            throw error;
        }
    },

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API PUT Error:', error);
            Dashboard.toast(`Error updating ${endpoint}`, 'danger');
            throw error;
        }
    },

    /**
     * DELETE request
     */
    async delete(endpoint) {
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: 'DELETE'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API DELETE Error:', error);
            Dashboard.toast(`Error deleting ${endpoint}`, 'danger');
            throw error;
        }
    },

    /**
     * Get project overview
     */
    async getProjectOverview() {
        return this.get('/project/overview');
    },

    /**
     * Get project files
     */
    async getProjectFiles() {
        return this.get('/project/files');
    },

    /**
     * Get dependencies
     */
    async getDependencies() {
        return this.get('/project/dependencies');
    },

    /**
     * Get violations
     */
    async getViolations() {
        return this.get('/project/violations');
    },

    /**
     * Get rules
     */
    async getRules() {
        return this.get('/project/rules');
    },

    /**
     * Get metrics
     */
    async getMetrics() {
        return this.get('/project/metrics');
    }
};

console.log('✓ API module loaded');
