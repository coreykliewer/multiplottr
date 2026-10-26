<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<?php

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
*  
*  
*  
*/


var $smallMarker = false;
var $myPolyline=false;
var $addtoLegend=true;
var $addtoKML=true;
var $thisMarkerIcon ="";
var $thisMarkerDescription="";
var $noAddresses="";

var $useThislat ="";
var $useThislong ="";
var $useThiszoomLevel ="";
var $centerIsDefined = false;

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
	
function setTotalPlotted(){
    $this->totalPlotted = '0';
    }	


    function fetchURL($url) {
        return file_get_contents($url);

    }



function addAddress($address,$htmlMessage=null,$newMarkerColor=null, $groupName=null, $howManyPlotted=null){
	
  $newLongitude = "";
  $newLatitude = "";
  $precision = "";
  $longiLat = false;
  /* Set errorBig to false*/
  $errorBig=false;
  $oldaddress=$address;
  echo "<script language='javascript'> loadingBox.innerHTML='Looking Up ".$address."';</script>\n";

  if (!is_string($address)){
    die("All Addresses must be passed as a string");
  }
  if (substr($address, 0, 1)=="~") {
    $myPolyline=true;
	$address = str_replace(array("~", " "), "", $address);
	list ($myLatitude, $myLongitude) = split (',', $address);
	$longiLat = true;
  }
  if (substr($address, 0, 7)=="ENCODED") {
	$myPolyline=true;
	$address = str_replace(array("ENCODED"), "", $address);
	$myLatitude = $address;
	$myLongitude = "";
	$longiLat = true;
  }	
  /* old code: http://maps.google.com/maps/geo?&output=xml&oe=utf8&sensor=false&key=".$this->apiKey."&q= */
  /*Using Google's New GeoCoding Method - gives us another  2,500 locations to plot - June 3, 2010*/
  if ((!$longiLat)&&(!$errorBig)) {  $apiURL = "http://maps.google.com/maps/api/geocode/xml?address=";
    echo "<script language='javascript'> loadingBox.innerHTML='Google Geocoding'; </script>\n";
	$request_url = "http://maps.google.com/maps/api/geocode/xml?address=" . urlencode($oldaddress) ."&sensor=false";
	$xml = simplexml_load_file($request_url); 
	$results = $this->xml2array($xml);
	
	$status = $xml->status;
	$newLatitude = $xml->result->geometry->location->lat;
    $newLongitude = $xml->result->geometry->location->lng;
	
	/*Using Google's Old GeoCoding Method - gives us another  2,500 locations to plot - June 3, 2010*/
	if ($status!="OK") {
	  /*echo "<script language='javascript'> loadingBox.innerHTML=' 1) Looking Up ".$address."';</script>\n";*/
      $address = rawurlencode($address);
	  /*echo "<script language='javascript'> loadingBox.innerHTML=' 2) Looking Up ".$address."';</script>\n";*/
      $url = "http://maps.google.com/maps/geo?q=".$address."&output=csv&key=".$this->apiKey;
	  /*echo "<script language='javascript'> loadingBox.innerHTML=' 3) Looking Up ".$url."';</script>\n";*/
      $_result = false;
	  $test = $this->fetchURL($url);
	 /* echo "<script language='javascript'> loadingBox.innerHTML=' 4) Looking Up ".$test."';</script>\n";*/
	  /*echo "<script language='javascript'> parent.document.mapwindowform.address1.value='Result Code is ".$test."';</script>\n";*/
	  if($_result == $test) {$errorBig=true;} else
      /*if($_result != $test)*/ {
        $_result_parts = explode(',',$test);
  	    //Use this to view Result codes in the Address Bar!!!!!
	    /*echo "<script language='javascript'> parent.document.mapwindowform.address1.value='Result Code is ".$_result_parts[0]."'; </script>\n";*/
        if($_result_parts[0] != 200){
          if($_result_parts[0] == 620){echo "<script language='javascript'> loadingBox.innerHTML='Google Geocoding';</script>\n";
   		    $delay=0;
		    $countit=0;
		    $geocode_pending = true;
		    while ($geocode_pending) {$request_url = "http://maps.google.com/maps/api/geocode/xml?address=" . urlencode($oldaddress);
		      $xml = simplexml_load_file($request_url); 
		      $status = $xml->Response->Status->code;
		      if (strcmp($status, "200") == 0) {	    // Successful geocode
			    echo "<script language='javascript'> loadingBox.innerHTML='Google found it!';</script>\n";
			    $errorBig=false;
			    $geocode_pending = false;
			    $coordinates = $xml->Response->Placemark->Point->coordinates;
                $coordinatesSplit = split(",", $coordinates);
                // Format: Longitude, Latitude, Altitude
			    $errorBig=false;
                $newLatitude = $coordinatesSplit[1];
                $newLongitude = $coordinatesSplit[0];} else if (strcmp($status, "620") == 0) {
			    // sent geocodes too fast
                $delay += 50000;
			    echo "<script language='javascript'> loadingBox.innerHTML='Google Search going to quick, slowing down a little.';</script>\n";
			    if ($countit==10) {$geocode_pending = false;  $errorBig=true;}
			    $countit += 1;} else {$geocode_pending = false;
			    $errorBig=true;}
			  usleep($delay);}}
		  if($_result_parts[0] == 602) {$errorBig=true;}
        } else {
	      echo "<script language='javascript'> loadingBox.innerHTML='Google Geocoding';</script>\n";
          $newLatitude = $_result_parts[2];
          $newLongitude = $_result_parts[3];
	    }
      } 
    } 
  }

		$htmlMessage = str_replace(array("\r", "\n"), "", $htmlMessage);
		if ($newMarkerColor=="") {$newMarkerColor = $this->defaultMarker;}
		if ($groupName=="") {$groupName = $this->defaultGroupName;}
		$newMarkerColor = str_replace(array("\r", "\n", " ",), "", $newMarkerColor);
		$firstFour = substr($newMarkerColor, 0, 4);
		$firstFour = strtoupper($firstFour);
		if (strcmp($firstFour, "HTTP")!==0) {
		$newMarkerColor = strtoupper($newMarkerColor);
		$newMarkerColor = str_replace("MARKER.PNG", "", $newMarkerColor);
		$newMarkerColor = str_replace("MARKER.GIF", "", $newMarkerColor);}
		$colorNumber = 0;
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
		echo "<script language='javascript'>\n";
// echo " alert('".$groupName."');\n";
		echo "if (allAddresses=='')\n";
//			if ($colorNumber==14)
//			{echo "msg = '\\r';\n";}
//			else
		if ($colorNumber==16)
		{
		  $this->howManyPlotted = $this->howManyPlotted+1;
		  $newMarkerColor="NUMBER";
		  echo "{
			   msg = '~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."|".$groupName."';
			 }
	         else {
			   msg = '\\r~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."|".$groupName."';
			 }
			 var encodeAddress = escape('`~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."');
			 allMapUrls += encodeAddress;
			 \n";
			 
	    }
		else if ($colorNumber==14){
		$centerIsDefined = true;
		$useThislat = $myLatitude;
		$useThislong = $myLongitude;
		$useThiszoomLevel = $htmlMessage;
		
		  echo "{
		    msg = '~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."';
	      }
	      else {
		    msg = '\\r~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."';
		  }
			 var encodeAddress = escape('`~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."');
			 parent.document.mapwindowform.centerLatLong.value='".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."';
			 allMapUrls += encodeAddress;
		  \n";
		 
		 
		}
		
		
		else {
		  echo "{
		    msg = '~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."';
		
	      }
	      else {
		    msg = '\\r~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."';
			
		  }
			 var encodeAddress = escape('`~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."');
			 allMapUrls += encodeAddress;
		  \n";
		}
		
		echo "allAddresses += msg;\n";
		
		echo "</script>\n";
		
		//LRV - store info for saving map
		$this->totalPlotted = $this->totalPlotted+1;
		echo "<script>allMapInfo = allAddresses; \n";
		echo "allMapLink = allMapUrls; \n 
		      
		      </script>\n";
