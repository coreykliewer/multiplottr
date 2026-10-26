<!doctype html>
<html>
   <head>
   <meta charset="utf-8">
   <title>Multiplottr: Plot, Save and Share.</title>
   <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
   <link rel="icon" href="favicon.ico" type="image/x-icon">
   <script src="https://maps.googleapis.com/maps/api/js?key=[REDACTED_TOKEN]&sensor=false" type="text/javascript"></script>
   <script src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/1.0/src/markermanager.js" type="text/javascript"></script>
   <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
   <script src="scripts/jquery.ui.map.js"></script>
   <script src="scripts/infobox.js"></script>
   <script src="scripts/placeholder/jquery.placeholder.js" type="text/javascript" charset="utf-8"></script>
   <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
   <link href="new.css" rel="stylesheet" type="text/css" />
   <link href='http://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
   <link href='http://fonts.googleapis.com/css?family=Nunito' rel='stylesheet' type='text/css'>
   <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
   <!-- TinyMCE -->
   <script type="text/javascript" src="file:///D|/Dropbox/InMotion/scripts/tiny_mce/tiny_mce.js"></script>
   <script type="text/javascript" src="file:///D|/Dropbox/InMotion/scripts/_advertisement.js"></script>
   <script type="text/javascript" src="file:///D|/Dropbox/InMotion/scripts/js-url.js"></script>
   <script type="text/javascript" src="file:///D|/Dropbox/InMotion/scripts/jquery.autosize-min.js"></script>
   <script type="text/javascript">
   

 tinyMCE.init({
     mode : "exact",
		remove_linebreaks : false,
        elements : "markerTextArea",
		theme : "advanced",
		theme_advanced_buttons1 : "bold,italic,|,link,unlink,image,|,bullist,numlist,|,code",
   });
   
   
   tinyMCE.init({
     mode : "exact",
		remove_linebreaks : false,
        elements : "mapDescriptionField",
		theme : "advanced",
		theme_advanced_buttons1 : "bold,italic,|,link,unlink,image,|,bullist,numlist,|,code",
		width: '100%',
		height: '275px',
   });
   
   
</script>
   <!-- /TinyMCE -->
   <script type='text/javascript'>
   
  
  var dataArray =[];
  var dataArrayCount = 0;
  var fileName;
  var src;
  var currentGroupName='group0';
  var resizedBatch = false;
  var geoCodeListArray=[];
  var addressLineCount;
  var totalAddressLineCount;
  
var geocoder = null;

var loadLocalStorage = true;
var submitContinue = false;




function lookForItems() {
	var lines = $('#markerLocationField').val().split("\n");
    var markerData = [];
	var markerLocationData = '';
	for (var ee=0; ee < lines.length; ee++) {
		var n=lines[ee].indexOf('|TITLE|');
		if (n<0) {markerLocationData = markerLocationData+lines[ee]+'\n';} else {markerLocationData = markerLocationData+'\n';}
	}
	if 	(!$('#mapTitle header').text()=='') {
		$('#markerLocationField').val(markerLocationData+'~0,0|'+$('#mapTitle header').text()+'|TITLE|TITLE \n');
		submitContinue=true;
	} else {
		var myDate = new Date();
		var displayDate = (myDate.getMonth()+1) + '/' + (myDate.getDate()) + '/' + myDate.getFullYear();
		//$('#modalAlert article').text('Your map must have a title before you can save.');
		//alert('Making Title');
		$('#markerLocationField').val(markerLocationData+'~0,0|New Map '+displayDate+'|TITLE|TITLE \n');
		submitContinue=true;
		//if (!$('#mapSettings').is(':visible')) { $('#mapSettings').slideDown("fast"); }
		//$('#modalTitleAlert, #modalBack').show();
	}
}



   

function runSubmit(i) {
	//alert(i);
	if (i==null) {lookForItems();} else {submitContinue=true;}
	
	
	var theseGeoCodedAddresses = $('#markerLocationField').val();
	var addressLines = theseGeoCodedAddresses.split("\n");
	
	//addressLines.filter(function(e){return e});
	if (addressLines[0]=="") {addressLines.splice(0,1)};
	theseGeoCodedAddresses=addressLines.join("\n");
	theseGeoCodedAddresses = theseGeoCodedAddresses.replace(/((\r\n|\r|\n)\s*)\2/g, "$2") ;
	$('#markerLocationField').val(theseGeoCodedAddresses);
	localStorage['geoCodedAddresses'] = $('#markerLocationField').val();
	localStorage['mapTitle'] = $('#mapTitleField').val();

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
		for (i=0; i<geoCodeListArray.length; i++) {
			{geoCoded.push(geoCodeListArray[i]+'\n');}
			var filteredGeoCoded = geoCoded.filter(function(word) {return word[0] == '~';});
			if (filteredGeoCoded.length==geoCodeListArray.length) {
				for (i=0; i<filteredGeoCoded.length; i++) {	
					if (filteredGeoCoded[i].substring(0,3)=='~NO') {
						if (filteredGeoCoded.length==linesLength) {notPlotted = notPlotted+filteredGeoCoded[i];}
					} else {
						if (filteredGeoCoded.length==linesLength) {plottedAddresses = plottedAddresses+filteredGeoCoded[i];}
					}
				}
				$('#markerLocationField').val(plottedAddresses);
				$('#batchAddressesField').val(notPlotted);	
				$('#plotTheseAddresses').trigger('click'); 

				
			}
		}
	}
		
				if (status == google.maps.GeocoderStatus.OK) {

			  		thisLattyLongy = results[0].geometry.location.lat()+', '+results[0].geometry.location.lng();
					
					
			  		//alert(thisLattyLongy+'|'+description+'|'+markertype+'|'+groupname);
					geoCodeListArray[orderNumber]='~'+thisLattyLongy+'|'+description+'|'+markertype+'|'+groupname;
					//alert('geoCodeListArray['+orderNumber+']: '+geoCodeListArray[orderNumber]);

					if (linesLength==geoCodeListArray.length) {
						//alert(linesLength+'|'+geoCodeListArray.length);
						filterTheseAddresses();
						localStorage['loadMap']='false';
						runSubmit();
					}

          		} else if (status == 'ZERO_RESULTS') {
					geoCodeListArray[orderNumber]='~NO LOCATION FOUND|'+description+'|'+markertype+'|'+groupname;
					
					if (orderNumber+1==geoCodeListArray.length) {
						//alert('google maps Zero- Filter');
						filterTheseAddresses();
						localStorage['loadMap']='false';
						//runSubmit();
						$('#progressContainer, #modalBack').hide();
					}
					
				} else {
			  		alert('Unsuccessful: '+status);
         		}
       		});
	
	//geoCodeListArray[orderNumber]=thisLattyLongy+'|'+description+'|'+markertype+'|'+groupname;

}
        
