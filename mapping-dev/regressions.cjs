// Temporarily tighten Maps to offline for the existing suite; restore exact private bytes.
const fs=require('fs'),cp=require('child_process'),crypto=require('crypto');
const filename='local-test/config/maps.local.json',original=fs.readFileSync(filename),hash=b=>crypto.createHash('sha256').update(b).digest('hex');
try{
 const config=JSON.parse(original.toString('utf8').replace(/^\uFEFF/,''));config.enabled=false;config.allowBrowserNetwork=false;fs.writeFileSync(filename,JSON.stringify(config));
 cp.execFileSync('powershell.exe',['-NoProfile','-ExecutionPolicy','Bypass','-File','local-test/Test-Local.ps1'],{stdio:'inherit'});
}finally{fs.writeFileSync(filename,original);if(hash(fs.readFileSync(filename))!==hash(original))throw Error('Private configuration restoration failed');console.log('Private configuration restored byte-for-byte; key never printed.');}
