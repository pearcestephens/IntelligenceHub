// JSDOM setup and global shims used by app.js

// Minimal window APIs used in app.js
global.DOMPurify = { sanitize: (html) => html };

// Use marked from a lightweight mock; app.js expects marked.parse
global.marked = { parse: (txt) => txt };

// hljs mock
global.hljs = { highlightElement: () => {} };

// SpeechRecognition mocks
global.window = global.window || {};
window.SpeechRecognition = function(){};
window.webkitSpeechRecognition = function(){};

// Notification elements helpers for tests
document.body.innerHTML = `
  <form id="message-form"></form>
  <textarea id="message-input"></textarea>
  <button id="send-btn"></button>
  <button id="voice-btn"></button>
  <button id="new-conversation"></button>
  <button id="knowledge-toggle"></button>
  <button id="knowledge-close"></button>
  <button id="health-check"></button>
  <div id="file-drop-zone"></div>
  <input id="file-input" type="file" />
  <input id="knowledge-search" />
  <button id="search-btn"></button>
  <div id="messages-container"></div>
  <div id="conversations-list"></div>
  <div id="progress-container"><span id="progress-text"></span></div>
  <div id="typing-indicator"></div>
  <span id="status-badge"></span>
  <span id="status-text"></span>
  <div id="documents-list"></div>
  <div id="notification-template"><div id="notification-title"></div><div id="notification-message"></div></div>
`;

// fetch mock per-test will override, but provide sensible defaults per endpoint
global.fetch = async (url, opts = {}) => {
  const u = String(url);
  const ok = true;
  let body = { success: true };
  if (u.includes('/api/health.php')) {
    body = { status: 'healthy', success: true };
  } else if (u.includes('/api/conversations.php') && (!opts.method || opts.method === 'GET')) {
    body = { success: true, conversations: [] };
  } else if (u.includes('/api/conversations.php') && opts.method === 'POST') {
    body = { success: true, conversation: { conversation_id: 'test-1', title: 'New Conversation' } };
  } else if (u.includes('/api/knowledge.php/documents')) {
    body = { success: true, documents: [] };
  } else if (u.includes('/api/knowledge.php/search')) {
    body = { success: true, results: [] };
  } else if (u.includes('/api/chat.php')) {
    // Non-stream default response
    body = { success: true, response: { content: 'Hello from test', tool_calls: [] } };
  }
  return { ok, json: async () => body };
};
