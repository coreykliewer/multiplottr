
<!doctype html>
<html>
   <head>
   <meta charset="utf-8">
   <title>Multiplottr: DayZ Map</title>
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
<script type="text/javascript">
	tinyMCE.init({
		mode : "exact",
		remove_linebreaks : false,
        elements : "markerTextArea",
		theme : "advanced",
		theme_advanced_buttons1 : "bold,italic,|,link,unlink,image,|,bullist,numlist,|,code",
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
							//alert('2) '+geoCodeListArray[i]);
                            //if (geoCodeListArray[i]=='undefined') {alert('Hey its undefined!');}
							geoCoded.push(geoCodeListArray[i]+'\n');
							//alert('geoCoded: '+geoCoded);
							var filteredGeoCoded = geoCoded.filter(function(word) {return word[0] == '~';});
							//alert(filteredGeoCoded);
							
							//$('#markerLocationField').append('Filtered:'+filteredGeoCoded.length+' List:'+geoCodeListArray.length/2+'\n');
							if (filteredGeoCoded.length==geoCodeListArray.length) {
								//alert(filteredGeoCoded.length+'|'+geoCodeListArray.length);
								
							for (i=0; i<filteredGeoCoded.length; i++) {	
								if (filteredGeoCoded[i].substring(0,3)=='~NO') {
									if (filteredGeoCoded.length==linesLength) {notPlotted = notPlotted+filteredGeoCoded[i];}
									
								} else {
									//alert('filteredGeoCoded.length: '+filteredGeoCoded.length+' linesLength: '+linesLength);
									if (filteredGeoCoded.length==linesLength) {plottedAddresses = plottedAddresses+filteredGeoCoded[i];}
									
								}
								
								}
							$('#markerLocationField').val(plottedAddresses);
							$('#batchAddressesField').val(notPlotted);	
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
						
						//$('#mapTool').submit();
					}

          		} else if (status == 'ZERO_RESULTS') {
					geoCodeListArray[orderNumber]='~NO LOCATION FOUND|'+description+'|'+markertype+'|'+groupname;
					
					if (orderNumber+1==geoCodeListArray.length) {
						//alert('google maps Zero- Filter');
						filterTheseAddresses();
						//$('#mapTool').submit();
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




		
	$('#batchAddressTab').live('click', function(){ 
	    if ($('#addMarkerTab').hasClass('expanded')) {
			$('#addMarkerTab').removeClass('expanded');
			$('#addMarkerTab').addClass('collapsed');
			$('#placeMarkers').hide();
			$('#addMarkerTab').removeClass("moveUpOnePx");
		}
	
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
	
	$('#placeMarkerHeader').live('click', function() {
		$('#markerSelection').toggle();
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

$('#batchGeocode').live('click', function() {
	var addresses = $('#batchAddressesField').val();
		batchGeocode(addresses);
		});

$('#loginRegister').live('click', function() {
	$("#loginRegisterBox").toggle();
		});

 
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
<div id="topBannerNav" class="gradient"><a id="loginRegister" class="topBannerButton" href="javascript:void(0)">Login | Register</a><img src="images/multiplottrLogo.png" width="210" height="50" alt="Multiplottr"></div>
<div id="popOutWrapper" >
	<div id="batchAddress">
		<div class="content">
			<h2>Add Addresses</h2>
            <p>Place each address on its own line and hit submit. Multiplottr will process the addresses and geocode them to work with the Multiplottr format.</p>
            <a id="batchGeocode" class="button3" href="javascript:void(0)" >Batch Geocode</a>
            <p>Progress: <span id="numOfAddresses"></span></p>
            <div id="progressbar"></div>
        </div>
        <form id="batchAddresses" method="post">
        <section><textarea id="batchAddressesField" name="batchAddressesField"  placeholder="Enter each address 1 line at a time. Unregistered users can plot 25 addresses at a time."></textarea></section>
        </form>
        <form  id="mapTool" method="post"  >
        	<section><textarea id="markerLocationField" name="markerLocationField"></textarea></section>
         	<ul id="defaultSettings" class="buttonHolder" >
        		<li><span id="currentMarker"></span><label>Default Marker</label></li>
                <li><label>Default Group :</label><span id="currentGroupNameDisplay"></span></li>
        		<li><a id="plotTheseAddresses" class="button2" href="javascript:void(0)">Plot Addresses</a></li>
      		</ul>
       	</form>
	</div>
    
     <ul id="tabNav">
    <li id="batchAddressTab" class="collapsed">Plot Addresses</li>
   <!-- <li id="addMarkerTab" class="collapsed">Place Marker</li> -->
  </ul>
   </div>
<div id="mapLegend">
<div id="loginRegisterBox">
<div class="container">
	<ul>
    	<h2>Login</h2>
		<li><input id="username" type="text" placeholder="Username"><br><a class="smallType" href="javascript:void(0)">Forgot Username</a></li>
        <li><input id="password" type="password" placeholder="Password"><br><a class="smallType" href="javascript:void(0)">Forgot Password</a></li>
        <li class="bottomSpacer"><a id="login" class="button3" href="javascript:void(0)" >Login</a></li>
        <h2>Register</h2>
        <li><input id="confirmPassword" type="password" placeholder="Confirm Password"></li>
        <li><input id="email" type="text" placeholder="Email"></li>
        <li><a id="register" class="button3" href="javascript:void(0)" >Register</a></li>
	</ul>
    </div>
</div>
<header id="placeMarkerHeader">Place Marker</header>
     <div id="markerSelection" >
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
                    <li><input id="markerURL" type="text" placeholder="Enter a customized marker URL"></li>
                    <li><a id="addNewMarkerURL" class="button3" href="javascript:void(0)" >Add New Marker URL</a></li>
                    <li><h2>Default Marker Group</h2></li>
                    <li><select id='allGroupNames'></select></li>
                    <li><input id="currentGroupName" type="text"></li>
                    <li><h2>Marker Header</h2></li>
                    <li><input id="markerHeader" name="Marker Header" type="text" placeholder="Enter Marker Header"></li>
                    <li><h2>Marker Description</h2></li>
                    <li><textarea id="markerTextArea" cols="" rows=""></textarea></li>
                    </ul>
				</div>
            	<ul id="addAddress">
            		<li><input id="markerAddress" type="text" placeholder="Marker Address"></li>
                    <li><a id="geoCodeAddress" class="button3" href="javascript:void(0)" >GeoCode Marker</a></li>
                    <li><a id="placeMarker"    class="button3" href="javascript:void(0)" ><input id="plotMarker" type="checkbox" value=""> Place Marker </a></li>
                    <li><a id="updateMarker" class="button3" href="javascript:void(0)" >Update Marker</a></li>
                    
                </ul>
			</section>
		</div>
     <header>Groups</header>
     <div id="groupControlDiv"> </div>
     <header>Legend</header>
     <div id="legendDiv" > </div>
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
<header id="saveMap" onclick="$('#mapTool').submit();">Save Map</header>
     
</div>
<div id="mapContainer"> <a name="maptop" id="maptop"></a>
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
$addressCarry = 'test';



$myDefaultMarker = $_POST['MarkerColor1'];
$myDefaultGroupName = $_POST['groupname1'];

$term = $_SERVER['HTTP_REFERER'];

if ($width=='0') {$width='100%';}
if ($height=='0') {$height='100%';}



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

$newcontents = $_POST["markerLocationField"];
if (isset($_POST['markerLocationField'])) {
    $fp = fopen("datafiles/dayz.txt","w");
    if(!$fp) {
		echo 'Error: Cannot open file.';
		exit;
	}
	fwrite($fp, $newcontents);
	fclose($fp);	
}

$bunchOaddresses = $_POST['addressBunch'];




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
} else {$map->addAddress('~0,0','2','CENTER','CENTER');}


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