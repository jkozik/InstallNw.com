<?php
$wsDebug		= true;                 // for testing ONLY, set to false after testing
#-------------------------------------------------------------------------------
# Function: display a one page forecast from nws/noaa
#-------------------------------------------------------------------------------
#  first we set which parts  of the page should be printed
#
$updateTimes	        = true;			// 3 lines with recent file / new update information
#
$showHazards            = true;                 // show hazard warnings when available
#
#-------------------------------------------------------------------------------
# some general settings FOR THIS PAGE
#
$colorClass             = 'beige';              // ##### CSS supported colors pastel green blue beige orange 
$includeHTML		= true; 		// <head><body><css><scripts> are loaded
$pageWidth		= '800px';		// set do disired width 999px  or 100%
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
	$echo .= '<!-- debug is switched on by user request - error reporting ALL -->'.PHP_EOL;
}
# ------------------------------------------------------------------------------
$pageName	= 'startNoaaPlain.php';
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
<meta http-equiv="Content-Type" content="text/html; charset='.$myCharset.'" />
<meta name="Keywords" content="weather reports, NWS, NOAA, weather forecast" />
<link rel="stylesheet" href="./noaa3.css" type="text/css">
<style type = "text/css">
body {margin: 0px;	font-family: arial; font-size: 9pt;}
table {font-size: 9pt;}
</style>'.$title.'
</head>
<body class="'.$colorClass.'" style="width: '.$pageWidth.'; margin: 0 auto;">'.PHP_EOL;
}
echo $echo;
#
#  ------------------- generate and print requested info   ---------------------
#
$script	= 'noaaPlainGenerateHtml.php';
echo '<!-- trying to load '.$script.' -->'.PHP_EOL;
include $script ;
#
echo 
'<div class="blockDiv">'.PHP_EOL;
if ($updateTimes) {
        echo 
'<div class="blockHead" style="">'.
$stringTop.'
</div>'.PHP_EOL;
}
echo 
'<div class="noaadiv" style="">
<br />'.
$noaaIconsHtml.'
</div>'.PHP_EOL;

if ( $showHazards  && $hazardsString <> '') {
	echo '<div class="blockDiv">'.PHP_EOL;
	echo $hazardsString;
	echo '</div>'.PHP_EOL;
}
echo $noaaPlainTextHead;
echo $noaaPlainText;
echo $creditLink ;

echo '</div>'.PHP_EOL;
#
#  ------------------- print rest of enclosing html  ---------------------------
#
if ($includeHTML) {
        echo '
</body>
</html>';
}
