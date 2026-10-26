 // cleaned with http://jshint.com/
 
 var dataArray =[];
  var dataArrayCount = 0;
  var fileName;
  var src;
  var currentGroupName='group0';
  var resizedBatch = false;
  var geoCodeListArray=[];
  var addressLineCount;
  var totalAddressLineCount;
  var mapID;
  var currentMap="";
  var mapName;
  
var geocoder = null;

var loadLocalStorage = true;
var submitContinue = false;
var sEmail='';




	


//This function looks to see if fields are populated and ads those unique fields to the map data - like the Title from the header
function lookForItems() {
	var lines = $('#markerLocationField').val().split("\n");
    var markerData = [];
	var markerLocationData = '';
	for (var ee=0; ee < lines.length; ee++) {
		var n=lines[ee].indexOf('|TITLE|');
		if (n<0) {markerLocationData = markerLocationData+lines[ee]+'\n';} else {markerLocationData = markerLocationData+'\n';}
	}
	if ($('#mapTitle header').text()!=='') {
		submitContinue=true;
	} else {
		var myDate = new Date();
		var displayDate = (myDate.getMonth()+1) + '/' + (myDate.getDate()) + '/' + myDate.getFullYear();
		submitContinue=true;
	}
}


//print map
function gmapPrint() {
	window.print();
	//$('#map_canvas').printArea();
    //var content = window.document.getElementById("map_canvas"); // get you map details
    //var newWindow = window.open(); // open a new window
    //newWindow.document.write(content.innerHTML); // write the map into the new window
    //newWindow.print(); // print the new window
}
   

function runSubmit(i) {
	//alert(i);
	if (i===null) {lookForItems();} else {submitContinue=true;}
	
	
	var theseGeoCodedAddresses = $('#markerLocationField').val();
	//var addresses = $('#batchAddressesField').text();
	//alert(theseGeoCodedAddresses);
	//7-16-2014 added: theseGeoCodedAddresses = theseGeoCodedAddresses.replace(/(['"])/g, "\\$1");
	//theseGeoCodedAddresses = theseGeoCodedAddresses.replace(/(['"])/g, "\\$1");
	
	
	var addressLines = theseGeoCodedAddresses.split("\n");
	
	//addressLines.filter(function(e){return e});
	if (addressLines[0]==="") {addressLines.splice(0,1);}
	theseGeoCodedAddresses=addressLines.join("\n");
	theseGeoCodedAddresses = theseGeoCodedAddresses.replace(/((\r\n|\r|\n)\s*)\2/g, "$2") ;
	$('#markerLocationField').val(theseGeoCodedAddresses);
	$('#mapLegend #mainButtonContainer #cleaRNSave').show();
	localStorage.geoCodedAddresses = $('#markerLocationField').val();
	localStorage.mapTitle = $('#mapTitleField').val();

	//
	$('#markerCount').val('0');
	$('#mapTitleFieldSubmit').val($('#mapTitleField').val());
if (submitContinue) { $('#mapTool').submit(); }
}

function geoCodeList(address, description, markertype, groupname, orderNumber, linesLength) {
	var thisLattyLongy;
	var geoCoded = [];
	var notPlotted = $('#batchAddressesField').val();
	var plottedAddresses = $('#markerLocationField').val();
	//alert('geoCodeListArray.length: '+geoCodeListArray.length+' linesLength: '+linesLength);
	//alert(address);
	geocoder.geocode(  { 'address': address}, function(results, status) {
	
		
	function filterTheseAddresses() {
    var tilda = function(word) {return word[0] == '~';};
		for (i=0; i<geoCodeListArray.length; i++) {
			{geoCoded.push(geoCodeListArray[i]+'\n');}
			var filteredGeoCoded = geoCoded.filter(tilda);
			if (filteredGeoCoded.length==geoCodeListArray.length) {
				for (i=0; i<filteredGeoCoded.length; i++) {	
					if (filteredGeoCoded[i].substring(0,3)=='~NO') {
						if (filteredGeoCoded.length==linesLength) {notPlotted = notPlotted+filteredGeoCoded[i];}
					} else {
						if (filteredGeoCoded.length==linesLength) {plottedAddresses = plottedAddresses+filteredGeoCoded[i];}
					}
				}
				$('#markerLocationField').val(plottedAddresses);
				$('#mapLegend #mainButtonContainer #cleaRNSave').show();
				$('#batchAddressesField').val(notPlotted);	
				$('#plotTheseAddresses').trigger('click'); 

				
			}
		}
	}
		
				if (status == google.maps.GeocoderStatus.OK) {
					thisLattyLongy = results[0].geometry.location.lat()+', '+results[0].geometry.location.lng();
					
					
					geoCodeListArray[orderNumber]='~'+thisLattyLongy+'|'+description+'|'+markertype+'|'+groupname;
					//alert('geoCodeListArray['+orderNumber+']: '+geoCodeListArray[orderNumber]);

					if (linesLength==geoCodeListArray.length) {
						//alert(linesLength+'|'+geoCodeListArray.length);
						filterTheseAddresses();
						localStorage.loadMap='false';
						runSubmit();
					}



          } else if (status == 'ZERO_RESULTS') {
					geoCodeListArray[orderNumber]='~NO LOCATION FOUND|'+description+'|'+markertype+'|'+groupname;
					
					if (orderNumber+1==geoCodeListArray.length) {
						//alert('google maps Zero- Filter');
						filterTheseAddresses();
						localStorage.loadMap='false';
						//runSubmit();
						$('#progressContainer, #modalBack').hide();
					}
					
				} else {
					alert('Unsuccessful: '+status);
        }
    });
	
}
        
