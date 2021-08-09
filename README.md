# InstallNw.com
NapervilleWeather.com is made from the scripts at Saratoga Weather. With this repository, I hope to automate putting these scripts into a docker container, making it easier for me to keep up to date and move as my server environment evolves.
First, setup a data storage container that wraps the realtime weather data files (eg realtime.txt) into something that can be shared across multiple containers.  So run below, but only once.  If you are running multiple websites from the same weather data, this container is shared.  So optionally run below:
```
$ # OLD docker run -dit --name wjr-data -v /mount/wjr:/var/www/html/mount php:7.2-apache
$ docker run -dit --name wjr-data -v /home/wjr/public_html:/var/www/html/mount php:7.2-apache
$ docker exec -it wjr-data /bin/bash     # verify that you can find realtime.txt in the directory sited above
```

Next, clone the repository, build and run it
```
$ git clone https://github.com/jkozik/InstallNw.com
# Optionally edit customerSettings.sh 
$ docker build -t jkozik/nw.com .
$ docker run -dit --name nw.com-app -p 8082:80 --volumes-from wjr-data jkozik/nw.com

# shell prompt, logs, and restart needed for rebuild
$ docker exec -it nw.com-app /bin/bash
$ docker logs -f nw.com-app
$ docker stop nw.com-app; docker rm nw.com-app

```

The wjr-data contain mounts a host directory. The data comes from the Cumulus program that runs in a separte Windows 10 instance.  The following data is expected there:
```
-realtime.txt -- key realtime file updated every minute
-davconfcst.php,davcon24.txt -- data to recreate the Davis Console.  Scripts from Silver Acron Weather
-Reports/ -- Cumulus reports by month
-images/ -- Cumulus station graphes
-awekas_wl.htm -- retrieved by https://www.awekas.at/
```
The Cumulus program has an Internet Files menu tab that generates these files from templates.  Setup for generating these files is outside of the scope for this repository.
The precise paths to the data above are not fixed.  EG, the key file realtime.txt is found at mount/cumulus/realtime.txt.  This path needs to get reflected in Settings.php and Settings-weather.php; I use the customizeSettings.sh script to set this. You'll need to edit customizeSettings.sh for your setup.

I decided to run the nw.com application straight on the server, no VMs.  Here's the two key commands I ran as user jkozik on the server:
```
$ docker run -dit --name wjr-data -v /home/wjr/public_html:/var/www/html/mount php:7.2-apache
$ docker run -dit --name nw.com-app -p 8082:80 --volumes-from wjr-data jkozik/nw.com
```
At this point, it is good to verify that the web server can correctly see the realtime weather data. Run the following test:
```
$ curl http://napervilleweather.com/mount/cumulus/realtime.txt
```
# Updates
The weather scripts update frequently.  I read the updates and every few months I apply the updates. Here's the steps I follow.
```
$ docker build --no-cache -t jkozik/nw.comtmp .
$ docker stop nw.com-app
$ docker run -dit --name nw.com-apptmp -p 8082:80 --volumes-from wjr-data jkozik/nw.comtmp
$ docker rm nw.com-app
$ docker rename nw.com-apptmp nw.com-app
```
Note: I rebuild everything with the no-cache option, but I build it to a temp name, so that I can fall back to the old version if the updates don't work. Once the new version works, I delete the old continer and rename the temp version.
# Restart after power outage
Rarely, I have to restart the server.  Most recently, this was because of a power outage.  On my todo list is to re-run the containers with the --restart unless-stopped and make sure the dependency with the shared data volume is addressed.  But for now, I run the following commands to manually restart the weather servers on the dell2 server. The following commands are run from the jkozik login:
``` 
$ docker start wjr-data
$ docker start weather-data
$ docker start nw.com-app
$ docker start chw.com-app
$ docker start chw.net-app
```
Verify that everything is working by running docker ps.
```
$ docker ps
CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS              PORTS                  NAMES
207c0f39ef59        jkozik/chw.com      "docker-php-entryp..."   3 weeks ago         Up 16 hours         0.0.0.0:8083->80/tcp   chw.com-app
b10d2f81c3d7        jkozik/nw.com       "docker-php-entryp..."   3 weeks ago         Up 16 hours         0.0.0.0:8082->80/tcp   nw.com-app
fcae2d91f8ac        jkozik/chw.net      "docker-php-entryp..."   7 months ago        Up 16 hours         0.0.0.0:8081->80/tcp   chw.net-app
d6b0265159e4        jkozik/nw.net       "docker-php-entryp..."   7 months ago        Up 16 hours         0.0.0.0:8080->80/tcp   nw.net-app
c4527394f999        php:7.2-apache      "docker-php-entryp..."   7 months ago        Up 16 hours         80/tcp                 weather-data
37de44712020        php:7.2-apache      "docker-php-entryp..."   8 months ago        Up 16 hours         80/tcp                 wjr-data
$
```
Updated to work with ssh access.



