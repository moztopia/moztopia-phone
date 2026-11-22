# Verification evidence

Source 0.4.0 prepares the initial package for reviewed release, including disposable agent examples. No Git tag, GitHub release or Packagist publication is authorized. The specification document version is 1.0; source integrations follow the numeric source policy.

## Disposable localhost examples at source 0.4.0

The owner requested two small agent recipes in one self-contained Laravel Docker application at localhost:8080, with teardown after testing and an explicit cloudflared exception. The fixture is under examples/laravel; it is not a product application scaffold and does not add SDK runtime routes or migrations. Its scoped AGENTS supplies exact execution instructions; the wiki Examples page is the authoritative walkthrough.

- The final PHP 8.4.27 Alpine / Laravel 13.43.0 / libphonenumber 9.0.43 runtime passed all 14 actual HTTP cases: auto-discovery, basic SDK operations, extension preservation, Eloquent normalization/persistence/read-back, UUID identity, optional/null and mobile fields, missing/empty/invalid/mixed values, extension storage rejection and ambiguous mobile rejection.
- The success path removed the container, network and tmpfs SQLite database. A forced host HTTP-client failure exited 42 and cleaned up; a real SIGTERM exited 143 and cleaned up. An existing manual session was refused without being stopped. Port 8080 was verified free after teardown. A localhost browser snapshot independently confirmed the JSON operations response; no browser was used for wiki work.
- The final image was approximately 225 MiB uncompressed on this workstation. A separate build stage retains Composer/Git/compilers outside the runtime; one non-root server container uses no external database, queue or tunnel. The build still downloads normal public dependencies. Cached images remain after teardown; test data does not.
- This workstation's Docker default address pools were exhausted. A verified unused small private subnet, 10.253.247.0/28, avoids consuming a large default pool. PHONE_EXAMPLE_SUBNET can override it when another machine has a route conflict. No unrelated Docker network was pruned or reconfigured.
- Existing package verification passed: 97 Pest tests / 504 assertions, maximum-level PHPStan, strict Composer validation and Pint. POSIX shell syntax, Compose configuration, Actionlint and diff whitespace checks passed. The original SDK runtime source is unchanged.
- Twenty Markdown documents, including all five wiki pages and the scoped example instructions, were rendered locally with CommonMark; 59 local/wiki/source link targets were checked. The existing executable documentation checks also cover the unchanged PHP quick-start recipes. Final source/CI/wiki revisions and integration evidence are recorded on issue #11; no unrun future checks are claimed here.

The new CI job builds the actual image, exercises the published localhost port and verifies container/network/volume removal plus an unresponsive port afterward. SIGKILL, host termination or an unavailable Docker daemon cannot be repaired by a shell trap; manual down.sh cleanup remains documented. One example session runs at a time. All data uses documented synthetic examples.

## Local compatibility matrix at source 0.3.0

All six combinations below passed strict Composer validation, 96 Pest tests / 502 assertions, maximum-level PHPStan and Pint. Each dependency set was resolved separately with Composer update (lowest uses --prefer-lowest --prefer-stable), then verification ran in an isolated Docker CLI container with network disabled. Engine: libphonenumber-for-php 9.0.43 in every set.

- PHP 8.3.36, lowest: Laravel v13.40.0, Pest v3.8.5, PHPStan 2.1.0, Pint v1.25.0; PASS.
- PHP 8.3.36, latest: Laravel v13.43.0, Pest v3.8.7, PHPStan 2.3.7, Pint v1.32.3; PASS.
- PHP 8.4.27, lowest: Laravel v13.40.0, Pest v3.8.5, PHPStan 2.1.0, Pint v1.25.0; PASS.
- PHP 8.4.27, latest: Laravel v13.43.0, Pest v3.8.7, PHPStan 2.3.7, Pint v1.32.3; PASS.
- PHP 8.5.13, lowest: Laravel v13.40.0, Pest v3.8.5, PHPStan 2.1.0, Pint v1.25.0; PASS.
- PHP 8.5.13, latest: Laravel v13.43.0, Pest v3.8.7, PHPStan 2.3.7, Pint v1.32.3; PASS.

The minimum sets are the lowest versions Composer could resolve under the declared constraints and current security-advisory policy, not every historical release in Laravel 13. Lowest sets on PHP 8.4/8.5 emit an upstream Symfony translation implicit-nullability deprecation during autoload. Tests/checks still pass; this warning is recorded rather than suppressed. Latest sets do not require that older dependency.

## Contract coverage

- Exact service method names, order, defaults and return types: reflection contract plus real named-argument service/facade calls. Every public method is exercised.
- Parsing, strict lexical validation, safe parsing, validity versus possibility, unsupported explicit regions, missing context, IDD prefixes, supported digits/whitespace/URI subset, all eight regions, malformed/empty/overlength values: tests/Unit/PhoneServiceTest.php.
- Value immutability, full extension preservation/rejection, E.164 representability, significant zeros, exact identifier equality and classification: unit tests. An explicitly controlled engine fixture tests metadata-valid overlength identity without falsifying live numbering rules.
- Extraction: upstream-only feasibility spike; duplicate ordering, supported match filtering, exact byte boundaries, 100/101 results and injected iterator failure after a valid accumulated match. No partial list is returned. See extraction-feasibility.md for observed performance and the absence of a universal CPU-time guarantee.
- Laravel provider/container/facade, actual constructor injection, non-implicit required/nullable behavior, mixed types, mobile ambiguity, English and overridden translations, and real HTTP middleware: tests/Feature/LaravelIntegrationTest.php.
- Eloquent assignment, real SQLite persistence/reload, structural-only retrieval, absolute-anchor regressions (including actual trailing newline), nulls, idempotency, JSON/array serialization, bypassed raw writes, and controlled metadata changes: feature tests. No package database tables/migrations are shipped.

