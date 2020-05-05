FROM php:7.2-apache 
RUN apt update && apt -y install vim unzip wget libpng-dev zlib1g-dev  && \
    docker-php-ext-install calendar && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install gd && \
    apt clean

WORKDIR /var/www/html

ADD http://saratoga-weather.org/wxtemplates/Base-USA.zip  /var/www/html
ADD http://saratoga-weather.org/wxtemplates/CU-plugin.zip  /var/www/html
ADD http://saratoga-weather.org/saratoga-icons2.zip /var/www/html
ADD http://saratoga-weather.org/wxtemplates/meteotreviglio-icons.zip /var/www/html
#ADD http://napervilleweather.com/davconsoleCW241_Full.zip /var/www/html
#ADD https://silveracorn.nz/weather/download.php?download_file=davconsoleCW241_Full.zip /var/www/html
COPY davconsoleCW241_Full.zip /var/www/html
COPY noaafct.zip  /var/www/html
COPY favicon.ico /var/www/html

RUN cd /var/www/html && \
    unzip Base-USA.zip  && rm Base-USA.zip && \
    unzip CU-plugin.zip && rm CU-plugin.zip && \
    unzip -of saratoga-icons2.zip && rm saratoga-icons2.zip && \
    unzip meteotreviglio-icons.zip && rm meteotreviglio-icons.zip && \
    unzip noaafct.zip && cp noaafct/wxStartNoaaFct.php /var/www/html && rm noaafct.zip && \
    unzip davconsoleCW241_Full.zip  && rm davconsoleCW241_Full.zip

RUN chown -R www-data:www-data * && \
    chmod -R 755 . && \
    chmod 775 alert-images/ ajax-images/ cache/ alertlog/ WXtags-template-files/ && \
    chmod 775 forecast/ forecast/images/ forecast/icon-templates/ && \
    chmod 777 cache && \ 
    mkdir mount && chmod 777 mount && chown www-data:www-data mount

COPY customizeSettings.sh /var/www/html/
COPY wxwebcam.php /var/www/html

RUN sed -i -e '/^#AddDef/s/\#AddDef/AddDef/' /etc/apache2/conf-enabled/charset.conf && \
    sed -i -e '/fcsticonstype/s/jpg/gif/' Settings.php && \
    chmod +x customizeSettings.sh && . ./customizeSettings.sh

#COPY /home/jkozik/apache/mount/realtime.txt /var/www/html/realtime.txt



#FROM php:7.2-apache
#RUN apt update && apt -y install vim && docker-php-ext-install calendar && apt clean
#COPY --from=builder /var/www/html/ /var/www/html/
#COPY customizeSettings.sh /var/www/html/
#RUN chmod +x customizeSettings.sh && /var/www/html/customizeSettings.sh
#RUN chown www-data:www-data CUtags.php




#From php:7.2-apache
#RUN apt update && apt -y install vim git && apt clean
#ADD http://saratoga-weather.org/wxtemplates/Base-USA.zip /var/www/html/
