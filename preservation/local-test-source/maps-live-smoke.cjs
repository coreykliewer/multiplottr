const fs=require('fs');
const {chromium}=require('./browser-tools/node_modules/playwright-core');
const origin='http://localhost:8080';
const allowed=new Set(['maps.googleapis.com','mapsresources-pa.googleapis.com','maps.gstatic.com','fonts.googleapis.com','fonts.gstatic.com']);
const privateKey=JSON.parse(fs.readFileSync('local-test/config/maps.local.json','utf8').replace(/^\uFEFF/,'')).apiKey;
function safe(value){return String(value).split(privateKey).join('[REDACTED]').replace(/AIza[\w-]+/g,'[REDACTED]').replace(/https?:\/\/[^\s"'<>]+/g,s=>{try{const u=new URL(s);return u.origin+u.pathname;}catch{return '[URL]';}});}
(async()=>{
 const b=await chromium.launch({executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',headless:true});
 const c=await b.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
 const requests=[],unexpected=[],errors=[],consoleMessages=[],csp=[],failed=[];
 await c.route('**/*',r=>{const u=new URL(r.request().url());if(u.origin===origin)return r.continue();if(u.protocol==='https:'&&allowed.has(u.hostname)){requests.push(u.origin+u.pathname);return r.continue();}unexpected.push(u.origin+u.pathname);return r.abort('blockedbyclient');});
 const p=await c.newPage();
 p.on('pageerror',e=>errors.push(safe(e.message)));
 p.on('console',m=>{if(['error','warning'].includes(m.type()))consoleMessages.push({type:m.type(),text:safe(m.text())});});
 p.on('requestfailed',r=>failed.push({destination:safe(r.url()),error:safe(r.failure()?.errorText)}));
 await p.addInitScript(()=>{window.__localCsp=[];document.addEventListener('securitypolicyviolation',e=>window.__localCsp.push({directive:e.effectiveDirective,uri:e.blockedURI}));});
 console.log('Opening real browser with only scoped Google hosts allowed. No API responses are mocked.');
 await p.goto(origin+'/',{waitUntil:'domcontentloaded'});
 const state=await p.evaluate(()=>MultiplottrLocal.startDevelopmentMaps());
 await p.waitForTimeout(1500);
 const ui=await p.evaluate(()=>({state:MultiplottrLocal.mapsState(),mapExists:!!window.map,gmStyle:!!document.querySelector('#map_canvas .gm-style'),loadedMapImages:[...document.querySelectorAll('#map_canvas img')].filter(x=>x.naturalWidth>0).length,status:document.getElementById('localEnvironmentStatus')?.textContent}));
 csp.push(...(await p.evaluate(()=>window.__localCsp)).map(x=>({directive:x.directive,uri:safe(x.uri)})));
 const result={realGoogleRequests:true,state:state.state,ui,externalDestinations:[...new Set(requests)],unexpectedDestinations:[...new Set(unexpected)],errors,consoleMessages,csp,failed};
 fs.writeFileSync('local-test/maps-live-smoke.json',JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
 await p.screenshot({path:'local-test/maps-live-smoke.png'});
 await b.close();
})().catch(e=>{console.error(safe(e.message));process.exitCode=1});
