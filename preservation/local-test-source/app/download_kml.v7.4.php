<?php
// this code was updated to PHP 7.4 via CHATGPT

/*
 * @author:         Luis Rivera
 * @version:        1.0
 * @company:        Multiplottr.com
 * @filename:       download_kml.php
 * @description:    takes post variable <code>kmlBox</code> and sends it as a XML file download
 * @filedate:       2007/09/05
 */

define("_FILENAME", "Map.kml");
define("_DATA_ERROR", "ERROR: no KML data!");
define("_REPLACE_NAME", "<name>Multiplottr.com</name>");
define("_REPLACE_DESCRIPTION", "<description>Plotted Markers from Multiplottr.com</description>");

// replace map name and description
if (!empty($_POST['name'])) {
    $_POST['kmlBox'] = str_replace(_REPLACE_NAME, "<name>" . htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') . "</name>", $_POST['kmlBox']);
    $_POST['kmlBox'] = str_replace(_REPLACE_DESCRIPTION, "<description>" . htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8') . "</description>", $_POST['kmlBox']);
}

// file type header
header('Content-type: application/vnd.google-earth.kml+xml');

// file name
header('Content-Disposition: attachment; filename="' . _FILENAME . '"');

// file source
echo !empty($_POST['kmlBox']) ? stripslashes($_POST['kmlBox']) : _DATA_ERROR;
?>