// document.getElementById('loadingBox').innerHTML=('finding ".$this->totalPlotted." Addresses');			  
		//$fp = fopen("data.txt","a");
         //  if(!$fp) {
         //     echo 'Error: Cannot open file.';
         //     exit;
         //     }
         //   fwrite($fp, $oldaddress."|".$htmlMessage."|".$newMarkerColor."|".$groupName."\n");
         //   fclose($fp);	
	    }
	else
			
	if ($errorBig) { 
	echo "<script language='javascript'> loadingBox.innerHTML='Yahoo Geocoding';</script>\n";
	/*$this->noAddresses="Wrong Address";
            	echo "<script language='javascript'>\n";
            	echo " {parent.document.mapwindowform.unplotBunch.value += '".$oldaddress."\\r';
				   parent.document.getElementById('unplotLink').className='unselected2';}\n";
            	echo "</script>\n";*/
	
	//$apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=_XJn5wXV34E9LIeyrXHWP1r81tV1Glu.ogFsvYuRNBfMPmQVYl144RcNldhGCr67qg4-&location=";
       // $apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=UlXDNO_V34FMvr2cHCcYZNK0JK5Buyws0O.JKUHYJC7piHqxBCV0.q7ecitYtaXpO8s-&location=";
	   
	  $Yahooterm = $_SERVER['HTTP_REFERER'];
       if (substr($term, 0, 22)=="http://www.multiplotte"){
       $apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=IoJOenrV34FXxXIBhjj1vxSQzzxPoEkwhaQ1.ZW0OnEZcTXB1wNTo7eel268G7l2A_A--&location=";
       }
       else
       if (substr($term, 0, 22)=="http://multiplotter.co"){
       $apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=_XJn5wXV34E9LIeyrXHWP1r81tV1Glu.ogFsvYuRNBfMPmQVYl144RcNldhGCr67qg4--&location=";
       }
       else{
       $apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=UlXDNO_V34FMvr2cHCcYZNK0JK5Buyws0O.JKUHYJC7piHqxBCV0.q7ecitYtaXpO8s--&location=";
       }

		$addressData = file_get_contents($apiURL.rawurlencode($oldaddress)); 
		$results = $this->xml2array($addressData);
		
		
		
		if (empty($results['ResultSet']['Result']['Longitude'])){
		    if ($oldaddress<>"") { $this->noAddresses="Wrong Address";
            	echo "<script language='javascript'>\n";
            	echo " {parent.document.mapwindowform.unplotBunch.value += '".$oldaddress."\\r';
				   parent.document.getElementById('unplotLink').className='unselected2';}\n";
            	echo "</script>\n";}
			}
		else {
		    $pointer = count($this->validPoints);
			$this->validPoints[$pointer]['lat']= $results['ResultSet']['Result']['Latitude'];
			$this->validPoints[$pointer]['long']= $results['ResultSet']['Result']['Longitude'];
			$myLatitude= $results['ResultSet']['Result']['Latitude'];
			$myLongitude= $results['ResultSet']['Result']['Longitude'];
			$this->validPoints[$pointer]['passedAddress'] = $oldaddress;
			$this->validPoints[$pointer]['passedhtmlMessage'] = $htmlMessage;
			$this->validPoints[$pointer]['newMarkerColor'] = $newMarkerColor;
			$this->validPoints[$pointer]['groupName'] = $groupName;
			if ($htmlMessage=="") {$htmlMessage=$oldaddress;}
						
		echo "<script language='javascript'>\n";
		echo "if (allAddresses=='')\n";
		if ($colorNumber==16)
		{
		$this->howManyPlotted = $this->howManyPlotted+1;
		$newMarkerColor="NUMBER";
		     echo "{
			   msg = '~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."|".$groupName."';
			 }
	         else {
			   msg = '\\r~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."|".$groupName."';
			 }
			 var encodeAddress = escape('`~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."');
			 allMapUrls += encodeAddress;
			 \n";
			 
	    }
		else {
		  echo "{
		    msg = '~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."';
	      }
	      else {
		    msg = '\\r~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."';
		  }
		  var encodeAddress = escape('`~".$myLatitude.",".$myLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."');
			 allMapUrls += encodeAddress;
		  \n";
		}
		echo "allAddresses += msg; \n </script>\n";
		
		//LRV - store info for saving map
		$this->totalPlotted = $this->totalPlotted+1;
		echo "<script>allMapInfo = allAddresses; \n";
		echo "allMapLink = allMapUrls; \n 
			  </script>\n";
//		      document.getElementById('loadingBox').innerHTML=('finding ".$this->totalPlotted." Addresses');
			  
					
		//$fp = fopen("data.txt","a");
         //  if(!$fp) {
         //     echo 'Error: Cannot open file.';
         //     exit;
         //    }
         //   fwrite($fp, $oldaddress."|".$htmlMessage."|".$newMarkerColor."|".$groupName."|Found by Yahoo\n");
         //   fclose($fp);
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
		if ($htmlMessage=="") {$htmlMessage=$oldaddress;}
		echo "<script language='javascript'>\n";
		echo "if (allAddresses=='')\n";
		if ($colorNumber==16)
		{
		$this->howManyPlotted = $this->howManyPlotted+1;
		$newMarkerColor="NUMBER";
		     echo "{
			   msg = '~".$newLatitude.",".$newLongitude."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."|".$groupName."';
			 }
	         else {
			   msg = '\\r~".$newLatitude.",".$newLongitude."|".$htmlMessage."|".$newMarkerColor." ".$this->howManyPlotted."|".$groupName."';
			 }
			 var encodeAddress = escape('`~".$newLatitude.",".$newLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."');
			 allMapUrls += encodeAddress;
			 \n";
			 
	    }
		else {
		  echo "{
		    msg = '~".$newLatitude.",".$newLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."';
	      }
	      else {
		    msg = '\\r~".$newLatitude.",".$newLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."';
		  }
		  var encodeAddress = escape('`~".$newLatitude.",".$newLongitude."|".$htmlMessage."|".$newMarkerColor."|".$groupName."');
			 allMapUrls += encodeAddress;
		  \n";
		}
		echo "allAddresses += msg; \n </script>\n";
		
		//LRV - store info for saving map
		$this->totalPlotted = $this->totalPlotted+1;
		echo "<script>allMapInfo = allAddresses; \n";
		echo "allMapLink = allMapUrls; \n 
		      </script>\n";
//		      document.getElementById('loadingBox').innerHTML=('finding ".$this->totalPlotted." Addresses');
	
		//$fp = fopen("data.txt","a");
          // if(!$fp) {
          //    echo 'Error: Cannot open file.';
          //    exit;
          //    }
          //  fwrite($fp, $oldaddress."|".$htmlMessage."|".$newMarkerColor."|".$groupName."|Found by Google\n");
          //  fclose($fp);
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
function setControl($myControl){
	$this->showControl = $myControl;
}
function setControlSize($myControlSize){
	$this->controlType = $myControlSize;
}
function setaddressCarry($addressCarry){
	$this->addressCarry = $addressCarry;
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
	echo "\n<script src=\"http://maps.google.com/maps?file=api&v=3&key=".$this->apiKey."\" type=\"text/javascript\"></script>
	<script src=\"http://gmaps-utility-library-dev.googlecode.com/svn/trunk/markermanager/src/markermanager.js\" type=\"text/javascript\"></script>
	              
	<script src=\"PolylineEncoder.js\" type=\"text/javascript\"></script>
	<script src=\"extinfowindow.js\" type=\"text/javascript\"></script>
	\n";
}




