/**
 * WebSocket Client for Bot Deployment Dashboard
 *
 * Handles real-time updates from WebSocket server
 *
 * Usage:
 *   const ws = new BotWebSocket('ws://localhost:8080');
 *   ws.subscribe('bots', (data) => { console.log('Bots updated:', data); });
 *   ws.subscribe('executions', (data) => { console.log('Execution update:', data); });
 */

class BotWebSocket {
    constructor(url, options = {}) {
        this.url = url;
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = options.maxReconnectAttempts || 10;
        this.reconnectDelay = options.reconnectDelay || 3000;
        this.pingInterval = options.pingInterval || 30000;
        this.pingTimer = null;
        this.subscribers = {};
        this.eventHandlers = {};
        this.connected = false;
        this.connectionId = null;

        this.connect();
    }

    /**
     * Connect to WebSocket server
     */
    connect() {
        console.log('[WebSocket] Connecting to:', this.url);

        try {
            this.ws = new WebSocket(this.url);

            this.ws.onopen = (event) => this.handleOpen(event);
            this.ws.onmessage = (event) => this.handleMessage(event);
            this.ws.onclose = (event) => this.handleClose(event);
            this.ws.onerror = (event) => this.handleError(event);

        } catch (error) {
            console.error('[WebSocket] Connection error:', error);
            this.scheduleReconnect();
        }
    }

    /**
     * Handle connection open
     */
    handleOpen(event) {
        console.log('[WebSocket] Connected');
        this.connected = true;
        this.reconnectAttempts = 0;

        // Start ping timer
        this.startPingTimer();

        // Trigger connection event
        this.trigger('connected', {});
    }

    /**
     * Handle incoming message
     */
    handleMessage(event) {
        try {
            const message = JSON.parse(event.data);

            console.log('[WebSocket] Message received:', message.type);

            switch (message.type) {
                case 'connection':
                    this.connectionId = message.client_id;
                    console.log('[WebSocket] Client ID:', this.connectionId);
                    break;

                case 'subscribed':
                    console.log('[WebSocket] Subscribed to:', message.channel);
                    this.trigger('subscribed', message);
                    break;

                case 'unsubscribed':
                    console.log('[WebSocket] Unsubscribed from:', message.channel);
                    this.trigger('unsubscribed', message);
                    break;

                case 'data':
                    this.handleData(message);
                    break;

                case 'event':
                    this.handleEvent(message);
                    break;

                case 'pong':
                    // Ping response received
                    break;

                case 'error':
                    console.error('[WebSocket] Server error:', message.message);
                    this.trigger('error', message);
                    break;

                default:
                    console.warn('[WebSocket] Unknown message type:', message.type);
            }

        } catch (error) {
            console.error('[WebSocket] Failed to parse message:', error);
        }
    }

    /**
     * Handle data message
     */
    handleData(message) {
        const channel = message.channel;
        const data = message.data;

        if (this.subscribers[channel]) {
            this.subscribers[channel].forEach(callback => {
                try {
                    callback(data, message);
                } catch (error) {
                    console.error('[WebSocket] Subscriber error:', error);
                }
            });
        }
    }

    /**
     * Handle event message
     */
    handleEvent(message) {
        const eventType = message.event;
        const data = message.data;
        const channel = message.channel;

        console.log('[WebSocket] Event:', eventType, 'Channel:', channel);

        // Trigger event handlers
        if (this.eventHandlers[eventType]) {
            this.eventHandlers[eventType].forEach(callback => {
                try {
                    callback(data, message);
                } catch (error) {
                    console.error('[WebSocket] Event handler error:', error);
                }
            });
        }

        // Also notify channel subscribers
        if (channel && this.subscribers[channel]) {
            this.subscribers[channel].forEach(callback => {
                try {
                    callback(data, message);
                } catch (error) {
                    console.error('[WebSocket] Subscriber error:', error);
                }
            });
        }
    }

    /**
     * Handle connection close
     */
    handleClose(event) {
        console.log('[WebSocket] Disconnected:', event.code, event.reason);
        this.connected = false;
        this.stopPingTimer();

        this.trigger('disconnected', { code: event.code, reason: event.reason });

        // Attempt to reconnect
        this.scheduleReconnect();
    }

