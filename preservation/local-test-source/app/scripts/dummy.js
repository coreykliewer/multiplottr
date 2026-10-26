var queryString = window.location.search;
var settings={};
var fieldsList = ["map_id", "mapControl", "hideMapType" , "minZoom", "maxZoom", "mapControlSize", "showMapOverview", "customMapURL"];
var paramsList = ["map_id", "mapControl", "mapType",      "minZoom", "maxZoom", "mapControlSize", "mapOverview",     "customMapURL"];
var checkParams = ["mapControl", "mapType", "mapOverview"];

initSettings = function() {
    if(queryString.length){
        var qParams = queryString.substr(1).split('&');
    }
    for(i=0; i< qParams.length; i++){
        item = qParams[i].split('=');
        if(jQuery.inArray(item[0], paramsList) != -1){ 
            updateSettings(item[0], item[1], (jQuery.inArray(item[0], checkParams) != -1))
        }
    }
}

updateSettings = function(key, value, isCheckbox) {
    val = (isCheckbox) ? value.toString() == 'true' ? 'true' : '' : jQuery.trim(value);
    if(jQuery.inArray(key, paramsList) != -1 && val){ 
        settings[key]=val;
    } else if ( jQuery.inArray(key, paramsList)) {
        delete settings[key];
    }
}

getSettingsUrl = function() {
    var i=0; 
    var _settings=[];
    $.each(settings, function( key, value ) {
        _settings[i++] = key+'='+value ;
    });

    return _settings.join('&');
    //OR
    //return location.hostname +'/?'+_settings.join('&')
}

updateURL = function() { 
    for(i=1; i< fieldsList.length; i++){
        if(jQuery.inArray(fieldsList[i], checkParams) != -1){ 
            val = ''
            if($('#'+fieldsList[i]).attr("checked")) {
                val = 'true';
            }
        } else {
            val = $('#'+fieldsList[i]).val();
        }
        updateSettings(paramsList[i], val)
    }
}

initSettings();//get the params from url and populate settings bucket
$('#updateURL').click(function(){updateURL(); console.log(getSettingsUrl())})