/**
* @function     showMap
* @description  Displays the Google Map on the page
*/


function showMap(){


echo "<div id=\"map\" style=\"width: ".$this->mapWidth."px; height: ".$this->mapHeight."px;\">\n</div>\n

<script type=\"text/javascript\">\n
//<![CDATA[\n
    var points = [];
var OV;
var OV1
var lastPoint;
var oldPoint='';
var center;
var map;
var infoWindowON = false;
var coordinates;
var coordinates2;
var coordinates3;
var coordinates4;
var batchPoints;
    var pts = [];
var batchOV;
var thisCustomIconButton = false;
var totalDistanceM = 0;
var totalDistanceK = 0;
var myDistanceK = 0;
var myDistanceM = 0;
var polygonNumbers = new Array();
var polygonNum = 0;
var batchpolygonNumbers = new Array();
var batchpolygonNum = 0;
var batchpolygonNumalt = 0;
var polygoncolor = '#FF0000';
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
var lineNumbers = new Array();
var lineNum = 0;
var batchlineNumbers = new Array();
var batchlineNum = 0;
var batchlineNumalt = 0;
var linecolor = '#FF0000';
var globalPolylineColor = '#FF0000';
var globalbatchpolylineNum = 0;
var polylineNumbers = new Array();
var polylineNum = 0;
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
var centerPoint='UNDEFINED';
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
var customIcon = new GIcon();
customIcon.iconSize = new GSize(21, 34);
customIcon.iconAnchor = new GPoint(11, 17);
customIcon.infoWindowAnchor = new GPoint(5, 0);
var circleIcon = new GIcon();
circleIcon.iconSize = new GSize(28, 28);
circleIcon.iconAnchor = new GPoint(14, 28);
circleIcon.infoWindowAnchor = new GPoint(15, 3);
circleIcon.infoShadowAnchor = new GPoint(24, 31);
var miniIcon = new GIcon();
miniIcon.iconSize = new GSize(20, 20);
miniIcon.iconAnchor = new GPoint(10, 20);
miniIcon.infoWindowAnchor = new GPoint(10, 10);
var labelMarkerON = false;
var labelMarkerText = 'A';
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
var bounds = new GLatLngBounds();
var centerOnMarkers=true;
var allMarkersArray = new Array();
var gmarkersA = []; 

function insertOption()
  {
  
  var y=parent.document.createElement('option');
  
  multiAddressDisplay = multiAddressDisplay.replace('(', '[','g');
  multiAddressDisplay = multiAddressDisplay.replace(')', ']','g');
  multiAddressDisplay = multiAddressDisplay.substr(0,100);

  
  
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
myIcon.image = 'images/BLACKMarker.png';
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
infoWindowON=false;


}\n;

function removeLastLinePoint(){ 
quickLine=true; 
var dotNumber = dotLinenum-1; 
map.removeOverlay(dotLine[dotNumber]); 
dotLinenum = dotLinenum-1; 
map.removeOverlay(lineNumbers[lineNum]); 
lineControlnum = lineControlnum-1; points.pop(); 
lineNumbers[lineNum] = new GPolyline(points,globalPolylineColor, 4, 0.5); 
if (dotLinenum>>0) {
map.addOverlay(lineNumbers[lineNum]);
} 
infoWindowON=false;
map.closeInfoWindow(); 
 }

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
windowopen=true; 
infoWindowON=true; 
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
	
	//LRV - store info for saving map
	parent.document.savemapform.mapinfo.value = parent.document.mapwindowform.addressBunch.value;
	
	map.removeOverlay(latlongmarker);
	map.closeInfoWindow();
	if (thisMarker.substr(0,5)==\"SMALL\") {var myIcon = new GIcon(smallIcon);} else 
	{var myIcon = new GIcon(baseIcon);}\n
	if (thisMarker.substr(0,5)==\"NUMBE\") {
	customMarkerNum++;  
	myIcon.image = 'images/'+customMarkerNum+'Marker.png';\n } else
	{myIcon.image = 'images/'+thisMarker+'Marker.png';\n}
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

	thisCustomIconButton = false;
}\n;
  
  
function addMarkerInfoWindowLine() {

latlongmarker.openInfoWindowHtml(\"<div id='infoDiv' class='infoWindowDiv'>Marker Color<br /><select class='formfields' id='markerType' name='markerType'><option selected='selected'>RED</option><option>BLUE</option><option>GREEN</option><option>GREY</option><option>ORANGE</option><option>YELLOW</option><option>SMALLRED</option><option>SMALLBLUE</option><option>SMALLGREEN</option><option>SMALLGREY</option><option>SMALLORANGE</option><option>SMALLYELLOW</option><option>NUMBER</option></select><br />HTML Description<br /><textarea class='formfieldstextarea' name='thisDescription'  id='thisDescription'  rows='5' /></textarea><a  class='nav' href='javascript:void(0)' onclick='addthisMarker()'>Submit</a></div>\");
}\n;	  
  
  
  
function addMarkerInfoWindow() {


    latlongmarker.openInfoWindowHtml(\"<div id='infoDiv' class='infoWindowDiv'>Marker Color<br /><select class='formfields' id='markerType' name='markerType'><option selected='selected'>RED</option><option>BLUE</option><option>GREEN</option><option>GREY</option><option>ORANGE</option><option>YELLOW</option><option>SMALLRED</option><option>SMALLBLUE</option><option>SMALLGREEN</option><option>SMALLGREY</option><option>SMALLORANGE</option><option>SMALLYELLOW</option><option>NUMBER</option></select><br />HTML Description<br /><textarea class='formfieldstextarea' name='thisDescription'  id='thisDescription'  rows='5' /></textarea><a  class='nav' href='javascript:void(0)' onclick='addthisMarker()'>Submit</a></div>\");
}\n;	
  
function addLineInfoWindow2() {
document.getElementById('map').firstChild.firstChild.style.cursor ='crosshair'; 
if (globalbatchpolylineNum>=lineNum){lineNum=globalbatchpolylineNum+1;}
if (newLine) { totalDistanceM = 0; totalDistanceK = 0; myDistanceK = 0; myDistanceM = 0; oldPoint=''; newLine=false;}
points.push(lastPoint);
if (oldPoint=='') {oldPoint=lastPoint;} 
myDistanceK = oldPoint.distanceFrom(lastPoint)/1000;
myDistanceM = myDistanceK*.621371192;
myDistanceM = Math.round(myDistanceM*10)/10;
myDistanceK = Math.round(myDistanceK*10)/10;

totalDistanceM = totalDistanceM+myDistanceM;
totalDistanceK = totalDistanceK+myDistanceK;
totalDistanceM = Math.round(totalDistanceM*10)/10;
totalDistanceK = Math.round(totalDistanceK*10)/10;

oldPoint=lastPoint;
if (lineNumbers[lineNum]) {map.removeOverlay(lineNumbers[lineNum]);}
lineNumbers[lineNum] = new GPolyline(points,globalPolylineColor, 4, 0.5);
map.addOverlay(lineNumbers[lineNum]);
map.closeInfoWindow();
map.removeOverlay(latlongmarker);
	
var myIcon = new GIcon(dotIcon);\n
myIcon.image = 'images/BLUEDOTMarker.gif';\n;
dotLine[dotLinenum] = new GMarker(lastPoint, {icon:myIcon});\n;
var thismarker = dotLine[dotLinenum];
var thisdotLineNum = dotLinenum;
dotLineMessage[dotLinenum] = '<div class=\'infoWindowDiv\'><p><b>Distance from First Point</b>:<br>'+totalDistanceM+' mi. | '+totalDistanceK+' km.</p><p><b>Distance from Previous Point</b>:<br>'+myDistanceM+' mi. | '+myDistanceK+' km. </p><ul class=\'infonav\'><li><a class=\'nav\' href=\'javascript:{quickLineOff()}\'>Complete Line</a></li><li><a class=\'nav\' href=\'javascript:{removeLastLinePoint()}\'>Undo Last Line Point</a></li><li><a class=\'nav\' href=\'javascript:{cancelLine()}\'>Cancel Line</a></li></ul></div>';

	
	
if (quickLine) {
    GEvent.addListener(thismarker, 'mouseover', function() {windowopen=true;});
	GEvent.addListener(thismarker, 'mouseout', function() {windowopen=false;});
	GEvent.addListener(thismarker, 'infowindowopen', function() {windowopen=true;});
	GEvent.addListener(thismarker, 'infowindowclose', function() {windowopen=false;});
}
	
    GEvent.addListener(thismarker, \"click\", function() {
var html = dotLineMessage[thisdotLineNum];
if (quickLine) { 
windowopen=true;

}
thismarker.openInfoWindowHtml(html);

    });
map.addOverlay(thismarker);\n;	
	
var msg = '~' + coordinates+'|'+globalPolylineColor+'|LINE'+lineNum;
lineControl[lineControlnum] = msg;
lineControlnum = lineControlnum+1
dotLinenum = dotLinenum+1;

  }\n;  
  

function firstPolylinePoint() {document.getElementById(\"infoDiv\").innerHTML = '<ul class=\"infonav\">Choose your Polygon Color:</br><li><form id=\"polygonForm\" name=\"polygonForm\"><table class=\"polygonTable\"><tr><td onclick=\"javascript:{globalPolylineColor=\'#CC0000\'; addLineInfoWindow2()}\" class=\"cell1\">&nbsp;</td><td onclick=\"javascript:{globalPolylineColor=\'#FF6600\'; addLineInfoWindow2()}\" class=\"cell2\">&nbsp;</td><td onclick=\"javascript:{globalPolylineColor=\'#FFCC00\'; addLineInfoWindow2()}\" class=\"cell3\">&nbsp;</td><td onclick=\"javascript:{globalPolylineColor=\'#339933\'; addLineInfoWindow2()}\" class=\"cell4\">&nbsp;</td><td onclick=\"javascript:{globalPolylineColor=\'#0A68F5\'; addLineInfoWindow2()}\" class=\"cell5\">&nbsp;</td><td onclick=\"javascript:{globalPolylineColor=\'#990099\'; addLineInfoWindow2()}\" class=\"cell6\">&nbsp;</td></tr><tr><td colspan=\"6\"><input name=\"polygonColor\" type=\"text\" id=\"polygonColor\" value=\"#0A68F5\" /></td></tr><tr><td colspan=\"6\"><a class=\"nav\" href=\"javascript:{ globalPolylineColor=document.polygonForm.polygonColor.value; addLineInfoWindow2()}\">Submit</a></td></tr></table></form></li></ul>';}		  
  
  
  

function quickPolygonOff() { 
	infoWindowON=false;

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
    infoWindowON=false;
}\n;

function removeLastPolygonPoint(){ 
infoWindowON=false;
quickPolygon=true; var dotNumber = dotPolygonnum-1; map.removeOverlay(dotPolygon[dotNumber]); dotPolygonnum = dotPolygonnum-1; map.removeOverlay(polygonNumbers[polygonNum]); polygonControlnum = polygonControlnum-1; points.pop(); polygonNumbers[polygonNum] = new GPolygon(points,'#0a68f5', 2, 0.7,globalPolygonColor, 0.5); if (dotPolygonnum>>0) {map.addOverlay(polygonNumbers[polygonNum]);}
infoWindowON=false;
map.closeInfoWindow();  }

function cancelPolygon(){ 
infoWindowON=false;
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
windowopen=true; 
infoWindowON=true;
if (polygonNum>1) {polygonNum=polygonNum-1;}
}







  
  
function addPolygon() {
    document.getElementById('map').firstChild.firstChild.style.cursor ='crosshair'; 
if (globalbatchpolygonNum>=polygonNum){polygonNum=globalbatchpolygonNum+1;}
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
if (polygonNumbers[polygonNum]) {map.removeOverlay(polygonNumbers[polygonNum]);}
polygonNumbers[polygonNum] = new GPolyline(points,globalPolygonColor, 2, 0.7,globalPolygonColor, 0.5);
map.addOverlay(polygonNumbers[polygonNum]);
map.closeInfoWindow();
map.removeOverlay(latlongmarker);
	
var myIcon = new GIcon(dotIcon);\n
myIcon.image = 'images/BLUEDOTMarker.gif';\n;
dotPolygon[dotPolygonnum] = new GMarker(lastPoint, {icon:myIcon});\n;
var thismarker = dotPolygon[dotPolygonnum];
var thisdotPolygonNum = dotPolygonnum;
dotPolygonMessage[dotPolygonnum] = '<div class=\'infoWindowDiv\'<p><b>Polygon Tool</b></p><ul class=\'infonav\'><li><a class=\'nav\' href=\'javascript:{quickPolygonOff()}\'>Complete Polygon</a></li><li><a class=\'nav\' href=\'javascript:{removeLastPolygonPoint()}\'>Undo Last Polygon Point</a></li><li><a class=\'nav\' href=\'javascript:{cancelPolygon()}\'>Cancel Polygone</a></li></ul></div>';
tempPolygonOff=false; windowopen=false;
	
if (quickPolygon) {GEvent.addListener(thismarker, \"mouseover\", function() {notMarker = false;});
GEvent.addListener(thismarker, \"mouseout\", function() {notMarker = true;});
GEvent.addListener(thismarker, \"infowindowopen\", function() {quickPolygon=false; tempPolygonOff=true; windowopen=true; });
GEvent.addListener(thismarker, \"infowindowclose\", function() {windowopen=false; infoWindowON=true;});}
	
GEvent.addListener(thismarker, \"click\", function() {
var html = dotPolygonMessage[thisdotPolygonNum];
if (quickPolygon) { infoWindowON=true;
 }
else {tempPolygonOff=false; windowopen=false; quickPolygon=false;}
thismarker.openInfoWindowHtml(html);

    });
map.addOverlay(thismarker);\n;	
	
var msg = '~' +coordinates4+'|'+globalPolygonColor+'|POLYGON'+polygonNum;
polygonControl[polygonControlnum] = msg;
polygonControlnum = polygonControlnum+1
dotPolygonnum = dotPolygonnum+1;

  }\n;	   
  
  
  
  
function firstPolygonPoint() {document.getElementById(\"infoDiv\").innerHTML = '<ul class=\"infonav\">Choose your Polygon Color:</br><li><form id=\"polygonForm\" name=\"polygonForm\"><table class=\"polygonTable\"><tr><td onclick=\"javascript:{globalPolygonColor=\'#CC0000\'; addPolygon()}\" class=\"cell1\">&nbsp;</td><td onclick=\"javascript:{globalPolygonColor=\'#FF6600\'; addPolygon()}\" class=\"cell2\">&nbsp;</td><td onclick=\"javascript:{globalPolygonColor=\'#FFCC00\'; addPolygon()}\" class=\"cell3\">&nbsp;</td><td onclick=\"javascript:{globalPolygonColor=\'#339933\'; addPolygon()}\" class=\"cell4\">&nbsp;</td><td onclick=\"javascript:{globalPolygonColor=\'#0A68F5\'; addPolygon()}\" class=\"cell5\">&nbsp;</td><td onclick=\"javascript:{globalPolygonColor=\'#990099\'; addPolygon()}\" class=\"cell6\">&nbsp;</td></tr><tr><td colspan=\"6\"><input name=\"polygonColor\" type=\"text\" id=\"polygonColor\" value=\"#0A68F5\" /></td></tr><tr><td colspan=\"6\"><a class=\"nav\" href=\"javascript:{ globalPolygonColor=document.polygonForm.polygonColor.value; addPolygon()}\">Submit</a></td></tr></table></form></li></ul>';}	  
  
  
  


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
//LRV - store info for saving map
parent.document.savemapform.mapinfo.value = parent.document.mapwindowform.addressBunch.value;
}\n;  

var gmarkers = [];
var ii = 0;
var carryMarkerNum = 0;
var side_bar_html = '';
var side_bar_html_all = '';
var group_side_bar_html = '';

function LabelControl() {  }
      LabelControl.prototype = new GControl();

      LabelControl.prototype.initialize = function(map) {
        labelContainer = document.createElement('div');
        labelContainer.style.overflow='auto';
        labelContainer.style.backgroundColor = '#ffffff';
        labelContainer.style.border = '1px solid black';
        labelContainer.style.height='200px';
        labelContainer.style.width='200px';
        labelContainer.style.paddingLeft='5px';
	labelContainer.style.paddingRight='5px';

        map.getContainer().appendChild(labelContainer);
        return labelContainer;
      }

      LabelControl.prototype.getDefaultPosition = function() {
        return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 33));
      }
  
function toggleControl() {  }
      toggleControl.prototype = new GControl();

      toggleControl.prototype.initialize = function(map) {
        toggleContainer = document.createElement('div');
        toggleContainer.style.overflow='auto';
        toggleContainer.style.backgroundColor = '#ffffff';
        toggleContainer.style.border = '1px solid black';
        toggleContainer.style.height='125px';
        toggleContainer.style.width='200px';
        toggleContainer.style.paddingLeft='5px';
	toggleContainer.style.paddingRight='5px';
	toggleContainer.style.marginTop='200px';

        map.getContainer().appendChild(toggleContainer);
        return toggleContainer;
      }

      toggleControl.prototype.getDefaultPosition = function() {
        return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 33));
      }	  


  function logoControl() {  }
      logoControl.prototype = new GControl();
	  yourHTML='<a href=\"http://www.multiplottr.com\" target=\"_blank\"><img src=\"http://www.Multiplottr.com/images/smallLogo.png\" alt=\"Multiplottr.com\"  border=\"0\" /></a>'; 

      logoControl.prototype.initialize = function(map) {
        logoContainer = document.createElement('div');
        logoContainer.style.height='25px';
        logoContainer.style.width='135px';
		logoContainer.innerHTML=yourHTML;
        map.getContainer().appendChild(logoContainer);
        return logoContainer;
      }

      logoControl.prototype.getDefaultPosition = function() {
        return new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(75, 2));
      }	
  
  
  
function myclick(ii) {
        GEvent.trigger(gmarkers[carryMarkerNum], \"click\");
      }
  
GMap.prototype.addOverlays=function(a){
        var b=this;
        for (i=0;i<a.length;i++) {
                try {
                        this.overlays.push(a[i]);
                        a[i].initialize(this);
                        a[i].redraw(true);
                } catch(ex) {
                        alert('err: ' + i + ', ' + ex.toString());
                }
        }
        this.reOrderOverlays();

}; 

var batchMarkers = false;
var markerGroups = { 'group0': [], 'group1': [], 'group2': [], 'group3': [], 'group3': [], 'group4': [], 'group5': [], 'group6': [], 'group7': [], 'group8': [], 'group9': [], 'group10': [], 'group11': [], 'group12': [], 'group13': [], 'group14': [], 'group15': [], 'group16': [], 'group17': [], 'group18': [], 'group19': [], 'group20': [], 'group21': [], 'group22': [], 'group23': [], 'group24': [], 'group25': [], 'group26': [], 'group27': [], 'group28': [], 'group29': [], 'group30': [], 'group31': [], 'group32': [], 'group33': [], 'group34': [], 'group35': [], 'group36': [], 'group37': [], 'group38': [], 'group39': []};

var markerGroupNames = [];
var markerGroupNumber = 0;
var getGroupName;
var writeToGroupLegend = false;
var dontPlot = false;
var polygonPlotted = false;
var kmlmsg='';
polylineEncoder = new PolylineEncoder();


function checkGroupNames(value) {
var i;
var groupNamesLength = markerGroupNames.length;
for (i=0; i<=markerGroupNames.length; i++)
{ 
if (markerGroupNames[i]==value) {markerGroupNumber = i; return true;} else
if (markerGroupNames[i]==undefined && value!=='') { markerGroupNames[i]=value; markerGroupNumber = i; writeToGroupLegend=true; return true;}
}
}


function changeElementsByClassName(fieldName,fieldName2)
{
   var my_array = document.getElementsByTagName('*');
   var i;
   var j;

   for (i=0,j=0;i<my_array.length;i++)
   {
      var c = ' ' + my_array[i].className + ' ';
      if (c.indexOf(' ' + fieldName + ' ') != -1) { my_array[i].className=fieldName2;  }
  
   }
}


function toggleGroup(type) {
        for (var i = 0; i < markerGroups[type].length; i++) {
	var groupmarker = markerGroups[type][i];
	var hidetype='hide'+type;
        if (groupmarker.isHidden()) { map.addOverlay(groupmarker);
          groupmarker.show(); 
	  changeElementsByClassName(hidetype,type);
        } else {
          groupmarker.hide();
		  myBatch.removeMarker(groupmarker);
	      changeElementsByClassName(type,hidetype);
        }
      } 
    }



function showmap(){
if (GBrowserIsCompatible()) {
	\n
	map = new GMap2(document.getElementById(\"map\"));
	map.addControl(new logoControl());
	var pos = new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(5,40));
    map.addControl(new GScaleControl(), pos); 
	
	\n";
	//map.addControl(new iconControl());
	
	if ($centerIsDefined) { echo "map.setCenter(new GLatLng(".$useThislat.",".$useThislong."), ".$useThiszoomLevel.");\n";} else {
	  if (empty($this->centerMap)){
		echo "map.setCenter(new GLatLng(".$this->validPoints[0]['lat'].",".$this->validPoints[0]['long']."), ".$this->zoomLevel.");\n";
      }
	else{echo $this->centerMap;}
	}  
	   
	echo "		
	map.disableDoubleClickZoom();
	map.enableContinuousZoom();

	
	\n";
	if ($this->showControl){
		if ($this->controlType == 'small'){echo "map.addControl(new GSmallMapControl());\n";}
		if ($this->controlType == 'large'){echo "map.addControl(new GLargeMapControl());\n";}
	}
	if ($this->showType){
		echo "map.addControl(new GMapTypeControl());\n";
	}
	if ($this->showOverview){
		echo "map.addControl(new GOverviewMapControl(new GSize(150,150)));\n";
	}
	if ($this->showLegend){echo "map.addControl(new LabelControl());\n";
	if ($this->showGroup) {echo "map.addControl(new toggleControl());\n";}
	}
	
	echo "myBatch = new MarkerManager(map);";
	$numPoints = count($this->validPoints);
	for ($g = 0; $g<$numPoints; $g++){;
	    $addtoLegend=true;
		$addtoKML=true;
        	echo "var point".$g." = new GPoint(".$this->validPoints[$g]['long'].",".$this->validPoints[$g]['lat'].")\n;";		  
		if ($this->validPoints[$g]['newMarkerColor']!=null){
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 5)=="SMALL"){
				echo " var myIcon = new GIcon(smallIcon);\n
		  		myIcon.image = 'images/".$this->validPoints[$g]['newMarkerColor']."Marker.png';
				myIcon.printImage = 'images/gif/".$this->validPoints[$g]['newMarkerColor']."Marker.gif';
  					myIcon.mozPrintImage = 'images/gif/".$this->validPoints[$g]['newMarkerColor']."Marker.gif';
				batchMarkers = true;
				\n";
			}
			else
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="HEAT"){
				echo " var myIcon = new GIcon(dotIcon);\n
		  		myIcon.image = 'images/HEATRED.png';
				batchMarkers = true;
				\n";
			}
			else
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 3)=="DOT"){
				echo " var myIcon = new GIcon(dotIcon);\n
		  		myIcon.image = 'images/BLUEDOTMarker.gif';
				batchMarkers = true;
				\n";
			}
			else
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 6)=="CIRCLE"){
				echo " var myIcon = new GIcon(circleIcon);\n
		  		myIcon.image = 'images/".$this->validPoints[$g]['newMarkerColor'].".png';	
				batchMarkers = true;

				\n";
			}
			else
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="MINI"){
				echo " var myIcon = new GIcon(miniIcon);\n
		  		myIcon.image = 'images/".$this->validPoints[$g]['newMarkerColor'].".png';	
				batchMarkers = true;

				\n";
			}
			else
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="http" or substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="HTTP"){
				echo " var myIcon = new GIcon(customIcon);\n
		  		myIcon.image = '".$this->validPoints[$g]['newMarkerColor']."';
				myIcon.printImage = '".$this->validPoints[$g]['newMarkerColor']."'
  				myIcon.mozPrintImage = '".$this->validPoints[$g]['newMarkerColor']."';
				batchMarkers = true;
				\n";
			}
			else
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 6)=="NUMBER"){
				echo " 
				customMarkerNum++;
				myMarkerNumber=myMarkerNumber+1;
				var myIcon = new GIcon(baseIcon);\n
				if (myMarkerNumber>400) {myIcon.image = 'images/MOREMarker.png';\n}
				else {myIcon.image = 'images/'+myMarkerNumber+'Marker.png';
				myIcon.printImage = 'images/gif/'+myMarkerNumber+'Marker.gif';
  					myIcon.mozPrintImage = 'images/gif/'+myMarkerNumber+'Marker.gif';
				batchMarkers = true;
				\n}";
				
			}
			else 
