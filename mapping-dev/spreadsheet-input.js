// Spreadsheet parsing/validation only. No DOM, geocoding, or document mutations.
(function(root){
 'use strict';
 const fields=['address','latitude','longitude','name','description','color','style','size','group','marker_id','group_id','description_format','icon','label','multiplottr_csv_version','address_is_null','appearance_fields','source_id'];
 const aliases={appearance_fields:['appearance fields'],source_id:['source id','id','record id'],description_format:['description format'],icon:['icon','icon url'],label:['label','marker label'],multiplottr_csv_version:['multiplottr csv version'],address_is_null:['address is null'],address:['address','full address','street address','location'],latitude:['latitude','lat'],longitude:['longitude','lng','lon','long'],name:['name','title','marker name','location name'],description:['description','note','notes','marker description'],color:['color','colour','marker color','marker colour'],style:['style','kind','marker style','marker type'],size:['size','marker size'],group:['group','group name'],marker_id:['marker id','marker_id','location id'],group_id:['group id','group_id']};
 const norm=s=>String(s??'').normalize('NFKC').trim().replace(/\s+/g,' ').toLowerCase();
 function heading(s){const key=v=>norm(v).replace(/[_-]+/g,' '),n=key(s);return fields.find(f=>aliases[f].some(a=>key(a)===n))||'';}
 function parse(text,delimiter){
  if(text.length>1048576)throw Error('Paste is too large (maximum 1 MB).');
  const rows=[];let cells=[],cell='',quoted=false,closed=false,line=1,start=1;
  function record(){cells.push(cell);if(cells.length>128)throw Error('Too many columns (maximum 128).');if(cells.some(s=>s.trim()))rows.push({cells,line:start});cells=[];cell='';closed=false;start=line+1;if(rows.length>1001)throw Error('Paste is too large (maximum 1,000 data rows).');}
  text=text.replace(/^\uFEFF/,'');
  for(let i=0;i<text.length;i++){
   const c=text[i];
   if(quoted){if(c==='"'){if(text[i+1]==='"'){cell+='"';i++;}else{quoted=false;closed=true;}}else{cell+=c;if(c==='\n')line++;}continue;}
   if(c==='"'){if(cell!==''||closed)throw Error('Unexpected quote on row '+line+'. Quote the entire cell and double embedded quotes.');quoted=true;continue;}
   if(c===delimiter){cells.push(cell);if(cells.length>=128)throw Error('Too many columns (maximum 128).');cell='';closed=false;continue;}
   if(c==='\n'||c==='\r'){record();if(c==='\r'&&text[i+1]==='\n')i++;line++;start=line;continue;}
   if(closed){if(c===' '||c==='\t')continue;throw Error('Unexpected text after a quoted cell on row '+line+'.');}
   cell+=c;
  }
  if(quoted)throw Error('Unclosed quoted cell starting on row '+start+'.');
  record();return rows;
 }
 const legacyMapping=['address','description','color','group'];
 function parseLegacy(text){
  if(text.length>1048576)throw Error('Paste is too large (maximum 1 MB).');
  const rows=[];let start=0,record=1;
  for(let i=0;i<=text.length;i++){
   if(i!==text.length&&!/[\r\n`]/.test(text[i]))continue;
   const raw=text.slice(start,i);let separator=i===text.length?'':text[i];if(text[i]==='\r'&&text[i+1]==='\n'){separator='\r\n';i++;}
   if(raw.trim())rows.push({cells:raw.split('|'),raw,separator,line:record});
   if(rows.length>1000)throw Error('Maximum 1,000 data rows per paste.');
   start=i+1;record++;
  }
  return rows;
 }
 function legacyHtmlSupported(value){
  const model=typeof module!=='undefined'&&module.exports?require('./document.js'):root.MapDocument;
  if(!model.safeHtml(value)||/<!|<\?/.test(value))return false;
  const stack=[],voids=new Set(['br','img']);let match;
  const tags=/<(\/?)([a-z][a-z0-9]*)\b([^<>]*)>/gi;
  while((match=tags.exec(value))){
   const closing=!!match[1],tag=match[2].toLowerCase();let attrs=match[3];
   if(closing){if(attrs.trim()||stack.pop()!==tag)return false;continue;}
   const selfClosing=/\/\s*$/.test(attrs);attrs=attrs.replace(/\/\s*$/,'');const seen=new Set();
   while(attrs.trim()){
    if(!/^\s/.test(attrs))return false;attrs=attrs.trimStart();
    const a=/^([a-z][a-z0-9:-]*)\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'=<>`]+))/i.exec(attrs);if(!a)return false;
    const name=a[1].toLowerCase(),content=a[2]??a[3]??a[4];if(seen.has(name))return false;seen.add(name);
    if(!(tag==='a'&&name==='href')&&!(tag==='img'&&['src','alt'].includes(name)))return false;
    if(name==='src'&&!/^\/mapping\/assets\/[a-z0-9-]+\.svg$/i.test(content))return false;
    if(name==='href'){const href=content.replace(/&amp;/gi,'&');if(/[:\\\s]|&(?:#|[a-z][a-z0-9]*;)/i.test(href)||/^[\/\\]{2}/.test(href))return false;}
    attrs=attrs.slice(a[0].length);
   }
   if(!voids.has(tag)){if(selfClosing)return false;stack.push(tag);}
  }
  // Reject malformed tag fragments, rather than letting a browser silently repair them.
  if(stack.length||/<[\/a-z!]/i.test(value.replace(tags,'')))return false;
  return true;
 }
 function legacySuggestion(text){
  if(!text.includes('|'))return false;
  const rows=parseLegacy(text);
  return rows.some(r=>r.cells.length>=2&&(/<[a-z!/]/i.test(r.cells[1])||(r.cells.length>=3&&/^(?:#?[a-f0-9]{3}|#?[a-f0-9]{6}|RED|GREEN|BLUE|ORANGE|YELLOW|GREY|GRAY|LIME|CYAN|VIOLET|PURPLE|BLACK|WHITE|PINK)$/i.test(r.cells[2].trim()))||(r.cells.length===4&&r.cells[0].trim()&&r.cells[3].trim())));
 }
 function detect(text,force=false){
  let quoted=false,hasTabs=false;for(let i=0;i<text.length;i++){if(text[i]==='"'){if(quoted&&text[i+1]==='"')i++;else quoted=!quoted;}else if(text[i]==='\t'&&!quoted)hasTabs=true;}
  const delimiter=hasTabs?'\t':',';
  let rows;try{rows=parse(text,delimiter);}catch(e){if(!hasTabs&&!/^(address|latitude|lat|longitude|lng|name|marker[ _]id)[,\t]/i.test(text)&&legacySuggestion(text))return {structured:true,format:'legacy',delimiter:'legacy',rows:parseLegacy(text),hasHeader:false,mapping:[...legacyMapping]};return (force||hasTabs||/^\s*"/.test(text)||/^(address|latitude|lat|longitude|lng|name|marker[ _]id)[,\t]/i.test(text))?{structured:true,delimiter,error:e.message}: {structured:false};}
  if(!rows.length)return {structured:false};
  const recognized=rows[0].cells.map(heading),headers=recognized.some(Boolean);
  // Commas in ordinary street addresses are not a delimiter signal. Headerless CSV
  // needs quoting, numeric coordinate structure, or the explicit Map columns action.
  const consistent=rows.length>1&&rows.every(r=>r.cells.length===rows[0].cells.length)&&rows[0].cells.length>1;
  const coordinateLike=consistent&&rows.every(r=>r.cells.length>=2&&r.cells.slice(0,2).every(v=>/^[-+]?\d+(\.\d+)?$/.test(v.trim()))&&Math.abs(Number(r.cells[0]))<=90&&Math.abs(Number(r.cells[1]))<=180);
  const structured=force||hasTabs||headers||/^\s*"/.test(text)||coordinateLike;
  if(!hasTabs&&!headers&&!/^\s*"/.test(text)&&legacySuggestion(text))return {structured:true,format:'legacy',delimiter:'legacy',rows:parseLegacy(text),hasHeader:false,mapping:[...legacyMapping]};
  return {structured,delimiter,rows,hasHeader:headers,mapping:recognized};
 }
 function serialize(rows,delimiter){if(delimiter==='legacy')return rows.map(r=>(r.raw??(r.cells||r).join('|'))+(r.separator??'\n')).join('');return rows.map(r=>(r.cells||r).map(value=>{const s=String(value??'');return s.includes(delimiter)||/["\r\n]/.test(s)?'"'+s.replace(/"/g,'""')+'"':s;}).join(delimiter)).join('\n');}
 function color(value){let v=value.trim().toUpperCase();if(v==='GRAY')v='GREY';if(/^#?[A-F0-9]{3}$/.test(v))v='#'+v.replace('#','').split('').map(c=>c+c).join('');if(/^#?[A-F0-9]{6}$/.test(v))v='#'+v.replace('#','');return v;}
 function validate(source,document,defaults){
  const legacy=source.format==='legacy'||source.delimiter==='legacy';
  const errors=[],mapping=legacy?legacyMapping:source.mapping,width=mapping.length,used=mapping.filter(Boolean),newGroups=new Map();
  if(new Set(used).size!==used.length)errors.push('Map each supported field to only one column.');
  if(!used.includes('address')&&!(used.includes('latitude')&&used.includes('longitude')))errors.push('Identify an address column or both latitude and longitude columns.');
  const data=source.rows.slice(!legacy&&source.hasHeader?1:0),markerIds=new Map();if(data.length>1000)errors.push('Maximum 1,000 data rows per paste.');
  data.forEach(r=>{const i=mapping.indexOf('marker_id'),id=i<0?'':String(r.cells[i]||'').trim();if(id)markerIds.set(id,(markerIds.get(id)||0)+1);});
  const colors=['RED','GREEN','BLUE','ORANGE','YELLOW','GREY','LIME','CYAN','VIOLET','PURPLE','BLACK','WHITE','PINK'];
  const entries=data.map(row=>{
   const values={};mapping.forEach((field,i)=>{if(field)values[field]=String(row.cells[i]??'').trim();});
   // Descriptions are plain text; retain whitespace/newlines, including leading spaces.
   const di=mapping.indexOf('description');if(di>=0)values.description=String(row.cells[di]??'');
   const roundtrip=values.multiplottr_csv_version==='1';const encodingErrors=[];
   if(values.multiplottr_csv_version&&values.multiplottr_csv_version!=='1')encodingErrors.push('Unsupported Multiplottr CSV version.');
   if(roundtrip){for(const field of ['address','name','description','description_format','color','style','size','icon','label','group','group_id','marker_id']){const index=mapping.indexOf(field);if(index>=0){const raw=String(row.cells[index]??'');if(!raw.startsWith("'"))encodingErrors.push('Missing text-protection prefix for '+field+'.');else values[field]=raw.slice(1);}}}
   const entry={row:row.line,cells:row.cells,raw:row.raw,separator:row.separator,address:values.address||'',values,errors:[],warnings:[],point:null,appearance:{...defaults.appearance},groupId:defaults.groupId};entry.errors.push(...encodingErrors);entry.preserveName=roundtrip;entry.roundtrip=roundtrip;if(roundtrip){if(!['true','false'].includes(values.address_is_null))entry.errors.push('Invalid address_is_null flag.');else entry.originalAddressOverride=values.address_is_null==='true'?null:values.address;}
   if(legacy){if(row.cells.length>4)entry.errors.push('Legacy rows have at most four fields. Extra pipes are ambiguous; use &#124; for a literal pipe in HTML. Original row retained.');if(/[<>]/.test(entry.address)||entry.address.startsWith('~'))entry.errors.push('Legacy input expects a street address, not HTML or saved ~latitude,longitude records.');}
   else if(row.cells.length!==width)entry.errors.push('Expected '+width+' columns; found '+row.cells.length+'.');
   const lat=values.latitude||'',lng=values.longitude||'',numeric=v=>v!==''&&/^[+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?$/i.test(v)&&Number.isFinite(Number(v));
   if(numeric(lat)&&numeric(lng)&&Math.abs(Number(lat))<=90&&Math.abs(Number(lng))<=180)entry.point={lat:Number(lat),lng:Number(lng)};
   else if(lat||lng){const issue='Coordinates must contain latitude -90 to 90 and longitude -180 to 180.';if(entry.address)entry.warnings.push(issue+' The address will be geocoded instead.');else entry.errors.push(issue);}
   if(!entry.point&&!entry.address)entry.errors.push('An address or valid coordinate pair is required.');
   if(values.color){const c=color(values.color);if(!colors.includes(c)&&!/^#[A-F0-9]{6}$/.test(c))entry.errors.push('Unsupported color. Use a named marker color or hex such as #90EE90.');else entry.appearance.color=roundtrip?values.color:c;}
   if(values.style){let style=norm(values.style);if(style==='numbered')style='number';if(!['pin','number','image'].includes(style))entry.errors.push('Style must be pin, numbered, or image (local star).');else{entry.appearance.kind=style;entry.appearance.icon=style==='image'?'/mapping/assets/star.svg':null;}}
   if(values.size){const size=norm(values.size);if(!['normal','small'].includes(size))entry.errors.push('Size must be normal or small.');else entry.appearance.size=size;}
   if(values.icon){if(!/^\/mapping\/assets\/[a-z0-9-]+\.svg$/i.test(values.icon))entry.errors.push('Only approved local SVG icons are supported.');else entry.appearance.icon=values.icon;}
   if(mapping.includes('label'))entry.appearance.label=values.label||'';
   if(roundtrip&&mapping.includes('appearance_fields')){const mask=Number(values.appearance_fields);if(!/^[0-3]$/.test(values.appearance_fields||''))entry.errors.push('Invalid appearance_fields flags.');else{if(!(mask&1))delete entry.appearance.icon;else if(!values.icon)entry.appearance.icon=null;if(!(mask&2))delete entry.appearance.label;}}
   if(values.marker_id){if(document.locations.some(m=>m.id===values.marker_id||m.extensions?.spreadsheet?.markerId===values.marker_id))entry.errors.push('marker_id conflicts with an existing marker. Existing markers will never be overwritten.');if(markerIds.get(values.marker_id)>1)entry.errors.push('marker_id is repeated in this paste.');if(!entry.errors.some(e=>e.includes('marker_id')))entry.warnings.push('marker_id is source metadata only; a new application ID will be generated.');}
   if(roundtrip&&values.group_id){entry.sourceGroupId=values.group_id;const groups=document.groups.filter(g=>g.extensions?.csvSourceId===values.group_id);if(groups.length>1)entry.errors.push('Ambiguous exported group reference.');else if(groups.length)entry.groupId=groups[0].id;else if(!values.group)entry.errors.push('Exported groups need a name.');else{entry.newGroup=values.group;entry.groupId=null;}}
   else if(values.group_id){const group=document.groups.find(g=>g.id===values.group_id);if(!group)entry.errors.push('Unknown group_id. Supply an existing stable ID or omit it to create a group by name.');else if(values.group&&norm(group.name)!==norm(values.group))entry.errors.push('group and group_id refer to different groups.');else entry.groupId=group.id;}
   else if(values.group){const groups=document.groups.filter(g=>norm(g.name)===norm(values.group));if(groups.length>1)entry.errors.push('Group name is ambiguous. Supply group_id.');else if(groups.length===1)entry.groupId=groups[0].id;else{entry.newGroup=values.group;entry.groupId=null;}}
   if(!entry.newGroup&&!document.groups.some(g=>g.id===entry.groupId))entry.errors.push('The default group no longer exists. Select a valid group.');
   entry.name=roundtrip?(values.name??''):values.name||entry.address||(entry.point?entry.point.lat+', '+entry.point.lng:'Row '+entry.row);
   const descriptionFormat=legacy?'html':values.description_format||'text';if(!['text','html'].includes(descriptionFormat))entry.errors.push('description_format must be text or html.');entry.description={format:descriptionFormat,value:values.description||''};if(descriptionFormat==='html'&&!legacyHtmlSupported(entry.description.value))entry.errors.push('Unsupported or unsafe HTML description. Review the original input; no markup has been stripped.');entry.sourceMarkerId=values.marker_id||null;
   if(entry.newGroup&&!entry.errors.length){const key=entry.sourceGroupId||norm(entry.newGroup);newGroups.set(key,newGroups.get(key)||entry.newGroup);}
   return entry;
  });
  if(!entries.length)errors.push('No data rows found.');
  return {entries,errors,newGroups:[...newGroups.values()],valid:entries.filter(e=>!e.errors.length).length};
 }
 const api={fields,heading,norm,parse,parseLegacy,legacyMapping,legacyHtmlSupported,detect,serialize,validate};if(typeof module!=='undefined')module.exports=api;else root.SpreadsheetInput=api;
})(typeof window==='undefined'?globalThis:window);
