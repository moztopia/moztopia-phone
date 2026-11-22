# Contributing

Read the [wiki](https://github.com/moztopia/moztopia-phone/wiki), [specification](docs/implementation-specification.md) and [current decisions](docs/specification-decisions.md). Open a bounded issue before changing the public contract. Keep numbering rules in the maintained upstream engine and add observable contract tests alongside changes.

Use supported PHP and run `composer install`, `composer validate --strict`, `composer test`, `composer analyse` and `composer lint`. Execute the clean-app procedure before claiming Composer discovery. Test minimum and latest compatible dependencies; do not commit lockfiles, vendor files, personal input, local app fixtures or the nested wiki checkout.

Changes use issue branches and PRs into develop. Explain scope, compatibility impact, source version and actual validation. Keep README brief; maintain the wiki through the ignored local Git checkout as the authoritative consumer documentation, then align the concise consumer AGENTS. No release/tag/publication without explicit authorization.

Treat contributors respectfully, discuss evidence and behavior, and avoid personal attacks. See CODE_OF_CONDUCT.md and SECURITY.md.
