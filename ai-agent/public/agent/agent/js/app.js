/**
 * AI Agent Frontend Application
 * 
 * Production-grade JavaScript application for AI Agent interface with:
 * - Real-time streaming chat via Server-Sent Events
 * - Voice input with Web Speech API
 * - File upload and knowledge base management
 * - Responsive design with modern UX
 * - Error handling and offline support
 * - Progress tracking and notifications
 * 
 * @version 1.0.0
 * @author Production AI Agent System
 */

class AIAgentApp {
    constructor() {
        // HARD-CODED FIX FOR BROKEN BOT PATHS
        // Force the correct API path regardless of detection
        this.apiBase = 'api';
        
        console.log('FORCED API Base to:', this.apiBase);
        console.log('Current location:', window.location.href);
        this.currentConversation = null;
        this.conversations = [];
        this.isConnected = false;
        this.eventSource = null;
        this.recognition = null;
        this.isRecording = false;
        this.messageHistory = [];
        this.currentModel = 'openai'; // Default to OpenAI
        this.availableModels = {
            'openai': {
                name: 'GPT-4o Mini',
                endpoint: 'chat.php',
                icon: 'fas fa-brain',
                color: '#0d6efd'
            },
            'claude': {
                name: 'Claude 3.5 Sonnet',
                endpoint: 'claude-chat.php',
                icon: 'fas fa-robot',
                color: '#ff6b35'
            }
        };
        
        // Initialize application
        this.init();
    }
    
    /**
     * Initialize the application
     */
    async init() {
        try {
            // Set up event listeners
            this.setupEventListeners();
            
            // Restore saved model preference
            try {
                const savedModel = localStorage.getItem('aiagent.model');
                if (savedModel && this.availableModels[savedModel]) {
                    this.currentModel = savedModel;
                    const radioButton = document.getElementById(`model-${savedModel}`);
                    if (radioButton) {
                        radioButton.checked = true;
                    }
                    this.switchModel(savedModel);
                }
            } catch (_) { /* ignore storage errors */ }
            
            // Initialize speech recognition if supported
            this.initSpeechRecognition();
            
            // Check system health
            await this.checkHealth();
            
            // Load conversations
            await this.loadConversations();
            
            // Set up auto-resize for textarea
            this.setupTextareaResize();
            
            console.log('AI Agent App initialized successfully');
            
        } catch (error) {
            console.error('Failed to initialize app:', error);
            this.showNotification('Error', 'Failed to initialize application', 'error');
        }
    }
    
