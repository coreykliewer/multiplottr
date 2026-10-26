<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<?php

/**
* Based on Phoogle Maps 2.0
* Uses Google Maps Mapping API to create customizable
* Google Maps that can be embedded on your website
*
* Modified by Corey L. Kliewer - [REDACTED_EMAIL]
* www.multiplotter.com
*/

error_reporting(0);


class MultiplottrMap{
/**
* validPoints : array
* Holds addresses and HTML Messages for points that are valid (ie: have longitude and latitutde)
*/
    var $validPoints = array();
/**
* invalidPoints : array
* Holds addresses and HTML Messages for points that are invalid (ie: don't have longitude and latitutde)
*/
    var $invalidPoints = array();
/**
* mapWidth
* width of the Google Map, in pixels
*/
    var $mapWidth = "";
/**
* mapHeight
* height of the Google Map, in pixels
*/
    var $mapHeight = "";

/**
* apiKey
* Google API Key
*/
    var $apiKey = "[REDACTED_CONFIG_VALUE]";

/**
* showControl
* True/False whether to show map controls or not
*/
    var $showControl = false;
	
/**
* showType
* True/False whether to show map type controls or not
*/
    var $showType = false;
	
/**
* showOverview
* True/False whether to show overview map control or not
*/
    var $showOverview = false;
	
	
	
	




/**
* controlType
* string: can be 'small' or 'large'
* display's either the large or the small controls on the map, small by default
*/

    var $controlType = 'small';
    

    var $zoomLevel = '11';
	
	var $zoomy = '11';
	var $smallMarker = false;
	var $myPolyline=false;
	
	
	var $arrayLocations = array();
	

/**
* @function     addGeoPoint
* @description  Add's an address to be displayed on the Google Map using latitude/longitude
*               early version of this function, considered experimental
*/

function addGeoPoint($lat,$long,$infoHTML){
    $pointer = count($this->validPoints);
        $this->validPoints[$pointer]['lat'] = $lat;
        $this->validPoints[$pointer]['long'] = $long;
        $this->validPoints[$pointer]['htmlMessage'] = $infoHTML;
    }
	
function setDefaultMarker($myDefaultMarker){$this->defaultMarker = $myDefaultMarker;}
function setDefaultGroupName($myDefaultGroupName){$this->defaultGroupName = $myDefaultGroupName;}
    
/**
* @function     centerMap
* @description  center's Google Map on a specific point
*               (thus eliminating the need for two different show methods from version 1.0)
*/


	

function centerMap($lat,$long,$zoomLevel){
    $this->centerMap = "map.setCenter(new GLatLng(".$lat.",".$long."), ".$zoomLevel.");\n";
	
}
   
 /**
* @function     get_string_between
* @description  Gets text between <legend></legend> tags
*               (thus eliminating the need for two different show methods from version 1.0)
*/    
  
  function get_string_between($string, $start, $end){
     $ini = strpos($string,$start);
     $ini += strlen($start);    
     $len = strpos($string,$end,$ini) - $ini;
     return substr($string,$ini,$len);
}   
   
function setHowManyPlotted(){
    $this->howManyPlotted = '0';
    }
	
function setTotalPlotted(){
    $this->totalPlotted = '0';
    }	


    function fetchURL($url) {
        return file_get_contents($url);

    }   
   
    
    
/**
* @function     addAddress
* @param        $address:string
* @returns      Boolean True:False (True if address has long/lat, false if it doesn't)
* @description  Add's an address to be displayed on the Google Map
*               (thus eliminating the need for two different show methods from version 1.0)
*/
function addAddress($address,$htmlMessage,$newMarkerColor, $groupName){
	 $groupName = htmlspecialchars($groupName);
	 if ($groupName == ''){$groupName = 'group0';}
	 $longiLat = false;
	 $errorBig=false;
	 $oldaddress=$address;
	 if (!is_string($address)){
		die("All Addresses must be passed as a string");
	  }
	 if (substr($address, 0, 1)=="~")
	 	{
		$myPolyline=true;
		$address = str_replace(array("~", " "), "", $address);
		list ($myLatitude, $myLongitude) = split (',', $address);
		$longiLat = true;
		
		//setting arrayLocations for editting a text doc - Custom DAYZ
		
		$locationsFields = $address."|".$htmlMessage."|".$newMarkerColor."|".$groupName;
		array_push($arrayLocations, $locationsFields);
		
		
		}
	  else if (!$longiLat) {
	    $address = urlencode($address);
		//$url = "http://maps.google.com/maps/geo?q=".$address."&output=csv&key=".$this->apiKey;

	    $url = "http://maps.google.com/maps/geo?q=".$address."&output=csv&key=[REDACTED_TOKEN]";
		$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER,0); //Change this to a 1 to return headers
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER[�HTTP_USER_AGENT�]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$mapdata = curl_exec($ch);
curl_close($ch);

if (strstr($mapdata,"200"))
{
$mapdata = explode(",",$mapdata);

$precision = $mapdata[1];
$newLatitude = $mapdata[2];
$newLongitude = $mapdata[3];

} else {$errorBig=true;}
}


	
		
			$htmlMessage = str_replace(array("\r", "\n"), "", $htmlMessage);
			$newMarkerColor = str_replace(array("\r", "\n", " ",), "", $newMarkerColor);
			$firstFour = substr($newMarkerColor, 0, 4);
		    $firstFour = strtoupper($firstFour);
			//$newMarkerColor = strtoupper($newMarkerColor);
			$groupName = str_replace(array("\r", "\n"), "", $groupName);
			$colorNumber = 0;
			if (strcmp($firstFour, "HTTP")!==0) {
		    	$newMarkerColor = strtoupper($newMarkerColor);
		    }
			
			
			
