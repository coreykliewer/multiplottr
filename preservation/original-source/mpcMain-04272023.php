<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<?php

error_reporting(0);

class MultiplottrMap{
    var $validPoints = array();
    var $invalidPoints = array();
    var $mapWidth = "";
    var $mapHeight = "";
    var $apiKey = "[REDACTED_CONFIG_VALUE]";
    var $showControl = false;
    var $showType = false;
	var $showOverview = false;
	var $myCustomMapURL = '';
	var $myCustomFormat = '';
	var $myMaxZoom = '';
	var $myMinZoom = '';
	var $controlType = 'small';
    var $zoomLevel = '4';
	var $zoomy = '4';
	var $smallMarker = false;
	var $myPolyline=false;
	var $arrayLocations = array();
	var $addtoKML=true;

	
	function setDefaultMarker($myDefaultMarker){$this->defaultMarker = $myDefaultMarker;}
	function setDefaultGroupName($myDefaultGroupName){$this->defaultGroupName = $myDefaultGroupName;}
		
	function centerMap($lat,$long,$zoomLevel){
    	$this->centerMap = "map.setCenter(new GLatLng(".$lat.",".$long."), ".$zoomLevel.");\n";
	}
   
   
	function setHowManyPlotted(){
		$this->howManyPlotted = '0';
	}
		
	function setTotalPlotted(){
		$this->totalPlotted = '0';
	}	

    function fetchURL($url) { return file_get_contents($url); }   
   

