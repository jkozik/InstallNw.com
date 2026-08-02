<?php
# ------------------------------------------------------------------------------
if (!isset ($insideTemplate) ){
# ---------------HERE YOU NEED TO MAKE SOME CHANGES       ----------------------
#                                                  ##### check and adapt lines with #####
$myTimezone 		= 'America/Chicago'; 	// ##### -94.6716 39.0693
$myLatitude		= '41.3';     	// ##### North=positive, South=negative decimal degrees
$myLongitude		= '-72.79'; 	 	// ##### East=positive, West=negative decimal degrees
$myLang 		= 'en'; 		// ##### default language you want to use use
$myArea			= 'your area';          // ##### Leuven country side
$myStation		= 'weatherstation-name';// ##### Wilsele weather
#
$myCharset		= 'UTF-8';		// 'UTF-8' is default for stand-alone use - 'ISO-8859-1'  is an alternative
#
$noaaIconsOwn		= true; 		// ##### use noaa icons (true) or our template icons (false)
#
$tempSimple     	= false;                // ##### for below freezing = red - above blue temp colors, set to true
#                                               //        		false =  range of colors, 
$lower			= true;			// ##### all texts to lowercase
# --------------- NORMALLY THE FOLLOWING SETTINGS ARE OK  ----------------------
# date and time formats                         // default date time settings are for USA
$dateTimeFormat     	= 'M j Y g:i a';
$timeFormat 		= 'g:i a';
$hourOnlyFormat 	= 'ga';
$dateOnlyFormat 	= 'M j Y';
$dateLongFormat 	= 'l M j Y';
#
}
# location of the scripts
if (!isset ($wsmyfolder) ) {
        $wsmyfolder		= './';	        // folder where the report scripts are located, 
#                                               // leave as is if you are running this script from that folder
}
# icons
$myImgDir		= $wsmyfolder.'img/';
$myIconsDir		= $wsmyfolder.'img/';
#
$myDefaultIconsDir	= $myIconsDir.'default_icons/';
$myDefaultIconsSml	= $myIconsDir.'default_icons_small/';
$myDefaultIconsExt	= 'png';
#
$myNoaaIconsDir   	= $myIconsDir.'NOAA_Icons/';
$myNoaaIconsSml   	= $myIconsDir.'NOAA_Icons_small/';
$myNoaaIconsExt   	= 'jpg';
#
$myWindIcons		= $myIconsDir.'wind_icons/';
$myWindIconsSmall	= $myIconsDir.'wind_icons_small/';
$windIconsExt		= 'png';
#
$myJavascriptsDir	= $wsmyfolder.'javaScripts/';
$myCacheDir		= $wsmyfolder.'cache/';
#
#
# all unit of measurement                       //  default settings are for USA
#
$uomTemp 	= '&deg;F';		// temp values: ='&deg;C', ='&deg;F'
#$uomTemp	= '&deg;C';		
#
$uomBaro	= ' inHg';		// baro values: =' hPa'   ' mb'   ' inHg'
#$uomBaro	= ' hPa';
#$uomBaro	= ' mb';
#
$uomWind	= ' mph';		// windspeed values: ' km/h'  ' kts'  ' m/s'  ' mph'
#$uomWind	= ' m/s';
#$uomWind	= ' km/h';
#$uomWind	= ' kts';
#
$uomRain	= ' in';		// rain values:  ' mm'  ' in'
#$uomRain	= ' mm';
#
$uomDistance	= ' mi';		// wind run values: ' km'  ' mi'
#$uomDistance	= ' km';
#
$uomSnow	= 'in';	
#$uomSnow	= 'cm';
#
# --------------- END OF SETTINGS ----------------------------------------------
# display source of script if requested so
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
#
$pageName	= 'noaaSettings.php';
$pageVersion	= '3.00 2014-06-12';
$string		= $pageName.'- version: ' . $pageVersion;
$pageFile = basename(__FILE__);			// check to see this is the real script
if ($pageFile <> $pageName) {
	$string.= $pageFile .' loaded instead';
}
$echo 	.=  '<!-- module '.$string.' -->'.PHP_EOL;
#-------------------------------------------------------------------------------
#
if (!isset ($topCount) ) {$topCount = 8;} elseif ($topCount > 10) {$topCount = 10;}
$wsIconWidth 	= '50px';  // width of the icons in the top part.
#
# set the Timezone abbreviation automatically based on $myTimezone;
if (!function_exists('date_default_timezone_set')) {
	 putenv("TZ=" . $myTimezone);
} else {
	 date_default_timezone_set($myTimezone);
}
# colors to use for temp
# temparray 2 starts at -32C, so add 32 to C temp
$tempArray2=array(
'#F6AAB1', '#F6A7B6', '#F6A5BB', '#F6A2C1', '#F6A0C7', '#F79ECD', '#F79BD4', '#F799DB', '#F796E2', '#F794EA', 
'#F792F3', '#F38FF7', '#EA8DF7', '#E08AF8', '#D688F8', '#CC86F8', '#C183F8', '#B681F8', '#AA7EF8', '#9E7CF8', 
'#9179F8', '#8477F9', '#7775F9', '#727BF9', '#7085F9', '#6D8FF9', '#6B99F9', '#68A4F9', '#66AFF9', '#64BBFA', 
'#61C7FA', '#5FD3FA', '#5CE0FA', '#5AEEFA', '#57FAF9', '#55FAEB', '#52FADC', '#50FBCD', '#4DFBBE', '#4BFBAE', 
'#48FB9E', '#46FB8D', '#43FB7C', '#41FB6A', '#3EFB58', '#3CFC46', '#40FC39', '#4FFC37', '#5DFC35', '#6DFC32', 
'#7DFC30', '#8DFC2D', '#9DFC2A', '#AEFD28', '#C0FD25', '#D2FD23', '#E4FD20', '#F7FD1E', '#FDF01B', '#FDDC19', 
'#FDC816', '#FDC816', '#FEB414', '#FEB414', '#FE9F11', '#FE9F11', '#FE890F', '#FE890F', '#FE730C', '#FE730C', 
'#FE5D0A', '#FE5D0A', '#FE4607', '#FE4607', '#FE2F05', '#FE2F05', '#FE1802', '#FE1802', '#FF0000', '#FF0000',
);
#
$arrLookupNoaa = array (
'bkn' => '300',               'blizzard' => '432',          'br' => '252',                'cloudy' => '300',            'cold' => '700',              'du' => '900',                'dust' => '900',              
'fc' => '900',                'few' => '100',               'fg' => '252',                'fog' => '252',               'fu' => '900',                'fzra' => '231',              'fzra_sn' => '432',           
'fzrara' => '432',            'haze' => '150',              'hazy' => '150',              'hi_bkn' => '300',            'hi_few' => '100',            'hi_nbkn' => '300n',          'hi_nfew' => '100n',          
'hi_nsct' => '200n',          'hi_nshwrs' => '111n',        'hi_nskc' => '100n',          'hi_ntsra' => '342n',         'hi_sct' => '200',            'hi_shwrs' => '111',          'hi_skc' => '100',            
'hi_tsra' => '342',           'hot' => '701',               'hur_warn' => '900',          'hur_watch' => '900',         'hurr' => '900',              'hurr-noh' => '900',          'hurr_warn' => '900',         
'hurr_watch' => '900',        'hz' => '150',                'ip' => '232',                'minus_ra' => '111',          'mist' => '250',              'mix' => '432',               'na' => '901',                
'nbkn' => '300n',             'nbknfg' => '252n',           'nblizzard' => '432n',        'nbr' => '252',               'ncloudy' => '300n',          'ncold' => '700',             'ndu' => '900',               
'ndust' => '900',             'nfc' => '900',               'nfew' => '100n',             'nfg' => '252n',              'nfog' => '252n',             'nfu' => '900',               'nfzra' => '432n',            
'nfzra_sn' => '432n',         'nhaze' => '150',             'nhazy' => '150',             'nhurr_warn' => '900',        'nhurr_watch' => '900',       'nip' => '232n',              'nmix' => '432n',             
'novc' => '400n',             'nra' => '211n',              'nra_fzra' => '132n',         'nra_sn' => '231n',           'nrain' => '211n',            'nrain_fzra' => '132n',       'nrain_showers' => '312n',    
'nrain_showers_hi' => '111n', 'nrain_sleet' => '231n',      'nrain_snow' => '231n',       'nraip' => '231n',            'nrasn' => '231n',            'nsct' => '200n',             'nscttsra' => '141n',         
'nshra' => '312n',            'nskc' => '100n',             'nsleet' => '232n',           'nsmoke' => '900',            'nsn' => '121n',              'nsn_ip' => '432n',           'nsnip' => '432n',            
'nsnow' => '121n',            'nsnow_fzra' => '432n',       'nsnow_sleet' => '432n',      'nsvrtsra' => '342n',         'ntor' => '900',              'ntornado' => '900',          'nts_hurr_warn' => '900',     
'nts_warn' => '900',          'nts_watch' => '900',         'ntsra' => '241n',            'ntsra_hi' => '342n',         'ntsra_sct' => '141n',        'nwind' => '600',             'nwind_bkn' => '600',         
'nwind_few' => '600',         'nwind_ovc' => '600',         'nwind_sct' => '600',         'nwind_skc' => '600',         'ovc' => '400',               'pcloudy' => '200',           'pcloudyn' => '200n',         
'ra' => '211',                'ra1' => '211',               'ra_fzra' => '132',           'ra_sn' => '231',             'rain' => '211',              'rain_fzra' => '132',         'rain_showers' => '312',      
'rain_showers_hi' => '111',   'rain_sleet' => '231',        'rain_snow' => '231',         'raip' => '231',              'rasn' => '231',              'sct' => '200',               'sctfg' => '251',             
'scttsra' => '141',           'shra' => '312',              'shra2' => '312',             'skc' => '100',               'sleet' => '232',             'smoke' => '900',             'sn' => '121',                
'sn_ip' => '432',             'snip' => '432',              'snow' => '121',              'snow_fzra' => '432',         'snow_sleet' => '432',        'svrtsra' => '342n',          'tcu' => '400',               
'tor' => '900',               'tornado' => '900',           'tropstorm' => '900',         'tropstorm-noh' => '900',     'ts_hur_flags' => '900',      'ts_hurr_warn' => '900',      'ts_no_flag' => '900',        
'ts_nowarn' => '900',         'ts_warn' => '900',           'ts_watch' => '900',          'tsra' => '241',              'tsra_hi' => '342',           'tsra_sct' => '141',          'tstormn' => '900',           
'wind' => '600',              'wind_bkn' => '600',          'wind_few' => '600',          'wind_ovc' => '600',          'wind_sct' => '600',          'wind_skc' => '600',          'windyrain' => '600',         
'901' => '901');
#
$repl		= array ('&deg;',' ','/');
#
$torain		= strtolower( str_replace ($repl,'',$uomRain ) );
$fromrain	= 'in';

