// Controlled local fixtures only. No data downloaded from or written to Google Sheets.
const S=require('./spreadsheet-input');
const headers=['source_id','latitude','longitude','name','color','style','size','icon','label','group','description'];
const records=[
 [
  ['source-a','39.75','-104.99','Controlled A','RED','pin','normal','','Source label A','Sheet team A','Controlled source A'],
  ['source-b','39.7501','-104.9901','Controlled B','BLUE','number','small','','Source label B','Sheet team A','Controlled source B']
 ],
 [
  ['source-b','39.7501','-104.9901','Controlled B updated','GREEN','image','normal','/mapping/assets/star.svg','Star B','Sheet team A','Updated description B'],
  ['source-a','39.75','-104.99','Controlled A updated','#123abc','number','small','','Sheet "A", updated','Sheet team B','Updated description A']
 ],
 [
  ['source-a','39.75','-104.99','Controlled A final','ORANGE','image','normal','/mapping/assets/star.svg','','Sheet team B','Final description A'],
  ['source-b','39.7501','-104.9901','Controlled B final','BLUE','pin','small','','Pin B','Sheet team B','Final description B']
 ]
];
const versions=records.map(rows=>S.serialize([headers,...rows],','));
// A valid changed first row precedes every syntax/shape fault: partial application must not occur.
const prefix=S.serialize([headers,['source-a','39.75','-104.99','MUST NOT APPLY','PURPLE','pin','normal','','MUST NOT APPLY','Must not create group','MUST NOT APPLY']],',');
const malformed=[
 {name:'unclosed quoted field',body:prefix+'\nsource-b,39.7501,-104.9901,"Unclosed',contentType:'text/csv',error:/Unclosed quoted cell/i},
 {name:'text after closing quote',body:prefix+'\nsource-b,39.7501,-104.9901,"Closed"tail,BLUE,pin,normal,,B,Sheet team A,Description',contentType:'text/csv',error:/Unexpected text after a quoted cell/i},
 {name:'ragged data row',body:prefix+'\nsource-b,39.7501,-104.9901,Missing columns',contentType:'text/csv',error:/Expected 11 columns; found 4/i},
 {name:'invalid UTF-8',body:Buffer.concat([Buffer.from(prefix+'\n'),Buffer.from([0xff,0xfe])]),contentType:'text/csv',error:/encoded data|encoding|utf-8/i},
 {name:'HTTP 200 HTML response',body:'<!doctype html><html><body>Synthetic unavailable sheet</body></html>',contentType:'text/html',error:/page rather than CSV/i}
];
function appearance(version,id){const row=records[version].find(r=>r[0]===id);return {kind:row[5],color:row[4].toUpperCase(),size:row[6],icon:row[7]||null,label:row[8]};}
function groupName(version,id){return records[version].find(r=>r[0]===id)[9];}
module.exports={headers,records,versions,malformed,appearance,groupName};
