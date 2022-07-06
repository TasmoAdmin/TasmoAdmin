.DEFAULT_GOAL := docker-build

BUILD_VERSION ?= dev


clean:
	rm -rf _releases
	rm -rf _tmp
	rm -rf .docker/_tmp

docker-build: clean
	./.docker/docker.sh prepare
	./.docker/docker.sh build

docker-test: docker-build
	./.docker/docker.sh test

docker-tag: docker-test
	./.docker/docker.sh tag

docker-publish: docker-tag
	./.docker/docker.sh push
	./.docker/docker.sh manifest-list

package: clean
	mkdir _releases
	mkdir _tmp
	composer install --no-dev -o -d tasmoadmin
	cd tasmoadmin; npm ci
	node tasmoadmin/minify.js
	tar -zcf ./_releases/tasmoadmin_${BUILD_VERSION}.tar.gz tasmoadmin
	zip -q -r ./_releases/tasmoadmin_${BUILD_VERSION}.zip tasmoadmin
