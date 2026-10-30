// Schema checks load only the class definition; no server, database or credentials.
const cp=require('node:child_process'),path=require('node:path');
module.exports=function php(code){
 const service=path.resolve(__dirname,'../document-service.php').replace(/\\/g,'/').replace(/'/g,"\\'");
 const portable=code.replace("/app/mapping-dev/document-service.php",service);
 const result=cp.spawnSync(process.env.PHP_BINARY||'php',['-d','display_errors=stderr','-r',portable],{encoding:'utf8',timeout:30000});
 if(result.error)throw Error('PHP CLI 8.2+ is required: '+result.error.message);
 if(result.status!==0||result.stderr.trim())throw Error('PHP contract failed: '+result.stderr+result.stdout);
 return result.stdout;
};
