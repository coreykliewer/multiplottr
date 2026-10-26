<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">

<?php

/**
* Phoogle Maps 2.0 | Uses Google Maps API to create customizable maps
* that can be embedded on your website
*    Copyright (C) 2005  Justin Johnson
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
*
*
* Phoogle Maps 2.0
* Uses Google Maps Mapping API to create customizable
* Google Maps that can be embedded on your website
*
* @class        Phoogle Maps 2.0
* @author       Justin Johnson <[REDACTED_EMAIL]>
* @copyright    2005 system7designs
*/

error_reporting(0);


class PhoogleMap{


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
    var $showControl = true;
	
/**
* showType
* True/False whether to show map type controls or not
*/
    var $showType = true;
	//var $showMetro = true;



/**
* controlType
* string: can be 'small' or 'large'
* display's either the large or the small controls on the map, small by default
*/

    var $controlType = 'large';
    

    var $zoomLevel = '11';
	
	var $zoomy = '11';
	var $smallMarker = false;
	var $myPolyline=false;
	
	
	
	

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
    
/**
* @function     centerMap
* @description  center's Google Map on a specific point
*               (thus eliminating the need for two different show methods from version 1.0)
*/


	

function centerMap($lat,$long,$zoomLevel){
    $this->centerMap = "map.setCenter(new GLatLng(".$lat.",".$long."), ".$zoomLevel.");\n";
	
}
    
    
/**
* @function     addAddress
* @param        $address:string
* @returns      Boolean True:False (True if address has long/lat, false if it doesn't)
* @description  Add's an address to be displayed on the Google Map
*               (thus eliminating the need for two different show methods from version 1.0)
*/

function setHowManyPlotted(){
	    $this->howManyPlotted = '0';
    }


function addAddress($address,$htmlMessage=null,$newMarkerColor=null, $howManyPlotted=null){
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
		}
	  else {
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
			$newMarkerColor = strtoupper($newMarkerColor);
			$colorNumber = 0;
			if (strcmp($newMarkerColor, "BLUE") == 0) {$colorNumber=1;}
			if (strcmp($newMarkerColor, "GREEN") == 0) {$colorNumber=2;}
			if (strcmp($newMarkerColor, "GREY") == 0) {$colorNumber=3;}
			if (strcmp($newMarkerColor, "ORANGE") == 0) {$colorNumber=4;}
			if (strcmp($newMarkerColor, "RED") == 0) {$colorNumber=5;}
			if (strcmp($newMarkerColor, "YELLOW") == 0) {$colorNumber=6;}
			if (strcmp($newMarkerColor, "SMALLBLUE") == 0) {$colorNumber=7;}
			if (strcmp($newMarkerColor, "SMALLGREEN") == 0) {$colorNumber=8;}
			if (strcmp($newMarkerColor, "SMALLGREY") == 0) {$colorNumber=9;}
			if (strcmp($newMarkerColor, "SMALLORANGE") == 0) {$colorNumber=10;}
			if (strcmp($newMarkerColor, "SMALLRED") == 0) {$colorNumber=11;}
			if (strcmp($newMarkerColor, "SMALLYELLOW") == 0) {$colorNumber=12;}
			if (substr($newMarkerColor, 0, 4)=="LINE") {$colorNumber=13;}
			if (strcmp($newMarkerColor, "CENTER") == 0) {$colorNumber=14;}
			if (strcmp($newMarkerColor, "DOT") == 0) {$colorNumber=15;}
			if (substr($newMarkerColor, 0, 6)=="NUMBER") {$colorNumber=16;}
			if (substr($newMarkerColor, 0, 7)=="POLYGON") {$colorNumber=17;}
			if (substr($newMarkerColor, 0, 10)=="PUSHPINRED") {$colorNumber=18;}
			if ($colorNumber == 0) {$newMarkerColor = "RED";}
        
		
		
		if ($longiLat) {
			$pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $myLatitude;
			$this->validPoints[$pointer]['long']= $myLongitude;
			$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
			$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
			$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
			
			echo "<script language='javascript'>\n";
			echo "if (parent.document.mapwindowform.addressBunch.value=='')\n";
			if ($colorNumber==16)
			{
			$this->howManyPlotted = $this->howManyPlotted+1;
			$newMarkerColor="NUMBER";
			     echo "{
				   var msg = '".$oldaddress."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."';
				 }
		         else {
				   var msg = '\\r".$oldaddress."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."';
				 }
				 \n";
				 
		    }
			else {
			  echo "{
			    var msg = '".$oldaddress."|".$htmlMessage."|".$newMarkerColor."';
		      }
		      else {
			    var msg = '\\r".$oldaddress."|".$htmlMessage."|".$newMarkerColor."';
			  }
			  \n";
			}
			echo "parent.document.mapwindowform.addressBunch.value += msg; \n </script>\n";
			
