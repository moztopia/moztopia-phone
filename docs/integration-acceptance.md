# Clean application and AI integration acceptance

The [wiki](https://github.com/moztopia/moztopia-phone/wiki) is the authoritative consumer documentation. The brief README and consumer AGENTS point there. This file is executable verification guidance for maintainers.

## Clean Laravel installation

From this package root, in a supported PHP CLI environment with Composer and SQLite available, execute:

```sh
sh tools/verify-clean-app.sh
```

The script must be executed, not merely read as reference. It refuses an existing destination, creates a new Laravel 13 application under ignored `.verification/laravel`, stages a source-only Composer archive (excluding generated applications, dependencies and the local wiki), adds a local Composer path repository pointing to that snapshot, and installs `moztopia/phone` using a source-only version from `VERSION`. This local version is test metadata, not a Git tag or published release. An alternative absent destination can be passed as the first argument.

The package is installed as a path mirror of that source snapshot rather than a source symlink. Never mirror the whole working checkout directly: it contains the generated acceptance apps and can recursively copy their temporary files. Normal Composer post-autoload discovery runs; no package provider is manually registered. The acceptance script boots the real application and verifies discovery, facade, shared DI, Thai normalization, international validation, extension formatting, required/nullable behavior, translations, comparison, extraction, model casting, nulls, serialization and metadata-independent stored reads. The synthetic model needs no package-owned table or migration to exercise attributes. Database persistence is separately verified by the isolated Testbench SQLite fixtures.

Successful output is a JSON object in which every check is true. Dependencies may be downloaded during Composer installation; the runtime tests call no external telephone service. Re-run `php tools/clean-app-acceptance.php /absolute/path/to/the/clean/app` with network disabled to verify that property.

## Independent-agent scenario

Give an unfamiliar coding agent only the final README, AGENTS and wiki usage guide, plus access to a supported PHP/Composer environment and an absent test-app destination. Do not provide source files, implementation hints, upstream documentation or the internal adapter.

Ask it to install via the documented pre-publication path repository and demonstrate: Thai normalization; international validity; extension-preserving formatting; normal Laravel validation including required/nullable; E.164 model casting; invalid input handling; and an explanation that validity is not ownership/reachability. Ask it to explain extraction policy and limits. Require a real command transcript and pass/fail output. It must not inspect package source.

The fixture/procedure is available even when a separate agent cannot be run. Claim independent-agent success only when a separate execution actually occurred; record its evidence in verification.md. Any failure should repair consumer documentation or implementation and be retested, rather than adding hidden source knowledge to the agent prompt.

The exact source specification is preserved in implementation-specification.md; current owner clarifications are in specification-decisions.md. Release-readiness evidence is separate from authorization to tag or publish.
