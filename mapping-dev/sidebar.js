(function(){
 'use strict';
 const main=document.querySelector('main'),aside=document.querySelector('main > aside');
 const minimum=360,storageKey='multiplottr.sidebar.width.v1';
 const mobile=matchMedia('(max-width:750px)');
 let preferred=minimum,full=false,frame;
 try{const saved=JSON.parse(localStorage.getItem(storageKey));if(saved&&Number.isFinite(saved.width))preferred=Math.max(minimum,saved.width);full=saved?.full===true;}catch(_){}
 aside.id='mappingSidebar';
 const splitter=document.createElement('div');splitter.id='sidebarResize';splitter.tabIndex=0;splitter.setAttribute('role','separator');splitter.setAttribute('aria-label','Resize sidebar');splitter.setAttribute('aria-orientation','vertical');splitter.setAttribute('aria-controls',aside.id);splitter.title='Drag to resize. Left/Right arrows adjust width; Home minimizes; End expands.';main.insertBefore(splitter,aside);
 const expand=document.createElement('button'),minimize=document.createElement('button');
 expand.id='sidebarExpand';expand.type='button';expand.textContent='Expand';expand.setAttribute('aria-label','Expand sidebar to full width');
 minimize.id='sidebarMinimize';minimize.type='button';minimize.textContent='Minimize';minimize.setAttribute('aria-label','Minimize sidebar to 360 pixels');
 const controls=document.createElement('div');controls.id='sidebarSizeControls';controls.append(expand,minimize);document.getElementById('sidebarActions').append(controls);
 function maximum(){return Math.max(minimum,main.getBoundingClientRect().width-8);}
 function persist(){try{localStorage.setItem(storageKey,JSON.stringify({width:preferred,full}));}catch(_){} }
 function apply(){
  const max=maximum(),width=full?max:Math.min(max,Math.max(minimum,preferred));
  const map=window.MappingWorkspace?.state.map,center=map?.getCenter();
  main.style.setProperty('--sidebar-width',width+'px');main.classList.toggle('sidebar-full',full&&!mobile.matches);
  splitter.setAttribute('aria-valuemin',String(minimum));splitter.setAttribute('aria-valuemax',String(Math.round(max)));splitter.setAttribute('aria-valuenow',String(Math.round(width)));splitter.setAttribute('aria-valuetext',full?'Full width':Math.round(width)+' pixels');
  expand.disabled=full;minimize.disabled=!full&&width===minimum;
  cancelAnimationFrame(frame);frame=requestAnimationFrame(()=>{if(map&&map===window.MappingWorkspace?.state.map&&window.google?.maps){google.maps.event.trigger(map,'resize');if(center)map.setCenter(center);}});
 }
 function setWidth(width){const max=maximum();preferred=Math.max(minimum,Math.min(max,width));full=preferred>=max-2;apply();}
 expand.onclick=()=>{full=true;apply();persist();};minimize.onclick=()=>{full=false;preferred=minimum;apply();persist();};
 splitter.addEventListener('pointerdown',event=>{if(event.button!==0||mobile.matches)return;event.preventDefault();splitter.setPointerCapture(event.pointerId);document.body.classList.add('sidebar-resizing');});
 splitter.addEventListener('pointermove',event=>{if(splitter.hasPointerCapture(event.pointerId))setWidth(main.getBoundingClientRect().right-event.clientX-4);});
 function finish(event){if(splitter.hasPointerCapture(event.pointerId))splitter.releasePointerCapture(event.pointerId);document.body.classList.remove('sidebar-resizing');persist();}
 splitter.addEventListener('pointerup',finish);splitter.addEventListener('pointercancel',finish);splitter.addEventListener('lostpointercapture',()=>document.body.classList.remove('sidebar-resizing'));
 splitter.addEventListener('keydown',event=>{const width=Number(splitter.getAttribute('aria-valuenow')),step=event.shiftKey?80:24;if(event.key==='ArrowLeft')setWidth(width+step);else if(event.key==='ArrowRight')setWidth(width-step);else if(event.key==='Home'){preferred=minimum;full=false;apply();}else if(event.key==='End'){full=true;apply();}else return;event.preventDefault();persist();});
 splitter.addEventListener('dblclick',()=>{full=!full;if(!full)preferred=minimum;apply();persist();});
 window.addEventListener('resize',apply);mobile.addEventListener('change',apply);apply();
})();
