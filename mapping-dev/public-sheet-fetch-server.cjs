const http=require('http'),{fetchPublished}=require('./public-sheet-fetch.cjs');
function createServer(options={}){let active=0;let recent=[];return http.createServer(async(req,res)=>{
 const origins=['http://localhost:8080','http://127.0.0.1:8080'],origin=req.headers.origin;if(!['127.0.0.1:8081','localhost:8081'].includes(req.headers.host)||origin&&!origins.includes(origin)){res.writeHead(403);return res.end();}
 if(origin)res.setHeader('Access-Control-Allow-Origin',origin);res.setHeader('Vary','Origin');res.setHeader('Cache-Control','no-store');res.setHeader('X-Content-Type-Options','nosniff');
 if(req.method==='OPTIONS'){res.writeHead(204,{'Access-Control-Allow-Methods':'GET'});return res.end();}if(req.method!=='GET'){res.writeHead(405);return res.end();}
 let url;try{url=new URL(req.url,'http://127.0.0.1:8081');}catch(_){res.writeHead(400,{'Content-Type':'application/json'});return res.end(JSON.stringify({error:'Invalid request URL.'}));}if(url.pathname==='/health'){res.writeHead(200,{'Content-Type':'application/json'});return res.end(JSON.stringify({service:'multiplottr-public-sheet-fetch-v1'}));}
 if(url.pathname!=='/source'){res.writeHead(404);return res.end();}recent=recent.filter(t=>Date.now()-t<60000);if(active>=2||recent.length>=30){res.writeHead(429,{'Content-Type':'application/json'});return res.end(JSON.stringify({error:'Too many sheet fetches. Wait a minute and retry.'}));}
 active++;recent.push(Date.now());const controller=new AbortController();res.on('close',()=>{if(!res.writableEnded)controller.abort();});
 try{const text=await(options.fetchPublished||fetchPublished)(url.searchParams.get('url')||'',{signal:controller.signal});res.writeHead(200,{'Content-Type':'text/csv; charset=utf-8'});res.end(text);}catch(e){res.writeHead(502,{'Content-Type':'application/json'});res.end(JSON.stringify({error:e.name==='AbortError'?'Sheet download timed out or was cancelled.':e.message}));}finally{active--;}
 });}
if(require.main===module){const server=createServer();server.listen(8081,'127.0.0.1',()=>console.log('Multiplottr restricted public-sheet helper listening on 127.0.0.1:8081'));server.on('error',e=>{console.error(e.code||'Helper failed');process.exitCode=1;});}
module.exports={createServer};
