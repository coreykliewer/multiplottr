const {chromium}=require('../local-test/browser-tools/node_modules/playwright-core');
const assert=require('assert');
(async()=>{
 const browser=await chromium.launch({executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',headless:true});
 try{
  const context=await browser.newContext({viewport:{width:1400,height:1000}});const errors=[],outside=[];
  await context.route('**/*',r=>{const u=new URL(r.request().url());if(u.origin!=='http://localhost:8080'){outside.push(u.origin);return r.abort();}if(u.pathname==='/__local_maps_config')return r.fulfill({contentType:'application/json',body:'{"enabled":false}'});return r.continue();});
  const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));await page.goto('http://localhost:8080/mapping/');await page.waitForFunction(()=>window.MappingWorkspace?.state.phase==='disabled');
  const width=()=>page.locator('main > aside').evaluate(e=>Math.round(e.getBoundingClientRect().width));
  const documentBefore=await page.evaluate(()=>JSON.stringify(MappingWorkspace.state.document));assert.equal(await width(),360);
  let handle=await page.locator('#sidebarResize').boundingBox();await page.mouse.move(handle.x+4,handle.y+50);await page.mouse.down();await page.mouse.move(796,handle.y+50,{steps:8});await page.mouse.up();assert(Math.abs(await width()-600)<=2);assert.equal(await page.locator('body').evaluate(e=>e.classList.contains('sidebar-resizing')),false);
  await page.reload();await page.waitForFunction(()=>window.MappingWorkspace?.state.phase==='disabled');assert(Math.abs(await width()-600)<=2);
  assert.equal(await page.locator('#sidebarExpand,#sidebarMinimize').count(),0);await page.locator('#sidebarResize').dblclick();assert.equal(await width(),1392);await page.locator('#sidebarResize').dblclick();assert.equal(await width(),360);
  await page.locator('#sidebarResize').focus();await page.keyboard.press('ArrowLeft');assert.equal(await width(),384);await page.keyboard.press('Home');assert.equal(await width(),360);await page.keyboard.press('End');assert.equal(await width(),1392);
  const current=await page.evaluate(()=>JSON.stringify(MappingWorkspace.state.document));await page.locator('#sidebarResize').focus();await page.keyboard.press('Home');assert.equal(await page.evaluate(()=>JSON.stringify(MappingWorkspace.state.document)),current);
  await page.locator('#sidebarResize').focus();await page.keyboard.press('End');await page.setViewportSize({width:390,height:844});assert(!await page.locator('#sidebarResize').isVisible());assert(await page.locator('#mapArea').isVisible());assert.equal(await width(),390);assert(await page.evaluate(()=>document.documentElement.scrollWidth===innerWidth));
  await page.setViewportSize({width:1400,height:1000});assert.equal(await width(),1392);await page.emulateMedia({media:'print'});assert(!await page.locator('aside').isVisible());assert(await page.locator('#mapArea').isVisible());
  assert.deepEqual(errors,[]);assert.deepEqual(outside,[]);assert(documentBefore);
  console.log('PASS: drag resize, remembered width, full width, 360px minimum, keyboard controls, mobile/print layout, unchanged document, no JS errors/external requests.');
 }finally{await browser.close();}
})().catch(e=>{console.log(e.message);process.exitCode=1;});
