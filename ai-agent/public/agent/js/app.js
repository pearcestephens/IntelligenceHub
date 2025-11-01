/**
 * AI Agent - Hardened Chat Interface
 * 
 * Production-grade chat interface with:
 * - Robust error handling and retry mechanisms
 * - Security hardening (XSS protection, input validation)
 * - Offline support and connection recovery
 * - Voice input with fallback
 * - Real-time streaming with SSE
 * - Performance optimizations
 * - Accessibility compliance
 * 
 * @version 2.0.0
 * @author AI Agent System
 */

class HardenedChatApp {
    constructor() {
        // Configuration
        this.config = {
            apiBase: this.getApiBase(),
            maxRetries: 3,
            retryDelay: 1000,
            connectionTimeout: 10000,
            messageMaxLength: 4000,
            reconnectInterval: 5000,
            heartbeatInterval: 30000,
            streamChunkTimeout: 30000,
            rateLimit: {
                messages: 20,
                window: 60000 // 1 minute
            }
        };
        
        // State management
        this.state = {
            currentBot: 1,
            isConnected: false,
            isTyping: false,
            isSending: false,
            isVoiceRecording: false,
            messageHistory: [],
            retryQueue: [],
            connectionAttempts: 0,
            lastHeartbeat: null,
            rateLimitCounter: [],
            offlineMessages: []
        };
        
        // DOM elements (cached for performance)
        this.elements = {};
        
        // Event sources and cleanup
        this.eventSource = null;
        this.heartbeatTimer = null;
        this.reconnectTimer = null;
        this.voiceRecognition = null;
        
        // Security and validation
        this.csrfToken = null;
        this.sessionId = null;
        
        // Initialize the application
        this.init();
    }
    
    /**
     * Initialize the chat application
     */
    async init() {
        try {
            console.log('[HardenedChat] Initializing application...');
            
            // Cache DOM elements
            this.cacheElements();
            
            // Set up event listeners
            this.setupEventListeners();
            
            // Initialize security
            await this.initializeSecurity();
            
            // Initialize voice recognition
            this.initializeVoiceRecognition();
            
            // Start connection
            await this.connect();
            
            // Start heartbeat
            this.startHeartbeat();
            
            // Set welcome timestamp
            this.updateWelcomeTime();
            
            // Load message history
            this.loadMessageHistory();
            
            // Initialize offline detection
            this.initializeOfflineDetection();
            
            console.log('[HardenedChat] Application initialized successfully');
            
        } catch (error) {
            console.error('[HardenedChat] Initialization failed:', error);
            this.showError('Failed to initialize chat interface', true);
        }
    }
    
    /**
     * Cache DOM elements for performance
     */
    cacheElements() {
        const selectors = {
            messageForm: '#message-form',
            messageTextarea: '#message-textarea',
            sendButton: '#send-button',
            voiceButton: '#voice-button',
            chatMessages: '#chat-messages',
            typingIndicator: '#typing-indicator',
            statusIndicator: '#status-indicator',
            statusText: '#status-text',
            currentBotName: '#current-bot-name',
            charCount: '#char-count',
            clearChat: '#clear-chat',
            streamMode: '#stream-mode',
            loadingOverlay: '#loading-overlay',
            errorToast: '#error-toast',
            successToast: '#success-toast',
            errorMessage: '#error-message',
            successMessage: '#success-message',
            welcomeTime: '#welcome-time'
        };
        
        for (const [key, selector] of Object.entries(selectors)) {
            this.elements[key] = document.querySelector(selector);
            if (!this.elements[key]) {
                console.warn(`[HardenedChat] Element not found: ${selector}`);
            }
        }
        
        // Bot selector buttons
        this.elements.botButtons = document.querySelectorAll('.bot-btn');
    }
    
