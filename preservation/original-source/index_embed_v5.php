<?php
// this code was updated via AI
// Updated the include statements to require_once for better practice
require_once 'db/db.class.v5.php';
require_once 'db/plotter.class.v5.php';

// Instantiate the Plotter class
$pltr = new Plotter();

// Initialize the $showMapNow variable to false
$showMapNow = false;

// Check if 'map_id' is present in the GET request
if (isset($_GET['map_id'])) { // Added isset() to check if 'map_id' is set
    // Fetch map details using the getMapBasicInfo method
    list($map_name, $map_info, $map_description, $map_username) = $pltr->getMapBasicInfo($_GET['map_id']);
    // Store the map id and sanitized map description
    $thisMapId = $_GET['map_id'];
    $thisMapDescription = strip_tags($map_description); // Using strip_tags to sanitize the description
} else {
    // Set default map name and id if 'map_id' is not set
    $map_name = 'Plot, save and share.';
    $thisMapId = '';
}

// Note: The commented meta tag line is left unchanged for your reference
/*<meta property="og:description" content="'.strip_tags($map_description).'" />*/

?>

<!doctype html>
<html>
    <head>
        <title>Multiplottr: Plot, Save and Share.</title>
        
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        
       <?php
// this code was updated to php 7.4 via CHATGPT		
// Check if 'map_id' is present in the GET request
if (isset($_GET['map_id'])) { // Added isset() to check if 'map_id' is set
    // Ensure $thisMapId is defined before use
    $thisMapId = htmlspecialchars($_GET['map_id'], ENT_QUOTES, 'UTF-8'); // Sanitize the input to avoid XSS attacks
    // Output meta tags for a specific map
    echo '<meta property="og:url" content="https://multiplottr.com/?map_id=' . $thisMapId . '" />
          <meta property="og:description" content="A custom map created by Multiplottr.com. Multiplottr.com allows you to plot, save and share multiple addresses with an easy to create customized map & markers." />
         ';
} else {
    // Output default meta tags
    echo '<meta property="og:url" content="https://www.multiplottr.com/" />
          <meta property="og:description" content="Multiplottr.com allows you to plot, save and share multiple addresses with an easy to create customized map & markers."/>
         ';
}
?>

        
        
        <meta property="og:title"           content="Multiplottr: <?php echo $map_name; ?>" /> 
        <meta property="og:image"           content="https://www.multiplottr.com/thumbnail.png" />
        <meta name="google-site-verification" content="HryWSTe1imC1tr54xRwCXzFJepCxBlZ4xh2pZh908VI" />
        <meta property="og:type" content="website" />
        
        
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
		<!-- <script src="https://maps.google.com/maps/api/js" type="text/javascript"></script> -->
      <script src="https://maps.googleapis.com/maps/api/js?v=weekly&key=[REDACTED_TOKEN]&sensor=true" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@markermanager/1.2/markermanager_packed.js" type="text/javascript"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="scripts/jquery.ui.map.js"></script>
        <script src="scripts/infobox.js"></script>
        <script src="scripts/placeholder/jquery.placeholder.js" type="text/javascript" charset="utf-8"></script>
        <link href="new.css" rel="stylesheet" type="text/css" />
        <!-- <link rel="stylesheet" href="fontstyles.css"> -->
        <link href="styles.css" rel="stylesheet">
        <link href='https://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet' type='text/css'>
        
        <script src="scripts/jquer-ui/js/jquery-ui-1.10.3.custom.min.js"></script>
        <link rel="stylesheet" type="text/css" href="scripts/jquer-ui/css/start/jquery-ui-1.10.3.custom.css">
        <!-- TinyMCE -->
        <script type="text/javascript" src="../scripts/_advertisement.js"></script>
        <script type="text/javascript" src="../scripts/js-url.js"></script>
        <script type="text/javascript" src="../scripts/jquery.autosize-min.js"></script>
        <script type="text/javascript" src="../scripts/markerwithlabel.js"></script>
        <script type="text/javascript" src="../scripts/geolocation-marker.js"></script>
       


     
  <!-- Google Auto Ads -->
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-7787442361581894",
          enable_page_level_ads: true
     });