			$fp = fopen("data.txt","a");
            if(!$fp) {
              echo 'Error: Cannot open file.';
              exit;
              }
              fwrite($fp, $oldaddress."|".$htmlMessage."|".$newMarkerColor."\n");
              fclose($fp);		
		    }
		else
				
		if ($errorBig) {
				
			$apiURL = "http://api.local.yahoo.com/MapsService/V2/geocode?appid=YahooDemo&street=";
			$addressData = file_get_contents($apiURL.urlencode($oldaddress));
			$results = $this->xml2array($addressData);
			if (empty($results['ResultSet']['Result']['Longitude'])){
            	echo "<script language='javascript'>\n";
            	echo "alert('Could not plot ".$oldaddress.".');\n";
            	echo "</script>\n";
				}
			else {
			    $pointer = count($this->validPoints);
				$this->validPoints[$pointer]['lat']= $results['ResultSet']['Result']['Latitude'];
				$this->validPoints[$pointer]['long']= $results['ResultSet']['Result']['Longitude'];
				$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
				$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
				$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
							
			echo "<script language='javascript'>\n";
			echo "if (parent.document.mapwindowform.addressBunch.value=='')\n";
			if ($colorNumber==16)
			{
			$this->howManyPlotted = $this->howManyPlotted+1;
			$newMarkerColor="NUMBER";
			     echo "{
				   var msg = '".$oldaddress."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."';
				 }
		         else {
				   var msg = '\\r".$oldaddress."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."';
				 }
				 \n";
				 
		    }
			else {
			  echo "{
			    var msg = '".$oldaddress."|".$htmlMessage."|".$newMarkerColor."';
		      }
		      else {
			    var msg = '\\r".$oldaddress."|".$htmlMessage."|".$newMarkerColor."';
			  }
			  \n";
			}
			echo "parent.document.mapwindowform.addressBunch.value += msg; \n </script>\n";
						
				$fp = fopen("data.txt","a");
			 	if(!$fp) {
              		echo 'Error: Cannot open file.';
              		exit;
              	}
              	fwrite($fp, $oldaddress."|".$htmlMessage."|".$newMarkerColor."\n");
              	fclose($fp);
			}		
		}	
		else {
			$pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $newLatitude;
			$this->validPoints[$pointer]['long']= $newLongitude;
			$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
			$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
			$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
			
			echo "<script language='javascript'>\n";
			echo "if (parent.document.mapwindowform.addressBunch.value=='')\n";
			if ($colorNumber==16)
			{
			$this->howManyPlotted = $this->howManyPlotted+1;
			$newMarkerColor="NUMBER";
			     echo "{
				   var msg = '".$oldaddress."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."';
				 }
		         else {
				   var msg = '\\r".$oldaddress."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."';
				 }
				 \n";
				 
		    }
			else {
			  echo "{
			    var msg = '".$oldaddress."|".$htmlMessage."|".$newMarkerColor."';
		      }
		      else {
			    var msg = '\\r".$oldaddress."|".$htmlMessage."|".$newMarkerColor."';
			  }
			  \n";
			}
			echo "parent.document.mapwindowform.addressBunch.value += msg; \n </script>\n";
		
			$fp = fopen("data.txt","a");
            if(!$fp) {
              echo 'Error: Cannot open file.';
              exit;
              }
            fwrite($fp, $oldaddress."|".$htmlMessage."|".$newMarkerColor."\n");
            fclose($fp);
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
* @function     setAPIkey
* @param        $key:string
* @returns      nothing
* @description  Stores the API Key acquired from Google
*/
	function setAPIkey($key){
		$this->apiKey = $key;
	}
/**
* @function     printGoogleJS
* @returns      nothing
* @description  Adds the necessary Javascript for the Google Map to function
*               should be called in between the html <head></head> tags
*/
	function printGoogleJS(){
		echo "\n<script src=\"http://maps.google.com/maps?file=api&v=2&key=".$this->apiKey."\" type=\"text/javascript\"></script>\n";
	}
	



/**
* @function     showMap
* @description  Displays the Google Map on the page
*/


