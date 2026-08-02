<?php
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
$pageName	= 'noaaDigitalGenerateHtml.php';
$pageVersion	= '3.00 2015-10-08';  # multi icon
$string		= $pageName.'- version: ' . $pageVersion;
$pageFile = basename(__FILE__);			// check to see this is the real script
if ($pageFile <> $pageName) {
	$string.= $pageFile .' loaded instead';
}
$echo           .=  '<!-- module '.$string.' -->'.PHP_EOL;
$myPageNoaa1    = $pageName;
#-------------------------------------------------------------------------------
# Display a list of forecast date from nws/noaa
#
#-------------------------------------------------------------------------------
# First get the data from the wuWeather class
$weatherDigital = new noaaDigitalWeather ();
$returnDigArray = $weatherDigital->getWeatherDataDigital($myLatitude,$myLongitude);
#
# The found weather fields are read from the cached file. 
# If you do not want a field to appear in the output set that indicator to false by removing the comment mark # on the corresponding line. 
$tempFound 	=	$returnDigArray['information']['tempFound'];	# 	temp field is essential and should always be in the input file
$dewpFound	=	$returnDigArray['information']['dewpFound'];	#	$dewpFound		=	false;
$appFound	= 	$returnDigArray['information']['appFound'];	#	$appFound		= 	false;
$windFound	=	$returnDigArray['information']['windFound'];	#	$windFound		=	false;
$gustFound	=	$returnDigArray['information']['gustFound'];	#	$gustFound		=	false;
$winddirFound	=	$returnDigArray['information']['winddirFound'];	#	$winddirFound	        =	false;
$cloudsFound	=	$returnDigArray['information']['cloudsFound'];	#	$cloudsFound	        =	false;
$humFound	=	$returnDigArray['information']['humFound'];	#	$humFound		=	false;
$txtFound	=	$returnDigArray['information']['txtFound'];	#	$txtFound		=	false;
$rainFound	=	$returnDigArray['information']['rainFound'];	#	$rainFound		=	false;
$snowFound	=	$returnDigArray['information']['snowFound'];	#	$snowFound		=	false;
$popFound	=	$returnDigArray['information']['popFound'];	#	$popFound		=	false;
#
#First test is essential information is there
if (!$tempFound) {echo '<h3> Essential data like temperature is mssing - program stops </h3>'; return;}
#-------------------------------------------------------------------------------
#
$itRained	= 0;
#
# informative text with update times and name of forecast area
# --------------
# text for top of page time/date of updates
$fileTime	= $returnDigArray['information']['fileTimeStamp'];
$nextUpdate 	= $returnDigArray['information']['cacheTimestamp'];
$wsUpdateTimes  = '
<div style="text-align: left; margin:  0px 10px 0px 10px;">
<span style="float:right;text-align:right;">';
$wsUpdateTimes .= wsnoaafcttransstr('Updated').': '.date ($dateLongFormat,$fileTime).' - '.date ($timeFormat,$fileTime).'<br />';
$wsUpdateTimes .= wsnoaafcttransstr('Next update').': '.date ($dateLongFormat,$nextUpdate).' - '.date ($timeFormat,$nextUpdate).'
</span>
<h4 style="margin: 0px;">'.wsnoaafcttransstr('noaa forecast').' '.wsnoaafcttransstr($myArea).'
<br />'.wsnoaafcttransstr('by: ').$myStation.'</h4>';
$wsUpdateTimes .= '</div>';
#
$location = '';
if (isset ($returnArray['information']['location'] ) ) {$location =  $returnArray['information']['location'];}
$creditString= '
<div style="width: 100%;">
<table style="width: 100%;">
<tr>
<td>'.$logoNWS.'</td>
<td><small>Meteogram and script developed by <a target="_blank" href="http://www.weerstation-leuven.be"> WeerstationLeuven</a>.&nbsp;&nbsp;
Graphs are drawn using <a target="_blank"  href="http://www.highcharts.com">Highcharts</a><br />
Weather <a target="new" href="http://graphical.weather.gov/xml/SOAP_server/ndfdXML.htm">forecast</a> from the NDFD SOAP - National Digital Forecast Database, 
delivered by the National Weather Service.<br />
Data retrieved for '.$location.$returnDigArray['information']['location'].'.  Data was generated at '.$returnDigArray['information']['fileTime'].'</small></td>
</tr>
</table>
</div>';
#
# headers for the table and count number of columns in table
$wsFcstTableHdr 	= '
<tr class="table-top">
<td>'.wsnoaafcttransstr('Period').'</td><td>&nbsp;</td><td>'.wsnoaafcttransstr('Forecast').'</td>
<td>'.wsnoaafcttransstr('Temperature').'</td>';
$cntFcstTableCols	= 4;
if ($rainFound) {
	$cntFcstTableCols++;
	$wsFcstTableHdr 	.= '
<td>'.wsnoaafcttransstr('Precipitation').'</td>';
}
if ($windFound || $gustFound) {
	$cntFcstTableCols++;
	$wsFcstTableHdr 	.= '
<td>'.wsnoaafcttransstr('Wind').'</td>';
}
if ($humFound) {
	$cntFcstTableCols++;
	$wsFcstTableHdr 	.= '
<td>'.wsnoaafcttransstr('Humidity').'</td>';
}
$wsFcstTableHdr 	.= '
</tr>'.PHP_EOL;

$wsFcstTable	= '<table class="genericTable" style="width: 100%;"><tbody>'.PHP_EOL;

