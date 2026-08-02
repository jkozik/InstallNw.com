<?php
#--------------------------------------------------------------------------------------------------
# check optional settings, set to default if missing	
#--------------------------------------------------------------------------------------------------
if (!isset ($arr_fct) || 
    !is_array ($arr_fct) )      {$arr_fct       = array();}
if (!isset ($use_test) )        {$use_test      = false;}       # $use_test     = true;  use test files, do not load from noaa
if (!isset ($curlFollow) )      {$curlFollow    = false;}       # $curlFollow   = true;  allow redirects, not allowed by all webhosts
if (!isset ($timeout) )         {$timeout       = 20;}          # waittime for loading from URL
if (!isset ($wsDebug) )         {$wsDebug       = true;} 
if (!isset ($showHazards) )     {$showHazards   = true;}
#--------------------------------------------------------------------------------------------------
# Metadata about a point. This is the primary endpoint for forecast information for a location. 
# It contains linked data for the forecast, the hourly forecast, observation and other information.
#--------------------------------------------------------------------------------------------------
$url_point      = 'https://api.weather.gov/points/'.noaa_adjust_latlon($lat).','.noaa_adjust_latlon($lon);
$url_hazard     = 'https://api.weather.gov/alerts/active?point='.noaa_adjust_latlon($lat).','.noaa_adjust_latlon($lon);
$url_hazard_2   = 'https://alerts-v2.weather.gov/products/';
#
########################  DO NOT CHNAGE BELOW THIS POINT  ########################################
#
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) { //--self downloader --
   $filenameReal = __FILE__;	# display source of script if requested so
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;
}
$pageName	= 'noaaLoadJson.php';
$pageVersion	= '0.00 2017-03-04';
#-------------------------------------------------------------------------------
# 0.00 2017-03-04 NWS / API  changes needs another way of loading the forecast
#-------------------------------------------------------------------------------
if (isset ($insideTemplate) && !isset($SITE)){echo "<h3>invalid call to script $pageName</h3>";exit;}
$SITE['wsModules'][$pageName] = 'version: ' . $pageVersion;
$pageFile = basename(__FILE__);			// check to see this is the real script
if ($pageFile <> $pageName) {$SITE['wsModules'][$pageFile]	= 'this file loaded instead of '.$pageName;}
ws_message ('<!-- module '.$pageFile.' ==== '.$SITE['wsModules'][$pageFile].' -->');
#
$myScriptName	= $pageFile;
#
#--------------------------------------------------------------------------------------------------
# noaa_adjust_latlon make sure latitude and longitude are noaa compliant	
#--------------------------------------------------------------------------------------------------
function noaa_adjust_latlon($value) {
        $start = $value;
        list ($first, $next) = explode ('.' , $start .'.');
        $len = strlen ((string) $next);

        
        if ($len > 4) { 
                $next = substr($next,0,4);
                $start = $first.'.'.$next;
                $len = 4;
        }
        if ($len > 1 && substr($next,-1) == '0') { 
                $start = $first.'.'.substr($next,0,$len -1).'1';
        }
        if ($len == 1 && $next == '0') { 
                $start = $first.'.'.'1';
        }
        return $start;
} // eof noaa_adjust_latlon
#--------------------------------------------------------------------------------------------------
# noaa_json_load get the data using CURL	
#--------------------------------------------------------------------------------------------------
function noaa_json_load ($url = '') {
        global $curlFollow, $timeout;
        if ($timeout  < 10) {$timeout       = 10;}
        $userAgent      = 'Mozilla/5.0 (noaaPlainGenerateHtml.php - weerstation-leuven.be)';
        $ch             = curl_init();                          // initialize a cURL session
        curl_setopt($ch, CURLOPT_URL, $url);                    // connect to provided URL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);            // verify peer off,removes a lot of errors with older hosts
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);        // nws checks this
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);            // data timeout
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         // return the data transfer
        curl_setopt($ch, CURLOPT_NOBODY, false);                // do the download request without getting the body
        curl_setopt($ch, CURLOPT_HEADER, false);                // include header information
        if (isset ($curlFollow) ) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $curlFollow); // follow Location: redirect allowed
                curl_setopt($ch, CURLOPT_MAXREDIRS, 1);         //   but only one time
        }
        $rawData        = curl_exec($ch);                       // try to get the data
        $info	        = curl_getinfo($ch);
        $errors         = curl_error($ch);
        curl_close ($ch);
        unset ($ch);
        ws_message ('<!-- Return codes:'.print_r ($info,true).' -->');
        ws_message ('<!-- Error  codes:'.print_r ($errors,true).' -->');
        if (strlen($rawData) > 2) { 
                return $rawData;
        }
        echo '<p><b>No data</b> was retrieved for '.$url.'</p>'.PHP_EOL;
} // eof noaa_json_load
#--------------------------------------------------------------------------------------------------
# noaa_json_check check if there are errors in the json,  thanks to Ken True for this code snippet	
#--------------------------------------------------------------------------------------------------
function noaa_json_check ($rawData = '') {
        $array = json_decode($rawData,true); // parse the JSON into an associative array
        
        if (strlen($rawData < 10) )             {return $array;}
        if (!function_exists('json_last_error')){return $array;}
        $error  = true;
        switch (json_last_error()) {
                case JSON_ERROR_NONE:           $JSONerror = '- No errors';  $error = false;            break;
                case JSON_ERROR_DEPTH:          $JSONerror = '- Maximum stack depth exceeded';          break;
                case JSON_ERROR_STATE_MISMATCH: $JSONerror = '- Underflow or the modes mismatch';       break;
                case JSON_ERROR_CTRL_CHAR:      $JSONerror = '- Unexpected control character found';    break;
                case JSON_ERROR_SYNTAX:         $JSONerror = '- Syntax error, malformed JSON';          break;
                case JSON_ERROR_UTF8:           $JSONerror = '- Malformed UTF-8 characters, possibly incorrectly encoded'; break;
                default:                        $JSONerror = '- Unknown error';                         break;
        }
        ws_message ('<!-- noaa_json_check, error = '.$JSONerror.' -->');
        if ($error) {
                ws_message ('<!-- content='.print_r($rawData,true).' -->',true);
        }
        return $array;
} // eof noaa_json_check
#--------------------------------------------------------------------------------------------------
# When set to test mode the data is not loaded, but old test data is used
#
# First URL gives a few general fields like distance to nearby city	
#--------------------------------------------------------------------------------------------------$url    = 'https://api.weather.gov/points/'.$myLatitude.','.$myLongitude;
if ($use_test) { $rawData       = '{ "@context": [ "https://raw.githubusercontent.com/geojson/geojson-ld/master/contexts/geojson-base.jsonld", { "wx": "https://api.weather.gov/ontology#", "s": "https://schema.org/", "geo": "http://www.opengis.net/ont/geosparql#", "unit": "http://codes.wmo.int/common/unit/", "@vocab": "https://api.weather.gov/ontology#", "geometry": { "@id": "s:GeoCoordinates", "@type": "geo:wktLiteral" }, "city": "s:addressLocality", "state": "s:addressRegion", "distance": { "@id": "s:Distance", "@type": "s:QuantitativeValue" }, "bearing": { "@type": "s:QuantitativeValue" }, "value": { "@id": "s:value" }, "unitCode": { "@id": "s:unitCode", "@type": "@id" }, "forecastOffice": { "@type": "@id" }, "forecastGridData": { "@type": "@id" }, "publicZone": { "@type": "@id" }, "county": { "@type": "@id" } } ], "id": "https://api.weather.gov/points/30.0693,-93.6716", "type": "Feature", "geometry": { "type": "Point", "coordinates": [ -93.6716, 30.0693 ] }, "properties": { "@id": "https://api.weather.gov/points/30.0693,-93.6716", "@type": "wx:Point", "cwa": "LCH", "forecastOffice": "https://api.weather.gov/offices/LCH", "gridX": 42, "gridY": 86, "forecast": "https://api.weather.gov/points/30.0693,-93.6716/forecast", "forecastHourly": "https://api.weather.gov/points/30.0693,-93.6716/forecast/hourly", "forecastGridData": "https://api.weather.gov/gridpoints/LCH/42,86", "relativeLocation": { "type": "Feature", "geometry": { "type": "Point", "coordinates": [ -93.759536, 30.079136 ] }, "properties": { "city": "West Orange", "state": "TX", "distance": { "value": 8532.0912687715, "unitCode": "unit:m" }, "bearing": { "value": 97, "unitCode": "unit:degrees_true" } } }, "forecastZone": "https://api.weather.gov/zones/forecast/LAZ041", "county": "https://api.weather.gov/zones/county/LAC019", "fireWeatherZone": "https://api.weather.gov/zones/fire/LAZ041", "timeZone": "America/Chicago", "radarStation": "KLCH", "observationStations": "https://api.weather.gov/points/30.0693,-93.6716/stations" } }';}
 else          { $rawData       = noaa_json_load ($url_point);}
