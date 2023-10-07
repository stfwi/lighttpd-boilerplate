#
# Based on https://github.com/m4rcu5nl/docker-lighttpd-alpine
#
ARG REGISTRY_PREFIX=
FROM ${REGISTRY_PREFIX}alpine:3.18
USER root

#&& rm -rf /var/cache/apk/* \
RUN apk update \
  && apk add --no-cache lighttpd lighttpd-mod_auth lighttpd-mod_webdav openssl php-cgi curl apache2-utils \
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

RUN chmod 700 /entrypoint

HEALTHCHECK --interval=1m --timeout=5s CMD curl -f http://localhost/ || exit 1
EXPOSE 80 443
VOLUME /var/www/

CMD ["/entrypoint"]
