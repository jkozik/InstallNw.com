<?php
#-------------------------------------------------------------------------------
# Function: display a list of forecast date from nws/noaa inside Saratoga template
#-------------------------------------------------------------------------------
#  first we set which parts  of the page should be printed
#
$updateTimes	        = true;			// two lines with recent file / new update information
#
$showHazards            = false;                 // show hazard warnings when available
#
$iconGraph	        = true;			// icon type header  with 2 icons for each day (12 hours data)
$topCount	        = 10;			// max nr of day-part forecast-icons 
#
$chartsGraph	        = true;			// high charts graph one colom for every 3 / 6 hours
$graphHeight	        = '340';		// height of graph.					
#
$graphsSeparate	        = true;		        // graph separate (true) or in a tab (false)
#
$fcstTable	        = true;			// table with one line for every 3 / 6 hours
$plainTable             = true;                 // table with plain forecast
$tabHeight 	        = '400';		// to restrict height of tabs to suppress very large/long pages
#
# ---------------END OF CHANGES - LEAVE REST OF SCRIPT AS IS -------------------
$wsmyfolder		= './noaafct/';		// only change this if you stored the wu forecasts scripts in another folder
$mypage			= 'wxStartNoaaFct.php'; // only change this if you renamed this script
$echo                   = '';
# ----------------display source of script if requested so ---------------------
#
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
# --------------------load first part of standard scripts-----------------------
require_once("Settings.php");
require_once("common.php");
# ------------------------------------------------------------------------------
$TITLE = langtransstr($SITE['organ']) . " - " .$mypage;
$showGizmo = false;  // set to false to exclude the gizmo
#
# ----------------load second part of standard scripts--------------------------
include("top.php");
# ------------------------------------------------------------------------------
?>
<link rel="stylesheet" href="<?php echo $wsmyfolder; ?>noaa3.css" type="text/css" media="screen" title="screen" />
<style type = "text/css">
body {margin: 0px;	font-family: arial; font-size: 9pt;}
table {font-size: 9pt;}
pre {tab-size: 16; }
pre {-moz-tab-size: 16; } /* Code for Firefox */
pre {-o-tab-size: 16; } /* Code for Opera */
#main-copy .blockHead a {color: white;}
#main-copy .blockHead small {color: white;}

</style>
<script type="text/javascript">
var docready=[],$=function(){return{ready:function(fn){docready.push(fn)}}};
</script>
</head>
<body class="pastel">
<?php
# ----------------  load third part of standard scripts-------------------------
include("header.php");
include("menubar.php");
# ------------------------------------------------------------------------------
?>
<div id="main-copy">
<?php
#  ------------------- load all settings ---------------------------------------
$script	= 'noaaSettings.php';
$echo   .='<!-- trying to load '.$script.' -->'.PHP_EOL;
include $wsmyfolder.$script;
#  ------------------- generate and print requested info   ---------------------
if ($showHazards || $iconGraph || $plainTable) {
        $script	= 'noaaPlainGenerateHtml.php';
        echo '<!-- trying to load '.$script.' -->'.PHP_EOL;
        include $wsmyfolder.$script;
}
#
$script	= 'noaaDigitalGenerateHtml.php';
echo '<!-- trying to load '.$script.' -->'.PHP_EOL;
include $wsmyfolder.$script;
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
if ($fcstTable|| $plainTable ) {
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
#
echo 
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
}
#------------end of generate and print requested info --------------------------
?>
</div><!-- end main-copy -->
<?php
#------------------------- load last part of standard scripts ------------------
include("footer.php");
#------------------------- end of Page------------------------------------------
?>