
# Containerized Local Web Server Template

This is a template for small LAN-only HTTP server
with WebDAV, a basic style definition, mundane
access restriction, and TLS (key generated when
the docker container is built).

PHP is used for server side scripting, mainly
for running test commands in the container, and
for capturing 404 resources. Only basic functions
are used.

Nothing much, easily extended and adapted.

#### Contained software

  - Docker `alpine` image base,
  - `lighttpd` with `mod_auth`, `mod_webdav`, and `openssl`,
  - `php81`
  - `curl`
  - `apache2-utils` (for htpasswd example)

#### Structure

  - The complete config and data are in `docker/files`, and
    statically contained in the image.

  - Server and PHP config in `docker/files/etc`.

  - www data are in `docker/files/htdocs`.

  - WebDAV root `docker/files/htdocs/files`.

  - Scripts and styles in `docker/files/htdocs/gp`.

  - There are intentionally some URLs left out in the
    navigation to test the 404 behaviour.
