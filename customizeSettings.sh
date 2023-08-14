echo "Customize Settings.php"
sed -i  -e  '/SITE\[\x27organ/s/= \x27.*;/= \x27NapervilleWeather.com\x27;/' \
        -e  '/SITE\[\x27copyr/s/Your Weather Website/NapervilleWeather.com/' \
        -e  '/SITE\[\x27location/s/Somewhere, SomeState, USA/Naperville, Illinois, USA/' \
        -e  '/SITE\[\x27email/s/mailto:somebody@somemail.org/jackkozik at email.com/' \
        -e  '/SITE\[\x27latitude/s/= \x27.*;/= \x2741.790072\x27;/' \
        -e  '/SITE\[\x27longitude/s/= \x27.*;/= \x27-88.124239\x27;/' \
        -e  '/SITE\[\x27cityname/s/Saratoga/Naperville/' \
        -e  '/SITE\[\x27tz/s/Los_Angeles/Chicago/' \
        -e  '/SITE\[\x27WXSIM/s/true/false/' \
        -e  '/SITE\[\x27noaazone/s/= \x27.*;/= \x27ILZ013\x27;/' \
        -e  '/SITE\[\x27noaaradar/s/= \x27.*;/= \x27LOT\x27;/' \
        -e  '/SITE\[\x27WUregion/s/= \x27.*;/= \x27mw\x27;/' \
        -e  '/SITE\[\x27WUsatellite/s/= \x27.*;/= \x27nc\x27;/' \
        -e  '/SITE\[\x27GR3radar/s/= \x27.*;/= \x27lot\x27;/' \
        -e   '/SITE\[\x27fcsturlNWS/s@= \x27.*;@= \x27https://forecast\.weather\.gov/MapClick\.php?lat=41\.790072\&lon=-88\.124239\&unit=0\&lg=english\&FcstType=text\&TextType=2\x27;@' \
        Settings.php


sed -i  '/SITE\[\x27NWSforecasts/,/^);/ c\
$SITE[\x27NWSforecasts\x27]   = array( // for the advforecast2.php V3.xx version script \
// use "Zone|Location|Point-printableURL",  as entries .. first one will be the default forecast. \
"ILZ013|Naperville|http://forecast.weather.gov/MapClick.php?CityName=Naperville&state=IL&site=LOT&textField1=41.7626&textField2=-88.1543&e=0&TextType=2", \
"ILZ014|Chicago Midway|http://forecast.weather.gov/MapClick.php?CityName=Chicago&state=IL&site=LOT&textField1=41.837&textField2=-87.685&e=1&TextType=2", \
"ILZ014|Chicago OHare|http://forecast.weather.gov/MapClick.php?CityName=Amf+Ohare&state=IL&site=LOT&lat=41.9803&lon=-87.9023&TextType=2", \
"ILZ012|St. Charles|http://forecast.weather.gov/MapClick.php?CityName=Saint+Charles&state=IL&site=LOT&textField1=41.9203&textField2=-88.3008&e=0&TextType=2", \
"ILZ012|Campton Hills|http://forecast.weather.gov/MapClick.php?lat=41.9861&lon=-88.3844&unit=0&lg=english&FcstType=text&TextType=2", \
);\
' Settings.php

sed -i '/SITE\[\x27NWSalertsCodes\x27/,/^);/ c\
$SITE[\x27NWSalertsCodes\x27] = array( \
"Naperville|ILZ013|ILC043",\
"Campton Hills|ILZ012|ILC089"\
);\
' Settings.php

echo "Customize Settings-weather.php"
sed -i  -e  '/SITE\[\x27realtimefile/s/realtime.txt/mount\/cumulus\/realtime.txt/' \
        -e  '/SITE\[\x27graphImageDir/s/images/mount\/cumulus\/images/' \
        -e  '/SITE\[\x27WXtags/s/CUtags/mount\/saratoga\/CUtags/' \
        -e  '/SITE\[\x27NOAAdir/s/Reports/mount\/cumulus\/Reports/' \
        -e  '/SITE\[\x27conditionsMETAR/s/= \x27.*;/= \x27KDPA\x27;/' \
        Settings-weather.php

echo "Customize ajaxCUwx.js"
sed -i '/realtimeFile = \x27/s/realtime.txt/.\/mount\/cumulus\/realtime.txt/' ajaxCUwx.js

echo "Customize wxquake.php"
sed -i -e '/$setLatitude/s//#&/' \
       -e '/$setLongitude/s//#&/' \
       -e '/$setLocationName/s//#&/' \
       -e '/$setTimeZone/s//#&/' \
       wxquake.php

