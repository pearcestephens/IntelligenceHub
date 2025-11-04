/*
  Lightweight chat client for on-server app
  - Keeps messages in memory
  - Calls /assets/services/ai-agent/api/chat.php
*/
(function(){
  const cfg = window.CHAT_CONFIG || {};
  const apiUrl = cfg.apiUrl || '/assets/services/ai-agent/api/chat.php';

  const el = (id) => document.getElementById(id);
  const messages = el('messages');
  const input = el('message');
  const btn = el('send');

  const state = {
    history: [],
    session_key: '',
  };

  function addBubble(role, text) {
    const wrap = document.createElement('div');
    wrap.className = 'bubble ' + (role === 'user' ? 'bubble-user' : 'bubble-assistant');
    wrap.textContent = text;
    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
  }

  async function sendMessage() {
    const message = input.value.trim();
    if (!message) return;

    addBubble('user', message);
    input.value = '';

    const provider = el('provider').value || 'openai';
    const model = el('model').value || (provider === 'openai' ? 'gpt-4o-mini' : 'claude-3-5-sonnet-latest');
    const temperature = parseFloat(el('temperature').value || '0.2');
    const session_key = el('session_key').value || state.session_key || crypto.randomUUID().replace(/-/g,'');
    state.session_key = session_key;
    el('session_key').value = session_key;

    const bot = el('bot').value || null;
    const org_id = parseInt(el('org_id').value || '1', 10);
    const unit_id = el('unit_id').value ? parseInt(el('unit_id').value, 10) : null;
    const project_id = el('project_id').value ? parseInt(el('project_id').value, 10) : null;

    const body = {
      message,
      history: state.history,
      session_key,
      provider,
      model,
      temperature,
      org_id,
      unit_id,
      project_id,
      bot
    };

    const headers = { 'Content-Type': 'application/json' };
    const apiKey = el('api_key').value && !cfg.hasEnvKey ? el('api_key').value.trim() : '';
    if (apiKey) headers['Authorization'] = 'Bearer ' + apiKey;

    try {
      const res = await fetch(apiUrl, { method: 'POST', headers, body: JSON.stringify(body) });
      const json = await res.json().catch(()=>({success:false,error:{message:'Invalid JSON'}}));
      if (!json.success) {
        addBubble('assistant', 'Error: ' + (json.error?.message || 'Unknown error'));
        return;
      }
      const text = json.data?.content || '';
      addBubble('assistant', text || '[empty reply]');
      // Update history
      state.history.push({ role: 'user', content: message });
      state.history.push({ role: 'assistant', content: text });
    } catch (e) {
      addBubble('assistant', 'Request failed: ' + (e && e.message ? e.message : e));
    }
  }

  btn.addEventListener('click', sendMessage);
  input.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter') sendMessage();
  });

})();
