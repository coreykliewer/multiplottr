// All paths resolve from the checkout, never an ignored local harness or node_modules.
const fs=require('node:fs'),path=require('node:path'),cp=require('node:child_process');
const root=path.resolve(__dirname,'../..'),mapping=path.join(root,'mapping-dev');
if(Number(process.versions.node.split('.')[0])<22)throw Error('Install Node.js 22 or 24 before running these checks.');
const phpVersion=cp.spawnSync(process.env.PHP_BINARY||'php',['-r',"if(PHP_VERSION_ID<80200)exit(1);echo PHP_VERSION;"],{encoding:'utf8'});
if(phpVersion.error||phpVersion.status!==0)throw Error('Install PHP CLI 8.2+ and add php to PATH (or set PHP_BINARY). No extensions or database are needed.');
function run(command,args){const r=cp.spawnSync(command,args,{cwd:root,stdio:'inherit',timeout:60000});if(r.error)throw r.error;if(r.status!==0)process.exit(r.status||1);}
const suites=['tests.cjs','spreadsheet-tests.cjs','legacy-paste-tests.cjs','csv-roundtrip-tests.cjs','public-sheet-tests.cjs','public-sheet-fetch-tests.cjs','sheet-contract-tests.cjs','clustering-contract-tests.cjs','clustering-unit-tests.cjs','review-regression-tests.cjs','live-tooling-contract-tests.cjs'];
if(process.argv[2]==='syntax'){
 const files=fs.readdirSync(mapping,{recursive:true}).filter(p=>/\.(?:c?js)$/.test(p)&&!p.split(path.sep).includes('node_modules'));
 for(const file of files)run(process.execPath,['--check',path.join(mapping,file)]);
 run(process.env.PHP_BINARY||'php',['-l',path.join(mapping,'document-service.php')]);
 console.log(`PASS: ${files.length} JavaScript syntax checks and PHP syntax (PHP ${phpVersion.stdout}).`);
}else if(process.argv[2]==='test'){
 for(const suite of suites){console.log('\nRunning '+suite);run(process.execPath,[path.join(mapping,suite)]);}
 console.log(`\nPASS: all ${suites.length} credential-free suites. No live Google requests or local browser/Docker setup used.`);
}else throw Error('Expected syntax or test');