function showMap(){
	echo "\n<div id=\"map\" style=\"width: ".$this->mapWidth."px; height: ".$this->mapHeight."px\">\n</div>\n
	<script type=\"text/javascript\">\n
	//<![CDATA[\n
    var points = [];
	var OV;
	var OV1
	var lastPoint;
	var oldPoint='';
	var center;
	var map;
	var coordinates;
	var coordinates2;
	var coordinates3;
	var coordinates4;
	var batchPoints;
    var pts = [];
	var batchOV;
	
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
	var newMarker = new Array();
	var newMarkerMessage = new Array();
	
	var mapZoom;
	var thisZoom;
	var notMarker = true;
	var windowopen = false;
	var myMarkerNumber = 0;
	
	var latlongmarker;
	
	var baseIcon = new GIcon();
	baseIcon.shadow = \"http://www.google.com/mapfiles/shadow50.png\";
	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);
	
	var smallIcon = new GIcon();
	smallIcon.iconSize = new GSize(12, 20);
	smallIcon.iconAnchor = new GPoint(6, 20);
	smallIcon.infoWindowAnchor = new GPoint(5, 1);
	
	var verysmallIcon = new GIcon();
	verysmallIcon.iconSize = new GSize(0, 0);
	verysmallIcon.shadowSize = new GSize(0, 0);
	verysmallIcon.iconAnchor = new GPoint(0, 0);

	var dotIcon = new GIcon();
	dotIcon.iconSize = new GSize(5, 5);
	dotIcon.iconAnchor = new GPoint(2, 2);
	dotIcon.infoWindowAnchor = new GPoint(5, 0);
	
	var pushpinIcon = new GIcon();
	pushpinIcon.shadow = \"images/PINSHADOW.png\";
	pushpinIcon.iconSize = new GSize(10, 15);
	pushpinIcon.shadowSize = new GSize(15, 15);
	pushpinIcon.iconAnchor = new GPoint(7, 15);
	pushpinIcon.infoWindowAnchor = new GPoint(5, 1);
	
	var multiAddressValue;
	var multiAddressDisplay;
	var carryG;
	var thisMarkerLat;
	var thisMarkerLong;
	
	
function insertOption()
  {
  
  var y=parent.document.createElement('option');
  y.value=multiAddressValue;
  y.text=multiAddressDisplay;
  
  var x=parent.document.getElementById('multiSelect');
  try
    {
    x.add(y,null); // standards compliant
    }
  catch(ex)
    {
    x.add(y); // IE only
    }
	map.removeOverlay(carryG);
	map.closeInfoWindow();
	var myIcon = new GIcon(baseIcon);   
	myIcon.image = 'images/BlackMarker.png';
	var myPoint = new GPoint(thisMarkerLong,thisMarkerLat);
	var newcarryG = new GMarker(myPoint, {icon:myIcon});
	map.addOverlay(newcarryG);
    
	
	
	
  }	
	
function quickLineOff() { 
		if (dotLinenum<=1) {
		map.removeOverlay(dotLine[0]); 
		map.removeOverlay(lineNumbers[0]); 
		dotLinenum = 0; 
		dotLinenum = 0; 
		points.pop();
	}
	
	if (lineControlnum > 0 && dotLinenum > 0) {
	    var i = 0;
		while (i < lineControlnum) {
			var dotNumber = dotLinenum-1; map.removeOverlay(dotLine[dotNumber]); dotLinenum=dotNumber;
			if (parent.document.mapwindowform.addressBunch.value=='') {parent.document.mapwindowform.addressBunch.value += lineControl[i];}
			else {parent.document.mapwindowform.addressBunch.value += '\\r'+lineControl[i];}
			i=i+1;
		}
	}
	lineControlnum = 0;
	dotLinenum = 0;
	map.closeInfoWindow();
	windowopen = false;
	notMarker = false;
	quickLine = false; 
	tempLineOff = false;
	document.getElementById('map').firstChild.firstChild.style.cursor ='default'; 

}\n;

function removeLastLinePoint(){ quickLine=true; var dotNumber = dotLinenum-1; map.removeOverlay(dotLine[dotNumber]); dotLinenum = dotLinenum-1; map.removeOverlay(lineNumbers[lineNum]); lineControlnum = lineControlnum-1; points.pop(); lineNumbers[lineNum] = new GPolyline(points,'#FF0000', 4, 0.5); if (dotLinenum>>0) {map.addOverlay(lineNumbers[lineNum]);} map.closeInfoWindow();  }

function cancelLine(){ 
while (dotLinenum >=0) {
var dotNumber = dotLinenum-1; map.removeOverlay(dotLine[dotNumber]); dotLinenum = dotLinenum-1; map.removeOverlay(lineNumbers[lineNum]); lineControlnum = lineControlnum-1; points.pop(); lineNumbers[lineNum] = new GPolyline(points,'#FF0000', 4, 0.5); }
map.closeInfoWindow();
quickLine=false;
map.removeOverlay(dotLine[0]); 
map.removeOverlay(lineNumbers[0]); 
dotLinenum = 0; 
dotLinenum = 0; 
points.pop();
lineControlnum = 0;
document.getElementById('map').firstChild.firstChild.style.cursor ='default'; 
}


