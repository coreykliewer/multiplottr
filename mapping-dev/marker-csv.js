(function(root){
 'use strict';
 const headers=['multiplottr_csv_version','address','address_is_null','latitude','longitude','name','description','description_format','appearance_fields','color','style','size','icon','label','group','group_id','marker_id'];
 const textFields=new Set(['address','name','description','description_format','color','style','size','icon','label','group','group_id','marker_id']);
 function exportMap(doc){
  const S=typeof module!=='undefined'&&module.exports?require('./spreadsheet-input'):root.SpreadsheetInput,warnings=[];
  const rows=doc.locations.map(m=>{const g=doc.groups.find(g=>g.id===m.groupId),a=m.appearance||{};
   if(!Number.isFinite(m.coordinates?.lat)||!Number.isFinite(m.coordinates?.lng)||Math.abs(m.coordinates.lat)>90||Math.abs(m.coordinates.lng)>180||!['pin','number','image'].includes(a.kind)||!['normal','small'].includes(a.size))warnings.push('Marker '+m.id+': unsupported coordinates/appearance require review on reimport.');
   if(!g||!g.name)warnings.push('Marker '+m.id+': missing/empty group name cannot be reconstructed faithfully.');
   if(!['text','html'].includes(m.description?.format)||(m.description?.format==='html'&&!S.legacyHtmlSupported(m.description.value)))warnings.push('Marker '+m.id+': unsupported description will require review on import.');
   if(a.kind==='image'&&!/^\/mapping\/assets\/[a-z0-9-]+\.svg$/i.test(a.icon||''))warnings.push('Marker '+m.id+': external/unsupported image will require review.');
   if(Object.keys(m.description||{}).some(k=>!['format','value'].includes(k))||Object.keys(m.coordinates||{}).some(k=>!['lat','lng'].includes(k)))warnings.push('Marker '+m.id+': additional description/coordinate metadata is outside CSV scope.');
   if(Object.keys(a).some(k=>!['kind','color','size','icon','label'].includes(k))||Object.keys(m).some(k=>!['id','name','originalAddress','coordinates','description','appearance','groupId','extensions','sheetSource'].includes(k))||Object.keys(m.extensions||{}).length||m.sheetSource)warnings.push('Marker '+m.id+': extension/source metadata is not part of CSV marker interchange.');
   const values={multiplottr_csv_version:'1',address:m.originalAddress??'',address_is_null:m.originalAddress===null?'true':'false',latitude:String(m.coordinates.lat),longitude:String(m.coordinates.lng),name:m.name,description:m.description?.value??'',description_format:m.description?.format||'text',appearance_fields:String((Object.prototype.hasOwnProperty.call(a,'icon')?1:0)+(Object.prototype.hasOwnProperty.call(a,'label')?2:0)),color:a.color,style:a.kind,size:a.size,icon:a.icon??'',label:a.label??'',group:g?.name??'',group_id:m.groupId,marker_id:m.id};
   // Prefix every text value, including existing apostrophes; only versioned imports
   // decode exactly one prefix. No formula-capable cell is emitted as executable text.
   return headers.map(h=>textFields.has(h)?"'"+String(values[h]??''):values[h]);
  });
  const text='\uFEFF'+S.serialize([headers,...rows],',')+'\r\n';if(new TextEncoder().encode(text).length>1048576)warnings.push('CSV exceeds the 1 MB reimport limit; split it before importing.');return {text,warnings:[...new Set(warnings)]};
 }
 const api={headers,textFields,exportMap};if(typeof module!=='undefined'&&module.exports)module.exports=api;else root.MarkerCSV=api;
})(typeof window==='undefined'?globalThis:window);
