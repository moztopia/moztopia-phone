#!/bin/sh
set -eu

phone_example_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
docker compose --project-name moztopia-phone-examples -f "$phone_example_dir/docker-compose.yaml" down --volumes --timeout 5
