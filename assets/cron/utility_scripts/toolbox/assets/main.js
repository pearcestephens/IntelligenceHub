/* Toolbox Loader (no frameworks, <10KB) */
(function(){
  'use strict';
  const state={modules:[],active:null,base:document.currentScript?.dataset?.base||'/assets/cron/utility_scripts/toolbox'};
  const el={tabs:null,panel:null,toast:null};

  function qs(s,sc=document){return sc.querySelector(s)}
  function qsa(s,sc=document){return Array.from(sc.querySelectorAll(s))}
  function toast(msg,timeout=2200){
    if(!el.toast){el.toast=document.createElement('div');el.toast.className='toast';document.body.appendChild(el.toast)}
    el.toast.textContent=msg;el.toast.classList.add('show');
    setTimeout(()=>el.toast.classList.remove('show'),timeout);
  }

  async function fetchJSON(url){
    const r=await fetch(url,{cache:'no-store'}); if(!r.ok) throw new Error('HTTP '+r.status); return r.json();
  }
  async function fetchText(url){
    const r=await fetch(url,{cache:'no-store'}); if(!r.ok) throw new Error('HTTP '+r.status); return r.text();
  }

  function renderTabs(){
    el.tabs.innerHTML='';
    state.modules.sort((a,b)=> (a.order||999)-(b.order||999));
    state.modules.forEach(m=>{
      const t=document.createElement('button');
      t.className='tab'+(state.active===m.id?' active':'');
      t.textContent=m.title||m.id; t.dataset.id=m.id;
      t.addEventListener('click',()=>activate(m.id));
      el.tabs.appendChild(t);
    });
  }

  async function activate(id){
    const mod=state.modules.find(x=>x.id===id); if(!mod) return;
    state.active=id; renderTabs();
    // Load HTML
    const html=await fetchText(mod.paths.html);
    el.panel.innerHTML=html;
    // Load CSS once per module
    if(mod.paths.css && !document.querySelector(`link[data-mod="${id}"]`)){
      const l=document.createElement('link'); l.rel='stylesheet'; l.href=mod.paths.css; l.dataset.mod=id; document.head.appendChild(l);
    }
    // Load and init JS
    if(mod.paths.js){
      const s=document.createElement('script'); s.type='module'; s.textContent=`import init from '${mod.paths.js}'; init?.({root:document.querySelector('[data-module-root]')||document,currentModule:${JSON.stringify(mod)}});`;
      el.panel.appendChild(s);
    }
    toast(`${mod.title||mod.id} loaded`);
  }

  function resolvePaths(m){
    const b=state.base+`/modules/${m.id}`;
    m.paths={
      html: m.html || `${b}/module.html`,
      css: m.css ? (m.css.startsWith('http')?m.css:`${b}/${m.css}`) : `${b}/module.css`,
      js: m.js ? (m.js.startsWith('http')?m.js:`${b}/${m.js}`) : `${b}/module.js`
    };
    return m;
  }

  async function discover(){
    try{
      const list=await fetchJSON(state.base+'/modules/index.json');
      state.modules=list.map(resolvePaths);
      renderTabs();
      if(state.modules[0]) activate(state.modules[0].id);
    }catch(e){
      el.panel.innerHTML=`<div class="small">Failed to load modules. ${e.message}</div>`;
    }
  }

  function boot(){
    el.tabs=qs('[data-tabs]');
    el.panel=qs('[data-panel]');
    discover();
  }

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',boot); else boot();
})();
