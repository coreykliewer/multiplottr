const {chromium}=require('./browser-tools/node_modules/playwright-core');
const fs=require('fs'),assert=require('assert');
const policy=JSON.parse(fs.readFileSync('local-test/maps-policy-test.json','utf8').replace(/^\uFEFF/,''));
const origin='http://localhost:8080';
const plugins=['/scripts/infobox.js','/scripts/markerwithlabel.js','/scripts/geolocation-marker.js','/scripts/vendor/markermanager-1.0.29/markermanager.umd.js','/scripts/jquery.ui.map.js','/scripts/local-map-engine.js'];
const googleDouble=`
window.__syntheticGeocodes=[];
class Point {constructor(x,y){this.x=x;this.y=y;}}
class LatLng {constructor(a,b){this.a=Number(a);this.b=Number(b);}lat(){return this.a;}lng(){return this.b;}}
class Bounds {constructor(){this.points=[];}extend(p){this.points.push(p);return this;}}
class MapDouble {constructor(el,options){this.options=options;this.center=options.center;this.zoom=options.zoom;el.textContent='Synthetic map test double (no map tiles)';window.__mapConstructions=(window.__mapConstructions||0)+1;}fitBounds(b){this.bounds=b;}getCenter(){return this.center;}getZoom(){return this.zoom;}}
class Marker {constructor(o){this.options=o;this.position=o.position;this.map=o.map;}setMap(m){this.map=m;}getPosition(){return this.position;}}
class Geocoder {geocode(p,cb){window.__syntheticGeocodes.push(p.address);cb([{geometry:{location:new LatLng(40,-74)}}],'OK');}}
const event={addListener(o,n,cb){o.events=o.events||{};(o.events[n]=o.events[n]||[]).push(cb);return {o,n,cb};},addListenerOnce(o,n,cb){const h=this.addListener(o,n,cb);if(n==='idle'||n==='loaded')queueMicrotask(()=>{cb();this.removeListener(h)});return h;},removeListener(h){h.o.events[h.n]=h.o.events[h.n].filter(cb=>cb!==h.cb);},trigger(o,n,e){for(const cb of o.events?.[n]||[])cb(e);}};
window.google={maps:{Map:MapDouble,Marker,OverlayView:class{},MVCObject:class{},Geocoder,LatLng,LatLngBounds:Bounds,Point,Size:Point,InfoWindow:class{close(){}},GeocoderStatus:{OK:'OK'},MapTypeId:{ROADMAP:'roadmap'},RenderingType:{RASTER:'RASTER'},event,plugins:{}}};
`;
const doubles={
 '/scripts/infobox.js':'window.InfoBox=class {close(){} setContent(){} open(){}};',
 '/scripts/markerwithlabel.js':'window.MarkerWithLabel=google.maps.Marker;',
 '/scripts/geolocation-marker.js':'window.GeolocationMarker=class {};',
 '/scripts/vendor/markermanager-1.0.29/markermanager.umd.js':'google.maps.plugins.markermanager={MarkerManager:class {constructor(){} clearMarkers(){} addMarkers(){} refresh(){} hide(){}}};',
 '/scripts/jquery.ui.map.js':'jQuery.fn.gmap=function(){return this;};'
};
(async()=>{
 const browser=await chromium.launch({executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',headless:true});
 const results=[];
 for(const scenario of ['disabled','invalid-key','ready-with-doubles','api-failure','plugin-failure','auth-failure']){
  const c=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});c.setDefaultTimeout(6000);
  const paths=[],blocked=[],errors=[];let googleCalls=0,configCalls=0;
  await c.route('**/*',async r=>{
   const u=new URL(r.request().url());
   if(u.origin===origin){
    if(u.pathname==='/__local_maps_config'){configCalls++;return r.fulfill({json:{enabled:scenario!=='disabled',reason:'disabled',apiKey:scenario==='invalid-key'?'invalid':scenario==='disabled'?'':'AIza'+'A'.repeat(35)}});}
    if(u.pathname==='/'){
     const response=await r.fetch({maxRedirects:0});assert.equal(response.status(),200);
     return r.fulfill({response,headers:{...response.headers(),'content-security-policy':policy.csp}});
    }
    if(plugins.includes(u.pathname)){
     paths.push(u.pathname);
     if(scenario==='plugin-failure'&&u.pathname==='/scripts/infobox.js')return r.abort('failed');
     if(doubles[u.pathname])return r.fulfill({contentType:'application/javascript',body:doubles[u.pathname]});
    }
    return r.continue();
   }
   // Google is fulfilled from synthetic text here; no external request is ever continued.
   if(u.origin==='https://maps.googleapis.com'&&u.pathname==='/maps/api/js'){
    googleCalls++;
    if(scenario==='api-failure')return r.abort('failed');
    assert.equal(u.searchParams.get('libraries'),'marker,geocoding');
    const cb=u.searchParams.get('callback');assert.equal(cb,'__multiplottrGoogleReady');
    return r.fulfill({contentType:'application/javascript',body:googleDouble+(scenario==='auth-failure'?'window.gm_authFailure();':'')+'window.'+cb+'();'});
   }
   blocked.push(u.origin+u.pathname);return r.abort('blockedbyclient');
  });
  const p=await c.newPage();p.on('pageerror',e=>errors.push(e.message.replace(/AIza[\w-]+/g,'[REDACTED]')));
  try{
   await p.goto(origin+'/',{waitUntil:'networkidle'});
   const state=await p.evaluate(()=>MultiplottrLocal.startDevelopmentMaps());
   const expected=scenario==='disabled'?'disabled':scenario==='ready-with-doubles'?'ready':'failed';assert.equal(state.state,expected);
   assert.equal(googleCalls,scenario==='disabled'||scenario==='invalid-key'?0:1);
   if(scenario==='ready-with-doubles'){
    assert.deepEqual(paths,plugins);
    await p.evaluate(()=>MultiplottrLocal.startDevelopmentMaps());assert.equal(configCalls,1);assert.equal(googleCalls,1);
    assert.equal(await p.evaluate(()=>window.__mapConstructions),1);
    assert.equal(await p.evaluate(()=>window.map.options.mapTypeControl),false);
    assert.equal(await p.evaluate(()=>window.map.options.streetViewControl),false);
    // Exercise the actual click boundary, not just the map functions: a stale offline closure previously blocked it.
    await p.locator('#quickSearch textarea').fill('Synthetic contract address');
    await p.locator('#quickSearch .quickSearchLiFix a').click();
    await p.waitForFunction(()=>window.__syntheticGeocodes.includes('Synthetic contract address') && addressPlottedMarkers.length===1);
    await p.evaluate(()=>{window.__syntheticGeocodes=[];jQuery('#markerLocationField').val('~40,-74|Synthetic contract location|RED|group0');clientMapLoad();geocodeThis('123 Example Street, Test City');});
    assert((await p.locator('#legendDiv').innerText()).includes('Synthetic contract location'));
    assert.deepEqual(await p.evaluate(()=>window.__syntheticGeocodes),['123 Example Street, Test City']);
    assert.equal(await p.evaluate(()=>window.__mapConstructions),1);
    await p.evaluate(()=>window.gm_authFailure());
    assert.equal(await p.evaluate(()=>MultiplottrLocal.mapsState()),'failed');
    assert.equal(await p.evaluate(()=>MultiplottrLocal.mapsAvailable()),false);
   }
   await p.locator('#introText .close').click();await p.locator('#aboutMP').click();assert(await p.locator('#introText').isVisible());
   assert.deepEqual(errors,[]);assert.deepEqual(blocked,[]);
   results.push({scenario,result:'PASS',scope:'Synthetic loader/control-flow contract only; no real Google Maps request or key.'});
  }catch(e){results.push({scenario,result:'FAIL',detail:e.message.replace(/AIza[\w-]+/g,'[REDACTED]').slice(0,500)});}
  await c.close();
 }
 await browser.close();
 fs.writeFileSync('local-test/maps-contract-results.json',JSON.stringify({realMapsTested:false,results},null,2));console.log(JSON.stringify({realMapsTested:false,results},null,2));
 if(results.some(r=>r.result==='FAIL'))process.exitCode=1;
})().catch(e=>{console.error(e.message);process.exitCode=1});
