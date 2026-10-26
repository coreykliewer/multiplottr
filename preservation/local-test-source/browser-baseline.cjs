const { chromium } = require('./browser-tools/node_modules/playwright-core');
const fs = require('fs');
(async () => {
 const browser = await chromium.launch({executablePath:'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless:true});
 const context = await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
 await context.route('**/*', route => {
  const u = new URL(route.request().url());
  if (u.origin === 'http://localhost:8080' || u.origin === 'http://127.0.0.1:8080') return route.continue();
  return route.abort('blockedbyclient');
 });
 const page = await context.newPage();
 const errors=[], failures=[], external=[];
 page.on('pageerror', e=>errors.push(e.message.replace(/AIza[\w-]+/g,'[REDACTED]')));
 page.on('response', r=>{if(r.status()>=400)failures.push({path:new URL(r.url()).pathname,status:r.status()});});
 page.on('request', r=>{const u=new URL(r.url());if(!['localhost','127.0.0.1'].includes(u.hostname))external.push(u.origin+u.pathname);});
 await page.goto('http://localhost:8080/',{waitUntil:'networkidle'});
 const scripts=await page.locator('script[src]').evaluateAll(es=>es.map(e=>{const u=new URL(e.src);return u.origin+u.pathname}));
 const state=await page.evaluate(()=>({jquery:window.jQuery?.fn.jquery,google:typeof google,aboutHandlers:window.jQuery?!!jQuery._data(document,'events'):false}));
 const out={errors,failures,external:[...new Set(external)],scripts,state};
 fs.writeFileSync('local-test/baseline-results.json',JSON.stringify(out,null,2));
 console.log(JSON.stringify(out,null,2));
 await browser.close();
})().catch(e=>{console.error(e.message);process.exitCode=1});