#
# now parse the JSON into an associative array
$array          = noaa_json_check ($rawData); // 
# echo '<pre>'.PHP_EOL;   print_r($array['properties']);  exit;

# store some general fields in the forecast array
$arr_fct['information']['fctURL']       = $array['properties']['forecast'];
#
$distance       = $array['properties']['relativeLocation']['properties']['distance']['value'];
$unit           = str_replace ('unit:','',$array['properties']['relativeLocation']['properties']['distance']['unitCode']);
if (trim($unit) <> 'm') {
        $distance       = round ($distance);
} else {$distance       = round ($distance/1609.344);
        $unit = ' Miles ';
};
#
$winddir        = $array['properties']['relativeLocation']['properties']['bearing']['value'];
$windlabel      = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South", "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
$direction      = $windlabel[ fmod((($winddir + 11) / 22.5),16) ];
$string         = '';
$string         .= $distance.$unit.$direction. ' of ';
$string         .= $array['properties']['relativeLocation']['properties']['city'];
$string         .= ' '.$array['properties']['relativeLocation']['properties']['state'];
$arr_fct['information']['location']             = $string;
$arr_fct['information']['forecastOffice']       = $array['properties']['forecastOffice'];
$arr_fct['information']['hazards']              = 0;

#--------------------------------------------------------------------------------------------------
# Second URL contains name of the NWS station	
# The link is contained in the first data retrieved
#--------------------------------------------------------------------------------------------------
$url_office     = $arr_fct['information']['forecastOffice'];