		if (strcmp($newMarkerColor, "BLUE") == 0) {$colorNumber=1; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "GREEN") == 0) {$colorNumber=2; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "GREY") == 0) {$colorNumber=3; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "ORANGE") == 0) {$colorNumber=4; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "RED") == 0) {$colorNumber=5; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "YELLOW") == 0) {$colorNumber=6; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLBLUE") == 0) {$colorNumber=7; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLGREEN") == 0) {$colorNumber=8; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLGREY") == 0) {$colorNumber=9; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLORANGE") == 0) {$colorNumber=10; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLRED") == 0) {$colorNumber=11; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLYELLOW") == 0) {$colorNumber=12; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 4)=="LINE") {$colorNumber=13;}
		if (strcmp($newMarkerColor, "CENTER") == 0) {$colorNumber=14;}
		if (strcmp($newMarkerColor, "DOT") == 0) {$colorNumber=15; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 6)=="NUMBER") {$colorNumber=16; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 6)=="CIRCLE") {$colorNumber=20; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 4)=="MINI") {$colorNumber=22; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($firstFour, "HTTP")==0) {$colorNumber=21; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 7)=="POLYGON") {$colorNumber=17;}
		if (substr($newMarkerColor, 0, 10)=="PUSHPINRED") {$colorNumber=18; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
	if (substr($newMarkerColor, "HEATRED")=="HEATRED") {$colorNumber=19; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 4)=="LIN2") {$colorNumber=23;}
		if (strcmp($newMarkerColor, "CYAN") == 0) {$colorNumber=24; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "LIME") == 0) {$colorNumber=25; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "VIOLET") == 0) {$colorNumber=26; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "PURPLE") == 0) {$colorNumber=27; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if ($colorNumber == 0) {$newMarkerColor = "RED"; $htmlMessage="";  $groupName="group0";}
		if ($longiLat) {
			$pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $myLatitude;
			$this->validPoints[$pointer]['long']= $myLongitude;
			$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
			$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
			$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
			$this->validPoints[$pointer]['groupName'] = $groupName;



		    }
		else
				
		if ($errorBig) {
				
			$apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=YahooDemo&street=";
			$addressData = file_get_contents($apiURL.urlencode($oldaddress));
			$results = $this->xml2array($addressData);
			if (empty($results['ResultSet']['Result']['Longitude'])){
				}
			else {
			    $pointer = count($this->validPoints);
				$this->validPoints[$pointer]['lat']= $results['ResultSet']['Result']['Latitude'];
				$this->validPoints[$pointer]['long']= $results['ResultSet']['Result']['Longitude'];
				$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
				$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
				$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
				$this->validPoints[$pointer]['groupName'] = $groupName;

				}		
		}	
		else {
			$pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $newLatitude;
			$this->validPoints[$pointer]['long']= $newLongitude;
			$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
			$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
			$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
			$this->validPoints[$pointer]['groupName'] = $groupName;

			}
			
	}
	
	
	
	
/**
* @function     showValidPoints
* @param        $displayType:string
* @param        $css_id:string
* @returns      nothing
* @description  Displays either a table or a list of the address points that are valid.
*               Mainly used for debugging but could be useful for showing a list of addresses
*               on the map
*/
	function showValidPoints($displayType,$css_id){
    $total = count($this->validPoints);
      if ($displayType == "table"){
        echo "<table id=\"".$css_id."\">\n<tr>\n\t<td>Address</td>\n</tr>\n";
        for ($t=0; $t<$total; $t++){
            echo"<tr>\n\t<td>".$this->validPoints[$t]['passedAddress']."</td>\n</tr>\n";
        }
        echo "</table>\n";
        }
      if ($displayType == "list"){
        echo "<ul id=\"".$css_id."\">\n";
      for ($lst=0; $lst<$total; $lst++){
        echo "<li>".$this->validPoints[$lst]['passedAddress']."</li>\n";
        }
        echo "</ul>\n";
       }
	}
/**
* @function     showInvalidPoints
* @param        $displayType:string
* @param        $css_id:string
* @returns      nothing
* @description  Displays either a table or a list of the address points that are invalid.
*               Mainly used for debugging shows only the points that are NOT on the map
*/
	function showInvalidPoints($displayType,$css_id){
      $total = count($this->invalidPoints);
      if ($displayType == "table"){
        echo "<table id=\"".$css_id."\">\n<tr>\n\t<td>Address</td>\n</tr>\n";
        for ($t=0; $t<$total; $t++){
            echo"<tr>\n\t<td>".$this->invalidPoints[$t]['passedAddress']."</td>\n</tr>\n";
        }
        echo "</table>\n";
        }
      if ($displayType == "list"){
        echo "<ul id=\"".$css_id."\">\n";
      for ($lst=0; $lst<$total; $lst++){
        echo "<li>".$this->invalidPoints[$lst]['passedAddress']."</li>\n";
        }
        echo "</ul>\n";
       }
	}
	
	
	function setZoom($zoomy){
		$this->zoomLevel = $zoomy;
	}
	
/**
* @function     setWidth
* @param        $width:int
* @returns      nothing
* @description  Sets the width of the map to be displayed
*/
	function setWidth($width){
		$this->mapWidth = $width;
	}
/**
* @function     setHeight
* @param        $height:int
* @returns      nothing
* @description  Sets the height of the map to be displayed
*/
	function setHeight($height){
		$this->mapHeight = $height;
	}