	function addAddress($address,$htmlMessage,$newMarkerColor, $groupName)	{
		
		$groupName = htmlspecialchars($groupName);
		
		if ($groupName == ''){
			$groupName = 'group0';
		}
		
		$longiLat = false;
		$errorBig=false;
		$oldaddress=$address;
	
		if (!is_string($address)) { 
			die("All Addresses must be passed as a string"); 
		}
		
		if (substr($address, 0, 1)=="*") {
			$address = str_replace(array("***EDITTED_CONTENT_LOCATION***"), "", $address);
			}
		if (substr($address, 0, 1)=="~") {
			$myPolyline=true;
			$address = str_replace(array("~"), "", $address);
			list ($myLatitude, $myLongitude) = split (',', $address);
			$longiLat = true;
			//setting arrayLocations for editting a text doc - Custom DAYZ
			$locationsFields = $address."|".$htmlMessage."|".$newMarkerColor."|".$groupName;
			array_push($arrayLocations, $locationsFields);
		} else if (!$longiLat) {
			$address = urlencode($address);
			//$url = "http://maps.google.com/maps/geo?q=".$address."&output=csv&key=".$this->apiKey;
			//$url = "https://maps.google.com/maps/geo?q=".$address."&output=csv&key=[REDACTED_TOKEN]";
			$url = "https://maps.google.com/maps/geo?q=".$address."&output=csv&key=[REDACTED_TOKEN]";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER,0); //Change this to a 1 to return headers
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER[‘HTTP_USER_AGENT’]);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$mapdata = curl_exec($ch);
			curl_close($ch);
			if (strstr($mapdata,"200")) {
				$mapdata = explode(",",$mapdata);
				$precision = $mapdata[1];
				$newLatitude = $mapdata[2];
				$newLongitude = $mapdata[3];
			} else {
				$errorBig=true;
			}
		}
		
		$htmlMessage = str_replace(array("\r", "\n"), "", $htmlMessage);
		$htmlMessage = addslashes($htmlMessage);
		$newMarkerColor = str_replace(array("\r", "\n", " ",), "", $newMarkerColor);
		$firstFour = substr($newMarkerColor, 0, 4);
		$firstFour = strtoupper($firstFour);
		//$newMarkerColor = strtoupper($newMarkerColor);
		$groupName = str_replace(array("\r", "\n"), "", $groupName);
		$colorNumber = 0;
		
		if (strcmp($firstFour, "HTTP")!==0) { $newMarkerColor = strtoupper($newMarkerColor); }
		
		if (strcmp($newMarkerColor, "BLUE") == 0) 			{$colorNumber=1; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "GREEN") == 0) 			{$colorNumber=2; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "GREY") == 0) 			{$colorNumber=3; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "ORANGE") == 0) 			{$colorNumber=4; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "RED") == 0) 			{$colorNumber=5; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "YELLOW") == 0) 			{$colorNumber=6; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLBLUE") == 0) 		{$colorNumber=7; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLGREEN") == 0) 	{$colorNumber=8; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLGREY") == 0) 		{$colorNumber=9; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLORANGE") == 0)	{$colorNumber=10; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLRED") == 0) 		{$colorNumber=11; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "SMALLYELLOW") == 0) 	{$colorNumber=12; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 4)=="LINE") 			{$colorNumber=13;}
		if (strcmp($newMarkerColor, "CENTER") == 0) 			{$colorNumber=14;}
		if (strcmp($newMarkerColor, "DOT") == 0) 			{$colorNumber=15; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 6)=="NUMBER") 		{$colorNumber=16; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 6)=="CIRCLE") 		{$colorNumber=20; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 4)=="MINI") 			{$colorNumber=22; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($firstFour, "HTTP")==0) 					{$colorNumber=21; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 7)=="POLYGON") 		{$colorNumber=17;}
		if (substr($newMarkerColor, 0, 10)=="PUSHPINRED") 	{$colorNumber=18; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, "HEATRED")=="HEATRED") 	{$colorNumber=19; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (substr($newMarkerColor, 0, 4)=="LIN2") 			{$colorNumber=23;}
		if (strcmp($newMarkerColor, "CYAN") == 0) 			{$colorNumber=24; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "LIME") == 0) 			{$colorNumber=25; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "VIOLET") == 0) 			{$colorNumber=26; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "PURPLE") == 0) 			{$colorNumber=27; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "BLACK") == 0)			{$colorNumber=28; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "WHITE") == 0) 			{$colorNumber=29; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "PINK") == 0) 			{$colorNumber=30; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "MYLOCAL") == 0) 		{$colorNumber=31; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}
		if (strcmp($newMarkerColor, "RADIUS") == 0) 		{$colorNumber=32; if ($htmlMessage=="") {$htmlMessage=$oldaddress;} if ($groupName=="") {$groupName="group0";}}

		if ($colorNumber == 0) 								{$newMarkerColor = "RED"; $htmlMessage="";  $groupName="group0";}
		
		if ($longiLat) {
			$pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $myLatitude;
			$this->validPoints[$pointer]['long']= $myLongitude;
			$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
			$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
			$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
			$this->validPoints[$pointer]['groupName'] = $groupName;
		}	else if ($errorBig) {
			$apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=YahooDemo&street=";
			$addressData = file_get_contents($apiURL.urlencode($oldaddress));
			$results = $this->xml2array($addressData);
			if (empty($results['ResultSet']['Result']['Longitude'])){} else {
				$pointer = count($this->validPoints);
				$this->validPoints[$pointer]['lat']= $results['ResultSet']['Result']['Latitude'];
				$this->validPoints[$pointer]['long']= $results['ResultSet']['Result']['Longitude'];
				$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
				$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
				$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
				$this->validPoints[$pointer]['groupName'] = $groupName;
			}		
		} else {
			$pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $newLatitude;
			$this->validPoints[$pointer]['long']= $newLongitude;
			$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
			$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
			$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
			$this->validPoints[$pointer]['groupName'] = $groupName;
		}
	}
		
	function setZoom($zoomy) {$this->zoomLevel = $zoomy;}
	
	function setWidth($width) { $this->mapWidth = $width; }
	
	function setHeight($height){ $this->mapHeight = $height; }
	
	function setAPIkey($key){ $this->apiKey = $key; }

	function setControl($myControl){ $this->showControl = $myControl; }
	
	function setCustomMap($customMap){ $this->customMap = $customMap; }
	
	function setMapCustomURL($myCustomMapURL){ $this->customMapURL = $myCustomMapURL; }

	function setMapCustomFormat($myCustomFormat){ $this->customFormat = $myCustomFormat; }

	function setControlSize($myControlSize){ $this->controlType = $myControlSize; }

	function setType($myType){ $this->showType = $myType; }

	function showEdit($allowEdit){ $this->displayEdit = $allowEdit; }

	function setOverview($myOverview){ $this->showOverview = $myOverview; }

	function setLegend($myLegend){ $this->showLegend = $myLegend; }

	function setMinZoom($myMinZoom){ $this->minZoom = $myMinZoom; }

	function setMaxZoom($myMaxZoom){ $this->maxZoom = $myMaxZoom; }

	function setGroup($myGroup){ $this->showGroup = $myGroup; }
	
	function setLabels($showLabels){ $this->showLabels = $showLabels; }
	
	function setMapTitle($mapTitle){ $this->showMapTitle = $mapTitle;}
	


	function showMap(){
		echo "\n
		<div id='map_canvas' style='width: ".$this->mapWidth."; height: ".$this->mapHeight.";'>\n</div>\n
			
		<script type=\"text/javascript\">\n
		//<![CDATA[\n
		var loadCustomMap  = 0;  
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
		var globalPolylineColor = '#FF0000';
		var globalbatchpolylineNum = 0;
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
		var geocoder;
		
		var IMAGES = [ 'REDMarker', 'BLUEMarker', 'CYANMarker', 'GREENMarker', 'GREYMarker', 'LIMEMarker', 'ORANGEMarker', 'PURPLEMarker', 'VIOLETMarker', 'YELLOWMarker' ];
		var ICONS = {};
		var map = null;
		var mgr = null;	
		var allmarkers = [];
		var gmarkers = []; 
		var side_bar_html = ''; 

		var thisBatch = [];
		var thisOtherBatch = [];
		
		var iconImageName = '';

		var batchMarkers = false;
		var markerGroups = { 'group0': [], 'group1': [], 'group2': [], 'group3': [], 'group3': [], 'group4': [], 'group5': [], 'group6': [], 'group7': [], 'group8': [], 'group9': [], 'group10': [], 'group11': [], 'group12': [], 'group13': [], 'group14': [], 'group15': [], 'group16': [], 'group17': [], 'group18': [], 'group19': [], 'group20': [], 'group21': [], 'group22': [], 'group23': [], 'group24': [], 'group25': [], 'group26': [], 'group27': [], 'group28': [], 'group29': [], 'group30': [], 'group31': [], 'group32': [], 'group33': [], 'group34': [], 'group35': [], 'group36': [], 'group37': [], 'group38': [], 'group39': [], 'group40': [], 'group41': [], 'group42': [], 'group43': [], 'group44': [], 'group45': [], 'group46': [], 'group47': [], 'group48': [], 'group49': [], 'group50': []};
			
		var markerGroupNames = [];
		var markerGroupNumber = 0;
		var getGroupName;
		var groupText = '';
		var groupArrayName = [];
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
			
		var allowEdit=true;
		var markerSelect = '<select class=\'formfields\' id=\'markerType\' name=\'markerType\'></select>';
		var geocoder = null;
		var thisArrayType;
		var markersArray = [];
		var batchAllMarkers = [];
		
		var markerLocal;
		var GeoMarker;
		
		var kmlmsg='';
		var showLabel = false;
		var showPlottedMap = false;
		var editGoogleMap = false;
		var allTheMarkers = [];



			
		function clearOverlays() {
			for (var i = 0; i < markersArray.length; i++ ) {
				markersArray[i].setMap(null);
			}
			for (var i = 0; i < thisBatch.length; i++ ) {
				thisBatch[i].setMap(null);
			}
		}

		function geocodeThis(address) {
			geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					$('#markerAddress').val(results[0].geometry.location.lat()+', '+results[0].geometry.location.lng());
					placeMarker(results[0].geometry.location, map);
				} else {
					$('#markerAddress').val('Unsuccessful: '+status);
				}
			});
		}

		function hideGroup(iii,ee) {
			$('.'+iii).toggleClass('markerHidden');	
			infowindow.close();
		 	$('.infoBox').hide();
		 	for (var i = 0; i < markerGroups[iii].length; i++ ) {
				markNum = markerGroups[iii][i];
				if (thisBatch[markNum] == 'undefined') {alert('undefined');} else {
					if(thisBatch[markNum].getVisible()) { 
						thisBatch[markNum].setVisible(false);
						infowindow.close();
						$('.infoBox').hide();
						$('#legendDiv .'+ee).hide();
					} else {
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
			for (i=0; i<=markerGroupNames.length; i++) { 
				if (markerGroupNames[i]==value) {
					markerGroupNumber = i; return true;
				} else if (markerGroupNames[i]==undefined && value!=='') { 
					var groupLink = 'group'+i;
					if (!dontPlot) {
						groupText += '<a  href=\"javascript:void(0)\" onclick=\"hideGroup(\''+groupLink+'\',\''+valueStyle+'\')\" class=\"'+groupLink+'\">'+value+'</a>';
						$('#groupControlDiv').html(groupText);
						var groupTextCount = groupText.split('</a>');
						if (groupTextCount.length > 2){ 
							$('#groupControlDiv').parent().show();
						}
					}
					markerGroupNames[i]=value; 
					markerGroupNumber = i; 
					writeToGroupLegend=true; 
					return true;
				}
			}
		}

		function hideAll() {
			infowindow.close();
		 	$('.infoBox').hide();
		 	for (var i = 0; i < thisBatch.length; i++ ) {
				thisBatch[i].setVisible(false);
		  	}
		}

		function showAll() {
			infowindow.close();
			$('.infoBox').hide();
		 	for (var i = 0; i < thisBatch.length; i++ ) {
				//var thisZIndex =  thisBatch[i].getZIndex();
				thisBatch[i].setVisible(true);
		  	}
		}

		function hideGroup1() {
			infowindow.close();
		 	$('.infoBox').hide();
		 	for (var i = 0; i < markerGroups['group1'].length; i++ ) {
				markNum = markerGroups['group1'][i];
				thisBatch[markNum].setVisible(false);
			}
		}
			
		function tempMyClick(i) {
			google.maps.event.trigger(markersArray[i], 'click');
		}
			
		function myclick(i) {
			google.maps.event.trigger(thisBatch[i], 'click');
			google.maps.event.trigger(addressPlottedMarkers[i], 'click');
			
		}


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

		function placePlotted(thisPosition, markerContent, markerType, markerGroup, zItem) {
			thisArrayType = 'placedPlotted';
			var thisIconImage;
			if ((markerType.substring(0,4)=='http')||(markerType.substring(0,4)=='HTTP')) {
				thisIconImage = new google.maps.MarkerImage(markerType);
			} else {
				thisIconImage = new google.maps.MarkerImage('images/'+markerType+'Marker.png');
			}
			var fieldZeroArray = thisPosition.split(',');
			var latty1 = fieldZeroArray[0];
			var longy2 = fieldZeroArray[1];
			
			newColorMarkerFinal = new google.maps.Marker({ 
				position: new google.maps.LatLng(latty1,longy2),
				map: map, 
				icon: thisIconImage,
				content: '<div class=\'infoBoxWrap\'>'+markerContent+'<div class=\'buttonHolder\'><a class=\'button\' id=\'editMarker\' href=\'javascript:void(0)\' onclick=\'editValues('+zItem+');\'>Edit</a> | <a class=\'button\' href=\'#\'>SAVE MAP</a></div></div><div class=\'triangle\'></div>',
			});
				
			markersArray.push(newColorMarkerFinal);
	
			google.maps.event.addListener(newColorMarkerFinal, 'click', function () {
			ib.close();
				ib.setContent(this.content);
				ib.open(map, this);
			}); 
		}	

		function quickPlotting() {
			var newAddresses;
			var plottedAddresses = $('#markerLocationField').val();
			side_bar_html = '';
			clearOverlays();
			$('#batchAddressTab').removeClass('expanded');
			$('#batchAddressTab').addClass('collapsed');
			$('#batchAddress').slideUp('fast');
			$('#BatchAddressTab').removeClass('moveUpOnePx');
			newAddresses=plottedAddresses.replace(/\\r/g,'`');
			newAddresses=newAddresses.replace(/\\n/g,'`');
			var plottedLines = newAddresses.split('`');
			markerGroupNames = [];
			markerGroupNumber = 0;
			for (i=0; i<plottedLines.length; i++) {
				var plottedFields = plottedLines[i].split('|');
				plottedFields[0]=plottedFields[0].replace(/\~/g,'');
				if (plottedFields[0]!=='') {
					placePlotted(plottedFields[0], plottedFields[1], plottedFields[2], plottedFields[3], i);
					if (plottedFields[2].substr(0,4) !== 'HTTP' || plottedFields[2].substr(0,4) !== 'http' ) {
						plottedFields[2] = 'images/'+plottedFields[2]+'Marker.png'; 
					}
					var thisGroupNameStyle = plottedFields[3].replace(/ /g,'_');
					dontPlot=false;
					if (thisGroupNameStyle=='CENTER') {dontPlot=true;}
					if (thisGroupNameStyle=='TITLE') {dontPlot=true;}
					checkGroupNames(thisGroupNameStyle);
					side_bar_html += '<li><a class=\"'+thisGroupNameStyle+'\" href=\"javascript:tempMyClick(' + (i) + ')\"><img class=\"marker\" src=\"'+plottedFields[2]+'\" > <label>' + plottedFields[1] + '</label><\/a><\li>';
					$('#legendDiv').html(side_bar_html);
				}
			}
			
			showPlottedMap = true;
			
		}
				
				






		$(document).ready(function(){
			 
			function markerChange() {
				//$('#mapLegend #mainButtonContainer #cleaRNSave').show();	
				var val = $('#markerCount').val();
				$('#markerCount').val((val*1)+1); 
				var i = $('#markerCount').val();
				$('#saveMap').removeClass('readyToSave');
				$('#saveMap').addClass('addedMarkers');
				$('#saveMap span').text(i+' unsaved marker(s).');
			} 
			
			getGroupNames('group0');
			
			$('#google-map').css('display', 'block !important'); 
			 
			$('#legendExpand').hide();	
		
			$('#legendCollapse').on('click', function(){ 
				$('#legendExpand, #legendCollapse, #legendDiv section').toggle();
			});
		 
			$('#legendExpand').on('click', function(){ 
				$('#legendExpand, #legendCollapse, #legendDiv section').toggle();
			});
		
			$('#groupCollapse').on('click', function(){ 
				$('#groupExpand, #groupCollapse, #groupControlDiv section').toggle();
			});
		 
			$('#groupExpand').on('click', function(){ 
				$('#groupExpand, #groupCollapse, #groupControlDiv section').toggle();
			});
		 
			$('#groupNames').on('change', function () { 
				$('#markerGroup').val($('#groupNames').val());
			});
		 
			$('#addNewMarkerURL').on('click', function(){
				var newMarkerURL = $('#markerURL').val(); 
				markerSelectOptions.push(newMarkerURL);
				$('#markerList li img').removeClass('selected');
				$('#markerList').append('<li><img src=\"'+newMarkerURL+'\" alt=\"'+newMarkerURL+'\" class=\"selected\"></li>');
				$('.currentMarker').html('<img src=\"'+newMarkerURL+'\"  alt=\"Current Marker\">');
				$('#addNewMarkerURL').hide();
				if ($('#markerList').is(':visible')) { 
					$('#markerList').hide(); 
					$('#customizedMarker').hide(); 
					
					$('#markerSelection .container').show(); 
					$('#addAddress').show(); 
				}
			});	
		
			$('#updateMarker').on('click', function(){
				var tinyMCEContent = tinyMCE.activeEditor.getContent();
				var buttonNav = '<a class=\'button\' id=\'markerSubmit\' href=\'javascript:void(0)\' onclick=\'showValues()\'>Submit-3</a>';
				//$('#mapLegend #mainButtonContainer #cleaRNSave').show();
				$('#markerSelection').hide();
				var val = $('#markerCount').val();
				$('#markerCount').val((val*1)+1); 
				var i = $('#markerCount').val();
				$('#saveMap span').text(i+' unsaved marker(s).');
				$('#saveMap').removeClass('readyToSave');
				$('#saveMap').addClass('addedMarkers');
				if ($('#markerHeader').val() == '') {
					$('#newMarkerContent').html(tinyMCEContent);
				}  else {		
					$('#newMarkerContent').html('<h1>'+$('#markerHeader').val()+'</h1>'+tinyMCEContent+' '+buttonNav);	
				}	
				$('#updateMarker').hide;
				showValues();
			});	
		 
			$('#batchGeocode-old').on('click', function() {
			
			   if (($('#wpLogin').text()!=='YES')&&(count>2)) {alert('You must be a registered user to plot more than 2 addresses at a time.'); return;} else 
if (($('#wpLogin').text()==='YES')&&($('#subscriberStatus').text()!=='YES')&&(count>10)) {alert('You must be a paid subscriber to plot more than 10 addresses at a time.'); return;} else
if ((localStorage.locationsPlotted>19)&&($('#subscriberStatus').text()!=='YES')) {alert('Sorry, you do not have a subscription. Only subscribers can plot more than 20 locations a day.');} else {
var addresses = $('#batchAddressesField').val();
				if (makeMap)	{clientMapLoad();}
				batchGeocode(addresses);}
			
			
				
			});
			
			$('#plotTheseAddresses').on('click', function() {
				quickPlotting();
			});
			
			
			
			$('#plotLocation').live('click', function() {
				infowindow.close();
				$('#customizedMarker, #markerSelection').hide();
				placeMarker(GeoMarker.getPosition(), map);
				showValues();
				//$('#mapLegend #mainButtonContainer #cleaRNSave').show();
				var val = $('#markerCount').val();
			   $('#markerCount').val((val*1)+1); 
				var i = $('#markerCount').val();
				$('#saveMap span').text(i+' unsaved marker(s).');
				$('#saveMap').removeClass('readyToSave');
				$('#saveMap').addClass('addedMarkers');
				$('#submitMarker').hide();
			});
			
			$('#submitMarker').on('click', function() {
				showValues();
				//$('#mapLegend #mainButtonContainer #cleaRNSave').show();
				var val = $('#markerCount').val();
			   $('#markerCount').val((val*1)+1); 
				var i = $('#markerCount').val();
				$('#saveMap span').text(i+' unsaved marker(s).');
				$('#saveMap').removeClass('readyToSave');
				$('#saveMap').addClass('addedMarkers');
				$('#submitMarker').hide();
			});
			
			function hideMobile() {
		if (matchMedia('only screen and (max-width: 600px)').matches) {
			$('#mapLegend').hide();
		}
	}
			
			
		function showThisLocation() {
					GeoMarker = new GeolocationMarker();
					GeoMarker.setCircleOptions({fillColor: '#a9defd'});
					GeoMarker.setMarkerOptions({clickable:true});
					
					google.maps.event.addListenerOnce(GeoMarker, 'position_changed', function() {
					  map.setCenter(this.getPosition());
					  map.fitBounds(this.getBounds());
					});
			
					google.maps.event.addListener(GeoMarker, 'geolocation_error', function(e) {
					  alert('There was an error obtaining your position. Message: ' + e.message);
					  $('#plotLocation').hide();
					  $('#showLocation').hide();
					});
					
					google.maps.event.addListener(GeoMarker, 'click', function() {
									infowindow.close();
								});
			
					GeoMarker.setMap(map);
				}
				
			var thisPosition;

			function getThisLocation() {
				
						
						
						
						
						
						thisPosition = GeoMarker.getPosition();
							thisPosition = 'x'+thisPosition;
							thisPosition = thisPosition.replace('x','');
							thisPosition = thisPosition.replace('(','');
							thisPosition = thisPosition.replace(')','');
							thisPosition = thisPosition.replace(' ','');
						
						
						
						var thisUrl = 'https://www.multiplottr.com/?location='+thisPosition;
						$('#tweetBox span').html('<a href=\"https://twitter.com/intent/tweet?button_hashtag=HereIAm '+thisUrl+'\" class=\"twitter-hashtag-button\" data-related=\"Multiplottr\" data-url=\"https://www.multiplottr.com/?location='+thisPosition+'\">Tweet #HereIAm</a>');
    					$.getScript('http://platform.twitter.com/widgets.js');
						$('#linkBox').html('<h2>Share Location</h2><a href=\"'+thisUrl+'\" target=\"_blank\">https://www.multiplottr.com/?location='+thisPosition+'</a>');
				
				}
			
			$('#showLocation').live('click', function () { 
				$('#plotLocation').show();
				$('#plotLocation').css('display','block');

				hideMobile();
				if ($('#showLocation').html()==='Show My Location') {
					showThisLocation();
					$('#showLocation').html('Hide My Location');
					setTimeout(function(e) {getThisLocation();}, 1000);
	
					$('#shareLocationBox').show(); 
				} else {
					GeoMarker.setMap(null);
					$('#showLocation').html('Show My Location')
					$('#shareLocationBox').hide(); 
				}
			});
			
			$('#zoomView').on('click', function() {
				var c = map.getCenter();
				var d = map.getZoom();
				var cLat = c.lat();
				var cLng = c.lng();
				var lines = $('#markerLocationField').val().split('\\n');
				var thisMarkerData = [];
				var newMarkerLocationData = '';
				for (var ee=0; ee < lines.length; ee++) {
					var n=lines[ee].indexOf('|CENTER|');
					if (n<0) {newMarkerLocationData = newMarkerLocationData+lines[ee]+'\\n';} else {newMarkerLocationData = newMarkerLocationData+'\\n';}
				}
				$('#markerLocationField').val(newMarkerLocationData+'~'+cLat+','+cLng+'|'+d+'|CENTER|CENTER \\n');
				markerChange();
			});
			
		});



		//********** end of jquery .ready ************


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
			var thisMarkerType  = $('#markerName').val();
			var markerContentHeader = '<h1>'+$('#markerHeader').val()+'</h1>';
			var markerContentBody = tinyMCE.get('markerTextArea').getContent();
			var markerContentBodyArray=markerContentBody.split('\\n');
			markerContentBodyArray=markerContentBodyArray.join('');
			
			if (markerContentHeader == '<h1></h1>') {
				thisMarkerDescription = markerContentBodyArray;
			} else {
				thisMarkerDescription = markerContentHeader+' '+markerContentBodyArray;
			}
				
			var thisMarkerGroup = $('#currentGroupName').val();
			oldMarkerDescription = thisMarkerDescription;
			infowindow.close();
			$('.infoBox').hide();
			
			if (!placeMarkerOn) {
				var textarea=$('#markerLocationField');	
				textarea = htmlspecialchars(textarea);
				var thisTextArea    = $('#markerLocationField').val();
				thisTextArea = thisTextArea.replace(thisEditMarkerNumb,'***EDITTED_CONTENT_LOCATION***');
				$('#markerLocationField').val(thisTextArea);		
				anotherColorMarker = thisNewColorMarker.position; 
				thisNewColorMarker.setVisible(false);
			} else {
				anotherColorMarker = newColorMarker.position; 
				newColorMarker.setVisible(false);
			}
			placeMarkerFinal(carryPosition, map, thisMarkerDescription, thisMarkerType, thisMarkerGroup, oldMarkerDescription);
		}	
	
		function editValues(i) {
			infowindow.close();
			$('.infoBox').hide();
			if (thisArrayType=='createMarker') {thisBatch[i].setVisible(false);} else {markersArray[i].setVisible(false);}
			var lines = $('#markerLocationField').val().split('\\n');
			var thisMarkerData = [];
			for (var ee=0; ee < lines.length; ee++) {
				markerData.push($.trim(lines[ee]));
			}
			thisEditMarkerNumb = markerData[i];
			var fields = markerData[i].split('|');	
			var fieldZero = fields[0].replace('~','');
			if (fields[1]=='') {fields[1]=fieldZero}
			editPlacedMarker(fieldZero, fields[1], fields[2], fields[3]);
		}

		function deleteValues(i) {
			infowindow.close();
			$('.infoBox').hide();
			thisBatch[i].setVisible(false);
			var lines = $('#markerLocationField').val().split('\\n');
			var thisMarkerData = [];
			var newMarkerLocationData = '';
			for (var ee=0; ee < lines.length; ee++) {
				if (ee!==i) {newMarkerLocationData = newMarkerLocationData+lines[ee]+'\\n';} else {newMarkerLocationData = newMarkerLocationData+'\\n';}
			}
			$('#markerLocationField').val(newMarkerLocationData);
			var val = $('#markerCount').val();
			$('#markerCount').val((val*1)+1); 
			var i = $('#markerCount').val();
			$('#saveMap span').text(i+' unsaved marker(s).');
			$('#saveMap').removeClass('readyToSave');
				$('#saveMap').addClass('addedMarkers');
		}

		
	
	//Places markers on the map by clicking on it.
	
		function placeMarkerFinal(position, map, markerContent, markerType, markerGroup, oldMarkerDescription) {
			var thisIconImage;
			if ((markerType.substring(0,4)=='http')||(markerType.substring(0,4)=='HTTP')) {
				thisIconImage = new google.maps.MarkerImage(markerType);
			} else	{
				thisIconImage = new google.maps.MarkerImage('images/'+markerType+'Marker.png');
			}
			
			newColorMarkerFinal = new google.maps.Marker({ 
				position: position, 
				map: map, 
				icon: thisIconImage,
				content: '<div class=\'infoBoxWrap\'>'+oldMarkerDescription+'<div class=\'buttonHolder\'>Click the <span><div data-icon=\"J\" class=\"icon\"> </div><strong>Unsaved Markers</strong> </span>button to update your map and edit again.</div></div><div class=\'triangle\'></div>',
				clickable:true
			});
			
			markersArray.push(newColorMarkerFinal);
			
			google.maps.event.addListener(newColorMarkerFinal, 'click', function () {
				ib.setContent(this.content);
				ib.open(map, this);
			}); 
			
			newMarkerCount++;
			var tempAllContent = $('#markerLocationField').val();
		
			position = position.toString();
			position = position.replace(/\(|\)/g,'');
			var fullline= '~'+position+'|'+markerContent+'|'+markerType+'|'+markerGroup+'\\n';
			var updatedContent = tempAllContent.replace('***EDITTED_CONTENT_LOCATION***', fullline);
			$('#markerLocationField').val(updatedContent);
			if(tempAllContent.indexOf('***EDITTED_CONTENT_LOCATION***') === -1) {
				$('#markerLocationField').val(tempAllContent+fullline)
			}
			var removeBreaks = $('#markerLocationField').val();
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
			if ( groupNameOptions=='' ) { 
				if (thisMG == 'group0')	 {
					groupVal = thisMG;
					groupNameOptions = groupNameOptions+'<option value=\''+thisMG+'\' selected>'+thisMG+'</option>';
				}
			}
			$('#allGroupNames').html(groupNameOptions);
			var optionCount = groupNameOptions.split('</option>');
			if (optionCount.length==2){ $('#currentGroupName').val(groupVal); }
		}


		function editPlacedMarker(thisLocation, thisMD, thisMT, thisMG) {
			$('#markerHeader').val('');
			tinyMCE.activeEditor.setContent('');
			$('#markerSelection').show();
			$('#updateMarker').show();
			$('#placeMarker').hide();
			$('#currentGroupName').val(thisMG);
			var oldThisMD = thisMD;
			var bodyContent = thisMD.split('</h1>');
			
			if (bodyContent[0].substr(0,4) == '<h1>') {
				var markerHeader = 	bodyContent[0];
				markerHeader = markerHeader.replace('<h1>', '');
				$('#markerHeader').val(markerHeader);
				thisMD = bodyContent[1];
			} else {
				markerHeader = '';
				thisMD = oldThisMD;	
			}
			
			tinyMCE.get('markerTextArea').setContent(thisMD);
			$('#markerList li img').removeClass('selected');
			$('#markerList li img[alt=\"'+thisMT+'\"]').addClass('selected');
			var src = $('#markerSelection section ul li .selected').attr('src');
			$('.currentMarker').html('<img src='+src+' alt=\"'+thisMT+'\">');
			$('#markerName').val(thisMT);
			markerTypeOptions='';
			var iconImage;
			
			if (thisMT.substr(0,4) == 'HTTP' || thisMT.substr(0,4) == 'http' ) { 
				iconImage = new google.maps.MarkerImage(thisMT); 
			} else {
				iconImage = new google.maps.MarkerImage('images/'+thisMT+'Marker.png'); 
			
			}
			editted=true;
			if (placeMarkerOn) {
				var removeMarker = placedMarkers[oldPlacedMarker]; 
				removeMarker.setMap(null); 
				placeMarkerOn=false;
			}
	
			for (var i = 0; i < markerSelectOptions.length; i++) {
				var val = markerSelectOptions[i];
				if (val!==thisMT) {
					markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\'>'+val+'</option>';
				} else {
					markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\' selected>'+val+'</option>';
				}
			}
	
			getGroupNames(thisMG);
			thisNewMarkerContent = '<div id=\'newMarkerContent\'class=\'infoBoxWrap editWrap\'>'+oldThisMD+'</div><div class=\'triangle editTriangle\'></div>';
			var fieldZeroArray = thisLocation.split(',');
			var latty1 = fieldZeroArray[0];
			var longy2 = fieldZeroArray[1];
		
			$('#markerAddress').val(latty1+', '+longy2);
			
			thisNewColorMarker = new google.maps.Marker({ 
				position: new google.maps.LatLng(latty1,longy2),
				map: map, 
				icon: iconImage, 
				content: thisNewMarkerContent,
				clickable:true,
				draggable: true
			});
	
			carryPosition = thisNewColorMarker.position;	
			ib.close();	
			ib.setContent(thisNewColorMarker.content);
			ib.open(map, thisNewColorMarker);
				
			google.maps.event.addListener(thisNewColorMarker, 'click', function () {
				ib.close();
				ib.setContent(thisNewColorMarker.content);
				ib.open(map, thisNewColorMarker);
			}); 
				
			google.maps.event.addListener (thisNewColorMarker, 'drag', function (event) {
				 carryPosition = thisNewColorMarker.position;
				 $('#markerAddress').val(thisNewColorMarker.position.lat()+', '+thisNewColorMarker.position.lng());
			});
		}

		var oldPlacedMarker;
		var placedMarkers = {};

		function placeMarker(position, map) {
			$('#submitMarker').show();
			$('#updateMarker').hide();
			$('#markerList').hide();
			$('#markerSelection .container').show(); 
			$('#addAddress').show(); 
			var thisMarkerName;
			var thisMarkerFileName = $('#markerList li .selected').attr('src');
			if (thisMarkerFileName.substr(0,4) == 'HTTP' || thisMarkerFileName.substr(0,4) == 'http' ) {
				thisMarkerName = thisMarkerFileName;
				$('#markerName').val(thisMarkerName);
			} else {
				thisMarkerName=$('#markerList li .selected').attr('alt');
			}
			markerTypeOptions='';	
			editted=false;
			if (placeMarkerOn) {
				var removeMarker = placedMarkers[oldPlacedMarker]; 
				removeMarker.setMap(null); 
				placeMarkerOn=false;
			}
			for (var i = 0; i < markerSelectOptions.length; i++) {
				var val = markerSelectOptions[i];
				if (val!==thisMarkerName) { 
					markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\'>'+val+'</option>'; 
				} else {
					markerTypeOptions = markerTypeOptions+'<option value=\''+val+'\' selected>'+val+'</option>';
				}
			}
			getGroupNames('group0');
			var markerContentHeader = '<h1>'+$('#markerHeader').val()+'</h1>';
			var markerContentBody = tinyMCE.get('markerTextArea').getContent();
			var allMarkerContent;
			if (markerContentHeader == '<h1></h1>') {
				allMarkerContent=markerContentBody
			} else {
				allMarkerContent=markerContentBody=markerContentHeader+markerContentBody;
			}
			newMarkerContent = '<div id=\'newMarkerContent\'class=\'infoBoxWrap\'>'+allMarkerContent+'</div><div class=\'triangle\'></div>';
			if ((thisMarkerName.substring(0,4)=='http')||(thisMarkerName.substring(0,4)=='HTTP')) {
				iconImage = new google.maps.MarkerImage(thisMarkerName);
			} else	{
				iconImage = new google.maps.MarkerImage('images/'+thisMarkerName+'Marker.png');
			}
			
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
			$('#markerAddress').val(newColorMarker.position.lat()+', '+newColorMarker.position.lng());
			placeMarkerOn=true;
			oldPlacedMarker=newColorMarker.__gm_id;
			placedMarkers[oldPlacedMarker] = newColorMarker;
			 
			google.maps.event.addListener(newColorMarker, 'click', function () {
			ib.close();
				ib.setContent(newColorMarker.content);
				ib.open(map, newColorMarker);
			}); 
			
			google.maps.event.addListener(newColorMarker, 'dblclick', function () {
				infowindow.close();
				$('.infoBox').hide();
				newColorMarker.setVisible(false);	
			});
			
			google.maps.event.addListener(newColorMarker, 'mouseup', function () {
				carryPosition = newColorMarker.position;
				google.maps.event.addListener (newColorMarker, 'drag', function (event) {
					carryPosition = newColorMarker.position;
					$('#markerAddress').val(newColorMarker.position.lat()+', '+newColorMarker.position.lng());
				});	
			});
			
			allTheMarkers.push(newColorMarker);
			
			ib.setContent(newColorMarker.content);
			ib.close();
		// 	ib.open(map, newColorMarker);
		}





		function CustomMapType() {}
		
		CustomMapType.prototype.tileSize = new google.maps.Size(256,256);
		CustomMapType.prototype.maxZoom = ".$this->maxZoom.";
		CustomMapType.prototype.minZoom = ".$this->minZoom.";
		CustomMapType.prototype.getTile = function(coord, zoom, ownerDocument) {
			var div = ownerDocument.createElement('DIV');
			var baseURL = '".$this->customMapURL."';
			baseURL += zoom + '_' + coord.x + '_' + coord.y + '.".$this->customFormat."';
			div.style.width = this.tileSize.width + 'px';
			div.style.height = this.tileSize.height + 'px';
			div.style.backgroundColor = '#90b8cc';
			div.style.backgroundImage = 'url(' + baseURL + ')';
			return div;
		};
		 
		CustomMapType.prototype.name = 'Custom';
		CustomMapType.prototype.alt = 'Tile Coordinate Map Type';
		var CustomMapType = new CustomMapType();
		
		google.maps.visualRefresh = true;

	
		function initialize(){
			var dataString='#ff0000';
			var mapCircle;
			var latty='".$this->validPoints[0]['lat']."';
			var longy='".$this->validPoints[0]['long']."';
			if (latty=='') {latty='0';}
			if (longy=='') {longy='0';}
			var myOptions = {
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
				streetViewControl:false,
				disableDoubleClickZoom: false,
				mapTypeControl: true, 
				minZoom: ".$this->minZoom.",
				maxZoom: ".$this->maxZoom.",
				navigationControl: true,
				zoomControl: true,
				disableDefaultUI: false,
				scaleControl: false,
				overviewMapControl: false,
				draggable:true,
	 			\n";
	
				if (empty($this->centerMap)){
					echo "zoom: ".$this->zoomLevel.",
					center: new google.maps.LatLng(latty,longy),
					\n";
				} else {
					echo $this->centerMap;
					echo "zoom: ".$zoomLevel.",
					center: new google.maps.LatLng(".$lat.",".$long."),
					\n";
				}
	
				if ($this->showControl){
					echo "
					mapTypeControl: true, \n
					panControl:false, \n
				    navigationControl: true,\n
					zoomControl: true,\n
					streetViewControl: false,\n
					scaleControl: true, \n
					";
					if ($this->controlType == 'small'){echo "zoomControlOptions: {style: google.maps.ZoomControlStyle.SMALL},\n";}
					if ($this->controlType == 'large'){echo "zoomControlOptions: {style: google.maps.ZoomControlStyle.LARGE},\n";}
				} else { 
					echo "
					navigationControl: false,\n
					zoomControl: false,\n
					panControl:false, \n
					";
				}
	
				if ($this->showOverview){
					echo "
						overviewMapControl: true,\n
						overviewMapControlOptions: {opened: true},
					";
				} else { 
					echo "overviewMapControl: false,\n";
				}
				if ($this->showType) {
					echo "
					mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
					\n";
				}  else { 
					echo "mapTypeControl: false,\n";
				}
				
				echo "
			}
			
			
			
			
			
			
			
			map = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
			geocoder = new google.maps.Geocoder();
			
		
		
			//	CUSTOM MAP
	 		loadCustomMap = '".$this->customMap."';
	 		if (loadCustomMap==1) {
    			map.mapTypes.set('custom',CustomMapType);
        		map.setMapTypeId('custom');
			}
			\n";

			if ($this->displayEdit) {
				echo "
				allowEdit = true;
				\n";
			} else {
				echo "
				allowEdit = false;
				\n";
			}
			if ($this->showLabels){
					echo "
						showLabel = true;\n
					";
				}
			echo "
		
		
		
		
		
		
		
		function createMarker(ii, zItem, thisGroupName) {
			
			
			
			thisArrayType = 'createMarker';
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
			var infoBoxEnding='';
			if (iii == -1) {
				totalCount = groupArrayAmount;

				groupArrayName.push(thisGroupName);
			} else {  
				groupArrayAmount = iii;
				totalCount =iii;
			}
		
			markerGroups['group'+totalCount].push(zItem);
			if (allowEdit) {
					infoBoxEnding='<div class=\'buttonHolder infoBoxButtons\' ><a class=\'button editDelete\' id=\'editMarker\' href=\'javascript:void(0)\' onclick=\'editValues('+zItem+');\'>Edit</a><a href=\'javascript:void(0)\' class=\'button editDelete\'  onclick=\'deleteValues('+zItem+');\'>Delete</a><a href=\'https://maps.google.com/maps?q='+myLatLng+' \' class=\'button\' target=\'_blank\' \'>Google Map It</a></div>' 
				} else {infoBoxEnding='<div class=\'buttonHolder infoBoxButtons\' ><a href=\'https://maps.google.com/maps?q='+myLatLng+' \' class=\'button\' target=\'_blank\' \'>Google Map It</a></div>'}
				
				
			if ( ii.indexOf('<h1>') >= 0) {	
				var thisLabelContent = ii.split('<h1>')[1].split('</h1>')[0];
			} else
			
			{thisLabelContent='Nada'; showLabel = false;}
		
			var colorMarker = new MarkerWithLabel({ 
				position: myLatLng, 
				map: map, 
				icon: iconImage, 
				clickable:true,
				labelContent: thisLabelContent,
              labelAnchor: new google.maps.Point(-13, 50),
              labelClass: 'markerLabels', // the CSS class for the label
              labelVisible: showLabel
			   
				
			});
		
			google.maps.event.addListener(colorMarker, 'click', function (e) {
				ib.close();
					ib.setContent('<div class=\'infoBoxWrap\'>'+ii+' '+infoBoxEnding+'</div><div class=\'triangle\'></div>');
					ib.open(map, colorMarker);
				});
		
			google.maps.event.addListener(colorMarker, 'dblclick', function (e) {
				infowindow.close();
				$('.infoBox').hide();
				colorMarker.setVisible(false);
			}); 
		
			thisBatch.push(colorMarker);
			return thisBatch;
			
				

 		}

			var listener = google.maps.event.addListener(map, 'bounds_changed', function(){
				setupMarkers();
				
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
	

	
			$numPoints = count($this->validPoints);
			for ($g = 0; $g<$numPoints; $g++){
				$addtoLegend=true;
				$addtoKML=true;
				$thisMarkerDescription = $this->validPoints[$g]['passedhtmlMessage'];
				if 	($thisMarkerDescription=="") {
					$thisMarkerDescription = $this->validPoints[$g]['passedAddress'];
				}
				$thisMarkerDescription = stripslashes($thisMarkerDescription);
				$fullMarkerDescription = $thisMarkerDescription;
				list($junk, $legendDescription) = split('<h1>', $thisMarkerDescription);
				list($legendDescription, $junk) = split('</h1>', $legendDescription);
				$kmlMarkerDescription=$legendDescription;
				$trimmedKML = str_replace($kmlMarkerDescription,"",$thisMarkerDescription);
				$trimmedKML = str_replace("<h1>","",$trimmedKML);
				$trimmedKML = str_replace("</h1>","",$trimmedKML);
				$trimmedKML = htmlspecialchars($trimmedKML);
				if ($legendDescription<>"") {
					$thisMarkerDescription = $legendDescription; 
					$kmlMarkerDescription = htmlspecialchars($legendDescription);
				}
				
				echo" 
				
				dontPlot=false;
				var myGroupName = '".$this->validPoints[$g]['groupName']."';
				var iconName = '".$this->validPoints[$g]['newMarkerColor']."';
				var iconZoomLevel = +'".$thisMarkerDescription."';
				var thisLatitude = ".$this->validPoints[$g]['lat'].";
				var thisLongitude = ".$this->validPoints[$g]['long']."
				var myLatLng = new google.maps.LatLng(thisLatitude,thisLongitude);
				
				";
				
				
				
				if ($this->validPoints[$g]['newMarkerColor'] == 'CENTER') {
					$addtoKML=false;
					echo"
					dontPlot=true; 
					map.setCenter(myLatLng); 
					lockedCenter=myLatLng;
					map.setZoom(iconZoomLevel);
					if (matchMedia('only screen and (max-width: 480px)').matches) {
					if ((thisLatitude =='15' ) && (thisLongitude =='-42' )){map.setZoom(2);}
					}
					setCenter = true;
					var iconImage = new google.maps.MarkerImage('images/CENTERMarker.png');
					iconImageName =                             'images/CENTERMarkerMarker.png';
					";	
				} else
				
				if ($this->validPoints[$g]['newMarkerColor'] == 'RADIUS') {
					$addtoKML=false;
					
					if ($this->validPoints[$g]['passedhtmlMessage'] == null) {echo "dataString = '#ffff00,5,m'";} else {echo "dataString = '".$this->validPoints[$g]['passedhtmlMessage']."'";}

						echo "
					
					
					
					var dataRes = dataString.split(',');
					var distance;
					var thisOpacity;
					if (dataRes[2] =='K') {distance=1000;} else {distance = 1609.3}
					if (dataRes[3] == null) {thisOpacity=0.35;} else {thisOpacity=dataRes[3];}
					
					
					
					
					
					
					var cityCircle = new google.maps.Circle({
            strokeColor: dataRes[0],
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: dataRes[0],
			fillOpacity: thisOpacity,
            map: map,
            center:  myLatLng,
            radius:  dataRes[1] * distance
          });
		  var iconImage = new google.maps.MarkerImage('images/CENTERMarker.png');
					iconImageName =                             'images/CENTERMarkerMarker.png';
					";	
				} else
				
				 if ($this->validPoints[$g]['newMarkerColor'] == 'NUMBER') {
					echo"
					markerNumber++; 
					thisMarkerNumber = true; 
					var iconImage = new google.maps.MarkerImage('images/NUMBER'+markerNumber+'Marker.png');
					iconImageName =                         'images/NUMBER'+markerNumber+'Marker.png';
					";
				} 
				
				//else if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4) == 'LINE') {
				//	echo"
				//	dontPlot=true; 
				//	showPolyline = true; 
				//	polylineArray.push(new google.maps.LatLng(".$this->validPoints[$g]['lat'].",".$this->validPoints[$g]['long']."));
				//	";
					
				
				else if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="LINE"){ 
					$addtoLegend=false;
					$addtoKML=false;
					echo " 
					var iconImage = new google.maps.MarkerImage('images/CENTERMarker.png');
					iconImageName =                             'images/CENTERMarkerMarker.png';
					dontPlot=true;
					batchlineNumalt = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
					if (batchlineNumalt !== batchlineNum) {var pointsPolyline = [];}
					batchlineNum = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
					
					
					
					if (batchlineNum>globalbatchpolylineNum)	{
					globalbatchpolylineNum = batchlineNum;
					}
					pointsPolyline.push(new google.maps.LatLng(".$this->validPoints[$g]['lat'].", ".$this->validPoints[$g]['long']."));\n
					
					
					var polyPath = new google.maps.Polyline({
						path: pointsPolyline,
						strokeOpacity:0.7,
  						strokeWeight:4,
						";
			
						if ($this->validPoints[$g]['passedhtmlMessage']==null) {echo "strokeColor: '#FF0000',";} else {echo "strokeColor: '".$this->validPoints[$g]['passedhtmlMessage']."'";
						}
						echo "

				  	});

  					polyPath.setMap(map);
  
					\n	";
				
				
				} else if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4) == 'http' || substr($this->validPoints[$g]['newMarkerColor'], 0, 4) == 'HTTP') {
					echo"
					var iconImage = new google.maps.MarkerImage(iconName);
					iconImageName =                             iconName;
					var dontPostCustomMarker = false;
					for (var ii = 0; ii <markerSelectOptions.length; ii++) {
						var markerSelectCustom = markerSelectOptions[ii];
						if (markerSelectCustom == iconImageName) {dontPostCustomMarker=true;}
					}
					if (!dontPostCustomMarker) {
						markerSelectOptions.push(iconImageName);
						$('#markerList').append('<li><img src=\"'+iconImageName+'\" alt=\"'+iconImageName+'\"></li>');
					}
					";
				} else {
					echo"
					if (!dontPlot) { 
						var iconImage = new google.maps.MarkerImage('images/".$this->validPoints[$g]['newMarkerColor']."Marker.png');
						iconImageName =                             'images/".$this->validPoints[$g]['newMarkerColor']."Marker.png';
					}
					";
				}
				
				if 	($thisMarkerDescription=="") {$thisMarkerDescription = $this->validPoints[$g]['passedAddress'];}
				
				echo "
				
				
				
				
				if (!dontPlot) {bound.extend(myLatLng);}
				var markerDescript = '".$thisMarkerDescription."';
				var item=".$g.";
				checkGroupNames(myGroupName);
				var myGroupNameStyle = myGroupName.replace(/ /g,'_');
				   
				if (!dontPlot) {
					side_bar_html += '<li><a class=\"'+myGroupNameStyle+'\" href=\"javascript:myclick(' + (thisBatch.length) + ')\"><img class=\"marker\" src=\"'+iconImageName+'\" > <label>' + markerDescript + '</label><\/a><\/li>';
						
				}

				var ii = '".$fullMarkerDescription."';
				var infoBoxEnding='';
				
										
			markers.push(createMarker(ii, item, myGroupName));
				$('#kmlBox').val('<?xml version=\"1.0\" encoding=\"UTF-8\"?>\\r<kml xmlns=\"https://www.opengis.net/kml/2.2\">\\r<Document>\\r'+kmlmsg+'</Document>\\r</kml>');













		
				\n";
		
		if ($addtoKML){echo "kmlmsg += '<Placemark>\\r  <Style>\\r    <IconStyle>\\r      <Icon>\\r        <href>https://www.multiplottr.com/images/".$this->validPoints[$g]['newMarkerColor']."Marker.png</href>\\r      </Icon>\\r    </IconStyle>\\r  </Style>\\r  <name>".$kmlMarkerDescription."</name>\\r  <description>".$trimmedKML."</description>\\r  <Point>\\r    <coordinates>".$this->validPoints[$g]['long'].",".$this->validPoints[$g]['lat']."</coordinates>\\r  </Point>\\r</Placemark>\\r';   \n 

	";}	
		
			}
		
			echo "
			google.maps.event.addListener(map,'click',function(e) { 
				if (($('#plotMarker').attr('checked'))&&(!$('.infoBox').is(':visible'))) {
					infowindow.close();
					$('#customizedMarker').hide();
					placeMarker(e.latLng, map);
				}
			});
					
			google.maps.event.addDomListener(window, 'resize', function() { 
				if (!setCenter) {
					map.fitBounds(bound);
				} else {
					map.setCenter(lockedCenter);
				} 
			}); 
					 
			if (!setCenter) {map.fitBounds(bound);}
			
			getGroupNames('group0');
			
			
			$('#legendDiv').html(side_bar_html);
			$('#printLegend').html(side_bar_html);
			
			function setupMarkers() {
				mgr = new MarkerManager(map);
				google.maps.event.addListener(mgr, 'loaded', function(){
					mgr.addMarkers(batchAllMarkers, 0);
					mgr.refresh();          
				}); 
				$('#kmlBox').val('<?xml version=\"1.0\" encoding=\"UTF-8\"?>\\r<kml xmlns=\"https://www.opengis.net/kml/2.2\">\\r<Document>\\r'+kmlmsg+'</Document>\\r</kml>');
			}
          
		  
		  
		
	  	    	
		}\n





		

		//]]>\n
		
		//window.onload = initialize;
		
		function fetchURL($url) { return file_get_contents($url); } 
		
		if ((url('?map_id')!==null)) {
			window.onload = initialize; 
			$('#mapSideBarOptions').show();
			$('#bigStartButtons').hide();
			
			}
		/*if ((url('?new')!==null)) {
			window.onload = initialize; 
			$('#mapSideBarOptions').show();
			$('#bigStartButtons').hide();
		}*/	
		//if ($('#markerLocationField').length>0) {window.onload = initialize;}
		var plottedLocations = $('#markerLocationField');
		//if ($('#markerLocationField').val()) {window.onload = initialize;}
		
		</script>\n";
	
		//if ($showMapNow) {echo"<script>window.onload = initialize;</script>";"}

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
	function endElement($parser, $name) {
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
