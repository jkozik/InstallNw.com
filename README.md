# InstallNw.com
NapervilleWeather.com is made from the scripts at Saratoga Weather. With this repository, I hope to automate putting these scripts into a docker container, making it easier for me to keep up to date and move as my server environment evolves.
First, setup a data storage container that wraps the realtime weather data files (eg realtime.txt) into something that can be shared across multiple containers.  So run below, but only once.  If you are running multiple websites from the same weather data, this container is shared.  So optionally run below:
```
$ docker run -dit --name wjr-data -v /mount/wjr:/var/www/html/mount php:7.2-apache
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