echo "Customize wxmetar.php"
sed -i '/MetarList = array/,/^);/ c\
$MetarList = array ( // set this list to your local METARs \
  // Metar(ICAO) | Name of station | dist-mi | dist-km | direction | \
  "KDPA|Chicago/Dupage, Illinois, USA|11|18|NW|", // lat=41.9167,long=-88.2500, elev=231, dated=24-MAY-17 \
  "KLOT|Romeoville/Chi, Illinois, USA|13|21|S|", // lat=41.6000,long=-88.1000, elev=205, dated=24-MAY-17 \
  "KORD|Chicago O\x27Hare, Illinois, USA|17|27|NE|", // lat=41.9833,long=-87.9333, elev=200, dated=24-MAY-17 \
  "KARR|Chicago/Aurora, Illinois, USA|19|30|W|", // lat=41.7667,long=-88.4833, elev=215, dated=24-MAY-17 \
  "KJOT|Joliet, Illinois, USA|19|31|S|", // lat=41.5167,long=-88.1833, elev=177, dated=24-MAY-17 \
  "KMDW|Chicago, Illinois, USA|19|31|E|", // lat=41.7833,long=-87.7500, elev=188, dated=24-MAY-17 \
  "KPWK|Palwaukee, Illinois, USA|25|41|NNE|", // lat=42.1167,long=-87.9000, elev=203, dated=24-MAY-17 \
  "KC09|Morris-Washburn, Illinois, USA|29|47|SSW|", // lat=41.4333,long=-88.4167, elev=178, dated=24-MAY-17 \
  "KDKB|De Kalb, Illinois, USA|31|50|WNW|", // lat=41.9333,long=-88.7000, elev=279, dated=24-MAY-17 \
  "KIGQ|Chicago/Lansing, Illinois, USA|35|57|ESE|", // lat=41.5333,long=-87.5333, elev=188, dated=24-MAY-17 \
  "KGYY|Gary Regional, Indiana, USA|38|62|ESE|", // lat=41.6167,long=-87.4167, elev=180, dated=22-OCT-17 \
  "KUGN|Waukegan, Illinois, USA|45|73|NNE|", // lat=42.4167,long=-87.8667, elev=222, dated=24-MAY-17 \
  "KRPJ|Rochelle/Koritz, Illinois, USA|50|80|W|", // lat=41.8833,long=-89.0833, elev=238, dated=24-MAY-17 \
// list generated Sat, 02-May-2020 11:10am PDT at https://saratoga-weather.org/wxtemplates/find-metar.php \
);\
' wxmetar.php

echo "Customize menubar.php"
sed -i '/External Links/, /^<.ul>/ c\
<p class="sideBarTitle"><?php langtrans(\x27External Links\x27); ?></p>\
<ul>\
   <li><a href="http://www.wunderground.com/" title="Weather Underground">Weather Underground</a></li>\
   <li><a href="https://www.wunderground.com/personal-weather-station/dashboard?ID=KILNAPER7" title="-KILNAPER7">-KILNAPER7</a></li>\
   <li><a href="http://www.wxforum.net/" title="WXForum">WXforum.net</a></li>\
   <li><a href="http://www.wxqa.com/sss/search1.cgi?keyword=CW7164" title="CW7164">APRS-CW7164</a></li>\
   <li><a href="https://www.awekas.at/en/instrument.php?id=3086" title="-AWEKAS">AWEKAS-3086</a></li>\
   <li><a href="https://www.pwsweather.com/obs/NAPERVILLE.html" title="PWS-NAPERVILLE">PWS-NAPER</a></li>\
   <li><a href="http://napervilleweather.net/weewx" title="WeeWx">WeeWx</a></li>\
   <li><a href="http://napervilleweather.com/wxweatherlink.php" title="WeatherLink">WeatherLink</a></li>\
</ul>\
' menubar.php

echo "Customize include-wxstatus.php"
sed -i '/realtimefile/s/15/60/' include-wxstatus.php

echo "Customize noaafct/noaaSettings.php"
sed -i -e '/myLatitude/s/= \x27.*\x27;/= \x2741.7900009\x27;/' \
       -e '/myLongitude/s/= \x27.*\x27;/= \x27-88.1200027\x27;/'   \
       -e '/myArea/s/= \x27.*\x27;/= \x27Naperville\x27;/'   \
       -e '/myStation/s/= \x27.*\x27;/= \x27NapervilleWeather.com\x27;/'   noaafct/noaaSettings.php

echo "Customize noaafct/noaaDigitalGenerateHtml.php"
sed -i '/<?php/a\
error_reporting(0);
' noaafct/noaaDigitalGenerateHtml.php

echo "Customize davconvp2CW.php"
sed -i '/graphurl/s/davcon24.txt/mount\/saratoga\/davcon24.txt/'  davconvp2CW.php

echo "rename wxindex.php to index.php"
mv wxindex.php index.php
sed -i  -e '/wxindex/s/wxindex/index/' flyout-menu.xml

echo "New Radar view in Settings.php"
sed -i -e '/NWSregion/s/sw/nc/' Settings.php
