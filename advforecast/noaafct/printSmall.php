<?php
$wsDebug		= true;                 // for testing ONLY, set to false after testing
#-------------------------------------------------------------------------------
# Function: display a list of forecast date from nws/noaa
#-------------------------------------------------------------------------------
#  first we set which parts  of the page should be printed
#
$updateTimes	        = false;		// two lines with recent file / new update information
#
$showHazards            = false;                 // show hazard warnings when available
#
$iconGraph	        = false;		// icon type header  with 2 icons for each day (12 hours data)
$topCount	        = 8;			// max nr of day-part forecast-icons 
#
$chartsGraph	        = true;			// high charts graph one colom for every 3 / 6 hours
$graphHeight	        = '340';		// height of graph.					
#
$graphsSeparate	        = false;		// graph separate (true) or in a tab (false)
#
$fcstTable	        = true;			// table with one line for every 3 / 6 hours
$plainTable             = true;                 // table with plain forecast
$tabHeight 	        = '340';		// to restrict height of tabs to suppress very large/long pages
#
#-------------------------------------------------------------------------------
# some general settings FOR THIS PAGE
#
$colorClass             = 'green';              // ##### CSS supported colors pastel green blue beige orange 
$includeHTML		= true; 		// <head><body><css><scripts> are loaded
$pageWidth		= '700px';		// set do disired width 999px  or 100%
#
#-------------------------------------------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) { //--self downloader --
   $filenameReal = __FILE__;
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
#  ------------------- do not change anything below this line ------------------
$echo   = '';
#  ------------------- error reporting -----------------------------------------
if (isset($_REQUEST['debug']) || $wsDebug == true ) {
	$wsDebug	= true;
	ini_set('display_errors', 'On'); 
	error_reporting(E_ALL);	
	$echo   .= '<!-- debug is switched on by user request - error reporting ALL -->'.PHP_EOL;
}
# ------------------------------------------------------------------------------
$pageName	= 'startNoaaSmall.php';
$pageVersion	= '3.00  2014-07-05';
$mypage	        = $string       = $pageName.'- version: ' . $pageVersion;
$pageFile 	= basename(__FILE__);			// check to see this is the real script
if ($pageFile <> $pageName) {
	$string .= ' - '.$pageFile .' loaded instead';
}
$echo 	.=  '<!-- module loaded:'.$string.' -->'.PHP_EOL;
$title  = '<title>Noaa  forecast. Script '.$pageName.'</title>';
#  ------------------- load all settings ---------------------------------------
$script	= 'noaaSettings.php';
$echo   .='<!-- trying to load '.$script.' -->'.PHP_EOL;
include $script ;
#
#  ------------------- print html head css  ------------------------------------
#
if ($includeHTML) {
        echo
'<!DOCTYPE html>
<html lang="'.$myLang.'">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="Keywords" content="weather reports, wunderground, weather history" />
<link rel="stylesheet" href="./noaa3.css" type="text/css">
<style type = "text/css">
body {margin: 0px;	font-family: arial; font-size: 9pt;}
table {font-size: 9pt;}
</style>
'.$title.PHP_EOL;
}
if ($chartsGraph) {
        echo 
'<script type="text/javascript">
var docready=[],$=function(){return{ready:function(fn){docready.push(fn)}}};
</script>'.PHP_EOL;
}
if ($includeHTML) {
        echo
'</head>
<body class="'.$colorClass.'" style="width: '.$pageWidth.';';
        if ($pageWidth <> '100%') {echo ' margin: 0 auto;';}
        echo '">'.PHP_EOL;
}
echo $echo;
#
#  ------------------- generate and print requested info   ---------------------
if ($showHazards || $iconGraph || $plainTable) {
        $script	= 'noaaPlainGenerateHtml.php';
        echo '<!-- trying to load '.$script.' -->'.PHP_EOL;
        include $script ;
}
#
$script	= 'noaaDigitalGenerateHtml.php';
echo '<!-- trying to load '.$script.' -->'.PHP_EOL;
include $script;
#
if ($updateTimes) {
	echo 
'<div class="blockHead" style="">'.
$wsUpdateTimes.'
</div>'.PHP_EOL;
}
if ($showHazards && $hazardsString <> '') {
	echo 
'<div class="blockDiv"><br />'.
$hazardsString.'
</div>'.PHP_EOL;
}
if ($iconGraph) {
	echo 
'<div class="noaadiv" style="">
<br />'.
$noaaIconsHtml.'
</div>'.PHP_EOL;
}
if ($chartsGraph && $graphsSeparate) {
	echo 
'<div id="containerTemp" class="noaadiv" style="height: '.$graphHeight.'px; ">
	here the graph will be drawn
</div>'.
$graphPart1.PHP_EOL;
}
# now the tabs for the tables with all data
#
if ($fcstTable || $plainTable ) {
if ($tabHeight <> '') { $styleHeight ='height:'.(int) $tabHeight.'px;';} else {$styleHeight = '';}
#
echo 
'<div class="tabber"  style="">'.PHP_EOL;
if ($chartsGraph && !$graphsSeparate) {		// are the graphs separate on the page or are they in a tab  (=false)
	echo
'	<div class="tabbertab" style="">
		<h2> '.wsnoaafcttransstr('Graph').' </h2>
		<div id="containerTemp" class="noaadiv" style="height: '.$graphHeight.'px; margin: 4px auto;">
			here the graph will be drawn
		</div>'.
		$graphPart1.'
	</div>'.PHP_EOL;	
}
if ($plainTable ) {
        echo '	<div class="tabbertab noaadiv" style="'.$styleHeight.'"><h2> '.wsnoaafcttransstr('Forecast').' </h2>'.
	$noaaPlainText.
	$creditLink.'
	</div>'.PHP_EOL;
}
if ($fcstTable) {
        echo '	<div class="tabbertab noaadiv" style="'.$styleHeight.'"><h2> '.wsnoaafcttransstr('Details').' </h2>'.
	        $wsFcstTable.'
	</div>'.PHP_EOL;
}
echo '</div>'.PHP_EOL;  // eo tabber
#echo 
'<div class="blockHead" style="">'.
$creditString.'
</div>'.PHP_EOL;
}
if ($fcstTable) {
	echo '<script type="text/javascript" src="'.$myJavascriptsDir.'tabber.js"></script>'.PHP_EOL;
}
if ($chartsGraph) {
	echo '<script type="text/javascript" src="'.$myJavascriptsDir.'jquery.js"></script>'.PHP_EOL;
	echo '<script type="text/javascript" src="'.$myJavascriptsDir.'highcharts.js"></script>'.PHP_EOL;
	echo '<script type="text/javascript">$=jQuery;jQuery(document).ready(function(){for(n in docready){docready[n]()}});</script>'.PHP_EOL;
}#  ------------------- print rest of enclosing html  ---------------------------
#
if ($includeHTML) {
        echo '
</body>
</html>';
}
?>