// PolyLine
            if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="LIN2"){ 
			$addtoLegend=false;
			$addtoKML=false;
			echo "var myIcon = new GIcon(verysmallIcon);\n
			batchlineNumalt = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
			if (batchlineNumalt !== batchlineNum) {var pointsPolyline = [];}
			batchlineNum = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
			
			
			
			if (batchlineNum>globalbatchpolylineNum)	{
			globalbatchpolylineNum = batchlineNum;

			}
			
		  	myIcon.image = 'images/BLUEDOTMarker.gif';\n
			pointsPolyline.push(new GLatLng(".$this->validPoints[$g]['lat'].", ".$this->validPoints[$g]['long']."));\n
			";
			
			if ($this->validPoints[$g]['passedhtmlMessage']==null) {
			echo "var lineColor = '#FF0000'";
			}
			else {
			echo "var lineColor = '".$this->validPoints[$g]['passedhtmlMessage']."'";
			}
			echo " 
			var pres=0.000001;
            var pe = new PolylineEncoder(18,2,pres);
			batchlineNumbers[batchlineNum] = pe.dpEncodeToGPolyline(pointsPolyline,lineColor, 4, 0.5);\n
			dontPlot=true;
			

			 \n
			";
			}
			
			
			else


			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 4)=="LINE"){ 
			$addtoLegend=false;
			$addtoKML=false;
			echo "var myIcon = new GIcon(verysmallIcon);\n
			batchlineNumalt = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
			if (batchlineNumalt !== batchlineNum) {var pointsPolyline = [];}
			batchlineNum = ".substr($this->validPoints[$g]['newMarkerColor'], 4, 4).";\n
			
			
			
			if (batchlineNum>globalbatchpolylineNum)	{
			globalbatchpolylineNum = batchlineNum;
			}
			
		  	myIcon.image = 'images/BLUEDOTMarker.gif';\n
			pointsPolyline.push(new GLatLng(".$this->validPoints[$g]['lat'].", ".$this->validPoints[$g]['long']."));\n
			";
			
			if ($this->validPoints[$g]['passedhtmlMessage']==null) {
			echo "var lineColor = '#FF0000'";
			}
			else {
			echo "var lineColor = '".$this->validPoints[$g]['passedhtmlMessage']."'";
			}
			echo " 
			var pres=0.000001;
            var pe = new PolylineEncoder();
			batchlineNumbers[batchlineNum] = pe.dpEncodeToGPolyline(pointsPolyline,lineColor, 4, 0.5);\n
			dontPlot=true;
			

			 \n
			";
			}
			else
			
