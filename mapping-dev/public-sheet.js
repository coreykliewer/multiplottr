(function(root){
 'use strict';const node=typeof module!=='undefined'&&module.exports;
 const S=node?require('./spreadsheet-input'):root.SpreadsheetInput,M=node?require('./document'):root.MapDocument;
 function sourceURL(value,tab=''){
  let u;try{u=new URL(value);}catch(_){throw Error('Paste the published Google Sheets CSV URL.');}
  const match=/^\/spreadsheets\/d\/e\/([a-zA-Z0-9_-]{20,200})\/pub(?:html)?$/.exec(u.pathname);
  if(u.protocol!=='https:'||u.hostname!=='docs.google.com'||u.port||u.username||u.password||!match)throw Error('Use File > Share > Publish to web, choose a tab and CSV. Ordinary edit/share links are not supported.');
  const gid=String(tab||u.searchParams.get('gid')||new URLSearchParams(u.hash.slice(1)).get('gid')||'0');if(!/^\d{1,15}$/.test(gid))throw Error('Tab ID (gid) must be numeric.');
  return 'https://docs.google.com/spreadsheets/d/e/'+match[1]+'/pub?gid='+gid+'&single=true&output=csv';
 }
 async function fetchCSV(value,tab='',options={}){
  const source=sourceURL(value,tab),url='http://127.0.0.1:8081/source?url='+encodeURIComponent(source),controller=new AbortController(),abort=()=>controller.abort();if(options.signal?.aborted)abort();options.signal?.addEventListener('abort',abort,{once:true});const timer=setTimeout(abort,15000);
  try{const response=await (options.fetch||fetch)(url,{credentials:'omit',redirect:'error',cache:'no-store',referrerPolicy:'no-referrer',signal:controller.signal,headers:{Accept:'text/csv,text/plain'}});
   if(!response.ok){let detail='Published sheet unavailable.';try{detail=(await response.json()).error||detail;}catch(_){}throw Error(detail);}
   if(response.redirected||(response.url&&response.url!==url))throw Error('Google did not return the published CSV. Check publishing permissions and the selected tab.');
   const type=response.headers.get('content-type')||'';if(!/^(text\/(csv|plain|tab-separated-values)|application\/(csv|octet-stream))(;|$)/i.test(type))throw Error('Google returned a page rather than CSV. Publish the selected tab as CSV.');
   if(Number(response.headers.get('content-length'))>1048576)throw Error('Sheet exceeds 1 MB.');
   const reader=response.body?.getReader();if(!reader)throw Error('Streaming download is unavailable in this browser.');const chunks=[];let size=0;
   try{while(true){const {done,value}=await reader.read();if(done)break;size+=value.length;if(size>1048576){await reader.cancel();throw Error('Sheet exceeds 1 MB.');}chunks.push(value);}}finally{reader.releaseLock();}
   const bytes=new Uint8Array(size);let offset=0;chunks.forEach(c=>{bytes.set(c,offset);offset+=c.length;});return new TextDecoder('utf-8',{fatal:true}).decode(bytes);
  }catch(e){if(e.name==='AbortError')throw Error('Sheet refresh cancelled or timed out after 15 seconds.');throw Error(e.message+(e.message==='Failed to fetch'?' Ensure the local sheet-fetch helper is running.':'')+' The last loaded map has been kept.');}finally{clearTimeout(timer);options.signal?.removeEventListener('abort',abort);}
 }
 function validate(source,doc,defaults){
  const result=S.validate(source,doc,defaults),seen=new Set();if(source.hasHeader&&source.rows.length===1)result.errors=result.errors.filter(e=>e!=='No data rows found.');if(source.delimiter!==','||!source.hasHeader)result.errors.push('Sheet connections require CSV with a heading row.');
  if(!source.mapping.includes('source_id'))result.errors.push('Map a stable unique source_id column. Row numbers are not identities.');
  result.entries.forEach(e=>{const id=e.values.source_id;if(!id||id.length>200)e.errors.push('Each connected row needs a stable source_id (1-200 characters).');else if(seen.has(id))e.errors.push('Duplicate source_id: '+id);else seen.add(id);if(e.address.length>2000)e.errors.push('Address exceeds 2,000 characters.');});
  result.valid=result.entries.filter(e=>!e.errors.length).length;return result;
 }
 function sourceFor(text,mapping){const rows=S.parse(text,',');return {delimiter:',',hasHeader:true,rows,mapping:mapping||rows[0]?.cells.map(h=>/^(?:source|location|record)[_ -]id$|^id$/i.test(h.trim())?'source_id':S.heading(h))||[]};}
 async function reconcile(document,source,connection,options={}){
  const result=validate(source,document,connection.defaults);if(result.errors.length||result.valid!==result.entries.length)throw Error([...result.errors,...result.entries.filter(e=>e.errors.length).map(e=>'Row '+e.row+': '+e.errors.join(' '))].join('\n'));
  const doc=M.copy(document),seen=new Set(),groups=new Map(),owned=new Map();let geocoded=0,added=0,updated=0;
  doc.locations.filter(m=>m.sheetSource?.connectionId===connection.id).forEach(m=>{if(owned.has(m.sheetSource.sourceId))throw Error('Saved connection has duplicate source identities.');owned.set(m.sheetSource.sourceId,m);});
  const current=()=>{if(options.signal?.aborted||options.isCurrent&&!options.isCurrent())throw Error('Sheet refresh cancelled because the map changed.');};
  for(const entry of result.entries){current();const id=entry.values.source_id;seen.add(id);const existing=owned.get(id),locationKey=entry.point?'point:'+entry.point.lat+','+entry.point.lng:'address:'+entry.address;
   let point=entry.point;if(!point&&existing?.sheetSource.locationKey===locationKey)point=M.copy(existing.coordinates);
   if(!point){if(!options.geocode)throw Error('Maps is unavailable. Load Maps before refreshing address-only rows.');point=await options.geocode(entry.address,options.signal);current();if(!Number.isFinite(point?.lat)||!Number.isFinite(point?.lng)||Math.abs(point.lat)>90||Math.abs(point.lng)>180)throw Error('Invalid geocoding result for row '+entry.row);geocoded++;}
   let groupId=entry.groupId;if(entry.newGroup){const key=entry.sourceGroupId||S.norm(entry.newGroup);if(groups.has(key))groupId=groups.get(key);else{const candidates=doc.groups.filter(g=>entry.sourceGroupId?g.extensions?.csvSourceId===entry.sourceGroupId:S.norm(g.name)===S.norm(entry.newGroup));if(candidates.length>1)throw Error('Group became ambiguous.');if(candidates.length)groupId=candidates[0].id;else{groupId=M.id();doc.groups.push({id:groupId,name:entry.newGroup,visible:true,...(entry.sourceGroupId?{extensions:{csvSourceId:entry.sourceGroupId}}:{})});}groups.set(key,groupId);}}
   if(!doc.groups.some(g=>g.id===groupId))throw Error('A referenced group was removed. Preview the connection again.');
   const values={name:entry.name,originalAddress:entry.originalAddressOverride!==undefined?entry.originalAddressOverride:(entry.address||null),coordinates:point,description:entry.description,appearance:entry.appearance,groupId,sheetSource:{connectionId:connection.id,sourceId:id,locationKey,missing:false}};
   // Source-owned fields replace supported values; retain unknown nested fields so
   // forward-compatible saved documents can still pass the server preservation gate.
   if(existing)for(const [key,known] of Object.entries({appearance:['kind','color','size','icon','label'],coordinates:['lat','lng'],description:['format','value'],sheetSource:['connectionId','sourceId','locationKey','missing']})){
    values[key]={...Object.fromEntries(Object.entries(existing[key]||{}).filter(([name])=>!known.includes(name))),...values[key]};
   }
   if(existing){if(JSON.stringify({...existing,...values})!==JSON.stringify(existing))updated++;Object.assign(existing,values);}else{if(doc.locations.length>=500)throw Error('Refresh would exceed the 500-marker map limit.');doc.locations.push({id:M.id(),...values,extensions:{}});added++;}
  }
  const missing=[];doc.locations.forEach(m=>{if(m.sheetSource?.connectionId===connection.id&&!seen.has(m.sheetSource.sourceId)){m.sheetSource.missing=true;missing.push(m.sheetSource.sourceId);}});
  current();doc.sheetConnection={...M.copy(connection),lastSuccess:new Date().toISOString()};const check=M.inspect(doc);if(check.errors.length||check.readonly.length)throw Error([...check.errors,...check.readonly].join('; '));
  return {document:doc,added,updated,missing,geocoded};
 }
 const api={sourceURL,fetchCSV,validate,sourceFor,reconcile};if(node)module.exports=api;else root.PublicSheet=api;
})(typeof window==='undefined'?globalThis:window);