if ($use_test) { $rawData       = '{"@context": {"@vocab": "https://schema.org/"},"@type": "GovernmentOrganization","@id": "https://api.weather.gov/offices/LCH", "id": "LCH", "name": "Lake Charles, LA" }';}
 else          { $rawData       = noaa_json_load ($url_office);}
# now parse the JSON into an associative array
$array          = noaa_json_check ($rawData); // 
# echo '<pre>'.PHP_EOL;   print_r($array['properties']);  exit;

$arr_fct['information']['issued']       =  $array['name'];
#--------------------------------------------------------------------------------------------------	
# The link to get the forecast is contained in the first data retrieved
#--------------------------------------------------------------------------------------------------
$url_fct     = $arr_fct['information']['fctURL'];

if ($use_test) { $rawData = 
'{ "@context": [ "https://raw.githubusercontent.com/geojson/geojson-ld/master/contexts/geojson-base.jsonld", { "wx": "https://api.weather.gov/ontology#", "geo": "http://www.opengis.net/ont/geosparql#", "unit": "http://codes.wmo.int/common/unit/", "@vocab": "https://api.weather.gov/ontology#" } ], "type": "Feature", "geometry": { "type": "Point", "coordinates": [ -93.6716, 30.0693 ] },
 "properties": { "updated": "2017-03-03T15:06:16+00:00", 
 "periods": [ { "number": 1, "name": "Today", "startTime": "2017-03-03T09:00:00-06:00", "endTime": "2017-03-03T18:00:00-06:00", "isDaytime": true, "temperature": 70, "windSpeed": " 8 to 14 mph", "windDirection": "ENE", "icon": "https://api.weather.gov/icons/land/day/few?size=medium", "shortForecast": "Sunny", "detailedForecast": "Sunny, with a high near 70. East northeast wind 8 to 14 mph, with gusts as high as 21 mph." },
 { "number": 2, "name": "Tonight", "startTime": "2017-03-03T18:00:00-06:00", "endTime": "2017-03-04T06:00:00-06:00", "isDaytime": false, "temperature": 49, "windSpeed": "9 mph", "windDirection": "E", "icon": "https://api.weather.gov/icons/land/night/few?size=medium", "shortForecast": "Mostly Clear", "detailedForecast": "Mostly clear, with a low around 49. East wind around 9 mph." },
 { "number": 3, "name": "Saturday", "startTime": "2017-03-04T06:00:00-06:00", "endTime": "2017-03-04T18:00:00-06:00", "isDaytime": true, "temperature": 70, "windSpeed": "10 mph", "windDirection": "E", "icon": "https://api.weather.gov/icons/land/day/bkn?size=medium", "shortForecast": "Mostly Cloudy", "detailedForecast": "Mostly cloudy, with a high near 70. East wind around 10 mph." },
 { "number": 4, "name": "Saturday Night", "startTime": "2017-03-04T18:00:00-06:00", "endTime": "2017-03-05T06:00:00-06:00", "isDaytime": false, "temperature": 58, "windSpeed": " 6 to 9 mph", "windDirection": "ESE", "icon": "https://api.weather.gov/icons/land/night/rain_showers,40?size=medium", "shortForecast": "Chance Rain Showers", "detailedForecast": "A chance of rain showers. Cloudy, with a low around 58. East southeast wind 6 to 9 mph. Chance of precipitation is 40%. New rainfall amounts less than a tenth of an inch possible." },
 { "number": 5, "name": "Sunday", "startTime": "2017-03-05T06:00:00-06:00", "endTime": "2017-03-05T18:00:00-06:00", "isDaytime": true, "temperature": 72, "windSpeed": " 9 to 14 mph", "windDirection": "ESE", "icon": "https://api.weather.gov/icons/land/day/rain_showers,50?size=medium", "shortForecast": "Chance Rain Showers", "detailedForecast": "A chance of rain showers. Cloudy, with a high near 72. East southeast wind 9 to 14 mph, with gusts as high as 20 mph. Chance of precipitation is 50%. New rainfall amounts between a quarter and half of an inch possible." },
 { "number": 6, "name": "Sunday Night", "startTime": "2017-03-05T18:00:00-06:00", "endTime": "2017-03-06T06:00:00-06:00", "isDaytime": false, "temperature": 65, "windSpeed": " 9 to 13 mph", "windDirection": "SE", "icon": "https://api.weather.gov/icons/land/night/rain_showers,40?size=medium", "shortForecast": "Chance Rain Showers", "detailedForecast": "A chance of rain showers. Mostly cloudy, with a low around 65. Chance of precipitation is 40%. New rainfall amounts between a tenth and quarter of an inch possible." },
 { "number": 7, "name": "Monday", "startTime": "2017-03-06T06:00:00-06:00", "endTime": "2017-03-06T18:00:00-06:00", "isDaytime": true, "temperature": 76, "windSpeed": " 8 to 14 mph", "windDirection": "SSE", "icon": "https://api.weather.gov/icons/land/day/tsra,40?size=medium", "shortForecast": "Chance T-storms", "detailedForecast": "A chance of thunderstorms and a chance of rain showers. Mostly cloudy, with a high near 76. Chance of precipitation is 40%. New rainfall amounts between a tenth and quarter of an inch possible." },
 { "number": 8, "name": "Monday Night", "startTime": "2017-03-06T18:00:00-06:00", "endTime": "2017-03-07T06:00:00-06:00", "isDaytime": false, "temperature": 66, "windSpeed": "10 mph", "windDirection": "S", "icon": "https://api.weather.gov/icons/land/night/tsra_sct,30?size=medium", "shortForecast": "Chance T-storms", "detailedForecast": "A chance of thunderstorms and a chance of rain showers. Mostly cloudy, with a low around 66. Chance of precipitation is 30%. New rainfall amounts less than a tenth of an inch possible." },
 { "number": 9, "name": "Tuesday", "startTime": "2017-03-07T06:00:00-06:00", "endTime": "2017-03-07T18:00:00-06:00", "isDaytime": true, "temperature": 77, "windSpeed": " 8 to 14 mph", "windDirection": "S", "icon": "https://api.weather.gov/icons/land/day/tsra,50?size=medium", "shortForecast": "Chance T-storms", "detailedForecast": "A chance of thunderstorms and a chance of rain showers. Mostly cloudy, with a high near 77. Chance of precipitation is 50%. New rainfall amounts between a quarter and half of an inch possible." },
 { "number": 10, "name": "Tuesday Night", "startTime": "2017-03-07T18:00:00-06:00", "endTime": "2017-03-08T06:00:00-06:00", "isDaytime": false, "temperature": 57, "windSpeed": "8 mph", "windDirection": "E", "icon": "https://api.weather.gov/icons/land/night/tsra_sct,30?size=medium", "shortForecast": "Chance T-storms", "detailedForecast": "A chance of thunderstorms and a chance of rain showers. Mostly cloudy, with a low around 57. Chance of precipitation is 30%. New rainfall amounts less than a tenth of an inch possible." },
 { "number": 11, "name": "Wednesday", "startTime": "2017-03-08T06:00:00-06:00", "endTime": "2017-03-08T18:00:00-06:00", "isDaytime": true, "temperature": 70, "windSpeed": " 8 to 12 mph", "windDirection": "NE", "icon": "https://api.weather.gov/icons/land/day/tsra_hi,20?size=medium", "shortForecast": "Slight Chance T-storms", "detailedForecast": "A slight chance of thunderstorms and a slight chance of rain showers. Partly sunny, with a high near 70. Chance of precipitation is 20%." },
 { "number": 12, "name": "Wednesday Night", "startTime": "2017-03-08T18:00:00-06:00", "endTime": "2017-03-09T06:00:00-06:00", "isDaytime": false, "temperature": 54, "windSpeed": "8 mph", "windDirection": "ENE", "icon": "https://api.weather.gov/icons/land/night/bkn?size=medium", "shortForecast": "Mostly Cloudy", "detailedForecast": "Mostly cloudy, with a low around 54." },
 { "number": 13, "name": "Thursday", "startTime": "2017-03-09T06:00:00-06:00", "endTime": "2017-03-09T18:00:00-06:00", "isDaytime": true, "temperature": 70, "windSpeed": "9 mph", "windDirection": "ENE", "icon": "https://api.weather.gov/icons/land/day/rain_showers,20?size=medium", "shortForecast": "Slight Chance Rain Showers", "detailedForecast": "A slight chance of rain showers. Partly sunny, with a high near 70. Chance of precipitation is 20%." },
 { "number": 14, "name": "Thursday Night", "startTime": "2017-03-09T18:00:00-06:00", "endTime": "2017-03-10T06:00:00-06:00", "isDaytime": false, "temperature": 54, "windSpeed": "7 mph", "windDirection": "ENE", "icon": "https://api.weather.gov/icons/land/night/rain_showers,20?size=medium", "shortForecast": "Slight Chance Rain Showers", "detailedForecast": "A slight chance of rain showers. Mostly cloudy, with a low around 54. Chance of precipitation is 20%." } ] } }' ;} 
 else          { $rawData       = noaa_json_load ($url_fct);}
 