// PolyGon     
			if (substr($this->validPoints[$g]['newMarkerColor'], 0, 7)=="POLYGON"){ 
			$addtoLegend=false;
			$addtoKML=false;
			echo "var myIcon = new GIcon(verysmallIcon);\n
			batchpolygonNumalt = ".substr($this->validPoints[$g]['newMarkerColor'], 7, 7).";\n
			if (batchpolygonNumalt !== batchpolygonNum) {pts = [];}
			batchpolygonNum = ".substr($this->validPoints[$g]['newMarkerColor'], 7, 7).";\n
			if (batchpolygonNum>globalbatchpolygonNum)	{
			globalbatchpolygonNum = batchpolygonNum;
			}
		  	myIcon.image = 'images/SMALLGREYMarker.png';\n
			pts.push(new GLatLng(".$this->validPoints[$g]['lat'].", ".$this->validPoints[$g]['long']."));\n";
			if ($this->validPoints[$g]['passedhtmlMessage']==null) {
			echo "var polygonColor = '#0a68f5'";
			}
			else {
			echo "var polygonColor = '".$this->validPoints[$g]['passedhtmlMessage']."'";
			}
			echo " 
			thisPolyline = polylineEncoder.dpEncodeToGPolygon([pts]);
			batchpolygonNumbers[batchpolygonNum] = new GPolygon(pts,polygonColor, 2, 0.7,polygonColor,0.5);\n
			dontPlot=true;
			\n
			";
			}				
			
