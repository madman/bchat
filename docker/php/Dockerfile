FROM alpine:3.4
#see https://github.com/RobLoach/docker-composer/blob/master/base/php5-alpine/Dockerfile

MAINTAINER Yuriy Prokopets <yuriy.prokopets@gmail.com>

# Environments
ENV TIMEZONE            Europe/Kiev

RUN set -x \
    && addgroup -g 82 -S www-data \
    && adduser -u 82 -D -S -G www-data www-data

# Let's roll
RUN apk update && \
    apk upgrade && \
    apk add --update tzdata && \
    cp /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && \
    echo "${TIMEZONE}" > /etc/timezone && \
    apk add --update \
                curl \ 
                wget \
                ca-certificates \
                php5-ctype \
                php5-phar \
                php5-zip \
                php5-bz2 \
                php5-apcu \
                php5-curl \
                php5-gd \
                php5-iconv \
                php5-imagick \
                php5-json \
                php5-intl \
                php5-mcrypt \
                php5-opcache \
                php5-openssl \
                php5-pdo \
                php5-pdo_mysql \
                php5-mysqli \
                php5-xml \
                php5-dom \
                php5-zlib

RUN sed -i "s|;*date.timezone =.*|date.timezone = ${TIMEZONE}|i" /etc/php5/php.ini

###############
RUN apk add php5-cli

ENV PHP_MEMORY_LIMIT    64M
ENV MAX_UPLOAD          5M
ENV PHP_MAX_FILE_UPLOAD 5
ENV PHP_MAX_POST        25M

RUN apk add supervisor

# 
RUN \
    sed -i "s|;*memory_limit =.*|memory_limit = ${PHP_MEMORY_LIMIT}|i" /etc/php5/php.ini && \
    sed -i "s|;*upload_max_filesize =.*|upload_max_filesize = ${MAX_UPLOAD}|i" /etc/php5/php.ini && \
    sed -i "s|;*max_file_uploads =.*|max_file_uploads = ${PHP_MAX_FILE_UPLOAD}|i" /etc/php5/php.ini && \
    sed -i "s|;*post_max_size =.*|post_max_size = ${PHP_MAX_POST}|i" /etc/php5/php.ini && \
    sed -i "s|;*cgi.fix_pathinfo=.*|cgi.fix_pathinfo= 0|i" /etc/php5/php.ini

RUN rm -rf /var/cache/apk/* && rm -rf /tmp/*

COPY supervisord.conf /etc/supervisord.conf

RUN mkdir -p /var/log/supervisor
RUN mkdir -p /var/run/php5-fpm

WORKDIR /data/www/site

EXPOSE 8080

CMD ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
