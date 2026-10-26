<!doctype html>
<html>
   <head>
   <meta charset="utf-8">
   <title>Multiplottr: Plot, Save and Share.</title>
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
   <script type="text/javascript" src="../scripts/tiny_mce/tiny_mce.js"></script>
   <script type="text/javascript" src="../scripts/_advertisement.js"></script>
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


   
function runSubmit() {
	var theseGeoCodedAddresses = $('#markerLocationField').val();
	theseGeoCodedAddresses = theseGeoCodedAddresses.replace(/((\r\n|\r|\n)\s*)\2/g, "$2") ;
	$('#markerLocationField').val(theseGeoCodedAddresses);
	localStorage['geoCodedAddresses'] = $('#markerLocationField').val();
	//
	$('#mapTool').submit();
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
			geoCoded.push(geoCodeListArray[i]+'\n');
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
						runSubmit();
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
		
	}
}
  
  
  
  
$(document).ready(function(){
	
$("#progressbar").progressbar({value: 0});
$("#loginRegisterBox").hide();
$('#currentGroupName').val(currentGroupName);
$('#currentGroupNameDisplay').text(currentGroupName);
$('#markerName').val('RED');
$('#markerList li img[alt=RED]').addClass('selected');
$('#markerSelection').toggle();
$('#updateMarker').hide();




		
	$('#batchAddressTab,#mapTitle').live('click', function(){ 
	    // if ($('#addMarkerTab').hasClass('expanded')) {
		//	$('#addMarkerTab').removeClass('expanded');
		//	$('#addMarkerTab').addClass('collapsed');
		//	$('#placeMarkers').hide();
		//	$('#addMarkerTab').removeClass("moveUpOnePx");
		//}
		if ($('#mapSettings').is(':visible')) { $('#mapSettings').hide(); }
	
	    if ($('#batchAddressTab').hasClass('expanded')) {
			$('#batchAddressTab').removeClass('expanded');
			$('#batchAddressTab').addClass('collapsed');
			$('#batchAddress').slideUp("fast");
			$('#BatchAddressTab').removeClass("moveUpOnePx");
		} else {
			$('#batchAddressTab').removeClass('collapsed');
			$('#batchAddressTab').addClass('expanded');
			$('#batchAddress').slideDown("fast");
			src = $('#markerSelection section ul li .selected').attr('src');
			$('#currentMarker').html('<img src='+src+' alt="Current Marker">');

		}
 	});
	
	$('#addMarkerTab').live('click', function(){ 
	    if ($('#addMarkerTab').hasClass('expanded')) {
			$('#addMarkerTab').removeClass('expanded');
			$('#addMarkerTab').addClass('collapsed');
			//$('#markerSelection').hide();
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
	
	$('#mapSettingsNav').live('click', function(){ 
	   if ($('#mapSettings').is(':visible')) { $('#mapSettings').slideUp("fast"); } else {
		   if ($('#batchAddressTab').hasClass('expanded')) {
			$('#batchAddressTab').removeClass('expanded');
			$('#batchAddressTab').addClass('collapsed');
			$('#batchAddress').slideUp("fast");
			$('#BatchAddressTab').removeClass("moveUpOnePx");
		}
		    $('#mapSettings').slideDown("fast"); }
 	});
 
	
	
	$('#markerSelCollapse').live('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		//$('#markerSelection section').addClass('Hide');
 	});
 
	$('#markerSelExpand').live('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		//$('#markerSelection section').removeClass('Hide');
 	});
 
 	$('#markerList li img').live('click', function(){ 
 		$('#markerList li img').removeClass('selected');
		$('#markerName').val($(this).attr('alt'));
		$(this).addClass("selected");
		$('#currentMarker').html('<img src='+$(this).attr('src')+' alt="Current Marker">');
		
	});
	
	
	
	$('#legendHeader').live('click', function() {
		if ($('#legendItems').is(':visible')) {$('#legendItems').slideUp("fast");} else {$('#legendItems').slideDown("fast");}
		});
	
	$('#placeMarkerHeader').live('click', function() {
		//setupMarkerTextArea();

		//tinyMCE.activeEditor.setContent('');
		tinyMCE.get('markerTextArea').setContent('');
		if ($('#markerSelection').is(':visible')) {
	//		$('#markerSelection').hide("slide", { direction: "right" }, 200);
			$('#markerSelection').hide();

			$('#plotMarker').prop('checked', false);
			} else
	{ 
	$('#markerSelection').show();
		//$('#markerSelection').show("slide", { direction: "right" }, 200);
			$('#plotMarker').prop('checked', true);
			//tinyMCE.execCommand('mceToggleEditor', true, 'markerTextArea');
			//tinyMCE.execCommand('mceAddControl',false,'markerTextArea');
			//tinyMCE.execCommand('mceToggleEditor', true, 'markerTextArea');
			
	//		tinyMCE.get('markerTextArea').setContent('test');
	//		tinyMCE.get('mapDescriptionField').setContent('test2');
		}
		});
		
	$('#markerSelection .closeBox').live('click', function() {
		//$('#markerSelection').hide("slide", { direction: "right" }, 200);
		//tinyMCE.execCommand('mceRemoveControl',false,'markerTextArea');
		$('#markerSelection').hide();
	});
	
	
	$('#placeMarker').live('click', function() {
		if ( $('#plotMarker').prop("checked") ) {
			$('#plotMarker').prop('checked', false);
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
	$("#loginRegisterBox").toggle();
		});
		
	

$('#forgotCredentialsLink, #hideCredentials').live('click', function() {
	$("#forgotCredentials").toggle();
		});	
		
		
$('#saveMap').live('click', function() {
    localStorage['loadMap']='true';
	runSubmit();
});			

$('#overMapAdContainer .closeAd').live('click', function() {
    $("#overMapAdContainer").hide();
});		

	
	
	
function init() {
	
        if ((localStorage['geoCodedAddresses']) && (loadLocalStorage)) {
			//alert(localStorage['loadMap']);
            $('#markerLocationField').val(localStorage['geoCodedAddresses']);
			if (localStorage['loadMap']=='true') {
				localStorage['loadMap']='false';
				runSubmit();
			}
        }
        
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
	<div id="modalBack"></div>
	<div id="topBannerNav" class="gradient">
    	
        <img src="images/multiplottrLogo.png" width="210" height="50" alt="Multiplottr">
        <h1><span>Multiplottr. </span>Plot, save and share multiple addresses with an easy to create customized map.</h1><a href="javascript:void(0)" id="loginRegister"  href="javascript:void(0)">Login | Register</a>
	</div>
	<div id="popOutWrapper" >
		<div id="batchAddress">
    		<div class="content">
         		<h2>Add Addresses</h2>
         		<p>Place each address on its own line and hit submit. Multiplottr will process the addresses and geocode them to work with the Multiplottr format.</p>
         		<p>Progress: <span id="numOfAddresses"></span></p>
         		<div id="progressbar"></div>
         <ul id="defaultSettings" class="buttonHolder" >
        <li><span id="currentMarker"></span>
             <label>Default Marker:</label>
           </li>
        <li>
             <label>Default Group: </label>
             <span id="currentGroupNameDisplay"></span></li>
        <li><a id="batchGeocode" class="button2" href="javascript:void(0)" >Plot Addresses</a></li>
<!-- <a id="plotTheseAddresses" class="button2" href="javascript:void(0)">Plot Addresses</a>-->
      </ul>
       </div>
    <form id="batchAddresses" method="post">
         <section>
        <textarea id="batchAddressesField" name="batchAddressesField"  placeholder="Enter each address 1 line at a time. Unregistered users can plot 25 addresses at a time."></textarea>
      </section>
       </form>
    <form  id="mapTool" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
         <section>
        <textarea id="markerLocationField" name="markerLocationField"></textarea>
      </section>
    </form>
  </div>
  <div id="mapSettings">
  <form id="urlAttributes" method="post">
  	<div class="content">
    <ul >
                  <li class="bulletLine">Show Zoom Control
                    <input id="mapControl" name="mapControl" type="checkbox" value="true" checked="checked" onclick="switchClass('saveMapBox','hide');" />
                  </li>
                  <li class="bulletLine">Zoom Control Type
                    <select name="mapControlSize" class="smallerFormField" id="mapControlSize" onfocus="switchClass('saveMapBox','hide');" >
                      <option>small</option>
                      <option selected="selected">large</option>
                    </select>
                  </li>
                  <li class="bulletLine">Show Map Type
                    <input type="checkbox" id="showMapType" name="mapType" value="true" checked="checked" onclick="switchClass('saveMapBox','hide');" />
                  </li>
                  <li class="bulletLine">Show Map Overview
                    <input type="checkbox" id="showMapOverview" name="mapOverview" value="true" checked="checked" onclick="switchClass('saveMapBox','hide');" />
                  </li>
                  <li class="bulletLine">Show Legend
                    <input type="checkbox" id="showMapLegend" name="mapLegend" value="true" checked="checked" onfocus="switchClass('saveMapBox','hide');" />
                  </li>
                  <li class="bulletLine">Show Groups
                    <input type="checkbox" id="showMapGroup" name="mapGroup" value="true" checked="checked" onclick="switchClass('saveMapBox','hide');"  />
                  </li>
                </ul>
    </div>
    <section><h2>Map Title</h2>
    <input type="text" id="mapTitleField">
    <h2>Map Descriptiont</h2>
    <textarea id="mapDescriptionField" ></textarea>
    </section>
    </form>
  </div>
     <div id="mapTitle">
    <header>Map Title</header>
    <div id="description"></div>
  </div>
     
     <!--   <ul id="tabNav">
    <li id="batchAddressTab" class="collapsed">Plot Addresses</li>
    <li id="addMarkerTab" class="collapsed">Place Marker</li> 
  </ul>--> 
   </div>
<div id="mapLegend">
    <ul id="miniNavContainer">
    	<li class="miniNav"><a href="javascript:void(0)">About</a></li>
        <li class="miniNav"><a href="http://multiplottr.tumblr.com/" target="_blank">Blog</a></li>
        <li class="miniNav"><a id="contactUsButton" href="javascript:void(0)"></a></li>
        <li class="miniNav"><a id="helpInfo" href="javascript:void(0)"></a></li>
        <li class="miniNav"><a id="mapSettingsNav" href="javascript:void(0)"></a></li>
        <li class="miniNav"><a id="facebookIcon" target="_blank" href="http://www.facebook.com/groups/multiplottr/"></a></li>
        <li class="miniNav"><a id="twitterIcon" href="https://twitter.com/multiplottr" target="_blank"></a></li>
       	<li class="miniNav"><a id="googlPlusIcon" href="https://plus.google.com/communities/113028818505618822695" target="_blank"></a></li>
        <li class="miniNav"><a id="tumblrIcon" href="http://multiplottr.tumblr.com/" target="_blank"></a></li>
    </ul>
     <div id="loginRegisterBox">
    <div class="container">
         <ul>
        <h2>Login</h2>
        <li>
             <input id="username" type="text" placeholder="Username">
           </li>
        <li>
             <input id="password" type="password" placeholder="Password">
           </li>
        <li class="bottomSpacer"><a id="login" class="button3" href="javascript:void(0)" >Login</a> <a class="smallType" id="forgotCredentialsLink" href="javascript:void(0)" >Forgot Username/Password</a></li>
        <h2>Register</h2>
        <li>
             <input id="confirmPassword" type="password" placeholder="Confirm Password">
           </li>
        <li>
             <input id="email" type="text" placeholder="Email">
           </li>
        <li class="bottomSpacer"><a id="register" class="button3" href="javascript:void(0)" >Register</a></li>
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
  </div>
<section id="editOptions">
    <header id="batchAddressTab">Plot Addresses</header>
    <header id="placeMarkerHeader">Place Marker</header>
    <header id="saveMap">Submit Map</header>

  </section>
    <header id="legendHeader">Legend</header>
    <section id="legendItems">
    <h2>Groups</h2>
    <div id="groupControlDiv"> </div>
    <h2>Legend</h2>
    <div id="legendDiv" > </div>
  </section>
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
   </div>
   
<div id="mapContainer">
<div id="markerSelection" >
<div class="closeBox"></div>
<div class="tip">Click on the map to add a marker.</div>
    <section>
         <h2>Default Marker</h2>
         <ul id="markerList">
        <li><img src="../images/REDMarker.png" width="20" height="34" alt="RED"></li>
        <li><img src="../images/BLUEMarker.png" width="20" height="34" alt="BLUE"></li>
        <li><img src="../images/CYANMarker.png" width="20" height="34" alt="CYAN"></li>
        <li><img src="../images/GREENMarker.png" width="20" height="34" alt="GREEN"></li>
        <li><img src="../images/GREYMarker.png" width="20" height="34" alt="GREY"></li>
        <li><img src="../images/LIMEMarker.png" width="20" height="34" alt="LIME"></li>
        <li><img src="../images/ORANGEMarker.png" width="20" height="34" alt="ORANGE"></li>
        <li><img src="../images/PURPLEMarker.png" width="20" height="34" alt="PURPLE"></li>
        <li><img src="../images/VIOLETMarker.png" width="20" height="34" alt="VIOLET"></li>
        <li><img src="../images/YELLOWMarker.png" width="20" height="34" alt="YELLOW"></li>
      </ul>
         <div class="container">
        <input id="markerName" type="text" class="hide" >
        <textarea id="tinyMCEHolder" class="hide"></textarea>
        <ul >
             <li>
            <input id="markerURL" type="text" placeholder="Enter a customized marker URL">
          </li>
             <li><a id="addNewMarkerURL" class="button3" href="javascript:void(0)" >Add New Marker URL</a></li>
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
           </ul>
      </div>
         <ul id="addAddress">
        <li>
             <input id="markerAddress" type="text" placeholder="Marker Address">
           </li>
        <li><a id="geoCodeAddress" class="button3" href="javascript:void(0)" >GeoCode Marker</a></li>
        <li><a id="placeMarker"    class="button3" href="javascript:void(0)" >
          <input id="plotMarker" type="checkbox" value="">
          Place Marker </a></li>
        <li><a id="updateMarker" class="button3" href="javascript:void(0)" >Update Marker</a></li>
      </ul>
       </section>
  </div>
   <a name="maptop" id="maptop"></a>
<div id="overMapAdContainer">
<a  class="closeAd">close</a>
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
	 
	 
require_once 'mpcMain.php';

$map = new MultiplottrMap();

$width = $_GET['mapWidth'];
$height = $_GET['mapHeight'];
$zoomy = $_GET['mapZoom'];
$metroFile = $_GET['mapDatabase'];
$myControl = $_GET['mapControl'];
$myCustomMap = $_GET['mapCustom'];
$myType = $_GET['mapType'];
$myOverview = $_GET['mapOverview'];
$myControlSize = $_GET['mapControlSize'];
$myLegend = $_GET['mapLegend'];
$myDatabase = $_GET['mapDatabase'];
$myGroup = $_GET['mapGroup'];
$infoWindowSize = '300';


$bunchOaddresses = $_POST['markerLocationField'];
$myDefaultMarker = $_POST['MarkerColor1'];
$myDefaultGroupName = $_POST['groupname1'];

$term = $_SERVER['HTTP_REFERER'];

if (($width=='0')||($width=='')) {$width='100%';}
if (($height=='0')||($height=='')) {$height='100%';}
if ($zoomy == '') {$zoomy = '11';}



$map->setWidth($width);
$map->setHeight($height);
$map->setZoom($zoomy);
$map->setControlSize($myControlSize);
if ($myControl=='false')  {$map->setControl(false);} else {$map->setControl(true);}
if ($myCustomMap=='true') {$map->setCustomMap(true);} else {$map->setCustomMap(false);}
if ($myType=='false')     {$map->setType(false);} else {$map->setType(true);}
if ($myOverview=='false') {$map->setOverview(false);} else {$map->setOverview(true);}
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

function writeToFlatFile () {
	$newcontents = $_POST["markerLocationField"];
	if (isset($_POST['markerLocationField'])) {
    	$fp = fopen("datafiles/sample.txt","w");
    	if(!$fp) {
			echo 'Error: Cannot open file.';
			exit;
		}
		fwrite($fp, $newcontents);
		fclose($fp);	
	}
}



if ($bunchOaddresses <> null) {
$myStr = nl2brStrict($bunchOaddresses);
$myStr = str_replace("\t", " ", $myStr);
$lines = array();
$lines = explode("`", $myStr);
$lineCount = count($lines);
for ($i=0; $i<$lineCount; $i++)
  {
	
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

if (($bunchOaddresses == null)&&($myDatabase == null)) {$map->addAddress('~0,0','3','CENTER','CENTER');}


  $map->showMap();
  ?>
  
  
   </div>
</body>
   <!--Google Analytics -->
   <script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
   <script type="text/javascript">
_uacct = "UA-75352-2";
urchinTracker();
</script>
</html>