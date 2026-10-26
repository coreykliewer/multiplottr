<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<?php
// this code was updated to php 7.4 via CHATGPT

error_reporting(0);

class MultiplottrMap {
    // Updated var to public
    public $validPoints = array();
    public $invalidPoints = array();
    public $mapWidth = "";
    public $mapHeight = "";
    public $apiKey = "[REDACTED_CONFIG_VALUE]";
    public $showControl = false;
    public $showType = false;
    public $showOverview = false;
    public $myCustomMapURL = '';
    public $myCustomFormat = '';
    public $myMaxZoom = '';
    public $myMinZoom = '';
    public $controlType = 'small';
    public $zoomLevel = '4';
    public $zoomy = '4';
    public $smallMarker = false;
    public $myPolyline = false;
    public $arrayLocations = array();
    public $addtoKML = true;

    // Constructor function
    public function __construct() {
        // Initialization if needed
    }

    public function setDefaultMarker($myDefaultMarker) {
        $this->defaultMarker = $myDefaultMarker;
    }

    public function setDefaultGroupName($myDefaultGroupName) {
        $this->defaultGroupName = $myDefaultGroupName;
    }

    public function centerMap($lat, $long, $zoomLevel) {
        $this->centerMap = "map.setCenter(new GLatLng(" . $lat . "," . $long . "), " . $zoomLevel . ");\\n";
    }

    public function setHowManyPlotted() {
        $this->howManyPlotted = '0';
    }

    public function setTotalPlotted() {
        $this->totalPlotted = '0';
    }    

    public function fetchURL($url) { 
        throw new RuntimeException('Server URL fetching is disabled locally'); 
    }

    public function addAddress($address, $htmlMessage, $newMarkerColor, $groupName) {
        $groupName = htmlspecialchars($groupName);
        
        if ($groupName == '') {
            $groupName = 'group0';
        }
        
        // The PHP server never geocodes addresses; Maps mode uses the browser-only API.
        if (substr(str_replace('***EDITTED_CONTENT_LOCATION***', '', $address), 0, 1) !== '~') {
            $this->invalidPoints[] = $address;
            return;
        }
        $longiLat = false;
        $errorBig = false;
        $oldaddress = $address;

        if (!is_string($address)) { 
            die("All Addresses must be passed as a string"); 
        }
        
        if (substr($address, 0, 1) == "*") {
            $address = str_replace("***EDITTED_CONTENT_LOCATION***", "", $address);
        }
        
        if (substr($address, 0, 1) == "~") {
            $this->myPolyline = true;
            $address = str_replace("~", "", $address);
            list($myLatitude, $myLongitude) = explode(',', $address);
            $longiLat = true;
            // Setting arrayLocations for editing a text doc - Custom DAYZ
            $locationsFields = $address . "|" . $htmlMessage . "|" . $newMarkerColor . "|" . $groupName;
            array_push($this->arrayLocations, $locationsFields);
        } else if (!$longiLat) {
            $address = urlencode($address);
            $url = "https://maps.google.com/maps/geo?q=" . $address . "&output=csv&key=[REDACTED_TOKEN]&signature=[REDACTED_TOKEN]";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0); // Change this to a 1 to return headers
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $mapdata = curl_exec($ch);
            curl_close($ch);

            if (strstr($mapdata, "200")) {
                $mapdata = explode(",", $mapdata);
                $precision = $mapdata[1];
                $newLatitude = $mapdata[2];
                $newLongitude = $mapdata[3];
            } else {
                $errorBig = true;
            }
        }
        
        $htmlMessage = str_replace(array("\r", "\n"), "", $htmlMessage);
        $htmlMessage = addslashes($htmlMessage);
        $newMarkerColor = str_replace(array("\r", "\n", " "), "", $newMarkerColor);
        $firstFour = strtoupper(substr($newMarkerColor, 0, 4));
        $groupName = str_replace(array("\r", "\n"), "", $groupName);
        $colorNumber = 0;

        if (strcmp($firstFour, "HTTP") !== 0) { 
            $newMarkerColor = strtoupper($newMarkerColor); 
        }
        
        $colorMapping = [
            "BLUE" => 1, "GREEN" => 2, "GREY" => 3, "ORANGE" => 4, "RED" => 5, "YELLOW" => 6,
            "SMALLBLUE" => 7, "SMALLGREEN" => 8, "SMALLGREY" => 9, "SMALLORANGE" => 10, 
            "SMALLRED" => 11, "SMALLYELLOW" => 12, "LINE" => 13, "CENTER" => 14, "DOT" => 15, 
            "NUMBER" => 16, "CIRCLE" => 20, "MINI" => 22, "POLYGON" => 17, "PUSHPINRED" => 18, 
            "HEATRED" => 19, "LIN2" => 23, "CYAN" => 24, "LIME" => 25, "VIOLET" => 26, 
            "PURPLE" => 27, "BLACK" => 28, "WHITE" => 29, "PINK" => 30, "MYLOCAL" => 31, 
            "RADIUS" => 32
        ];

