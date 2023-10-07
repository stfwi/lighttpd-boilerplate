#
# Docker lighttpd image.
#
DOCKER_LOCAL_REPOSITORY_NAMESPACE=   # -> localhost:5000/  if you use a local docker registry
LIGHTTPD_IMAGE_VERSION=1
LIGHTTPD_IMAGE:=$(DOCKER_LOCAL_REPOSITORY_NAMESPACE)lighttpd:$(LIGHTTPD_IMAGE_VERSION)
LIGHTTPD_CONTAINER_NAME=lighttpd
WITH_DEV_HTDOCS=0 # -> Switch to 1 for directly working on the htdocs from outside the container and update quickly in the browser with F5.

ifeq ($(WITH_DEV_HTDOCS),1)
 LIGHTTPD_VOLUME_DEV_OVERLOAD:=-v $(shell pwd)/docker/files/htdocs:/var/www/htdocs
endif

MAKEFLAGS += --no-print-directory
.PHONY: default container-image clean start stop restart shell

#
# Default make target: build+start.
#
default: start

#
# Build docker image.
#
container-image:
	@echo "Building image ..."
	@cd docker && docker build \
		--build-arg REGISTRY_PREFIX=$(DOCKER_LOCAL_REPOSITORY_NAMESPACE) \
		--build-arg IMAGE_VERSION=$(LIGHTTPD_IMAGE_VERSION) \
		-t $(LIGHTTPD_IMAGE) \
		.

#
# Start the container, conditionally build.
# Intentionally uniquely named, so that duplicate runs fail.
#
start: container-image
	@docker kill $(LIGHTTPD_CONTAINER_NAME) >/dev/null 2>&1; \
	docker rm $(LIGHTTPD_CONTAINER_NAME) >/dev/null 2>&1; \
	docker run -d --name $(LIGHTTPD_CONTAINER_NAME) --restart=always \
					$(LIGHTTPD_VOLUME_DEV_OVERLOAD) \
					--mount type=bind,source=/etc/localtime,destination=/etc/localtime,readonly=true \
					--publish 80:80 \
					--publish 443:443 \
					$(LIGHTTPD_IMAGE) \
					$(CMD)

restart: start

#
# Stop the server clear docker caches (development).
#
stop:
	@docker kill $(LIGHTTPD_CONTAINER_NAME) >/dev/null 2>&1 && echo "Container stopped." || /bin/true
	@docker system prune -f >/dev/null 2>&1 || /bin/true
	@docker volume prune -f >/dev/null 2>&1 || /bin/true

#
# Stop and remove image.
#
clean: stop
	@docker kill $(LIGHTTPD_CONTAINER_NAME) >/dev/null 2>&1 || /bin/true
	@docker image rm $(LIGHTTPD_IMAGE) >/dev/null 2>&1 || /bin/true
	@docker system prune -f >/dev/null 2>&1 || /bin/true

#
# Open shell in the running container.
#
shell:
	@docker exec -ti $(LIGHTTPD_CONTAINER_NAME) /bin/sh