$totemp		= strtolower( str_replace ($repl,'',$uomTemp) );
$fromtemp	= 'f';

$tobaro		= strtolower( str_replace ($repl,'',$uomBaro) );
$frombaro	= 'inhg';

$towind		= strtolower( str_replace ($repl,'',$uomWind) );
$fromwind	= 'mph';

$tosnow		= strtolower( str_replace ($repl,'',$uomSnow) );
$fromsnow	= 'in';

$todistance	= strtolower( str_replace ($repl,'',$uomDistance) );
$fromdistance	= 'mi';
#
if ($myCharset == 'UTF-8') {$degree  ='°';} else {$degree  = utf8_decode ('°');}
#
$utcDiff 	= date('Z');	// used for graphs timestamps
$srise		= 8;		// will be set correctly based on latitude en longitude
$sset		= 20;		// same
#
# How many degrees difference fromnormal temp for chill / heat, before shown in output
$appTempDiff	= 4;	// for C: 4
if ($totemp == 'F') {$appTempDiff = round(9*$appTempDiff/5);}
#
$minUV		= 1;	//  Min UV level to be shown in hour-table
$minPoP		= 9;	// min Pop % before it will be shown when there is no amount of rain forecasted
#
$logoNWS	= '<a href="https://www.weather.gov/" target="_blank"><img src="https://www.weather.gov/images/nws/nws_logo.png" alt="logo nws" style="height: 40px;"  /></a>';
#
$wsnoaalang	= $wsmyfolder.'lang/';	// lang files
#-------------------------------------------------------------------------------------
#  Language array construct
$echo .= '<!-- Creating lang translate array -->'.PHP_EOL;
$ownTranslate	= true;
$wsnoaafctLOOKUP= array();				// array with FROM and TO languages
$missingTrans	= array();				// array with strings with missing translation requests
$myLangfile		= $wsnoaalang.'noaalanguage-'.$myLang.'.txt';
$echo .=  '<!-- Trying to load langfile '.$myLangfile.'  -->'.PHP_EOL;
if (file_exists($myLangfile) ) {
	$echo .=  '<!-- Langfile '.$myLangfile.' loading -->'.PHP_EOL;
	$loaded = $nLanglookup = $skipped = $invalid = 0;
	$lfile 		= file($myLangfile);		// read the file
	foreach ($lfile as $rec) { 
		$loaded++;
		$recin = trim($rec);
		list($type, $item,$translation) = explode('|',$recin . '|||||');
		if ($type <> 'langlookup') {$skipped++; continue;}
		if ($item && $translation) {
			$translation		= trim($translation);
			$item 				= trim($item);
			if ($myCharset <> 'UTF-8') {
				$translation 	= utf8_decode($translation);
			} 
			if ($lower) {
				$translation	= strtolower($translation);
			}						
			$wsnoaafctLOOKUP[$item]  = $translation;
			$nLanglookup++;
		} else {
			$invalid++;
		}  // eo is langlookup
	}  // eo for each lang record
	$echo .= '<!-- loaded: '.$loaded.' - skipped: '.$skipped.' - invalid: '.$invalid.' - used: '.$nLanglookup.' entries of file '.$myLangfile.' -->'.PHP_EOL;
} // eo file exist

