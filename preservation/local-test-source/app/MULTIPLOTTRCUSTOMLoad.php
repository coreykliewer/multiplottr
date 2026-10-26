<?php
include 'db/db.class.php';
include 'db/plotter.class.php';
$pltr = new Plotter();
include 'checklogin.php';
include 'reglogin.php';
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta property="og:url"             content="http://localhost:8080" /> 
        <meta property="og:title"           content="Multiplottr - Plot, Save & Share" /> 
        <meta property="og:image"           content="http://localhost:8080/thumbnail.png" />
        <title>Multiplottr: Plot, Save and Share.</title>
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <script src="https://maps.googleapis.com/maps/api/js?v=3.13&key=[REDACTED_TOKEN]&sensor=true" type="text/javascript"></script>
        <script src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/1.0/src/markermanager.js" type="text/javascript"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="scripts/jquery.ui.map.js"></script>
        <script src="scripts/infobox.js"></script>
        <script src="scripts/placeholder/jquery.placeholder.js" type="text/javascript" charset="utf-8"></script>
        <link href="new-embed.css" rel="stylesheet" type="text/css" />
        <link href='http://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Nunito' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="fontstyles.css">
        <script src="scripts/jquer-ui/js/jquery-ui-1.10.3.custom.min.js"></script>
        <link rel="stylesheet" type="text/css" href="scripts/jquer-ui/css/start/jquery-ui-1.10.3.custom.css">
        <!-- TinyMCE -->
        <script type="text/javascript" src="../scripts/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="../scripts/_advertisement.js"></script>
        <script type="text/javascript" src="../scripts/js-url.js"></script>
        <script type="text/javascript" src="../scripts/jquery.autosize-min.js"></script>
        <script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/geolocationmarker/src/geolocationmarker-compiled.js"></script>


      <script type="text/javascript" src="../scripts/main.js"></script>


    </head>
    <body>
    
<?php
include 'localsession.php';
?>
              

        <div id="fb-root"></div>
        <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=13993495363";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        </script>




        
        
        
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-75352-2', 'multiplottr.com');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');

</script>

        
        
        
        
            
		
        <div id="mapContainer"> <a name="maptop" id="maptop"></a>
        	
        
            
			
            
            

            <div id="overMapAdContainer"> <a  class="closeAd">close</a>
                <div class="blockedAd">Advertisement Blocked</div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- OnlineCustomMaps Responsive -->
<ins class="adsbygoogle onlinecustommaps-responsive"
     style="display:inline-block"
     data-ad-client="ca-pub-7787442361581894"
     data-ad-slot="2843519154"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
            </div>
<?
if($_GET['map_id']) {
    list($map_name, $map_info, $map_description, $map_username) = $pltr->getMapBasicInfo($_GET['map_id']);
}
require_once 'mpcMain.php';

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

if (($width == '0') || ($width == '')) {
    $width = '100%';
}
if (($height == '0') || ($height == '')) {
    $height = '100%';
}
if ($zoomy == '') {
    $zoomy = '11';
}
if ($myMaxZoom < 0 || $myMaxZoom > 19 || $myMaxZoom == '') {
    $myMaxZoom = 20;
}
if ($myMinZoom < 0 || $myMinZoom > 19 || $myMinZoom == '') {
    $myMinZoom = 2;
}
if ($myCustomFormat != 'jpg') {
    $myCustomFormat = 'png';
}


$map->setWidth($width);
$map->setHeight($height);
$map->setZoom($zoomy);
$map->setMinZoom($myMinZoom);
$map->setMaxZoom($myMaxZoom);
$map->setControlSize($myControlSize);
$map->setMapCustomURL($myCustomMapURL);
$map->setMapCustomFormat($myCustomFormat);
$map->setMapTitle($mapTitle);



if ($myControl == 'true') {
    $map->setControl(false);
} else {
    $map->setControl(true);
}
if ($myCustomMap == 'true') {
    $map->setCustomMap(true);
} else {
    $map->setCustomMap(false);
}
if ($myType == 'true') {
    $map->setType(false);
} else {
    $map->setType(true);
}
if ($myOverview == 'true') {
    $map->setOverview(true);
} else {
    $map->setOverview(false);
}
if ($myLegend == 'false') {
    $map->setLegend(false);
} else {
    $map->setLegend(true);
}
if ($myCustomMapURL <> '') {
	 $map->setCustomMap(true);
    $map->setType(false);
}

$map->showEdit(true);

function nl2brStrict($text, $replacement = '`') {
    return preg_replace("((\r\n)+)", trim($replacement), $text);
}

function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    $ch = curl_init($pageURL);
    curl_exec($ch);
}

function writeToFlatFile($i) {
    $newcontents = $_POST["markerLocationField"];
    if (isset($_POST['markerLocationField'])) {
        $fp = fopen($i, "w");
        if (!$fp) {
            echo 'Error: Cannot open file.';
            exit;
        }
        fwrite($fp, $newcontents);
        fclose($fp);
    }
}


// from map_id

