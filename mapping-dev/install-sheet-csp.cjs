const fs=require('fs'),path=require('path');
const filename=path.join(__dirname,'../local-test/router.php'),marker='// New mapping workspace public-sheet connection only.';
let text=fs.readFileSync(filename,'utf8');if(text.includes(marker)){text=text.replace("connect-src 'self' https://docs.google.com","connect-src 'self' http://127.0.0.1:8081");fs.writeFileSync(filename,text);}
if(!text.includes(marker)){
 const anchor="if ($path === '/mapping/' || strpos($path, '/mapping/') === 0) {";
 if(!text.includes(anchor))throw Error('New mapping route missing; scoped Sheet CSP not installed.');
 const addition=`\n    ${marker}\n    header('Content-Security-Policy: ' . str_replace("connect-src 'self'", "connect-src 'self' http://127.0.0.1:8081", local_maps_csp($localMaps['enabled'])));\n`;
 fs.writeFileSync(filename,text.replace(anchor,anchor+addition));
}
console.log('Scoped public-sheet CSP installed for /mapping/ only.');