</script>
    
    </head>
    <body>
<?php
// this code was updated to PHP 7.4 via CHATGPT

// Updated the include statement to require_once for better practice
require_once 'localsession.v5.php';
?>

              

      
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-75352-2', 'multiplottr.com');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');

</script>

 <script type="text/javascript" src="../scripts/main.js"></script>

     
        <div id="mapContainer"> <a name="maptop" id="maptop"></a>
        	  <div id="markerSelection" >
        	
        	
           <div class="tip"><span><div data-icon="w" class="contract"></div></span>Click on the map to place a marker.</div>
           <section>
			<h2>Default Marker</h2>
            <a href="javascript:void(0)" class="changeMarker">Show Available Markers</a>
           <div class="currentMarker"></div>
                
                <ul id="markerList">
                    <li><img src="../images/REDMarker.png" width="20" height="34" alt="RED"></li>
                    <li><img src="../images/ORANGEMarker.png" width="20" height="34" alt="ORANGE"></li>
                    <li><img src="../images/YELLOWMarker.png" width="20" height="34" alt="YELLOW"></li>
                    <li><img src="../images/GREENMarker.png" width="20" height="34" alt="GREEN"></li>
                    <li><img src="../images/LIMEMarker.png" width="20" height="34" alt="LIME"></li>
                    <li><img src="../images/BLUEMarker.png" width="20" height="34" alt="BLUE"></li>
                    <li><img src="../images/CYANMarker.png" width="20" height="34" alt="CYAN"></li>
                    <li><img src="../images/VIOLETMarker.png" width="20" height="34" alt="VIOLET"></li>
                    <li><img src="../images/PURPLEMarker.png" width="20" height="34" alt="PURPLE"></li>
                    <li><img src="../images/PINKMarker.png" width="20" height="34" alt="PINK"></li>
                    <li><img src="../images/WHITEMarker.png" width="20" height="34" alt="WHITE"></li>
                    <li><img src="../images/GREYMarker.png" width="20" height="34" alt="GREY"></li>
                    <li><img src="../images/BLACKMarker.png" width="20" height="34" alt="BLACK"></li>
                    <li><img src="../images/NUMBERMarker.png" width="20" height="34" alt="NUMBER"></li>
                    <li><img src="../images/SMALLREDMarker.png" width="20" height="34" alt="SMALLRED"></li>
                    <li><img src="../images/SMALLORANGEMarker.png" width="20" height="34" alt="SMALLORANGE"></li>
                    <li><img src="../images/SMALLYELLOWMarker.png" width="20" height="34" alt="SMALLYELLOW"></li>
                    <li><img src="../images/SMALLGREENMarker.png" width="20" height="34" alt="SMALLGREEN"></li>
                    <li><img src="../images/SMALLBLUEMarker.png" width="20" height="34" alt="SMALLBLUE"></li>
                    <li><img src="../images/SMALLGREYMarker.png" width="20" height="34" alt="SMALLGREY"></li>
                    
                </ul>                
           
                <div class="container">
                    <input id="markerName" type="text" class="hide" >
                    <textarea id="tinyMCEHolder" class="hide"></textarea>
                         </div>
              
            </section>
        </div>
            
			 
           
			
         
  <div id="overMapAdContainer"> 
	 <!-- <a  class="closeAd">close</a> -->
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

            
<?php
// this code was updated to PHP 7.4 via CHATGPT

require_once 'mpcMain.v5.php';

// Instantiate the MultiplottrMap class
$map = new MultiplottrMap();

