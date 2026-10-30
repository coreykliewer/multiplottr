const assert=require('node:assert/strict'),M=require('./document'),P=require('./public-sheet');
const php=require('./test-support/php.cjs');
(async()=>{
 const source=P.sourceFor('source_id,lat,lng,name,color\na,1,2,Before,RED');
 const connection={id:M.id(),url:P.sourceURL('https://docs.google.com/spreadsheets/d/e/2PACX_SYNTHETIC_REVIEW_12345678901234567890/pub'),headers:source.rows[0].cells,mapping:source.mapping,intervalMinutes:0,defaults:{appearance:{kind:'pin',color:'BLUE',size:'normal'},groupId:'default'},lastSuccess:null};
 const before=(await P.reconcile(M.create(),source,connection)).document;
 for(const key of ['appearance','coordinates','description','sheetSource'])before.locations[0][key].future={nested:['preserve',1]};
 const snapshot=JSON.stringify(before),after=(await P.reconcile(before,P.sourceFor('source_id,lat,lng,name,color\na,3,4,After,GREEN'),before.sheetConnection)).document;
 assert.equal(JSON.stringify(before),snapshot);assert.equal(after.locations[0].id,before.locations[0].id);
 assert.equal(after.locations[0].name,'After');assert.equal(after.locations[0].appearance.color,'GREEN');
 for(const key of ['appearance','coordinates','description','sheetSource'])assert.deepEqual(after.locations[0][key].future,before.locations[0][key].future,key+' extension retained');
 const encoded=Buffer.from(JSON.stringify([before,after])).toString('base64');
 assert.equal(php(`require '/app/mapping-dev/document-service.php';$d=json_decode(base64_decode('${encoded}'));$m=new ReflectionMethod('MappingDocuments','extras');$m->setAccessible(true);echo json_encode($m->invoke(null,$d[0],$d[1]));`).trim(),'true');
 const malformed=[];
 const add=(label,change)=>{const d=M.create();change(d);malformed.push({label,document:d});};
 for(const value of [null,1,'invalid',[],{lastCustomColor:[]}])add('plotDefaults '+JSON.stringify(value),d=>d.settings.plotDefaults=value);
 for(const value of [null,1,'invalid',[]])add('settings '+JSON.stringify(value),d=>d.settings=value);
 for(const value of [1,'invalid',[],{id:[],headers:'bad',mapping:3}])add('sheetConnection '+JSON.stringify(value),d=>d.sheetConnection=value);
 add('array identity',d=>d.id=[]);add('null location',d=>d.locations=[null]);
 add('invalid source identity',d=>{d.locations=M.copy(before.locations);d.sheetConnection=M.copy(before.sheetConnection);d.locations[0].sheetSource.sourceId=[];});
 for(const {label,document} of malformed){const inspected=M.inspect(document);assert(inspected.errors.length||inspected.readonly.length,label);}
 const payload=Buffer.from(JSON.stringify(malformed)).toString('base64');
 const results=JSON.parse(php(`require '/app/mapping-dev/document-service.php';set_error_handler(function($n,$s){throw new ErrorException($s,0,$n);});$out=[];foreach(json_decode(base64_decode('${payload}')) as $case){list($e,$ro)=MappingDocuments::inspect($case->document);$out[]=['label'=>$case->label,'rejected'=>count($e)+count($ro)>0];}echo json_encode($out);`));
 for(const r of results)assert(r.rejected,r.label);
 console.log('PASS: source updates preserve nested forward-compatible fields and remain saveable; '+malformed.length+' malformed document cases return validation results without exceptions.');
})().catch(e=>{console.error(e);process.exitCode=1;});
