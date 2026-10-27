(function(){
 'use strict';
 const source=document.getElementById('description'),editor=document.getElementById('descriptionEditor'),toolbar=document.getElementById('descriptionToolbar');
 let locked=false,globalLocked=false,protectedContent=false;
 const hint=document.createElement('p');hint.id='descriptionEditorHint';hint.hidden=true;editor.after(hint);
 function supported(html){if(!MapDocument.safeHtml(html))return false;const parsed=new DOMParser().parseFromString(html,'text/html');return [...parsed.body.querySelectorAll('*')].every(el=>[...el.attributes].every(a=>(el.tagName==='A'&&a.name==='href')||(el.tagName==='IMG'&&['src','alt'].includes(a.name)))&&(!(el.tagName==='IMG')||/^\/mapping\/assets\/[a-z0-9-]+\.svg$/i.test(el.getAttribute('src')||'')));}
 function readOnly(){locked=globalLocked||protectedContent;editor.contentEditable=locked?'false':'true';editor.setAttribute('aria-readonly',String(locked));toolbar.querySelectorAll('button').forEach(b=>b.disabled=locked);hint.hidden=!protectedContent;hint.textContent='Original description retained. Unsupported markup is read-only in this visual editor.';}
 function notice(text){document.getElementById('status').textContent=text;}
 function range(){const selection=window.getSelection();if(selection.rangeCount&&editor.contains(selection.getRangeAt(0).commonAncestorContainer))return selection.getRangeAt(0);editor.focus();const r=document.createRange();r.selectNodeContents(editor);r.collapse(false);selection.removeAllRanges();selection.addRange(r);return r;}
 function insert(node){const r=range(),last=node.nodeType===Node.DOCUMENT_FRAGMENT_NODE?node.lastChild:node;if(!last)return;r.deleteContents();r.insertNode(node);r.setStartAfter(last);r.collapse(true);const selection=getSelection();selection.removeAllRanges();selection.addRange(r);}
 function changed(){if(locked)return;source.value=editor.innerHTML;source.dispatchEvent(new Event('change',{bubbles:true}));}
 function command(format){if(locked)return;editor.focus();const r=range();
  if(['h2','p','ul','ol'].includes(format)){
   let top=r.commonAncestorContainer;while(top!==editor&&top.parentNode!==editor)top=top.parentNode;
   if(top!==editor&&top.parentNode===editor)r.selectNode(top);
  }
  if(format==='ul'||format==='ol'){
   const list=document.createElement(format),fragment=r.extractContents();
   if(fragment.childNodes.length===1&&fragment.firstChild.nodeType===Node.ELEMENT_NODE&&fragment.firstChild.matches('ul,ol'))list.append(...fragment.firstChild.childNodes);
   else {const li=document.createElement('li');li.append(fragment);if(!li.childNodes.length)li.append(document.createElement('br'));list.append(li);}
   r.insertNode(list);const caret=document.createRange();caret.selectNodeContents(list.lastChild);caret.collapse(false);getSelection().removeAllRanges();getSelection().addRange(caret);
  }else if(format==='h2'||format==='p'){
   const node=r.startContainer.nodeType===Node.ELEMENT_NODE?r.startContainer:r.startContainer.parentElement;
   const selected=r.startContainer===editor&&r.endContainer===editor&&r.endOffset===r.startOffset+1?editor.childNodes[r.startOffset]:null;
   const block=selected?.nodeType===Node.ELEMENT_NODE&&selected.matches('p,h1,h2,h3')?selected:node.closest('p,h1,h2,h3');
   if(block&&editor.contains(block)){const replacement=document.createElement(format);replacement.append(...block.childNodes);block.replaceWith(replacement);const caret=document.createRange();caret.selectNodeContents(replacement);caret.collapse(false);getSelection().removeAllRanges();getSelection().addRange(caret);}
   else {const el=document.createElement(format);el.append(r.extractContents());if(!el.childNodes.length)el.append(document.createElement('br'));r.insertNode(el);r.selectNodeContents(el);r.collapse(false);getSelection().removeAllRanges();getSelection().addRange(r);}
  }else {const el=document.createElement(format);el.append(r.extractContents());if(!el.childNodes.length)el.append(document.createElement('br'));r.insertNode(el);r.selectNodeContents(el);if(!el.textContent)r.collapse(true);getSelection().removeAllRanges();getSelection().addRange(r);}
  changed();
 }
 toolbar.addEventListener('mousedown',e=>e.preventDefault());
 toolbar.addEventListener('click',e=>{const button=e.target.closest('button[data-format]');if(button)command(button.dataset.format);});
 editor.addEventListener('input',changed);
 editor.addEventListener('keydown',e=>{if(locked)return;if((e.ctrlKey||e.metaKey)&&['b','i'].includes(e.key.toLowerCase())){e.preventDefault();command(e.key.toLowerCase()==='b'?'strong':'em');return;}if(e.key==='Enter'){e.preventDefault();const r=range(),node=r.startContainer.nodeType===Node.ELEMENT_NODE?r.startContainer:r.startContainer.parentElement,li=node.closest('li');if(li&&editor.contains(li)&&!e.shiftKey){const next=document.createElement('li');next.append(document.createElement('br'));li.after(next);r.selectNodeContents(next);r.collapse(true);getSelection().removeAllRanges();getSelection().addRange(r);}else insert(document.createElement('br'));changed();}});
 editor.addEventListener('click',e=>{if(e.target.closest('a'))e.preventDefault();});
 editor.addEventListener('paste',e=>{
  e.preventDefault();if(locked)return;const html=e.clipboardData.getData('text/html'),text=e.clipboardData.getData('text/plain');
  if(html){if(!supported(html)){notice('Pasted formatting or media is unsupported and was not inserted. Your map is unchanged. Paste plain text or use the formatting buttons.');return;}const parsed=new DOMParser().parseFromString(html,'text/html');const fragment=document.createDocumentFragment();fragment.append(...parsed.body.childNodes);insert(fragment);}
  else {const fragment=document.createDocumentFragment();text.split(/\r?\n/).forEach((line,i)=>{if(i)fragment.append(document.createElement('br'));fragment.append(document.createTextNode(line));});insert(fragment);}
  changed();
 });
 window.DescriptionEditor={
  render(content){const value=content?.value||'';protectedContent=content?.format==='html'&&!supported(value);readOnly();if(document.activeElement===editor&&source.value===editor.innerHTML)return;if(content?.format==='html'&&!protectedContent){if(editor.innerHTML!==value)editor.innerHTML=value;}else if(editor.textContent!==value)editor.textContent=value;},
  setReadOnly(value){globalLocked=value;readOnly();}
 };
})();
