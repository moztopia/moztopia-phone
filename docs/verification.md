# Verification evidence

Source 0.3.0 prepares the initial package for reviewed release. No Git tag, GitHub release or Packagist publication is authorized. The specification document version is 1.0; source integrations follow the numeric source policy.

## Local compatibility matrix

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

## Final gates pending at this checkpoint

The maintainer clean-app rerun and remote GitHub Actions verification are in progress. Local six-way compatibility and the separate agent clean-app run have passed. Final CI, clean-app, wiki publication and source integration evidence will be appended before completion is claimed.

by-dolly