/**
* @function     setAPIkeyRED
* @param        $key:string
* @returns      nothing
* @description  Stores the API Key acquired from Google
*/
	function setAPIkey($key){
		$this->apiKey = $key;
	}
	
	function setControl($myControl){
		$this->showControl = $myControl;
	}
	
	function setControlSize($myControlSize){
		$this->controlType = $myControlSize;
	}
	
	
	function setType($myType){
		$this->showType = $myType;
	}
	
	function setOverview($myOverview){
		$this->showOverview = $myOverview;
	}
	
	
	function setLegend($myLegend){
		$this->showLegend = $myLegend;
	}
	
	function setGroup($myGroup){
	$this->showGroup = $myGroup;
}
	
	
	
	
	
/**
* @function     printGoogleJS
* @returns      nothing
* @description  Adds the necessary Javascript for the Google Map to function
*               should be called in between the html <head></head> tags
*/
	function printGoogleJS(){
		echo "\n
		        <script src=\"https://maps.googleapis.com/maps/api/js?key=[REDACTED_TOKEN]&sensor=false\" type=\"text/javascript\"></script>\n
				<script src=\"http://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/1.0/src/markermanager.js\" type=\"text/javascript\"></script>\n
				<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js\"></script>
				<script src=\"http://multiplottr.com/scripts/jquery.ui.map.js\"></script>
				<script src=\"http://multiplottr.com/scripts/infobox.js\"></script>
				

				
";
	}
	


	
/**
* @function     showMap
* @description  Displays the Google Map on the page
*/


