#!/bin/bash
set -o errexit

TARGET=ghcr.io/tasmoadmin/tasmoadmin
QEMU_VERSION=v6.1.0-8
ALPINE_VERSION=3.22
BUILD_REF="${BUILD_REF:=dev}"
BUILD_VERSION="${BUILD_VERSION:=dev}"

main() {
    case $1 in
        "prepare")
            docker_prepare
            ;;
        "build")
            docker_build
            ;;
        "build_amd64")
            docker_build_amd64
            ;;
        "build_arm")
            docker_build_arm
            ;;
        "build_arm64")
            docker_build_arm64
            ;;
        "test")
            docker_test
            ;;
        "test_amd64")
            docker_test_amd64
            ;;
        "test_arm")
            docker_test_arm
            ;;
        "test_arm64")
            docker_test_arm64
            ;;
        "tag")
            docker_tag
            ;;
        "push")
            docker_push
            ;;
        "manifest-list")
            docker_manifest_list
            ;;
        *)
            echo "none of above!"
            exit 1
    esac
}

docker_prepare() {
    # Prepare qemu to build images other then x86_64 on travis
    prepare_qemu
}

docker_build() {
    echo "DOCKER BUILD: Build all docker images."
    docker_build_amd64
    docker_build_arm
    docker_build_arm64
}

docker_build_amd64() {
    echo "DOCKER BUILD: Build amd64."
    docker build --progress=plain --platform linux/amd64 --build-arg BUILD_REF=${BUILD_REF} --build-arg BUILD_DATE=$(date +"%Y-%m-%dT%H:%M:%SZ") --build-arg BUILD_VERSION=${BUILD_VERSION} --build-arg BUILD_FROM=amd64/alpine:${ALPINE_VERSION} --build-arg BUILD_ARCH=amd64 --build-arg QEMU_ARCH=x86_64 --file ./.docker/Dockerfile.alpine-tmpl --tag ${TARGET}:build-alpine-amd64 .
}

docker_build_arm() {
    echo "DOCKER BUILD: Build arm."
    docker build --progress=plain --platform linux/arm/v7 --build-arg BUILD_REF=${BUILD_REF} --build-arg BUILD_DATE=$(date +"%Y-%m-%dT%H:%M:%SZ") --build-arg BUILD_VERSION=${BUILD_VERSION} --build-arg BUILD_FROM=arm32v7/alpine:${ALPINE_VERSION} --build-arg BUILD_ARCH=arm32v7 --build-arg QEMU_ARCH=arm --file ./.docker/Dockerfile.alpine-tmpl --tag ${TARGET}:build-alpine-arm32v7 .
}

docker_build_arm64()  {
    echo "DOCKER BUILD: Build arm64."
    docker build --progress=plain --platform linux/arm64 --build-arg BUILD_REF=${BUILD_REF} --build-arg BUILD_DATE=$(date +"%Y-%m-%dT%H:%M:%SZ") --build-arg BUILD_VERSION=${BUILD_VERSION} --build-arg BUILD_FROM=arm64v8/alpine:${ALPINE_VERSION} --build-arg BUILD_ARCH=aarch64 --build-arg QEMU_ARCH=aarch64 --file ./.docker/Dockerfile.alpine-tmpl --tag ${TARGET}:build-alpine-arm64v8 .
}

docker_test() {
    echo "DOCKER TEST: Test all docker images."
    docker_test_amd64
    docker_test_arm
    docker_test_arm64
}

docker_test_amd64() {
    echo "DOCKER TEST: Test amd64."
    docker run -d --rm --name=test-alpine-amd64 ${TARGET}:build-alpine-amd64
    if [ $? -ne 0 ]; then
       echo "DOCKER TEST: FAILED - Docker container failed to start build-alpine-amd64."
       exit 1
    else
       echo "DOCKER TEST: PASSED - Docker container succeeded to start build-alpine-amd64."
    fi

    docker kill test-alpine-amd64
}