$graphsData	= '';
$graphsDays 	= array ();
$graphsStop 	= 0;
$graphsStart	= 0;
$graphTempMin 	= 100;
$graphTempMax 	= -100;
$graphRainMax 	= 0;
$graphSnowMax 	= 0;
$graphWindMax 	= 0;
$oldDay  	= '';
$dataLine	= false;
# now we generate the html for graphs and tables
#
$endFcst	= count ($returnDigArray['forecast']);
for ($i = 0; $i < $endFcst; $i++) {
	$arr	= $returnDigArray['forecast'][$i];
	$strDay	= date('Y-m-d',$arr['toStamp']); 
	if ($oldDay <> $strDay) {		// do we have a new day
		$oldDay  	= $strDay;
		$graphsDays[]	= 1000 * strtotime($strDay.'T00:00:00Z');
		$dataLine	= true;
	} 
	if ($graphsStart == 0) { $graphsStart    = 1000 * $arr['fromStamp'];}
	$graphsStop	= 1000 * $arr['toStamp'];
# check min max values for graphs. 
# also add '-' for missing data in data file for graphs
	if ($tempFound) {
		if ($arr['tempNU'] 	< $graphTempMin) {$graphTempMin = $arr['tempNU'];}
		if ($arr['tempNU'] 	> $graphTempMax) {$graphTempMax = $arr['tempNU'];}
	}	else {$arr['tempNU'] = '-';}
	if ($dewpFound) {
		if ($arr['dewpNU'] 	< $graphTempMin) {$graphTempMin = $arr['dewpNU'];}
		if ($arr['dewpNU'] 	> $graphTempMax) {$graphTempMax = $arr['dewpNU'];}
	}	else {$arr['dewpNU'] = '-';}
	if ($appFound) {
		if ($arr['tempAppNU'] 	< $graphTempMin) {$graphTempMin = $arr['tempAppNU'];}
		if ($arr['tempAppNU'] 	> $graphTempMax) {$graphTempMax = $arr['tempAppNU'];}
	}	else {$arr['tempAppNU'] = '-';}
	if ($windFound) {
		if ($arr['windSpeedNU'] > $graphWindMax) {$graphWindMax = $arr['windSpeedNU'];}
	}	else {$arr['windSpeedNU'] = '-';}
	if ($gustFound) {
		if ($arr['windGustNU']	> $graphWindMax) {$graphWindMax = $arr['windGustNU'];}
	}	else {$arr['windGustNU']       = '-';}
	if ($rainFound) {
		if ($arr['liquidNU']	> $graphRainMax) {$graphRainMax = $arr['liquidNU'];}
	}	else {$arr['liquidNU']  = '-';}
	if ($snowFound) {
		if ($arr['snowNU']		> $graphSnowMax) {$graphSnowMax = $arr['snowNU'];}
	}	else {$arr['snowNU'] = '-';}
	if (!$popFound) {$arr['pop'] = '-';}
#
	if ($noaaIconsOwn) {
		$iconUrl        = $arr['noaaIconSmlUrl'];
	} else {
		$iconUrl	= $arr['defailtIconSmlUrl'];
	}
	$time	        = $arr['toStamp'] + $utcDiff;
	$graphsData	.= 'tsv['.$i.'] ="'.
		$time.'|'.
		$arr['tempNU'].'|'.		
		1.0*$arr['tempAppNU'].'|'.
		$arr['dewpNU'].'|'.
		$arr['windSpeedNU'].'|'.
		$arr['windGustNU'].'|'.
		$arr['windDirDeg'].'|'.
		$arr['windLbl'].'|'.
		$arr['hum'].'|'.
		$arr['snowNU'].'|'.
		$arr['liquidNU'].'|'.
		$iconUrl.'|'.
		'";'.PHP_EOL;
#
	if ($dataLine == true) {
		$tableColoms    = $cntFcstTableCols;
		$wsFcstTable    .= myDateLinePrint($arr['toStamp'], $rowColor);
		$wsFcstTable    .= $wsFcstTableHdr;
		$dataLine       = false;
	}
	$wsFcstTable    .= '<tr class="'.$rowColor.'">'.PHP_EOL;
	if ($rowColor   == 'row-dark') {$rowColor = 'row-light';} else {$rowColor =  'row-dark';}
# period
	$to	        = date($hourOnlyFormat,$arr['toStamp']);
	if (!isset ($startNext) ) {
	        $start          = date($hourOnlyFormat,($arr['toStamp'] - 3* 60 *60));
	} else {
	        $start          = $startNext;
	}
	$period         = $start .'&nbsp;-&nbsp;'. $to;
	$startNext      = $to;
	$wsFcstTable    .= '<td>'.$period.'</td>';
# forecast icon and text
	if ($noaaIconsOwn) {
		$iconUrl        = $arr['noaaIconUrl'];
	} else {
		$iconUrl	= $arr['defailtIconUrl'];
	}
#
	$textString	= '';
	if ($txtFound) {
		$textString = $arr['weatherTxt'];
		if ($textString == '') {$textString = wsnoaafcttransstr('Clear');}
	}
	$wsFcstTable    .= '<td><img src="'.$iconUrl.'" alt ="icon" style="width:40px;" title="icon"/></td>';
	$wsFcstTable    .= '<td>'.$textString.'</td>';
#	normal temperature
	$temp 		= $arr['tempNU'];
	$tempString     = noaacommontemperature($temp);
#	app temperature	
	if ($appFound && $arr['tempAppNU'] <> '') {
		$diff = abs($arr['tempNU'] - $arr['tempAppNU']);
		if ($diff >= $appTempDiff) {
			$tempString .= '<br /><small>'.wsnoaafcttransstr('Feels like').' '.round($arr['tempAppNU']).$uomTemp.'</small>';
		}
	}
	$wsFcstTable  .= '<td>'.$tempString.'</td>';
# precipitation rain and or snow ??
	$rainString = $popString = '';
	if ($rainFound && (1.0 * $arr['liquidNU']) > 0) {
		$rainString .= $arr['liquid'].'<br />'.wsnoaafcttransstr('for next 6 hours');
		$itRained	= 2;
	}
	if ($popFound && $arr['pop'] <> '' && $arr['pop'] <> 0) {
		if ($arr['pop'] > $minPoP) {
			$popString .= '<small>'.wsnoaafcttransstr('PoP').'<br />'.$arr['pop'].'%</small>';
		}
	}
	if ($itRained == 2) { 
		$wsFcstTable  .= '<td>'.$rainString.'<br />'.$popString.'</td>';
		$itRained	= 1;
	} elseif ($itRained == 1) { 
		$wsFcstTable  .= '<td>'.$popString.'</td>';
		$itRained = 0;
	} else {
		$wsFcstTable  .= '<td>'.$popString.'</td>';
	}
#
	$windString		= '';
	if ($windFound) {
		$windText	='<span style="background-color: '.$arr['beaufortclr'].';">'.$arr['windSpeed'].' - '.$arr['beauforttxt'].'</span>';
		$windString	= $windText.'<br />'.wsnoaafcttransstr ('from the').' '.wsnoaafcttransstr($arr['windDirRose']);
	}
	if ($gustFound ) {
		$gust	= $arr['windGustNU'];
		if ($gust <> '-' && 1.0*$gust > 0) {
			$windString .= '<br />'.wsnoaafcttransstr('gusts up to').' '.$arr['windGust'];
		}
	}
	$wsFcstTable  .= '<td>'.$windString.'</td>';
#
	if ($humFound ) {$wsFcstTable  .= '<td>'.$arr['hum'].'</td>';}
	$wsFcstTable  .= '</tr>'.PHP_EOL;
}  // eo loop through forecasts