// Sanitize GET inputs
$width = htmlspecialchars($_GET['mapWidth'] ?? '', ENT_QUOTES, 'UTF-8');
$height = htmlspecialchars($_GET['mapHeight'] ?? '', ENT_QUOTES, 'UTF-8');
$zoomy = htmlspecialchars($_GET['mapZoom'] ?? '', ENT_QUOTES, 'UTF-8');
$metroFile = htmlspecialchars($_GET['mapDatabase'] ?? '', ENT_QUOTES, 'UTF-8');
$myMaxZoom = htmlspecialchars($_GET['maxZoom'] ?? '', ENT_QUOTES, 'UTF-8');
$myMinZoom = htmlspecialchars($_GET['minZoom'] ?? '', ENT_QUOTES, 'UTF-8');
$myControl = htmlspecialchars($_GET['mapControl'] ?? '', ENT_QUOTES, 'UTF-8');
$myCustomMap = htmlspecialchars($_GET['mapCustom'] ?? '', ENT_QUOTES, 'UTF-8');
$myCustomMapURL = htmlspecialchars($_GET['mapCustomURL'] ?? '', ENT_QUOTES, 'UTF-8');
$myCustomFormat = htmlspecialchars($_GET['mapCustomFormat'] ?? '', ENT_QUOTES, 'UTF-8');
$myType = htmlspecialchars($_GET['mapType'] ?? '', ENT_QUOTES, 'UTF-8');
$myOverview = htmlspecialchars($_GET['mapOverview'] ?? '', ENT_QUOTES, 'UTF-8');
$myControlSize = htmlspecialchars($_GET['mapControlSize'] ?? '', ENT_QUOTES, 'UTF-8');
$myLegend = htmlspecialchars($_GET['mapLegend'] ?? '', ENT_QUOTES, 'UTF-8');
$myDatabase = htmlspecialchars($_GET['mapDatabase'] ?? '', ENT_QUOTES, 'UTF-8');
$myGroup = htmlspecialchars($_GET['mapGroup'] ?? '', ENT_QUOTES, 'UTF-8');
$myLocation = htmlspecialchars($_GET['location'] ?? '', ENT_QUOTES, 'UTF-8');
$showLabels = htmlspecialchars($_GET['showLabels'] ?? '', ENT_QUOTES, 'UTF-8');
$addresses = htmlspecialchars($_GET['addresses'] ?? '', ENT_QUOTES, 'UTF-8');
$infoWindowSize = '300';

// Sanitize POST inputs
$bunchOaddresses = htmlspecialchars($_POST['markerLocationField'] ?? '', ENT_QUOTES, 'UTF-8');
$myDefaultMarker = htmlspecialchars($_POST['MarkerColor1'] ?? '', ENT_QUOTES, 'UTF-8');
$mapTitle = htmlspecialchars($_POST['mapTitleFieldSubmit'] ?? '', ENT_QUOTES, 'UTF-8');
$mapDescription = htmlspecialchars($_POST['mapDescriptionFieldSubmit'] ?? '', ENT_QUOTES, 'UTF-8');
$myDefaultGroupName = htmlspecialchars($_POST['groupname1'] ?? '', ENT_QUOTES, 'UTF-8');

$term = htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '', ENT_QUOTES, 'UTF-8');

// Default values for width and height
if (empty($width) || $width == '0') {
    $width = '100%';
}
if (empty($height) || $height == '0') {
    $height = '100%';
}
if (empty($zoomy)) {
    $zoomy = '11';
}
if (empty($myMaxZoom) || $myMaxZoom < 0 || $myMaxZoom > 19) {
    $myMaxZoom = 20;
}
if (empty($myMinZoom) || $myMinZoom < 0 || $myMinZoom > 19) {
    $myMinZoom = 2;
}
if ($myCustomFormat != 'jpg') {
    $myCustomFormat = 'png';
}

// Set map properties
$map->setWidth($width);
$map->setHeight($height);
$map->setZoom($zoomy);
$map->setMinZoom($myMinZoom);
$map->setMaxZoom($myMaxZoom);
$map->setControlSize($myControlSize);
$map->setMapCustomURL($myCustomMapURL);
$map->setMapCustomFormat($myCustomFormat);
$map->setMapTitle($mapTitle);

// Set map controls
$map->setControl($myControl !== 'true');
$map->setLabels($showLabels === 'true');
$map->setCustomMap($myCustomMap === 'true');
$map->setType($myType !== 'true');
$map->setOverview($myOverview === 'true');
$map->setLegend($myLegend !== 'false');

if (!empty($myCustomMapURL)) {
    $map->setCustomMap(true);
    $map->setType(false);
}

$map->showEdit(true);

// Helper functions
function nl2brStrict($text, $replacement = '`') {
    return preg_replace("((\r\n)+)", trim($replacement), $text);
}