// Center				
			
			else if (substr($this->validPoints[$g]['newMarkerColor'], 0, 6)=="CENTER")
			{ 
			
			
			
			
				$addtoLegend=false;
				$addtoKML=false;
				echo "
				var myIcon = new GIcon(verysmallIcon);\n 	
				centerOnMarkers=false;
				centerPoint='DEFINED'
				";
				
				
				
				
				
				
				if ($this->validPoints[$g]['passedhtmlMessage']!=null)
					{
					  echo "map.setCenter(new GLatLng(".$this->validPoints[$g]['lat'].",".$this->validPoints[$g]['long']."), ".$this->validPoints[$g]['passedhtmlMessage'].");";
					}
				else 
					{
					echo "map.setCenter(new GLatLng(".$this->validPoints[$g]['lat'].",".$this->validPoints[$g]['long']."), ".$this->zoomLevel.");";
					}
			}
		  	else {
				echo "var myIcon = new GIcon(baseIcon);\n
		  		myIcon.image = 'images/".$this->validPoints[$g]['newMarkerColor']."Marker.png';\n
				myIcon.printImage = 'images/gif/".$this->validPoints[$g]['newMarkerColor']."Marker.gif';
  					myIcon.mozPrintImage = 'images/gif/".$this->validPoints[$g]['newMarkerColor']."Marker.gif';
				batchMarkers = true;
				";}
				
		 	}
		else {echo "var myIcon = new GIcon(baseIcon);\n   
		            myIcon.image = 'images/GREYMarker.png';\n";}
					
		if ($centerIsDefined) { echo "map.setCenter(new GLatLng(".$useThislat.",".$useThislong."), ".$useThiszoomLevel.");\n";} 			
		echo "
		
		getGroupName = '".$this->validPoints[$g]['groupName']."';
		checkGroupNames(getGroupName);
		
		markerGroupNumber_alt = 'group'+markerGroupNumber;
		
		var marker".$g." = new GMarker(point".$g.", {icon:myIcon});\n;
		if (!dontPlot) {
		  if (batchMarkers) { 
		    markerGroups[markerGroupNumber_alt].push(marker".$g.");
			
		    batchMarkers=false;
		    writeToGroupLegend=false;
		  } else 
		  {map.addOverlay(marker".$g.");}
		} else {}
		
		
		
		
		dontPlot=false;
		bounds.extend(marker".$g.".getPoint());
		labelMarkerON=false;\n";


			
			
$thisMarkerDescription = $this->validPoints[$g]['passedhtmlMessage'];
if 	($thisMarkerDescription=="") {$thisMarkerDescription = $this->validPoints[$g]['passedAddress'];}



$fullMarkerDescription = htmlspecialchars($thisMarkerDescription);
list($junk, $legendDescription) = split('<h1>', $thisMarkerDescription);
list($legendDescription, $junk) = split('</h1>', $legendDescription);
$kmlMarkerDescription=$legendDescription;

$trimmedKML = str_replace($kmlMarkerDescription,"",$thisMarkerDescription);
$trimmedKML = str_replace("<h1>","",$trimmedKML);
$trimmedKML = str_replace("</h1>","",$trimmedKML);
$trimmedKML = htmlspecialchars($trimmedKML);

if ($legendDescription<>"") {$thisMarkerDescription = $legendDescription; $kmlMarkerDescription = htmlspecialchars($legendDescription);}

if ($addtoLegend){echo "gmarkers[ii] = marker".$g.";  side_bar_html += '<tr class=\"'+markerGroupNumber_alt+'\"><td class=\"legend\"><a href=\"javascript:GEvent.trigger(gmarkers[".$g."], \'click\');\">".$thisMarkerDescription."</a></td><td class=\"legendImageBox\"><img src=\"'+myIcon.image+'\"/></td></tr>';\n side_bar_html_all += '<tr class=\"legendRow\"><td class=\"legend\">".$fullMarkerDescription."</td><td class=\"legendImageBox\"><img src=\"'+myIcon.image+'\"/></td></tr>';\nii++; \n";} else {echo "ii++; \n";};
			
//Adding to KML


if ($addtoKML){echo "kmlmsg += '<Placemark>\\r  <Style>\\r    <IconStyle>\\r      <Icon>\\r        <href>http://www.multiplottr.com/images/GoogleMapsMarkers/".$this->validPoints[$g]['newMarkerColor']."Marker.png</href>\\r      </Icon>\\r    </IconStyle>\\r  </Style>\\r  <name>".$kmlMarkerDescription."</name>\\r  <description>".$trimmedKML."</description>\\r  <Point>\\r    <coordinates>".$this->validPoints[$g]['long'].",".$this->validPoints[$g]['lat']."</coordinates>\\r  </Point>\\r</Placemark>\\r'; \n 

	";}	

if (!$centerIsDefined) {			
if ($numPoints<100)  { echo "  if (centerOnMarkers) {map.setZoom(map.getBoundsZoomLevel(bounds)); map.setCenter(bounds.getCenter());}";} 
}
			
			
			
			
			
			echo "
			GEvent.addListener(marker".$g.", 'mouseover', function() {notMarker = false;});
			GEvent.addListener(marker".$g.", 'mouseout', function() {notMarker = true;});
			";
			
// Opening infoWindow



		  	echo "GEvent.addListener(marker".$g.", \"click\", function() {\n";
              	if ($this->validPoints[$g]['passedhtmlMessage']!=null){
			
              		echo "
				thisMarkerLat = '".$this->validPoints[$g]['lat']."';
				thisMarkerLong = '".$this->validPoints[$g]['long']."';
				multiAddressValue = thisMarkerLat+','+thisMarkerLong;
				multiAddressDisplay = '".$this->validPoints[$g]['passedhtmlMessage']."';
				carryG = marker".$g.";
				marker".$g.".openExtInfoWindow(map,\"custom_info_window_red\",\"<div>".$this->validPoints[$g]['passedhtmlMessage']."<p id='bottomNote'><a class = 'smallLink' href='javascript:void(0);' style='font-size: 8pt;' onclick='insertOption();'>Add to Multi-Directions</a></p></div>\",{beakOffset: 6});\n";
              	}
			else{
             		echo "
				thisMarkerLat = '".$this->validPoints[$g]['lat']."';
				thisMarkerLong = '".$this->validPoints[$g]['long']."';
				multiAddressValue = thisMarkerLat+','+thisMarkerLong;
				multiAddressDisplay = '".$this->validPoints[$g]['passedAddress']."';
				carryG = marker".$g.";
				marker".$g.".openExtInfoWindow(map,\"custom_info_window_red\",\"<div>".$this->validPoints[$g]['passedAddress']."<p id='bottomNote'><a class = 'smallLink' href='javascript:void(0);' style='font-size: 8pt;' onclick='insertOption();'>Add to Multi-Directions</a></p></div>\",{beakOffset: 6});\n";
                }
			
			
			
			echo "});\n
			";
			
			
			
			

		
			
			
// Removing marker on a double click
		  	echo "GEvent.addListener(marker".$g.", \"dblclick\",  function(){ 
		    	map.removeOverlay(marker".$g."); 
				map.closeInfoWindow();  
	      	}); \n";
	}  
	
	
	
// Updating Zoom Level in Parent Document
echo "GEvent.addListener(map, \"moveend\",function()
{parent.document.mapwindowform.myZoomLevel.value = map.getZoom();
parent.document.mapwindowform.centerLatLong.value=\"~\"+map.getCenter().toUrlValue()+\"|\"+map.getZoom()+\"|CENTER\";
}); ";					




			
			
