function bubble(role, text){
  const div=document.createElement('div');
  div.className='msg '+(role==='user'?'me':'bot');
  // naive markdown render for code fences
  if(/```[\s\S]*?```/.test(text)){
    const pre=document.createElement('pre');
    pre.textContent=text.replace(/```/g,'');
    div.appendChild(pre);
  } else {
    div.textContent=text;
  }
  return div;
}

function personaToSystem(p){
  switch(p){
    case 'neuro':
      return 'You are a Neuro Tech Specialist focused on applied neuroscience, signal processing, and robust systems. Be precise, cite concrete steps, and avoid speculation.';
    case 'security':
      return 'You are a Security Analyst. Prioritize threat modeling, least privilege, secure defaults, and actionable mitigations.';
    case 'perf':
      return 'You are a Performance Tuner. Focus on profiling, budgets, and concrete tuning steps with measurable outcomes.';
    default:
      return undefined; // let server default or .env override apply
  }
}

export default function init(ctx){
  const root=ctx?.root||document;
  const log=root.querySelector('#chat-log');
  const input=root.querySelector('#chat-input');
  const send=root.querySelector('#chat-send');
  const clear=root.querySelector('#chat-clear');
  const provider=root.querySelector('#chat-provider');
  const unitPicker=root.querySelector('#picker-unit');
  const projectPicker=root.querySelector('#picker-project');
  const domainPicker=root.querySelector('#picker-domain');
  const attachmentsList=root.querySelector('#chat-attachments');
  const model=root.querySelector('#chat-model');
  const temp=root.querySelector('#chat-temp');
  const session=root.querySelector('#chat-session');
  const bot=root.querySelector('#chat-bot');
  const persona=root.querySelector('#chat-persona');
  const token=root.querySelector('#chat-token');
  const stream=root.querySelector('#chat-stream');
  const org=root.querySelector('#chat-org');
  const unit=root.querySelector('#chat-unit');
  const project=root.querySelector('#chat-project');
  const fileInput=root.querySelector('#chat-file');
  const uploadBtn=root.querySelector('#chat-upload');
  const uploadStatus=root.querySelector('#chat-upload-status');
  const drop=root.querySelector('#chat-drop');
  const modeSel=root.querySelector('#chat-mode');
  const loadConv=root.querySelector('#chat-load-conv');
  const loadHist=root.querySelector('#chat-load-history');
  const toolTicketBtn=root.querySelector('#chat-tool-ticket');
  const sseStatus=root.querySelector('#chat-sse-status');
  const mcpUrlSpan=root.querySelector('#chat-mcp-url');
  const mcpRouteAll=root.querySelector('#chat-mcp-route-all');

  function append(role,text){ log.appendChild(bubble(role,text)); log.scrollTop=log.scrollHeight; }

  clear.addEventListener('click',()=>{ log.innerHTML=''; });
  // Initialize MCP settings panel
  (async function initMcpPanel(){
    try{
      const r=await fetch('/assets/services/ai-agent/api/tools/mcp_config.php',{headers:{...(token.value?{'Authorization':'Bearer '+token.value}:{})}});
      const j=await r.json();
      if(j?.success){ if(mcpUrlSpan) mcpUrlSpan.textContent=j.data.server_url||'(not set)'; }
    }catch{}
    // Restore route-all preference
    const pref = localStorage.getItem('chat.mcp.routeAll') === '1';
    if(mcpRouteAll){ mcpRouteAll.checked = pref; mcpRouteAll.addEventListener('change',()=>{
      localStorage.setItem('chat.mcp.routeAll', mcpRouteAll.checked ? '1' : '0');
    }); }
  })();

  const attachments=[]; // accumulated uploaded attachments for next message

  async function uploadMany(files){
    if(!files || files.length===0){ uploadStatus.textContent='No files selected'; return; }
    uploadStatus.textContent='Uploading '+files.length+'…';
    for(const f of files){
      const fd=new FormData(); fd.append('file', f);
      try{
        const r=await fetch('/assets/services/ai-agent/api/upload.php',{method:'POST',headers:{...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:fd});
        const j=await r.json();
        if(j?.success){ attachments.push(j.data.attachment); }
      }catch(e){ /* continue */ }
    }
    uploadStatus.textContent='Uploaded: '+attachments.length+' file(s)';
    attachmentsList.textContent='Attachments: '+attachments.map(a=>a.name).join(', ');
  }
  uploadBtn.addEventListener('click', async ()=>{ await uploadMany(fileInput.files); });
  if(drop){
    ['dragover','dragenter'].forEach(ev=>drop.addEventListener(ev,(e)=>{ e.preventDefault(); drop.style.background='#f6f6f6'; }));
    ['dragleave','drop'].forEach(ev=>drop.addEventListener(ev,(e)=>{ e.preventDefault(); drop.style.background=''; }));
    drop.addEventListener('drop', async (e)=>{ const files=e.dataTransfer?.files; await uploadMany(files); });
  }

  send.addEventListener('click', async ()=>{
    const content=input.value.trim(); if(!content) return;
    append('user',content); input.value='';

    // Slash commands -> MCP proxy (non-LLM fast path)
    if (content.startsWith('/')) {
      const [cmd, ...rest] = content.split(' ');
      const arg = rest.join(' ').trim();
      const call = async (name, argumentsObj)=>{
        try{
          const r=await fetch('/assets/services/ai-agent/api/tools/mcp_proxy.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify({name,arguments:argumentsObj})});
          const j=await r.json();
          if(!j?.success){ append('assistant','MCP error: '+(j?.error?.message||'failed')); return; }
          append('assistant', JSON.stringify(j.data.result).slice(0,2000));
        }catch(e){ append('assistant','MCP network error: '+e.message); }
      };
      if (cmd === '/help') {
        append('assistant','MCP commands:\n/kb <query>\n/code <pattern>\n/category <name> <query>\n/health\n/analytics\n/stats\n/top <limit>\n/list-categories\n/invoke <tool> <jsonArgs>');
        return;
      }
      if (cmd === '/kb')      { await call('semantic_search',{query: arg||'dashboard', limit: 10}); return; }
      if (cmd === '/code')    { await call('find_code',{pattern: arg||'function', search_in:'all', limit: 20}); return; }
      if (cmd === '/category'){
        const parts = arg.split(' ');
        const cat = parts.shift()||''; const q = parts.join(' ').trim()||'*';
        await call('search_by_category',{category_name: cat, query: q, limit: 20}); return;
      }
      if (cmd === '/health')  { await call('health_check',{}); return; }
      if (cmd === '/analytics'){ await call('get_analytics',{action:'overview', timeframe:'24h'}); return; }
      if (cmd === '/stats')   { await call('get_stats',{breakdown_by:'unit'}); return; }
      if (cmd === '/top')     { await call('top_keywords',{limit: Math.max(1, parseInt(arg||'20',10)||20)}); return; }
      if (cmd === '/list-categories'){ await call('list_categories',{min_priority:1.0, order_by:'priority'}); return; }
      if (cmd === '/invoke'){
        const space = arg.indexOf(' ');
        const tool = space === -1 ? arg : arg.slice(0,space);
        const json = space === -1 ? '{}' : arg.slice(space+1);
        try{
          const argsObj = json ? JSON.parse(json) : {};
          const r=await fetch('/assets/services/ai-agent/api/tools/invoke.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify({tool, args: argsObj, session_key: session.value||undefined})});
          const j=await r.json();
          if(!j?.success){ append('assistant','Invoke error: '+(j?.error?.message||'failed')); return; }
          append('assistant', JSON.stringify(j.data, null, 2).slice(0,2000));
        }catch(e){ append('assistant','Invoke parse/network error: '+e.message); }
        return;
      }
      // Unknown slash falls through to LLM
    }
  const sys=personaToSystem(persona.value);
    // Mode hinting (can be used server-side later via system prompt)
    const mode = modeSel?.value || 'chat';
    const payload={
      provider: provider.value,
      model: model.value,
      temperature: parseFloat(temp.value)||0.2,
      message: content,
      session_key: session.value||undefined,
      bot: bot.value||undefined,
      system: sys,
      org_id: org.value?parseInt(org.value,10):undefined,
      unit_id: unit.value?parseInt(unit.value,10):undefined,
      project_id: project.value?parseInt(project.value,10):undefined,
      attachments: attachments.length?attachments:undefined,
      mode
    };
    // Route-all-to-MCP: if enabled and not a slash-command, send semantic_search to MCP
    if (mcpRouteAll && mcpRouteAll.checked && !content.startsWith('/')) {
      try{
        const r=await fetch('/assets/services/ai-agent/api/tools/mcp_proxy.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify({name:'semantic_search',arguments:{query: content, limit: 10}})});
        const j=await r.json();
        if(!j?.success){ append('assistant','MCP error: '+(j?.error?.message||'failed')); return; }
        append('assistant', JSON.stringify(j.data.result).slice(0,2000));
        return;
      }catch(e){ append('assistant','MCP network error: '+e.message); return; }
    }

    if(stream.value==='on'){
      // SSE mode
      try{
        const r=await fetch('/assets/services/ai-agent/api/chat_stream.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify(payload)});
        if(!r.ok){ append('assistant','Error: HTTP '+r.status); return; }
        const reader=r.body.getReader();
        const decoder=new TextDecoder('utf-8');
        sseStatus.textContent='Streaming…';
        let buffer='';
        let acc='';
        while(true){
          const {value,done}=await reader.read();
          if(done) break;
          buffer+=decoder.decode(value,{stream:true});
          const parts=buffer.split('\n\n');
          buffer=parts.pop()||'';
          for(const chunk of parts){
            const lines=chunk.split('\n');
            let event=null, data='';
            for(const line of lines){
              if(line.startsWith('event: ')) event=line.slice(7).trim();
              else if(line.startsWith('data: ')) data += line.slice(6);
            }
            if(event==='delta'){
              try{ const d=JSON.parse(data); acc += (d.content||''); }catch{}
            } else if(event==='done'){
              append('assistant', acc||'(no response)');
              sseStatus.textContent='';
            } else if(event==='error'){
              try{ const d=JSON.parse(data); append('assistant','Error: '+(d.message||'stream error')); }catch{ append('assistant','Stream error'); }
              sseStatus.textContent='';
            }
          }
        }
      }catch(e){ append('assistant','Stream failed: '+e.message); sseStatus.textContent=''; }
    } else {
      // No-stream
      try{
        const r=await fetch('/assets/services/ai-agent/api/chat.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify(payload)});
        const j=await r.json();
        if(!j?.success){ append('assistant', 'Error: '+(j?.error?.message||r.status)); return; }
        append('assistant', j.data?.content||j.data?.assistant||'(no response)');
      }catch(e){ append('assistant','Network error: '+e.message); }
    }
  });

  loadConv.addEventListener('click', async ()=>{
    try{
      const r=await fetch('/assets/services/ai-agent/api/conversations.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify({platform:'github_copilot',session_key:session.value||undefined,limit:20})});
      const j=await r.json();
      append('assistant', j?.success? ('Conversations: '+JSON.stringify(j.data.conversations).slice(0,800)+'…') : 'Error loading conversations');
    }catch(e){ append('assistant','Conversations error: '+e.message); }
  });

  loadHist.addEventListener('click', async ()=>{
    try{
      const r=await fetch('/assets/services/ai-agent/api/history.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify({session_key:session.value||undefined})});
      const j=await r.json();
      if(!j?.success){ append('assistant','History error'); return; }
      // Render last 10 messages
      const items=(j.data.history||[]).slice(-10);
      for(const m of items){ append(m.role==='user'?'user':'assistant', m.content); }
    }catch(e){ append('assistant','History error: '+e.message); }
  });

  toolTicketBtn.addEventListener('click', async ()=>{
    try{
      const r=await fetch('/assets/services/ai-agent/api/tools/ticket.php',{method:'POST',headers:{'Content-Type':'application/json',...(token.value?{'Authorization':'Bearer '+token.value}:{})},body:JSON.stringify({tool:'sample.tool',args:{q:'ping'}})});
      const j=await r.json();
      if(!j?.success){ append('assistant','Ticket error'); return; }
      append('assistant','Ticket created: '+j.data.ticket);
      // connect SSE events (tool notifications)
      const es=new EventSource(j.data.sse);
      es.addEventListener('start',()=>{ sseStatus.textContent='Tool started…'; });
      es.addEventListener('phase',(e)=>{ try{const d=JSON.parse(e.data); sseStatus.textContent='Tool: '+(d.phase||'');}catch{}});
      es.addEventListener('result',(e)=>{ try{const d=JSON.parse(e.data); append('assistant','Tool result: '+JSON.stringify(d).slice(0,800)+'…'); }catch{} });
      es.addEventListener('error',(e)=>{ sseStatus.textContent='Tool error'; });
      es.addEventListener('done',()=>{ sseStatus.textContent=''; es.close(); });
    }catch(e){ append('assistant','Ticket create failed: '+e.message); }
  });

  // Load pickers
  (async function initPickers(){
    try{
      const [u,p,d]=await Promise.all([
        fetch('/assets/services/ai-agent/api/lookup/units.php').then(r=>r.json()).catch(()=>null),
        fetch('/assets/services/ai-agent/api/lookup/projects.php').then(r=>r.json()).catch(()=>null),
        fetch('/assets/services/ai-agent/api/lookup/domains.php').then(r=>r.json()).catch(()=>null)
      ]);
      if(u?.success){ for(const x of u.data.units){ const o=document.createElement('option'); o.value=x.id; o.textContent=x.name; unitPicker.appendChild(o);} }
      if(p?.success){ for(const x of p.data.projects){ const o=document.createElement('option'); o.value=x.id; o.textContent=x.name; projectPicker.appendChild(o);} }
      if(d?.success){ for(const x of d.data.domains){ const o=document.createElement('option'); o.value=x.project_id||''; o.textContent=x.domain; domainPicker.appendChild(o);} }
    }catch{}
  })();
  unitPicker.addEventListener('change',()=>{ unit.value=unitPicker.value; });
  projectPicker.addEventListener('change',()=>{ project.value=projectPicker.value; });
  domainPicker.addEventListener('change',()=>{ if(domainPicker.value) project.value=domainPicker.value; });

  // Bridge: prompts module can insert into chat input
  window.addEventListener('toolbox:insertToChat',(e)=>{
    if(!e?.detail?.text) return; input.value=e.detail.text; input.focus();
  });
}