docker_test_arm() {
    echo "DOCKER TEST: Test arm."
    docker run -d --rm --name=test-alpine-arm32v7 ${TARGET}:build-alpine-arm32v7
    if [ $? -ne 0 ]; then
       echo "DOCKER TEST: FAILED - Docker container failed to start build-alpine-arm32v7."
       exit 1
    else
       echo "DOCKER TEST: PASSED - Docker container succeeded to start build-alpine-arm32v7."
    fi

    docker kill test-alpine-arm32v7

}

docker_test_arm64() {
    echo "DOCKER TEST: Test arm64."
    docker run -d --rm --name=test-alpine-arm64v8 ${TARGET}:build-alpine-arm64v8
    if [ $? -ne 0 ]; then
       echo "DOCKER TEST: FAILED - Docker container failed to start build-alpine-arm64v8."
       exit 1
    else
       echo "DOCKER TEST: PASSED - Docker container succeeded to start build-alpine-arm64v8."
    fi

    docker kill test-alpine-arm64v8
}

docker_tag() {
    echo "DOCKER TAG: Tag all docker images."
    docker tag $TARGET:build-alpine-amd64 $TARGET:$BUILD_VERSION-alpine-amd64
    docker tag $TARGET:build-alpine-arm32v7 ${TARGET}:${BUILD_VERSION}-alpine-arm32v7
    docker tag $TARGET:build-alpine-arm64v8 $TARGET:$BUILD_VERSION-alpine-arm64v8
}

docker_push() {
    echo "DOCKER PUSH: Push all docker images."
    docker push $TARGET:${BUILD_VERSION}-alpine-amd64
    docker push $TARGET:${BUILD_VERSION}-alpine-arm32v7
    docker push $TARGET:${BUILD_VERSION}-alpine-arm64v8
}

docker_manifest_list() {
    echo "DOCKER MANIFEST: Create and Push docker manifest lists."
    docker_manifest_list_version

    if [[ ${BUILD_VERSION} == "dev" ]]; then
        echo "DOCKER MANIFEST: Create and Push docker manifest lists DEV."
    elif [[ ${BUILD_VERSION} == *"beta"* ]]; then
        echo "DOCKER MANIFEST: Create and Push docker manifest lists BETA."
        docker_manifest_list_beta
	  else
        echo "DOCKER MANIFEST: Create and Push docker manifest lists LATEST."
        docker_manifest_list_latest
    fi

    docker_manifest_list_version_os_arch
}

docker_manifest_list_version() {
  # Manifest Create BUILD_VERSION
  echo "DOCKER MANIFEST: Create and Push docker manifest list - $TARGET:$BUILD_VERSION."
  docker manifest create $TARGET:$BUILD_VERSION \
      $TARGET:$BUILD_VERSION-alpine-amd64 \
      $TARGET:$BUILD_VERSION-alpine-arm32v7 \
      $TARGET:$BUILD_VERSION-alpine-arm64v8

  # Manifest Annotate BUILD_VERSION
  docker manifest annotate $TARGET:$BUILD_VERSION $TARGET:$BUILD_VERSION-alpine-arm32v7 --os=linux --arch=arm --variant=v6
  docker manifest annotate $TARGET:$BUILD_VERSION $TARGET:$BUILD_VERSION-alpine-arm64v8 --os=linux --arch=arm64 --variant=v8

  # Manifest Push BUILD_VERSION
  docker manifest push $TARGET:$BUILD_VERSION
}

docker_manifest_list_latest() {
  # Manifest Create latest
  echo "DOCKER MANIFEST: Create and Push docker manifest list - $TARGET:latest."
  docker manifest create $TARGET:latest \
      $TARGET:$BUILD_VERSION-alpine-amd64 \
      $TARGET:$BUILD_VERSION-alpine-arm32v7 \
      $TARGET:$BUILD_VERSION-alpine-arm64v8

  # Manifest Annotate BUILD_VERSION
  docker manifest annotate $TARGET:latest $TARGET:$BUILD_VERSION-alpine-arm32v7 --os=linux --arch=arm --variant=v6
  docker manifest annotate $TARGET:latest $TARGET:$BUILD_VERSION-alpine-arm64v8 --os=linux --arch=arm64 --variant=v8

  # Manifest Push BUILD_VERSION
  docker manifest push $TARGET:latest
}