function timedGeocoding(val, ii, linesLength) {
				$('#batchAddressesField').val('');
    setTimeout ( function () {
			fields = val.split('|');
			
	        if (fields[1]==undefined) {fields[1]=fields[0];}
	        if (fields[1]=='') {fields[1]=fields[0];}
	        if (fields[2]==undefined) {fields[2]=$('#markerName').val();}
	        if (fields[3]==undefined) {fields[3]=$('#currentGroupName').val();}
			//if (val=='') {
			//	linesLength=linesLength-1;
			//	addressLineCount = addressLineCount-1;
			//} else
			$('#progressContainer, #modalBack').show();
			{
				addressLineCount = addressLineCount-1;
				var progress = addressLineCount/linesLength;
				progress = 100-(progress*100);
				$( "#progressbar" ).progressbar({value: progress});
				geoCodeListArray.push(val);
				//alert('1) Alert Val: '+val+' geoCodeListArray.length: '+geoCodeListArray.length);
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
	
	if (addresses!='') {
		newAddresses=addresses.replace(/\r/g,'`');
	    newAddresses=newAddresses.replace(/\n/g,'`');
		newAddresses=newAddresses.replace(/\t/g,' ');
		newAddresses=newAddresses.replace(/\| /g,'|');
		newAddresses=newAddresses.replace(/\" /g,'"');
		newAddresses=newAddresses.replace(/ \"/g,'"');
		newAddresses=newAddresses.replace(/> /g,'>');
		newAddresses=newAddresses.replace(/ </g,'<');
	    var lines = newAddresses.split('`');
	    var fields = new Array();
		
		//alert('lines.length: '+lines);
		lines = lines.filter(function(e){return e});
		//alert('lines.length: '+lines);
		addressLineCount = lines.length;
		$('#numOfAddresses').html(addressLineCount);
		for (i=0; i<lines.length; i++) {
			timedGeocoding(lines[i], i, addressLineCount);
		}
		
	} else {runSubmit();} 
}
  
  
  
  
$(document).ready(function(){
	
$("#progressbar").progressbar({value: 0});
$("#loginRegisterBox").hide();
$('#currentGroupName').val(currentGroupName);
$('#currentGroupNameDisplay').text(currentGroupName);
$('#markerName').val('RED');
$('#markerList li img[alt=RED]').addClass('selected');
//$('#markerSelection').toggle();
$('#updateMarker').hide();
$('#submitMarker').hide();
$('#quickSearch textarea').autosize({append: "\n"});




// DESCRIPTION: Shows introText box if the user has not closed it during this session.
if ( (!(sessionStorage.hideIntro)=='yes')||((sessionStorage.hideIntro)==null) ) {$('#introText').show();}

if (url('?mapControl')=='false') {$('#mapControl').prop('checked', false);} else {$('#mapControl').prop('checked', true);}
if (url('?mapControlSize')=='small') {$("#mapControlSize").val('small');    } else {$("#mapControlSize").val('large');}
if (url('?mapType')=='false') {  $('#showMapType').prop('checked', false);} else {$('#showMapType').prop('checked', true);}
if (url('?mapOverview')=='true') {$('#showMapOverview').prop('checked', true);} else {$('#showMapOverview').prop('checked', false);}
if (url('?showMapLegend')=='false') {$('#showMapLegend').prop('checked', false);} else {$('#showMapLegend').prop('checked', true);}
if (url('?showMapGroup')=='false') {$('#showMapGroup').prop('checked', false);} else {$('#showMapGroup').prop('checked', true);}
if ((url('?mapWidth')==null)||(url('?mapWidth')=='0')) {$('#mapWidth').val('100%');} else {$('#mapWidth').val(url('?mapWidth'));}
if ((url('?mapHeight')==null)||(url('?mapHeight')=='0')) {$('#mapHeight').val('100%');} else {$('#mapHeight').val(url('?mapHeight'));}
if (!(url('?maxZoom')>=0)||(url('?maxZoom')<20)) {$('#maxZoom').val('17');} else {$('#maxZoom').val(url('?maxZoom'));}
if (!(url('?minZoom')>=0)||(url('?minZoom')<20)) {$('#minZoom').val('2');} else {$('#minZoom').val(url('?minZoom'));}
if (!(url('?mapDatabase')==null)) {$('#mapDatabaseURL').val(url('?mapDatabase')); loadLocalStorage = false; $("#overMapAdContainer").hide();}
if (url('?mapCustom')=='true') {$('#mapCustom').prop('checked', true);} else {$('#mapCustom').prop('checked', false);}
$('#customMapURL').val(url('?mapCustomURL'));



$(function() { 
//	$( "#introText" ).resizable();
//	$( "#draggable3" ).draggable({ containment: "#containment-wrapper", scroll: false }) 
	$('#introText, #myMapsBox, #contactUsBox').draggable({ handle: "h1", containment: "#mapContainer" });
});



/*

Get Draggable position
$('#dragThis').draggable(
    {
        drag: function(){
            var offset = $(this).offset();
            var xPos = offset.left;
            var yPos = offset.top;
            $('#posX').text('x: ' + xPos);
            $('#posY').text('y: ' + yPos);
        }
    });


*/


$(document).mouseup(function (e)
{
    var container = $('#overMapAdContainer');

    if (container.has(e.target).length === 0)
    {
       // container.hide('slide',{direction:'right'},1000);
	   container.hide();
    }
});

$('#introText h1, #myMapsBox h1, #contactUsBox h1').live('mousedown', function(){ 
       $("#introText, #myMapsBox, #contactUsBox").removeClass("infront");
	   $(this).parent().addClass("infront");
	
	    
 	});

$('#aboutMP').live('click', function(){ 
	    var container = $('#introText');
		container.show();
	
	    
 	});
	
	
	
	$('#userSettings').live('click', function(){ 
	    var container = $('#userAccountBox');
		container.show();
		$('#loggedInBox').slideUp("fast"); $('#loggedIn div').removeClass('contract'); $('#loggedIn div').addClass('expand'); 
	});
		
	$('#changePassword').live('click', function(){ 
	    var container = $('#changePasswordForm');
		if ($('#changePasswordForm').is(':visible')) { container.hide(); } else {
			container.show();
		}
	});	
		
	$('#changeEmail').live('click', function(){ 
	    var container = $('#changeEmailForm');
		if ($('#changeEmailForm').is(':visible')) { container.hide(); } else {
			container.show();
		}
	});	
		
	$('#userAccountBox .closeBox').live('click', function() {
		$("#userAccountBox").hide();
	});	


	
	$('#contactUsButton').live('click', function(){ 
	    var container = $('#contactUsBox');
		container.show();
	
	    
 	});
	
	
	
	$('#myMaps').live('click', function(){ 
	    var container = $('#myMapsBox');
		container.show();
	
	    
 	});
	
	$('#myMapsBox .closeBox').live('click', function() {
    $("#myMapsBox").hide();
	
	
});

$('#contactUsBox .closeBox').live('click', function() {
    $("#contactUsBox").hide();
	
	
});
	
	


// http://localhost:8080/?mapControl=true&mapControlSize=large&mapType=false&mapCustom=true&mapCustomURL=http://localhost:8080/dayz/tilecutter/&mapDatabase=http://localhost:8080/datafiles/dayz.txt
// http://localhost:8080/?mapCustom=true&mapCustomURL=dayz/tilecutter/&mapDatabase=datafiles/dayz.txt

		
	$('#batchAddressTab').live('click', function(){ 
	    // if ($('#addMarkerTab').hasClass('expanded')) {
		//	$('#addMarkerTab').removeClass('expanded');
		//	$('#addMarkerTab').addClass('collapsed');
		//	$('#placeMarkers').hide();
		//	$('#addMarkerTab').removeClass("moveUpOnePx");
		//}
		if ($('#mapSettings').is(':visible')) { $('#mapSettings').slideUp("fast"); }  
		if ($('#batchAddress').is(':visible')) { $('#batchAddress').slideUp("fast"); } else
		{ $('#batchAddress').slideDown("fast");
		src = $('#markerSelection section ul li .selected').attr('src');
			$('.currentMarker').html('<img src='+src+' alt="Current Marker">');	
			}
	
	    
 	});
	
	
	
	
	$('#mapSettingsNav').live('click', function(){ 
		if ($('#mapSettings').is(':visible')) { 
	   		$('#mapSettings').slideUp("fast"); 
		} else {
			if ($('#batchAddress').is(':visible')) { $('#batchAddress').slideUp("fast"); }
		   	//if ($('#batchAddressTab').hasClass('expanded')) {
			//	$('#batchAddressTab').removeClass('expanded');
			//	$('#batchAddressTab').addClass('collapsed');
			//	$('#batchAddress').slideUp("fast");
			//	$('#BatchAddressTab').removeClass("moveUpOnePx");
			//} 
		$('#mapSettings').slideDown("fast"); 
		
		}
 	});
	
	$('#mapTitle').live('click', function(){ 
		if ($('#mapSettings').is(':visible')) { $('#mapSettings').slideUp("fast"); } else 
		if ($('#batchAddress').is(':visible')) { $('#batchAddress').slideUp("fast"); } else
		{$('#mapSettings').slideDown("fast");}
	
	    
 	});
	
	$('#addMarkerTab').live('click', function(){ 
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
	
	
 
	
	
	$('#markerSelCollapse').live('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		//$('#markerSelection section').addClass('Hide');
 	});
 
	$('#markerSelExpand').live('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		//$('#markerSelection section').removeClass('Hide');
 	});
	
	
	
	$('.currentMarker, .changeMarker').live('click', function(){ 
 	$('#markerSelection').show();	
	if ($('#markerList').is(':visible')) { $('#markerList').hide(); $('#customizedMarker').hide(); } else {$('#markerList').show();  $('#customizedMarker').show();}
		
	});
	
	$('#batchAddress .currentMarker').live('click', function(){ 
 		$('#markerSelection .headerContainer').html('<div class="closeBox"></div><header>Marker Defaults</header></header>');
		$('#markerSelection .container ul section, #zoomView').hide();
		
	});

 
 	$('#markerList li img').live('click', function(){ 
 		$('#markerList li img').removeClass('selected');
		$('#markerName').val($(this).attr('alt'));
		$(this).addClass("selected");
		$('.currentMarker').html('<img src='+$(this).attr('src')+' alt="Current Marker">');
		
	});
	
	
	
	$('#legendHeader').live('click', function() {
		if ($('#legendItems').is(':visible')) {
			$('#mainButtonContainer').slideDown("fast")  
			$('#mainButtonContainer').show(); 
			$('#legendItems').slideUp("fast"); 
			$('#legendHeader div').removeClass('contract'); 
			$('#legendHeader div').addClass('expand'); 
		} else {
			$('#legendItems').slideDown("fast");
			$('#mainButtonContainer').slideUp("fast")  
			$('#legendHeader div').removeClass('expand');
			$('#legendHeader div').addClass('contract');
		}
		});
	
	$('#placeMarkerHeader').live('click', function() {
		src = $('#markerSelection section ul li .selected').attr('src');
        $('.currentMarker').html('<img src='+src+' alt="Current Marker">');
		tinyMCE.get('markerTextArea').setContent('');
		 $('#markerSelection .headerContainer').html('<div class="closeBox"></div><header>Place Marker</header></header><div class="tip">Click on the map to place a marker.</div>');
		$('#markerSelection .container ul section, #zoomView').show();
		$('#markerList').hide();

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
		
	$('#markerSelection .closeBox').live('click', function() {
		$('#markerSelection, #submitMarker, #updateMarker, #customizedMarker').hide();
	});
	
	
	$('#placeMarker').live('click', function() {
		if ( $('#plotMarker').prop("checked") ) {
			$('#plotMarker').prop('checked', false);
			$('#submitMarker, #updateMarker').hide();
		} else {
			$('#plotMarker').prop('checked', true);
			
		}
	});
	
	
	
	
	$('#geoCodeAddress').live('click', function() {
		i=$('#markerAddress').val();
		if (i=='') {alert('Please enter an address or click on Place Marker button and then click on the map where you would like your marker to appear. You may drag your marker to fine tune your points after placement. ');} else {geocodeThis(i);}
	}); 
	
	
	$("#currentGroupName").on("keyup change", function() {
		var i = $('#currentGroupName').val();
    	$('#currentGroupNameDisplay').text(i);
		
	});
	
	$("#mapTitleField").on("keyup change", function() {
		var i = $('#mapTitleField').val();
    	$('#mapTitle header').text(i);
		
	});
	
	$('#allGroupNames').live('change', function () { 
 	$('#currentGroupName').val($('#allGroupNames').val());
	$('#currentGroupNameDisplay').text($('#currentGroupName').val());
 });
		
//$('#resizeBatchBoxes').live('click', function() {
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

$('#batchAddresses section textarea').live('click', function() {
	if (resizedBatch) {
		resizedBatch=false; 
		$('#batchAddresses section').animate({bottom:'-=150'},500); 
		$('#mapTool section').animate({top:'+=150'},500);
	}
		
}); 

$('#mapTool section textarea').live('click', function() {
	if (!resizedBatch) {
		resizedBatch=true;
		$('#batchAddresses section').animate({bottom:'+=150'},500);
		$('#mapTool section').animate({top:'-=150'},500);
	}
		
}); 	



$('#loginRegister').live('click', function() {
	//$("#loginRegisterBox").toggle();
	if ($('#loginRegisterBox').is(':visible')) {
			$('#loginRegisterBox').slideUp("fast"); $('#loginRegister div').removeClass('contract'); $('#loginRegister div').addClass('expand'); 
		} else {
			$('#loginRegisterBox').slideDown("fast"); $('#loginRegister div').removeClass('expand');
			$('#loginRegister div').addClass('contract');
		}
	
	
	
		});
		
		
$('#loggedIn').live('click', function() {
	//$("#loginRegisterBox").toggle();
	if ($('#loggedInBox').is(':visible')) {
			$('#loggedInBox').slideUp("fast"); $('#loggedIn div').removeClass('contract'); $('#loggedIn div').addClass('expand'); 
		} else {
			$('#loggedInBox').slideDown("fast"); $('#loggedIn div').removeClass('expand');
			$('#loggedIn div').addClass('contract');
		}
	
	
	
		});		



function runLogin() {
	$('#loginRegister').hide();
	$('#loggedIn').show();
	$('#editOptions').show();
	$('#loginRegisterBox').slideUp("fast"); $('#loginRegister div').removeClass('contract'); $('#loginRegister div').addClass('expand');
	if  (localStorage['geoCodedAddresses']) {
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
	}
	}


$('#userLogin').live('click', function() {
	localStorage['loggedIn']='true';
	runLogin();
	
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

	$('#markerLocationField').val('');
	$('#myMapsBox section').html('<ul><li class="title"><a href="javascript:void(0)"></a></li><li class="date"></li><li class="delete"></li></ul>');
	location.reload();
}); 


$('#logOut').live('click', function() {
	localStorage['loggedIn']='false';
	$('#loggedIn').hide();
	$('#loginRegister').show();
	$('#editOptions').hide();
	$('#myMapsBox').hide();
	if ($('#loggedInBox').is(':visible')) {
			$('#loggedInBox').slideUp("fast"); $('#loggedIn div').removeClass('contract'); $('#loggedIn div').addClass('expand'); 
		}
	window.location.href = window.location.pathname;	
	
}); 

$('#forgotCredentialsLink, #hideCredentials').live('click', function() {
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
		
$('#registerLink').live('click', function() {
	
	if ($('#registerBox').is(':visible')) {
			$('#registerBox').slideUp("fast"); 
		} else {
			$('#registerBox').slideDown("fast"); 
		}
	
	});	
	
$('#plotYes').live('click', function() {
	$('#loadLastPlotted, #modalBack').hide();
	sessionStorage.visited = 'yes';
	$('#markerLocationField').val(localStorage['geoCodedAddresses']);
	$('#mapTitleField').val('');
	tinyMCE.get('markerTextArea').setContent('');
	$('#mapTitle header').text('');
	localStorage['loadMap']='false';
	runSubmit();
});		

$('#plotNo').live('click', function() {
	sessionStorage.visited = 'yes';
	localStorage['loadMap']='false';
	$('#loadLastPlotted, #modalBack').hide();
});	

$('#clearYes').live('click', function() {
	var myDate = new Date();
	var displayDate = (myDate.getMonth()+1) + '/' + (myDate.getDate()) + '/' + myDate.getFullYear();
		//$('#modalAlert article').text('Your map must have a title before you can save.');
	$('#loadLastPlotted, #modalBack').hide();
	sessionStorage.visited = 'yes';
	$('#mapTitleField').val('');
	tinyMCE.get('markerTextArea').setContent(localStorage['']);
	$('#mapTitle header').text('New Map '+displayDate);
	localStorage['loadMap']='false';
	//localStorage.clear();
	$('#markerLocationField').val('');
	runSubmit('dontLook');
});		

$('#clearNo').live('click', function() {
	sessionStorage.visited = 'yes';
	$('#loadLastPlotted, #modalBack').hide();
});	
		
$('#saveMap, #miniSaveMap').live('click', function() {
	$('#markerCount').val('0');
	$('#markerSelection').hide();
    localStorage['loadMap']='true';
	runSubmit();
});	

$('#loadPreviousMap').live('click', function() {
	$('#markerLocationField').val(localStorage['geoCodedAddresses']);
	$('#mapTitleField').val(localStorage['mapTitle']);
	$('#mapTitle header').text(localStorage['mapTitle']);
	//tinyMCE.get('mapDescriptionField').setContent(localStorage['mapDescription']);
    localStorage['loadMap']='true';
	runSubmit();
});			


$('#cleanMap').live('click', function() {
	$('#loadLastPlotted, #modalBack').show();
	
});	


$('#buttonOK').live('click', function() {
	$('#modalAlert, #modalBack').hide();
	
});



$('#introText .closeBox').live('click', function() {
    $("#introText").hide();
	sessionStorage.hideIntro = 'yes';
	
	
});


$('#advancedSettingsButton').live('click', function() {
	
	if ($('#advancedMapSettings li').is(':visible')) {
    $("#advancedMapSettings").slideUp("fast");} else {$("#advancedMapSettings").slideDown("fast");}
	
});





$('#overMapAdContainer .closeAd').live('click', function() {
    $("#overMapAdContainer").hide();
});		


$(window).bind('beforeunload', function(){
	if ($('#markerCount').val()!='0') {
    return 'You have unsaved markers.';
	} else {window.onbeforeunload = null;}
    });
	
	
$('#quickSearch textarea').focus(function () {
$(this).toggleClass('force35pxheight');
});	
	
$('#quickSearch textarea').focusout(function () {
$(this).toggleClass('force35pxheight');
});	

$('#quickSearch a').live('click', function() {
var quickSearchData = $('#quickSearch textarea').val();
$('#batchAddressesField').text(quickSearchData);
var addresses = $('#batchAddressesField').val();
batchGeocode(addresses);
});	



	
function init() {
	
	     sessionStorage.visited = 'yes';
		 if (localStorage['loggedIn']=='true') {runLogin();}
	
	
 //       if ( (localStorage['geoCodedAddresses']) && (loadLocalStorage) && ((sessionStorage.visited)==null)) {
			
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
   </script>

   <!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->
   </head>
   <body>
   <div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=13993495363";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>


   
   
<div class="infoBoxWrap centered" id="loadLastPlotted">Would you like to clear all the markers?
     <div class="buttonHolder"> <a class ="button" id="clearYes" href="javascript:void(0)">Yes</a><a class ="button" id="clearNo" href="javascript:void(0)">No</a></div>
   </div>
<div class="infoBoxWrap centered" id="progressContainer">
     <p>Progress: <span id="numOfAddresses"></span></p>
     <div id="progressbar"></div>
   </div>
<div class="infoBoxWrapAlert centered" id="modalAlert">
     <article></article>
     <div class="buttonHolder"> <a class ="button" id="buttonOK" href="javascript:void(0)">OK</a></div>
   </div>
   
<div id="modalBack"></div>
<div id="topBannerNav"> <!---->
     <div id="logo">Multiplottr</div><h1>Plot, save and share multiple addresses with an easy to create customized map.</h1>
     <a href="javascript:void(0)" id="loginRegister"  href="javascript:void(0)">
     <div class="expand"></div>
     Login | Register</a> <a href="javascript:void(0)" id="loggedIn"       href="javascript:void(0)">
     <div class="expand"></div>
     Guest</a> </div>
<div id="popOutWrapper" >
     <div id="batchAddress">
    <div class="content">
         <h2>Plot Multiple Addresses</h2>
         <p>Place each address on its own line and hit the "Submit" button below. Multiplottr will process the addresses and geocode them to work with the Multiplottr format.</p>
         <ul id="defaultSettings" class="buttonHolder" >
        <li><span class="currentMarker"></span>
             <label>Default Marker:</label>
           </li>
        <li>
             <label>Default Group: </label>
             <span id="currentGroupNameDisplay"></span></li>
        <li>
        	<a id="batchGeocode" class="button4 buttonB" href="javascript:void(0)" >
          	<div class="icon"></div>
          	Submit</a>
        </li>
        <!-- <a id="plotTheseAddresses" class="button2" href="javascript:void(0)">Plot Addresses</a>-->
      </ul>
       </div>
    <form id="batchAddresses" method="post">
         <section>
        <textarea id="batchAddressesField" name="batchAddressesField"  placeholder="Enter each address 1 line at a time. Unregistered users can plot 25 addresses at a time."></textarea>
      </section>
       </form>
    <form  id="mapTool" method="post" >
         <section>
        <textarea id="markerLocationField" name="markerLocationField" placeholder="Multiplottr formatted data."></textarea>
      </section>
         <section class="hide">
        <input type="text" id="mapTitleFieldSubmit" name="mapTitleFieldSubmit">
        <textarea id="mapDescriptionFieldSubmit" name="mapDescriptionFieldSubmit" ></textarea>
      </section>
       </form>
  </div>
     <div id="mapSettings">
    <form id="urlAttributes" method="get">
         <div class="content">
        <h2>Map Settings</h2>
                <a id="advancedSettingsButton" href="javascript:void(0)" class="button">Advanced Map Settings</a>

        <ul id="advancedMapSettings">
             <li class="bulletLine">Show Zoom Control
            <input id="mapControl"  type="checkbox" name="mapControl" value="true"   />
          </li>
             <li class="bulletLine">Min Zoom Level
            <select  class="smallerFormField" id="minZoom"  name="minZoom">
                 <option>1</option>
                 <option>2</option>
                 <option>3</option>
                 <option>4</option>
                 <option>5</option>
                 <option>6</option>
                 <option>7</option>
                 <option>8</option>
                 <option>9</option>
                 <option>10</option>
                 <option>11</option>
                 <option>12</option>
                 <option>13</option>
                 <option>14</option>
                 <option>15</option>
                 <option>16</option>
                 <option>17</option>
                 <option>18</option>
                 <option>19</option>
                 <option>20</option>
               </select>
          </li>
             <li class="bulletLine">Max Zoom level
            <select  class="smallerFormField" id="maxZoom"  name="maxZoom">
                 <option>1</option>
                 <option>2</option>
                 <option>3</option>
                 <option>4</option>
                 <option>5</option>
                 <option>6</option>
                 <option>7</option>
                 <option>8</option>
                 <option>9</option>
                 <option>10</option>
                 <option>11</option>
                 <option>12</option>
                 <option>13</option>
                 <option>14</option>
                 <option>15</option>
                 <option>16</option>
                 <option>17</option>
                 <option>18</option>
                 <option>19</option>
                 <option>20</option>
               </select>
          </li>
             <li class="bulletLine">Zoom Control Type
            <select  class="smallerFormField" id="mapControlSize"  name="mapControlSize">
                 <option>small</option>
                 <option>large</option>
               </select>
          </li>
             <li class="bulletLine">Show Map Type
            <input type="checkbox" id="showMapType"  value="true"  name="mapType"/>
          </li>
             <li class="bulletLine">Show Map Overview
            <input type="checkbox" id="showMapOverview"  value="true" name="mapOverview" />
          </li>
             <li class="bulletLine">Show Legend
            <input type="checkbox" id="showMapLegend"  value="true" name="showMapLegend" />
          </li>
             <li class="bulletLine">Show Groups
            <input type="checkbox" id="showMapGroup"  value="true" name="showMapGroup" />
          </li>
             <li class="bulletLine">Map Width
            <input type="text" id="mapWidth" name="mapWidth" />
          </li>
             <li class="bulletLine">Map Height
            <input type="text" id="mapHeight" name="mapHeight" />
          </li>
             <li class="bulletLine hide">MapDatabase URL
            <input type="text" id="mapDatabaseURL" />
          </li>
             <li class="bulletLine">Show Custom Map
            <input type="checkbox" id="mapCustom"  value="true" name="mapCustom" />
          </li>
             <li class="bulletLine">customMap URL
            <input type="text" id="customMapURL" name="mapCustomURL" />
          </li>
             <li class="bulletLine">
            <input name="" type="submit">
          </li>
           </ul>
      </div>
         <section>
        <h2>Map Title</h2>
        <input type="text" id="mapTitleField" placeholder="New Map">
        <h2>Map Description</h2>
        <textarea id="mapDescriptionField" ></textarea>
      </section>
       </form>
  </div>
     <div id="mapTitle">
    <header></header>
    <div id="mapDescript"></div>
  </div>
     
     <!--   <ul id="tabNav">
    <li id="batchAddressTab" class="collapsed">Plot Addresses</li>
    <li id="addMarkerTab" class="collapsed">Place Marker</li> 
  </ul>--> 
   </div>
<div id="mapLegend">
     <div id="loginRegisterBox">
    <ul>
         <h2>Login</h2>
         <li>
        <input id="username" name="username" type="text" placeholder="Username">
      </li>
         <li>
        <input id="password" name="password" type="password" placeholder="Password">
      </li>
         <li class="bottomSpacer"><a id="userLogin" class="button3" href="javascript:void(0)" >Login</a> <a class="smallType" id="forgotCredentialsLink" href="javascript:void(0)" >Forgot Username/Password</a></li>
         <h2 id="registerLink"><a href="javascript:void(0)">Register</a></h2>
      <div id="registerBox">
        <li><input id="regUsername" name="regUsername" type="text" placeholder="Username"></li>
        <li><input id="regPassword" name="regPassword" type="password" placeholder="Password"></li>
        <li><input id="confirmPassword" type="password" placeholder="Confirm Password"></li>
        <li><input id="email" type="text" placeholder="Email"></li>
        <li class="bottomSpacer"><a id="register" class="button3" href="javascript:void(0)" >Register</a></li>
      </div>
         <section id="forgotCredentials">
        <h2 class="bottomSpacerHalf">Forgot Credentials</h2>
        <li >
             <label>Forgot Username</label>
             <input id="forgotUsername" type="checkbox" value="">
             <label>Forgot Password</label>
             <input id="forgotPassword" type="checkbox" value="">
           </li>
        <li class="topSpacerHalf">
             <input id="email" type="text" placeholder="Email">
           </li>
        <li><a id="forgotStuff" class="button3" href="javascript:void(0)" >Submit</a> <a class="smallType" id="hideCredentials" href="javascript:void(0)" >Hide</a></li>
      </section>
       </ul>
  </div>
     <div id="loggedInBox">
    <ul>
         <h2>My Account</h2>
         <li><a id="logOut" class="button3" href="javascript:void(0)" >Log Out</a></li>
         <li><a id="userSettings" class="button3" href="javascript:void(0)" >User Settings</a></li>
       </ul>
  </div>
     <ul id="miniNavContainer">
    <li class="miniNav"><a id="aboutMP" href="javascript:void(0)"><div></div>About</a></li>
    <li class="miniNav"><a id="blogLink"href="http://multiplottr.tumblr.com/" target="_blank"><div></div>Blog</a></li>
    <li class="miniNav"><a id="contactUsButton" href="javascript:void(0)"><div></div>Contact Us</a></li>
    <!--<li class="miniNav"><a id="helpInfo" href="javascript:void(0)"></a></li>
     <li class="miniNav"><a id="facebookIcon" target="_blank" href="http://www.facebook.com/groups/multiplottr/"></a></li>
    <li class="miniNav"><a id="twitterIcon" href="https://twitter.com/multiplottr" target="_blank"></a></li>
    <li class="miniNav"><a id="googlPlusIcon" href="https://plus.google.com/communities/113028818505618822695" target="_blank"></a></li>
    <li class="miniNav"><a id="tumblrIcon" href="http://multiplottr.tumblr.com/" target="_blank"></a></li> -->
  </ul>
  <div id="mainButtonContainer">
     <section id="editOptions">
    <header id="mapSettingsNav">
         <div class="icon"></div>
         Map Settings</header>
    <header id="myMaps">
         <div class="icon"></div>
         My Maps</header>
    </section>
    <section>
    <header  id="batchAddressTab">
         <div class="icon"></div>
         Plot Addresses</header>
    <header  id="placeMarkerHeader">
         <div class="icon"></div>
         Place<br>Marker</header>
    </section>
    <section>
    <header  id="cleanMap">
         <div class="icon"></div>
         Clear Map</header>
    <header id="saveMap">
         <div class="icon"></div>
         <span>Save Map</span>
         <div id="unfinishedMarkers"><input id="markerCount" class="hide" type="text" value="0"></div>
       </header>
       
       </section>
       </div>
       		<div class="expand" id="legendHeader">
    		<div class="expand"></div>Legend
            </div>
  
     
    <div id="legendItems">
    <h2>Groups</h2>
    <div id="groupControlDiv"> </div>
    <h2>Legend</h2>
    <div id="legendDiv" > </div>
  </div>
     <div id="adSideBar"> 
    <script type="text/javascript"><!--
google_ad_client = "ca-pub-7787442361581894";
/* Multiplottr Sidebar */
google_ad_slot = "2158987558";
google_ad_width = 234;
google_ad_height = 60;
//-->
</script> 
    <script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script></div>
<div id="socialBar">

<div class="fb-like" data-href="http://localhost:8080/" data-send="false" data-layout="button_count" data-width="225" data-show-faces="false"></div>
<!-- Place this tag where you want the +1 button to render. -->
<div class="g-plusone" data-size="medium"></div>

<!-- Place this tag after the last +1 button tag. -->
<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
</div>
   </div>
<div id="markerSelection" >
	<div class="headerContainer">
    <div class="closeBox"></div>
    	<header> Place Marker
        	
       	</header>
    	<div class="tip">Click on the map to place a marker.</div>
	</div>
	<section>
    	<h2>
        	<span class="currentMarker"></span>
            <span class="text">Default Marker</span>
		</h2>
    	<a href="javascript:void(0)" class="changeMarker">Change Default Marker</a>
    	<ul id="markerList">
         	<li><img src="file:///D|/Dropbox/InMotion/images/REDMarker.png" width="20" height="34" alt="RED"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/ORANGEMarker.png" width="20" height="34" alt="ORANGE"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/YELLOWMarker.png" width="20" height="34" alt="YELLOW"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/GREENMarker.png" width="20" height="34" alt="GREEN"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/LIMEMarker.png" width="20" height="34" alt="LIME"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/BLUEMarker.png" width="20" height="34" alt="BLUE"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/CYANMarker.png" width="20" height="34" alt="CYAN"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/VIOLETMarker.png" width="20" height="34" alt="VIOLET"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/PURPLEMarker.png" width="20" height="34" alt="PURPLE"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/PINKMarker.png" width="20" height="34" alt="PINK"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/WHITEMarker.png" width="20" height="34" alt="WHITE"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/GREYMarker.png" width="20" height="34" alt="GREY"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/BLACKMarker.png" width="20" height="34" alt="BLACK"></li>
         	<li><img src="file:///D|/Dropbox/InMotion/images/NUMBERMarker.png" width="20" height="34" alt="NUMBER"></li>
       	</ul>
    <div class="container">
         <input id="markerName" type="text" class="hide" >
         <textarea id="tinyMCEHolder" class="hide"></textarea>
         <ul>
        <span id="customizedMarker">
           <li>
          <input id="markerURL" type="text" placeholder="Enter a customized marker URL">
        </li>
           <li><a id="addNewMarkerURL" class="button3" href="javascript:void(0)" >Add New Marker URL</a></li>
           </span>
        <li>
             <h2>Default Marker Group</h2>
           </li>
        <li>
             <select id='allGroupNames'>
          </select>
           </li>
        <li>
             <input id="currentGroupName" type="text">
           </li>
           <section>
        <li>
             <h2>Marker Header</h2>
           </li>
        <li>
             <input id="markerHeader" name="Marker Header" type="text" placeholder="Enter Marker Header">
           </li>
        <li>
             <h2>Marker Description</h2>
           </li>
        <li>
             <textarea id="markerTextArea" cols="" rows=""></textarea>
           </li>
           </section>
      </ul>
       </div>
    <ul id="addAddress">
         <li class="hide">
        <input id="markerAddress" type="text" placeholder="Marker Address">
      </li>
         <li class="hide"><a id="geoCodeAddress" class="button3" href="javascript:void(0)" >Plot Address</a></li>
         <li class="hide"><a id="placeMarker"    class="button3 hide" href="javascript:void(0)" >
           <input id="plotMarker" type="checkbox" value="">
           Place Marker </a></li>
         <li><a id="zoomView"     class="button3" href="javascript:void(0)" >Save Zoom & View</a></li>
         <li><a id="updateMarker" class="button4 buttonA" href="javascript:void(0)" >Update Marker</a></li>
         <li><a id="submitMarker" class="button4 buttonA" href="javascript:void(0)" >Submit Marker</a></li>
       </ul>
  </section>
   </div>
        <div id="quickSearch"><a href="javascript:void(0)">Plot</a><textarea class="force35pxheight" placeholder="Plot Multiple Addresses. One address per line."></textarea></div>
<div id="mapContainer"> <a name="maptop" id="maptop"></a>
<div id="introText">
     <h1>
    <div class="closeBox"></div>
    Multiplottr 3.0 Beta</h1>
     <div class="content">
    <p>Multiplottr allows you to map, save and share multiple addresses on Google Maps. </p>
    <p> Customization is easy and on the fly!</p>
    <p>You have the ability to define each markers's description with text, images, hyperlinks and video, use your own customized markers and maps and assign markers to groups for easy on and off  batch viewing.</p>
    <p>A good place to begin is to click on <b>PLOT ADDRESSES</b> on the right and start plotting addresses. Or you can click on <b>PLACE MARKERS</b> and you can start adding markers with a simple click on the map.</p>
    <p>Happy Plotting!</p>
    <ul>
         <li>Latest News</li>
         <li>Follow Us</li>
         <li>Tutorials</li>
       </ul>
  </div>
   </div>
   <div id="myMapsBox">
     <h1>
    <div class="closeBox"></div>
    My Maps</h1>
     <ol>
    <li>
         <ul class="headerList">
        <li class="title">Title</li>
        <li class="date">Last Save</li>
        <li class="delete"></li>
      </ul>
         <section>
           
         </section>
       </li>
  </ol>
   </div>
   <div id="userAccountBox">
   <h1>
    <div class="closeBox"></div>
    My Account</h1>
    <ul class="content">
    <li id="userName"><label>Username</label>Guest</li>
    <li><label>Password</label><a href="javascript:void(0)"id="changePassword" >Change</a></li>
    <form id="changePasswordForm">
    <li><input id="chngPw" name="regPassword" type="password" placeholder="Password"></li>
    <li><input id="confirmChngPw" type="password" placeholder="Confirm Password"></li>
    <li><input type="submit"></li>
    </form>
    <li><label>Email</label><a href="javascript:void(0)" id="changeEmail">[REDACTED_EMAIL]</a></li>
    
    <form id="changeEmailForm">
    <li><input id="chngEml" type="text" placeholder="Email"></li>
    <li><input id="confirmChngEml" type="text" placeholder="Confirm Email"></li>
    <li><input type="submit"></li>
    </form>
  </ul>
   </div>
   <div id="contactUsBox">
     <h1>
    <div class="closeBox"></div>
    Contact us</h1>
   <div class="content">
   <div id="wufoo-z7x4m1">
Fill out my <a href="http://coreykliewer.wufoo.com/forms/z7x4m1">online form</a>.
</div>
<script type="text/javascript">var z7x4m1;(function(d, t) {
var s = d.createElement(t), options = {
'userName':"[REDACTED_CONFIG_VALUE]", 
'formHash':'z7x4m1', 
'autoResize':true,
'height':'437',
'async':true,
'header':'hide'};
s.src = ('https:' == d.location.protocol ? 'https://' : 'http://') + 'wufoo.com/scripts/embed/form.js';
s.onload = s.onreadystatechange = function() {
var rs = this.readyState; if (rs) if (rs != 'complete') if (rs != 'loaded') return;
try { z7x4m1 = new WufooForm();z7x4m1.initialize(options);z7x4m1.display(); } catch (e) {}};
var scr = d.getElementsByTagName(t)[0], par = scr.parentNode; par.insertBefore(s, scr);
})(document, 'script');</script>
   </div>
   </div>
   
     <div id="overMapAdContainer"> <a  class="closeAd">close</a>
    <div class="blockedAd">Advertisement Blocked</div>
    <script type="text/javascript"><!--
google_ad_client = "ca-pub-7787442361581894";
/* 728x90, created 4/15/10 */
google_ad_slot = "6521200948";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script> 
    <script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script> 
  </div>
     <?
	 
	 
require_once 'mpcMain07302013.php';

$map = new MultiplottrMap();

$width = $_GET['mapWidth'];
$height = $_GET['mapHeight'];
$zoomy = $_GET['mapZoom'];
$metroFile = $_GET['mapDatabase'];
$myMaxZoom = $_GET['maxZoom'];
$myMinZoom = $_GET['minZoom'];
$myControl = $_GET['mapControl'];
$myCustomMap = $_GET['mapCustom'];
$myCustomMapURL = $_GET['mapCustomURL'];
$myCustomFormat = $_GET['mapCustomFormat'];
$myType = $_GET['mapType'];
$myOverview = $_GET['mapOverview'];
$myControlSize = $_GET['mapControlSize'];
$myLegend = $_GET['mapLegend'];
$myDatabase = $_GET['mapDatabase'];
$myGroup = $_GET['mapGroup'];
$infoWindowSize = '300';


$bunchOaddresses = $_POST['markerLocationField'];
$myDefaultMarker = $_POST['MarkerColor1'];
$mapTitle = $_POST['mapTitleFieldSubmit'];
$mapDescription = $_POST['mapDescriptionFieldSubmit'];

$myDefaultGroupName = $_POST['groupname1'];

$term = $_SERVER['HTTP_REFERER'];

if (($width=='0')||($width=='')) {$width='100%';}
if (($height=='0')||($height=='')) {$height='100%';}
if ($zoomy == '') {$zoomy = '11';}
if ($myMaxZoom < 0 || $myMaxZoom > 19 || $myMaxZoom == '') {$myMaxZoom = 19;}
if ($myMinZoom < 0 || $myMinZoom > 19 || $myMinZoom == '') {$myMinZoom = 2;}
if ($myCustomFormat != 'jpg') {$myCustomFormat = 'png';} 


$map->setWidth($width);
$map->setHeight($height);
$map->setZoom($zoomy);
$map->setMinZoom($myMinZoom);
$map->setMaxZoom($myMaxZoom);
$map->setControlSize($myControlSize);
$map->setMapCustomURL($myCustomMapURL);
$map->setMapCustomFormat($myCustomFormat);
$map->setMapTitle($mapTitle);



if ($myControl=='false')  {$map->setControl(false);} else {$map->setControl(true);}
if ($myCustomMap=='true') {$map->setCustomMap(true);} else {$map->setCustomMap(false);}
if ($myType=='false')     {$map->setType(false);} else {$map->setType(true);}
if ($myOverview=='true') {$map->setOverview(true);} else {$map->setOverview(false);}
if ($myLegend=='false')   {$map->setLegend(false);} else {$map->setLegend(true);}
	 
	 
 function nl2brStrict($text, $replacement = '`')
{
   return preg_replace("((\r\n)+)", trim($replacement), $text);
}

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 
 $ch = curl_init($pageURL);
curl_exec($ch);
 
}








function writeToFlatFile($i) {
	$newcontents = $_POST["markerLocationField"];
	if (isset($_POST['markerLocationField'])) {
    	$fp = fopen($i,"w");
    	if(!$fp) {
			echo 'Error: Cannot open file.';
			exit;
		}
		fwrite($fp, $newcontents);
		fclose($fp);	
	}
}


//if (substr($myDatabase, 0, 1)=="da") {writeToFlatFile();}

// $bunchOaddresses is from $_POST['markerLocationField'];

if ($bunchOaddresses <> null) {
	if (substr($myDatabase, 0, 2)=="da") {
		writeToFlatFile($myDatabase);
	} else {
		$myStr = nl2brStrict($bunchOaddresses);
		$myStr = str_replace("\t", " ", $myStr);
		$lines = array();
		$lines = explode("`", $myStr);
		$lineCount = count($lines);
		for ($i=0; $i<$lineCount; $i++)  {
  			list ($field1, $field2, $field3, $field4) = split ('\|', $lines[$i]);
  			$map->addAddress($field1,$field2,$field3,$field4);
  			$line = $lines[$i];
  			$line = htmlspecialchars($line, ENT_QUOTES);
			$line = preg_replace('/\p{C}+/u', '\\n', $line);
  			echo "
			<script type='text/javascript'>
      		$(document).ready(function(){
				var line = '".$line."'; 
				if (line !== '\\n') {
        			$('#markerLocationField').append('".$line."\\n');	
					dataArray.push('".$line."')
					dataArrayCount++
		 		}
			});
   			</script>
			";
  		}
  	}
}

// $myDatabase is from flat file database

if ($myDatabase<>"") {
	$fp = fopen($myDatabase,'r');
	if (!$fp) {echo 'ERROR: Unable to open file.'; exit;}
	while(!feof($fp))
	{
		$line = fgets($fp,1024); //use 2048 if very long lines
		list ($field1, $field2, $field3, $field4) = split ('\|', $line);
		//	$addressField = nl2brStrict2($field2);
		//$field2 = htmlspecialchars($field2, ENT_QUOTES);
		$map->addAddress($field1,$field2,$field3,$field4);
		$line = htmlspecialchars($line, ENT_QUOTES);
		$line = preg_replace('/\p{C}+/u', '\\n', $line);
				
		echo "
		<script type='text/javascript'>
		loadLocalStorage=false;
      	$(document).ready(function(){
			var line = '".$line."'; 
			if (line !== '\\n') {
        		$('#markerLocationField').append('".$line."');	
				dataArray.push('".$line."')
				dataArrayCount++
		 	}
		});
   		</script>
		";
	}
	fclose($fp);
} 

if (($bunchOaddresses == null)&&($myDatabase == null)) {$map->addAddress('~15.623036831528264,-42.890625','3','CENTER','CENTER');}




  $map->showMap();
  ?>
   </div>
</body>
   <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-75352-7', 'onlinecustommaps.com');
  ga('send', 'pageview');

</script>
</html>