# now we are going to generate the javascript graphs
# create array for javascript lookup from short dayname to (translated) long daynames 
$graphDaysString = '{';
$daysShort	= array ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
$daysLong	= array ('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
for ($i = 0; $i < count ($daysShort); $i++) {
	$graphDaysString .= "'$daysShort[$i]':'".wsnoaafcttransstr($daysLong[$i])."',";
}
$graphDaysString = substr($graphDaysString, 0, -1);  // get rid of the last ,
$graphDaysString .= '}';
#
$ddays		= '';
$endDays	= count($graphsDays);	// is filled with all weekdays found in the forecast
# we want a shaded background in the graphs every other day
for($i=0 ; $i < $endDays; $i++) { //  shaded background every other day
	if($i ==  $endDays - 1) {     // last incomplete day
		$ddays.= '{ from: '.$graphsDays[$i].', to: '.($graphsStop).', color: "rgba(255, 255, 255, 0.9)" },';
	} else {
		$ddays.= '{ from: '.$graphsDays[$i].', to: '.$graphsDays[$i+1].', color: "rgba(255, 255, 255, 0.9)" },';
	}
	$i++;		// skip next day
}
$ddays	= substr($ddays, 0, -1); // get rid of the last ,
# color for above freezing and below freezing temp on yAxis
$negValue = "return '<span style=\"fill: blue;\">' + this.value + '</span>'";
$posValue = "return '<span style=\"fill: red;\">' + this.value + '</span>'";
# set the value for this color change
if ($totemp == 'f') {$treshold = 32;} else {$treshold = 0;}
#
# calculate Y axis steps for graphs
$graphNrLines	= 6;
$graphTempMin	= $tempMin = floor ($graphTempMin);  	// round down
$graphTempMax	= ceil 	($graphTempMax);  				// round up

$stringY = '<!-- temp max: '.$graphTempMax. ' temp min: '.$graphTempMin;

$graphTempStep	= 2* ceil(($graphTempMax - $graphTempMin) / $graphNrLines);

$stringY .= ' temp step: '.$graphTempStep;

$graphTempMax	= $graphTempStep * ceil($graphTempMax/$graphTempStep);
$tempMax		= $graphTempMax;
$tempMin		= $tempMin - $graphTempStep;
$graphTempMax	= $graphTempMax	+  $graphTempStep;
$graphTempMin   = $graphTempMax - (1+ $graphNrLines) * $graphTempStep;

$stringY .= '  temp max: '.$graphTempMax.' temp min: '.$graphTempMin;

# weather icons are place inside the plotting area just above the highest graphs
$graphIconYvalue = $graphTempMax - ($graphTempStep/2);

$stringY .= ' icon: '.$graphIconYvalue. ' -->'.PHP_EOL;
#
$rainMax		=  $graphRainMax;
if (preg_match("|mm|",$uomRain)) {
	if ($graphRainMax < 3.5) {$graphRainMax = 3.5;}
	$graphRainStep	= round (($graphRainMax / $graphNrLines),0);
	$graphRainMax	=  $graphRainStep * $graphNrLines;
} else {
#	if ($graphRainMax < 1.3) {$graphRainMax = 14;} else {$graphRainMax = 10 * $graphRainMax;}
	$graphRainStep	= (ceil (10*$graphRainMax / $graphNrLines))/ 10;
	$graphRainMax	= $graphRainStep * $graphNrLines;	
}
$graphRainMax	= $graphRainMax	* 2;
$graphRainStep	= $graphRainStep * 2;
$rainMax		= $rainMax + $graphRainStep;
$stringY .= '<!-- rain max: '.$graphRainMax.'   rain step: '.$graphRainStep.' -->'.PHP_EOL;
#
$snowMax		=  $graphSnowMax;
if (preg_match("|cm|",$uomSnow)) {
	if ($graphSnowMax < 3.5) {$graphSnowMax = 3.5;}
	$graphSnowStep	= round (($graphSnowMax / $graphNrLines),0);
	$graphSnowMax	=  $graphSnowStep * $graphNrLines;
} else {
	if ($graphSnowMax < 1.3) {$graphSnowMax = 14;} else {$graphSnowMax = 10 * $graphSnowMax;}
	$graphSnowStep	= (ceil ($graphSnowMax / $graphNrLines))/ 10;
	$graphSnowMax	= $graphSnowStep * $graphNrLines;	
}
$graphSnowMax	= $graphSnowMax	* 2;
$graphSnowStep	= $graphSnowStep * 2;
$snowMax		= $snowMax + $graphSnowStep;
$stringY .= '<!-- snow max: '.$graphSnowMax.'   snow step: '.$graphSnowStep.' -->'.PHP_EOL;
#
if ($graphWindMax < $graphNrLines) {$graphWindMax = $graphNrLines;}
$graphWindStep	= ceil ($graphWindMax / $graphNrLines);
$graphWindMax	= $graphNrLines * $graphWindStep;
$windMax		= $graphWindMax;
$graphWindMax	= $graphWindMax	* 2;
$graphWindStep	= $graphWindStep * 2;
$stringY .='<!-- wind max: '.$graphWindMax.' wind step: '.$graphWindStep.'-->'.PHP_EOL;
echo $stringY;


$graphPart1	= '
<script type="text/javascript">
<!--
var days        = '.$graphDaysString.';

var globalX = [{
	type: "datetime",
	min: '.$graphsStart.',
	max: '.$graphsStop.',
	plotBands: ['.$ddays.'],
	title: {text: null},
	dateTimeLabelFormats: {day: "%H",hour: "%H"},	
	tickInterval: 6 * 3600 * 1000,	
	gridLineWidth: 0.4,      
	lineWidth: 0,
	labels: {y: 20,style:{fontWeight: \'normal\',fontSize:\'10px\'},
		formatter: function() { 
			var uh = Highcharts.dateFormat("%H", this.value);
			if(uh=="12"){return Highcharts.dateFormat("%H <br />", this.value) + days[Highcharts.dateFormat("%a", this.value)];}
			else{return Highcharts.dateFormat("%H", this.value);}
		}
	}
}];
-->
</script>
';
$graphPart1 .='
<script type="text/javascript">
<!--
var tsv = [];
'.$graphsData.'
var 
	temps	= [], dewps	= [], apts	= [],
	wsps	= [], gusts = [],
	wdirs 	= [], wlbls = [],
	hums 	= [],
	rains	= [], snows	= [],
	icons 	= [];
for (j = 0; j < tsv.length; j++) {
	var line =[];
	line = tsv[j].split("|");
	if(line[0].length > 0 && parseInt(line[0]) != "undefined"){
		date = 1000 * parseInt(line[0]);
		temps.push([date, parseFloat(line[1]) ]);
		dewps.push([date, parseFloat(line[3]) ]);
		apts.push ([date, parseFloat(line[2]) ]);
		mkr = "'.$myWindIconsSmall.'" +line[7]+".png";
		str = {x:date,y:parseFloat(line[4]), marker:{symbol:\'url(\'+mkr+\')\'}};
		wsps.push(str);
		if (line[5] != \'\') {
			gusts.push([date, parseFloat(line[5])]);
		}
		wdirs.push([date, parseFloat(line[6])]);
		hums.push ([date, parseFloat(line[8])]);
		if (line[9] != \'-\') {
			snows.push([date, parseFloat(line[9])]);
		}
		if (line[10] != \'-\') {
			rains.push([date, parseFloat(line[10])]);
		}
		mkr = line[11];
		str = {x:date,y:'.$graphIconYvalue.', marker:{symbol:\'url(\'+mkr+\')\'}};
		icons.push(str); 
    } // Line contains correct data           
}; // eo for each tsv

var yTitles 	= {color: "#000000", fontWeight: "bold", fontSize:"10px"};
var yLabels 	= {color: "#4572A7", fontWeight: "bold", fontSize:"8px"};
var yLabelsWind = {color: "#1485DC", fontWeight: "bold", fontSize:"8px"};

$(document).ready(function() {
	Highcharts.setOptions({
		chart: {
		    spacingTop:4,
			renderTo: "placeholder",
			defaultSeriesType: "spline",
			backgroundColor: "rgba(255, 255, 255, 0.4)",
			plotBackgroundColor: {linearGradient: [0, 0, 0, 150],stops: [[0, "#ddd"],[1, "rgba(255, 255, 255, 0.4)"]]},
			plotBorderColor: "#88BCCE",
			plotBorderWidth: 0.5,
			marginRight: 60,
			marginTop: 30,
			marginLeft: 60,
			style: {fontFamily: \'"UbuntuM","Lucida Grande",Verdana,Helvetica,sans-serif\',fontSize:\'11px\'}
		},
		title: {text: ""},
		xAxis: globalX,
		lang: {thousandsSep: ""},
		credits: {enabled: false},
		plotOptions: {
			series: {marker: { radius: 0,states: {hover: {enabled: true}}}},
			spline: {lineWidth: 1.5, shadow: false, cursor: "pointer",states:{hover:{enabled: false}}},
			column: {pointWidth:15},
			areaspline: {lineWidth: 1.5, shadow: false,states:{hover:{enabled: false}}}
		},
		legend: { borderWidth: 0, align: \'center\', verticalAlign: \'top\', rtl: true},
		exporting: {enabled:false},
		tooltip: {
            positioner: function () {return { x: 0, y:0};},
			backgroundColor: "#A2D959",
         	borderColor: "#fff",
         	borderRadius: 3,
         	borderWidth: 0,  
         	shared: true,
         	crosshairs: { width: 0.5,color: "#666"},
         	style: {lineHeight: "1.3em",fontSize: "11px",color: "#000"},
         	formatter: function() {
              var head = "" + days[Highcharts.dateFormat(\'%a\', this.x)]+" "+ Highcharts.dateFormat(\'%H:%M\', this.x) +"<br />----------------";
              var s = "";
              $.each(this.points, function(i, point) {
               if (point.series.name != " ") {
				var unit = {
				   "'.wsnoaafcttransstr('Precipation').'": " '.$torain.'",
				   "'.wsnoaafcttransstr('Wind').'": " '.$towind.'",
				   "'.wsnoaafcttransstr('Gust').'": " '.$towind.'",
				   "'.wsnoaafcttransstr('Humidity').'": " %",
				   " ": "",
				   "'.wsnoaafcttransstr('Feels like').'": " '.$degree.ucfirst($totemp).'",
				   "'.wsnoaafcttransstr('Temperature').'": " '.$degree.ucfirst($totemp).'"
				}[point.series.name];
				
				s = "<br/>"+point.series.name+": <b>"+point.y+unit+"</b>" + s;
			  }			     
            });  // eo each
            
            return head+s;
         }
      }
	});  // eo set general options
   chartTempData  = new Highcharts.Chart({
        chart: { renderTo: "containerTemp" },		
      	yAxis: [
      	{ lineWidth: 2, 
          gridLineWidth: 0.4, min: '.$graphTempMin.',max:'.$graphTempMax.',tickInterval:'.$graphTempStep.', offset: 25,
          title: {text: "'.$degree.ucfirst($totemp).'", rotation: 0, align:"high", offset: 4, y: 0, style:yTitles},
          labels: {x: -4, y: 1, formatter: function() {if (this.value < '.$tempMin.' || this.value > '.$tempMax.' ){ return ""; } 
          else
          {if (this.value < 0) {'.$negValue.';} else {'.$posValue.';}}
          },style:yLabels}       
      	}';
if ($rainFound) {
	$graphPart1 .=',
      	{ 
          gridLineWidth: 0, min: 0,max:'.$graphRainMax.',tickInterval:'.$graphRainStep.', offset: 0,
          title: {text: "'.$torain.'", rotation: 0, align:"low", offset: 0,x: -30, y: 15, style:yTitles},
          labels: {align: "left", x: -20, y: 1,  formatter: function() {if (this.value < 0 || this.value > '.$rainMax.' ){ return ""; } else {return this.value;}},style:yLabels}
      	}';
}
if ($windFound || $gustFound) {
	$graphPart1 .=',
      	{ 
          gridLineWidth: 0, min: 0, max: '.$graphWindMax.', tickInterval: '.$graphWindStep.', opposite: true,
          title: {text: "'.$towind.'", rotation:0, align:"low", offset: 0,x: 0, y: 15, style:yTitles},     
          labels: {align: "right",x: 20, y: 1, formatter: function() {if (this.value < 0 || this.value > '.$windMax.' ){ return ""; } else {return this.value;}},style:yLabelsWind}      
      	}';
}
if ($humFound) {
	$graphPart1 .=',
      	{ lineWidth: 2,
          gridLineWidth: 0.4, min: -20, max: 100, tickInterval: 20, opposite: true,
          title: {text: "%", rotation:0, align:"high",  offset: 4, x: 10,  y: 0, style:yTitles},     
          labels: {x: 4, y: 1, formatter: function() {if (this.value < 0 || this.value > 100 ){ return ""; } else {return this.value;}},style:yLabels}      
      	}';
}
$graphPart1 .='      	
       	],
      	series: [';
if ($humFound) {
	$graphPart1 .='
      		{name: "'.wsnoaafcttransstr('Humidity').'", 	data: hums,  	color:"#178A3D",	yAxis:3, dashStyle:"Dot"}, ';
}
if ($rainFound) {
	$graphPart1 .=' 
      		{name: "'.wsnoaafcttransstr('Precipation').'",	data: rains,	color:"#4572A7",	yAxis:1, type:"column"},';
}
if ($windFound) {
	$graphPart1 .='
      		{name: "'.wsnoaafcttransstr('Wind').'", 	data: wsps,	color:"#EEEE00",	yAxis:2, marker:{radius:2,symbol:"circle"}},';
}
if ($gustFound) {
$graphPart1 .='
      		{name: "'.wsnoaafcttransstr('Gust').'", 	data: gusts,  	color:"#FFB90F",	yAxis:2, marker:{radius:2,symbol:"circle"}},';
}
if ($appFound) {
	$graphPart1 .='
 		{name: "'.wsnoaafcttransstr('Feels like').'",	data: apts,	color:"#EE4643", 	dashStyle:"Dot"}, ';
}
$graphPart1 .='  		
      		{name: "'.wsnoaafcttransstr('Temperature').'",	data: temps,	color:"#EE4643", 	threshold: '.$treshold.', negativeColor: "#4572EE"},
      		{name: " ",					data:icons, 	color:"transparent",type:"",events:{legendItemClick:false} }
      	]
        });  // eo chart    
}); // eo document ready
';

$graphPart1 .='-->
</script>'.PHP_EOL;	
$wsFcstTable.= '
</tbody></table>'.PHP_EOL;
# ------------------------------------------------------------------
# myDateLinePrint
#-------------------------------------------------------------------------------
function myDateLinePrint($time, &$rowColor) {
	global  $myLatitude, $myLongitude, $dateLongFormat, $timeFormat, $srise, $sset, $tableColoms, $myImgDir; 
	$srise 	= date_sunrise($time, SUNFUNCS_RET_TIMESTAMP, $myLatitude, $myLongitude);   // standard time integer
	$sset 	= date_sunset ($time, SUNFUNCS_RET_TIMESTAMP, $myLatitude, $myLongitude);
	$dlength= $sset - $srise;
	$dlengthHr = floor ($dlength /3600);
	$dlengthMin = round (($dlength - (3600 * $dlengthHr) ) / 60);
	$strDayLength = $dlengthHr.':'. substr('00'.$dlengthMin,-2);
	$longDate = date ($dateLongFormat,$time);
	$string	='<tr style="background-color: grey; height: 1px;"><td style="height: 0px;" colspan="'.$tableColoms.'"></td></tr>'.PHP_EOL;
	$string.='<tr class="dateline table-top"><td colspan="'.$tableColoms.'">
<span style="float:left; position:relative;">&nbsp;<b>'.$longDate.'</b></span>
<span style="float:right;position:relative;">
	<span class="rTxt">
		<img src="'.$myImgDir.'/sunrise.png" style="width: 24px; height: 12px;" alt="sunrise" />&nbsp;&nbsp;'.date($timeFormat,$srise).'&nbsp;&nbsp;
		<img src="'.$myImgDir.'/sunset.png"  style="width: 24px; height: 12px;" alt="sunset" />&nbsp;&nbsp;'.date($timeFormat,$sset).'&nbsp;&nbsp;&nbsp;'.
		wsnoaafcttransstr('Daylength').': '.$strDayLength.'&nbsp;
	</span>
</span>
</td></tr>'.PHP_EOL;
	$string	.='<tr style="background-color: grey; height: 1px;"><td style="height: 0px;" colspan="'.$tableColoms.'"></td></tr>'.PHP_EOL;
	$rowColor = 'row-light';	
	return $string;
}
#--------------------------------------------------------------------------------------------------
# retrieve weather infor from weathersource  
# and return array with retrieved data in the desired language and units C/F
#--------------------------------------------------------------------------------------------------
class noaaDigitalWeather{
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
	private $cacheTime 	= 7200; 	// Cache expiration time Default: 7200 seconds = 2Hour
	private $cacheFile	= 'xxx';
	private $apiUrlpart 	= array(	// http://forecast.weather.gov/MapClick.php?lat=41.3&lon=-72.78&FcstType=dwml
/*
	# http://graphical.weather.gov/xml/SOAP_server/ndfdXML.htm
	# http://graphical.weather.gov/xml/SOAP_server/ndfdXMLclient.php?
whichClient=NDFDgen
&lat=38.99
&lon=-77.01
&listLatLon=&lat1=&lon1=&lat2=&lon2=&resolutionSub=&listLat1=&listLon1=&listLat2=&listLon2=&resolutionList=&endPoint1Lat=&endPoint1Lon=&endPoint2Lat=&endPoint2Lon=&listEndPoint1Lat=&listEndPoint1Lon=&listEndPoint2Lat=&listEndPoint2Lon=&zipCodeList=&listZipCodeList=&centerPointLat=&centerPointLon=&distanceLat=&distanceLon=&resolutionSquare=&listCenterPointLat=&listCenterPointLon=&listDistanceLat=&listDistanceLon=&listResolutionSquare=&citiesLevel=&listCitiesLevel=&sector=&gmlListLatLon=&featureType=&requestedTime=&startTime=&endTime=&compType=&propertyName=&product=time-series
&begin=2004-01-01T00%3A00%3A00&end=2017-06-14T00%3A00%3A00
&Unit=e
&maxt=maxt
&mint=mint
&temp=temp
&qpf=qpf
&pop12=pop12
&snow=snow
&dew=dew
&wspd=wspd
&wdir=wdir
&sky=sky
&wx=wx
&icons=icons
&rh=rh
&critfireo=critfireo
&conhazo=conhazo
&wwa=wwa
&wgust=wgust
&Submit=Submit

&listLatLon=&lat1=&lon1=&lat2=&lon2=&resolutionSub=&listLat1=&listLon1=&listLat2=&listLon2=&resolutionList=&endPoint1Lat=&endPoint1Lon=&endPoint2Lat=&endPoint2Lon=&listEndPoint1Lat=&listEndPoint1Lon=&listEndPoint2Lat=&listEndPoint2Lon=&zipCodeList=&listZipCodeList=&centerPointLat=&centerPointLon=&distanceLat=&distanceLon=&resolutionSquare=&listCenterPointLat=&listCenterPointLon=&listDistanceLat=&listDistanceLon=&listResolutionSquare=&citiesLevel=&listCitiesLevel=&sector=&gmlListLatLon=&featureType=&requestedTime=&startTime=&endTime=&compType=&propertyName=&product=time-series&begin=2004-01-01T00%3A00%3A00&end=2017-06-16T00%3A00%3A00&Unit=e&temp=temp&qpf=qpf&pop12=pop12&snow=snow&dew=dew&wspd=wspd&wdir=wdir&sky=sky&wx=wx&icons=icons&rh=rh&appt=appt&ptotsvrtstm=ptotsvrtstm&wwa=wwa&wgust=wgust&Submit=Submit
*/
	 0 => 'https://graphical.weather.gov/xml/SOAP_server/ndfdXMLclient.php?whichClient=NDFDgen&lat=',
	 1 => 'userinputLatitude',
	 2 => '&lon=',
	 3 => 'userinputLatitude',
	 4 => '&listLatLon=&lat1=&lon1=&lat2=&lon2=&resolutionSub=&listLat1=&listLon1=&listLat2=&listLon2=&resolutionList=&endPoint1Lat=&endPoint1Lon=&endPoint2Lat=&endPoint2Lon=&listEndPoint1Lat=&listEndPoint1Lon=&listEndPoint2Lat=&listEndPoint2Lon=&zipCodeList=&listZipCodeList=&centerPointLat=&centerPointLon=&distanceLat=&distanceLon=&resolutionSquare=&listCenterPointLat=&listCenterPointLon=&listDistanceLat=&listDistanceLon=&listResolutionSquare=&citiesLevel=&listCitiesLevel=&sector=&gmlListLatLon=&featureType=&requestedTime=&startTime=&endTime=&compType=&propertyName=&product=time-series&Unit=e&temp=temp&qpf=qpf&pop12=pop12&snow=snow&dew=dew&wspd=wspd&wdir=wdir&sky=sky&wx=wx&icons=icons&rh=rh&appt=appt&ptotsvrtstm=ptotsvrtstm&wwa=wwa&wgust=wgust&Submit=Submit',
	);
	private $weatherApiUrl	= '';
	private $rawData	= '';
# 	 4 => '&listLatLon=&lat1=&lon1=&lat2=&lon2=&resolutionSub=&listLat1=&listLon1=&listLat2=&listLon2=&resolutionList=&endPoint1Lat=&endPoint1Lon=&endPoint2Lat=&endPoint2Lon=&listEndPoint1Lat=&listEndPoint1Lon=&listEndPoint2Lat=&listEndPoint2Lon=&zipCodeList=&listZipCodeList=&centerPointLat=&centerPointLon=&distanceLat=&distanceLon=&resolutionSquare=&listCenterPointLat=&listCenterPointLon=&listDistanceLat=&listDistanceLon=&listResolutionSquare=&citiesLevel=&listCitiesLevel=&sector=&gmlListLatLon=&featureType=&requestedTime=&startTime=&endTime=&compType=&propertyName=&product=time-series&begin=2004-01-01T00%3A00%3A00&end=2017-06-16T00%3A00%3A00&Unit=e&temp=temp&qpf=qpf&pop12=pop12&snow=snow&dew=dew&wspd=wspd&wdir=wdir&sky=sky&wx=wx&icons=icons&rh=rh&appt=appt&ptotsvrtstm=ptotsvrtstm&wwa=wwa&wgust=wgust&Submit=Submit',
#--------------------------------------------------------------------------------------------------
# public functions	
#--------------------------------------------------------------------------------------------------
	public function getWeatherDataDigital($lat = '', $lon = '') {
		global $myPageNoaa1,
		$myCacheDir, $dateLongFormat, $timeFormat, $myDefaultIconsDir  , $myDefaultIconsExt  , $myDefaultIconsSml,
		$myNoaaIconsDir, $myNoaaIconsExt  , $myNoaaIconsSml,
		$uomTemp  , $uomWind  , $uomRain  , $uomSnow  , $uomDistance 
		 ;
		#----------------------------------------------------------------------------------------------
		# clean user input
		#----------------------------------------------------------------------------------------------
		$this->apiUrlpart[1] = round(trim($lat),3);
		$this->apiUrlpart[3] = round(trim($lon),3);
		#----------------------------------------------------------------------------------------------
		# try loading data from cache
		#----------------------------------------------------------------------------------------------		
		if ( $this->enableCache && !empty($this->cache) ){
			$this->cache	= $myCacheDir;
			$uoms		= $uomTemp.'-'.$uomWind.'-'.$uomRain.'-'.$uomSnow.'-'.$uomDistance;
			$string		= $myPageNoaa1.$this->apiUrlpart[1].$this->apiUrlpart[3] .$uoms;			
			$from		= array('&deg;','∞','/',' ','.',);
			$string		= str_replace($from,'',$string);
			$this->cacheFile= $this->cache .$string.'.txt';
			$returnArray	= $this->loadFromCache();	// load from cache returns data only when its data is valid
			if (!empty($returnArray)) {
				return $returnArray;					// if data is in cache and valid return data to calling program
			}	// eo valid data, return to calling program
		}  		// eo check cache
		#----------------------------------------------------------------------------------------------
		# combine everything into required url
		#  http://forecast.weather.gov/MapClick.php?lat=41.3&lon=-72.78&FcstType=dwml
		#----------------------------------------------------------------------------------------------
		$this->weatherApiUrl = '';
		$end	= count($this->apiUrlpart);
		for ($i = 0; $i < $end; $i++){
			$this->weatherApiUrl .= $this->apiUrlpart[$i];
		}
		#----------------------------------------------------------------------------------------------
		echo "<!-- Weatherforecast loading from  $this->weatherApiUrl   -->".PHP_EOL;		
		if ($this->makeRequest()) {  	// load xml from url and process
			$this->rawData = str_replace ('-name','_name',$this->rawData);
			$xml = new SimpleXMLElement($this->rawData);
			if (!isset ($xml->head->product->{'creation-date'}) ) {
				$string = substr($this->rawData,0,100);
				echo '<h3>Invalid data loaded for '.$this->weatherApiUrl.'</h3>'.PHP_EOL.'First 100 chars of data loaded = '.$string.PHP_EOL; return;
			}
			$returnArray    = array();
			$utcDiff 	= date('Z');                    // to help to correct utc differences
			$string		= (string) $xml->head->product->{'creation-date'};
			$time		= strtotime($string);
			$returnArray['information']['fileTimeStamp']    = strtotime($string);
			$returnArray['information']['fileTime']		= date('c', strtotime($string ) );
			$returnArray['information']['updated']		= date($dateLongFormat, $time).' '.date($timeFormat, $time);			
			$returnArray['information']['location']		= (string) $xml->data->location->description.'-'.
			                                        	  (string) $xml->data->location->point['latitude'].'-'.
                                                                          (string) $xml->data->location->point['longitude'];			
			$returnArray['information']['issued']		= (string) $xml->head->source->{'production-center'};			
#--------------------------------------------------------------------------------------------------
#  get forecast info
#--------------------------------------------------------------------------------------------------
			$arrTimes	= array();
			$endData	= count ($xml->data);
			$nData	        = 0;
			$endLayouts	= count ($xml->data[$nData]->{'time-layout'});
			if ($endLayouts == 0)  {echo '<h3> '.$myPageNoaa1 . ' - invalid xml file - program halted </h3>'; exit;}
			for ($nLayouts = 0; $nLayouts < $endLayouts; $nLayouts++) {
				$forecast       = $xml->data[$nData]->{'time-layout'}[$nLayouts];
				$endTimes       = count($forecast->{'start-valid-time'});
				$layoutKey      = (string) $forecast->{'layout-key'};
				for ($nTimes = 0; $nTimes < $endTimes; $nTimes++) {
					$key 		                = $layoutKey.'|'.$nTimes;
					$startTime	                = (string) $forecast->{'start-valid-time'}[$nTimes];
					$daypart	                = (string) $forecast->{'start-valid-time'}[$nTimes]['period_name'];
					$arrTimes[$key]['startTime']    = $startTime;
					if ($daypart <> '') {
					        $arrTimes[$key]['daypart'] 	= $daypart;
					}
					$endTime	                = (string) $forecast->{'end-valid-time'}[$nTimes];
					$arrTimes[$key]['endTime']      = $endTime;
				}  // eof times
			}  // eof layouts
# echo '<pre>'; print_r ($arrTimes); exit;	
			$result = array();		
			$endParameters	= count ($xml->data[$nData]->{'parameters'});
			for ($nParameters = 0; $nParameters < $endParameters;  $nParameters++) {
				$arr    = $xml->data[$nData]->{'parameters'}[$nParameters];
				$count  = -1;	
				foreach ($arr as $key => $value) {
					$count++;
					$name	= (string) $key;
					$layout	= (string) $value['time-layout'];
					if (isset ($value['type']) )	{$type	= (string) $value['type'];} 	else {$type	= '';}
					if (isset ($value['units']) )	{$units	= (string) $value['units'];} 	else {$units    = '';}					
					$endValues	= count($value->value);
					for ($nValues = 0; $nValues < $endValues; $nValues++) {
						$start	= $arrTimes[$layout.'|'.$nValues]['startTime'].'|'.$arrTimes[$layout.'|'.$nValues]['endTime']; 
						#echo '<pre>'.$start.PHP_EOL;
						$result[$start][$name][$type] = array ('units' => $units, 'value' => (string) $value->value[$nValues]);
					}  // eo for evert parameter value
					if (isset($value->{'weather-conditions'})) {
						$endConds = count($value->{'weather-conditions'});
						for ($nConds = 0; $nConds < $endConds ; $nConds++) {
							$start	        = $arrTimes[$layout.'|'.$nConds]['startTime'].'|'.$arrTimes[$layout.'|'.$nConds]['endTime'];  //
							$condition	= $value->{'weather-conditions'}[$nConds];
# echo '<pre>'; print_r($condition); exit;
							$endValues	= count($condition->value);
							for ($nValues = 0; $nValues < $endValues; $nValues++) {
								$type 		= 'weather-conditions';
								$weather	= (string) $condition->value[$nValues]['weather-type'];		// rain showers
								$units		= (string) $condition->value[$nValues]['coverage'];			// chance
								$extra		= (string) $condition->value[$nValues]['additive'];			// and
								$intensity	= (string) $condition->value[$nValues]['intensity'];		// light  or non
								$qualifier	= (string) $condition->value[$nValues]['qualifier'];		// ??  none?
								$result[$start][$name][$type][$nValues] = array ('units' => $units, 'intensity'=> $intensity, 'value' => $weather, 'qualifier' => $qualifier  ,'extra' => $extra);
							}  // for every conditionvalue				
						}  // eo for every weather-conditions
					}  // eo if weather-conditions
					if (isset($value->{'icon-link'})) {
# echo '<pre>'; print_r($value->{'icon-link'}); print_r($value); exit;
						$endIcons = count($value->{'icon-link'});
						for ($nIcons = 0; $nIcons <  $endIcons; $nIcons++){
							$start	= $arrTimes[$layout.'|'.$nIcons]['startTime'].'|'.$arrTimes[$layout.'|'.$nIcons]['endTime']; 
							$icon	= $value->{'conditions-icon'}[$nIcons];
							$result[$start][$name][$type] = array ('units' => 'url', 'value' =>  (string) $value->{'icon-link'}[$nIcons]);
						} // eo for every icon
					} // eo if conditions-icon
				}  // eo foreach parameter values
			}  // eo for every parameter
		ksort($result);
# echo '<pre>'.PHP_EOL; print_r ($result); echo '</pre>'.PHP_EOL;# exit;
		$arrFcst = array();
		$tempFound = $dewpFound	= $appFound = $windFound = $gustFound = $winddirFound = $cloudsFound = $humFound = $txtFound = $rainFound = $snowFound = $popFound = false;
		foreach ($result as $key => $arr) {
			list ($from , $to) = explode ('|', $key);
			if ($to == '') {  // new point forecast
				if (isset ($arrFcst['to']) ) {
					$returnArray['forecast'][] = $arrFcst;
					$arrFcst['liquid'] = $arrFcst['liquidNU'] = $arrFcst['snow'] = $arrFcst['snowNU'] = '-';
				} else {
					$arrFcst['to'] 		= $arrFcst['from'];
					$arrFcst['toStamp'] = strtotime($arrFcst['from']);
				}
				$arrFcst['from']		= $arrFcst['to'];
				$arrFcst['fromStamp']	= strtotime($arrFcst['to']);
				$arrFcst['to']			= $from;
				$arrFcst['toStamp']		= strtotime($from);
				if ( isset ($arr['temperature']['hourly']['value']) ) {
					$string 		= $arr['temperature']['hourly']['value'];
					$amount			= round(noaaconvertemp($string, $this->uomTemp),1);
					$arrFcst['temp']	= $amount.$uomTemp;
					$arrFcst['tempNU']	= $amount;
					$tempFound		= true;
				} else {
					$arrFcst['temp']	= $arrFcst['tempNU']	= '';
				}
				if ( isset ($arr['temperature']['dew point']['value']) ) {
					$string 			= $arr['temperature']['dew point']['value'];
					$amount				= round(noaaconvertemp($string, $this->uomTemp),1);
					$arrFcst['dewp']	= $amount.$uomTemp;
					$arrFcst['dewpNU']	= $amount;
					$dewpFound			= true;				
				} else {
					$arrFcst['dew']		= $arrFcst['dewpNU']	= '';
				}
				if ( isset ($arr['temperature']['apparent']['value']) ) {
					$string 				= $arr['temperature']['apparent']['value'];
					$amount					= round(noaaconvertemp($string, $this->uomTemp),1);
					$arrFcst['tempApp']		= $amount.$uomTemp;
					$arrFcst['tempAppNU']	= $amount;
					$appFound				= true;			
				} else {
					$arrFcst['tempApp']	= $arrFcst['tempAppNU']	= '';
				}				
				if ( isset ($arr['wind-speed']['sustained']['value']) ) {
					$string                 = $arr['wind-speed']['sustained']['value'];
					$arrbft                 = noaabeaufort($string);
					$arrFcst['beaufortnr']	= $arrbft[0];
					$arrFcst['beaufortclr']	= $arrbft[1];
					$arrFcst['beauforttxt']	= $arrbft[2];
					$amount 		= round( noaaconvertwind($string, $this->uomWindSpeed));
					$arrFcst['windSpeed']	= $amount.$uomWind;
					$arrFcst['windSpeedNU']	= $amount;
					$windFound		= true;
					$arrFcst['windSpeedNU']	= $amount;
				} else {
					$arrFcst['windSpeed']	= $arrFcst['windSpeedNU']	= '';
				}
				if ( isset ($arr['wind-speed']['gust']['value']) ) {
					$string 		= $arr['wind-speed']['gust']['value'];
					$amount 		= round( noaaconvertwind($string, $this->uomWindSpeed));							
					$arrFcst['windGust']	= $amount.$uomWind;
					$arrFcst['windGustNU']	= $amount;
					$gustFound				= true;
				} else {
					$arrFcst['windGust']	= $arrFcst['windGustNU']	= '';
				}
				if ( isset ($arr['direction']['wind']['value']) ) {	
					$arrFcst['windDirDeg']	= $arr['direction']['wind']['value'];
					$string		        =  noaaconvertwinddir ($arrFcst['windDirDeg']);
					$arrFcst['windDirRose']	= $string;		
					$from 		        = array ('North','East','South','West');
					$to 		        = array ('N','E','S','W');
					$arrFcst['windLbl']	= str_replace ($from, $to, $string);
					$winddirFound		= true;
				} else {
					$arrFcst['windDirDeg']	= $arrFcst['windLbl'] = $arrFcst['windDirRose'] = '';
					
				}
				if ( isset ($arr['cloud-amount']['total']['value']) ) {	
					$arrFcst['clouds']		= $arr['cloud-amount']['total']['value'];
					$cloudsFound			= true;
				} else {
					$arrFcst['clouds']		= '';
				}
				if ( isset ($arr['humidity']['relative']['value']) ) {	
					$arrFcst['hum'] 		= $arr['humidity']['relative']['value'].' %';
					$humFound				= true;
				} else {
					$arrFcst['hum']			= '';
				}
				if ( isset ($arr['conditions-icon']['forecast-NWS']['value']) ) {
					$iconFound		        = true;
					$string 		        = $arr['conditions-icon']['forecast-NWS']['value'];
					$arrFcst['iconUrl']             = $string;
					$arrUrl	                        = explode ('/',$string);
					$nr		                = count($arrUrl) - 1;
					list($icon,$ext)	        = explode('.',$arrUrl[$nr]);
					$arrFcst['icon']	        = $icon;
					$from			        = array ('0','1','2','3','4','5','6','7','8','9');
					$inconIn		        = str_replace ($from, '', $icon);
					$arrFcst['iconCleaned']	        = $inconIn;
					$iconDefault                    = wsconvertnoaaicon ($inconIn);
					$arrFcst['defailtIcon']		= $iconDefault;
					$arrFcst['defailtIconUrl']	= $myDefaultIconsDir.$iconDefault.'.'.$myDefaultIconsExt;
					$arrFcst['defailtIconSmlUrl']	= $myDefaultIconsSml.$iconDefault.'.'.$myDefaultIconsExt;
					$arrFcst['noaaIconUrl']	        = $myNoaaIconsDir.$icon.'.'.$myNoaaIconsExt;
					$arrFcst['noaaIconSmlUrl']	= $myNoaaIconsSml.$icon.'.'.$myNoaaIconsExt;
				} else {
					$arrFcst['iconUrl']             = $arrFcst['iconDefault'] 
					                                = $arrFcst['icon']	
					                                = '';
				}
				$nr                                     = 1.0*$arrFcst['defailtIcon'];
				if 	($nr < 101)     {$string	= 'no clouds';}
				elseif	($nr < 201)     {$string	= 'some clouds';}
				elseif	($nr < 301)     {$string	= 'partly cloudy';}
				else                    {$string	= 'overcast';}
				$string                                 = wsnoaafcttransstr($string);;
				if ( isset ($arr['weather']['weather-conditions']) ) {
					$endCond 	= count($arr['weather']['weather-conditions']);
					for ($i = 0; $i < $endCond; $i++) {
						$arrCond	= $arr['weather']['weather-conditions'][$i];
					#	if ($arrCond['extra'] <> '') {$string    .= '<br />'.wsnoaafcttransstr($arrCond['extra']);}
						if ($arrCond['units'] <> '') {$string    .= '<br />'.wsnoaafcttransstr($arrCond['units']).' ';}
						if ($arrCond['intensity'] <> '' && $arrCond['intensity'] <> 'none') {
							$string    .= wsnoaafcttransstr($arrCond['intensity']).' ';
						}
						if ($arrCond['value'] <> '') {$string    .= wsnoaafcttransstr($arrCond['value']).' ';}
						if ($arrCond['qualifier'] <> '' && $arrCond['qualifier'] <> 'none') {
							$string    .= wsnoaafcttransstr($arrCond['qualifier']).' ';
						}						
					}
					$txtFound	= true;
				}
				$arrFcst['weatherTxt'] 	= $string;
				continue;
			} else {
				if (!isset ($arrFcst['from']) ) {$arrFcst['from'] = $from;}				
			}
			if (isset ( $arr['precipitation']) ){
				$arrFcst['liquid'] = $arrFcst['liquidNU'] = $arrFcst['snow'] = $arrFcst['snowNU'] = '-';
				if (isset ( $arr['precipitation']['liquid']) ){
					$string 		= $arr['precipitation']['liquid']['value'];
					$amount 		= noaaconvertrain($string, $this->uomRain);			
					$arrFcst['liquid']	= $amount.$uomRain;
					$arrFcst['liquidNU']= $amount;
					$rainFound			= true;
				}
				if (isset ( $arr['precipitation']['snow']) ){
					$string 		= $arr['precipitation']['snow']['value'];
					$amount 		=  noaaconvertrain($string, $this->uomRain);
					$arrFcst['snow']	= $amount.$uomSnow;
					$arrFcst['snowNU']	= $amount;
					if ($amount <> 0) {$snowFound	= true;}
				} 
				continue;	
			}
			if (isset ( $arr['temperature']) ) {
				$arrFcst['tempMax']	= $arrFcst['tempMin'] =	 '';
				if (isset ( $arr['temperature']['maximum']) ){
					$string 		= $arr['temperature']['maximum']['value'];
					$amount			= round(noaaconvertemp($string, $this->uomTemp));
					$arrFcst['tempMax']	= $amount.$uomTemp;
					$arrFcst['tempMaxNU']	= $amount;
				}
				if (isset ( $arr['temperature']['minimum']) ){
					$string 		= $arr['temperature']['minimum']['value'];
					$amount			= round(noaaconvertemp($string, $this->uomTemp));
					$arrFcst['tempMin']	= $amount.$uomTemp;
					$arrFcst['tempMinNU']	= $amount;				
				}				
			}
			if (isset ( $arr['probability-of-precipitation']) ){
				$arrFcst['pop']		        =  $arr['probability-of-precipitation']['12 hour']['value'];
				$popFound			= true;
			}
		} // eo for each $return
		$returnArray['forecast'][] = $arrFcst;
		$returnArray['information']['tempFound'] 	=	$tempFound;
		$returnArray['information']['dewpFound'] 	=	$dewpFound;
		$returnArray['information']['appFound'] 	=	$appFound;
		$returnArray['information']['windFound'] 	=	$windFound;
		$returnArray['information']['gustFound'] 	=	$gustFound;
		$returnArray['information']['winddirFound']     =	$winddirFound;
		$returnArray['information']['cloudsFound'] 	=	$cloudsFound;
		$returnArray['information']['humFound'] 	=	$humFound;
		$returnArray['information']['txtFound'] 	=	$txtFound;
		$returnArray['information']['rainFound'] 	=	$rainFound;
		$returnArray['information']['snowFound'] 	=	$snowFound;
		$returnArray['information']['popFound'] 	=	$popFound;
		$ret = $this->writeToCache($returnArray);
		return $returnArray;
		}  // eo makeRequest processing
		else {
			echo '<h3>Loading data '.$this->weatherApiUrl. ' failed </h3>'.PHP_EOL;		
		}
	} // eof getWeatherData

	private function loadFromCache(){
	        global $wsDebug, $cron_all, $myPageNoaa1;
	        if (isset ($_REQUEST['force']) && $_REQUEST['force'] == 'noaafct') {
	                echo '<!-- module noaaDigitalGenerateHtml.php ('.__LINE__.'): no cache checked as force=noaafct is used'.PHP_EOL;
	                return;
	        }
		if (file_exists($this->cacheFile)){	
			$file_time	= filemtime($this->cacheFile);
			$now 		= time();
			$diff		= ($now - $file_time);		
			if (isset ($wsDebug) && $wsDebug == true) {
				echo "<!-- Weatherforecast ($this->cacheFile) 
	cache time   = ".date('c',$file_time)." from unix time $file_time
	current time = ".date('c',$now)." from unix time $now 
	difference   = $diff (seconds)
	diff allowed = $this->cacheTime (seconds) -->".PHP_EOL;	
			} 
			else {  echo "<!-- Weatherforecast ($this->cacheFile) cache time=  $file_time - current time = $now - difference  =  $diff - Diff allowed = $this->cacheTime -->".PHP_EOL;	
			}
			if ($diff <= $this->cacheTime){
				echo "<!-- Weatherforecast ($this->cacheFile) loaded from cache  -->".PHP_EOL;
				$returnArray =  unserialize(file_get_contents($this->cacheFile));
				return $returnArray;
			}
		}
		else {  echo "<!-- Weatherforecast ($this->cacheFile) does not exist yet  -->".PHP_EOL;
		}	
	} // eof loadFromCache
	
	private function writeToCache(&$returnArray){
		$returnArray['information']['cacheTimestamp']	= time()+ $this->cacheTime;
		$returnArray['information']['cacheTime']	= date('c', (time()+ $this->cacheTime));		
		if ( $this->enableCache && !empty($this->cache) ){
			if (!file_exists($this->cache)){
				mkdir($this->cache, 0777);   // attempt to make the cache dir
			}
			if (!file_put_contents($this->cacheFile, serialize($returnArray))){   
				echo PHP_EOL."<!-- Could not save data ($this->cacheFile) to cache ($this->cacheFile). Please make sure your cache directory exists and is writable. -->".PHP_EOL;
			} else {echo "<!-- Weatherforecast ($this->cacheFile) saved to cache  -->".PHP_EOL;}
		}
	} // eof writeToCache

	private function makeRequest(){
		$test= false;
		if ($test) {
			$this->rawData  = file_get_contents('./zzdigital.xml');
#			print_r ($this->rawData); exit;
		} else {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_URL, $this->weatherApiUrl);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$this->rawData = curl_exec ($ch);
		curl_close ($ch);
		}
		if (empty($this->rawData)){
			return false;
		}
		$search = array ('Service Unavailable','Error 504','Error 503');
		$error = false;
		for ($i = 0; $i < count($search); $i++) {
			$int = strpos($this->rawData , $search[$i]);
			if ($int > 0) {$error = true; break;}
		}
		if ($error == false) {return true; } else {return false;}
	} // eof makeRequest
}
?>
