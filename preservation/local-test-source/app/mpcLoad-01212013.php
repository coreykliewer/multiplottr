<?php 

 echo "
   
   <script language='javascript'>\n
         var allAddresses=''; 
		 var allMapUrls='';
		 var allMapLink='';
		 var allMapInfo='';
		 </script> \n";


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



$map->setWidth($width);
$map->setHeight($height);
$map->setZoom($zoomy);
$map->setControlSize($myControlSize);
if ($myControl=='false')  {$map->setControl(false);} else {$map->setControl(true);}
if ($myCustomMap=='true') {$map->setCustomMap(true);} else {$map->setCustomMap(false);}
if ($myType=='false')     {$map->setType(false);} else {$map->setType(true);}
if ($myOverview=='false') {$map->setOverview(false);} else {$map->setOverview(true);}
if ($myLegend=='false')   {$map->setLegend(false);} else {$map->setLegend(true);}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
   <title>Multiplottr: DayZ Map</title>
   <? $map->printGoogleJS(); ?>
   <link href="new.css" rel="stylesheet" type="text/css" />
   <link href='http://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
   <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
   <script type='text/javascript'>
  
  var dataArray =[];
  var dataArrayCount = 0;
  var fileName;
  
$(document).ready(function(){
		
	$('#batchAddressTab').live('click', function(){ 
	    if ($('#addMarkerTab').hasClass('expanded')) {
			$('#addMarkerTab').removeClass('expanded');
			$('#addMarkerTab').addClass('collapsed');
			$('#markerSelection, #addAddresses').hide();
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
		}
 	});
	
	$('#addMarkerTab').live('click', function(){ 
	    if ($('#addMarkerTab').hasClass('expanded')) {
			$('#addMarkerTab').removeClass('expanded');
			$('#addMarkerTab').addClass('collapsed');
			$('#markerSelection').hide();
			$('#addAddresses').slideUp("fast");
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
			$('#addAddresses').slideDown("fast");
			$('#markerSelection').show();	
		}
 	});
 
	
	
	$('#markerSelCollapse').live('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		$('#markerSelection section').addClass('Hide');
 	});
 
	$('#markerSelExpand').live('click', function(){ 
		$('#markerSelCollapse, #markerSelExpand').toggle();
		$('#markerSelection section').removeClass('Hide');
 	});
 
 	$('#markerSelection ul li img').live('click', function(){ 
 		$('#markerSelection ul li img').removeClass('selected');
		$('#markerName').val($(this).attr('src'));
		$(this).addClass("selected");
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
<div id="topBannerNav" class="gradient"> <img src="images/multiplottrLogo.png" width="268" height="50" alt="Multiplottr"> </div>
<div id="popOutWrapper" >
  <div id="batchAddress">
    <div class="content">
      <h2>Add Addresses</h2>
      <p>Place each address on its own line and hit submit. Multiplottr will process the addresses and geocode them to work with the Multiplottr format.</p>
    </div>
    <form  id="mapTool" method="post"  >
      <section>
        <textarea id="markerLocationField" name="markerLocationField"></textarea>
      </section>
      <ul class="buttonHolder" >
        <li> <a class="button2" href="javascript:void(0)" onclick="$('#mapTool').submit();">Save Changes</a></li>
        <li > <a class="button2" href="javascript:void(0)" class="button2">Default Marker</a> </li>
      </ul>
    </form>
  </div>
  <ul id="tabNav">
    <li id="batchAddressTab" class="collapsed">Add Addresses</li>
    <li id="addMarkerTab" class="collapsed">Place Marker</li>
  </ul>
</div>
<div id="mapLegend">
<header>
Groups
<div id="groupCollapse"></div>
<div id="groupExpand">
  </header>
  <div id="groupControlDiv"> </div>
  <header>Legend</header>
  <div id="legendDiv"> </div>
</div>
<div id="mapContainer"> <a name="maptop" id="maptop"></a>
  <?
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