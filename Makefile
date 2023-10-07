#
# Docker lighttpd image.
#
DOCKER_LOCAL_REPOSITORY_NAMESPACE=localhost:5000
LIGHTTPD_IMAGE_VERSION=1
LIGHTTPD_IMAGE:=$(DOCKER_LOCAL_REPOSITORY_NAMESPACE)/lighttpd:$(LIGHTTPD_IMAGE_VERSION)
LIGHTTPD_CONFIG_VOLUME_NAME=local_config_volume
LIGHTTPD_CONTAINER_NAME=lighttpd
WITH_DEV_HTDOCS=1

ifeq ($(WITH_DEV_HTDOCS),1)
 LIGHTTPD_VOLUME_DEV_OVERLOAD:=-v $(shell pwd)/docker/files/htdocs:/var/www/htdocs
endif

MAKEFLAGS += --no-print-directory
.PHONY: default image start stop restart shell

default: start

image:
	@echo "Building image ..."
	@cd docker && docker build \
		--build-arg REGISTRY_PREFIX=$(DOCKER_LOCAL_REPOSITORY_NAMESPACE)/ \
		--build-arg IMAGE_VERSION=$(LIGHTTPD_IMAGE_VERSION) \
		-t $(LIGHTTPD_IMAGE) \
		.

start: image
	@docker kill $(LIGHTTPD_CONTAINER_NAME) >/dev/null 2>&1; \
	if [ -z "$(shell docker volume ls -q -f 'name=$(LIGHTTPD_CONFIG_VOLUME_NAME)')" ]; then \
		echo "Creating config volume ..."; \
		docker volume create "$(LIGHTTPD_CONFIG_VOLUME_NAME)"; \
	fi; \
	docker rm $(LIGHTTPD_CONTAINER_NAME) >/dev/null 2>&1; \
	docker run -d --name $(LIGHTTPD_CONTAINER_NAME) --restart=always \
					$(LIGHTTPD_VOLUME_DEV_OVERLOAD) \
					-v $(LIGHTTPD_CONFIG_VOLUME_NAME):/config:ro \
					--mount type=bind,source=/etc/localtime,destination=/etc/localtime,readonly=true \
					--publish 80:80 \
					--publish 443:443 \
					$(LIGHTTPD_IMAGE) \
					$(CMD)

restart: start

stop:
	@docker kill $(LIGHTTPD_CONTAINER_NAME) >/dev/null 2>&1 && echo "Container stopped." || /bin/true
	@docker system prune -f >/dev/null 2>&1 || /bin/true
	@docker volume prune -f >/dev/null 2>&1 || /bin/true

shell:
	@docker exec -ti $(LIGHTTPD_CONTAINER_NAME) /bin/sh
