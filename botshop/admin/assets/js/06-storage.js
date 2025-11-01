/**
 * 06-storage.js - Local and session storage management
 */

const Storage = {
    /**
     * Set item
     */
    set(key, value, type = 'local') {
        const store = type === 'local' ? localStorage : sessionStorage;
        store.setItem(key, JSON.stringify({
            value: value,
            timestamp: Date.now()
        }));
    },

    /**
     * Get item
     */
    get(key, type = 'local') {
        const store = type === 'local' ? localStorage : sessionStorage;
        const item = store.getItem(key);
        if (!item) return null;

        try {
            const data = JSON.parse(item);
            return data.value;
        } catch (e) {
            return item;
        }
    },

    /**
     * Remove item
     */
    remove(key, type = 'local') {
        const store = type === 'local' ? localStorage : sessionStorage;
        store.removeItem(key);
    },

    /**
     * Clear all
     */
    clear(type = 'local') {
        const store = type === 'local' ? localStorage : sessionStorage;
        store.clear();
    },

    /**
     * Set with expiration
     */
    setExpiring(key, value, minutes) {
        const expireTime = Date.now() + (minutes * 60000);
        localStorage.setItem(key, JSON.stringify({
            value: value,
            expire: expireTime
        }));
    },

    /**
     * Get expiring item
     */
    getExpiring(key) {
        const item = localStorage.getItem(key);
        if (!item) return null;

        try {
            const data = JSON.parse(item);
            if (data.expire && Date.now() > data.expire) {
                localStorage.removeItem(key);
                return null;
            }
            return data.value;
        } catch (e) {
            return item;
        }
    }
};

console.log('✓ Storage module loaded');