# --------------------------------------------------------------------------------------
# functions for noaa scripts
#-------------------------------------------------------------------------------------
#  calculate winddir compass
# --------------------------------------------------------------------------------------
function noaaconvertwinddir($value) {
	global $wsDebug;	
	$winddir = $value;
	if (!isset($winddir)) { 
		$return = "---"; 
	} elseif (!is_numeric($winddir)) { 
		$return = $winddir;
	} else {
		$windlabel = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South",
		 "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
		$return = $windlabel[ fmod((($winddir + 11) / 22.5),16) ];
	}
	if ($wsDebug) {
		echo "<!-- function noaaconvertwinddir in = $value out = $return -->\n";
	}
	return $return;
}
# --------------------------------------------------------------------------------------
#  convert windspeed
# --------------------------------------------------------------------------------------
function noaaconvertwind($value,$from ='',$to ='') {
	global $fromwind, $towind, $wsDebug;
	if ($to == '') {
		if ($towind == 'mh') {$to = 'mph';}  else {$to = $towind;}
	}
	if ($from == '') {
		$from =	 $fromwind;
	}	
	if ($from == $to) {
		if ($wsDebug) {echo '<!-- function wuconvertwind: in = speed: '.$value.', unitFrom: '.$from.', unitTo: '.$to.'. No conversion needed -->'.PHP_EOL;}
		return $value;
	}
	$amount		=str_replace(',','.',$value);
	$out 		= 0;
	$error		= '';
	$convertArr= array
			   (    "kmh"=> array('kmh' => 1	, 'kts' => 0.5399568034557235	, 'ms' => 0.2777777777777778 	, 'mph' => 0.621371192237334 ),
				"kts"=> array('kmh' => 1.852	, 'kts' => 1 			, 'ms' => 0.5144444444444445 	, 'mph' => 1.1507794480235425),
				"ms" => array('kmh' => 3.6	, 'kts' => 1.9438444924406046	, 'ms' => 1 			, 'mph' => 2.236936292054402 ),
				"mph"=> array('kmh' => 1.609344	, 'kts' => 0.8689762419006479	, 'ms' => 0.44704 		, 'mph' => 1 ));
	if ((  		$from	==='kmh')	|| ($from === 'kts') 	|| ($from === 'ms')	|| ($from === 'mph') ) {
		if ((	$to	==='kmh')	|| ($to	  === 'kts') 	|| ($to   === 'ms')	|| ($to   === 'mph') ) {
			$out = $convertArr[$from][$to];
		}  
	}
	$return 	= round($out*$amount,0);
	if ($wsDebug || $error <> '') {
		echo '<!-- function wuconvertwind: in = speed: '.$value.', unitFrom: '.$from.' ,unitTo: '.$to.', out = '.$return.' -->'.PHP_EOL;
	}	
	return $return;
} // eof convert windspeed	
#-------------------------------------------------------------------------------------
#    Convert rainfall
# --------------------------------------------------------------------------------------
function noaaconvertrain($value,$xx='',$yy='') {
	global $fromrain, $torain, $wsDebug;
	if ($fromrain == $torain) {
		if ($wsDebug) {echo '<!-- function wuconvertrain: in = rainfall: '.$value.', unitFrom: '.$fromrain.', unitTo: '.$torain.'. No conversion needed -->'.PHP_EOL;}
		return $value;
	}
	$amount		= str_replace(',','.',$value);
	$out 		= 0;	
	$convertArr	= array
			   ("mm"=> array('mm' => 1		,'in' => 0.03937007874015748 	, 'cm' => 0.1 ),
				"in"=> array('mm' => 25.4	,'in' => 1						, 'cm' => 2.54),
				"cm"=> array('mm' => 10		,'in' => 0.3937007874015748 	, 'cm' => 1 )
				);
	if ((  $fromrain ==='mm') || ($fromrain === 'in') || ($fromrain === 'cm') ) {
		if (($torain ==='mm') ||   ($torain === 'in') || ($torain === 'cm') ) {
			$out = $convertArr[$fromrain][$torain];
		}  
	}
	if ($torain == 'mm') {
		$round = 0;
	} elseif ($torain == 'cm') {
		$round = 1;	
	} else {
		$round = 2;	
	}
	$return	= round($out*$amount,$round);
	if ($wsDebug) {
		echo '<!-- function noaaconvertrain: in = rainfall: '.$amount.' , unitFrom: '.$fromrain.' ,unitTo: '.$torain.', out = '.$return.' -->'.PHP_EOL;;
	}
	return $return;
} // eof convert rainfall
# --------------------------------------------------------------------------------------
#    Convert temperature and clean up input
# --------------------------------------------------------------------------------------
function noaaconvertemp($amount,$xx='',$yy='') {
	global $fromtemp, $totemp, $wsDebug;
	if (isset ($amount)) {
		$amount	= str_replace(',','.',$amount);
		$out 	= $amount*1.0;
	} else {
		$out	= 0;
	}
	$error 	= '';
	if ($fromtemp == $totemp) {return $amount;}
	elseif (($fromtemp == 'c') && ($totemp = 'f')) {$out = 32 +(9*$amount/5);}
	elseif (($fromtemp == 'f') && ($totemp = 'c')) {$out = 5*($amount -32)/9;}
	else { $error	= 'invalid UOM';}
	$return = round($out,1);
	if ($wsDebug || $error 	<> '') {
		if ($amount == '---') {$amount = '- - -';}
		echo "<!-- function noaaconvertemp in = temperature: $amount , unitFrom: $fromtemp ,unitTo: $totemp, out = $return -->\n";
		if ($error <> '') {echo "<!-- ========== $error ============== -->".PHP_EOL;}
	}
	return $return;
} // eof convert temperature
# --------------------------------------------------------------------------------------
#	noaacommontemperature
# --------------------------------------------------------------------------------------
function noaacommontemperature($value,$xx='',$yy=''){
	global $totemp, $tempArray2, $tempSimple;
	$color			= 'red';
	$temp			= round($value);
	if ($totemp == 'c') {								// for the color lookup we need C as unit
		$colorTemp	= $temp + 32;				// first color entry => -32 C
	} else {
		$colorTemp	= round( 5*($value-32)/9 ) + 32;		
	} 
	if (!$tempSimple) {
		if ($colorTemp < 0) {$colorTemp = 0;} elseif ($colorTemp >= count ($tempArray2) )  {$colorTemp = count ($tempArray2) - 1;}
		$color		= $tempArray2[$colorTemp];
		$tempString	= '<span class="myTemp" style="color: '.$color.';" >'.$temp.'&deg;</span>';	
	} else {
		if ($colorTemp <  32) { $color = 'blue'; } else {$color = 'red';}
		$tempString	= '<span class="myTemp" style="font-size: 150%; color: '.$color.';" >'.$temp.'&deg;</span>';	
	}
	return $tempString;
}
#-------------------------------------------------------------------------------------
#	returns Beaufort information based on windspeed 
#-------------------------------------------------------------------------------------
function noaabeaufort ($wind,$usedunit='') {
        global $wsDebug;
        $beaufort       = array();      // return array with nr - color - text
        $colors         = array(        // colors for beaufort numbers
	"transparent", "transparent", "transparent", "transparent", "transparent", "transparent", 
	"#FFFF53", "#F46E07", "#F00008", "#F36A6A", "#6D6F04", "#640071", "#650003"
	);	
	$texts          = array(        //  descriptive text for beaufirt scale numbers
	"Calm", "Light air", "Light breeze", "Gentle breeze", "Moderate breeze", "Fresh breeze",
	"Strong breeze", "Near gale", "Gale", "Strong gale", "Storm",
	"Violent storm", "Hurricane"
	);

        if ($usedunit <> '' && $usedunit <> 'kts') {            // convert windspeed
               $wind    = noaaconvertwind($wind,$usedunit,'kts');
        }
	$windkts = $wind * 1.0;
#
	switch (TRUE) {         	// return a number for the beaufort scale based on wind in knots
		case ($windkts < 1 ):
	 		$beuafortnr = 0;
	 		break;
		case ($windkts <	4 ):
			$beuafortnr = 1;
			break;
		case ($windkts <	7 ):
			$beuafortnr = 2;
			break;
		case ($windkts <  11 ):
			$beuafortnr = 3;
			break;
		case ($windkts <  17 ):
			$beuafortnr = 4;
			break;
		case ($windkts <  22 ):
			$beuafortnr = 5;
			break;
		case ($windkts <  28 ):
			$beuafortnr = 6;
			break;
		case ($windkts <  34 ):
			$beuafortnr = 7;
			break;
		case ($windkts <  41 ):
			$beuafortnr = 8;
			break;
		case ($windkts <  48 ):
			$beuafortnr = 9;
			break;
		case ($windkts <  56 ):
			$beuafortnr = 10;
			break;
		case ($windkts <  64 ):
			$beuafortnr = 11;
			break;
		default:
			$beuafortnr = 12;
			break;
	}  // eo switch
	$beaufort[]  = $beuafortnr;
	$beaufort[]  = $colors[$beuafortnr];
	$beaufort[]  = $texts[$beuafortnr];
	if ($wsDebug) {
		echo '<!-- function noaabeaufortnr in = winspeed: '.$wind.
		' , unitFrom: '.$usedunit.
		',  nr = '.$beaufort[0].' color ='.$beaufort[1].', text = '.$beaufort[2].' -->'.PHP_EOL;
	}	
	return $beaufort;
} // eof noaabeaufort
#----------------------------------------------------------------------not used yet --
function wsconvertnoaaicon ($icon) {
	global $arrLookupNoaa;
	$key = trim($icon);
	if (!isset ($arrLookupNoaa[$key]) ) {
	        $return = $arrLookupNoaa['901'];
	} else {$return = $arrLookupNoaa[$key];}
	echo '<!-- icon in: '.$icon.' - icon out: '.$return.' -->'.PHP_EOL;
	return $return;
}
#-------------------------------------------------------------------------------------
#  Language function
function wsnoaafcttransstr ($string) {
	global $trans,  $wsnoaafctLOOKUP, $missingTrans, $lower;	
	$value	= trim ($string);
	if (!isset ($wsnoaafctLOOKUP[$value]) ) {
		$return	= str_replace ($trans,'',$string);
		$missingTrans[$value]	= $return;	
	} else {
		$value	= $wsnoaafctLOOKUP[$value];
		$return	= $value;
	}
	if ($lower) {$return	= strtolower($return);}
	return $return;	
}
if (!function_exists ('ws_message') ) {
#-------------------------------------------------------------------------------------
#  message function
        function ws_message ($message,$always=false,&$string=false) {
                global $wsDebug, $SITE;
                $echo	= $always;
                if ( $echo == false && isset ($wsDebug) && $wsDebug == true ) 			{$echo = true;}
                if ( $echo == false && isset ($SIE['wsDebug']) && $SIE['wsDebug'] == true ) 	{$echo = true;}
                if ( $echo == true  && $string === false) {echo $message.PHP_EOL;}
                if ( $echo == true  && $string <>  false) {$string .= $message.PHP_EOL;}
        }
}