docker_manifest_list_beta() {
  # Manifest Create beta
  echo "DOCKER MANIFEST: Create and Push docker manifest list - $TARGET:beta."
  docker manifest create $TARGET:beta \
      $TARGET:$BUILD_VERSION-alpine-amd64 \
      $TARGET:$BUILD_VERSION-alpine-arm32v7 \
      $TARGET:$BUILD_VERSION-alpine-arm64v8

  # Manifest Annotate BUILD_VERSION
  docker manifest annotate $TARGET:beta $TARGET:$BUILD_VERSION-alpine-arm32v7 --os=linux --arch=arm --variant=v6
  docker manifest annotate $TARGET:beta $TARGET:$BUILD_VERSION-alpine-arm64v8 --os=linux --arch=arm64 --variant=v8

  # Manifest Push BUILD_VERSION
  docker manifest push $TARGET:beta
}

docker_manifest_list_version_os_arch() {
  # Manifest Create alpine-amd64
  echo "DOCKER MANIFEST: Create and Push docker manifest list - $TARGET:$BUILD_VERSION-alpine-amd64."
  docker manifest create $TARGET:$BUILD_VERSION-alpine-amd64 \
      $TARGET:$BUILD_VERSION-alpine-amd64

  # Manifest Push alpine-amd64
  docker manifest push $TARGET:$BUILD_VERSION-alpine-amd64

  # Manifest Create alpine-arm32v7
  echo "DOCKER MANIFEST: Create and Push docker manifest list - $TARGET:$BUILD_VERSION-alpine-arm32v7."
  docker manifest create $TARGET:$BUILD_VERSION-alpine-arm32v7 \
      $TARGET:$BUILD_VERSION-alpine-arm32v7

  # Manifest Annotate alpine-arm32v7
  docker manifest annotate $TARGET:$BUILD_VERSION-alpine-arm32v7 $TARGET:$BUILD_VERSION-alpine-arm32v7 --os=linux --arch=arm --variant=v6

  # Manifest Push alpine-arm32v7
  docker manifest push $TARGET:$BUILD_VERSION-alpine-arm32v7

  # Manifest Create alpine-arm64v8
  echo "DOCKER MANIFEST: Create and Push docker manifest list - $TARGET:$BUILD_VERSION-alpine-arm64v8."
  docker manifest create $TARGET:$BUILD_VERSION-alpine-arm64v8 \
      $TARGET:$BUILD_VERSION-alpine-arm64v8

  # Manifest Annotate alpine-arm64v8
  docker manifest annotate $TARGET:$BUILD_VERSION-alpine-arm64v8 $TARGET:$BUILD_VERSION-alpine-arm64v8 --os=linux --arch=arm64 --variant=v8

  # Manifest Push alpine-amd64
  docker manifest push $TARGET:$BUILD_VERSION-alpine-arm64v8
}


prepare_qemu(){
    echo "PREPARE: Qemu"
    # Prepare qemu to build non amd64 / x86_64 images
    mkdir -p .docker/_tmp
    docker run --rm --privileged multiarch/qemu-user-static --reset -p yes
    pushd .docker/_tmp &&
    curl -L -o qemu-x86_64-static.tar.gz https://github.com/multiarch/qemu-user-static/releases/download/$QEMU_VERSION/qemu-x86_64-static.tar.gz && tar xzf qemu-x86_64-static.tar.gz &&
    curl -L -o qemu-arm-static.tar.gz https://github.com/multiarch/qemu-user-static/releases/download/$QEMU_VERSION/qemu-arm-static.tar.gz && tar xzf qemu-arm-static.tar.gz &&
    curl -L -o qemu-aarch64-static.tar.gz https://github.com/multiarch/qemu-user-static/releases/download/$QEMU_VERSION/qemu-aarch64-static.tar.gz && tar xzf qemu-aarch64-static.tar.gz &&
    popd
}

main $1