    /**
     * Set up all event listeners
     */
    setupEventListeners() {
        // AI Model switcher
        document.querySelectorAll('input[name="aiModel"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.switchModel(e.target.value);
            });
        });
        
        // Message form
        const messageForm = document.getElementById('message-form');
        messageForm.addEventListener('submit', (e) => this.handleMessageSubmit(e));
        
        // Message input
        const messageInput = document.getElementById('message-input');
        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.handleMessageSubmit(e);
            }
            // Keyboard shortcut: Cmd/Ctrl + Enter to send
            if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                e.preventDefault();
                this.handleMessageSubmit(e);
            }
        });
        
        // New conversation button
        document.getElementById('new-conversation').addEventListener('click', () => {
            this.createNewConversation();
        });
        
        // Voice button
        document.getElementById('voice-btn').addEventListener('click', () => {
            this.toggleVoiceRecording();
        });
        
        // Knowledge panel toggle
        document.getElementById('knowledge-toggle').addEventListener('click', () => {
            this.toggleKnowledgePanel();
        });
        
        // Knowledge panel close
        document.getElementById('knowledge-close').addEventListener('click', () => {
            this.toggleKnowledgePanel();
        });
        
        // Health check button
        document.getElementById('health-check').addEventListener('click', () => {
            this.checkHealth();
        });
        
        // File upload
        const fileInput = document.getElementById('file-input');
        const dropZone = document.getElementById('file-drop-zone');
        
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => this.handleDragOver(e));
        dropZone.addEventListener('drop', (e) => this.handleFileDrop(e));
        fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        
        // Knowledge search
        document.getElementById('search-btn').addEventListener('click', () => {
            this.searchKnowledge();
        });
        
        document.getElementById('knowledge-search').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.searchKnowledge();
            }
        });
        
        // Window events
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });

        // Global key handlers (Escape closes knowledge panel)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const panel = document.getElementById('knowledge-panel');
                if (panel && panel.classList.contains('show')) {
                    this.toggleKnowledgePanel();
                }
            }
        });

        // Persist toggle states in localStorage
        try {
            const streamToggle = document.getElementById('stream-toggle');
            const toolsToggle = document.getElementById('tools-toggle');
            const savedStream = localStorage.getItem('aiagent.stream')
            const savedTools = localStorage.getItem('aiagent.tools')
            if (savedStream !== null) streamToggle.checked = savedStream === '1';
            if (savedTools !== null) toolsToggle.checked = savedTools === '1';
            streamToggle.addEventListener('change', () => localStorage.setItem('aiagent.stream', streamToggle.checked ? '1' : '0'));
            toolsToggle.addEventListener('change', () => localStorage.setItem('aiagent.tools', toolsToggle.checked ? '1' : '0'));
        } catch (_) { /* ignore storage errors */ }
    }
    
    /**
     * Switch between AI models (OpenAI/Claude)
     */
    switchModel(model) {
        if (!this.availableModels[model]) {
            console.error('Invalid model:', model);
            return;
        }
        
        this.currentModel = model;
        const modelInfo = this.availableModels[model];
        
        // Update UI
        const appTitle = document.getElementById('app-title');
        if (appTitle) {
            appTitle.innerHTML = `<i class="${modelInfo.icon} me-2" aria-hidden="true"></i>AI Agent - ${modelInfo.name}`;
        }
        
        // Update status message
        const statusText = document.getElementById('status-text');
        if (statusText) {
            statusText.textContent = `${modelInfo.name} Ready`;
        }
        
        // Store preference
        try {
            localStorage.setItem('aiagent.model', model);
        } catch (_) { /* ignore storage errors */ }
        
        // Show notification
        this.showNotification(`Switched to ${modelInfo.name}`, 'info');
        
        console.log(`Switched to ${modelInfo.name}`);
    }
    
    /**
     * Handle message form submission
     */
    async handleMessageSubmit(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();
        
        if (!message) return;
        
        if (!this.currentConversation) {
            await this.createNewConversation();
        }
        
        // Clear input and disable form
        messageInput.value = '';
        this.setFormEnabled(false);
        this.showTypingIndicator(true);
        
        try {
            // Get form options
            const streamEnabled = document.getElementById('stream-toggle').checked;
            const toolsEnabled = document.getElementById('tools-toggle').checked;
            
            // Add user message to UI immediately
            this.addMessageToUI({
                role: 'user',
                content: message,
                created_at: new Date().toISOString()
            });
            
            if (streamEnabled) {
                await this.sendMessageStreaming(message, toolsEnabled);
            } else {
                await this.sendMessageRegular(message, toolsEnabled);
            }
            
        } catch (error) {
            console.error('Failed to send message:', error);
            this.showNotification('Error', `Failed to send message: ${error.message}`, 'error');
        } finally {
            this.setFormEnabled(true);
            this.showTypingIndicator(false);
            messageInput.focus();
        }
    }
    
    /**
     * Send message with streaming response
     */
    async sendMessageStreaming(message, toolsEnabled) {
        return new Promise((resolve, reject) => {
            const modelInfo = this.availableModels[this.currentModel];
            const url = `${this.apiBase}/${modelInfo.endpoint}`;
            
            // Close existing EventSource if any
            if (this.eventSource) {
                this.eventSource.close();
            }
            
            // Set up Server-Sent Events
            const postData = {
                message: message,
                conversation_id: this.currentConversation,
                stream: true,
                ...(this.currentModel === 'openai' && { tools_enabled: toolsEnabled })
            };
            
            // Use fetch for POST with EventSource simulation
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream'
                },
                body: JSON.stringify(postData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let currentMessage = null;
                let buffer = '';
                let currentEvent = null;
                
                const processStream = () => {
                    reader.read().then(({ done, value }) => {
                        if (done) {
                            resolve();
                            return;
                        }
                        
                        // Decode chunk and add to buffer
                        buffer += decoder.decode(value, { stream: true });
                        
                        // Process complete lines
                        const lines = buffer.split('\n');
                        buffer = lines.pop(); // Keep incomplete line in buffer
                        
                        for (const line of lines) {
                            if (line.startsWith('event: ')) {
                                currentEvent = line.substring(7).trim();
                                continue;
                            }
                            
                            if (line.startsWith('data: ')) {
                                try {
                                    const payload = JSON.parse(line.substring(6));
                                    const data = (payload && payload.type) ? payload : { type: currentEvent || 'message', data: payload };
                                    
                                    // Special handling for terminal events
                                    if (currentEvent === 'complete') {
                                        // Nothing required here; backend already sent final result in payload
                                        this.hideProgress();
                                    } else if (currentEvent === 'end') {
                                        this.hideProgress();
                                        resolve();
                                        return; // Stop processing further
                                    } else {
                                        this.handleStreamEvent(data, currentMessage);
                                    }
                                    
                                    // Update current message reference
                                    if (data.type === 'message_added' && data.data.role === 'assistant') {
                                        currentMessage = data.data;
                                    }
                                    
                                } catch (e) {
                                    // Ignore JSON parse errors for non-JSON data
                                }
                            }
                        }
                        
                        processStream();
                    }).catch(reject);
                };
                
                processStream();
            })
            .catch(reject);
        });
    }
    
    /**
     * Send message with regular response
     */
    async sendMessageRegular(message, toolsEnabled) {
        const modelInfo = this.availableModels[this.currentModel];
        const response = await fetch(`${this.apiBase}/${modelInfo.endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                conversation_id: this.currentConversation,
                stream: false,
                ...(this.currentModel === 'openai' && { enable_tools: toolsEnabled })
            })
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error?.message || 'Unknown error occurred');
        }
        
        // Add assistant response to UI
        this.addMessageToUI({
            role: 'assistant',
            content: result.response.content,
            tool_calls: result.response.tool_calls || [],
            created_at: new Date().toISOString()
        });
    }
    
    /**
     * Handle streaming events
     */
    handleStreamEvent(data, currentMessage) {
        switch (data.type) {
            case 'message_added':
                if (data.data.role === 'assistant') {
                    this.addMessageToUI(data.data);
                }
                break;
                
            case 'content_chunk':
                this.appendToMessage(data.data.content);
                break;
                
            case 'tool_call_chunk':
                // Show progressive tool call construction for visibility
                try {
                    const calls = data.data.tool_calls || [];
                    if (calls.length > 0) {
                        const names = calls
                            .map(tc => (tc.function && tc.function.name) ? tc.function.name : (tc.tool || 'tool'))
                            .filter(Boolean)
                            .join(', ');
                        this.showProgress(`Preparing tool call(s): ${names}`);
                    }
                } catch (_) { /* no-op */ }
                break;

            case 'ai_thinking':
                this.showProgress(`AI thinking... (${data.data.model})`);
                break;
                
            case 'tool_execution_start':
                this.showProgress(`Executing ${data.data.tool_count} tool(s)...`);
                break;
                
            case 'tool_execution':
                this.showProgress(`Running: ${data.data.tool_name}...`);
                break;
                
            case 'tool_result':
                if (!data.data.success) {
                    this.showNotification('Tool Error', `${data.data.tool_name} failed`, 'error');
                }
                break;
            
            case 'tool_execution_complete':
                // All tools for this assistant turn have finished
                this.hideProgress();
                break;
                
            case 'message_complete':
                this.hideProgress();
                if (data.data.tool_calls && data.data.tool_calls.length > 0) {
                    this.addToolCallsToMessage(data.data.tool_calls);
                }
                // Replace the last assistant message's content with rendered Markdown
                if (typeof data.data.content === 'string') {
                    this.updateLastAssistantMessageContent(data.data.content);
                }
                break;
                
            case 'error':
                this.hideProgress();
                this.showNotification('Error', data.data.message, 'error');
                break;
        }
    }
    
    /**
     * Add message to UI
     */
    addMessageToUI(message) {
        const container = document.getElementById('messages-container');
        
        // Clear welcome message if present
        if (container.querySelector('.text-center')) {
            container.innerHTML = '';
        }
        
        const messageElement = this.createMessageElement(message);
        container.appendChild(messageElement);
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
        
        // Store in history
        this.messageHistory.push(message);
    }
    
    /**
     * Create message DOM element
     */
    createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.role}`;
        messageDiv.setAttribute('data-message-id', message.message_id || '');
        
        // Avatar
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        
        const avatarIcon = document.createElement('i');
        switch (message.role) {
            case 'user':
                avatarIcon.className = 'fas fa-user';
                break;
            case 'assistant':
                avatarIcon.className = 'fas fa-robot';
                break;
            case 'system':
                avatarIcon.className = 'fas fa-cog';
                break;
        }
        avatar.appendChild(avatarIcon);
        
        // Content
        const content = document.createElement('div');
        content.className = 'message-content';
        
        const text = document.createElement('div');
        text.className = 'message-text';
        
        // Render markdown if it's from assistant
        if (message.role === 'assistant') {
            text.innerHTML = DOMPurify.sanitize(marked.parse(message.content));
            // Highlight code blocks
            text.querySelectorAll('pre code').forEach(block => {
                hljs.highlightElement(block);
            });
        } else {
            text.textContent = message.content;
        }
        
        content.appendChild(text);
        
        // Meta information
        if (message.created_at) {
            const meta = document.createElement('div');
            meta.className = 'message-meta';
            meta.textContent = new Date(message.created_at).toLocaleTimeString();
            content.appendChild(meta);
        }
        
        // Tool calls
        if (message.tool_calls && message.tool_calls.length > 0) {
            const toolCallsDiv = this.createToolCallsElement(message.tool_calls);
            content.appendChild(toolCallsDiv);
        }
        
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(content);
        
        return messageDiv;
    }
    
    /**
     * Create tool calls display element
     */
    createToolCallsElement(toolCalls) {
        const toolCallsDiv = document.createElement('div');
        toolCallsDiv.className = 'tool-calls';
        
        toolCalls.forEach(toolCall => {
            const toolDiv = document.createElement('div');
            toolDiv.className = 'tool-call';
            
            const header = document.createElement('div');
            header.className = 'tool-call-header';
            header.innerHTML = `<i class="fas fa-tools me-1"></i>${toolCall.tool || toolCall.function}`;
            
            const result = document.createElement('div');
            result.className = 'tool-call-result';
            
            if (toolCall.result && toolCall.result.success) {
                result.innerHTML = `<small class="text-success"><i class="fas fa-check me-1"></i>Executed successfully</small>`;
            } else {
                result.innerHTML = `<small class="text-danger"><i class="fas fa-times me-1"></i>Failed</small>`;
            }
            
            toolDiv.appendChild(header);
            toolDiv.appendChild(result);
            toolCallsDiv.appendChild(toolDiv);
        });
        
        return toolCallsDiv;
    }
    
    /**
     * Append content to the last message
     */
    appendToMessage(content) {
        const container = document.getElementById('messages-container');
        const lastMessage = container.querySelector('.message:last-child .message-text');
        
        if (lastMessage) {
            lastMessage.textContent += content;
        }
    }
    
    /**
     * Add tool calls to the last message
     */
    addToolCallsToMessage(toolCalls) {
        const container = document.getElementById('messages-container');
        const lastMessage = container.querySelector('.message:last-child .message-content');
        
        if (lastMessage && !lastMessage.querySelector('.tool-calls')) {
            const toolCallsDiv = this.createToolCallsElement(toolCalls);
            lastMessage.appendChild(toolCallsDiv);
        }
    }
    
    /**
     * Replace content of the last assistant message with rendered Markdown
     */
    updateLastAssistantMessageContent(content) {
        const container = document.getElementById('messages-container');
        const lastAssistant = container.querySelector('.message:last-child.assistant .message-text');
        if (lastAssistant) {
            lastAssistant.innerHTML = DOMPurify.sanitize(marked.parse(content));
            // Re-run code highlighting for any blocks
            lastAssistant.querySelectorAll('pre code').forEach(block => {
                try { hljs.highlightElement(block); } catch (_) { /* ignore */ }
            });
        }
    }
    
    /**
     * Create new conversation
     */
    async createNewConversation() {
        try {
            const response = await fetch(`${this.apiBase}/conversations.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    title: 'New Conversation',
                    metadata: {}
                })
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error?.message || 'Failed to create conversation');
            }
            
            this.currentConversation = result.conversation.conversation_id;
            
            // Enable message input
            document.getElementById('message-input').disabled = false;
            document.getElementById('send-btn').disabled = false;
            
            // Clear messages
            document.getElementById('messages-container').innerHTML = '';
            
            // Reload conversations list
            await this.loadConversations();
            
            // Focus message input
            document.getElementById('message-input').focus();
            
            this.showNotification('Success', 'New conversation created', 'success');
            
        } catch (error) {
            console.error('Failed to create conversation:', error);
            this.showNotification('Error', 'Failed to create conversation', 'error');
        }
    }
    
    /**
     * Load conversations list
     */
    async loadConversations() {
        try {
            const conversationsUrl = `${this.apiBase}/conversations.php`;
            console.log('Loading conversations from:', conversationsUrl);
            
            const response = await fetch(conversationsUrl);
            console.log('Conversations response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Expected JSON but got:', contentType, text.substring(0, 200));
                throw new Error('Server returned HTML instead of JSON - check API path');
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error?.message || 'Failed to load conversations');
            }
            
            this.conversations = result.conversations;
            this.renderConversationsList();
            
        } catch (error) {
            console.error('Failed to load conversations:', error);
            this.showNotification('Error', 'Failed to load conversations - check console for details', 'error');
        }
    }
    
    /**
     * Render conversations list in sidebar
     */
    renderConversationsList() {
        const container = document.getElementById('conversations-list');
        container.innerHTML = '';
        
        if (this.conversations.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'text-center text-muted py-3';
            emptyState.innerHTML = '<i class="fas fa-comments mb-2"></i><br>No conversations yet';
            container.appendChild(emptyState);
            return;
        }
        
        this.conversations.forEach(conversation => {
            const item = document.createElement('div');
            item.className = 'conversation-item';
            if (conversation.conversation_id === this.currentConversation) {
                item.classList.add('active');
            }
            
            item.innerHTML = `
                <div class="conversation-title">${this.escapeHtml(conversation.title)}</div>
                <div class="conversation-meta">
                    ${conversation.message_count} messages • 
                    ${this.formatDate(conversation.updated_at)}
                </div>
            `;
            
            item.addEventListener('click', () => {
                this.selectConversation(conversation.conversation_id);
            });
            
            container.appendChild(item);
        });
    }
    
    /**
     * Select and load conversation
     */
    async selectConversation(conversationId) {
        try {
            this.currentConversation = conversationId;
            
            // Update UI
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Load messages
            await this.loadMessages(conversationId);
            
            // Enable message input
            document.getElementById('message-input').disabled = false;
            document.getElementById('send-btn').disabled = false;
            document.getElementById('message-input').focus();
            
        } catch (error) {
            console.error('Failed to select conversation:', error);
            this.showNotification('Error', 'Failed to load conversation', 'error');
        }
    }
    
    /**
     * Load messages for conversation
     */
    async loadMessages(conversationId) {
        try {
            const response = await fetch(`${this.apiBase}/conversations.php?id=${conversationId}&include_messages=true`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error?.message || `Conversation not found: ${conversationId}`);
            }
            
            const messages = result.conversation.messages || [];
            
            // Clear current messages
            const container = document.getElementById('messages-container');
            container.innerHTML = '';
            
            // Add messages to UI
            messages.forEach(message => {
                this.addMessageToUI(message);
            });
            
        } catch (error) {
            console.error('Failed to load messages:', error);
            this.showNotification('Error', 'Failed to load messages', 'error');
        }
    }
    
    /**
     * Check system health
     */
    async checkHealth() {
        try {
            const healthUrl = `${this.apiBase}/health.php`;
            console.log('Checking health at:', healthUrl);
            
            const response = await fetch(healthUrl);
            console.log('Health response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Expected JSON but got:', contentType, text.substring(0, 200));
                throw new Error('Server returned HTML instead of JSON - check API path');
            }
            
            const result = await response.json();
            
            const statusBadge = document.getElementById('status-badge');
            const statusText = document.getElementById('status-text');
            
            if (result.status === 'healthy') {
                statusBadge.className = 'status-badge online';
                statusText.textContent = 'Online';
                this.isConnected = true;
            } else {
                statusBadge.className = 'status-badge offline';
                statusText.textContent = result.status || 'Offline';
                this.isConnected = false;
            }
            // Update last-checked tooltip
            try {
                const now = new Date();
                statusBadge.setAttribute('title', `Last checked: ${now.toLocaleString()}`);
            } catch (_) { /* no-op */ }
            
        } catch (error) {
            console.error('Health check failed:', error);
            const statusBadge = document.getElementById('status-badge');
            const statusText = document.getElementById('status-text');
            statusBadge.className = 'status-badge offline';
            statusText.textContent = 'Offline';
            this.isConnected = false;
            try {
                statusBadge.setAttribute('title', `Last checked: ${new Date().toLocaleString()}`);
            } catch (_) { /* ignore */ }
        }
    }
    
    /**
     * Initialize speech recognition
     */
    initSpeechRecognition() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.warn('Speech recognition not supported');
            document.getElementById('voice-btn').style.display = 'none';
            return;
        }
        
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.lang = 'en-US';
        
        this.recognition.onstart = () => {
            this.isRecording = true;
            document.getElementById('voice-btn').classList.add('recording');
            this.showNotification('Listening', 'Speak now...', 'info');
        };
        
        this.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            document.getElementById('message-input').value = transcript;
            this.showNotification('Voice Input', 'Message captured successfully', 'success');
        };
        
        this.recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            this.showNotification('Voice Error', 'Speech recognition failed', 'error');
        };
        
        this.recognition.onend = () => {
            this.isRecording = false;
            document.getElementById('voice-btn').classList.remove('recording');
        };
    }
    
    /**
     * Toggle voice recording
     */
    toggleVoiceRecording() {
        if (!this.recognition) {
            this.showNotification('Error', 'Speech recognition not supported', 'error');
            return;
        }
        
        if (this.isRecording) {
            this.recognition.stop();
        } else {
            this.recognition.start();
        }
    }
    
    /**
     * Toggle knowledge panel
     */
    toggleKnowledgePanel() {
        const panel = document.getElementById('knowledge-panel');
        const toggleBtn = document.getElementById('knowledge-toggle');
        panel.classList.toggle('show');
        const isOpen = panel.classList.contains('show');
        try {
            panel.setAttribute('aria-hidden', String(!isOpen));
            if (isOpen) panel.removeAttribute('inert'); else panel.setAttribute('inert', '');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(isOpen));
        } catch (_) { /* no-op */ }
        if (isOpen) {
            this.loadDocuments();
        }
    }
    
    /**
     * Handle file drag over
     */
    handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('dragover');
    }
    
    /**
     * Handle file drop
     */
    handleFileDrop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.uploadFile(files[0]);
        }
    }
    
    /**
     * Handle file selection
     */
    handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.uploadFile(file);
        }
    }
    
    /**
     * Upload file to knowledge base
     */
    async uploadFile(file) {
        try {
            this.showProgress(`Uploading ${file.name}...`);
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('title', file.name);
            
            const response = await fetch(`${this.apiBase}/upload.php`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error?.message || 'Upload failed');
            }
            
            this.showNotification('Success', `${file.name} uploaded successfully`, 'success');
            this.loadDocuments(); // Refresh documents list
            
        } catch (error) {
            console.error('File upload failed:', error);
            this.showNotification('Error', `Upload failed: ${error.message}`, 'error');
        } finally {
            this.hideProgress();
        }
    }
    
    /**
     * Load documents from knowledge base
     */
    async loadDocuments() {
        try {
            const response = await fetch(`${this.apiBase}/knowledge.php?action=documents`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error?.message || 'Failed to load documents');
            }
            
            this.renderDocumentsList(result.documents);
            
        } catch (error) {
            console.error('Failed to load documents:', error);
        }
    }
    
    /**
     * Render documents list
     */
    renderDocumentsList(documents) {
        const container = document.getElementById('documents-list');
        container.innerHTML = '';
        
        if (documents.length === 0) {
            container.innerHTML = '<div class="text-muted text-center py-3">No documents uploaded</div>';
            return;
        }
        
        documents.forEach(doc => {
            const item = document.createElement('div');
            item.className = 'card mb-2';
            item.innerHTML = `
                <div class="card-body p-2">
                    <h6 class="card-title mb-1">${this.escapeHtml(doc.title)}</h6>
                    <small class="text-muted">${this.formatDate(doc.created_at)}</small>
                </div>
            `;
            container.appendChild(item);
        });
    }
    
    /**
     * Search knowledge base
     */
    async searchKnowledge() {
        const query = document.getElementById('knowledge-search').value.trim();
        if (!query) return;
        
        try {
            this.showProgress('Searching knowledge base...');
            
            const response = await fetch(`${this.apiBase}/knowledge.php?action=search`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    query: query,
                    limit: 10
                })
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error?.message || 'Search failed');
            }
            
            this.showSearchResults(result.results);
            
        } catch (error) {
            console.error('Knowledge search failed:', error);
            this.showNotification('Error', 'Search failed', 'error');
        } finally {
            this.hideProgress();
        }
    }
    
    /**
     * Show search results
     */
    showSearchResults(results) {
        const container = document.getElementById('documents-list');
        container.innerHTML = '';
        
        if (results.length === 0) {
            container.innerHTML = '<div class="text-muted text-center py-3">No results found</div>';
            return;
        }
        
        results.forEach(result => {
            const item = document.createElement('div');
            item.className = 'card mb-2';
            item.innerHTML = `
                <div class="card-body p-2">
                    <h6 class="card-title mb-1">${this.escapeHtml(result.title)}</h6>
                    <p class="card-text small mb-1">${this.escapeHtml(result.content.substring(0, 100))}...</p>
                    <small class="text-muted">Score: ${result.score.toFixed(3)}</small>
                </div>
            `;
            container.appendChild(item);
        });
    }
    
    /**
     * Show progress indicator
     */
    showProgress(text) {
        const container = document.getElementById('progress-container');
        const textElement = document.getElementById('progress-text');
        textElement.textContent = text;
        container.classList.add('show');
    }
    
    /**
     * Hide progress indicator
     */
    hideProgress() {
        document.getElementById('progress-container').classList.remove('show');
    }
    
    /**
     * Show typing indicator
     */
    showTypingIndicator(show) {
        const indicator = document.getElementById('typing-indicator');
        if (show) {
            indicator.classList.add('show');
        } else {
            indicator.classList.remove('show');
        }
    }
    
    /**
     * Enable/disable form elements
     */
    setFormEnabled(enabled) {
        document.getElementById('message-input').disabled = !enabled;
        document.getElementById('send-btn').disabled = !enabled;
        document.getElementById('voice-btn').disabled = !enabled;
    }
    
    /**
     * Setup textarea auto-resize
     */
    setupTextareaResize() {
        const textarea = document.getElementById('message-input');
        
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }
    
    /**
     * Show notification
     */
    showNotification(title, message, type = 'info') {
        // Create notification element
        const notification = document.getElementById('notification-template').cloneNode(true);
        notification.id = 'notification-' + Date.now();
        notification.classList.add(type);
        
        notification.querySelector('#notification-title').textContent = title;
        notification.querySelector('#notification-message').textContent = message;
        
        // Add to document
        document.body.appendChild(notification);
        
        // Show with animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.hideNotification(notification.id);
        }, 5000);
    }
    
    /**
     * Hide notification
     */
    hideNotification(id = null) {
        const notification = id ? document.getElementById(id) : document.querySelector('.notification.show');
        if (notification) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }
    
    /**
     * Utility: Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Utility: Format date
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) {
            return 'Today';
        } else if (diffDays === 1) {
            return 'Yesterday';
        } else if (diffDays < 7) {
            return `${diffDays} days ago`;
        } else {
            return date.toLocaleDateString();
        }
    }
    
    /**
     * Cleanup resources
     */
    cleanup() {
        if (this.eventSource) {
            this.eventSource.close();
        }
        
        if (this.recognition && this.isRecording) {
            this.recognition.stop();
        }
    }
}

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.aiAgent = new AIAgentApp();
});

// Global function for notification close button
window.hideNotification = function() {
    const notification = document.querySelector('.notification.show');
    if (notification) {
        window.aiAgent.hideNotification(notification.id);
    }
};