// Adding  and removing small marker --> getting longitude and latitude	- Continuing adding lines and polygons
		  echo "GEvent.addListener(map, \"movestart\", function () {center = map.getCenter();}); \n;
		  
		  
		  
		  
		  
		  
		  GEvent.addListener(map, \"click\", function(overlay, point) 
		  
		  {
		  if (thisCustomIconButton) {
		  
			
		  	var myIcon = new GIcon(customIcon);\n
		 	myIcon.image = 'images/tools.png';\n;
		  	latlongmarker = new GMarker(point, {icon:myIcon, draggable:true });\n;
		  	map.addOverlay(latlongmarker);\n;
			point = latlongmarker.getPoint();
			lastPoint = point;
			mapZoom = map.getZoom();
			coordinates = point.lat()+','+point.lng();
			coordinates2 = point.lat()+','+point.lng();
			coordinates3 = point.lat()+','+point.lng()+'|'+mapZoom+'|CENTER';
			coordinates4 = point.lat()+','+point.lng()+'||POLYGON';
			addMarkerInfoWindow();
			thisCustomIconButton = false;
			GEvent.addListener(latlongmarker, 'click', function() {
			  point = latlongmarker.getPoint();
			  lastPoint = point;
			  coordinates = point.lat()+','+point.lng();
			  coordinates2 = point.lat()+','+point.lng();
			  coordinates3 = point.lat()+','+point.lng()+'|'+mapZoom+'|CENTER';
			  coordinates4 = point.lat()+','+point.lng()+'||POLYGON';
			  addMarkerInfoWindow();
			});
		  
		  
		  }
		  
		  if ((quickLine) && (point != undefined)) {
		  lastPoint = point;
		  coordinates = point.lat()+','+point.lng();
		  addLineInfoWindow2();}
		  \n
		  
		  if ((quickPolygon) && (point != undefined)) {
		  lastPoint = point;
		  coordinates4 = point.lat()+','+point.lng();
		  addPolygon();}
					  \n
		  }
		  
		  ); \n
		  




		  GEvent.addListener(map, \"dblclick\", function(overlay,point,test) 
		  {
		
		  if (latlongmarker) {
		  map.removeOverlay(latlongmarker);
		  map.closeInfoWindow(latlongmarker);}
		  if (point) {map.setCenter(center);\n} 
		 			
		  	var myIcon = new GIcon(customIcon);\n
		 	myIcon.image = 'images/tools.png';\n;
		  	latlongmarker = new GMarker(point, {icon:myIcon, draggable:true });\n;
		  	map.addOverlay(latlongmarker);\n;
			
			GEvent.addListener(latlongmarker, \"mouseover\", function() {notMarker = false;});
			GEvent.addListener(latlongmarker, \"mouseout\", function() {notMarker = true;});
							
		  	GEvent.addListener(latlongmarker, 'click', function() {
		  		point = latlongmarker.getPoint();
				lastPoint = point;
				mapZoom = map.getZoom();
				coordinates = point.lat()+','+point.lng();
				coordinates2 = point.lat()+','+point.lng();
				coordinates3 = point.lat()+','+point.lng()+'|'+mapZoom+'|CENTER';
				coordinates4 = point.lat()+','+point.lng();
				latlongmarker.openInfoWindowHtml(\"<div id='infoDiv' class='infoWindowDiv'>Latitude is \"+point.lat()+\"<br>Longitude is \"+point.lng()+\"<ul class='infonav'><li><a class='nav' href='javascript:{var points = []; lineNum = lineNum+1; newLine=true;  tempnotMarker = false; quickLine=true; firstPolylinePoint()}'>Line Tool</a></li><li><a class='nav' href='javascript:{var points = []; polygonNum = polygonNum+1; newPolygon=true;  tempnotMarker = false; quickPolygon=true; firstPolygonPoint()}'>Polygon Tool</a></li><li><a class='nav' href='javascript:addMarkerInfoWindow()'>Customized Marker</a></li><li><a class='nav' href='javascript:mapCenterInfoWindow()'>Save Zoom & Mark Center</a></li></ul></div>\");\n;
  			   					});
		   	GEvent.addListener(latlongmarker, 'dblclick', function() {
				map.removeOverlay(latlongmarker);
				map.closeInfoWindow(latlongmarker);
  			   	});
		  	return latlongmarker;\n;
        
		  	
			
			
			
			
			
		  }); 
		  \n
		  ";


if ($this->showLegend){				
echo "labelContainer.innerHTML = '<table class=\"legendTable\" cellspacing=\"0\" width=\"100%\">'+side_bar_html+'  </table>';
      var originalSTR = '.gif';
  var side_bar_html2 = side_bar_html_all.replace(/.png/g,originalSTR);
  	  mapResults.innerHTML = '<table class=\"legendTable\" cellspacing=\"0\" width=\"100%\">'+side_bar_html2+'</table>';\n;";}
  
  
echo "
  	
var myPolygon;
var myPolyline;
for (myPolyline=0; myPolyline<batchlineNumbers.length; myPolyline++) {
 if (batchlineNumbers[myPolyline]!==undefined) {map.addOverlay(batchlineNumbers[myPolyline]);
}
}
for (myPolygon=0; myPolygon<batchpolygonNumbers.length; myPolygon++) {
 if (batchpolygonNumbers[myPolygon]!==undefined) {map.addOverlay(batchpolygonNumbers[myPolygon]);}
}
var iii;
var group_Alt;
var groupValue='';
var iPolygon;
var iPolygon_Alt=0;
testPoly=false;
var ignorPoly;
for (iii=0; iii<markerGroupNames.length; iii++)
{ if (markerGroupNames[iii]!==undefined) 
	{
	group_Alt='group'+iii;
	myBatch.addMarkers(markerGroups[group_Alt],0,19);
	if (markerGroups[group_Alt]==groupValue)	{
	
	if (group_side_bar_html.match(markerGroupNames[iii])==null) {
	iPolygon_Alt=iPolygon_Alt+1; 
	ignorPoly=false;
	if (batchpolygonNumbers.length>0) {
		for (iPolygon=iPolygon_Alt; iPolygon<batchpolygonNumbers.length; iPolygon++) { if (ignorPoly) { } else
		if (batchpolygonNumbers[iPolygon]!==undefined && group_side_bar_html.match(markerGroupNames[iii])==null) { iPolygon_Alt=iPolygon;  ignorPoly=true; group_side_bar_html += '<tr class=\"legendRow\"><td class=\"legend\"><input id=\"checkbox\'+iPolygon+\'\" name=\"checkbox\'+iPolygon+\'\" type=\"checkbox\" onclick=\"if (this.checked) {map.addOverlay(batchpolygonNumbers[\''+iPolygon+'\']);} else {map.removeOverlay(batchpolygonNumbers[\''+iPolygon+'\']);} \" CHECKED />'+markerGroupNames[iii]+'</td></tr>';  
			}
		}
	}
}
	
	
	
	
	
	
	
	
	
	}
		else {	
	group_side_bar_html += '<tr class=\"legendRow\"><td class=\"legend\"><input type=\"checkbox\" onclick=\"  toggleGroup(\''+group_Alt+'\')\" CHECKED />'+markerGroupNames[iii]+'</td></tr>';
		}
	}
}
    myBatch.refresh();
if (testPoly){}";





if ($this->showGroup){echo "toggleContainer.innerHTML= '<table class=\"legendTable\" cellspacing=\"0\" width=\"100%\">'+group_side_bar_html+'</table>';";}
echo "

parent.document.kmlform.kmlBox.value += '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\\r<kml xmlns=\"http://www.opengis.net/kml/2.2\">\\r<Document>\\r'+kmlmsg+'</Document>\\r</kml>';



	}
	
	
	
	}\n

	

	//]]>\n
	
	
	if (parent.document.getElementById('unplotBunch').value !== '')
        {alert('Sorry, There were some addresses that could not be plotted. See Unplotted Address, Next to the Batch Address tab for full list.');}
		parent.document.getElementById('screenOut2').className=\"hide\";
parent.document.getElementById('working').className=\"hide\";

window.onload = showmap;
	
    
	</script>\n";


}
	
	
 ///////////THIS BLOCK OF CODE IS FROM Roger Veciana's CLASS (assoc_array2xml) OBTAINED FROM PHPCLASSES.ORG//////////////
   	
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