if (($map_info <> null)&&($bunchOaddresses == null)) {
	
	
    $myStr = nl2brStrict($map_info);
    $myStr = str_replace("\t", " ", $myStr);
	if (strpos($myStr,'"') !== false) { 
		if (strpos($myStr,'\"') === false) {
			$myStr = str_replace('"', '\"', $myStr);
		}
	}
	if (strpos($myStr,"'") !== false) { 
		if (strpos($myStr,"\'") === false) {
			$myStr = str_replace("'", "\'", $myStr);
		}
	}
	
	
	$myStr = str_replace("Multiplottr formatted data.", "", $myStr);
	//$myStr = addslashes($myStr);
    $lines = array();
    $lines = explode("`", $myStr);
    $lineCount = count($lines);
    for ($i = 0; $i < $lineCount; $i++) {
        list ($field1, $field2, $field3, $field4) = split('\|', $lines[$i]);
        if ($field1<>'') {$map->addAddress($field1, $field2, $field3, $field4);}
        $line = $lines[$i];
        $line = htmlspecialchars($line, ENT_QUOTES);
        $line = preg_replace('/\p{C}+/u', '\\n', $line);
        echo "
			<script type='text/javascript'>
      		$(document).ready(function(){
				var line = '" . $line . "'; 
				if (line !== '\\n') {
        			$('#markerLocationField').append('" . $line . "\\n');	
					dataArray.push('" . $line . "')
					dataArrayCount++
					
				 		}

			$('#quickSearch').hide();
			$('#mapLegend #mainButtonContainer #cleaRNSave').show();
			
			});
			
   			</script>
			";
    }
	
	if ($map_username != $_user['user_username']) {
    $map->showEdit(false);
}  
	
	
}

// $bunchOaddresses is from $_POST['markerLocationField'];

if ($bunchOaddresses <> null) {
    if (substr($myDatabase, 0, 2) == "da") {
        writeToFlatFile($myDatabase);
    } else {
       $myStr = nl2brStrict($bunchOaddresses);
        $myStr = str_replace("\t", " ", $myStr);
		
        $lines = array();
        $lines = explode("`", $myStr);
        $lineCount = count($lines);
        for ($i = 0; $i < $lineCount; $i++) {
            list ($field1, $field2, $field3, $field4) = split('\|', $lines[$i]);
			if ($field1<>'') {$map->addAddress($field1, $field2, $field3, $field4);}
            
			
            $line = $lines[$i];
            $line = htmlspecialchars($line, ENT_QUOTES);
            $line = preg_replace('/\p{C}+/u', '\\n', $line);
            echo "
			<script type='text/javascript'>
      		$(document).ready(function(){
				var line = '" . $line . "'; 
				if (line !== '\\n') {
        			$('#markerLocationField').append('" . $line . "\\n');	
					dataArray.push('" . $line . "')
					dataArrayCount++
		 		}
				$('#mapLegend #mainButtonContainer #cleaRNSave').show();
			});
   			</script>
			";
        }
    }
}

// $myDatabase is from flat file database

if ($myDatabase <> "") {
    $fp = fopen($myDatabase, 'r');
    if (!$fp) {
        echo 'ERROR: Unable to open file.';
        exit;
    }
    while (!feof($fp)) {
        $line = fgets($fp, 1024); //use 2048 if very long lines
        list ($field1, $field2, $field3, $field4) = split('\|', $line);
        //	$addressField = nl2brStrict2($field2);
        //$field2 = htmlspecialchars($field2, ENT_QUOTES);
        if ($field1<>'') {$map->addAddress($field1, $field2, $field3, $field4);}
        $line = htmlspecialchars($line, ENT_QUOTES);
        $line = preg_replace('/\p{C}+/u', '\\n', $line);

        echo "
		<script type='text/javascript'>
		loadLocalStorage=false;
      	$(document).ready(function(){
			var line = '" . $line . "'; 
			if (line !== '\\n') {
        		$('#markerLocationField').append('" . $line . "');	
				dataArray.push('" . $line . "')
				dataArrayCount++
		 	}
			$('#mapLegend #mainButtonContainer #cleaRNSave').show();
		});
   		</script>
		";
    }
    fclose($fp);
}

if (($bunchOaddresses == null) && ($myDatabase == null) && ($map_info == null)) {
    $map->addAddress('~15,-42', '3', 'CENTER', 'CENTER');
}

if ($map_name <> null) {
    echo "
	<script type='text/javascript'>
	$('#mapTitle header').text('" . $map_name . "');
	$('#mapTitleField').val('" . $map_name . "')
	</script>
	";
}


if ($map_description <> null) {
    echo "
	<script type='text/javascript'>
	$('#mapDescriptionField').html('" . $map_description . "');
        $('#mapDescriptionFieldSubmit').html('" . $map_description . "');
   $('#mapDescript').html('" . $map_description . "');
   $('#legendItems').show();
	$('#mainButtonContainer').hide();  
	$('#legendHeader .contract').show(); 
	$('#legendHeader .expand').hide();
	$('#mapTitle #expandClose .expand').show();
	$('#mapTitle #expandClose .contract').hide();
	$('#mapDescript').hide();
	</script>
	";
}








$map->showMap();
?>
<div id="printLegend"></div> 
        </div>
       
    </body>
    
</html>