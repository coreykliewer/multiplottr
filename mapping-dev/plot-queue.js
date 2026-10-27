// Queue controller; Google objects and pending UI state never enter the saved document.
(function(){
 'use strict';
 window.createPlotQueue=function(api){
  const $=id=>document.getElementById(id);let active=null;
  const normalize=s=>String(s||'').normalize('NFKC').trim().replace(/\s+/g,' ').toLowerCase();
  function matches(address,point){return api.state.document.locations.filter(m=>normalize(m.originalAddress)&&normalize(m.originalAddress)===normalize(address)||point&&Math.abs(m.coordinates.lat-point.lat)<=0.000001&&Math.abs(m.coordinates.lng-point.lng)<=0.000001);}
  function controls(){api.state.plotting=!!active;$('plotSubmit').disabled=api.readonly()||!!active;$('addresses').readOnly=!!active;$('plotCancel').hidden=!active;$('plot').setAttribute('aria-busy',String(!!active));}
  function current(job){return active===job&&job.generation===api.state.generation;}
  function update(job){if(!current(job))return;$('addresses').value=job.entries.filter(e=>!['plotted','skipped'].includes(e.result)).map(e=>e.address).join('\n');const count=result=>job.entries.filter(e=>e.result===result).length;const attention=job.entries.filter(e=>['failed','ambiguous'].includes(e.result)).length;const pending=count('pending');const summary=count('plotted')+' plotted · '+attention+' need attention · '+count('skipped')+' skipped'+(pending?' · '+pending+' waiting':'');$('plotResults').textContent=summary;$('plotResultList').textContent='';job.entries.filter(e=>e.result!=='pending').forEach(e=>{const li=document.createElement('li');li.textContent=e.address+': '+(e.detail||e.result);$('plotResultList').append(li);});}
  function duplicate(job,address,found){return new Promise(resolve=>{const dialog=$('plotDuplicate');let settled=false;function finish(choice){if(settled)return;settled=true;job.cancelChoice=null;dialog.oncancel=null;dialog.onclose=null;if(dialog.open)dialog.close();resolve(choice);}job.cancelChoice=()=>finish('cancel');$('duplicateMessage').textContent='Possible duplicate: '+address+' matches '+found.length+' location'+(found.length===1?'':'s')+' already on this map. Skip it or plot another marker?';$('duplicateSkip').onclick=()=>finish('skip');$('duplicateAdd').onclick=()=>finish('add');$('duplicateCancel').onclick=()=>finish('cancel');dialog.oncancel=e=>{e.preventDefault();finish('cancel');};dialog.showModal();$('duplicateSkip').focus();});}
  function geocode(job,address){return new Promise(resolve=>{let settled=false;const timer=setTimeout(()=>finish({items:[],status:'TIMEOUT'}),20000);function finish(result){if(settled)return;settled=true;clearTimeout(timer);job.cancelRequest=null;resolve(result);}job.cancelRequest=()=>finish({items:[],status:'CANCELLED'});try{api.state.geocoder.geocode({address},(items,status)=>finish({items:items||[],status}));}catch(_){finish({items:[],status:'ERROR'});}});}
  function cancel(clear=false){const job=active;if(job){if(!clear)update(job);active=null;job.cancelRequest?.();job.cancelChoice?.();}if(clear){$('addresses').value='';$('plotResults').textContent='';$('plotResultList').textContent='';}controls();if(job&&!clear)api.notice('Plot stopped. Completed markers are kept; remaining addresses are still in the box.');}
  async function submit(event){event.preventDefault();if(active||api.readonly())return;if(!api.state.geocoder){api.notice('Maps is unavailable; address input retained.');return;}const entries=$('addresses').value.split(/\r?\n/).map(s=>s.trim()).filter(Boolean).map(address=>({address,result:'pending'}));if(!entries.length){api.notice('Enter at least one address to plot.');return;}
   const job={entries,generation:api.state.generation,defaults:api.defaults()};active=job;controls();update(job);
   try{for(const entry of entries){if(!current(job))break;let approved=false;const existing=matches(entry.address);
     if(existing.length){const choice=await duplicate(job,entry.address,existing);if(!current(job))break;if(choice==='cancel'){cancel();return;}if(choice==='skip'){entry.result='skipped';update(job);continue;}approved=true;}
     const r=await geocode(job,entry.address);if(!current(job))break;
     if(r.status==='OK'&&r.items.length===1&&!r.items[0].partial_match){
      const p=r.items[0].geometry?.location;let point;try{point={lat:p.lat(),lng:p.lng()};}catch(_){entry.result='failed';entry.detail='Invalid geocoder result';update(job);continue;}
      const samePlace=approved?[]:matches(entry.address,point);if(samePlace.length){const choice=await duplicate(job,entry.address,samePlace);if(!current(job))break;if(choice==='cancel'){cancel();return;}if(choice==='skip'){entry.result='skipped';update(job);continue;}}
      if(api.add(point,entry.address,entry.address,job.defaults,false))entry.result='plotted';else{entry.result='failed';entry.detail='Could not add marker; address retained';}
     }else{entry.result=r.items.length>1||r.items[0]?.partial_match?'ambiguous':'failed';entry.detail=entry.result==='ambiguous'?'Ambiguous result; refine this address':r.status;}
     update(job);
    }
    if(current(job)){update(job);if(entries.some(e=>e.result==='plotted'))api.fit(job.generation);api.notice($('plotResults').textContent+'. Add more addresses, or correct and retry those still in the box.');}
   }catch(_){if(current(job))api.notice('Plot interrupted. Completed markers are kept; unprocessed addresses remain in the box.');}
   finally{if(active===job){active=null;controls();}}
  }
  $('plot').onsubmit=submit;$('plotCancel').onclick=()=>cancel();return {cancel,refresh:controls};
 };
})();
