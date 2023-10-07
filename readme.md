
# Containerized Local Web Server Template

This is a template for small LAN-only HTTP server with WebDAV,
a basic style definition, mundane access restriction, and TLS
(key generated when the docker container is built).

PHP is used for server side scripting, mainly for running test
commands in the container, capturing 404 resources, and JIT
markdown conversion.

Only basic functions are used, nothing much, easily extended
and adapted.

#### Contained software, references

Binary packages:

  - Docker `alpine` image base,
  - `lighttpd` with `mod_auth`, `mod_webdav`, and `openssl`,
  - `php81`
  - `curl`
  - `apache2-utils` (for htpasswd example)

Code references/libraries:

  - Titillium font (by Google, Open Font license)
  - FontAwesome (by Dave Gandy, SIL OFL 1.1)
  - php-markdown v2.0.0 (by Michel Fortin)

#### Structure

  - The complete config and data are in `docker/files`, and
    statically contained in the image.

  - Server and PHP config in `docker/files/etc`.

  - www data are in `docker/files/htdocs`.

  - WebDAV root `docker/files/htdocs/files`.

  - Scripts and styles in `docker/files/htdocs/gp`.

  - There are intentionally some URLs left out in the
    navigation to test the 404 behaviour.

#### Building

  - Requires Docker, version >= v24.0.5
  - Optional GNU Make, version >= v4.2

Building example with GNU make:

  ```bash
  # Build and start (same as "make start")
  $ make
  Building image ...
  [+] Building 6.2s (11/11) FINISHED
  => [internal] load build definition from Dockerfile                              0.0s
  => => transferring dockerfile: 1.34kB                                            0.0s
  => [internal] load .dockerignore                                                 0.0s
  => => transferring context: 81B                                                  0.0s
  => [internal] load metadata for docker.io/library/alpine:3.18                    1.0s
  => [1/6] FROM docker.io/library/alpine:3.18@sha256:eece025e432126ce23f223450a03  0.5s
  => => resolve docker.io/library/alpine:3.18@sha256:eece025e432126ce23f223450a03  0.0s
  => => sha256:eece025e432126ce23f223450a0326fbebde39cdf496a85d8c 1.64kB / 1.64kB  0.0s
  => => sha256:48d9183eb12a05c99bcc0bf44a003607b8e941e1d4f41f9ad12bdc 528B / 528B  0.0s
  => => sha256:8ca4688f4f356596b5ae539337c9941abc78eda10021d35cbc 1.47kB / 1.47kB  0.0s
  => => sha256:96526aa774ef0126ad0fe9e9a95764c5fc37f409ab9e97021e 3.40MB / 3.40MB  0.3s
  => => extracting sha256:96526aa774ef0126ad0fe9e9a95764c5fc37f409ab9e97021e7b477  0.1s
  => [internal] load build context                                                 0.0s
  => => transferring context: 461.73kB                                             0.0s
  => [2/6] RUN apk update   && apk add --no-cache lighttpd lighttpd-mod_auth ligh  3.5s
  => [3/6] COPY files/etc/ /etc/                                                   0.0s
  => [4/6] COPY files/htdocs /var/www/htdocs                                       0.1s
  => [5/6] COPY files/entrypoint /entrypoint                                       0.0s
  => [6/6] RUN /bin/true   && addgroup -S -g 1000 httpd   && adduser -S -H -u 100  0.8s
  => exporting to image                                                            0.2s
  => => exporting layers                                                           0.2s
  => => writing image sha256:0ecd4027e24e9d023046abe4d38a0af53c3d6d68caf1e1f0de86  0.0s
  => => naming to docker.io/library/lighttpd:1                                     0.0s
  cfb5fa6cab048998a5120137ef53a539f907bd6ae433e6b40a16cfc866726d69

  $ docker ps
  CONTAINER ID   IMAGE       COMMAND       CREATED   STATUS  PORTS                                    NAMES
  cfb5fa6cab04   lighttpd:1  "/entrypoint" About ..  Up ...  0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp lighttpd

  # Stop container
  $ make stop
  $ docker ps
  CONTAINER ID   IMAGE       COMMAND       CREATED   STATUS  PORTS                                    NAMES

  # Remove image
  $ make clean
  ```

Building example manually:

  ```bash
  cd docker && docker build --build-arg IMAGE_VERSION=1 -t lighttpd:1 .

  docker run -d --name lighttpd --restart=always \
    --mount type=bind,source=/etc/localtime,destination=/etc/localtime,readonly=true \
    --publish 80:80 --publish 443:443 \
    lighttpd:1
  ```