    /**
     * Handle error
     */
    handleError(event) {
        console.error('[WebSocket] Error:', event);
        this.trigger('error', { message: 'WebSocket error occurred' });
    }

    /**
     * Schedule reconnection attempt
     */
    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('[WebSocket] Max reconnect attempts reached');
            this.trigger('reconnect_failed', {});
            return;
        }

        this.reconnectAttempts++;
        const delay = this.reconnectDelay * this.reconnectAttempts;

        console.log(`[WebSocket] Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);

        setTimeout(() => {
            if (!this.connected) {
                this.connect();
            }
        }, delay);
    }

    /**
     * Start ping timer
     */
    startPingTimer() {
        this.stopPingTimer();

        this.pingTimer = setInterval(() => {
            if (this.connected) {
                this.send({ action: 'ping' });
            }
        }, this.pingInterval);
    }

    /**
     * Stop ping timer
     */
    stopPingTimer() {
        if (this.pingTimer) {
            clearInterval(this.pingTimer);
            this.pingTimer = null;
        }
    }

    /**
     * Subscribe to channel
     *
     * @param {string} channel - Channel name (bots, executions, health, metrics)
     * @param {function} callback - Callback function to receive updates
     */
    subscribe(channel, callback) {
        if (!this.subscribers[channel]) {
            this.subscribers[channel] = [];
        }

        this.subscribers[channel].push(callback);

        // Send subscribe message to server
        if (this.connected) {
            this.send({
                action: 'subscribe',
                channel: channel
            });
        }

        console.log('[WebSocket] Subscribed to channel:', channel);
    }

    /**
     * Unsubscribe from channel
     *
     * @param {string} channel - Channel name
     * @param {function} callback - Optional specific callback to remove
     */
    unsubscribe(channel, callback = null) {
        if (callback) {
            // Remove specific callback
            if (this.subscribers[channel]) {
                this.subscribers[channel] = this.subscribers[channel].filter(cb => cb !== callback);
            }
        } else {
            // Remove all callbacks for channel
            delete this.subscribers[channel];

            // Send unsubscribe message to server
            if (this.connected) {
                this.send({
                    action: 'unsubscribe',
                    channel: channel
                });
            }
        }

        console.log('[WebSocket] Unsubscribed from channel:', channel);
    }

    /**
     * Listen for specific event types
     *
     * @param {string} eventType - Event type (bot.started, bot.completed, etc.)
     * @param {function} callback - Callback function
     */
    on(eventType, callback) {
        if (!this.eventHandlers[eventType]) {
            this.eventHandlers[eventType] = [];
        }

        this.eventHandlers[eventType].push(callback);
    }

    /**
     * Remove event listener
     *
     * @param {string} eventType - Event type
     * @param {function} callback - Optional specific callback to remove
     */
    off(eventType, callback = null) {
        if (callback) {
            if (this.eventHandlers[eventType]) {
                this.eventHandlers[eventType] = this.eventHandlers[eventType].filter(cb => cb !== callback);
            }
        } else {
            delete this.eventHandlers[eventType];
        }
    }

    /**
     * Trigger internal event
     */
    trigger(eventType, data) {
        const internalEvent = `ws.${eventType}`;
        if (this.eventHandlers[internalEvent]) {
            this.eventHandlers[internalEvent].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('[WebSocket] Event trigger error:', error);
                }
            });
        }
    }

    /**
     * Send message to server
     *
     * @param {object} data - Data to send
     */
    send(data) {
        if (!this.connected || !this.ws) {
            console.warn('[WebSocket] Not connected, message queued');
            return false;
        }

        try {
            this.ws.send(JSON.stringify(data));
            return true;
        } catch (error) {
            console.error('[WebSocket] Failed to send message:', error);
            return false;
        }
    }

    /**
     * Close connection
     */
    close() {
        console.log('[WebSocket] Closing connection');
        this.stopPingTimer();

        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }

        this.connected = false;
    }

    /**
     * Get connection status
     */
    isConnected() {
        return this.connected;
    }

    /**
     * Get connection ID
     */
    getConnectionId() {
        return this.connectionId;
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BotWebSocket;
}
