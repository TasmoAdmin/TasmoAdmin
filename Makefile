.DEFAULT_GOAL := package

BUILD_VERSION ?= dev


clean:
	rm -rf _releases
	rm -rf _tmp

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

