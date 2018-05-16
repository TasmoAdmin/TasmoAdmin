#!/bin/bash
set -o errexit

main() {
    case $1 in
        "prepare")
            docker_prepare
            ;;
        "build")
            docker_build
            ;;
        "test")
            docker_test
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
    esac
}

docker_prepare() {
    # Prepare the machine before any code installation scripts
    setup_dependencies

    # Update docker configuration to enable docker manifest command
    update_docker_configuration

    # Prepare qemu to build images other then x86_64 on travis
    prepare_qemu
}

docker_build() {
    echo "DOCKER BUILD: Build all docker images."
    docker build --build-arg BASE_IMAGE=amd64/alpine   --build-arg QEMU_ARCH=x86_64 --file ./.docker/Dockerfile.alpine-tmpl --tag $IMAGE:build-$SONWEB_VERSION-alpine-amd64 .
    docker build --build-arg BASE_IMAGE=arm32v6/alpine --build-arg QEMU_ARCH=arm    --file ./.docker/Dockerfile.alpine-tmpl --tag $IMAGE:build-$SONWEB_VERSION-alpine-arm32v6 .
    docker build --build-arg BASE_IMAGE=arm64v8/alpine --build-arg QEMU_ARCH=aarch64    --file ./.docker/Dockerfile.alpine-tmpl --tag $IMAGE:build-$SONWEB_VERSION-alpine-arm64v8 .
}

docker_test() {
    echo "DOCKER TEST: Test all docker images."
    docker run -d --name=test-$SONWEB_VERSION-alpine-amd64 $IMAGE:build-$SONWEB_VERSION-alpine-amd64
    if [ $? -ne 0 ]; then
        echo "ERROR: Docker container failed to start for build-$SONWEB_VERSION-alpine-amd64 ."
        exit 1
    fi
    docker stop test-$SONWEB_VERSION-alpine-amd64 && docker rm test-$SONWEB_VERSION-alpine-amd64

    docker run -d --name=test-$SONWEB_VERSION-alpine-arm32v6 $IMAGE:build-$SONWEB_VERSION-alpine-arm32v6
    if [ $? -ne 0 ]; then
        echo "ERROR: Docker container failed to start for build-$SONWEB_VERSION-alpine-arm32v6 ."
        exit 1
    fi
    docker stop test-$SONWEB_VERSION-alpine-arm32v6 && docker rm test-$SONWEB_VERSION-alpine-arm32v6

    docker run -d --name=test-$SONWEB_VERSION-alpine-arm64v8 $IMAGE:build-$SONWEB_VERSION-alpine-arm64v8
    if [ $? -ne 0 ]; then
        echo "ERROR: Docker container failed to start for build-$SONWEB_VERSION-alpine-arm64v8 ."
        exit 1
    fi
    docker stop test-$SONWEB_VERSION-alpine-arm64v8 && docker rm test-$SONWEB_VERSION-alpine-arm64v8
}

docker_tag() {
    echo "DOCKER TAG: Tag all docker images."
    docker tag $IMAGE:build-$SONWEB_VERSION-alpine-amd64 $IMAGE:$SONWEB_VERSION-alpine-amd64
    docker tag $IMAGE:build-$SONWEB_VERSION-alpine-amd64 $IMAGE:latest-alpine-amd64

    docker tag $IMAGE:build-$SONWEB_VERSION-alpine-arm32v6 $IMAGE:$SONWEB_VERSION-alpine-arm32v6
    docker tag $IMAGE:build-$SONWEB_VERSION-alpine-arm32v6 $IMAGE:latest-alpine-arm32v6

    docker tag $IMAGE:build-$SONWEB_VERSION-alpine-arm64v8 $IMAGE:$SONWEB_VERSION-alpine-arm64v8
    docker tag $IMAGE:build-$SONWEB_VERSION-alpine-arm64v8 $IMAGE:latest-alpine-arm64v8
}