## Independent agent acceptance

A separate unfamiliar agent used only the candidate README, AGENTS, wiki usage guide and installation procedure; it did not inspect package source, tests, internal adapter or upstream docs. Its own consumer script passed 44/44 checks on PHP 8.4.27 / Laravel 13.43.0 with Docker --network none, including real SQLite persistence and serialization. It demonstrated all requested integration tasks and explained validity versus ownership/reachability plus extraction limits.

The independent run first exposed recursive path-mirroring of generated acceptance applications. The helper now stages a Composer archive with generated-directory exclusions and mirrors the source snapshot. The agent then identified an unclear extension-exception description; the authoritative wiki and AGENTS now explicitly state UnrepresentablePhoneNumberException for valid extension-bearing normalization/casting. Both findings were repaired and retested. A focused assessment also verified the AI-first README → AGENTS → wiki task-guide path; its wording was clarified to distinguish package-owned migrations from host schema changes required by the consuming application. The reproducible fallback remains in integration-acceptance.md. Local transcripts/results are preserved under ignored .verification/; the final signed issue records acceptance evidence.

## Documentation and distribution

- README is a brief gateway. The owner explicitly made the wiki authoritative and AI agents the primary audience. Root AGENTS is the first entry point; wiki Agent Integration supplies task-to-call recipes and the Usage Guide is the full API/behavior reference. Current decisions are recorded in specification-decisions.md.
- Documentation examples are cross-checked against public-contract tests and the independent consumer execution. Stored values, formats and extension examples use synthetic/upstream documentation numbers; no network telephone verification occurs.
- Composer runtime dependencies are the explicitly used Illuminate 13 components, ctype/mbstring and libphonenumber-for-php ^9.0.43. Dev tools remain require-dev. Apache-2.0 LICENSE, NOTICE and retained inspected engine/locale licenses are present. Source archives exclude generated apps, vendor, caches, local wiki and lockfiles.
- The confirmed wiki is edited only in the ignored local wiki/ Git repository. Local CommonMark rendering/link checks and the eventual wiki revision are recorded on the implementation issue. No browser wiki work is performed.

## Clean application, examples and CI

The maintainer's revised clean-app procedure passed all 17 checks in a fresh Laravel 13.43.0 application on PHP 8.4.27, using a staged source-only path mirror and normal Composer discovery. The installed runtime checks were repeated with Docker --network none and passed. The separate agent independently installed another clean app and passed its own 44 checks.

All 11 executable PHP blocks in README, AGENTS and the two wiki guides ran successfully against the installed clean app. The documented model method snippets were evaluated in their stated model context. The four wiki Markdown files were rendered locally with League CommonMark; relative wiki targets and source-file links were checked. Generated HTML is kept under ignored .verification/wiki-render. No browser was used to create or test the wiki; browser-level presentation was not claimed.

[GitHub Actions run 45046902154](https://github.com/moztopia/moztopia-phone/actions/runs/45046902154) passed all seven jobs at candidate commit 1f7e0c097fbee97163431796fc346cd5a6aa75f7: six PHP/dependency combinations plus genuine clean-app installation and source guide execution. Actionlint also passed. Subsequent verification-record updates do not change the SDK implementation; the final PR/issue records the reviewed head, its checks, integrated source and published wiki revision.

## Final scalar-typing clarification

A strict-file runtime audit additionally confirmed that Laravel facade magic forwarding may coerce scalar values before typed service dispatch. Direct constructor-injected PhoneService calls enforce their string signatures under a strict caller. Agent instructions and the wiki now explicitly recommend direct typed-service calls for that guarantee, while rules/casts reject mixed values through their framework interfaces. Source patch 0.3.1 adds a regression test and documentation only; src/ runtime files are unchanged from the fully verified 0.3.0 integration. Final patch test counts and CI revisions are recorded on issue #5.

## Release readiness

The implemented public API, integration tests, local compatibility matrix, executable documentation, independent-agent scenario, licenses and clean installation meet the technical acceptance requirements. README is intentionally brief and the wiki is authoritative under the owner's later clarification. Remaining inherent limits are explicit: metadata cannot prove ownership/reachability, supported extraction coverage follows upstream boundaries, no strict CPU-time guarantee exists, and weak PHP callers can coerce scalar arguments. The recorded lowest-dependency upstream deprecation is not suppressed.

Initial source is prepared for a reviewed release. Git tags, GitHub releases and Packagist publication remain prohibited pending explicit owner authorization. Repository/Project closure is completed only after final source integration and local-Git wiki publication are verified, with exact revisions on issue #5.

by-dolly
