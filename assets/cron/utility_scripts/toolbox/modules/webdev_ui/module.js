export default function init(ctx){
  const root=ctx?.root||document;
  // Example enhancement: live status ping
  const el=document.createElement('div');
  el.className='small';
  el.textContent='Status: checking…';
  root.appendChild(el);
  fetch('/assets/services/ai-agent/api/healthz.php',{method:'POST'}).then(r=>r.json()).then(j=>{
    el.textContent = j?.alive? 'Status: OK' : 'Status: degraded';
  }).catch(()=>{el.textContent='Status: offline';});
}
