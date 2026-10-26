(function(window,document){
 'use strict';
 var boot=JSON.parse(document.getElementById('local-membership-bootstrap').textContent);
 var user=boot.user,csrf=boot.csrf,current=boot.map;
 function notice(text){window.MultiplottrLocal.notice(text);}
 function encode(value){return '@local:'+encodeURIComponent(value||'');}
 function decode(value){if(!value)return '';try{return value.indexOf('@local:')===0?decodeURIComponent(value.slice(7)):value;}catch(e){return value;}}
 function identity(){
  var nextId=user?String(user.id):'';
  if(localStorage.localMembershipUserId!==nextId){['geoCodedAddresses','mapTitle','loadMap','thisUserPW','thisUserName'].forEach(function(k){localStorage.removeItem(k);});sessionStorage.removeItem('plottedData');}
  localStorage.localMembershipUserId=nextId;
  sessionStorage.loggedIn=user?'yes':'';sessionStorage.userName=user?user.username:"[REDACTED_CONFIG_VALUE]";sessionStorage.userEmail=user?user.username+'@example.invalid':'';
  localStorage.loggedIn='false';localStorage.removeItem('thisUserPW');
 }
 identity();
 async function request(path,method,data){
  var response=await fetch('/__local_membership/'+path,{method:method||'GET',credentials:'same-origin',cache:'no-store',headers:{'Content-Type':'application/json','X-Local-CSRF':csrf},body:data===undefined?undefined:JSON.stringify(data)});
  var body=await response.json();if(!response.ok){var error=new Error(body.error||'Local request failed');error.status=response.status;throw error;}return body;
 }
 function markersFromField(){
  return (document.getElementById('markerLocationField').value||'').split('\n').filter(Boolean).map(function(line){var f=line.split('|'),coords=f[0].replace(/^~/,'').split(',');return {lat:Number(coords[0]),lng:Number(coords[1]),description:decode(f[1]),color:f[2]||'RED',group:decode(f[3])||'group0'};});
 }
 function settingsFromMap(){
  if(window.map&&window.MultiplottrLocal.mapsAvailable()){
   var center=map.getCenter();return {center:{lat:center.lat(),lng:center.lng()},zoom:Math.round(map.getZoom()),minZoom:map.get('minZoom')??2,maxZoom:map.get('maxZoom')??19,zoomControl:map.get('zoomControl')!==false,mapTypeControl:map.get('mapTypeControl')===true,legendVisible:current?current.data.settings.legendVisible:true,mapTypeId:'roadmap'};
  }
  return current?current.data.settings:{center:{lat:0,lng:0},zoom:2,minZoom:2,maxZoom:19,zoomControl:true,mapTypeControl:false,legendVisible:true,mapTypeId:'roadmap'};
 }
 function applySettings(){
  if(!current)return;var s=current.data.settings;
  jQuery('#mapControl').prop('checked',!s.zoomControl);jQuery('#hideMapType').prop('checked',!s.mapTypeControl);jQuery('#minZoom').val(s.minZoom);jQuery('#maxZoom').val(s.maxZoom);
  jQuery('#legendItems').toggle(s.legendVisible);
  if(window.map&&window.MultiplottrLocal.mapsAvailable()){
   map.setOptions({minZoom:s.minZoom,maxZoom:s.maxZoom,zoomControl:s.zoomControl,mapTypeControl:s.mapTypeControl,mapTypeControlOptions:{mapTypeIds:[google.maps.MapTypeId.ROADMAP]},streetViewControl:false});map.setCenter(s.center);map.setZoom(s.zoom);
  }
 }
 function restore(record,render){
  current=record;var d=record.data;
  jQuery('#markerLocationField').val(d.markers.map(function(m){return '~'+m.lat+','+m.lng+'|'+encode(m.description)+'|'+m.color+'|'+encode(m.group);}).join('\n'));
  document.title=d.title+' | Multiplottr local';jQuery('#mapTitleField').val(d.title);jQuery('#mapTitle header').text(d.title);jQuery('#mapDescript').text(d.description);
  if(render&&window.MultiplottrLocal.mapsAvailable())window.clientMapLoad();applySettings();
 }
 function element(tag,props,text){var e=document.createElement(tag);Object.assign(e,props||{});if(text!==undefined)e.textContent=text;return e;}
 function input(parent,id,label,type,value){var l=element('label',{},label),e=element(type==='textarea'?'textarea':'input',{id:id});if(type!=='textarea')e.type=type||'text';if(type==='checkbox')e.checked=!!value;else e.value=value??'';if(type==='number')e.step='any';l.appendChild(e);parent.appendChild(l);return e;}
 var authPanel,savePanel,rows;
 function close(panel){panel.hidden=true;}
 function buildUI(){
  authPanel=element('section',{id:'localMembershipPanel',className:'local-membership-panel',hidden:true});authPanel.setAttribute('role','dialog');authPanel.setAttribute('aria-label','Local test membership');
  authPanel.appendChild(element('h2',{},'Local test membership'));
  var auth=element('form',{id:'localMembershipForm'});input(auth,'localUsername','Synthetic username (local_...)','text','');input(auth,'localPassword','Password (12+ characters)','password','');
  auth.appendChild(element('button',{type:'submit',id:'localSignIn'},'Sign in'));auth.appendChild(element('button',{type:'button',id:'localRegister'},'Create synthetic account'));auth.appendChild(element('button',{type:'button',id:'localAuthClose'},'Close'));authPanel.appendChild(auth);document.body.appendChild(authPanel);
  async function authenticate(mode){try{var result=await request(mode,'POST',{username:document.getElementById('localUsername').value,password:document.getElementById('localPassword').value});user=result.user;csrf=result.csrf;identity();location.assign('/');}catch(e){notice(e.message);}}
  auth.addEventListener('submit',function(e){e.preventDefault();authenticate('login');});document.getElementById('localRegister').onclick=function(){authenticate('register');};document.getElementById('localAuthClose').onclick=function(){close(authPanel);};
  savePanel=element('section',{id:'localSavePanel',className:'local-membership-panel',hidden:true});savePanel.setAttribute('role','dialog');savePanel.setAttribute('aria-label','Private local map');savePanel.appendChild(element('h2',{},'Private local map'));
  var form=element('form',{id:'localSaveForm'});input(form,'localMapTitle','Title','text','');input(form,'localMapDescription','Description','textarea','');
  rows=element('div',{id:'localMarkerRows'});form.appendChild(rows);var add=element('button',{type:'button',id:'localAddMarker'},'Add marker');add.onclick=function(){markerRow({lat:40,lng:-74,description:'',group:'group0',color:'RED'});};form.appendChild(add);
  var settings=element('fieldset',{id:'localSavedSettings'});settings.appendChild(element('legend',{},'Map settings'));
  input(settings,'localCenterLat','Center latitude','number',0);input(settings,'localCenterLng','Center longitude','number',0);input(settings,'localZoom','Zoom','number',2);input(settings,'localMinZoom','Minimum zoom','number',2);input(settings,'localMaxZoom','Maximum zoom','number',19);input(settings,'localZoomControl','Show zoom controls','checkbox',true);input(settings,'localTypeControl','Show road map type control','checkbox',false);input(settings,'localLegendVisible','Show legend','checkbox',true);form.appendChild(settings);
  form.appendChild(element('button',{type:'submit',id:'localSaveSubmit'},'Save private map'));var cancel=element('button',{type:'button',id:'localSaveClose'},'Close');cancel.onclick=function(){close(savePanel);};form.appendChild(cancel);savePanel.appendChild(form);document.body.appendChild(savePanel);
  form.addEventListener('submit',async function(e){e.preventDefault();try{
   var v=function(id){return document.getElementById(id).value;},c=function(id){return document.getElementById(id).checked;};
   var data={title:v('localMapTitle'),description:v('localMapDescription'),markers:[...rows.children].map(function(row){return {lat:Number(row.querySelector('.local-marker-lat').value),lng:Number(row.querySelector('.local-marker-lng').value),description:row.querySelector('.local-marker-note').value,group:row.querySelector('.local-marker-group').value,color:row.querySelector('.local-marker-color').value};}),settings:{center:{lat:Number(v('localCenterLat')),lng:Number(v('localCenterLng'))},zoom:Number(v('localZoom')),minZoom:Number(v('localMinZoom')),maxZoom:Number(v('localMaxZoom')),zoomControl:c('localZoomControl'),mapTypeControl:c('localTypeControl'),legendVisible:c('localLegendVisible')}};
   var saved=await request(current?'maps/'+current.id:'maps',current?'PUT':'POST',data);restore(saved,true);history.replaceState(null,'','/?map_id='+saved.id);close(savePanel);notice('Private local map saved.');
  }catch(e){notice(e.message);}});
 }
 function markerRow(m){
  var row=element('fieldset',{className:'local-marker-row'});row.appendChild(element('legend',{},'Marker '+(rows.children.length+1)));
  [['lat','Latitude','number',m.lat],['lng','Longitude','number',m.lng],['note','Description','textarea',m.description],['group','Group','text',m.group]].forEach(function(f){var e=input(row,'',f[1],f[2],f[3]);e.removeAttribute('id');e.className='local-marker-'+f[0];});
  var label=element('label',{},'Color'),select=element('select',{className:'local-marker-color'});['RED','GREEN','BLUE','ORANGE','YELLOW','GREY','LIME','CYAN','VIOLET','PURPLE','BLACK','WHITE','PINK'].forEach(function(color){select.appendChild(element('option',{value:color},color));});select.value=m.color;label.appendChild(select);row.appendChild(label);var remove=element('button',{type:'button'},'Remove marker');remove.onclick=function(){row.remove();};row.appendChild(remove);rows.appendChild(row);
 }
 function openSave(){if(!user){authPanel.hidden=false;return;}var d=current?{...current.data,markers:markersFromField(),settings:settingsFromMap()}:{title:jQuery('#mapTitleField').val()||'New local map',description:'',markers:markersFromField(),settings:settingsFromMap()};
  document.getElementById('localMapTitle').value=d.title;document.getElementById('localMapDescription').value=d.description;rows.textContent='';d.markers.forEach(markerRow);
  ['Zoom','MinZoom','MaxZoom'].forEach(function(key){document.getElementById('local'+key).value=d.settings[key[0].toLowerCase()+key.slice(1)];});document.getElementById('localCenterLat').value=d.settings.center.lat;document.getElementById('localCenterLng').value=d.settings.center.lng;document.getElementById('localZoomControl').checked=d.settings.zoomControl;document.getElementById('localTypeControl').checked=d.settings.mapTypeControl;document.getElementById('localLegendVisible').checked=d.settings.legendVisible;savePanel.hidden=false;
 }
 async function showMaps(){if(!user){authPanel.hidden=false;return;}try{var list=await request('maps'),table=document.querySelector('#myMapsBox table');table.textContent='';var header=element('tr');['Title','Actions'].forEach(function(t){header.appendChild(element('th',{},t));});table.appendChild(header);
  list.maps.forEach(function(m){var row=element('tr');row.dataset.localMapId=m.id;var title=element('td'),open=element('a',{href:'/?map_id='+m.id},m.title);open.className='local-open-map';title.appendChild(open);row.appendChild(title);var actions=element('td'),del=element('button',{type:'button',className:'local-delete-map'},'Delete');del.onclick=async function(){if(!confirm('Delete this private local map?'))return;try{await request('maps/'+m.id,'DELETE');if(current&&current.id===m.id){current=null;history.replaceState(null,'','/');jQuery('#markerLocationField').val('');jQuery('#mapTitleField').val('New local map');jQuery('#mapTitle header').text('New local map');jQuery('#mapDescript').text('');if(window.MultiplottrLocal.mapsAvailable())window.clientMapLoad();}await showMaps();notice('Private local map deleted.');}catch(e){notice(e.message);}};actions.appendChild(del);row.appendChild(actions);table.appendChild(row);});jQuery('#myMapsBox').show();
 }catch(e){notice(e.message);}}
 document.addEventListener('DOMContentLoaded',function(){buildUI();if(current)restore(current,false);if(user){jQuery('#subscriberStatus,#wpLogin').text('YES');jQuery('#saveMap,#cleanMap,#editOptions,#plotButtons,#cleaRNSave').show();} });
 window.addEventListener('pageshow',function(e){if(e.persisted)location.reload();});
 window.addEventListener('storage',function(e){if(e.key==='localMembershipUserId'&&e.newValue!==(user?String(user.id):''))location.replace('/');});
 window.addEventListener('local-maps-ready',function(){applySettings();});
 document.addEventListener('click',function(event){var target=event.target.closest&&event.target.closest('a,button,header');if(!target)return;
  if(target.matches('#loginRegister,#mobilLogin,#subscribeNow')){event.preventDefault();event.stopImmediatePropagation();authPanel.hidden=false;return;}
  if(target.matches('#saveMap,#miniSaveMap,#saveMapButton')){event.preventDefault();event.stopImmediatePropagation();openSave();return;}
  if(target.matches('#myMaps,#loadAnOldMap')){event.preventDefault();event.stopImmediatePropagation();showMaps();return;}
  if(target.matches('#logOut')){event.preventDefault();event.stopImmediatePropagation();request('logout','POST',{}).then(function(){user=null;current=null;identity();['geoCodedAddresses','mapTitle','loadMap'].forEach(function(k){localStorage.removeItem(k);});location.assign('/');}).catch(function(e){notice(e.message);});return;}
  if(target.matches('#clearYes'))current=null;
 },true);
 window.LocalMembership={encodeField:encode,decodeField:decode,isSignedIn:function(){return !!user;}};
}(window,document));
