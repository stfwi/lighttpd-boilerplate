#
# Based on https://github.com/m4rcu5nl/docker-lighttpd-alpine
#
ARG REGISTRY_PREFIX=
FROM ${REGISTRY_PREFIX}alpine:3.18
USER root

#&& rm -rf /var/cache/apk/* \
RUN apk update \
  && apk add --no-cache lighttpd lighttpd-mod_auth lighttpd-mod_webdav openssl php-cgi curl apache2-utils sudo \
  && mkdir -p /etc/lighttpd/ssl/ \
  && openssl req -x509 -newkey rsa:4096 -keyout /tmp/key.pem -out /tmp/cert.pem -days 365 -subj '/CN=localhost' -nodes -sha256 \
  && cat /tmp/key.pem /tmp/cert.pem > /etc/lighttpd/ssl/localhost.pem \
  && rm /tmp/key.pem /tmp/cert.pem \
  && chmod 400 /etc/lighttpd/ssl/localhost.pem \
  && mkdir -p /var/www/htdocs \
  && echo "Installation step done."

COPY files/etc/ /etc/
COPY files/htdocs /var/www/htdocs
COPY files/entrypoint /entrypoint

RUN /bin/true \
  && addgroup -S -g 1000 httpd \
  && adduser -S -H -u 1000 -G httpd -h /var/www/htdocs httpd \
  && mkdir -p /var/log/lighttpd \
  && find /etc/lighttpd -exec chgrp httpd {} \;  \
  && chmod 640 /etc/lighttpd/ssl/* \
  && chmod 777 /var/run \
  && chgrp httpd /var/log/lighttpd \
  && chmod 770 /var/log/lighttpd \
  && chmod 700 /entrypoint \
  && echo "User setup done."

HEALTHCHECK --interval=1m --timeout=5s CMD curl -f http://localhost/ || exit 1
EXPOSE 80 443
VOLUME /var/www/

CMD ["/entrypoint"]
