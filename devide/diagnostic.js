// DevIDE Diagnostic Script
// Paste this in browser console (F12) to debug

console.log('=== DevIDE Diagnostic ===');

// Check if elements exist
const checks = {
    'Editor Container': document.getElementById('editor'),
    'Welcome Screen': document.getElementById('welcomeScreen'),
    'File Tree': document.getElementById('fileTree'),
    'Chat Messages': document.getElementById('chatMessages'),
    'Chat Input': document.getElementById('chatInput'),
    'Output Panel': document.getElementById('outputPanel'),
    'Terminal Panel': document.getElementById('terminalPanel'),
    'GPT Panel': document.getElementById('gptPanel'),
    'Tabs Container': document.getElementById('tabs'),
    'Status Line': document.getElementById('statusLine')
};

console.log('\n📋 Element Checks:');
Object.entries(checks).forEach(([name, element]) => {
    if (element) {
        const display = window.getComputedStyle(element).display;
        const visibility = window.getComputedStyle(element).visibility;
        console.log(`✅ ${name}: Found (display: ${display}, visibility: ${visibility})`);
    } else {
        console.log(`❌ ${name}: Missing`);
    }
});

// Check if Monaco is loaded
console.log('\n🎨 Monaco Editor:');
if (typeof monaco !== 'undefined') {
    console.log('✅ Monaco loaded');
    if (typeof editor !== 'undefined') {
        console.log('✅ Editor instance exists');
    } else {
        console.log('❌ Editor instance not created');
    }
} else {
    console.log('❌ Monaco not loaded');
}

// Check if variables are defined
console.log('\n📦 Global Variables:');
const vars = ['editor', 'currentFile', 'openTabs', 'conversationHistory', 'currentConversationId'];
vars.forEach(v => {
    console.log(`${typeof window[v] !== 'undefined' ? '✅' : '❌'} ${v}: ${typeof window[v]}`);
});

// Check for errors
console.log('\n🐛 Recent Errors:');
console.log('Check Console tab for any red error messages');

// Test API
console.log('\n🔌 Testing API:');
fetch('/devide/api.php?action=list&path=.')
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log(`✅ API working: ${data.files.length} files found`);
        } else {
            console.log(`❌ API error: ${data.error}`);
        }
    })
    .catch(err => console.log(`❌ API failed: ${err.message}`));

console.log('\n=== End Diagnostic ===');
console.log('Copy this output and share with support if needed');
