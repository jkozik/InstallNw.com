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
        Settings.php

echo "Customize Settings-weather.php"
sed -i  -e  '/SITE\[\x27realtimefile/s/realtime.txt/mount\/realtime.txt/' \
        -e  '/SITE\[\x27graphImageDir/s/images/mount\/cumulusimages/' \
        -e  '/SITE\[\x27conditionsMETAR/s/= \x27.*;/= \x27KDPA\x27;/' \
        Settings-weather.php
