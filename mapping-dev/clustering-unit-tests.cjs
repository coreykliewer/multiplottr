// Controller lifecycle doubles. Real Maps/vendor clustering is covered by the local browser suite.
const assert=require('node:assert/strict'),vm=require('node:vm'),fs=require('node:fs');
let builds=0,picks=0,changes=0;
class Algorithm {constructor(){builds++;}calculate(){return {clusters:[],changed:false};}}
class Clusterer {
 constructor(options){this.options=options;this.markers=[];this.listeners={};}
 addListener(name,fn){this.listeners[name]=fn;}
 clearMarkers(){this.markers=[];}
 addMarkers(markers){this.markers.push(...markers);}
 setMap(map){this.map=map;if(map)this.render();}
 render(){this.options.algorithm.calculate({});this.listeners.clusteringend?.();}
}
let zoom=3,center,fit=0;
const map={getZoom:()=>zoom,get:()=>22,setZoom:n=>zoom=n,setCenter:p=>center=p,fitBounds:()=>fit++};
const context={markerClusterer:{MarkerClusterer:Clusterer,SuperClusterAlgorithm:Algorithm},google:{maps:{Marker:Object.assign(function(options){Object.assign(this,options);},{MAX_ZINDEX:1000}),SymbolPath:{CIRCLE:'circle'},event:{clearInstanceListeners(){}}}}};
vm.runInNewContext(fs.readFileSync(require.resolve('./clustering.js'),'utf8'),context);
const controller=context.MultiplottrClustering.create({map,onPick:()=>picks++,onChange:()=>changes++});
function marker(id,lat=1,lng=2){return {__locationId:id,lat,lng,map:null,getPosition(){const m=this;return {lat:()=>m.lat,lng:()=>m.lng,equals:p=>p.lat()===m.lat&&p.lng()===m.lng};},getMap(){return this.map;},setMap(map){this.map=map;}};}
const markers=Array.from({length:105},(_,i)=>marker(String(i),1+i/100,2));
controller.sync(markers.slice(0,5),true);assert.equal(controller.clusterer.markers.length,5);assert.equal(builds,1);
controller.sync(markers.slice(0,5),true);assert.equal(builds,1);
controller.sync(markers,true);assert.equal(controller.clusterer.markers.length,105);assert.equal(builds,2);
assert.equal(controller.clusterer.markers[0],markers[0]); // shared Google marker instances
markers[0].lat=9;controller.sync(markers,true);assert.equal(builds,3); // same instance moved
controller.sync(markers.slice(1),true);assert.equal(controller.clusterer.markers.length,104);assert.equal(builds,4);
controller.sync(markers.slice(1),false);assert.equal(controller.clusterer.map,null);assert(markers.slice(1).every(m=>m.map===map));
controller.sync(markers.slice(1),true);assert.equal(controller.clusterer.markers.length,104);assert.equal(builds,5);
const duplicate=[marker('a'),marker('b')],position=duplicate[0].getPosition();
controller.clusterer.options.onClusterClick(null,{markers:duplicate,position});assert.equal(picks,1);assert.equal(center,position);
controller.clusterer.options.onClusterClick(null,{markers:markers.slice(0,5),bounds:{}});assert.equal(fit,1);
zoom=22;controller.clusterer.options.onClusterClick(null,{markers:markers.slice(0,5),position});assert.equal(picks,2);
const icon=controller.clusterer.options.renderer.render({count:105,position});assert.equal(icon.label.text,'105');assert.equal(icon.icon.scale,26);
controller.destroy();assert.equal(controller.clusterer.markers.length,0);assert.equal(controller.clusterer.map,null);assert(changes>0);
console.log('PASS: cluster controller 5/105-marker lifecycle, stable instances, movement, removal/hiding, toggle, duplicate/max-zoom chooser, count icon and teardown (doubles; no Google HTTP).');