docker_push() {
    echo "DOCKER PUSH: Push all docker images."
    docker push $IMAGE:$SONWEB_VERSION-alpine-amd64
    #docker push $IMAGE:latest-alpine-amd64

    docker push $IMAGE:$SONWEB_VERSION-alpine-arm32v6
    #docker push $IMAGE:latest-alpine-arm32v6

    docker push $IMAGE:$SONWEB_VERSION-alpine-arm64v8
    #docker push $IMAGE:latest-alpine-arm64v8
}

docker_manifest_list() {
    # Create and push manifest lists, displayed as FIFO
    echo "DOCKER MANIFEST: Create and Push docker manifest list."
    docker_manifest_list_version
    docker_manifest_list_latest
}

docker_manifest_list_version() {
    # Manifest Create $SONWEB_VERSION default
    docker manifest create $IMAGE:$SONWEB_VERSION \
        $IMAGE:$SONWEB_VERSION-alpine-amd64 \
        $IMAGE:$SONWEB_VERSION-alpine-arm32v6 \
        $IMAGE:$SONWEB_VERSION-alpine-arm64v8

    # Manifest Annotate SONWEB_VERSION
    docker manifest annotate $IMAGE:$SONWEB_VERSION $IMAGE:$SONWEB_VERSION-alpine-arm32v6 --os=linux --arch=arm --variant=v6
    docker manifest annotate $IMAGE:$SONWEB_VERSION $IMAGE:$SONWEB_VERSION-alpine-arm64v8 --os=linux --arch=arm64 --variant=v8

    # Manifest Push SONWEB_VERSION
    docker manifest push $IMAGE:$SONWEB_VERSION
}

docker_manifest_list_latest() {
    # Manifest Create LATEST
    docker manifest create $IMAGE:latest \
        $IMAGE:$SONWEB_VERSION-alpine-amd64 \
        $IMAGE:$SONWEB_VERSION-alpine-arm32v6 \
        $IMAGE:$SONWEB_VERSION-alpine-arm64v8

    # Manifest Annotate LATEST
    docker manifest annotate $IMAGE:latest $IMAGE:$SONWEB_VERSION-alpine-arm32v6 --os=linux --arch=arm --variant=v6
    docker manifest annotate $IMAGE:latest $IMAGE:$SONWEB_VERSION-alpine-arm64v8 --os=linux --arch=arm64 --variant=v8

    # Manifest Push LATEST
    docker manifest push $IMAGE:latest
}

setup_dependencies() {
  echo "PREPARE: Setting up dependencies."

  sudo apt update -y
  # sudo apt install realpath python python-pip -y
  sudo apt install --only-upgrade docker-ce -y
  # sudo pip install docker-compose || true

  docker info
  # docker-compose --version
}

update_docker_configuration() {
  echo "PREPARE: Updating docker configuration"

  mkdir $HOME/.docker

  # enable experimental to use docker manifest command
  echo '{
    "experimental": "enabled"
  }' | tee $HOME/.docker/config.json

  # enable experimental
  echo '{
    "experimental": true,
    "storage-driver": "overlay2",
    "max-concurrent-downloads": 100,
    "max-concurrent-uploads": 100
  }' | sudo tee /etc/docker/daemon.json

  sudo service docker restart
}

prepare_qemu(){
    echo "PREPARE: Qemu"
    # Prepare qemu to build non amd64 / x86_64 images
    docker run --rm --privileged multiarch/qemu-user-static:register --reset
    mkdir tmp
    pushd tmp &&
    curl -L -o qemu-x86_64-static.tar.gz https://github.com/multiarch/qemu-user-static/releases/download/$QEMU_VERSION/qemu-x86_64-static.tar.gz && tar xzf qemu-x86_64-static.tar.gz &&
    curl -L -o qemu-arm-static.tar.gz https://github.com/multiarch/qemu-user-static/releases/download/$QEMU_VERSION/qemu-arm-static.tar.gz && tar xzf qemu-arm-static.tar.gz &&
    curl -L -o qemu-aarch64-static.tar.gz https://github.com/multiarch/qemu-user-static/releases/download/$QEMU_VERSION/qemu-aarch64-static.tar.gz && tar xzf qemu-aarch64-static.tar.gz &&
    popd
}

main $1
