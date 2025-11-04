export default function init(ctx){
  const root=ctx?.root||document;
  const list=root.querySelector('#prompts-list');
  const refreshBtn=root.querySelector('#prompts-refresh');
  const title=root.querySelector('#prompt-title');
  const tags=root.querySelector('#prompt-tags');
  const content=root.querySelector('#prompt-content');
  const saveBtn=root.querySelector('#prompt-save');
  const clearBtn=root.querySelector('#prompt-clear');
  const status=root.querySelector('#prompt-status');

  async function load(){
    status.textContent='Loading…';
    try{
      const r=await fetch('/assets/services/ai-agent/api/prompts/list.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({limit:200})});
      const j=await r.json();
      list.innerHTML='';
      if(!j?.success){ status.textContent='Load error'; return; }
      for(const p of j.data.prompts){
        const div=document.createElement('div');
        div.className='card';
        div.innerHTML=`<div><strong>${p.title}</strong> <span class="small">${p.tags||''}</span></div>`;
        div.style.cursor='pointer';
        div.addEventListener('click',()=>{
          // Emit event so Chat module can capture and insert to input
          const ev = new CustomEvent('toolbox:insertToChat',{detail:{text: p.title}});
          window.dispatchEvent(ev);
        });
        list.appendChild(div);
      }
      status.textContent='';
    }catch(e){ status.textContent='Network error: '+e.message; }
  }

  refreshBtn.addEventListener('click',load);
  clearBtn.addEventListener('click',()=>{ title.value=''; tags.value=''; content.value=''; status.textContent=''; });
  saveBtn.addEventListener('click', async ()=>{
    const payload={title:title.value.trim(), tags:tags.value.trim(), content:content.value};
    if(!payload.title){ status.textContent='Title required'; return; }
    try{
      const r=await fetch('/assets/services/ai-agent/api/prompts/save.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
      const j=await r.json();
      status.textContent=j?.success? ('Saved id '+j.data.id) : 'Save error';
      if(j?.success){ await load(); }
    }catch(e){ status.textContent='Save failed: '+e.message; }
  });

  load();
}
