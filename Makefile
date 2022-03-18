.DEFAULT_GOAL := docker-build

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
	tar -zcf ./_releases/tasmoadmin_${BUILD_VERSION}.tar.gz tasmoadmin
	zip -q -r ./_releases/tasmoadmin_${BUILD_VERSION}.zip tasmoadmin
	cat portable/xampp.zip.* > portable/xampp.zip
	unzip -q portable/xampp.zip -d _tmp/
	cp -R tasmoadmin _tmp/xampp/htdocs/
	cp -R portable/root_xampp/* _tmp/xampp/
	zip -q -r ./_releases/tasmoadmin_${BUILD_VERSION}_xampp_portable.zip _tmp/xampp