function curPageURL() {
    $pageURL = 'http';
    if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
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
    $newcontents = htmlspecialchars($_POST["markerLocationField"] ?? '', ENT_QUOTES, 'UTF-8');
    if (!empty($_POST['markerLocationField'])) {
        $fp = fopen($i, "w");
        if (!$fp) {
            echo 'Error: Cannot open file.';
            exit;
        }
        fwrite($fp, $newcontents);
        fclose($fp);
    }
}

// From map_id
if (!empty($map_info) && empty($bunchOaddresses)) {
    $myStr = nl2brStrict($map_info);
    $myStr = str_replace("\t", " ", $myStr);
    $myStr = addslashes($myStr);

    if (strpos($myStr, '"') !== false && strpos($myStr, '\"') === false) {
        $myStr = str_replace('"', '\"', $myStr);
    }
    if (strpos($myStr, "'") !== false && strpos($myStr, "\'") === false) {
        $myStr = str_replace("'", "\'", $myStr);
    }

    $myStr = str_replace("Multiplottr formatted data.", "", $myStr);
    $lines = explode("`", $myStr);
    $lines = array_unique($lines);
    $lineCount = count($lines);
    for ($i = 0; $i < $lineCount; $i++) {
        list($field1, $field2, $field3, $field4) = explode('|', $lines[$i]);
        $field1 = str_replace(array("***EDITTED_CONTENT_LOCATION***"), "", $field1);
        if (!empty($field1)) {
            $map->addAddress($field1, $field2, $field3, $field4);
        }
        $line = htmlspecialchars($lines[$i], ENT_QUOTES);
        $line = preg_replace('/\p{C}+/u', '\\n', $line);
        $line = str_replace(array("***EDITTED_CONTENT_LOCATION***"), "", $line);

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
        });
        </script>
        ";
    }

    if ($map_username !== $_user['user_username']) {
        $map->showEdit(false);
    }
}

// $bunchOaddresses is from $_POST['markerLocationField'];
if (!empty($bunchOaddresses)) {
    if (substr($myDatabase, 0, 2) == "da") {
        writeToFlatFile($myDatabase);
    } else {
        $showMapNow = true;
        $myStr = nl2brStrict($bunchOaddresses);
        $myStr = str_replace("\t", " ", $myStr);
        $myStr = addslashes($myStr);
        $lines = explode("`", $myStr);
        $lines = array_unique($lines);
        $lineCount = count($lines);
        for ($i = 0; $i < $lineCount; $i++) {
            list($field1, $field2, $field3, $field4) = explode('|', $lines[$i]);
            if (!empty($field1)) {
                $map->addAddress($field1, $field2, $field3, $field4);
            }
            $line = htmlspecialchars($lines[$i], ENT_QUOTES);
            $line = str_replace("'", "", $line);
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
            });
            </script>
            ";
        }
    }
}

// $myDatabase is from flat file database
if (!empty($myDatabase)) {
    $fp = fopen($myDatabase, 'r');
    if (!$fp) {
        echo 'ERROR: Unable to open file.';
        exit;
    }
    while (!feof($fp)) {
        $line = fgets($fp, 1024);
        list($field1, $field2, $field3, $field4) = explode('|', $line);
        if (!empty($field1)) {
            $map->addAddress($field1, $field2, $field3, $field4);
        }
        $line = htmlspecialchars($line, ENT_QUOTES);
        $line = str_replace("'", "", $line);
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
        });
        </script>
        ";
    }
    fclose($fp);
}

if (!empty($myLocation)) {
    $map->showEdit(false);
    $map->addAddress('~' . $myLocation, 'Here I am.', 'BLUE', 'My Location');
} else {
    if (empty($bunchOaddresses) && empty($myDatabase) && empty($map_info)) {
        $map->addAddress('~15,-42', '3', 'CENTER', 'CENTER');
    }
}

if (!empty($map_name)) {
    echo "
    <script type='text/javascript'>
    $('#mapTitle header').text('" . $map_name . "');
    $('#mapTitleField').val('" . $map_name . "')
    </script>
    ";
}

if (!empty($map_description)) {
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


        </div>
       
 
       
    </body>

		
	
		
</html>