(function(root){
 'use strict';
 const copy=x=>JSON.parse(JSON.stringify(x));
 const random=typeof crypto!=='undefined'?crypto:require('crypto').webcrypto;
 const id=()=>Array.from(random.getRandomValues(new Uint8Array(16)),x=>x.toString(16).padStart(2,'0')).join('');
 function create(){return {schemaVersion:1,id:id(),title:'New map',description:{format:'html',value:''},locations:[],groups:[{id:'default',name:'Locations',visible:true}],geometry:[],settings:{center:{lat:0,lng:0},zoom:2,minZoom:0,maxZoom:22,mapType:'roadmap',zoomControl:true,mapTypeControl:false,legendVisible:true,clusterMarkers:true,shareWithLink:true},extensions:{}};}
 const colors=['RED','GREEN','BLUE','ORANGE','YELLOW','GREY','LIME','CYAN','VIOLET','PURPLE','BLACK','WHITE','PINK'];
 function safeHtml(value){return typeof value==='string'&&!/<\s*\/?\s*(?!h[1-3]\b|p\b|br\b|strong\b|b\b|em\b|i\b|ul\b|ol\b|li\b|a\b|img\b)[a-z][^>]*>/i.test(value)&&!/<[^>]*\s(?:on\w+|style|srcset|formaction)\s*=/i.test(value)&&!/(?:javascript|data|vbscript)\s*:/i.test(value)&&!/(?:href|src)\s*=\s*["']?(?:https?:|\/\/)/i.test(value)&&!/<img\b(?![^>]*\bsrc\s*=\s*["']\/mapping\/assets\/[^"']+["'])/i.test(value);}
 function inspect(d){
  const errors=[],readonly=[];
  if(!d||typeof d!=='object'||Array.isArray(d))return {errors:['Document object required'],readonly:[]};
  if(d.schemaVersion!==1)readonly.push('Unsupported document version');
  if(!/^[a-f0-9]{32}$/.test(d.id||''))errors.push('Invalid map identity');
  if(typeof d.title!=='string'||!d.title.trim()||new TextEncoder().encode(d.title).length>1000)errors.push('Title is required (1000 UTF-8 bytes maximum)');
  if(!Array.isArray(d.locations)||!Array.isArray(d.groups)||!Array.isArray(d.geometry))errors.push('Locations, groups and geometry arrays are required');
  if(errors.length)return {errors,readonly};
  if(d.locations.length>500)errors.push('Local document limit: 500 locations');
  const ids=new Set(),groupIds=new Set();
  d.groups.forEach(g=>{if(!g||typeof g.id!=='string'||groupIds.has(g.id)||typeof g.name!=='string'||typeof g.visible!=='boolean')errors.push('Invalid group');groupIds.add(g?.id);});
  function content(c){if(!c||!['text','html'].includes(c.format)||typeof c.value!=='string')readonly.push('Unsupported description representation');else if(c.format==='html'&&!safeHtml(c.value))readonly.push('Description contains unsupported or external media/markup; original retained');}
  content(d.description);
  d.locations.forEach(m=>{
   if(!m||typeof m.id!=='string'||!m.id||ids.has(m.id)||typeof m.name!=='string'||!(m.originalAddress===null||typeof m.originalAddress==='string')||!Number.isFinite(m.coordinates?.lat)||Math.abs(m.coordinates.lat)>90||!Number.isFinite(m.coordinates?.lng)||Math.abs(m.coordinates.lng)>180||!groupIds.has(m.groupId))errors.push('Invalid location identity, coordinates or group');
   ids.add(m?.id);content(m?.description);
   const a=m?.appearance;
   if(!a||!['pin','number','image'].includes(a.kind)||!['normal','small'].includes(a.size)||!(colors.includes(a.color)||/^#[a-f0-9]{6}$/i.test(a.color||'')))readonly.push('Unsupported marker appearance');
   if(a?.kind==='image'&&!/^\/mapping\/assets\/[a-z0-9-]+\.svg$/i.test(a.icon||''))readonly.push('Custom icon requires an approved local asset; original retained');
  });
  const connection=d.sheetConnection;
  if(connection!=null){const c=connection,a=c.defaults?.appearance;if(!/^[a-f0-9]{32}$/.test(c.id||'')||!/^https:\/\/docs\.google\.com\/spreadsheets\/d\/e\/[a-zA-Z0-9_-]{20,200}\/pub\?gid=\d{1,15}&single=true&output=csv$/.test(c.url||'')||!Array.isArray(c.headers)||c.headers.length>128||!c.headers.every(v=>typeof v==='string'&&v.length<=500)||!Array.isArray(c.mapping)||c.mapping.length>128||!c.mapping.every(v=>typeof v==='string'&&v.length<=40)||!Number.isInteger(c.intervalMinutes)||(c.intervalMinutes!==0&&(c.intervalMinutes<5||c.intervalMinutes>1440))||!groupIds.has(c.defaults?.groupId)||!a||!['pin','number','image'].includes(a.kind)||!['normal','small'].includes(a.size)||!(colors.includes(a.color)||/^#[a-f0-9]{6}$/i.test(a.color||''))||(a.kind==='image'&&!/^\/mapping\/assets\/[a-z0-9-]+\.svg$/i.test(a.icon||''))||(c.lastSuccess!=null&&!Number.isFinite(Date.parse(c.lastSuccess))))errors.push('Invalid sheet connection');}
  const sourceIds=new Set();d.locations.forEach(m=>{const source=m.sheetSource;if(source!=null){if(!connection||source.connectionId!==connection.id||typeof source.sourceId!=='string'||!source.sourceId||source.sourceId.length>200||typeof source.locationKey!=='string'||source.locationKey.length>2100||typeof source.missing!=='boolean'||sourceIds.has(source.sourceId))errors.push('Invalid sheet marker identity');sourceIds.add(source.sourceId);}});
  const s=d.settings;
  if(s&&Object.prototype.hasOwnProperty.call(s,'clusterMarkers')&&typeof s.clusterMarkers!=='boolean')errors.push('Invalid clustering setting');
  if(s&&Object.prototype.hasOwnProperty.call(s,'shareWithLink')&&typeof s.shareWithLink!=='boolean')errors.push('Invalid sharing setting');
  if(!s||!Number.isFinite(s.center?.lat)||Math.abs(s.center.lat)>90||!Number.isFinite(s.center?.lng)||Math.abs(s.center.lng)>180||![s.zoom,s.minZoom,s.maxZoom].every(Number.isInteger)||s.minZoom<0||s.maxZoom>22||s.minZoom>s.zoom||s.zoom>s.maxZoom||!['zoomControl','mapTypeControl','legendVisible'].every(k=>typeof s[k]==='boolean'))errors.push('Invalid camera or zoom/control settings');
  if(s?.mapType!=='roadmap')readonly.push('Only road map is enabled in this local slice');
  if(s&&Object.prototype.hasOwnProperty.call(s,'plotDefaults')){
   const p=s.plotDefaults,a=p?.appearance;if(p&&Object.prototype.hasOwnProperty.call(p,'lastCustomColor')&&!/^#[a-f0-9]{6}$/i.test(p.lastCustomColor||''))errors.push('Invalid remembered custom color');
   if(!p||!groupIds.has(p.groupId))errors.push('Invalid new-marker default group');
   if(!a||!['pin','number','image'].includes(a.kind)||!['normal','small'].includes(a.size)||!(colors.includes(a.color)||/^#[a-f0-9]{6}$/i.test(a.color||''))||(a.kind==='image'&&!/^\/mapping\/assets\/[a-z0-9-]+\.svg$/i.test(a.icon||'')))readonly.push('Unsupported new-marker default appearance retained');
  }
  d.geometry.forEach(g=>{if(!['LineString','Polygon','Circle'].includes(g?.type))readonly.push('Unsupported geometry retained');else if(g.type==='Circle'){if(!Number.isFinite(g.center?.lat)||Math.abs(g.center.lat)>90||!Number.isFinite(g.center?.lng)||Math.abs(g.center.lng)>180||!Number.isFinite(g.radiusMeters)||g.radiusMeters<=0)errors.push('Invalid circle');}else{const rings=g.type==='Polygon'?g.coordinates:[g.coordinates];if(!Array.isArray(rings)||!rings.length||!rings.every(r=>Array.isArray(r)&&r.length>1&&r.every(p=>Array.isArray(p)&&p.length===2&&p.every(Number.isFinite)&&Math.abs(p[0])<=180&&Math.abs(p[1])<=90)))errors.push('Invalid geometry coordinates');}});
  if(d.compatibility?.unsupported?.length)readonly.push(...d.compatibility.unsupported);
  return {errors:[...new Set(errors)],readonly:[...new Set(readonly)]};
 }
 function fromLegacy(raw){
  const d=create();d.settings.clusterMarkers=false;d.title='Synthetic legacy preview';d.compatibility={rawOriginal:raw,adapterVersion:1,unsupported:[]};
  const lines=raw.replace(/\r\n/g,'\n').split(/\n|`/).filter(Boolean);
  lines.forEach((line,i)=>{
   const f=line.split('|');if(f.length!==4||!/^~/.test(f[0])){d.compatibility.unsupported.push('Unrecognized legacy record '+(i+1));return;}
   const xy=f[0].slice(1).split(',').map(Number),token=f[2];
   if(token==='CENTER'){d.settings.center={lat:xy[0],lng:xy[1]};d.settings.zoom=Number(f[1]);return;}
   const small=token.startsWith('SMALL'),color=small?token.slice(5):token,number=token==='NUMBER';
   if(!colors.includes(color)&&!number){d.compatibility.unsupported.push('Unsupported legacy token '+token);return;}
   let group=d.groups.find(g=>g.name===f[3]);if(!group){group={id:id(),name:f[3],visible:true};d.groups.push(group);}
   d.locations.push({id:id(),name:'Legacy location '+(i+1),originalAddress:null,coordinates:{lat:xy[0],lng:xy[1]},description:{format:'html',value:f[1]},appearance:{kind:number?'number':'pin',color:number?'RED':color,size:small?'small':'normal',label:''},groupId:group.id,extensions:{legacyToken:token,rawRecord:line}});
  });return d;
 }
 const api={copy,id,create,inspect,safeHtml,fromLegacy,colors};
 if(typeof module!=='undefined')module.exports=api;else root.MapDocument=api;
})(typeof window==='undefined'?globalThis:window);
