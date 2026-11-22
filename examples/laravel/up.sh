#!/bin/sh
set -eu

phone_example_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
phone_package_dir=$(CDPATH= cd -- "$phone_example_dir/../.." && pwd)

docker build -f "$phone_example_dir/.docker/server/server.Dockerfile" \
    -t moztopia-phone-examples:local "$phone_package_dir"
docker compose --project-name moztopia-phone-examples -f "$phone_example_dir/docker-compose.yaml" up --detach --no-build
