# InstallNw.com
NapervilleWeather.com is made from the scripts at Saratoga Weather. With this repository, I hope to automate putting these scripts into a docker container, making it easier for me to keep uptodate and move as my server environment evolves.

```
# InstallNw.net
Install NapervilleWeather.com

To install the NapervilleWeather.com website, do the following steps
$ git clone https://github.com/jkozik/InstallNw.com
$ cd InstallNw.com


# direct the Cumulus software to write realtime.txt to this directory.  Cronjob one a minute
$ mkdir mount 
$ sudo adduser www-data && chown www-data:www-data mount 

$ docker build -t jkozik/nw.com .
$ docker run -dit --name nw.net-app -p 8082:80 -v mount:/var/www/html/mount jkozik/nw.com
$ docker exec -it nw.com-app /bin/bash # to get a bash prompt into the container

# to Rebuild, first stop and rm container
$ docker stop nw.com-app
$ docker rm   nw.com-app
```

