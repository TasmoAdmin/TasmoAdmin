.DEFAULT_GOAL := docker-build

BUILD_VERSION ?= dev


clean:
	rm -rf _releases
	rm -rf _tmp
	rm -rf .docker/_tmp
	rm -f tasmoadmin/.php-cs-fixer.cache
	rm -f tasmoadmin/.phpunit.result.cache

docker-dev: clean
	./.docker/docker.sh prepare
	docker build -t tasmoadmin:loca -f .docker/Dockerfile.alpine-tmpl  .

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
	cd tasmoadmin; npm ci; npm run build; rm -rf node_modules
	tar -zcf ./_releases/tasmoadmin_${BUILD_VERSION}.tar.gz tasmoadmin
	zip -q -r ./_releases/tasmoadmin_${BUILD_VERSION}.zip tasmoadmin

quality: clean
	composer install -d tasmoadmin
	cd tasmoadmin; ./vendor/bin/php-cs-fixer fix
	cd tasmoadmin; ./vendor/bin/phpunit
	cd tasmoadmin; php -d memory_limit=4G ./vendor/bin/phpstan

dev:
	./.docker/docker.sh prepare
	composer install -d tasmoadmin
	cd tasmoadmin; npm ci; npm run build
	docker-compose build  --no-cache && docker-compose up
