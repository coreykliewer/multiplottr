const P=require('./public-sheet');
function permittedRedirect(value){let u;try{u=new URL(value);}catch(_){return false;}return u.protocol==='https:'&&!u.username&&!u.password&&!u.port&&/^doc-[a-z0-9-]+-sheets\.googleusercontent\.com$/i.test(u.hostname)&&u.pathname.startsWith('/pub/');}
async function fetchPublished(value,options={}){
 let target=P.sourceURL(value);const controller=new AbortController(),abort=()=>controller.abort();options.signal?.addEventListener('abort',abort,{once:true});if(options.signal?.aborted)abort();const timer=setTimeout(abort,15000);
 try{for(let hop=0;hop<=2;hop++){
  const response=await(options.fetch||fetch)(target,{redirect:'manual',credentials:'omit',signal:controller.signal,headers:{Accept:'text/csv,text/plain'}});
  if([301,302,303,307,308].includes(response.status)){const location=response.headers.get('location');await response.body?.cancel();if(hop===2||!location)throw Error('Google exceeded the permitted redirect limit.');const next=new URL(location,target).href;if(!permittedRedirect(next))throw Error('Google redirected to an unsupported host or URL.');target=next;continue;}
  if(!response.ok)throw Error('Published sheet unavailable (HTTP '+response.status+'). Check the published tab and permissions.');
  if(!/^(text\/(csv|plain)|application\/(csv|octet-stream|binary))(;|$)/i.test(response.headers.get('content-type')||''))throw Error('Google returned a page instead of CSV. Publish the selected tab as CSV.');
  if(Number(response.headers.get('content-length'))>1048576){await response.body?.cancel();throw Error('Sheet exceeds 1 MB.');}
  const reader=response.body?.getReader();if(!reader)throw Error('Empty CSV response.');const chunks=[];let size=0;try{while(true){const {done,value}=await reader.read();if(done)break;size+=value.length;if(size>1048576){await reader.cancel();throw Error('Sheet exceeds 1 MB.');}chunks.push(Buffer.from(value));}}finally{reader.releaseLock();}
  const text=new TextDecoder('utf-8',{fatal:true}).decode(Buffer.concat(chunks));if(/^\s*<(?:!doctype|html)/i.test(text))throw Error('Google returned HTML rather than CSV.');return text;
 }throw Error('No CSV returned.');}finally{clearTimeout(timer);options.signal?.removeEventListener('abort',abort);}
}
module.exports={fetchPublished,permittedRedirect};
