# InstallNw.com
NapervilleWeather.com is made from the scripts at Saratoga Weather. With this repository, I hope to automate putting these scripts into a docker container, making it easier for me to keep up to date and move as my server environment evolves.

```
# To get started, clone the repository, build and run it
$ git clone https://github.com/jkozik/InstallNw.com
$ docker build -t jkozik/nw.com .
$ docker run -dit --name nw.com-app -p 8082:80 -v /home/jkozik/apache/mount:/var/www/html/mount jkozik/nw.com

# shell prompt, logs, and restart needed for rebuild
$ docker exec -it nw.com-app /bin/bash
$ docker logs -f nw.com-app
$ docker stop nw.com-app; docker rm nw.com-app

```

This container mounts a host directory (see -v option above). The data comes from the Cumulus program that runs in a separte Windows 10 instance.  The following data is expected there:
```
realtime.txt -- key realtime file updated every minute
davconfcst.php,davcon24.txt -- data to recreate the Davis Console.  Scripts from Silver Acron Weather
Reports/ -- from Cumulus program
images/ -- from Cumulus program
awekas_wl.htm -- from Cumulus, retrieved by https://www.awekas.at/

```
The Cumulus program has an Internet Files menu tab that generates these files from templates.  Setup for generating these files is outside of the scope for this repository.