function showMap(){
	echo "\n
	<div id='map_canvas' style='width: ".$this->mapWidth."; height: ".$this->mapHeight.";'>\n</div>\n
	<script type=\"text/javascript\">\n
	//<![CDATA[\n
    var points = [];
	var OV;
	var OV1
	var lastPoint;
	var oldPoint='';
	var center;
	var infoWindowON = false;
	var coordinates;
	var coordinates2;
	var coordinates3;
	var coordinates4;
	var batchPoints;
    var pts = [];
	var batchOV;
	
	var lockedCenter;
	
	var thisCustomIconButton = false;
	
	var totalDistanceM = 0;
	var totalDistanceK = 0;
	var myDistanceK = 0;
	var myDistanceM = 0;
	
	polygonNumbers = new Array();
	polygonNum = 0;
	batchpolygonNumbers = new Array();
	batchpolygonNum = 0;
	batchpolygonNumalt = 0;
	polygoncolor = '#FF0000';
	var newPolygon = false;
	var dotPolygonnum = 0;
	var dotPolygon = new Array();
	var dotPolygonMessage = new Array();
	var quickPolygon = false;
	var polygonControlnum = 0;
	var polygonControl = new Array();
	var globalbatchpolygonNum = 0;
	var globalPolygonColor ='#0A68F5';
	var tempPolygonOff = false;
	
	lineNumbers = new Array();
	lineNum = 0;
	batchlineNumbers = new Array();
	batchlineNum = 0;
	batchlineNumalt = 0;
	linecolor = '#FF0000';
	var newLine = false;
	var dotLinenum = 0;
	var dotLine = new Array();
	var dotLineMessage = new Array();
	var quickLine = false;
	var lineControlnum = 0;
	var lineControl = new Array();
	var tempLineOff = false;
	
	var newMarkernum = 0;
	var customMarkerNum = 0;
	var newMarker = new Array();
	var newMarkerMessage = new Array();
	
	var mapZoom;
	var thisZoom;
	var notMarker = true;
	var windowopen = false;
	var myMarkerNumber = 0;
	
	var latlongmarker;
	

    var markerNumber = 0;
	var totalMarkerNumber = 0;
	var thisMarkerNumber = false;


	var map = null;
	
	
	var IMAGES = [ 'REDMarker', 'BLUEMarker', 'CYANMarker', 'GREENMarker', 'GREYMarker', 'LIMEMarker', 'ORANGEMarker', 'PURPLEMarker', 'VIOLETMarker', 'YELLOWMarker' ];
    var ICONS = {};
    var map = null;
    var mgr = null;	
    var allmarkers = [];
	var gmarkers = []; 
	var side_bar_html = ''; 

var thisBatch = [];

var iconImageName = '';

    
	


var batchMarkers = false;
var markerGroups = { 'group0': [], 'group1': [], 'group2': [], 'group3': [], 'group3': [], 'group4': [], 'group5': [], 'group6': [], 'group7': [], 'group8': [], 'group9': [], 'group10': [], 'group11': [], 'group12': [], 'group13': [], 'group14': [], 'group15': [], 'group16': [], 'group17': [], 'group18': [], 'group19': [], 'group20': [], 'group21': [], 'group22': [], 'group23': [], 'group24': [], 'group25': [], 'group26': [], 'group27': [], 'group28': [], 'group29': [], 'group30': [], 'group31': [], 'group32': [], 'group33': [], 'group34': [], 'group35': [], 'group36': [], 'group37': [], 'group38': [], 'group39': []};

var markerGroupNames = [];
var markerGroupNumber = 0;
var getGroupName;
var groupText = '';
var groupArrayName = [];
//var groupArrayName=new Array('group0');
var groupArrayNumber = 0;
var groupArrayAmount = 0;

var totalCount = 0;

var infowindow = new google.maps.InfoWindow();

var dontPlot = false;
var showPolyline = false;
var polylineArray = [];

var httpMarker = false;
var setCenter = false;
var placeMarkerOn = false;

var newColorMarker;
var thisNewColorMarker;
var anotherColorMarker;
var thisMarkerDescription 
var newMarkerCount = 0;

var markerData = [];
var carryLocation;
var editted = false;
var thisEditMarkerNumb;
var carryPosition;
var globalCoordinates;

//http://stackoverflow.com/questions/8521766/google-maps-api-3-remove-selected-marker-only



var markerSelect = '<select class=\'formfields\' id=\'markerType\' name=\'markerType\'></select>';







function hideGroup(iii,ee) {
$('.'+iii).toggleClass('markerHidden');	
 $('.infoBox').hide();
 for (var i = 0; i < markerGroups[iii].length; i++ ) {
	markNum = markerGroups[iii][i];
    //thisBatch[markNum].setVisible(false);
	
	
	if (thisBatch[markNum] == 'undefined') {alert('undefined');} else {
		if(thisBatch[markNum].getVisible()) { 
		    
			thisBatch[markNum].setVisible(false);
             $('.infoBox').hide();
			$('#legendDiv .'+ee).hide();
		}
    	else {
			thisBatch[markNum].setVisible(true);
			$('.'+ee).show();
		}
    }
	
 }
}



function checkGroupNames(value) {
var i;
var groupNamesLength = markerGroupNames.length;
var valueStyle = value.replace(/ /g,'_');
for (i=0; i<=markerGroupNames.length; i++)
{ 
if (markerGroupNames[i]==value) {markerGroupNumber = i; return true;} else
if (markerGroupNames[i]==undefined && value!=='') { 
    var groupLink = 'group'+i;
	if (!dontPlot) {groupText += '<a  href=\"javascript:void(0)\" onclick=\"hideGroup(\''+groupLink+'\',\''+valueStyle+'\')\" class=\"'+groupLink+'\">'+value+'</a>';
	$('#groupControlDiv section').html(groupText);
	}

	markerGroupNames[i]=value; 
	markerGroupNumber = i; 
	writeToGroupLegend=true; 
	//alert('UNdefined group '+value+' markerGroupNumber = '+markerGroupNumber);
	return true;
	
}
}
}















function hideAll() {

 $('.infoBox').hide();
 for (var i = 0; i < thisBatch.length; i++ ) {
    thisBatch[i].setVisible(false);
  }

}

function showAll() {

 $('.infoBox').hide();
 for (var i = 0; i < thisBatch.length; i++ ) {
	var thisZIndex =  thisBatch[i].getZIndex();
//	alert(thisZIndex);
    thisBatch[i].setVisible(true);
  }

}



function hideGroup1() {

 $('.infoBox').hide();
 //alert(markerGroups['group1'].toString());
 for (var i = 0; i < markerGroups['group1'].length; i++ ) {
	 markNum = markerGroups['group1'][i];
    thisBatch[markNum].setVisible(false);
	
	
  }

}


function myclick(i) {

 google.maps.event.trigger(thisBatch[i], 'click');

}

//***************************************************************************************************************************

var boxText = document.createElement('div');


var myOptions = {
			 content: boxText
			,disableAutoPan: false
			,maxWidth: 0
			,pixelOffset: new google.maps.Size(-110, -23)
			,zIndex: null
			,closeBoxMargin: '10px 10px 10px 10px'
			,closeBoxURL: '/images/divControl_x_grey.png'
			,infoBoxClearance: new google.maps.Size(1, 1)
			,isHidden: false
			,pane: 'floatPane'
			,enableEventPropagation: false
		};

var ib = new InfoBox(myOptions);



$(document).ready(function(){
	
	$('#google-map').css('display', 'block !important');  
$('#legendExpand').hide();	

$('#legendCollapse').live('click', function(){ 
	$('#legendExpand, #legendCollapse, #legendDiv section').toggle();
 });
 
 $('#legendExpand').live('click', function(){ 
	$('#legendExpand, #legendCollapse, #legendDiv section').toggle();
 });


$('#groupCollapse').live('click', function(){ 
	$('#groupExpand, #groupCollapse, #groupControlDiv section').toggle();
 });
 
 $('#groupExpand').live('click', function(){ 
	$('#groupExpand, #groupCollapse, #groupControlDiv section').toggle();
 });
 
 $('#groupNames').live('change', function () { 
 	$('#markerGroup').val($('#groupNames').val());
 });
 
 $('#addNewMarkerURL').live('click', function(){
	var newMarkerURL = $('#markerURL').val(); 
    markerSelectOptions.push(newMarkerURL);
	$('#markerSelection ul').append('<li><img src=\"'+newMarkerURL+'\" alt=\"Custom\"></li>');
});	
 
 




});

function htmlspecialchars(str) {
 if (typeof(str) == 'string') {
  str = str.replace(/&/g, '&amp;');
  str = str.replace(/\"/g, '&quot;');
  str = str.replace(/\'/g, '&#039;');
  str = str.replace(/</g, '&lt;');
  str = str.replace(/>/g, '&gt;');
  }
 return str;
 }
 


function showValues() {
	
				
		var str = $('#markerForm').serialize();
		var thisMarkerType  = $('#markerType').val();
        thisMarkerDescription = $('#thisDescription').val();
		var thisMarkerGroup = $('#markerGroup').val();
		
		oldMarkerDescription = thisMarkerDescription;
		thisMarkerDescription = htmlspecialchars(thisMarkerDescription);
		
		//alert('thisMarkerDescription: '+thisMarkerDescription);
		//alert('thisEditMarkerNumb: '+thisEditMarkerNumb);
		thisMarkerDescription = thisMarkerDescription.replace('\\r', '<br>');
	    thisMarkerDescription = thisMarkerDescription.replace('\\n', '<br>');
		$('.infoBox').hide();

		

		

		if (!placeMarkerOn) {
		var textarea=$('#markerLocationField');	
		textarea = htmlspecialchars(textarea);
		
		
		var thisTextArea    = $('#markerLocationField').val();
		
		
		thisTextArea = thisTextArea.replace(thisEditMarkerNumb,'');
		//thisEditMarkerNumb = htmlspecialchars(thisEditMarkerNumb);
		//thisTextArea = htmlspecialchars(thisTextArea);
		textarea.html(thisTextArea);		
		
			anotherColorMarker = thisNewColorMarker.position; 
			thisNewColorMarker.setVisible(false);
		} else {
			anotherColorMarker = newColorMarker.position; 
			newColorMarker.setVisible(false);
		}
		placeMarkerFinal(carryPosition, map, thisMarkerDescription, thisMarkerType, thisMarkerGroup, oldMarkerDescription);
    }	
	
function editValues(i) {
		$('.infoBox').hide();
		thisBatch[i].setVisible(false);
		var lines = $('#markerLocationField').val().split('\\n');
        var thisMarkerData = [];
		for (var ee=0; ee < lines.length; ee++) {
			markerData.push($.trim(lines[ee]));
		}
		thisEditMarkerNumb = markerData[i];
		var fields = markerData[i].split('|');	
		var fieldZero = fields[0].replace('~','');
		editPlacedMarker(fieldZero, fields[1], fields[2], fields[3]);
}

function deleteValues(i) {
		$('.infoBox').hide();
		thisBatch[i].setVisible(false);
		var lines = $('#markerLocationField').val().split('\\n');
        var thisMarkerData = [];
		var newMarkerLocationData = '';
		for (var ee=0; ee < lines.length; ee++) {
			//var markerLine = htmlspecialchars(lines[ee]);
			if (ee!==i) {newMarkerLocationData = newMarkerLocationData+lines[ee]+'\\n';} else {newMarkerLocationData = newMarkerLocationData+'\\n';}
		}
		$('#markerLocationField').val(newMarkerLocationData);
}


	
	
function placeMarkerFinal(position, map, markerContent, markerType, markerGroup, oldMarkerDescription) {
	
	var thisIconImage;
	
	if ((markerType.substring(0,4)=='http')||(markerType.substring(0,4)=='HTTP')) {thisIconImage = new google.maps.MarkerImage(markerType);} else
	
	{thisIconImage = new google.maps.MarkerImage('images/'+markerType+'Marker.png');}
	
	newColorMarkerFinal = new google.maps.Marker({ 
		position: position, 
		map: map, 
		icon: thisIconImage, 
		//title: 'title',
		content: '<div class=\'infoBoxWrap\'>'+oldMarkerDescription+'</div><div class=\'triangle\'></div>',
		//zIndex: zItem,
		clickable:true
	});
	
	
	google.maps.event.addListener(newColorMarkerFinal, 'click', function () {
		ib.setContent(this.content);
        ib.open(map, this);
	}); 
	
	newMarkerCount++;
	position = position.toString();
	position = position.replace(/\(|\)/g,'');
	
	$('#markerLocationField').append('\\n~'+position+'|'+markerContent+'|'+markerType+'|'+markerGroup);
	
	}	
	
	
	
var markerTypeOptions;	
var markerSelectOptions=new Array('RED', 'BLUE', 'CYAN', 'GREEN', 'GREY', 'LIME', 'ORANGE', 'PURPLE', 'VIOLET', 'YELLOW');

var groupNameOptions;



function getGroupNames(thisMG) {
	
	groupNameOptions='';
	for (var i = 0; i <groupArrayName.length; i++) {
		var groupVal = groupArrayName[i];
		if (groupVal !=='CENTER') {
			if (groupVal!==thisMG) {groupNameOptions = groupNameOptions+'<option value=\''+groupVal+'\'>'+groupVal+'</option>'} else 
			{groupNameOptions = groupNameOptions+'<option value=\''+groupVal+'\' selected>'+groupVal+'</option>';}
		}
	}
	if (thisMG =='group0') {groupNameOptions = groupNameOptions+'<option value=\''+thisMG+'\' selected>'+thisMG+'</option>';}
}


function editPlacedMarker(thisLocation, thisMD, thisMT, thisMG) {	
	markerTypeOptions='';
	var iconImage;
	if (thisMT.substr(0,4) == 'HTTP' || thisMT.substr(0,4) == 'http' ) { iconImage = new google.maps.MarkerImage(thisMT); } else {iconImage = new google.maps.MarkerImage('images/'+thisMT+'Marker.png'); }
	editted=true;
	
	if (placeMarkerOn) {
		var removeMarker = placedMarkers[oldPlacedMarker]; 
    	removeMarker.setMap(null); 
		placeMarkerOn=false;
	}
	
	for (var i = 0; i < markerSelectOptions.length; i++) {
    	var val = markerSelectOptions[i];
		if (val!==thisMT) {markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\'>'+val+'</option>';} else {markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\' selected>'+val+'</option>';}
 	}
	
	getGroupNames(thisMG);
	
	var thisNewMarkerContent = '<div id=\'newMarkerContent\'class=\'infoBoxWrap\'><form id=\'markerForm\'><select class=\'formfields\' id=\'markerType\' name=\'markerType\'>'+markerTypeOptions+'</select><h2>HTML Description</h2><textarea class=\'formfieldstextarea\' name=\'thisDescription\'  id=\'thisDescription\'  rows=\'5\' />'+thisMD+'</textarea><select id=\'groupNames\'>'+groupNameOptions+'</select><input id=\'markerGroup\' type=\'text\' value=\''+thisMG+'\'><div class=\'buttonHolder\' ><a class=\'button\' id=\'markerSubmit\' href=\'javascript:void(0)\' onclick=\'showValues()\'>Submit</a></div></form></div><div class=\'triangle\'></div>';

	var fieldZeroArray = thisLocation.split(', ');
	var latty1 = fieldZeroArray[0];
	var longy2 = fieldZeroArray[1];


thisNewColorMarker = new google.maps.Marker({ 
		position: new google.maps.LatLng(latty1,longy2),
		map: map, 
		icon: iconImage, 
		//title: 'title',
		content: thisNewMarkerContent,
		//zIndex: zItem,
		clickable:true,
		draggable: true
	});
	
carryPosition = thisNewColorMarker.position;	
	
	ib.setContent(thisNewColorMarker.content);
    ib.open(map, thisNewColorMarker);
	
	
	google.maps.event.addListener(thisNewColorMarker, 'click', function () {
		ib.setContent(thisNewColorMarker.content);
    	ib.open(map, thisNewColorMarker);
	}); 
	
	google.maps.event.addListener (thisNewColorMarker, 'drag', function (event) 
      {
      carryPosition = thisNewColorMarker.position;
      });
	

}

var oldPlacedMarker;





var placedMarkers = {};



function placeMarker(position, map) {
	var thisMarkerName;
	var thisMarkerFileName = $('#markerSelection ul li .selected').attr('src');
	
	 if (thisMarkerFileName.substr(0,4) == 'HTTP' || thisMarkerFileName.substr(0,4) == 'http' ) {thisMarkerName = thisMarkerFileName;} else {thisMarkerName=$('#markerSelection ul li .selected').attr('alt');}
	
	 
	 
	 
	$('#markerSelection ul li img').removeClass('selected');
	markerTypeOptions='';	

	editted=false;
	
	if (placeMarkerOn) {
		var removeMarker = placedMarkers[oldPlacedMarker]; 
    	removeMarker.setMap(null); 
		placeMarkerOn=false;}
	
	
	for (var i = 0; i < markerSelectOptions.length; i++) {
    	var val = markerSelectOptions[i];
		if (val!==thisMarkerName) { markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\'>'+val+'</option>'; } else {markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\' selected>'+val+'</option>';}
 	}
	
	getGroupNames('group0');

	var newMarkerContent = '<div id=\'newMarkerContent\'class=\'infoBoxWrap\'><form id=\'markerForm\'><select class=\'formfields\' id=\'markerType\' name=\'markerType\'>'+markerTypeOptions+'</select><h2>HTML Description</h2><textarea class=\'formfieldstextarea\' name=\'thisDescription\'  id=\'thisDescription\'  rows=\'5\' /></textarea><select id=\'groupNames\'>'+groupNameOptions+'</select><input id=\'markerGroup\' type=\'text\' value=\'group0\'><div class=\'buttonHolder\' ><a class=\'button\' id=\'markerSubmit\' href=\'javascript:void(0)\' onclick=\'showValues()\'>Submit</a></div></form></div><div class=\'triangle\'></div>';
	
	var iconImage = new google.maps.MarkerImage($('#markerName').val());
	
	
	newColorMarker = new google.maps.Marker({ 
		position: position, 
		map: map, 
		icon: iconImage, 
		//title: 'title',
		content: newMarkerContent,
		//zIndex: zItem,
		clickable:true,
		draggable: true
	});
	
	carryPosition = newColorMarker.position;
	
	
	
	placeMarkerOn=true;
	
	oldPlacedMarker=newColorMarker.__gm_id;
	placedMarkers[oldPlacedMarker] = newColorMarker; 
	
	google.maps.event.addListener(newColorMarker, 'click', function () {
		ib.setContent(newColorMarker.content);
        ib.open(map, newColorMarker);
	}); 
	
	google.maps.event.addListener(newColorMarker, 'dblclick', function () {
		$('.infoBox').hide();
		newColorMarker.setVisible(false);	
	});
	
	google.maps.event.addListener(newColorMarker, 'mouseup', function () {
		carryPosition = newColorMarker.position;	

	});
	

      }


/***************************************************************************************************/

function CustomMapType() {
}
CustomMapType.prototype.tileSize = new google.maps.Size(256,256);
CustomMapType.prototype.maxZoom = 6;
CustomMapType.prototype.minZoom = 2;
CustomMapType.prototype.getTile = function(coord, zoom, ownerDocument) {
    var div = ownerDocument.createElement('DIV');
    var baseURL = 'http://www.multiplottr.com/dayz/tilecutter/';
    baseURL += zoom + '_' + coord.x + '_' + coord.y + '.png';
    div.style.width = this.tileSize.width + 'px';
    div.style.height = this.tileSize.height + 'px';
    div.style.backgroundColor = '#000000';
    div.style.backgroundImage = 'url(' + baseURL + ')';
    return div;
};
 
CustomMapType.prototype.name = 'Custom';
CustomMapType.prototype.alt = 'Tile Coordinate Map Type';
var CustomMapType = new CustomMapType();





	
function initialize(){
		\n
	
	var myOptions = {
			
    //mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
	streetViewControl:false,
	disableDoubleClickZoom: false,
	mapTypeControl: false, 
    navigationControl: false,
	zoomControl: false,
	disableDefaultUI: true,
	scaleControl: false,
	overviewMapControl: false,
	
	 \n";
	
	if (empty($this->centerMap)){
			echo 
			 "zoom: ".$this->zoomLevel.",
			  center: new google.maps.LatLng(".$this->validPoints[0]['lat'].",".$this->validPoints[0]['long']."),
			 \n";
       		}
		else{
        	echo $this->centerMap;
			echo 
			 "zoom: ".$zoomLevel.",
			  center: new google.maps.LatLng(".$lat.",".$long."),
			 \n";
        }
	
	if ($this->showOverview){
		echo "overviewMapControl: true,\n";
	}
	
	if ($this->showControl){
		echo "
		mapTypeControl: false, \n
        navigationControl: true,\n
		zoomControl: true,\n
		streetViewControl: false,\n
		scaleControl: false, \n
		";
		if ($this->controlType == 'small'){echo "zoomControlOptions: {style: google.maps.ZoomControlStyle.SMALL},\n";}
		if ($this->controlType == 'large'){echo "zoomControlOptions: {style: google.maps.ZoomControlStyle.LARGE},\n";}
		if ($this->showType) {
			echo "
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
			\n";
		}
	}
		
	
	
	
	
		
		
	
		
		
		
		
		
	
    echo "
	
	

	
	
    mapTypeId: google.maps.MapTypeId.ROADMAP
			
			}
	
	

    
	
	
			
			
			
		map = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
		
		
		


	//	CUSTOM MAP
    	map.mapTypes.set('custom',CustomMapType);
        map.setMapTypeId('custom');
		
		
	
	
		
		
function clearOverlays() {
  for (var i = 0; i < thisBatch.length; i++ ) {
    thisBatch[i].setMap(null);
  }
}	


		

		


//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!




function createMarker(ii, zItem, thisGroupName) {
	
	
if (!Array.indexOf) {
	Array.prototype.indexOf = function (obj, start) {
    	for (var i = (start || 0); i < this.length; i++) {
      		if (this[i] == obj) {
       		return i;
     	}
    }
    return -1;
	}
}
	
	groupArrayAmount = groupArrayName.length;
	var iii = groupArrayName.indexOf(thisGroupName);
	if (iii == -1) {
		totalCount = groupArrayAmount;
		groupArrayName.push(thisGroupName);
	} else {  
		groupArrayAmount = iii;
		totalCount =iii;
	}
	//alert(totalCount);
	//HACK
	
	//totalCount=totalCount-1;
	markerGroups['group'+totalCount].push(zItem);
	//alert(groupArrayAmount+' '+markerGroups['group'+groupArrayAmount].toString()+' '+thisGroupName);
	//alert(markerGroups['group'+groupArrayAmount].toString());
    
	
	var colorMarker = new google.maps.Marker({ 
		position: myLatLng, 
		map: map, 
		icon: iconImage, 
		//title: 'title',\
		content: '<div class=\'infoBoxWrap\'>'+ii+'<div><div class=\'buttonHolder\' ><a class=\'button\' id=\'editMarker\' href=\'javascript:void(0)\' onclick=\'editValues('+zItem+');\'>Edit</a><a href=\'javascript:void(0)\' class=\'button\'  onclick=\'deleteValues('+zItem+');\'>Delete</a></div></div></div><div class=\'triangle\'></div>',
		zIndex: zItem,
		clickable:true
	});
	
	google.maps.event.addListener(colorMarker, 'click', function (e) {
		ib.setContent(colorMarker.content);
        ib.open(map, colorMarker);
	}); 
	
	google.maps.event.addListener(colorMarker, 'dblclick', function (e) {
		$('.infoBox').hide();
		colorMarker.setVisible(false);

	}); 
	
	
	
	

    thisBatch.push(colorMarker);
    return thisBatch;
	

	
 }

		
		  var listener = google.maps.event.addListener(map, 'bounds_changed', function(){
          
		  google.maps.event.removeListener(listener);
		  

		  
		  
		  //var groupControlDiv = document.createElement('div');
         // var groupControl = new GroupControl(groupControlDiv, map);

         // groupControlDiv.index = 1;
         // map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(groupControlDiv);
		  
		  //var homeControlDiv = document.createElement('div');
         // var homeControl = new HomeControl(homeControlDiv, map);

         // homeControlDiv.index = 1;
         // map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(homeControlDiv); 
		  
      });
	  
		

    allmarkers.length = 0;
	var markers = [];
	var bound = new google.maps.LatLngBounds();
	
		
		\n";
	
	echo "			
	
	
	
";
	
	$numPoints = count($this->validPoints);
		for ($g = 0; $g<$numPoints; $g++){
		    $addtoLegend=true;
			$thisMarkerDescription = $this->validPoints[$g]['passedhtmlMessage'];
			if 	($thisMarkerDescription=="") {$thisMarkerDescription = $this->validPoints[$g]['passedAddress'];}
			
			
$fullMarkerDescription = $thisMarkerDescription;
list($junk, $legendDescription) = split('<h1>', $thisMarkerDescription);
list($legendDescription, $junk) = split('</h1>', $legendDescription);
$kmlMarkerDescription=$legendDescription;

$trimmedKML = str_replace($kmlMarkerDescription,"",$thisMarkerDescription);
$trimmedKML = str_replace("<h1>","",$trimmedKML);
$trimmedKML = str_replace("</h1>","",$trimmedKML);
$trimmedKML = htmlspecialchars($trimmedKML);

if ($legendDescription<>"") {$thisMarkerDescription = $legendDescription; $kmlMarkerDescription = htmlspecialchars($legendDescription);}
			
			echo" 
			dontPlot=false;
			
			var myLatLng = new google.maps.LatLng(".$this->validPoints[$g]['lat'].",".$this->validPoints[$g]['long'].");
			var myGroupName = '".$this->validPoints[$g]['groupName']."';
			
			var iconName = '".$this->validPoints[$g]['newMarkerColor']."';
			var iconZoomLevel = +'".$thisMarkerDescription."';
				if (iconName == 'CENTER') { 
					dontPlot=true; 
					map.setCenter(myLatLng); 
					lockedCenter=myLatLng;
					map.setZoom(iconZoomLevel);
					setCenter = true;
					var iconImage = new google.maps.MarkerImage('images/CENTERMarker.png');
				    iconImageName =                             'images/CENTERMarkerMarker.png';
				} else if (iconName == 'NUMBER') { 
				  	markerNumber++; 
					thisMarkerNumber = true; 
					
				  	var iconImage = new google.maps.MarkerImage('images/NUMBER'+markerNumber+'Marker.png');
				  	    iconImageName =                         'images/NUMBER'+markerNumber+'Marker.png';
				} else if (iconName.substr(0,4) =='LINE'){ 
				 	dontPlot=true; 
					showPolyline = true; 
					polylineArray.push(new google.maps.LatLng(".$this->validPoints[$g]['lat'].",".$this->validPoints[$g]['long'].")); 
				} else if (iconName.substr(0,4) == 'HTTP' || iconName.substr(0,4) == 'http' ){ 
				  	var iconImage = new google.maps.MarkerImage(iconName);
				  	iconImageName =                             iconName;
					var dontPostCustomMarker = false;
					for (var ii = 0; ii <markerSelectOptions.length; ii++) {
						var markerSelectCustom = markerSelectOptions[ii];
						if (markerSelectCustom == iconImageName) {dontPostCustomMarker=true;}
					}
					if (!dontPostCustomMarker) {
    					markerSelectOptions.push(iconImageName);
						$('#markerSelection ul').append('<li><img src=\"'+iconImageName+'\" alt=\"Custom\"></li>');
					}
				} else if (!dontPlot) { 
					var iconImage = new google.maps.MarkerImage('images/".$this->validPoints[$g]['newMarkerColor']."Marker.png');
				    iconImageName =                             'images/".$this->validPoints[$g]['newMarkerColor']."Marker.png';
				}
				
				bound.extend(myLatLng);

			\n";
			
            if 	($thisMarkerDescription=="") {$thisMarkerDescription = $this->validPoints[$g]['passedAddress'];}
			echo "var markerDescript = '".$thisMarkerDescription."';
			      var item=".$g.";
				  
			
			
			checkGroupNames(myGroupName);
			
			var myGroupNameStyle = myGroupName.replace(/ /g,'_');
	       
		    if (!dontPlot) {
				side_bar_html += '<a class=\"'+myGroupNameStyle+'\" href=\"javascript:myclick(' + (thisBatch.length) + ')\"><img class=\"marker\" src=\"'+iconImageName+'\" > <label>' + markerDescript + '</label><\/a>';
				
			$('#legendDiv section').html(side_bar_html);	
				
				}

	        var ii = '".$fullMarkerDescription."';
			
				                
	       //if (!dontPlot) 
		   { var allOfTheseMarkers =  createMarker(ii, item, myGroupName); }
			
			
			
			
			
			
			
			
			
			
			
			
			
			// ````````````````````````````````````````````````````````````````````````````````````````````````````````````` 
			
			
	
			
			
			
			
			
	
			
			
            markers.push(allOfTheseMarkers);

			 //Polyline
			 
						
	  
				  
				  
			\n";
		
					
		}
	
	echo "
			
			google.maps.event.addListener(map,'click',function(e) { 
			
			if ($('#markerSelection ul li img').hasClass('selected')) {
				$('.infoBox').hide();
              	placeMarker(e.latLng, map);
			}
			
			});
			
			//google.maps.event.addListener(map, 'rightclick', function(e) {
			//	$('.infoBox').hide();
            //  	placeMarker(e.latLng, map);
       		// });
			 
			// google.maps.event.addListener(map, 'mousemove', function(pt) { 
            // globalCoordinates = pt.latLng; 
            // });
			 
			 
			 
			 
			google.maps.event.addDomListener(window, 'resize', function() { if (!setCenter) {map.fitBounds(bound);} else {map.setCenter(lockedCenter);} }); 
			 
			 

   if (!setCenter) {map.fitBounds(bound);}



			return markers;

    
            
		
		\n";
		


		
	  
echo "
  	    	
		}\n

		

		//]]>\n
		
		window.onload = initialize;

		
			
		
			
			
			
		
    
		</script>\n";
	

}
		
		
 ///////////THIS BLOCK OF CODE IS FROM Roger Veciana's CLASS (assoc_array2xml) OBTAINED FROM PHPCLASSES.ORG//////////////
   	function xml2array($xml){
		$this->depth=-1;
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, $this);
		xml_parser_set_option ($this->xml_parser,XML_OPTION_CASE_FOLDING,0);//Don't put tags uppercase
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser,"characterData");
		xml_parse($this->xml_parser,$xml,true);
		xml_parser_free($this->xml_parser);
		return $this->arrays[0];

    }
    function startElement($parser, $name, $attrs){
		   $this->keys[]=$name; 
		   $this->node_flag=1;
		   $this->depth++;
     }
    function characterData($parser,$data){
       $key=end($this->keys);
       $this->arrays[$this->depth][$key]=$data;
       $this->node_flag=0; 
     }
    function endElement($parser, $name)
     {
       $key=array_pop($this->keys);
       if($this->node_flag==1){
         $this->arrays[$this->depth][$key]=$this->arrays[$this->depth+1];
         unset($this->arrays[$this->depth+1]);
       }
       $this->node_flag=1;
       $this->depth--;
     }
//////////////////END CODE FROM Roger Veciana's CLASS (assoc_array2xml) /////////////////////////////////


}//End Of Class

?>
