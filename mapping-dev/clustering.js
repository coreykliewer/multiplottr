(function(root){
 'use strict';
 // One clusterer owns the existing markers. Only the spatial index is rebuilt on changes.
 function create({map,onPick,onChange}){
  const library=root.markerClusterer;
  if(!library)throw new Error('Marker clustering library unavailable');
  let signature=null,active=false,delegate=null,invalid=true;
  const algorithm={calculate(input){const rebuild=invalid;if(rebuild){delegate=new library.SuperClusterAlgorithm({maxZoom:22,radius:60});invalid=false;}const result=delegate.calculate(input);return {...result,changed:rebuild||result.changed};}};
  const clusterer=new library.MarkerClusterer({algorithm,renderer:{render({count,position}){
   const size=count>=100?52:44;
   return new google.maps.Marker({position,title:count+' locations. Select to zoom or choose a location.',label:{text:String(count),color:'#fff',fontSize:'14px',fontWeight:'bold'},icon:{path:google.maps.SymbolPath.CIRCLE,scale:size/2,fillColor:'#2e81b7',fillOpacity:1,strokeColor:'#00acd1',strokeWeight:4},zIndex:google.maps.Marker.MAX_ZINDEX+count});
  }},onClusterClick:(_,cluster)=>{
   const markers=cluster.markers,first=markers[0].getPosition();
   const identical=markers.every(m=>m.getPosition().equals(first));
   const zoom=map.getZoom(),max=map.get('maxZoom')??22;
   if(identical||zoom>=max){onPick(markers,cluster.position);if(identical&&zoom<max){map.setCenter(cluster.position);map.setZoom(Math.min(max,zoom+2));}}
   else {map.fitBounds(cluster.bounds,48);if(map.getZoom()<=zoom)map.setZoom(Math.min(max,zoom+2));}
  }});
  clusterer.addListener('clusteringend',()=>onChange?.());
  return {
   clusterer,
   sync(markers,enabled){
    const next=JSON.stringify(markers.map(m=>[m.__locationId,m.getPosition().lat(),m.getPosition().lng()]));
    if(!enabled){if(active){clusterer.setMap(null);active=false;signature=null;invalid=true;}markers.forEach(m=>{if(m.getMap()!==map)m.setMap(map);});onChange?.();return;}
    if(next!==signature){clusterer.clearMarkers(true);clusterer.addMarkers(markers,true);invalid=true;signature=next;}
    if(!active){invalid=true;active=true;clusterer.setMap(map);}else clusterer.render();
   },
   destroy(){clusterer.setMap(null);clusterer.clearMarkers(true);google.maps.event.clearInstanceListeners(clusterer);active=false;signature=null;}
  };
 }
 root.MultiplottrClustering={create};
})(typeof window==='undefined'?globalThis:window);
