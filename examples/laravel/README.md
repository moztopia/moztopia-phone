# Disposable Laravel examples

AI agents: read [AGENTS.md](AGENTS.md), then run this from the package checkout:

```sh
sh examples/laravel/test.sh
```

Requires Docker Engine, Docker Compose v2+ and host `curl`. No host PHP or Composer is needed. The first build downloads PHP, Composer and dependencies. The test starts Laravel at **http://localhost:8080**, exercises both JSON examples, then removes its container, network and temporary SQLite database on success, failure or a handled interruption. It refuses an already running example session. Run one session at a time and keep port 8080 free.

To inspect the responses manually:

```sh
sh examples/laravel/up.sh
curl -H 'Accept: application/json' http://localhost:8080/examples/basic
curl -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"phone":"0812345678","mobile":null}' http://localhost:8080/examples/contacts
sh examples/laravel/down.sh
```

The two examples are [SDK operations](routes/basic.php) and [Laravel validation with Eloquent casting](routes/validation-and-casting.php). The latter persists to SQLite only inside the disposable container. Its application-owned migration demonstrates storage; the SDK itself has no migrations.

The **[wiki Examples guide](https://github.com/moztopia/moztopia-phone/wiki/Examples)** is the authoritative walkthrough. This is a small consumer fixture, not an application scaffold or production deployment. The owner explicitly exempted these localhost Docker examples from the cloudflared requirement. No tunnel or external database is used. The image cache remains available for faster later runs; example data does not survive shutdown.

The example uses the small private subnet `10.253.247.0/28` rather than consuming a large Docker default pool. If it conflicts with your local routes, set `PHONE_EXAMPLE_SUBNET` to an unused private CIDR for both startup/test and shutdown. It is an optional Docker setting, not SDK configuration.

by-dolly
