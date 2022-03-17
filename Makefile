.DEFAULT_GOAL := docker-build

BUILD_REF ?= dev
BUILD_VERSION ?= dev

clean:
	rm -rf _releases
	rm -rf _tmp
	rm -rf .docker/_tmp

docker-build: clean
	BUILD_REF=$(BUILD_REF) BUILD_VERSION=$(BUILD_VERSION) ./.docker/docker.sh prepare
	BUILD_REF=$(BUILD_REF) BUILD_VERSION=$(BUILD_VERSION) ./.docker/docker.sh build

docker-test: docker-build
	BUILD_REF=$(BUILD_REF) BUILD_VERSION=$(BUILD_VERSION) ./.docker/docker.sh test

docker-tag: docker-test
	BUILD_REF=$(BUILD_REF) BUILD_VERSION=$(BUILD_VERSION) ./.docker/docker.sh tag

docker-publish: docker-tag
	BUILD_REF=$(BUILD_REF) BUILD_VERSION=$(BUILD_VERSION) ./.docker/docker.sh push
	BUILD_REF=$(BUILD_REF) BUILD_VERSION=$(BUILD_VERSION) ./.docker/docker.sh manifest-list

package: clean
	mkdir _releases
	mkdir _tmp
	tar -zcf ./_releases/tasmoadmin_${BUILD_VERSION}.tar.gz tasmoadmin
	zip -q -r ./_releases/tasmoadmin_${BUILD_VERSION}.zip tasmoadmin
	cat portable/xampp.zip.* > portable/xampp.zip
	unzip -q portable/xampp.zip -d _tmp/
	cp -R tasmoadmin _tmp/xampp/htdocs/
	cp -R portable/root_xampp/* _tmp/xampp/
	zip -q -r ./_releases/tasmoadmin_${BUILD_VERSION}_xampp_portable.zip _tmp/xampp