function timedGeocoding(val, ii, linesLength) {
				$('#batchAddressesField').val('');
    setTimeout ( function () {
			fields = val.split('|');
			
      if (fields[1]===undefined) {fields[1]=fields[0];}
			if (fields[1]==='') {fields[1]=fields[0];}
			if (fields[2]===undefined) {fields[2]=$('#markerName').val();}
			if (fields[3]===undefined) {fields[3]=$('#currentGroupName').val();}
			
			$('#progressContainer, #modalBack').show();
			{
				addressLineCount = addressLineCount-1;
				var progress = addressLineCount/linesLength;
				progress = 100-(progress*100);
				$( "#progressbar" ).progressbar({value: progress});
				geoCodeListArray.push(val);
				$('#numOfAddresses').html(addressLineCount);
				geoCodeList(fields[0], fields[1], fields[2], fields[3], ii, linesLength);
			}
			
			
			}, i*1000);
			$('#progressContainer, #modalBack').hide();
				
}	
		
  
function batchGeocode(addresses) {
	
    var newAddresses;
	
	geoCodeListArray = [];
	
	if (!resizedBatch) {
		resizedBatch=true;
		$('#batchAddresses section').animate({bottom:'+=150'},500);
		$('#mapTool section').animate({top:'-=150'},500);
	}
	
	if (addresses!=='') {
		//newAddresses=addresses.replace(/'/g, "\\'");
		newAddresses=addresses.replace(/\r/g,'`');
		newAddresses=newAddresses.replace(/\n/g,'`');
        newAddresses=newAddresses.replace(/\t/g,' ');
		newAddresses=newAddresses.replace(/\| /g,'|');
		newAddresses=newAddresses.replace(/\" /g,'"');
		newAddresses=newAddresses.replace(/ \"/g,'"');
		newAddresses=newAddresses.replace(/> /g,'>');
		newAddresses=newAddresses.replace(/ </g,'<');
		var lines = newAddresses.split('`');
		var fields = [];
		
		//alert('lines.length: '+lines);
		lines = lines.filter(function(e){return e;});
		//alert('lines.length: '+lines);
		addressLineCount = lines.length;
		$('#numOfAddresses').html(addressLineCount);
		for (i=0; i<lines.length; i++) {
			timedGeocoding(lines[i], i, addressLineCount);
		}
		
	} else {runSubmit();} 
}


  
function deleteThisMap() {
	$('#alertBox, #modalBack').show();
	$('#alertYes, #alertNo').show();
	$('#alertOk').hide();
	$('#alertBox span').html('Delete <br>'+mapName+'?');
	$('#alertYes').addClass('yesDelete');
}

  
$(document).ready(function(){
	
$('.yesDelete').live('click', function() {
	$('#alertBox span').html('');
	$('#alertBox span').removeClass();
	$('#alertBox, #modalBack').hide();
	$('#alertYes').removeClass('yesDelete');
	$('#'+currentMap).submit();
});

$('#alertNo').live('click', function() {
	$('#alertBox span').html('');
	$('#alertBox span').removeClass();
	$('#alertBox, #modalBack').hide();
	$('#alertYes').removeClass('yesDelete');
});


	
	
$("#progressbar").progressbar({value: 0});
$("#loginRegisterBox, #plotLocation").hide();
$('#currentGroupName').val(currentGroupName);
$('#currentGroupNameDisplay').text(currentGroupName);
$('#markerName').val('RED');
$('#markerList li img[alt=RED]').addClass('selected');
//$('#markerSelection').toggle();
$('#updateMarker, #submitMarker, #mrkrGroups, #addNewMarkerURL').hide();
$('#quickSearch textarea').autosize({append: "\n"});
$("#advancedSettingsButton .contract").hide();
var src = $('#markerSelection section ul li .selected').attr('src');
$('.currentMarker').html('<img src='+src+' alt="Current Marker">');

$('#markerCount').val('0');

/*(function() {
if (typeof scrollY === 'undefined') return;
function s() { if (!scrollY) scroll(0, 0); }
addEventListener('load', s);
addEventListener('orientationchange', s);
})();
*/


if ($('#mapTitle header').text()==='') {$('#mapTitle #expandClose').hide();}



// DESCRIPTION: Shows introText box if the user has not closed it during this session.
if ( ((sessionStorage.hideIntro)!=='yes')||((sessionStorage.hideIntro)===null) ) {/*$('#introText').show();*/}

if ((sessionStorage.loggingIn)!=='') {
	//alert(sessionStorage.loggingIn); 
	
	if ((sessionStorage.loggingIn)=='registered') {
		$('#alertBox span').html('You are now registered. Please login.');
		$('#alertBox, #modalBack').show();
		$('#alertYes, #alertNo').hide();
		sessionStorage.loggingIn='';
		$('#loginRegisterBox').show();
		} else {
	
	//$('#loginRegisterBox').show();
	//$('#registerBox').show();
	$('#loginRegister .expand').hide();
	$('#loginRegister .contract').show();
	$('#alertBox span').html(sessionStorage.loggingIn);
	//$('#alertBox, #modalBack').show();
	//$('#alertYes, #alertNo').hide();
	sessionStorage.loggingIn=''; }
}


if ( (sessionStorage.loggedIn)=='yes' ) { 

	$('#loginRegister').hide();
	$('#loggedIn').show();
	$('#editOptions').show();
	$('#loginRegisterBox').hide(); 
	$('#loginRegister div').removeClass('contract'); 
	$('#loginRegister div').addClass('expand');
	$('#loggedIn span').text(sessionStorage.userName);
	$('#userName span').text(sessionStorage.userName);
	$('#changeEmail').text(sessionStorage.userEmail);
	$('#mobilLogin').removeClass('icon-user-outline'); 
	$('#mobilLogin').addClass('icon-user'); 
	
	
}




$('#markerSelection .tip').live('click', function(){ 
	$('#markerSelection section').toggle();
	if ($('#markerSelection section').is(':hidden')) { 
		$('#markerSelection .tip span').html('<div data-icon="y" class="expand">');
		if ($('#markerSelection').css('bottom') == '5px' ){
			$('#markerSelection').css({'top': 'auto'});
		}
	} else 
	{
		$('#markerSelection .tip span').html('<div data-icon="w" class="contract"></div>');
		if ($('#markerSelection').css('bottom') == '5px' ){
			$('#markerSelection').css({'top': '70px'});
		}
		
	}
 		
		
	});






$('#alertOk').on('click', function() {
	$('#alertBox span').html('');
	$('#alertBox span').removeClass();
	$('#alertBox, #modalBack').hide();
	
});


$('#printMap').on('click', function() {
	
	var contents = window.opener.document.getElementById("map_canvas"); 
	document.write(contents.innerHTML); 
	window.print();
});




//if ( ((sessionStorage.plottedData)!=='') || ((sessionStorage.plottedData)===null) ) {
//		$('#markerLocationField').val(sessionStorage.plottedData);
//		sessionStorage.plottedData='';
//		runSubmit();
//}
	

// DESCRIPTION: looks for extra values in URL
if ((url('?location')!==null)) {$('#introText').hide(); $('#quickSearch').hide(); $('#rssFeed').hide(); }
if ((url('?map_id')===null)) {$('#embedMap').hide(); $('#shareMap').hide();} else {
	$('#introText').hide();  
	$('#map_id').val(url('?map_id')); 
	$('#quickSearch').hide(); 
	$('#rssFeed').hide(); 
	document.title = 'Multiplottr: '+$('#mapTitle header').text(); 
} 
if (url('?mapControl')=='true') {$('#mapControl').prop('checked', true); } else {$('#mapControl').prop('checked', false);}
if (url('?mapControlSize')=='small') {$("#mapControlSize").val('small');    } else {$("#mapControlSize").val('large');}
if (url('?mapType')=='true') {  $('#hideMapType').prop('checked', true);} else {$('#hideMapType').prop('checked', false);}
if (url('?mapOverview')=='true') {$('#showMapOverview').prop('checked', true);} else {$('#showMapOverview').prop('checked', false);}
if (url('?showMapLegend')=='false') {$('#showMapLegend').prop('checked', false);} else {$('#showMapLegend').prop('checked', true);}
if (url('?showMapGroup')=='false') {$('#showMapGroup').prop('checked', false);} else {$('#showMapGroup').prop('checked', true);}
if ((url('?mapWidth')===null)||(url('?mapWidth')=='0')) {
	$('#mapWidth').val('100%');
} else {
	var mapWidthUrl = decodeURIComponent(url('?mapWidth'));
	$('#mapWidth').val(mapWidthUrl);
}
if ((url('?mapHeight')===null)||(url('?mapHeight')=='0')) {
	$('#mapHeight').val('100%');
} else {
	var mapHeightUrl = decodeURIComponent(url('?mapHeight'));
	$('#mapHeight').val(mapHeightUrl);
}

var prevMaxZoom=url('?maxZoom');
var prevMinZoom=url('?minZoom');
var prevMapCustomURL=url('?mapCustomURL');


if ((prevMaxZoom<=0)||(prevMaxZoom>20)) {$('#maxZoom').val('19');} else {$('#maxZoom').val(url('?maxZoom')); prevMaxZoom=url('?maxZoom');}
if ((prevMinZoom<=0)||(prevMinZoom>20)) {$('#minZoom').val('2');} else {$('#minZoom').val(url('?minZoom')); prevMinZoom=url('?minZoom');}


if ((url('?mapDatabase')!==null)) {$('#mapDatabaseURL').val(url('?mapDatabase')); loadLocalStorage = false; $("#overMapAdContainer").hide();}
if (url('?mapCustom')=='true') {$('#mapCustom').prop('checked', true);} else {$('#mapCustom').prop('checked', false);}
if (url('?mapCustomURL')!==null) {$('#introText').hide(); $('#quickSearch').hide(); } 

if ((url('?embed')=='true')||(window.location.href.indexOf("index_embed") > -1)) {
	$('#introText, #myMapsBox, #userAccountBox, #popOutWrapper, #mapLegend, #markerSelection, #contactUsBox, #topBannerNav, #mapSettings, #quickSearch, #overMapAdContainer, #blockedAd').hide();
$("#mapContainer").css( {left:0, top:0, bottom: 0, right:0 });	
} 

$('#customMapURL').val(url('?mapCustomURL'));

/*Look at local storage for placement of draggable divs
var coords;
var elementLeft;
var elementTop;
if  (sessionStorage.markerSelection) {
	coords = localStorage.markerSelection.split(",");
	elementLeft = parseInt(coords[1]);
	elementTop = parseInt(coords[0]);
	$("#markerSelection").css( {left:elementLeft, top:elementTop });

}*/

/*if  (sessionStorage.introText) {
	coords = localStorage.introText.split(",");
	elementLeft = parseInt(coords[1]);
	elementTop = parseInt(coords[0]);
	$("#introText").css( {left:elementLeft, top:elementTop });

}
*/

/*if  (sessionStorage.myMapsBox) {
	coords = localStorage.myMapsBox.split(",");
	elementLeft = parseInt(coords[1]);
	elementTop = parseInt(coords[0]);
	$("#myMapsBox").css( {left:elementLeft, top:elementTop });

}*/

if ($('#loginRegister').is(':visible')) {$(".editDelete").hide(); }
	

//if ($('#markerLocationField').val()==='') {
//	$('#markerLocationField').attr('placeholder', 'Multiplottr formatted data.');
//	}	



    $('#groupControlDiv:empty').parent().hide();



/*Saves position of draggable divs to localStorage */

$("#markerSelection").draggable({
    stop: function(event, ui) {
		var pos = $('#markerSelection').position();
		sessionStorage.markerSelection = pos.top+','+pos.left;
    }
});

$("#introText").draggable({
    stop: function(event, ui) {
		var pos = $('#introText').position();
		sessionStorage.introText = pos.top+','+pos.left;
    }
});

$("#myMapsBox").draggable({
    stop: function(event, ui) {
		var pos = $('#myMapsBox').position();
		sessionStorage.myMapsBox = pos.top+','+pos.left;
    }
});


$(function() { 
	$('#introText, #myMapsBox, #contactUsBox, #markerSelection').draggable({ handle: "h1", containment: "#mapContainer" });
});

$(document).mouseup(function (e)
{
    var container = $('#overMapAdContainer');

    if (container.has(e.target).length === 0)
    {
			container.hide();
    }
});








$('#introText h1, #myMapsBox h1, #contactUsBox h1').on('mousedown', function(){ 
  $("#introText, #myMapsBox, #contactUsBox").removeClass("infront");
	$(this).parent().addClass("infront");    
});

$('#downloadKML').live('click', function() {$( "#kmlform" ).submit();});
	
	$('#advancedSettingsButton').live('click', function() {
	
		if ($('#advancedMapSettings li').is(':visible')) {
			$("#advancedSettingsButton .expand").show();
			$("#advancedSettingsButton .contract").hide();
			$("#advancedMapSettings").slideUp("fast");
			$('#mapSettings .buttonContainer').show();
		} else {
			$("#advancedMapSettings").slideDown("fast");
			$("#advancedSettingsButton .expand").hide();
			$("#advancedSettingsButton .contract").show();
			$('#mapSettings .buttonContainer').hide();
			$('#urlParam').val(window.location.href);
		}
		
	
	});
	
	
	

	
	
	
	
	
//Mapsettings

	var thisUrl = window.location.href;
	var urlParamObj = $('#urlParam');

	function getParam() {
		var n;
		var myUrl = urlParamObj.val();
		var lastFour = myUrl.substr(myUrl.length - 4);
		if (lastFour == 'com/') {n='?';} else {n='&';}
		return n;
	}
	
function hideMobile() {
		if (matchMedia('only screen and (max-width: 600px)').matches) {
			$('#mapLegend').hide();
		}
	}
	
	$('#mapControl').on('click', function(){ 
		
		if(this.checked === true) {
			var param = getParam();
			thisUrl=thisUrl+param+'mapControl=true';
		} else {
    urlParamObj.val(thisUrl);
    thisUrl=thisUrl.replace('?mapControl=true','').replace('&mapControl=true',''); 
		}
		urlParamObj.val(thisUrl);
	});
	
	
	
	$('#hideMapType').on('click', function(){ 
		if(this.checked === true) {
			var param = getParam();
			thisUrl=thisUrl+param+'mapType=true';
		} else {
			urlParamObj.val(thisUrl);
			thisUrl=thisUrl.replace('?mapType=true','').replace('&mapType=true',''); 
		}
		urlParamObj.val(thisUrl);
	});
	
	
	$('#showMapOverview').on('click', function(){ 
		
		if(this.checked === true) {
    		var param = getParam();
   			thisUrl=thisUrl+param+'mapOverview=true';
		} else {
    		urlParamObj.val(thisUrl);
        	thisUrl=thisUrl.replace('?mapOverview=true','').replace('&mapOverview=true',''); 
		}
		urlParamObj.val(thisUrl);
	});
	
	
	
	$('#mapControlSize').change(function(){ 
	if($('#mapControlSize').val()==='small') {
    		var param = getParam();
   			thisUrl=thisUrl+param+'mapControlSize='+$('#mapControlSize').val();
		} else {
    		urlParamObj.val(thisUrl);
        	thisUrl=thisUrl.replace('?mapControlSize=small','').replace('&mapControlSize=small',''); 
		}
		urlParamObj.val(thisUrl);	
	});
	
	

	
	$('#maxZoom').change(function(){ 
	if (!($('#maxZoom').val()>=0)||($('#maxZoom').val()<20)) {
			urlParamObj.val(thisUrl);
        	thisUrl=thisUrl.replace('?maxZoom='+prevMaxZoom,'').replace('&maxZoom='+prevMaxZoom,'');
			if (($('#maxZoom').val()!==19)) {
    			var param = getParam();
   				thisUrl=thisUrl+param+'maxZoom='+$('#maxZoom').val();
			}
		} 
		urlParamObj.val(thisUrl);	
	});
	
	$('#minZoom').change(function(){ 
	if (!($('#minZoom').val()>=0)||($('#minZoom').val()<20)) {
			urlParamObj.val(thisUrl);
        	thisUrl=thisUrl.replace('?minZoom='+prevMinZoom,'').replace('&minZoom='+prevMinZoom,'');
			if (!($('#minZoom').val()==2)) {
    			var param = getParam();
   				thisUrl=thisUrl+param+'minZoom='+$('#minZoom').val();
			}
		} 
		urlParamObj.val(thisUrl);	
	});
	
	
	
	$('#updateURL').on('click', function(){
		    var currentUrlParm = urlParamObj.val();
			sessionStorage.plottedData = $('#markerLocationField').val();
			currentUrlParm=currentUrlParm.replace('&mapCustomURL='+prevMapCustomURL,'').replace('?mapCustomURL='+prevMapCustomURL,'');
			if (($('#customMapURL').val()!=='')) {
				currentUrlParm=currentUrlParm+'&mapCustomURL='+$('#customMapURL').val();
			} 
			urlParamObj.val(currentUrlParm.replace('multiplottr.com/&','multiplottr.com/?'));
                    console.log(urlParamObj.val());
                    $('#urlMapSettings').attr('action', urlParamObj.val());
                    $('#urlMapSettings').submit();
			//window.location.replace(urlParamObj.val());
         	return false;
	});
	
	
	function clearParams() {
		var urlparts= thisUrl.split('?');
		var pars= urlparts[1].split(/[&;]/g);
		if (pars.indexOf('map_id')) {window.location.replace(urlparts[0]+'?'+pars[0]);}
	}
	
	
	
	$('#clearParameters').on('click', function(){
		urlParamObj.val('');
    $('#urlMapSettings').attr('action', '?map_id='+url('?map_id'));
    $('#urlMapSettings').submit();
	});
	
	if( thisUrl.indexOf('&http://www') >= 0){
		clearParams();
	}
	
	
	$('#embedMap').on('click', function(){
		var embedCode = '<iframe src="http://localhost:8080/index_embed.php?map_id='+url('?map_id')+'" name="embededMPMap" width="100%" height="500px" SCROLLING="no" frameborder="0" id="embededMPMap"></iframe>';
		$('#alertBox span').addClass('textLeft');
		$('#alertBox span').html('<pre><code></code></pre>');
		$('#alertBox span pre code').text(embedCode);
		$('#alertBox, #modalBack').show();
		$('#alertYes, #alertNo').hide();
	});
// end of Mapsettings	


	
	
	

$('#aboutMP').live('click', function(){ 
	    var container = $('#introText');
		container.show();
	
	    
 	});
	
	$('#shareMap').on('click', function(){ 
	   // var container = $('#introText');
		//container.show();
	
	    
 	});
	
	
	
	$('#userSettings').on('click', function(){ 
	    var container = $('#userAccountBox');
		container.show();
		$('#loggedInBox').slideUp("fast"); $('#loggedIn div').removeClass('contract'); $('#loggedIn div').addClass('expand'); 
	});
		
	$('#changePassword').on('click', function(){ 
	    var container = $('#changePasswordForm');
		if ($('#changePasswordForm').is(':visible')) { container.hide(); } else {
			container.show();
		}
	});	
		
	$('#changeEmail').on('click', function(){ 
	    var container = $('#changeEmailForm');
		if ($('#changeEmailForm').is(':visible')) { container.hide(); } else {
			container.show();
		}
	});	
		
	$('#userAccountBox .closeBox').on('click', function() {
		$("#userAccountBox").hide();
	});	


	
	$('#contactUsButton').on('click', function(){ 
	    var container = $('#contactUsBox');
		container.show();
	
	    
 	});
	
	$('#logo').on('click', function(){ 
	    window.location = '../';
	
	    
 	});
	
	
	$('#myMaps').on('click', function(){ 
	    var container = $('#myMapsBox');
		hideMobile();
		container.show();
	
	    
 	});
	
	$('#myMapsBox .close').on('click', function() {
    $("#myMapsBox").hide();
	
	
});

$('#contactUsBox .closeBox').on('click', function() {
    $("#contactUsBox").hide();
	
	
});
	
	


// http://localhost:8080/?mapControl=true&mapControlSize=large&mapType=false&mapCustom=true&mapCustomURL=http://localhost:8080/dayz/tilecutter/&mapDatabase=http://localhost:8080/datafiles/dayz.txt
// http://localhost:8080/?mapCustom=true&mapCustomURL=../dayz/tilecutter/&mapDatabase=datafiles/dayz.txt

		
	$('#batchAddressTab, #batchAddress .close').on('click', function(){ 
	    // if ($('#addMarkerTab').hasClass('expanded')) {
		//	$('#addMarkerTab').removeClass('expanded');
		//	$('#addMarkerTab').addClass('collapsed');
		//	$('#placeMarkers').hide();
		//	$('#addMarkerTab').removeClass("moveUpOnePx");
		//}
		hideMobile();
		if ($('#mapSettings').is(':visible')) { $('#mapSettings').slideUp("fast"); }  
		if ($('#batchAddress').is(':visible')) { $('#batchAddress').slideUp("fast"); } else
		{ $('#batchAddress').slideDown("fast");
		src = $('#markerSelection section ul li .selected').attr('src');
			$('.currentMarker').html('<img src='+src+' alt="Current Marker">');	
			}
	
	    clearSaveMap();
 	});
	
	
	
	
	$('#mapSettingsNav').live('click', function(){ 
		clearSaveMap();
		hideMobile();
		if (((localStorage['geoCodedAddresses'])==null)||((localStorage['geoCodedAddresses'])=='')) {$('#lastPlotted').hide();}
		$('#saveMapButton').hide();
		$('#saveMapSettings').hide(); 
		$('#advancedSettingsButton').show();
		$('#showLocation').show();
		$('#zoomView').show();
		if ($('#saveMapButton').is(':visible')) { 
		
			$('#showLocation').hide();
			$('#saveMapButton').hide(); 
			$('#saveMapSettings').hide();
			$('#advancedSettingsButton').show();
			$('#zoomView').show();
			$('#mapSettings h2.mainHeader').html('Map Settings');
		} else 	 {
			if ($('#batchAddress').is(':visible')) { $('#batchAddress').slideUp("fast"); }
		   	$('#mapSettings').slideDown("fast"); 
		}
 	});
	
	
	$('#mapSettings .close').on('click', function(){
		clearSaveMap();
		$('#mapSettings').slideUp("fast");
		});
	
	
	$('#mapTitle').on('click', function(){ 
		//if ($('#mapSettings').is(':visible')) { $('#mapSettings').slideUp("fast"); } else 
		//if ($('#batchAddress').is(':visible')) { $('#batchAddress').slideUp("fast"); } else
		//{$('#mapSettings').slideDown("fast");}
		
		if ($('#mapDescript').is(':visible')) {
			$('#mapDescript').slideUp("fast");
			$('#mapTitle #expandClose .expand').show();
			$('#mapTitle #expandClose .contract').hide();
		} else
		{
			$('#mapTitle #expandClose .expand').hide();
			$('#mapTitle #expandClose .contract').show();
			$('#mapDescript').slideDown("fast");
		}
	
	    
 	});
	
	$('#addMarkerTab').on('click', function(){ 
	    if ($('#addMarkerTab').hasClass('expanded')) {
			$('#addMarkerTab').removeClass('expanded');
			$('#addMarkerTab').addClass('collapsed');
			$('#placeMarkers').slideUp("fast");
			$('#addMarkerTab').removeClass("moveUpOnePx");
		} else {
			
			if ($('#batchAddressTab').hasClass('expanded')) {
				$('#batchAddressTab').removeClass('expanded');
				$('#batchAddressTab').addClass('collapsed');
				$('#batchAddress').hide();
				$('#BatchAddressTab').removeClass("moveUpOnePx");
			}
			
			$('#addMarkerTab').removeClass('collapsed');
			$('#addMarkerTab').addClass('expanded');
			$('#placeMarkers').slideDown("fast");
			//$('#markerSelection').show();	
		}
 	});
	
	
 
	
	
	$('#markerSelCollapse').on('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		//$('#markerSelection section').addClass('Hide');
 	});
 
	$('#markerSelExpand').on('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		//$('#markerSelection section').removeClass('Hide');
 	});
	
	
	
	$('.changeMarker').on('click', function(){ 
 	$('#markerSelection').show();	
	if ($('#markerList').is(':visible')) { 
		$('.changeMarker').html('Show Available Markers');
		$('#markerList').hide(); 
		$('#customizedMarker').hide(); 
		
		$('#markerSelection .container').show(); 
		$('#addAddress').show(); 
	} else 
	{
		$('#markerList').show();  
		$('#customizedMarker').show();
		$('#markerSelection .container').hide(); 
		$('#addAddress, #addNewMarkerURL').hide();
		$('.changeMarker').html('Hide Available Markers');
	}
		
	});
	
	
	
	
	
	
	

 
 	$('#markerList li img').live('click', function(){ 
 		$('#markerList li img').removeClass('selected');
		$('#markerName').val($(this).attr('alt'));
		$(this).addClass("selected");
		$('.currentMarker').html('<img src='+$(this).attr('src')+' alt="Current Marker">');
		$('#markerList').hide(); 
		$('#customizedMarker').hide(); 
		$('.changeMarker').html('Show Available Markers');
		$('#markerSelection .container').show(); 
		$('#addAddress').show(); 
	});
	
	

	$('#groupHeader').on('click', function(){ 
	$('#mrkrGroups').toggle();
	if ($('#mrkrGroups').is(':hidden')) { 
		$('#groupHeader span').html('<div data-icon="y" class="expand">');
	} else 
	{
		$('#groupHeader span').html('<div data-icon="w" class="contract"></div>');
	}
 		
		
	});


                	
	
	$('#legendHeader').on('click', function() {
		if ($('#legendItems').is(':visible')) {
			$('#mainButtonContainer').slideDown("fast");
			$('#mainButtonContainer').show(); 
			$('#legendItems').slideUp("fast"); 
			$('#legendHeader .contract').hide(); 
			$('#legendHeader .expand').show(); 
		} else {
			$('#legendItems').slideDown("fast");
			$('#mainButtonContainer').slideUp("fast"); 
			$('#legendHeader .contract').show(); 
			$('#legendHeader .expand').hide();
		}
		});
		
	$('#batchAddress .currentMarker, #quickSearch .currentMarker').on('click', function(){ 
 		$('#markerSelection h1').html('<div data-icon="p" class="close"></div>Default Marker');
		//$('#markerSelection .container ul section, #zoomView, #markerSelection .tip, #markerSelection h2, #markerSelection .changeMarker').hide();
		
	});	
	
	$('#placeMarkerHeader, #batchAddress .currentMarker, #quickSearch .currentMarker').on('click', function() {
		
		
		src = $('#markerSelection section ul li .selected').attr('src');
        $('.currentMarker').html('<img src='+src+' alt="Current Marker">');
		
		if ($(this).attr('class') == 'currentMarker') {
			$('#markerSelection h1').html('<div data-icon="p" class="close"></div>Default Marker');
			$('#markerSelection .tip, .markerInfoWindow').hide();
		} else {
			$('#markerSelection h1').html('<div data-icon="p" class="close"></div>Place Marker');
			$('#markerSelection .container ul section, #zoomView, #markerSelection .tip, #markerSelection h2, #markerSelection .changeMarker').show();

		}
		
		tinyMCE.get('markerTextArea').setContent('');
		$('#markerList').hide();
		$('.changeMarker').html('Show Available Markers');
		$('#customizedMarker').hide(); 
		$('#markerSelection .container').show(); 
		$('#addAddress').show();
			
		hideMobile();
		
		if ($('#markerSelection').is(':visible')) {
			$('#markerSelection').hide();
			$('#customizedMarker').hide();
			$('#submitMarker').hide();
			$('#plotMarker').prop('checked', false);
			} else
	{ 
	$('#markerSelection').show();
	$('#plotMarker').prop('checked', true);
		
		}
		});
		
	$('#markerSelection .close').live('click', function() {
		$('#markerSelection, #submitMarker, #updateMarker, #customizedMarker').hide();
		$('#plotMarker').prop('checked', false);
		
	});
	
	
	$('#placeMarker').on('click', function() {
		if ( $('#plotMarker').prop("checked") ) {
			$('#plotMarker').prop('checked', false);
			$('#submitMarker, #updateMarker').hide();
		} else {
			$('#plotMarker').prop('checked', true);
			
		}
		hideMobile();
	});
	
	
	
	
	$('#geoCodeAddress').on('click', function() {
		i=$('#markerAddress').val();
		if (i=='') {alert('Please enter an address or click on Place Marker button and then click on the map where you would like your marker to appear. You may drag your marker to fine tune your points after placement. ');} else {geocodeThis(i);}
	}); 
	
	
	$("#currentGroupName").on("keyup change", function() {
		var i = $('#currentGroupName').val();
    	$('#currentGroupNameDisplay').text(i);
		
	});
	
	
	
	
	
	$('#allGroupNames').on('change', function () { 
 		$('#currentGroupName').val($('#allGroupNames').val());
		$('#currentGroupNameDisplay').text($('#currentGroupName').val());
 	});
		
//$('#resizeBatchBoxes').on('click', function() {
//if (!resizedBatch) {
//		resizedBatch=true;
//		$('#batchAddresses section').animate({bottom:'+=150'},500);
//		$('#mapTool section').animate({top:'-=150'},500);
//	} else {
//		resizedBatch=false; 
//		$('#batchAddresses section').animate({bottom:'-=150'},500); 
//		$('#mapTool section').animate({top:'+=150'},500);
//	}
//}); 

$('#batchAddresses section textarea').on('click', function() {
	if (resizedBatch) {
		resizedBatch=false; 
		$('#batchAddresses section').animate({bottom:'-=150'},500); 
		$('#mapTool section').animate({top:'+=150'},500);
	}
		
}); 

$('#mapTool section textarea').on('click', function() {
	if (!resizedBatch) {
		resizedBatch=true;
		$('#batchAddresses section').animate({bottom:'+=150'},500);
		$('#mapTool section').animate({top:'-=150'},500);
	}
		
}); 	



$('#loginRegister').on('click', function() {
	//$("#loginRegisterBox").toggle();
	
	var theseGeoCodedAddresses = $('#markerLocationField').val();
	
	//addressLines.filter(function(e){return e});
	if (!theseGeoCodedAddresses=='') {sessionStorage.geoCodedAddresses=theseGeoCodedAddresses;} else {sessionStorage.geoCodedAddresses='';}
	
	if ($('#loginRegisterBox').is(':visible')) {
			$('#loginRegisterBox').slideUp("fast"); 
			$('#loginRegister .expand').show();
			$('#loginRegister .contract').hide(); 
		} else {
			$('#loginRegisterBox').slideDown("fast"); 
			$('#loginRegister .expand').hide();
			$('#loginRegister .contract').show();
			if  (localStorage['thisUserName']) {
				if  (localStorage['thisUserName'] !='') {
					$('#saveLogin').prop('checked', true);
					$('#username').val(localStorage['thisUserName']);
					$('#password').val(localStorage['thisUserPW']);
				}
			}
		}
		
});
		
		
$('#loggedIn').on('click', function() {
	//$("#loginRegisterBox").toggle();
	if ($('#loggedInBox').is(':visible')) {
			$('#loggedInBox').slideUp("fast"); 
			$('#loggedIn .contract').hide(); 
			$('#loggedIn .expand').show(); 
		} else {
			$('#loggedInBox').slideDown("fast"); 
			$('#loggedIn .contract').show(); 
			$('#loggedIn .expand').hide();
		}
	
	
	
		});		



function runLogin() {
	$('#loginRegister').hide();
	$('#loggedIn').show();
	$('#editOptions').show();
	$('#mobilLogin').removeClass('icon-user-outline'); 
	$('#mobilLogin').addClass('icon-user'); 
	$('#loginRegisterBox').slideUp("fast"); $('#loginRegister div').removeClass('contract'); $('#loginRegister div').addClass('expand');
	
//	if (localStorage['geoCodedAddresses']) {
//		$('#markerLocationField').val(localStorage['geoCodedAddresses']);
//		$('#mapTitleField').val(localStorage['mapTitle']);
//		$('#mapTitle header').text(localStorage['mapTitle']);
//		runSubmit();
//		}
	
	/*if  (localStorage['geoCodedAddresses']) {
		var lines = localStorage['geoCodedAddresses'].split("\n");
 		var markerData = [];
	   	var markerLocationData = '';
		var foundTitle;
	   	for (var ee=0; ee < lines.length; ee++) {
			var n=lines[ee].indexOf('|TITLE|');
			if (n>0) {
				foundTitle=lines[ee];
				fields = foundTitle.split('|');
				$('#myMapsBox section').html('<ul><li class="title"><a href="javascript:void(0)">'+fields[1]+'</a></li><li class="date">05/26/2012</li><li class="delete"><a href="javascript:void(0)">Delete</a></li></ul>');	
			}
		}
	}*/
}


$('#userLogin').live('click', function() {
//	localStorage['loggedIn']='true';
//	runLogin();
	
}); 




$('#loginSubmit').live('click', function() {
	if ($('#saveLogin').attr("checked")){
		localStorage['thisUserName'] = $('#username').val();
		localStorage['thisUserPW'] = $('#password').val();
    } else {
		localStorage['thisUserName'] = '';
		localStorage['thisUserPW'] = '';
	}
});



$('#logOut').live('click', function() {
	localStorage['geoCodedAddresses']='';
});



$('#myMapsBox section .title').live('click', function() {
	$('#markerLocationField').val(localStorage['geoCodedAddresses']);
		$('#mapTitleField').val(localStorage['mapTitle']);
		//tinyMCE.get('mapDescriptionField').getContent();
		
		$('#mapTitle header').text(localStorage['mapTitle']);
		localStorage['loadMap']='false';
			//$('#introText').show();
			//runSubmit('dontLook');
			runSubmit();
});

$('#myMapsBox section .delete').live('click', function() {
	localStorage['loadMap']='false';
	localStorage['geoCodedAddresses']='';
	$('#mapLegend #mainButtonContainer #cleaRNSave').hide();
	$('#markerLocationField').val('');
	$('#mapLegend #mainButtonContainer #cleaRNSave').hide();
	$('#myMapsBox section').html('<ul><li class="title"><a href="javascript:void(0)"></a></li><li class="date"></li><li class="delete"></li></ul>');
	location.reload();
}); 




/*$('#logOut').on('click', function() {
	localStorage['loggedIn']='false';
	$('#loggedIn').hide();
	$('#loginRegister').show();
	$('#editOptions').hide();
	$('#myMapsBox').hide();
	sessionStorage.loggedIn='';
	if ($('#loggedInBox').is(':visible')) {
			$('#loggedInBox').slideUp("fast"); $('#loggedIn div').removeClass('contract'); $('#loggedIn div').addClass('expand'); 
		}
	window.location.href = window.location.pathname;	
	
}); 

*/

$('#forgotCredentialsLink, #hideCredentials').on('click', function() {
	//$("#forgotCredentials").toggle();
	if ($('#forgotCredentials').is(':visible')) {
			$('#forgotCredentials').hide();
			$('#registerLink').slideDown("fast"); 
		} else {
			$('#forgotCredentials').slideDown("fast");
			$('#registerLink').hide();
			$('#registerBox').slideUp("fast");
		}
		});	
		
$('#registerLink').on('click', function() {
	
	if ($('#registerBox').is(':visible')) {
			$('#registerBox').slideUp("fast");
			$('#loginForm' ).slideDown("fast"); 
		} else {
			$('#registerBox').slideDown("fast");
			$('#loginForm' ).slideUp("fast");
		}
	
	});	
	
$('#plotYes').live('click', function() {
	$('#loadLastPlotted, #modalBack').hide();
	sessionStorage.visited = 'yes';
	$('#markerLocationField').val(localStorage['geoCodedAddresses']);
	$('#mapLegend #mainButtonContainer #cleaRNSave').show();
	$('#mapTitleField').val('');
	tinyMCE.get('markerTextArea').setContent('');
	$('#mapTitle header').text('');
	localStorage['loadMap']='false';
	runSubmit();
});		

$('#plotNo').on('click', function() {
	sessionStorage.visited = 'yes';
	localStorage['loadMap']='false';
	$('#loadLastPlotted, #modalBack').hide();
});	

$('#clearYes').on('click', function() {
	var myDate = new Date();
	var displayDate = (myDate.getMonth()+1) + '/' + (myDate.getDate()) + '/' + myDate.getFullYear();
	$('#loadLastPlotted, #modalBack').hide();
	sessionStorage.visited = 'yes';
	$('#mapTitleField').val('');
	$('#mapTitle header').text('New Map '+displayDate);
	localStorage['loadMap']='false';
	$('#markerLocationField').val('');
	$('#mapLegend #mainButtonContainer #cleaRNSave').hide();
	runSubmit('dontLook');
	location.href='/';
});		

$('#clearNo').on('click', function() {
	sessionStorage.visited = 'yes';
	$('#loadLastPlotted, #modalBack').hide();
});	

function clearSaveMap() {
	$("#saveMap").removeClass();
	$('#saveMap').addClass('readyToSave');
}
		
$('#saveMap, #miniSaveMap').on('click', function() {
	$('#saveMapButton').show(); 
	$('#saveMapSettings').show();
	if ($('#saveMap').hasClass('addedMarkers')) {
		$('#markerCount').val('0');
		$('#saveMap').removeClass('addedMarkers');
		$('#saveMap').addClass('readyToSave');
		$('#saveMapButton').trigger('click');
	} else {
		
		$('#markerSelection').hide();
		$('#mapSettings h2.mainHeader').html('Save Map');
		$('#advancedSettingsButton').hide();
		$('#showLocation').hide();
		$('#zoomView').hide();
		if ($('#batchAddress').is(':visible')) { $('#batchAddress').slideUp("fast"); }
		$('#mapSettings').slideDown("fast");
		$('#advancedSettingsButton').hide();
		$('#zoomView').hide();
		$('#saveMap').removeClass('addedMarkers');
		$('#saveMap').removeClass('readyToSave');
		$('#saveMap').addClass('saveOff');
		
	}
});	

$('#saveMapButton').on('click', function() {
	var tinyMCEContent = $('#mapDescriptionField').val();
	$('#mapDescriptionFieldSubmit').html(tinyMCEContent);
	$('#mapUrlParam').val($('#urlParam').val());
        $('#mapTool').attr('action', urlParamObj.val());
	localStorage['loadMap']='true';
        try {
            $('#saveMapAction').val('savemap');
            if($("input:radio[name=saveMapOptions]").length) {
               $('#saveOverwriteMapOption').val($("input:radio[name=saveMapOptions]:checked").val())
            }
        } catch(e){}
	runSubmit();
	});	

	
	$('#lastPlotted').live('click', function() {
	
		if (localStorage['geoCodedAddresses']) {
		$('#markerLocationField').val(localStorage['geoCodedAddresses']);
		$('#mapTitleField').val(localStorage['mapTitle']);
		$('#mapTitle header').text(localStorage['mapTitle']);
		runSubmit();
		}
		
	
	});
	

$('#loadPreviousMap').on('click', function() {
	$('#markerLocationField').val(localStorage['geoCodedAddresses']);
	$('#mapLegend #mainButtonContainer #cleaRNSave').show();
	$('#mapTitleField').val(localStorage['mapTitle']);
	$('#mapTitle header').text(localStorage['mapTitle']);
    localStorage['loadMap']='true';
	runSubmit();
});			


$('#cleanMap').on('click', function() {
	$('#loadLastPlotted, #modalBack').show();
	
});	



 $('#mobilMenu').on('click', function() {
	
	if ($('#mapLegend').is(':visible')) {$('#mapLegend').hide();} else {
	
	$('#mapLegend').show();}
	
});	


$('#mobilLogin').on('click', function() {
	if ($('#loginRegisterBox').is(':visible')) {
		$('#loginRegisterBox').hide();
	} else {
		if ($('#mobilLogin').hasClass('icon-user-outline')) {
			$('#loginRegisterBox').show(); 
		} else {
			if ($('#loggedInBox').is(':visible')) {$('#loggedInBox').hide();} else {
			$('#loggedInBox').show();}  }
	}
});






$('#introText .close').on('click', function() {
    $("#introText").hide();
	sessionStorage.hideIntro = 'yes';
	
	
});



	





$('#overMapAdContainer .closeAd').on('click', function() {
    $("#overMapAdContainer").hide();
});		


/*

If a change happens to a marker - and its not saved notify with this.

$(window).bind('beforeunload', function(){
	if ($('#markerCount').val()!=='0') {
		return 'You have '+$('#markerCount').val()+' unsaved markers.';
	} else { window.onbeforeunload = null;}
});
	*/
	
$('#quickSearch textarea').focus(function () {
$(this).toggleClass('force35pxheight');
});	

/*$("#quickSearch textarea").bind('paste', function(e) {
     var self = this;
          setTimeout(function(e) {
            alert($(self).val());
          }, 0);
});*/
	
$('#quickSearch textarea').focusout(function () {
$(this).toggleClass('force35pxheight');
});	

$('#quickSearch a').on('click', function() {
var quickSearchData = $('#quickSearch textarea').val();
$('#batchAddressesField').text(quickSearchData);
var addresses = $('#batchAddressesField').text();
batchGeocode(addresses);
});

$("#markerURL").on("keyup change", function() {
		var id = $('#markerURL').val();
    	var lastFour = id.substr(id.length - 4); 
		lastFour = lastFour.toLowerCase();
		if ((lastFour =='.jpg')||(lastFour =='.png')||(lastFour =='.gif')) {$('#addNewMarkerURL').show();} else {$('#addNewMarkerURL').hide();}
		
	});


function validategood() {
	if (($('#confCheckPW .check .good').is(':visible'))&&($('#checkPW .check .good').is(':visible'))&&($('#checkEmail .check .good').is(':visible'))) { 
		$('#register').slideDown("fast"); 
	} else  if ($('#register').is(':visible')) {
		$('#register').slideUp("fast");
	}
}	

function validateEmail(sEmail) {
	var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    if (filter.test(sEmail)) {
        return true;
    }
    else {
        return false;
    }
	
	}

    

function validatePassword(){
	var i = $("#regPassword").val().length;
	if ($('#regPassword').val()==$('#confirmPassword').val()) { 
		$('#confCheckPW .check').html('<div data-icon="1" class="good"></div>');
	} else {$('#confCheckPW .check').html('<div data-icon="S" class="bad"></div>');}
	
	if (i>3) { 
		$('#checkPW .check').html('<div data-icon="1" class="good"></div>');
	} else {$('#checkPW .check').html('<div data-icon="S" class="bad"></div>');}
	
} 

$("#regPassword").on("keyup change", function() {
		validatePassword();
		validategood();
});

$("#confirmPassword").on("keyup change", function() {
		validatePassword();
		validategood();
});

$("#email").on("keyup change", function() {
	sEmail = $('#email').val();
	validateEmail(sEmail);
	if ($.trim(sEmail).length == 0) {
            $('#checkEmail .check').html('<div data-icon="S" class="bad"></div>');
        }
        if (validateEmail(sEmail)) {
            $('#checkEmail .check').html('<div data-icon="1" class="good"></div>');
        }
        else {
            $('#checkEmail .check').html('<div data-icon="S" class="bad"></div>');
        }
	validategood();
});
	
	

function init() {
	sessionStorage.visited = 'yes'; 
	if (localStorage['loggedIn']=='true') {
		runLogin();
		
	}
	//if ( (localStorage['geoCodedAddresses']) && (loadLocalStorage) && ((sessionStorage.visited)==null))
	
      
			
//			$('#loadLastPlotted, #modalBack').hide();
//			sessionStorage.visited = 'yes';
//			$('#markerLocationField').val(localStorage['geoCodedAddresses']);
//		$('#mapTitleField').val(localStorage['mapTitle']);
//			$('#mapTitle header').text(localStorage['mapTitle']);
//		localStorage['loadMap']='false';
			//$('#introText').show();
			//runSubmit();
			 
            
//        } else if ((sessionStorage.visited)==null)  {
//		$('#mapTitleField').val('');
//        $('#mapTitle header').text('New Map Title');
			//$('#introText').show();
//		}
	}
	


init();

$(function () {
    //check if the adblocker variable is undefined
    if ($.adblocker == undefined) {
        //it is undefined so an adblocker is present
        $.adblocker = true;
        //pop up the alert
        $('.blockedAd').show();
    }
});		

 //http://www.thomashardy.me.uk/using-html5-localstorage-on-a-form
 //$('.draggableMarker').draggable();		
		
	
		 
		
		
		});
		
$(window).load(function () {
  
    
});		