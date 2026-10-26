const fs=require('fs'),crypto=require('crypto');const {chromium}=require('./browser-tools/node_modules/playwright-core');
const origin='http://localhost:8080',allowed=new Set(['maps.googleapis.com','mapsresources-pa.googleapis.com','maps.gstatic.com','fonts.googleapis.com','fonts.gstatic.com']);
const key=JSON.parse(fs.readFileSync('local-test/config/maps.local.json','utf8').replace(/^\uFEFF/,'')).apiKey;
const fixture=JSON.parse(fs.readFileSync('local-test/fixture.json','utf8').replace(/^\uFEFF/,''));
const safe=s=>String(s).split(key).join('[REDACTED]').replace(/AIza[\w-]+/g,'[REDACTED]').replace(/https?:\/\/[^\s"'<>]+/g,t=>{try{const u=new URL(t);return u.origin+u.pathname}catch{return '[URL]'}});
(async()=>{
 const b=await chromium.launch({executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',headless:false,args:['--no-first-run','--no-default-browser-check']});
 const c=await b.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});c.setDefaultTimeout(10000);
 const unexpected=[],errors=[],csp=[];
 await c.route('**/*',r=>{const u=new URL(r.request().url());if(u.origin===origin||(u.protocol==='https:'&&allowed.has(u.hostname)))return r.continue();unexpected.push(u.origin+u.pathname);return r.abort('blockedbyclient');});
 const authSession=await (await c.request.get(origin+'/__local_membership/session')).json();
 const authenticated=await c.request.post(origin+'/__local_membership/login',{headers:{'X-Local-CSRF':authSession.csrf},data:{username:fixture.username,password:fixture.password}});
 if(authenticated.status()!==200)throw Error('Synthetic local membership sign-in failed');
 await c.addInitScript(()=>{sessionStorage.loggedIn='yes';sessionStorage.hideIntro='yes';window.__printCount=0;window.__csp=[];window.addEventListener('beforeprint',()=>window.__printCount++);document.addEventListener('securitypolicyviolation',e=>window.__csp.push({directive:e.effectiveDirective,uri:e.blockedURI}));});
 const p=await c.newPage();p.on('pageerror',e=>errors.push(safe(e.message)));
 const session=await b.newBrowserCDPSession();
 const browserPid=(await session.send('SystemInfo.getProcessInfo')).processInfo.find(x=>x.type==='browser').id;
 let printTarget;
 try {
  await p.goto(origin+'/',{waitUntil:'domcontentloaded'});const ready=await p.evaluate(()=>MultiplottrLocal.startDevelopmentMaps());if(ready.state!=='ready')throw Error('Maps not ready for print preview');
  // Reuse the two city coordinates already returned by the real geocoder, plus a synthetic test marker. No extra geocoding request.
  await p.evaluate(()=>{jQuery('#markerLocationField').val('~38.9072873,-77.0369274|Washington, DC|RED|group0\n~39.2905023,-76.6104072|Baltimore, MD|RED|group0\n~39.05,-76.85|Synthetic print test marker|GREEN|group0');clientMapLoad();});
  await p.waitForTimeout(1000);
  const click=p.locator('.rightNavButton').filter({hasText:'Print Map'}).click({timeout:15000}).catch(()=>{});
  for(let i=0;i<30;i++){const all=(await session.send('Target.getTargets')).targetInfos;printTarget=all.find(t=>/^(chrome|edge):\/\/print\//.test(t.url));if(printTarget)break;await new Promise(r=>setTimeout(r,300));}
  if(!printTarget)throw Error('Native print preview target not found');
  const attached=await session.send('Target.attachToTarget',{targetId:printTarget.targetId,flatten:false});
  let id=0;const pending=new Map();session.on('Target.receivedMessageFromTarget',e=>{if(e.sessionId!==attached.sessionId)return;const m=JSON.parse(e.message);if(pending.has(m.id)){pending.get(m.id)(m);pending.delete(m.id)}});
  async function rpc(method,params={}){const n=++id;const result=new Promise((resolve,reject)=>{pending.set(n,resolve);setTimeout(()=>{if(pending.delete(n))reject(Error('Print preview inspection timed out'))},8000)});await session.send('Target.sendMessageToTarget',{sessionId:attached.sessionId,message:JSON.stringify({id:n,method,params})});const r=await result;if(r.error)throw Error(r.error.message);return r.result;}
  await new Promise(r=>setTimeout(r,2000));
  const evaluated=await rpc('Runtime.evaluate',{expression:`(()=>{const app=document.querySelector('print-preview-app');const area=app?.shadowRoot?.querySelector('print-preview-preview-area');const doc=app?.documentInfo_;return {title:document.title,appPresent:!!app,previewAreaPresent:!!area,pageCount:doc?.documentSettings?.pageCount??doc?.pageCount??null,previewState:area?.previewState??area?.previewState_??null,pluginPresent:!!area?.plugin_,pageLabels:document.body.innerText.match(/\\b\\d+\\s+(?:pages?|sheets?)\\b/g)||[],diagnosticProperties:app?Object.keys(app).filter(k=>/document|preview/i.test(k)):[]};})()`,returnByValue:true});
  const preview=evaluated.result.value;
  const metrics=await rpc('Page.getLayoutMetrics');const viewport=metrics.cssLayoutViewport||metrics.layoutViewport;
  const capture=await rpc('Page.captureScreenshot',{format:'png',clip:{x:Math.min(350,viewport.clientWidth/3),y:0,width:Math.max(100,viewport.clientWidth-Math.min(350,viewport.clientWidth/3)),height:viewport.clientHeight,scale:1}});
  fs.writeFileSync('local-test/maps-native-print-preview.png',Buffer.from(capture.data,'base64'));
  // Cancel from the preview UI; closing this protected target directly can stall Edge.
  const result={testType:'Real headed Edge native print preview; no print job sent',result:'REQUIRES_VISUAL_REVIEW',preview,errors,csp,unexpectedDestinations:[...new Set(unexpected)]};
  fs.writeFileSync('local-test/maps-native-print-results.json',JSON.stringify(result,null,2));
  console.log(JSON.stringify(result,null,2));
  await Promise.race([rpc('Runtime.evaluate',{expression:"(()=>{const app=document.querySelector('print-preview-app');const sidebar=app?.shadowRoot?.querySelector('print-preview-sidebar');const strip=sidebar?.shadowRoot?.querySelector('print-preview-button-strip');const cancel=strip?.shadowRoot?.querySelector('.cancel-button');if(cancel){cancel.click();return true;}return false;})()",returnByValue:true}).catch(()=>{}),new Promise(r=>setTimeout(r,1000))]);
  await Promise.race([rpc('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27}).catch(()=>{}),new Promise(r=>setTimeout(r,1000))]);
  await Promise.race([click,new Promise(r=>setTimeout(r,1000))]);
  // Do not evaluate the blocked page renderer while the native print dialog is open.

  const saved=JSON.parse(fs.readFileSync('local-test/maps-native-print-results.json','utf8'));saved.csp=csp;fs.writeFileSync('local-test/maps-native-print-results.json',JSON.stringify(saved,null,2));
 } finally {const closed=await Promise.race([b.close().then(()=>true).catch(()=>false),new Promise(r=>setTimeout(()=>r(false),3000))]);if(!closed)require('child_process').execFileSync('taskkill',['/PID',String(browserPid),'/T','/F'],{stdio:'ignore'});}
})().catch(e=>{console.error(safe(e.message));process.exitCode=1});
