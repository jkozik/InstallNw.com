<?php
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
$pageName	= 'noaaPlainGenerateHtml.php';
$pageVersion	= '3.30 2017-03-03';  // modified cleaned - latest - fix dualimage.php - adapted for NWS / API  changes
#-------------------------------------------------------------------------------
# 3.30 2017-03-04 adapted for NWS / API  changes
#-------------------------------------------------------------------------------
if (isset ($insideTemplate) && !isset($SITE)){echo "<h3>invalid call to script $pageName</h3>";exit;}
$SITE['wsModules'][$pageName] = 'version: ' . $pageVersion;
$pageFile = basename(__FILE__);			// check to see this is the real script
if ($pageFile <> $pageName) {$SITE['wsModules'][$pageFile]	= 'this file loaded instead of '.$pageName;}
ws_message ('<!-- module '.$pageFile.' ==== '.$SITE['wsModules'][$pageFile].' -->');
#
$myPageNoaa1	= $pageFile;
#-------------------------------------------------------------------------------
# Display a list of forecast date from nws/noaa
#-------------------------------------------------------------------------------
# First get the data from the weather class
$weather 	= new noaaPlainWeather ();
$returnArray 	= $weather->getWeatherData(round($myLatitude,3),round($myLongitude,3));
#echo '<pre>'.print_r($returnArray, true); exit;
#-------------------------------------------------------------------------------
# now we generate the html to be used for output to the screen
#
$city = $myArea; #$returnArray['information']['location'];
$line1= 'National Weather Service '.wsnoaafcttransstr('forecast for').': <span style="color: green;">'.$city.'</span>';
$line2= wsnoaafcttransstr('Issued by').': '.'National Weather Service '.$returnArray['information']['issued'];
$line3= wsnoaafcttransstr('Updated').  ': '.$returnArray['information']['updated']; 
# These are the first three lines on the one page city forecast
$stringTop ='<div style="text-align: center;">'.$line1.'<br />'.$line2.'<br />'.$line3.'</div>';

# the icons 
if ($noaaIconsOwn) {$showPoP = false;} else  {$showPoP = true;}
$tdWidth	= floor(100 / $topCount).'%';
$daypart 	= $icon = $PoP = $desc = $temp = '';
$noaaIconsHtml	='
<table class= "genericTable" style="width: 100%;">
  <tbody>
';
$first='    <tr>
      <td style="width: '.$tdWidth.'; vertical-align: top; text-align:center;  font-size: 80%;">';
$PoPNeeded	= false;
$count		= 1;
foreach ($returnArray['forecast'] as $key => $arr) {
	if (!isset ($arr['PoP']) || !isset ($arr['icon'] ) || $key == 0){continue;}   // skip all other information
	if ($count > $topCount) {break;} else {$count++;}
	$arrTxt 	= explode (' ',$arr['dayPart'].' &nbsp;');
	$daypartTxt 	= str_replace($arrTxt[0],$arrTxt[0].'<br />&nbsp;',$arr['dayPart']);
	$daypart	.= $first.'<span style="font-weight: bold;">'.$daypartTxt.'</span></td>';
	$iconImg	='<img src="';
	if ($noaaIconsOwn) {
		$iconImg 	.= $arr['noaaIconurl'];
	} else {
		$iconImg 	.= $arr['defaultIconurl'];
		$arr['weatherDescShort'] .= '<!-- '.$arr['noaaIcon'].' -->';
	}
	$iconImg	.= '" style="width: '.$wsIconWidth .';" title="'.$arr['weatherDescShort'].'" alt="'.$arr['weatherDescShort'].'">';
	$icon		.= $first.$iconImg.'</td>';
	if ($showPoP && $arr['PoP'] > 0) {
		$PoP	.= $first.'PoP: '.$arr['PoP'].'%</td>';
		$PoPNeeded	= true;
	} else {$PoP	.= $first.'</td>';}
	$descTxt	= str_replace('Slight Chc','Slight&nbsp;Chc',trim($arr['weatherDescShort']) );
	$desc		.= $first.$descTxt.'</td>';
	if (isset ($arr['tempMin']) ){
		$temp	.= $first.'<span style="color: blue;">Lo: </span>'.noaacommontemperature($arr['tempMin']).'</td>';
	} else {
		$temp	.= $first.'<span style="color: red;">Hi: </span>'.noaacommontemperature($arr['tempMax']).'</td>';
	}
	$first='
      <td style="width: '.$tdWidth.'; vertical-align: top; text-align:center; font-size: 80%;">';
}
$daypart.= '
    </tr>'.PHP_EOL;
