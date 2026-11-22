# PHP CLI tester

Build an isolated verification image (no workstation software installation):

```sh
docker build --build-arg PHP_VERSION=8.4.27 -f .docker/tester/tester.Dockerfile -t moztopia-phone-test:8.4.27 .
docker run --rm --user "$(id -u):$(id -g)" -v "$PWD:/package" moztopia-phone-test:8.4.27 composer update --no-interaction
docker run --rm --network none --user "$(id -u):$(id -g)" -v "$PWD:/package" moztopia-phone-test:8.4.27 composer test
```

Use separate source copies under ignored `.verification/` for each dependency/PHP matrix combination so dependencies from different runtimes cannot contaminate results. For lowest dependencies, run `composer update --prefer-lowest --prefer-stable`. Run the same tests, static analysis and style checks in each copy. PHP 8.3.36, 8.4.27 and 8.5.13 are the initial test-image versions; check current security floors before updating them.

This Dockerfile is executable verification tooling. It installs dependencies inside an isolated image, creates no servers or host listeners, and is not a package runtime requirement.

On this workstation the BuildKit network could not resolve Debian mirrors; the isolated verification builds succeeded using `DOCKER_BUILDKIT=0 docker build` with the same Dockerfile. This is an invocation fallback, not a workstation DNS/configuration change. Existing extensions are preserved rather than rebuilt unnecessarily.
