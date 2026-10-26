<?php 

 echo "
   
   <script language='javascript'>\n
         var allAddresses=''; 
		 var allMapUrls='';
		 var allMapLink='';
		 var allMapInfo='';
		 </script> \n";


require_once 'MULTIPLOTTRCUSTOMMain.php';

$map = new MultiplottrMap();

$width = $_GET['mapWidth'];
$height = $_GET['mapHeight'];
$zoomy = $_GET['mapZoom'];
$metroFile = $_GET['mapDatabase'];
$myControl = $_GET['mapControl'];
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
if (substr($term, 0, 22)=="http://www.multiplotte"){
$map->setAPIKey("[REDACTED_LEGACY_GOOGLE_KEY]");
}
else
if (substr($term, 0, 22)=="http://multiplotter.co"){
$map->setAPIKey("[REDACTED_LEGACY_GOOGLE_KEY]");
}
else{
$map->setAPIKey("[REDACTED_LEGACY_GOOGLE_KEY]");
}


$map->setWidth($width);
$map->setHeight($height);
$map->setZoom($zoomy);
$map->setControlSize($myControlSize);
if ($myControl=='false')  {$map->setControl(false);} else {$map->setControl(true);}
if ($myType=='false')     {$map->setType(false);} else {$map->setType(true);}
if ($myOverview=='false') {$map->setOverview(false);} else {$map->setOverview(true);}
if ($myLegend=='false')   {$map->setLegend(false);} else {$map->setLegend(true);}




?>
<!DOCTYPE html>
<html style="overflow:hidden; height:100%">
   <head>
   <? $map->printGoogleJS(); ?>
   <link href="http://localhost:8080/mapCSS.css" rel="stylesheet" type="text/css" />
   <link href='http://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
   <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
   <script type='text/javascript'>
  
  var dataArray =[];
  var dataArrayCount = 0;
  var fileName;
  
$(document).ready(function(){
		
	$('#mapToolCollapse').live('click', function(){ 
		$('#mapToolCollapse, #mapToolExpand').toggle();
		$('#batchAddress').slideUp("fast");
		$('#BatchAddressTab').removeClass("moveUpOnePx");
 	});
 
	$('#mapToolExpand').live('click', function(){ 
		$('#mapToolCollapse, #mapToolExpand').toggle();
		$('#batchAddress').slideDown("fast");
		$('#BatchAddressTab').addClass("moveUpOnePx");
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
   <title>Multiplottr: DayZ Map</title>
   <!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->
   </head>
   <body  style="height:100%; margin:0">
<div id="topBannerNav" class="gradient"><img src="images/multiplottrLogo.png" width="268" height="50" alt="Multiplottr"></div>
<div id="parent">
<div id="mapContainer">
<div id="popOutWrapper" >
	<div id="batchAddress">
		<div class="content"><h2>Batch Addresses</h2><p>Place each address on its own line and hit submit. Multiplottr will process the addresses and geocode them to work with the Multiplottr format.</p></div>    	
    	<form  id="mapTool" method="post"  >
        	<section>
        	<textarea id="markerLocationField" name="markerLocationField"></textarea>
            </section>
         	<div class="buttonHolder" > <a class="button2" href="javascript:void(0)" onclick="$('#mapTool').submit();">Save Changes</a> </div>
       	</form></div>
    <div id="BatchAddressTab"><div id="mapToolCollapse"></div><div id="mapToolExpand"></div>Batch Addresses</div>
   </div> <a name="maptop" id="maptop"></a>
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
  

if ($myDatabase<>"") 
{
	$fp = fopen($metroFile,'r');
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
	 		
}
  $map->showMap();
  ?>
   </div>
<div id="mapLegend">
	<div id="groupControlDiv">
		<header>Groups<div id="groupCollapse"></div><div id="groupExpand"></header>
		<section></section>
	</div>
    <div id="legendDiv">
    	<header><div id="legendCollapse"></div><div id="legendExpand"></div>Legend</header>
    	<section></section>
	</div>
	<div id="markerSelection">
    	<header><div id="markerSelCollapse"></div><div id="markerSelExpand"></div>Available Map Markers</header>
    		<section>
				<ul>
					<li><img src="images/REDMarker.png" width="20" height="34" alt="RED"></li>
					<li><img src="images/BLUEMarker.png" width="20" height="34" alt="BLUE"></li>
					<li><img src="images/CYANMarker.png" width="20" height="34" alt="CYAN"></li>
					<li><img src="images/GREENMarker.png" width="20" height="34" alt="GREEN"></li>
					<li><img src="images/GREYMarker.png" width="20" height="34" alt="GREY"></li>
					<li><img src="images/LIMEMarker.png" width="20" height="34" alt="LIME"></li>
					<li><img src="images/ORANGEMarker.png" width="20" height="34" alt="ORANGE"></li>
					<li><img src="images/PURPLEMarker.png" width="20" height="34" alt="PURPLE"></li>
					<li><img src="images/VIOLETMarker.png" width="20" height="34" alt="VIOLET"></li>
					<li><img src="images/YELLOWMarker.png" width="20" height="34" alt="YELLOW"></li>
				</ul>
       			<div class="container">
        			<input id="markerName" type="text" class="hide">
        			<input id="markerURL" type="text">
					<div class="buttonHolder" ><a id="addNewMarkerURL" class="button" href="javascript:void(0)" >Add New Marker URL</a></div>
				</div>
			</section>
		</div>
	</div>
<div id="mapResults"></div>

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