$icon	.= '
    </tr>'.PHP_EOL;
if ($showPoP) {$PoP	.= '
    </tr>'.PHP_EOL;}
$desc	.= '
    </tr>'.PHP_EOL;
$temp	.= '
    </tr>'.PHP_EOL;

$noaaIconsHtml .= $daypart.$icon;
if ($PoPNeeded == true) {$noaaIconsHtml .= $PoP;}
$noaaIconsHtml .= $desc.$temp.'  <tbody>
</table>'.PHP_EOL;

# -----------   are there any warnings
$hazardsString	= '';
if ($returnArray['information']['hazards']<> 0 && isset ($returnArray['hazard'])) {
	$hazardsString = '<div style="width: 100%; background-color: #FFF0F0; font-weight: bold; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #980000;">
  <p style="width: 100%; background-color: #980000; margin: 0px; padding: 3px 0px; color: white;">&nbsp;&nbsp;&nbsp;HAZARDOUS&nbsp;&nbsp;WEATHER&nbsp;&nbsp;CONDITIONS</p>
    <p style="padding: 5px 0px 10px 15px; margin: 0px; font-size: 110%; color: #980000;">'.PHP_EOL;
	foreach ($returnArray['hazard'] as $key => $arr) {
		$hazardsString .= '    <a href="'.$arr['hazardUrl'].'" title ="'.$arr['description'].'" target="_blank" style="color: #980000; ">'.$arr['hazardType'].'</a><br />'.PHP_EOL;
	}  // eo each forecast
	$hazardsString .= '</p></div>'.PHP_EOL;	

}  // eo hazards
# -----------
$noaaPlainTextHead = '<p style="margin: 4px; color: blue; font-size: 200%;">7-DAY FORECAST</p>'.PHP_EOL;
$rowColor	= 'row-dark'; // = row-light;
$noaaPlainText= '
<table class="genericTable" style="width: 100%;  text-align:left;">
  <tbody>'.PHP_EOL;
foreach ($returnArray['forecast'] as $key => $arr) {
	if (!isset ($arr['PoP']) || !isset ($arr['icon'] ) || $key == 0){continue;}   // skip all other information
	$arrTxt = explode (' ',$arr['dayPart'].' &nbsp;');
	$daypartString = str_replace($arrTxt[0],$arrTxt[0].'<br />&nbsp;',$arr['dayPart']);
	if ($noaaIconsOwn) {
		$iconTable	= $arr['noaaIconurl'];
	} else {
		$iconTable 	= $arr['defaultIconurl'];
	}
	$noaaPlainText .= '
    <tr class="'.$rowColor.'" >
      <td style="vertical-align: middle; text-align:right;  font-weight:bold; padding: 10px 10px 10px 10px;"><span style="">'.$daypartString.'</span></td>';
 	$noaaPlainText .= '
      <td style="vertical-align: top; text-align:right;  font-weight:bold; padding: 10px 10px 10px 10px;">
      <img src="'.$iconTable.'" style="vertical-align: bottom; width: '.$wsIconWidth .';" title="'.$arr['weatherDescShort'].'" alt="'.$arr['weatherDescShort'].'"></td>';
	
    	$noaaPlainText .= '
      <td  style="vertical-align: middle; padding: 10px 10px 10px 10px;">'.$arr['weatherDescText'].'</td>
    </tr>'.PHP_EOL;
    if ($rowColor	== 'row-dark') {$rowColor	= 'row-light';} else {$rowColor	= 'row-dark';}
}
$noaaPlainText .= '
  <tbody>
</table>
<br />'.PHP_EOL;
$city_link=str_replace(' ','%20',$city);
$creditLink = '&nbsp;Forecast from <a href="http://forecast.weather.gov/MapClick.php?CityName='.$city_link.'" target="_blank">NOAA-NWS</a> for '.$returnArray['information']['location'].'<br /><br />';
#--------------------------------------------------------------------------------------------------
# retrieve weather infor from weathersource  
# and return array with retrieved data in the desired language and units C/F
#--------------------------------------------------------------------------------------------------
class noaaPlainWeather{
	# public variables
	public $lat		= '41.3';	// 
	public $lon		= '-72.8';	// 
# private variables
	private $uomTemp	= 'F';		// <temperature type="maximum" units="Fahrenheit" 
	private $uomWindDir	= 'deg';	// <direction type="wind" units="degrees
	private $uomWindSpeed	= 'kts';	// <wind-speed type="sustained" units="knots"
	private $uomHum		= 'percent';	// <humidity type="relative" 
	private $uomBaro	= 'inHg';	// <pressure type="barometer" units="inches of mercury"
	private $uomCloud	= 'percent';	// 
	private $uomRain	= 'in';
	private $uomDistance	= 'mi';		// <visibility units="statute miles">
	private $uomPoP		= '%';		// <probability-of-precipitation  units="percent"
	
	private $enableCache	= true; 	// cache should be anabled when frequent request are made. Keep in mind that the data is only refreshed every hour by google 
	private $cache		= 'cache';	// cache dir is created when not available
	private $cacheTime 	= 3600; 	// Cache expiration time Default: 3600 seconds = 1 Hour
	private $cacheFile	= 'xxx';
	private $rawData		= '';
#--------------------------------------------------------------------------------------------------
# public functions	
#--------------------------------------------------------------------------------------------------
	public function getWeatherData($lat = '', $lon = '') {
		global $dateLongFormat, $timeFormat, $myCacheDir, $myPageNoaa1,$showHazards,
		$myDefaultIconsDir, $myDefaultIconsExt ;
		#----------------------------------------------------------------------------------------------
		# try loading data from cache
		#----------------------------------------------------------------------------------------------
	        if ( $this->enableCache && !empty($this->cache) ){
			$this->cache	= $myCacheDir;
			$string		= $myPageNoaa1.'_'.round(trim($lat),3).'_'.round(trim($lon),3);			
			$this->cacheFile= $this->cache .$string.'.txt';
			if (isset ($_REQUEST['force']) && $_REQUEST['force'] == 'noaafct') {
	                        ws_message (  '<!-- module '.$myPageNoaa1.' ('.__LINE__.'):  no cache checked as force=noaafct is used  -->',true);
	                } 
                        elseif (file_exists($this->cacheFile)){	
                                $file_time	= filemtime($this->cacheFile);
                                $now 		= time();
                                $diff		= ($now - $file_time);		
                                ws_message (  '<!-- module '.$myPageNoaa1.'  ('.__LINE__.'): '."($this->cacheFile) 
        cache time   = ".date('c',$file_time)." from unix time $file_time
        current time = ".date('c',$now)." from unix time $now 
        difference   = $diff (seconds)
        diff allowed = $this->cacheTime (seconds) -->");	
                                if (isset ($cron_all) ) {		// runnig a cron job
                                        $this->cacheTime = $this->cacheTime - 360;
                                        ws_message (  '<!-- module n'.$myPageNoaa1.'  ('.__LINE__.'): max cache lowered with 360 seconds as cron job is running -->');
                                }
                                if ($diff <= $this->cacheTime){
                                        ws_message (  '<!-- module '.$myPageNoaa1.'  ('.__LINE__.'): '."($this->cacheFile) loaded from cache  -->");
                                        $arr_fct =  unserialize(file_get_contents($this->cacheFile));
                                        return $arr_fct;
                                }
                        }	
		}  		// eo check cache
#
                include 'noaaLoadJson.php'; 
#
		if (!file_exists($this->cache)){
			mkdir($this->cache, 0755);   // attempt to make the cache dir
		}
		if (!file_put_contents($this->cacheFile, serialize($arr_fct))){   
			exit ("<h3>Could not save data to cache ($this->cacheFile).<br />Please make sure your cache directory exists and is writable.<br />Script ended</h3>");
		}
		else {	ws_message (  '<!-- module '.$myPageNoaa1.' ('.__LINE__.'): '."($this->cacheFile) saved to cache  -->");
		}
		return $arr_fct;
	} // eof getWeatherData
}
# ----------------------  version history
# 3.30 2017-03-03 adapted for NWS / API  changes