function addthisMarker() {
		var mymarkerColor = document.getElementById('markerType');
		var myDescription = document.getElementById('thisDescription').value;
		myDescription = myDescription.replace('\\r', '<br>');
		myDescription = myDescription.replace('\\n', '<br>');
		var thisMarker = mymarkerColor.options[mymarkerColor.selectedIndex].text;
		if (parent.document.mapwindowform.addressBunch.value=='') 
			{var msg = '~' + coordinates2+'|'+myDescription+'|'+thisMarker;}
		else {var msg = '\\r~' + coordinates2+'|'+myDescription+'|'+thisMarker;}
		parent.document.mapwindowform.addressBunch.value += msg;
		map.removeOverlay(latlongmarker);
		map.closeInfoWindow();
		var myIcon = new GIcon(baseIcon);\n
		myIcon.image = 'images/'+thisMarker+'Marker.png';\n
		newMarker[newMarkernum] = new GMarker(lastPoint, {icon:myIcon});\n;
	    var thisNewMarker = newMarker[newMarkernum];
		var thisNewMarkerNum = newMarkernum;
		newMarkerMessage[newMarkernum] = '<div class=\'infoWindowDiv\'>'+myDescription+'</div>';
		
		GEvent.addListener(thisNewMarker, \"mouseover\", function() {notMarker = false;});
		GEvent.addListener(thisNewMarker, \"mouseout\", function() {notMarker = true;});
		
		
		GEvent.addListener(thisNewMarker, \"click\", function() {
			var html = newMarkerMessage[thisNewMarkerNum];
			thisNewMarker.openInfoWindowHtml(html);
    	});
		
		
		
		
		map.addOverlay(thisNewMarker);\n;
		newMarkernum = newMarkernum+1;	

		
	}\n;
	  
	  
function addMarkerInfoWindowLine() {


    latlongmarker.openInfoWindowHtml(\"<div id='infoDiv' class='infoWindowDiv'>Marker Color<br /><select class='formfields' id='markerType' name='markerType'><option selected='selected'>RED</option><option>BLUE</option><option>GREEN</option><option>GREY</option><option>ORANGE</option><option>YELLOW</option></select><br />HTML Description<br /><textarea class='formfieldstextarea' name='thisDescription'  id='thisDescription'  rows='5' /></textarea><a  class='nav' href='javascript:void(0)' onclick='addthisMarker()'>Submit</a></div>\");
	
	}\n;	  
	  
	  
	  
function addMarkerInfoWindow() {


    latlongmarker.openInfoWindowHtml(\"<div id='infoDiv' class='infoWindowDiv'>Marker Color<br /><select class='formfields' id='markerType' name='markerType'><option selected='selected'>RED</option><option>BLUE</option><option>GREEN</option><option>GREY</option><option>ORANGE</option><option>YELLOW</option></select><br />HTML Description<br /><textarea class='formfieldstextarea' name='thisDescription'  id='thisDescription'  rows='5' /></textarea><a  class='nav' href='javascript:void(0)' onclick='addthisMarker()'>Submit</a></div>\");
	
	}\n;	
	  
function addLineInfoWindow2() {
	document.getElementById('map').firstChild.firstChild.style.cursor ='crosshair'; 
	
	
	if (newLine) { totalDistanceM = 0; totalDistanceK = 0; myDistanceK = 0; myDistanceM = 0; oldPoint=''; newLine=false;}
	points.push(lastPoint);
	if (oldPoint=='') {oldPoint=lastPoint;} 
	
	myDistanceK = oldPoint.distanceFrom(lastPoint)/1000;
	myDistanceM = oldPoint.distanceFrom(lastPoint)/1609.344;
	
	myDistanceM = Math.round(myDistanceM*10)/10;
	myDistanceK = Math.round(myDistanceK*10)/10;

	
	totalDistanceM = totalDistanceM+myDistanceM;
	totalDistanceK = totalDistanceK+myDistanceK;
	
	totalDistanceM = Math.round(totalDistanceM*10)/10;
	totalDistanceK = Math.round(totalDistanceK*10)/10;
	
	
	oldPoint=lastPoint;
	map.removeOverlay(lineNumbers[lineNum]);
	lineNumbers[lineNum] = new GPolyline(points,'#FF0000', 4, 0.5);
	map.addOverlay(lineNumbers[lineNum]);
	map.closeInfoWindow();
	map.removeOverlay(latlongmarker);
	
		
	var myIcon = new GIcon(dotIcon);\n
	myIcon.image = 'images/BLUEDOTMarker.gif';\n;
	dotLine[dotLinenum] = new GMarker(lastPoint, {icon:myIcon});\n;
	
	var thismarker = dotLine[dotLinenum];
	var thisdotLineNum = dotLinenum;
	dotLineMessage[dotLinenum] = '<div class=\'infoWindowDiv\'><p><b>Distance from First Point</b>:<br>'+totalDistanceM+' mi. | '+totalDistanceK+' km.</p><p><b>Distance from Previous Point</b>:<br>'+myDistanceM+' mi. | '+myDistanceK+' km. </p></div>';
	
	tempLineOff=false; windowopen=false;
		
	if (quickLine) {GEvent.addListener(thismarker, \"mouseover\", function() {notMarker = false;});
	GEvent.addListener(thismarker, \"mouseout\", function() {notMarker = true;});
	GEvent.addListener(thismarker, \"infowindowopen\", function() {quickLine=false; tempLineOff=true; windowopen=true; });
	GEvent.addListener(thismarker, \"infowindowclose\", function() {windowopen=false;});}
	
		
    GEvent.addListener(thismarker, \"click\", function() {
	
	var html = dotLineMessage[thisdotLineNum];
	if (quickLine) { html = html+'<div class=\'infoWindowDiv\'><ul class=\'infonav\'><li><a class=\'nav\' href=\'javascript:{quickLineOff()}\'>Complete Line</a></li><li><a class=\'nav\' href=\'javascript:{removeLastLinePoint()}\'>Undo Last Line Point</a></li><li><a class=\'nav\' href=\'javascript:{cancelLine()}\'>Cancel Line</a></li></ul></div>'; }
	else {tempLineOff=false; windowopen=false; quickLine=false;}
	thismarker.openInfoWindowHtml(html);
	
	
    });
	
	map.addOverlay(thismarker);\n;	
		
	var msg = '~' + coordinates +lineNum;
	lineControl[lineControlnum] = msg;
	lineControlnum = lineControlnum+1
	dotLinenum = dotLinenum+1;
	

	  }\n;  
	  

function quickPolygonOff() { 
		if (dotPolygonnum<=1) {
		map.removeOverlay(dotPolygon[0]); 
		map.removeOverlay(polygonNumbers[0]); 
		dotPolygonenum = 0; 
		dotPolygonnum = 0; 
		points.pop();
	}
	
	if (polygonControlnum > 0 && dotPolygonnum > 0) {
	    var i = 0;
		while (i < polygonControlnum) {
			var dotNumber = dotPolygonnum-1; map.removeOverlay(dotPolygon[dotNumber]); dotPolygonnum=dotNumber;
			if (parent.document.mapwindowform.addressBunch.value=='') {parent.document.mapwindowform.addressBunch.value += polygonControl[i];}
			else {parent.document.mapwindowform.addressBunch.value += '\\r'+polygonControl[i];}
			i=i+1;
		}
		parent.document.mapwindowform.addressBunch.value += '\\r'+polygonControl[0];
	}
	polygonControlnum = 0;
	dotPolygonnum = 0;
	map.closeInfoWindow();
	windowopen = false;
	notMarker = false;
	quickPolygon = false; 
	tempPolygonOff = false;
	document.getElementById('map').firstChild.firstChild.style.cursor ='default'; 

}\n;

function removeLastPolygonPoint(){ quickPolygon=true; var dotNumber = dotPolygonnum-1; map.removeOverlay(dotPolygon[dotNumber]); dotPolygonnum = dotPolygonnum-1; map.removeOverlay(polygonNumbers[polygonNum]); polygonControlnum = polygonControlnum-1; points.pop(); polygonNumbers[polygonNum] = new GPolygon(points,'#0a68f5', 2, 0.7,'#0a68f5', 0.5); if (dotPolygonnum>>0) {map.addOverlay(polygonNumbers[polygonNum]);} map.closeInfoWindow();  }

function cancelPolygon(){ 
while (dotPolygonnum >=0) {
var dotNumber = dotPolygonnum-1; map.removeOverlay(dotPolygon[dotNumber]); dotPolygonnum = dotPolygonnum-1; map.removeOverlay(polygonNumbers[polygonNum]); polygonControlnum = polygonControlnum-1; points.pop(); polygonNumbers[polygonNum] = new GPolygon(points,'#0a68f5', 2, 0.7,'#0a68f5', 0.5); }
map.closeInfoWindow();
quickPolygon=false;
map.removeOverlay(dotPolygon[0]); 
map.removeOverlay(polygonNumbers[0]); 
dotPolygonnum = 0; 
dotPolygonnum = 0; 
points.pop();
polygonControlnum = 0;
document.getElementById('map').firstChild.firstChild.style.cursor ='default'; 
}







	  
	  
function addPolygon() {
	document.getElementById('map').firstChild.firstChild.style.cursor ='crosshair'; 
	
	
	if (newPolygon) { totalDistanceM = 0; totalDistanceK = 0; myDistanceK = 0; myDistanceM = 0; oldPoint=''; newPolygon=false;}
	points.push(lastPoint);
	if (oldPoint=='') {oldPoint=lastPoint;} 
	
	myDistanceK = oldPoint.distanceFrom(lastPoint)/1000;
	myDistanceM = oldPoint.distanceFrom(lastPoint)/1609.344;
	
	myDistanceM = Math.round(myDistanceM*10)/10;
	myDistanceK = Math.round(myDistanceK*10)/10;

	
	totalDistanceM = totalDistanceM+myDistanceM;
	totalDistanceK = totalDistanceK+myDistanceK;
	
	totalDistanceM = Math.round(totalDistanceM*10)/10;
	totalDistanceK = Math.round(totalDistanceK*10)/10;
	
	
	oldPoint=lastPoint;
	map.removeOverlay(polygonNumbers[polygonNum]);
	polygonNumbers[polygonNum] = new GPolygon(points,'#0a68f5', 2, 0.7,'#0a68f5', 0.5);
	map.addOverlay(polygonNumbers[polygonNum]);
	map.closeInfoWindow();
	map.removeOverlay(latlongmarker);
	
		
	var myIcon = new GIcon(dotIcon);\n
	myIcon.image = 'images/BLUEDOTMarker.gif';\n;
	dotPolygon[dotPolygonnum] = new GMarker(lastPoint, {icon:myIcon});\n;
	
	var thismarker = dotPolygon[dotPolygonnum];
	var thisdotPolygonNum = dotPolygonnum;
	dotPolygonMessage[dotPolygonnum] = '<div class=\'infoWindowDiv\'><p><b>Distance from First Point</b>:<br>'+totalDistanceM+' mi. | '+totalDistanceK+' km.</p><p><b>Distance from Previous Point</b>:<br>'+myDistanceM+' mi. | '+myDistanceK+' km. </p></div>';
	
	tempPolygonOff=false; windowopen=false;
		
	if (quickPolygon) {GEvent.addListener(thismarker, \"mouseover\", function() {notMarker = false;});
	GEvent.addListener(thismarker, \"mouseout\", function() {notMarker = true;});
	GEvent.addListener(thismarker, \"infowindowopen\", function() {quickPolygon=false; tempPolygonOff=true; windowopen=true; });
	GEvent.addListener(thismarker, \"infowindowclose\", function() {windowopen=false;});}
	
		
    GEvent.addListener(thismarker, \"click\", function() {
	
	var html = dotPolygonMessage[thisdotPolygonNum];
	if (quickPolygon) { html = html+'<div class=\'infoWindowDiv\'><ul class=\'infonav\'><li><a class=\'nav\' href=\'javascript:{quickPolygonOff()}\'>Complete Polygon</a></li><li><a class=\'nav\' href=\'javascript:{removeLastPolygonPoint()}\'>Undo Last Polygon Point</a></li><li><a class=\'nav\' href=\'javascript:{cancelPolygon()}\'>Cancel Polygone</a></li></ul></div>'; }
	else {tempPolygonOff=false; windowopen=false; quickPolygon=false;}
	thismarker.openInfoWindowHtml(html);
	
	
    });
	
	map.addOverlay(thismarker);\n;	
		
	var msg = '~' + coordinates4 +polygonNum;
	polygonControl[polygonControlnum] = msg;
	polygonControlnum = polygonControlnum+1
	dotPolygonnum = dotPolygonnum+1;
	

	  }\n;	  
	  
	  
	  
	  
	  
	  
	  
	  

	
function mapCenterInfoWindow() {
	map.closeInfoWindow();
	map.removeOverlay(latlongmarker);

	var myIcon = new GIcon(smallIcon);\n
	myIcon.image = 'images/SMALLCENTERMarker.gif';\n;
	var centerMark = new GMarker(lastPoint, {icon:myIcon});\n;
	map.addOverlay(centerMark);\n;
	
	if (parent.document.mapwindowform.addressBunch.value=='') 
		{var msg = '~' +coordinates3;}
		else {var msg = '\\r~' + coordinates3;}
	parent.document.mapwindowform.addressBunch.value += msg;
	
	}\n;  
	

function showmap(){
	if (GBrowserIsCompatible()) {
		\n
		map = new GMap2(document.getElementById(\"map\"));\n";
      	if (empty($this->centerMap)){
			echo 
			 "map.setCenter(new GLatLng(".$this->validPoints[0]['lat'].",".$this->validPoints[0]['long']."), ".$this->zoomLevel.");\n";
       		}
		else{
        	echo $this->centerMap;
        }
		echo "
	
		map.enableContinuousZoom();
		map.continuousZoomEnabled();
		
		
		\n";
		if ($this->showControl){
			if ($this->controlType == 'small'){echo "map.addControl(new GSmallMapControl());\n";}
			if ($this->controlType == 'large'){echo "map.addControl(new GLargeMapControl());\n";}
		}
		if ($this->showType){
			echo "map.addControl(new GMapTypeControl());\n";
			echo "map.addControl(new GOverviewMapControl(new GSize(150,150)));\n";
		}
		$numPoints = count($this->validPoints);
		for ($g = 0; $g<$numPoints; $g++){;
        	echo "var point".$g." = new GPoint(".$this->validPoints[$g]['long'].",".$this->validPoints[$g]['lat'].")\n;";		  
			if ($this->validPoints[$g]['newMarkerColor']!=null){
				if (substr($this->validPoints[$g]['newMarkerColor'], 0, 5)=="SMALL"){
					echo " var myIcon = new GIcon(smallIcon);\n
			  		myIcon.image = 'images/".$this->validPoints[$g]['newMarkerColor']."Marker.png';\n";
				}
				else
				if (substr($this->validPoints[$g]['newMarkerColor'], 0, 3)=="DOT"){
					echo " var myIcon = new GIcon(dotIcon);\n
			  		myIcon.image = 'images/BLUEDOTMarker.gif';\n";
				}
				else
				if (substr($this->validPoints[$g]['newMarkerColor'], 0, 3)=="PUS"){
					echo " var myIcon = new GIcon(pushpinIcon);\n
			  		myIcon.image = 'images/PUSHPINRed.png';\n";
				}
				else
				if (substr($this->validPoints[$g]['newMarkerColor'], 0, 6)=="NUMBER"){
					echo " 
					myMarkerNumber=myMarkerNumber+1;
					var myIcon = new GIcon(baseIcon);\n
					if (myMarkerNumber>75) {myIcon.image = 'images/moreMarker.png';\n}
					else {myIcon.image = 'images/'+myMarkerNumber+'Marker.png';
					myIcon.printImage = 'images/gif/'+myMarkerNumber+'Marker.gif';
  					myIcon.mozPrintImage = 'images/gif/'+myMarkerNumber+'Marker.gif';
					\n}";
				}
				else 
// PolyLine
				if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="LINE"){
				echo "var myIcon = new GIcon(verysmallIcon);\n
				batchlineNumalt = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
				if (batchlineNumalt !== batchlineNum) {pts = [];}
				batchlineNum = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
				map.removeOverlay(batchlineNumbers[batchlineNum]);\n
			  	myIcon.image = 'images/BLUEDOTMarker.gif';\n
				pts.push(new GLatLng(".$this->validPoints[$g]['lat'].", ".$this->validPoints[$g]['long']."));\n";
				if ($this->validPoints[$g]['passedhtmlMessage']==null) {
				echo "var lineColor = '#FF0000'";
				}
				else {
				echo "var lineColor = '".$this->validPoints[$g]['passedhtmlMessage']."'";
				}
				echo " 
				batchlineNumbers[batchlineNum] = new GPolyline(pts,lineColor, 4, 0.5);\n
				map.addOverlay(batchlineNumbers[batchlineNum]); \n
				";
				}
				else
				
// PolyGon     
				if (substr($this->validPoints[$g]['newMarkerColor'], 0, 7)=="POLYGON"){
				echo "var myIcon = new GIcon(verysmallIcon);\n
				batchpolygonNumalt = ".substr($this->validPoints[$g]['newMarkerColor'], 7, 7).";\n
				if (batchpolygonNumalt !== batchpolygonNum) {pts = [];}
				batchpolygonNum = ".substr($this->validPoints[$g]['newMarkerColor'], 7, 7).";\n
				map.removeOverlay(batchpolygonNumbers[batchpolygonNum]);\n
			  	myIcon.image = 'images/SMALLGREYMarker.png';\n
				pts.push(new GLatLng(".$this->validPoints[$g]['lat'].", ".$this->validPoints[$g]['long']."));\n";
				if ($this->validPoints[$g]['passedhtmlMessage']==null) {
				echo "var polygonColor = '#0a68f5'";
				}
				else {
				echo "var polygonColor = '".$this->validPoints[$g]['passedhtmlMessage']."'";
				}
				echo " 
				batchpolygonNumbers[batchpolygonNum] = new GPolygon(pts,polygonColor, 2, 0.7,polygonColor,0.5);\n
				map.addOverlay(batchpolygonNumbers[batchpolygonNum]); \n
				";
				}				
				
// Center				
				
				else if (substr($this->validPoints[$g]['newMarkerColor'], 0, 6)=="CENTER"){
				echo "var myIcon = new GIcon(verysmallIcon);\n ";
				if ($this->validPoints[$g]['passedhtmlMessage']!=null){echo "map.setCenter(new GLatLng(".$this->validPoints[$g]['lat'].",".$this->validPoints[$g]['long']."), ".$this->validPoints[$g]['passedhtmlMessage'].");";}
				else {echo "map.setCenter(new GLatLng(".$this->validPoints[$g]['lat'].",".$this->validPoints[$g]['long']."), ".$this->zoomLevel.");";}
				}
			  	else {
					echo "var myIcon = new GIcon(baseIcon);\n
			  		myIcon.image = 'images/".$this->validPoints[$g]['newMarkerColor']."Marker.png';\n
					myIcon.printImage = 'images/".$this->validPoints[$g]['newMarkerColor']."Marker.gif';
  					myIcon.mozPrintImage = 'images/".$this->validPoints[$g]['newMarkerColor']."Marker.gif';";}
			 	}
			else {
				echo "var myIcon = new GIcon(baseIcon);\n   
			  	myIcon.image = 'images/GREYMarker.png';\n";}
			  	echo "var marker".$g." = new GMarker(point".$g.", {icon:myIcon});\n;
              	map.addOverlay(marker".$g."); \n";
				
				echo "
				GEvent.addListener(marker".$g.", \"mouseover\", function() {notMarker = false;});
				GEvent.addListener(marker".$g.", \"mouseout\", function() {notMarker = true;});
				";
				
// Opening infoWindow
			  	echo "GEvent.addListener(marker".$g.", \"click\", function() {\n";
              	if ($this->validPoints[$g]['passedhtmlMessage']!=null){
				
              		echo "
					thisMarkerLat = '".$this->validPoints[$g]['lat']."';
					thisMarkerLong = '".$this->validPoints[$g]['long']."';
					multiAddressValue = thisMarkerLat+','+thisMarkerLong;
					multiAddressDisplay = '".$this->validPoints[$g]['passedhtmlMessage']."'
					carryG = marker".$g."
					marker".$g.".openInfoWindowHtml(\"<div style='font-family: Arial; font-size: 10pt;  width: 200px'>".$this->validPoints[$g]['passedhtmlMessage']."<p><a class = 'smallLink' href='javascript:void(0);' style='font-size: 8pt;' onfocus='insertOption();'>Add to Multi-Directions</a></p></div>\");\n";
              	}
				else{
             		echo "
					thisMarkerLat = '".$this->validPoints[$g]['lat']."';
					thisMarkerLong = '".$this->validPoints[$g]['long']."';
					multiAddressValue = thisMarkerLat+','+thisMarkerLong;
					multiAddressDisplay = '".$this->validPoints[$g]['passedAddress']."'
					carryG = marker".$g.";
					marker".$g.".openInfoWindowHtml(\"<div style='font-family: Arial; font-size: 10pt;  width: 200px'>".$this->validPoints[$g]['passedAddress']."<p><a class = 'smallLink' href='javascript:void(0);' style='font-size: 8pt;' onfocus='insertOption();'>Add to Multi-Directions</a></p></div>\");\n";
                }
				
				
				
				echo "});\n";
// Removing marker on a double click
			  	echo "GEvent.addListener(marker".$g.", \"dblclick\",  function(){ 
			    	map.removeOverlay(marker".$g."); 
					map.closeInfoWindow();  
		      	}); \n";
		}  
		
				
				
				
// Adding  and removing small marker --> getting longitude and latitude	- Continuing adding lines and polygons
			  echo "GEvent.addListener(map, \"movestart\", function () {center = map.getCenter();}); \n;
			  
			  GEvent.addListener(map, \"click\", function(overlay, point) 
			  {
			  if (tempLineOff) {quickLine=true; notMarker=true; tempLineOff=false;}
			  if (tempPolygonOff) {quickPolygon=true; notMarker=true; tempPolygonOff=false;}
			  if ((quickLine) && (notMarker)) {
			  lastPoint = point;
			  coordinates = point.lat()+','+point.lng()+'||LINE';
			  addLineInfoWindow2();}
			  else if ((quickLine) && !(notMarker)) {notMarker=true;}
			  \n
			  
			  if ((quickPolygon) && (notMarker)) {
			  lastPoint = point;
			  coordinates4 = point.lat()+','+point.lng()+'||POLYGON';
			  addPolygon();}
			  else if ((quickPolygon) && !(notMarker)) {notMarker=true;}
			  \n
			  }); \n
			  
			  

			  GEvent.addListener(map, \"dblclick\", function(overlay, point) 
			  {
			  	map.removeOverlay(latlongmarker);
				map.closeInfoWindow(latlongmarker);
			  
			  	if (point) {map.setCenter(center);\n}
			  	var myIcon = new GIcon(smallIcon);\n
			 	myIcon.image = 'images/SMALLREDMarker.png';\n;
			  	latlongmarker = new GMarker(point, {icon:myIcon, draggable:true });\n;
			  	map.addOverlay(latlongmarker);\n;
				
				GEvent.addListener(latlongmarker, \"mouseover\", function() {notMarker = false;});
				GEvent.addListener(latlongmarker, \"mouseout\", function() {notMarker = true;});
								
			  	GEvent.addListener(latlongmarker, 'click', function() {
			  		var point = latlongmarker.getPoint();
					lastPoint = point;
					mapZoom = map.getZoom();
					coordinates = point.lat()+','+point.lng()+'||LINE';
					coordinates2 = point.lat()+','+point.lng();
					coordinates3 = point.lat()+','+point.lng()+'|'+mapZoom+'|CENTER';
					coordinates4 = point.lat()+','+point.lng()+'||POLYGON';
					latlongmarker.openInfoWindowHtml(\"<div id='infoDiv' class='infoWindowDiv'>Latitude is \"+point.lat()+\"<br>Longitude is \"+point.lng()+\"<ul class='infonav'><li><a class='nav' href='javascript:{var points = []; lineNum = lineNum+1; newLine=true;  tempnotMarker = false; quickLine=true; addLineInfoWindow2()}'>Line Tool</a></li><li><a class='nav' href='javascript:{var points = []; polygonNum = polygonNum+1; newPolygon=true;  tempnotMarker = false; quickPolygon=true; addPolygon()}'>Polygon Tool</a></li><li><a class='nav' href='javascript:addMarkerInfoWindow()'>Customized Marker</a></li><li><a class='nav' href='javascript:mapCenterInfoWindow()'>Save Zoom & Mark Center</a></li></ul></div>\");\n;
  			   	});
			   	GEvent.addListener(latlongmarker, 'dblclick', function() {
					map.removeOverlay(latlongmarker);
					map.closeInfoWindow(latlongmarker);
  			   	});
			  	return latlongmarker;\n;
			  }); 
			  \n";


		echo "    	
		}
		}\n

		

		//]]>\n
		
		

		
			
		
    
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