# now parse the JSON into an associative array
$temp          = noaa_json_check ($rawData); 
# we only need the forecasts, so we skipo the rest 
$array  = $temp['properties'];
unset ($temp);
#echo '<pre>'.print_r($array,true); exit;

$string		= $array['updated'];
$time		= strtotime($string);
$arr_fct['information']['filetime']	= date('c', strtotime($string ) );
$arr_fct['information']['updated']	= date( $dateLongFormat, $time).' '.date($timeFormat, $time);			
#
$count  = count($array['periods']);
foreach ($array['periods'] as $key => $array2) {
        $key    = $array2['startTime'];
        $arr_fct['forecast'][$key]['dayPart']           = $array2['name'];
        $arr_fct['forecast'][$key]['startTime']         = $array2['startTime'];
        $arr_fct['forecast'][$key]['endTime']           = $array2['endTime'];
        $isDaytime      = $array2['isDaytime'];
        $temperature    = $array2['temperature'];
        if ($isDaytime) {
                $arr_fct['forecast'][$key]['tempMaxNU'] = $temperature;
                $arr_fct['forecast'][$key]['tempMax']   = $temperature.'F';
        } else {$arr_fct['forecast'][$key]['tempMinNU'] = $temperature;
                $arr_fct['forecast'][$key]['tempMin']   = $temperature.'F';
        }
        $arr_fct['forecast'][$key]['weatherDescShort']  = $array2['shortForecast'];
        $arr_fct['forecast'][$key]['weatherDescText']   = $array2['detailedForecast'];
        $arr_fct['forecast'][$key]['windSpeed']         = $array2['windSpeed'];
        $arr_fct['forecast'][$key]['windSpeed']         = $array2['windSpeed'];
        $arr_fct['forecast'][$key]['windDirection']     = $array2['windDirection']; 
        $arr_fct['forecast'][$key]['icon']              = $array2['icon'];
        # [icon] => https://api.weather.gov/icons/land/night/bkn/rain_showers,20?size=medium
        # "icon": " https://api.weather.gov/icons/land/day/rain_showers,30/tsra,50?size=medium
        if ($isDaytime) {
                list ($iconFirst,$iconRest)	= explode('day/',  $array2['icon']);
        } else {list ($iconFirst,$iconRest)	= explode('night/',$array2['icon']);
        }
        list ($icons, $size )   = explode ('?',$iconRest.'?');
        $arrPOP                 = explode (',',$icons.',');
        $arr_fct['forecast'][$key]['PoP']       = '';
        if (isset ($arrPOP[1])) {
                list ($pop,$rest) = explode('/', $arrPOP[1].'/');
                $arr_fct['forecast'][$key]['PoP']       = $pop;
        }
        $arrRest                = explode ('/',$arrPOP[0].'/');
        $arr_fct['forecast'][$key]['noaaIcon']          = $arrRest[0];
        $arr_fct['forecast'][$key]['noaaIconurl']        = htmlspecialchars($array2['icon']);
        $defaultIcon            = wsconvertnoaaicon ($arrRest[0]);
        if (!$isDaytime && substr($defaultIcon,0,1) < '6') {$defaultIcon .= 'n';}
        $arr_fct['forecast'][$key]['defaultIcon']       = $defaultIcon;
        $arr_fct['forecast'][$key]['defaultIconurl']    = $myDefaultIconsDir.$defaultIcon.'.'.$myDefaultIconsExt;     
}
#--------------------------------------------------------------------------------------------------	
# hazerds
#--------------------------------------------------------------------------------------------------
if ($showHazards == false) {return;}
if ($use_test) { $rawData = '
{   "type": "FeatureCollection",
    "features": [],
    "properties": { "title": "Current watches, warnings, and advisories for 30.0693 N, 93.6716 W" }
}';}
 else          { $rawData       = noaa_json_load ($url_hazard );}
# now parse the JSON into an associative array
$array          = noaa_json_check ($rawData); 
$count = 0;
foreach ($array['features'] as $key => $feature) {  # echo '<pre>'.print_r($feature,true); exit;
        $count++;
        $arr_fct['information']['hazards']              = $count;
        $arr_fct['hazard'][$count]['hazardUrl']         = $url_hazard_2.$feature['properties']['id'];
        $arr_fct['hazard'][$count]['hazardType']        = $feature['properties']['event'];
        $arr_fct['hazard'][$count]['description']       = $feature['properties']['description'];
}


# end of script 