        if (array_key_exists($newMarkerColor, $colorMapping)) {
            $colorNumber = $colorMapping[$newMarkerColor];
        } elseif (strcmp($firstFour, "HTTP") == 0) {
            $colorNumber = 21;
        }

        if ($colorNumber == 0) {
            $newMarkerColor = "RED";
            $htmlMessage = "";
            $groupName = "group0";
        }

        if ($longiLat) {
            $pointer = count($this->validPoints);
            $this->validPoints[$pointer] = [
                'lat' => $myLatitude,
                'long' => $myLongitude,
                'passedAddress' => $oldaddress,
                'passedhtmlMessage' => $htmlMessage,
                'newMarkerColor' => $newMarkerColor,
                'groupName' => $groupName
            ];
        } else if ($errorBig) {
            $apiURL = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=YahooDemo&street=";
            $addressData = file_get_contents($apiURL . urlencode($oldaddress));
            $results = $this->xml2array($addressData);
            if (!empty($results['ResultSet']['Result']['Longitude'])) {
                $pointer = count($this->validPoints);
                $this->validPoints[$pointer] = [
                    'lat' => $results['ResultSet']['Result']['Latitude'],
                    'long' => $results['ResultSet']['Result']['Longitude'],
                    'passedAddress' => $oldaddress,
                    'passedhtmlMessage' => $htmlMessage,
                    'newMarkerColor' => $newMarkerColor,
                    'groupName' => $groupName
                ];
            }
        } else {
            $pointer = count($this->validPoints);
            $this->validPoints[$pointer] = [
                'lat' => $newLatitude,
                'long' => $newLongitude,
                'passedAddress' => $oldaddress,
                'passedhtmlMessage' => $htmlMessage,
                'newMarkerColor' => $newMarkerColor,
                'groupName' => $groupName
            ];
        }
    }
	
public function setZoom($zoomy) {
    $this->zoomLevel = $zoomy;
}

public function setWidth($width) {
    $this->mapWidth = $width;
}

public function setHeight($height) {
    $this->mapHeight = $height;
}

public function setAPIkey($key) {
    $this->apiKey = $key;
}

public function setControl($myControl) {
    $this->showControl = $myControl;
}

public function setCustomMap($customMap) {
    $this->customMap = $customMap;
}

public function setMapCustomURL($myCustomMapURL) {
    $this->customMapURL = $myCustomMapURL;
}

public function setMapCustomFormat($myCustomFormat) {
    $this->customFormat = $myCustomFormat;
}

public function setControlSize($myControlSize) {
    $this->controlType = $myControlSize;
}

public function setType($myType) {
    $this->showType = $myType;
}

public function showEdit($allowEdit) {
    $this->displayEdit = $allowEdit;
}

public function setOverview($myOverview) {
    $this->showOverview = $myOverview;
}

public function setLegend($myLegend) {
    $this->showLegend = $myLegend;
}

public function setMinZoom($myMinZoom) {
    $this->minZoom = $myMinZoom;
}

public function setMaxZoom($myMaxZoom) {
    $this->maxZoom = $myMaxZoom;
}

public function setGroup($myGroup) {
    $this->showGroup = $myGroup;
}

public function setLabels($showLabels) {
    $this->showLabels = $showLabels;
}

public function setMapTitle($mapTitle) {
    $this->showMapTitle = $mapTitle;
}

public function showMap(){
    // Local mode uses one async client engine, in both offline and optional Maps mode.
    // No legacy globals, server geocoding, or onload handlers are emitted here.
    echo "<div id='map_canvas' style='width:100%;height:100%;'></div>";
}

 ///////////THIS BLOCK OF CODE IS FROM Roger Veciana's CLASS (assoc_array2xml) OBTAINED FROM PHPCLASSES.ORG//////////////
	 function xml2array($xml) {
        $this->depth = -1;
        $this->xml_parser = xml_parser_create();
        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0); // Don't put tags uppercase
        xml_set_element_handler($this->xml_parser, [$this, "startElement"], [$this, "endElement"]);
        xml_set_character_data_handler($this->xml_parser, [$this, "characterData"]);
        xml_parse($this->xml_parser, $xml, true);
        xml_parser_free($this->xml_parser);
        return $this->arrays[0] ?? [];
    }

    function startElement($parser, $name, $attrs) {
        $this->keys[] = $name;
        $this->node_flag = 1;
        $this->depth++;
    }

    function characterData($parser, $data) {
        $key = end($this->keys);
        $this->arrays[$this->depth][$key] = $data;
        $this->node_flag = 0;
    }

    function endElement($parser, $name) {
        $key = array_pop($this->keys);
        if ($this->node_flag == 1) {
            $this->arrays[$this->depth][$key] = $this->arrays[$this->depth + 1] ?? null;
            unset($this->arrays[$this->depth + 1]);
        }
        $this->node_flag = 1;
        $this->depth--;
    }
//////////////////END CODE FROM Roger Veciana's CLASS (assoc_array2xml) /////////////////////////////////


}//End Of Class

?>
