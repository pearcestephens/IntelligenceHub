const fs = require('fs');
const path = require('path');

module.exports = function loadApp() {
  // Avoid re-injecting the app script multiple times across tests
  const FLAG = '__AI_AGENT_APP_SCRIPT_LOADED__';
  if (!globalThis[FLAG]) {
    const appPath = path.resolve(__dirname, '../../../public/agent/js/app.js');
    const code = fs.readFileSync(appPath, 'utf8');
    const script = document.createElement('script');
    script.textContent = code;
    document.head.appendChild(script);
    // Trigger the app startup once so window.aiAgent is created
    document.dispatchEvent(new Event('DOMContentLoaded'));
    globalThis[FLAG] = true;
  }
};