    /**
     * Set up event listeners with proper error handling
     */
    setupEventListeners() {
        // Form submission
        if (this.elements.messageForm) {
            this.elements.messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSendMessage();
            });
        }
        
        // Textarea events
        if (this.elements.messageTextarea) {
            this.elements.messageTextarea.addEventListener('input', (e) => {
                this.handleTextareaInput(e);
            });
            
            this.elements.messageTextarea.addEventListener('keydown', (e) => {
                this.handleTextareaKeydown(e);
            });
            
            this.elements.messageTextarea.addEventListener('paste', (e) => {
                this.handleTextareaPaste(e);
            });
        }
        
        // Voice button
        if (this.elements.voiceButton) {
            this.elements.voiceButton.addEventListener('click', () => {
                this.handleVoiceToggle();
            });
        }
        
        // Bot selector buttons
        this.elements.botButtons?.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const botId = parseInt(e.currentTarget.dataset.bot);
                this.switchBot(botId);
            });
        });
        
        // Clear chat
        if (this.elements.clearChat) {
            this.elements.clearChat.addEventListener('click', () => {
                this.clearChatHistory();
            });
        }
        
        // Window events
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });
        
        window.addEventListener('online', () => {
            this.handleOnline();
        });
        
        window.addEventListener('offline', () => {
            this.handleOffline();
        });
        
        // Page visibility API
        document.addEventListener('visibilitychange', () => {
            this.handleVisibilityChange();
        });
        
        console.log('[HardenedChat] Event listeners set up');
    }
    
    /**
     * Initialize security measures
     */
    async initializeSecurity() {
        try {
            // Get CSRF token
            const response = await this.makeRequest('/api/security.php', {
                method: 'GET'
            });
            
            if (response.success) {
                this.csrfToken = response.csrf_token;
                this.sessionId = response.session_id;
                console.log('[HardenedChat] Security initialized');
            } else {
                throw new Error('Failed to get security tokens');
            }
        } catch (error) {
            console.warn('[HardenedChat] Security initialization failed:', error);
            // Continue without CSRF if not available
        }
    }
    
    /**
     * Initialize voice recognition with fallbacks
     */
    initializeVoiceRecognition() {
        try {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                // Hide voice button if not supported
                if (this.elements.voiceButton) {
                    this.elements.voiceButton.style.display = 'none';
                }
                return;
            }
            
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.voiceRecognition = new SpeechRecognition();
            
            this.voiceRecognition.continuous = false;
            this.voiceRecognition.interimResults = false;
            this.voiceRecognition.lang = 'en-US';
            
            this.voiceRecognition.onstart = () => {
                this.state.isVoiceRecording = true;
                this.updateVoiceButton();
                console.log('[HardenedChat] Voice recording started');
            };
            
            this.voiceRecognition.onresult = (event) => {
                const result = event.results[0][0].transcript;
                if (result && this.elements.messageTextarea) {
                    this.elements.messageTextarea.value = result;
                    this.handleTextareaInput({ target: this.elements.messageTextarea });
                    this.elements.messageTextarea.focus();
                }
            };
            
            this.voiceRecognition.onerror = (event) => {
                console.warn('[HardenedChat] Voice recognition error:', event.error);
                this.state.isVoiceRecording = false;
                this.updateVoiceButton();
                
                if (event.error === 'not-allowed') {
                    this.showError('Microphone access denied. Please enable microphone permissions.');
                }
            };
            
            this.voiceRecognition.onend = () => {
                this.state.isVoiceRecording = false;
                this.updateVoiceButton();
                console.log('[HardenedChat] Voice recording ended');
            };
            
            console.log('[HardenedChat] Voice recognition initialized');
            
        } catch (error) {
            console.warn('[HardenedChat] Voice recognition initialization failed:', error);
            if (this.elements.voiceButton) {
                this.elements.voiceButton.style.display = 'none';
            }
        }
    }
    
    /**
     * Connect to the chat service
     */
    async connect() {
        try {
            console.log('[HardenedChat] Attempting to connect...');
            this.updateConnectionStatus(false, 'Connecting...');
            
            // Test connection
            const response = await this.makeRequest('/api/health.php', {
                method: 'GET'
            });
            
            if (response.success) {
                this.state.isConnected = true;
                this.state.connectionAttempts = 0;
                this.updateConnectionStatus(true, 'Connected');
                console.log('[HardenedChat] Connection established');
                
                // Send any offline messages
                await this.sendOfflineMessages();
                
            } else {
                throw new Error('Health check failed');
            }
            
        } catch (error) {
            console.error('[HardenedChat] Connection failed:', error);
            this.state.isConnected = false;
            this.state.connectionAttempts++;
            
            if (this.state.connectionAttempts < this.config.maxRetries) {
                this.updateConnectionStatus(false, `Retrying... (${this.state.connectionAttempts}/${this.config.maxRetries})`);
                setTimeout(() => this.connect(), this.config.retryDelay * this.state.connectionAttempts);
            } else {
                this.updateConnectionStatus(false, 'Connection failed');
                this.showError('Unable to connect to chat service. Please refresh the page.');
            }
        }
    }
    
    /**
     * Handle sending messages with comprehensive error handling
     */
    async handleSendMessage() {
        if (this.state.isSending) return;
        
        const message = this.elements.messageTextarea?.value?.trim();
        if (!message || !this.validateMessage(message)) {
            return;
        }
        
        // Rate limiting check
        if (!this.checkRateLimit()) {
            this.showError('Rate limit exceeded. Please wait before sending another message.');
            return;
        }
        
        try {
            this.state.isSending = true;
            this.updateSendButton();
            
            // Add user message to chat
            this.addMessage('user', message);
            
            // Clear textarea
            if (this.elements.messageTextarea) {
                this.elements.messageTextarea.value = '';
                this.handleTextareaInput({ target: this.elements.messageTextarea });
            }
            
            // Show typing indicator
            this.showTypingIndicator();
            
            // Send message
            const response = await this.sendMessageToAPI(message);
            
            if (response.success) {
                // Handle streaming or direct response
                if (this.elements.streamMode?.checked && response.stream_url) {
                    await this.handleStreamResponse(response.stream_url);
                } else if (response.message) {
                    this.addMessage('assistant', response.message);
                }
                
                this.updateMessageHistory();
                
            } else {
                throw new Error(response.error || 'Unknown error occurred');
            }
            
        } catch (error) {
            console.error('[HardenedChat] Send message failed:', error);
            this.handleSendError(error, message);
        } finally {
            this.state.isSending = false;
            this.updateSendButton();
            this.hideTypingIndicator();
        }
    }
    
    /**
     * Send message to API with retries
     */
    async sendMessageToAPI(message, retryCount = 0) {
        try {
            const payload = {
                message: this.sanitizeInput(message),
                bot_id: this.state.currentBot,
                stream: this.elements.streamMode?.checked || false,
                session_id: this.sessionId
            };
            
            if (this.csrfToken) {
                payload.csrf_token = this.csrfToken;
            }
            
            const response = await this.makeRequest('/api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });
            
            return response;
            
        } catch (error) {
            if (retryCount < this.config.maxRetries) {
                console.log(`[HardenedChat] Retrying send (${retryCount + 1}/${this.config.maxRetries})...`);
                await this.delay(this.config.retryDelay * (retryCount + 1));
                return this.sendMessageToAPI(message, retryCount + 1);
            }
            throw error;
        }
    }
    
    /**
     * Handle streaming response with SSE
     */
    async handleStreamResponse(streamUrl) {
        return new Promise((resolve, reject) => {
            let responseText = '';
            let messageElement = null;
            let timeoutId = null;
            
            // Create message element for streaming
            messageElement = this.addMessage('assistant', '', true);
            
            try {
                const eventSource = new EventSource(streamUrl);
                
                // Timeout handler
                timeoutId = setTimeout(() => {
                    eventSource.close();
                    reject(new Error('Stream timeout'));
                }, this.config.streamChunkTimeout);
                
                eventSource.onmessage = (event) => {
                    try {
                        clearTimeout(timeoutId);
                        
                        const data = JSON.parse(event.data);
                        
                        if (data.chunk) {
                            responseText += data.chunk;
                            this.updateMessageContent(messageElement, responseText);
                        }
                        
                        if (data.done) {
                            eventSource.close();
                            resolve(responseText);
                        } else {
                            // Reset timeout for next chunk
                            timeoutId = setTimeout(() => {
                                eventSource.close();
                                reject(new Error('Stream timeout'));
                            }, this.config.streamChunkTimeout);
                        }
                        
                    } catch (error) {
                        console.error('[HardenedChat] Stream parsing error:', error);
                    }
                };
                
                eventSource.onerror = (error) => {
                    console.error('[HardenedChat] Stream error:', error);
                    eventSource.close();
                    clearTimeout(timeoutId);
                    reject(error);
                };
                
            } catch (error) {
                clearTimeout(timeoutId);
                reject(error);
            }
        });
    }
    
    /**
     * Add message to chat with XSS protection
     */
    addMessage(type, content, isStreaming = false) {
        if (!this.elements.chatMessages) return null;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.innerHTML = type === 'user' 
            ? '<i class="fas fa-user" aria-hidden="true"></i>'
            : '<i class="fas fa-robot" aria-hidden="true"></i>';
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.innerHTML = `<i class="fas fa-clock me-1" aria-hidden="true"></i>${this.getCurrentTime()}`;
        
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(contentDiv);
        
        if (!isStreaming) {
            this.updateMessageContent(messageDiv, content);
            contentDiv.appendChild(timeDiv);
        }
        
        this.elements.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
        
        // Store in history
        if (!isStreaming) {
            this.state.messageHistory.push({
                type,
                content,
                timestamp: Date.now()
            });
        }
        
        return messageDiv;
    }
    
    /**
     * Update message content with Markdown and XSS protection
     */
    updateMessageContent(messageElement, content) {
        if (!messageElement) return;
        
        const contentDiv = messageElement.querySelector('.message-content');
        if (!contentDiv) return;
        
        try {
            // Sanitize and render Markdown
            const sanitizedHtml = DOMPurify.sanitize(marked.parse(content));
            contentDiv.innerHTML = sanitizedHtml;
            
            // Highlight code blocks
            contentDiv.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
            
            this.scrollToBottom();
            
        } catch (error) {
            console.error('[HardenedChat] Content update error:', error);
            contentDiv.textContent = content; // Fallback to plain text
        }
    }
    
    /**
     * Handle textarea input events
     */
    handleTextareaInput(event) {
        const textarea = event.target;
        const value = textarea.value;
        
        // Update character count
        if (this.elements.charCount) {
            this.elements.charCount.textContent = value.length;
        }
        
        // Auto-resize textarea
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        
        // Update send button state
        this.updateSendButton();
        
        // Validate length
        if (value.length > this.config.messageMaxLength) {
            textarea.value = value.substring(0, this.config.messageMaxLength);
        }
    }
    
    /**
     * Handle textarea keydown events
     */
    handleTextareaKeydown(event) {
        // Send on Enter (unless Shift+Enter)
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.handleSendMessage();
        }
        
        // Escape to clear
        if (event.key === 'Escape') {
            event.target.value = '';
            this.handleTextareaInput(event);
        }
    }
    
    /**
     * Handle textarea paste events
     */
    handleTextareaPaste(event) {
        // Allow paste but validate content
        setTimeout(() => {
            this.handleTextareaInput(event);
        }, 10);
    }
    
    /**
     * Handle voice toggle
     */
    handleVoiceToggle() {
        if (!this.voiceRecognition) {
            this.showError('Voice recognition not supported in this browser');
            return;
        }
        
        if (this.state.isVoiceRecording) {
            this.voiceRecognition.stop();
        } else {
            try {
                this.voiceRecognition.start();
            } catch (error) {
                console.error('[HardenedChat] Voice recognition error:', error);
                this.showError('Failed to start voice recognition');
            }
        }
    }
    
    /**
     * Switch bot with validation
     */
    async switchBot(botId) {
        if (botId === this.state.currentBot || this.state.isSending) return;
        
        try {
            // Validate bot exists
            const response = await this.makeRequest(`/api/bot-info.php?bot_id=${botId}`);
            
            if (response.success && response.bot) {
                this.state.currentBot = botId;
                
                // Update UI
                this.elements.botButtons?.forEach(btn => {
                    btn.classList.toggle('active', parseInt(btn.dataset.bot) === botId);
                });
                
                if (this.elements.currentBotName) {
                    this.elements.currentBotName.textContent = response.bot.name;
                }
                
                console.log(`[HardenedChat] Switched to bot ${botId}: ${response.bot.name}`);
                
            } else {
                throw new Error('Invalid bot selected');
            }
            
        } catch (error) {
            console.error('[HardenedChat] Bot switch failed:', error);
            this.showError('Failed to switch bot');
        }
    }
    
    /**
     * Clear chat history with confirmation
     */
    clearChatHistory() {
        if (this.state.messageHistory.length === 0) return;
        
        if (confirm('Are you sure you want to clear the chat history? This action cannot be undone.')) {
            // Clear UI
            if (this.elements.chatMessages) {
                const welcomeMessage = this.elements.chatMessages.querySelector('.message.assistant');
                this.elements.chatMessages.innerHTML = '';
                if (welcomeMessage) {
                    this.elements.chatMessages.appendChild(welcomeMessage);
                }
            }
            
            // Clear state
            this.state.messageHistory = [];
            
            // Clear local storage
            this.clearStoredHistory();
            
            this.showSuccess('Chat history cleared');
            console.log('[HardenedChat] Chat history cleared');
        }
    }
    
    /**
     * Update connection status
     */
    updateConnectionStatus(connected, text) {
        if (this.elements.statusIndicator) {
            this.elements.statusIndicator.classList.toggle('connected', connected);
        }
        
        if (this.elements.statusText) {
            this.elements.statusText.textContent = text;
        }
        
        this.state.isConnected = connected;
    }
    
    /**
     * Update send button state
     */
    updateSendButton() {
        if (!this.elements.sendButton) return;
        
        const hasText = this.elements.messageTextarea?.value?.trim().length > 0;
        const canSend = hasText && this.state.isConnected && !this.state.isSending;
        
        this.elements.sendButton.disabled = !canSend;
        
        if (this.state.isSending) {
            this.elements.sendButton.innerHTML = '<div class="spinner"></div>';
        } else {
            this.elements.sendButton.innerHTML = '<i class="fas fa-paper-plane" aria-hidden="true"></i>';
        }
    }
    
    /**
     * Update voice button state
     */
    updateVoiceButton() {
        if (!this.elements.voiceButton) return;
        
        this.elements.voiceButton.classList.toggle('recording', this.state.isVoiceRecording);
        
        const icon = this.elements.voiceButton.querySelector('i');
        if (icon) {
            icon.className = this.state.isVoiceRecording 
                ? 'fas fa-stop' 
                : 'fas fa-microphone';
        }
    }
    
    /**
     * Show/hide typing indicator
     */
    showTypingIndicator() {
        if (this.elements.typingIndicator) {
            this.elements.typingIndicator.style.display = 'flex';
            this.scrollToBottom();
        }
        this.state.isTyping = true;
    }
    
    hideTypingIndicator() {
        if (this.elements.typingIndicator) {
            this.elements.typingIndicator.style.display = 'none';
        }
        this.state.isTyping = false;
    }
    
    /**
     * Scroll chat to bottom
     */
    scrollToBottom() {
        if (this.elements.chatMessages) {
            this.elements.chatMessages.scrollTop = this.elements.chatMessages.scrollHeight;
        }
    }
    
    /**
     * Show error message
     */
    showError(message, persistent = false) {
        console.error('[HardenedChat] Error:', message);
        
        if (this.elements.errorMessage && this.elements.errorToast) {
            this.elements.errorMessage.textContent = message;
            
            const toast = new bootstrap.Toast(this.elements.errorToast, {
                autohide: !persistent,
                delay: persistent ? 0 : 5000
            });
            toast.show();
        }
    }
    
    /**
     * Show success message
     */
    showSuccess(message) {
        if (this.elements.successMessage && this.elements.successToast) {
            this.elements.successMessage.textContent = message;
            
            const toast = new bootstrap.Toast(this.elements.successToast);
            toast.show();
        }
    }
    
    /**
     * Validate message input
     */
    validateMessage(message) {
        if (!message || typeof message !== 'string') {
            return false;
        }
        
        if (message.length > this.config.messageMaxLength) {
            this.showError(`Message too long. Maximum ${this.config.messageMaxLength} characters allowed.`);
            return false;
        }
        
        // Check for potential XSS
        if (/<script|javascript:|data:/i.test(message)) {
            this.showError('Invalid message content detected.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Sanitize input to prevent XSS
     */
    sanitizeInput(input) {
        if (typeof input !== 'string') return '';
        
        return input
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;')
            .trim();
    }
    
    /**
     * Check rate limiting
     */
    checkRateLimit() {
        const now = Date.now();
        
        // Remove old entries
        this.state.rateLimitCounter = this.state.rateLimitCounter.filter(
            timestamp => now - timestamp < this.config.rateLimit.window
        );
        
        // Check limit
        if (this.state.rateLimitCounter.length >= this.config.rateLimit.messages) {
            return false;
        }
        
        // Add current request
        this.state.rateLimitCounter.push(now);
        return true;
    }
    
    /**
     * Make HTTP request with error handling
     */
    async makeRequest(url, options = {}) {
        const fullUrl = this.config.apiBase + url;
        
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            timeout: this.config.connectionTimeout
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), mergedOptions.timeout);
            
            const response = await fetch(fullUrl, {
                ...mergedOptions,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            return data;
            
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }
            throw error;
        }
    }
    
    /**
     * Get API base URL with fallbacks
     */
    getApiBase() {
        // Try to determine from current URL
        const currentPath = window.location.pathname;
        const basePath = currentPath.substring(0, currentPath.lastIndexOf('/'));
        return basePath || '/assets/services/neuro/neuro_/ai-agent/api';
    }
    
    /**
     * Handle send errors with retry options
     */
    handleSendError(error, originalMessage) {
        console.error('[HardenedChat] Send error:', error);
        
        let errorMessage = 'Failed to send message. ';
        
        if (error.message.includes('timeout')) {
            errorMessage += 'Request timed out.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage += 'Network error.';
        } else {
            errorMessage += error.message || 'Unknown error occurred.';
        }
        
        // Add retry option for non-validation errors
        if (!error.message.includes('Invalid') && !error.message.includes('Rate limit')) {
            this.state.retryQueue.push(originalMessage);
            errorMessage += ` <button class="retry-button" onclick="hardenedChat.retryLastMessage()">Retry</button>`;
        }
        
        // Add error message to chat
        this.addMessage('system', `❌ ${errorMessage}`);
    }
    
    /**
     * Retry last failed message
     */
    async retryLastMessage() {
        if (this.state.retryQueue.length === 0) return;
        
        const message = this.state.retryQueue.pop();
        if (this.elements.messageTextarea) {
            this.elements.messageTextarea.value = message;
            this.handleTextareaInput({ target: this.elements.messageTextarea });
        }
        
        await this.handleSendMessage();
    }
    
    /**
     * Handle online/offline events
     */
    handleOnline() {
        console.log('[HardenedChat] Connection restored');
        this.updateConnectionStatus(true, 'Connection restored');
        this.connect();
    }
    
    handleOffline() {
        console.log('[HardenedChat] Connection lost');
        this.updateConnectionStatus(false, 'Offline');
        this.state.isConnected = false;
    }
    
    /**
     * Initialize offline detection and support
     */
    initializeOfflineDetection() {
        // Check if already offline
        if (!navigator.onLine) {
            this.handleOffline();
        }
        
        console.log('[HardenedChat] Offline detection initialized');
    }
    
    /**
     * Handle visibility change (tab switching)
     */
    handleVisibilityChange() {
        if (document.hidden) {
            // Page hidden - pause heartbeat
            this.stopHeartbeat();
        } else {
            // Page visible - resume heartbeat
            this.startHeartbeat();
            
            // Check connection
            if (!this.state.isConnected) {
                this.connect();
            }
        }
    }
    
    /**
     * Start heartbeat to maintain connection
     */
    startHeartbeat() {
        this.stopHeartbeat(); // Clear any existing timer
        
        this.heartbeatTimer = setInterval(async () => {
            if (this.state.isConnected) {
                try {
                    await this.makeRequest('/api/health.php');
                    this.state.lastHeartbeat = Date.now();
                } catch (error) {
                    console.warn('[HardenedChat] Heartbeat failed:', error);
                    this.state.isConnected = false;
                    this.updateConnectionStatus(false, 'Connection lost');
                }
            }
        }, this.config.heartbeatInterval);
    }
    
    /**
     * Stop heartbeat
     */
    stopHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }
    }
    
    /**
     * Send offline messages when reconnected
     */
    async sendOfflineMessages() {
        if (this.state.offlineMessages.length === 0) return;
        
        console.log(`[HardenedChat] Sending ${this.state.offlineMessages.length} offline messages`);
        
        for (const message of this.state.offlineMessages) {
            try {
                await this.sendMessageToAPI(message);
            } catch (error) {
                console.error('[HardenedChat] Failed to send offline message:', error);
                break; // Stop on first failure
            }
        }
        
        this.state.offlineMessages = [];
    }
    
    /**
     * Load message history from storage
     */
    loadMessageHistory() {
        try {
            const stored = localStorage.getItem('hardenedChat_history');
            if (stored) {
                this.state.messageHistory = JSON.parse(stored);
                console.log(`[HardenedChat] Loaded ${this.state.messageHistory.length} messages from history`);
            }
        } catch (error) {
            console.warn('[HardenedChat] Failed to load message history:', error);
        }
    }
    
    /**
     * Update message history in storage
     */
    updateMessageHistory() {
        try {
            // Keep only last 100 messages
            const recentHistory = this.state.messageHistory.slice(-100);
            localStorage.setItem('hardenedChat_history', JSON.stringify(recentHistory));
        } catch (error) {
            console.warn('[HardenedChat] Failed to save message history:', error);
        }
    }
    
    /**
     * Clear stored history
     */
    clearStoredHistory() {
        try {
            localStorage.removeItem('hardenedChat_history');
        } catch (error) {
            console.warn('[HardenedChat] Failed to clear stored history:', error);
        }
    }
    
    /**
     * Update welcome timestamp
     */
    updateWelcomeTime() {
        if (this.elements.welcomeTime) {
            this.elements.welcomeTime.textContent = this.getCurrentTime();
        }
    }
    
    /**
     * Get current time formatted
     */
    getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
    }
    
    /**
     * Utility delay function
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Cleanup resources
     */
    cleanup() {
        console.log('[HardenedChat] Cleaning up resources...');
        
        // Close event source
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
        
        // Stop timers
        this.stopHeartbeat();
        
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }
        
        // Stop voice recognition
        if (this.voiceRecognition && this.state.isVoiceRecording) {
            this.voiceRecognition.stop();
        }
        
        // Save current state
        this.updateMessageHistory();
        
        console.log('[HardenedChat] Cleanup completed');
    }
}

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.hardenedChat = new HardenedChatApp();
});

// Global error handler
window.addEventListener('error', (event) => {
    console.error('[HardenedChat] Global error:', event.error);
    if (window.hardenedChat) {
        window.hardenedChat.showError('An unexpected error occurred. Please refresh the page if issues persist.');
    }
});

// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', (event) => {
    console.error('[HardenedChat] Unhandled promise rejection:', event.reason);
    event.preventDefault(); // Prevent console spam
    if (window.hardenedChat) {
        window.hardenedChat.showError('A background operation failed. Please try again.');
    }
});

console.log('[HardenedChat] Script loaded successfully');