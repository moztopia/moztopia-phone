#!/bin/sh
set -eu

phone_example_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
phone_compose_file=$phone_example_dir/docker-compose.yaml

command -v curl >/dev/null 2>&1 || { echo 'curl is required on the host.' >&2; exit 1; }
docker compose version >/dev/null

# Never take ownership of an existing example session.
if [ -n "$(docker ps -aq --filter label=com.docker.compose.project=moztopia-phone-examples)" ] ||
    [ -n "$(docker network ls -q --filter label=com.docker.compose.project=moztopia-phone-examples)" ]; then
    echo 'An example session already exists. Run down.sh before this test.' >&2
    exit 1
fi

phone_cleanup() {
    phone_exit_status=$?
    trap - 0 HUP INT TERM
    sh "$phone_example_dir/down.sh" || phone_exit_status=1
    exit "$phone_exit_status"
}
trap phone_cleanup 0
trap 'exit 129' HUP
trap 'exit 130' INT
trap 'exit 143' TERM

sh "$phone_example_dir/up.sh"

phone_ready=false
phone_wait=0
while [ "$phone_wait" -lt 45 ]; do
    if curl --silent --fail --max-time 1 http://localhost:8080/ >/dev/null; then
        phone_ready=true
        break
    fi
    phone_wait=$((phone_wait + 1))
    sleep 1
done
if [ "$phone_ready" != true ]; then
    docker compose --project-name moztopia-phone-examples -f "$phone_compose_file" logs --tail 40 server
    echo 'The example did not become ready on localhost:8080.' >&2
    exit 1
fi

phone_check() {
    phone_case=$1
    phone_expected_status=$2
    shift 2
    phone_response=$(curl --silent --show-error --max-time 10 -w '%{http_code}' \
        -H 'Accept: application/json' "$@")
    phone_body=${phone_response%???}
    phone_status=${phone_response#"$phone_body"}
    if [ "$phone_status" != "$phone_expected_status" ]; then
        echo "$phone_case: expected HTTP $phone_expected_status; received $phone_status." >&2
        exit 1
    fi
    printf '%s' "$phone_body" | docker compose --project-name moztopia-phone-examples -f "$phone_compose_file" exec -T server \
        php tests/assert-response.php "$phone_case"
}

phone_check discovery 200 http://localhost:8080/
phone_check basic 200 http://localhost:8080/examples/basic
phone_contact_id=$(phone_check stored 201 -H 'Content-Type: application/json' \
    -d '{"phone":"0812345678","mobile":null}' http://localhost:8080/examples/contacts)
phone_check retrieved 200 "http://localhost:8080/examples/contacts/$phone_contact_id"
phone_check mobile 201 -H 'Content-Type: application/json' \
    -d '{"phone":"+16502530000","mobile":"0812345678"}' http://localhost:8080/examples/contacts
phone_check optional 201 -H 'Content-Type: application/json' \
    -d '{"phone":"0812345678"}' http://localhost:8080/examples/contacts

for phone_payload in '{}' '{"phone":""}' '{"phone":"   "}' '{"phone":"invalid"}' \
    '{"phone":66812345678}' '{"phone":["0812345678"]}' \
    '{"phone":"+16502530000 ext. 0012"}'; do
    phone_check invalid-phone 422 -H 'Content-Type: application/json' \
        -d "$phone_payload" http://localhost:8080/examples/contacts
done
phone_check invalid-mobile 422 -H 'Content-Type: application/json' \
    -d '{"phone":"0812345678","mobile":"+16502530000"}' http://localhost:8080/examples/contacts

echo 'PASS: 14 HTTP cases; example resources will now be removed.'
