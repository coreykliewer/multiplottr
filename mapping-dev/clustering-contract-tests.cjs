const assert=require('assert'),fs=require('fs'),crypto=require('crypto'),cp=require('child_process'),M=require('./document');
const vendor='mapping-dev/assets/markerclusterer/';
assert.equal(require('./assets/markerclusterer/package.json').version,'2.6.2');
assert.equal(crypto.createHash('sha256').update(fs.readFileSync(vendor+'index.min.js')).digest('hex'),'e4261b900608bf4918c6f613041d40150cf86a302a96667f81421716d69682ff');
assert(fs.readFileSync(vendor+'LICENSE','utf8').includes('Apache'));
const cases=[];
for(const [label,value,valid] of [['on',true,true],['off',false,true],['missing',undefined,true],['string','true',false],['number',1,false],['null',null,false]]){
 const d=M.create();if(value===undefined)delete d.settings.clusterMarkers;else d.settings.clusterMarkers=value;
 const snapshot=JSON.stringify(d);assert.equal(M.inspect(d).errors.length===0,valid,label);assert.equal(JSON.stringify(d),snapshot);cases.push({label,document:d,valid});
}
assert.equal(M.create().settings.clusterMarkers,true);
assert.equal(M.fromLegacy('~1,2|Hello|RED|Locations').settings.clusterMarkers,false);
const old=M.create(),next=M.copy(old);next.settings.clusterMarkers=false;old.extensions={keep:{value:1}};next.extensions=M.copy(old.extensions);
const data=Buffer.from(JSON.stringify({cases,old,next})).toString('base64');
const php=`require '/app/mapping-dev/document-service.php';$data=json_decode(base64_decode('${data}'));$results=[];foreach($data->cases as $case){list($errors,$readonly)=MappingDocuments::inspect($case->document);$results[]=['label'=>$case->label,'valid'=>count($errors)===0];}$method=new ReflectionMethod('MappingDocuments','extras');$method->setAccessible(true);$toggle=$method->invoke(null,$data->old,$data->next);unset($data->next->extensions->keep);$dropsUnknown=$method->invoke(null,$data->old,$data->next);echo json_encode(['results'=>$results,'toggle'=>$toggle,'dropsUnknown'=>$dropsUnknown]);`;
const server=JSON.parse(cp.execFileSync('docker',['exec','multiplottr-local-web-1','php','-r',php],{encoding:'utf8'}));
assert.deepEqual(server.results,cases.map(c=>({label:c.label,valid:c.valid})));assert.equal(server.toggle,true);assert.equal(server.dropsUnknown,false);
console.log('PASS: pinned official library integrity; new/legacy defaults; optional setting validation in JS/PHP; toggles allowed and unknown data preserved.');
