// Local synthetic integration only: real Google Maps + Docker API, controlled CSV.
const {chromium}=require('../local-test/browser-tools/node_modules/playwright-core');
const assert=require('node:assert/strict'),fs=require('node:fs'),crypto=require('node:crypto'),cp=require('node:child_process'),M=require('./document'),P=require('./public-sheet');
const base='http://localhost:8080',username='local_probe_review_'+crypto.randomBytes(5).toString('hex');
const csv=['source_id,lat,lng,name,color\na,35.1,-100.5,Synthetic Before,RED','source_id,lat,lng,name,color\na,35.2,-100.6,Synthetic After,GREEN'];
const source=P.sourceFor(csv[0]),connection={id:M.id(),url:P.sourceURL('https://docs.google.com/spreadsheets/d/e/2PACX_SYNTHETIC_REVIEW_12345678901234567890/pub'),headers:source.rows[0].cells,mapping:source.mapping,intervalMinutes:0,defaults:{appearance:{kind:'pin',color:'BLUE',size:'normal'},groupId:'default'},lastSuccess:null};
const nested=['appearance','coordinates','description','sheetSource'],results=[],errors=[],outside=[],violations=[];
let browser,context,registered=false,mapId,version=0,accountCleaned=false,mapRemoved=false;
const clean=s=>String(s).replace(/AIza[\w-]+/g,'[KEY]');
(async()=>{
 browser=await chromium.launch({executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',headless:true});
 context=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
 const allowed=new Set(['maps.googleapis.com','maps.gstatic.com','mapsresources-pa.googleapis.com','fonts.googleapis.com','fonts.gstatic.com']);
 await context.route('**/*',r=>{const u=new URL(r.request().url());if(u.origin==='http://127.0.0.1:8081'&&u.pathname==='/source'){assert.equal(u.searchParams.get('url'),connection.url);return r.fulfill({status:200,contentType:'text/csv',headers:{'access-control-allow-origin':base},body:csv[version]});}if(u.origin===base||allowed.has(u.hostname))return r.continue();outside.push(u.hostname);return r.abort();});
 const session=await(await context.request.get(base+'/__local_membership/session')).json();
 const registration=await context.request.post(base+'/__local_membership/register',{headers:{'X-Local-CSRF':session.csrf},data:{username,password:crypto.randomBytes(18).toString('hex')}});assert(registration.ok());registered=true;
 async function api(method,id,data){const s=await(await context.request.get(base+'/__local_membership/session')).json();return context.request.fetch(base+'/__local_documents/maps'+(id?'/'+id:''),{method,headers:{'X-Local-CSRF':s.csrf},data});}
 const seed=(await P.reconcile(M.create(),source,connection)).document;mapId=seed.id;seed.settings.center={lat:35.15,lng:-100.55};seed.settings.zoom=7;
 for(const key of nested)seed.locations[0][key].future={nested:['preserve',1]};
 const manual={id:M.id(),name:'Synthetic manual control',originalAddress:null,coordinates:{lat:36,lng:-101},description:{format:'text',value:'Manual note'},appearance:{kind:'image',size:'small',color:'#6D28D9',icon:'/mapping/assets/star.svg',label:'Manual label'},groupId:'default',extensions:{keep:true}};seed.locations.push(manual);
 assert.equal((await api('POST',null,{document:seed})).status(),201);
 const page=await context.newPage();page.setDefaultTimeout(25000);page.on('dialog',d=>d.accept());page.on('pageerror',e=>errors.push(clean(e.message)));
 await page.addInitScript(()=>{window.__reviewCsp=[];document.addEventListener('securitypolicyviolation',e=>__reviewCsp.push(e.violatedDirective));});
 const doc=()=>page.evaluate(()=>MapDocument.copy(MappingWorkspace.state.document));
 async function loaded(){await page.waitForFunction(()=>window.MappingWorkspace?.state.phase==='ready'&&!!MappingWorkspace.state.revision&&MappingWorkspace.state.document.sheetConnection.lastSuccess!==null&&!MappingWorkspace.sheetController.busy);}
 async function save(){await page.evaluate(()=>MappingWorkspace.save());assert.equal(await page.evaluate(()=>MappingWorkspace.state.dirty),false);assert.match(await page.locator('#statusFull').textContent(),/^Saved revision/);}
 async function test(name,fn){await fn();results.push({name,status:'PASS'});console.log('PASS: '+name);}
 await test('Real API save and browser load retain source extensions and manual marker',async()=>{
  await page.goto(base+'/mapping/?map='+mapId);await loaded();const d=await doc();for(const key of nested)assert.deepEqual(d.locations[0][key].future,seed.locations[0][key].future);assert.deepEqual(d.locations[1],manual);await save();
 });
 await test('Controlled refresh updates real markers without replacing IDs, instances or extensions',async()=>{
  await page.evaluate(()=>{const s=MappingWorkspace.state;window.__reviewRefs=[...s.markers.values()];window.__reviewManager=s.clustering;});version=1;
  await page.evaluate(()=>MappingWorkspace.sheetController.refresh());assert.equal(await page.locator('#sheetErrorMessage').textContent(),'');
  const d=await doc();assert.equal(d.locations[0].id,seed.locations[0].id);assert.equal(d.locations[0].name,'Synthetic After');assert.deepEqual(d.locations[1],manual);
  for(const key of nested)assert.deepEqual(d.locations[0][key].future,seed.locations[0][key].future);
  assert(await page.evaluate(()=>{const s=MappingWorkspace.state,m=s.markers.get(s.document.locations[0].id);return __reviewRefs.every(m=>s.markers.get(m.__locationId)===m)&&s.clustering===__reviewManager&&m.getPosition().lat()===35.2&&m.getIcon().fillColor==='GREEN';}));await save();
 });
 await test('Refreshed nested metadata survives real save, reopen and resave',async()=>{
  const before=await doc();await page.reload();await loaded();assert.deepEqual((await doc()).locations,before.locations);await save();const stored=await(await api('GET',mapId)).json();assert.deepEqual(stored.document.locations,before.locations);
 });
 await test('Malformed POST/PUT return 422 and preserve stored map, revision and browser state',async()=>{
  const stored=await(await api('GET',mapId)).json(),inMemory=await doc(),bad=[];
  for(const value of [null,1,'invalid',[],{lastCustomColor:[]}]){const d=M.copy(stored.document);d.settings.plotDefaults=value;bad.push(d);}
  {const d=M.copy(stored.document);d.locations=[null];bad.push(d);}
  {const d=M.copy(stored.document);d.sheetConnection.headers='bad';d.sheetConnection.mapping=3;bad.push(d);}
  for(const document of bad)for(const method of ['POST','PUT']){const response=await api(method,method==='PUT'?mapId:null,{document,revision:stored.revision});assert.equal(response.status(),422);assert.equal(typeof(await response.json()).error,'string');assert.deepEqual(await(await api('GET',mapId)).json(),stored);}
  assert.deepEqual(await doc(),inMemory);assert((await page.evaluate(()=>MapDocument.inspect({...MappingWorkspace.state.document,locations:[null]}))).errors.length);
 });
 await test('Valid edit and save still succeed after rejected writes',async()=>{
  await page.evaluate(()=>{const d=MapDocument.copy(MappingWorkspace.state.document);d.title='Synthetic recovery edit';MappingWorkspace.selectDocument(d,MappingWorkspace.state.revision);});
  await loaded();await save();assert.equal((await(await api('GET',mapId)).json()).document.title,'Synthetic recovery edit');
 });
 violations.push(...await page.evaluate(()=>__reviewCsp));assert.deepEqual(errors,[]);assert.deepEqual(outside,[]);assert.deepEqual(violations,[]);
})().catch(e=>{results.push({name:'Harness',status:'FAIL',reason:clean(e.message)});console.error(clean(e.stack));process.exitCode=1;}).finally(async()=>{
 await browser?.close();if(registered){const code=fs.readFileSync('local-test/membership-cleanup.php','utf8');cp.execFileSync('docker',['exec','-i','-e','LOCAL_MEMBER_FIXTURES_B64='+Buffer.from(JSON.stringify([{username}])).toString('base64'),'multiplottr-local-web-1','php'],{input:code,encoding:'utf8'});accountCleaned=true;assert.equal((await fetch(base+'/__local_documents/maps/'+mapId)).status,404);mapRemoved=true;}
 fs.writeFileSync('local-test/review-browser-results.json',JSON.stringify({results,errors,outside,violations,accountCleaned,mapRemoved,realGoogleMaps:true,realDockerPersistence:true,controlledCSV:true,liveGoogleSheets:false},null,2));
});
