# Moztopia Phone

**Small tools. Powerful AI.** A headless Laravel SDK for international telephone-number parsing, validation, normalization, formatting, comparison and extraction, powered by libphonenumber-for-php.

The **[wiki is the authoritative documentation](https://github.com/moztopia/moztopia-phone/wiki)**. **AI agents: start with [AGENTS.md](AGENTS.md), then the [agent integration instructions](https://github.com/moztopia/moztopia-phone/wiki/Agent-Integration).** The [usage guide](https://github.com/moztopia/moztopia-phone/wiki/Usage-Guide) covers the complete API, input policy, extensions, errors, validation, casting and extraction limits.

## Quick start

```sh
composer require moztopia/phone
```

```php
use Moztopia\Phone\Facades\Phone;

Phone::normalize('0812345678', 'TH'); // '+66812345678'
Phone::isValid('+66812345678'); // true
Phone::format('0812345678', 'national', 'TH'); // '081 234 5678'
```

Laravel 13 auto-discovers the provider. No package configuration, migrations, setup command, external service or global alias is required. Requires supported PHP 8.3.36+, 8.4.27+ or 8.5.12+ and `ctype`/`mbstring`; [verification evidence](docs/verification.md) records actual tested environments.

**Initial source is not yet published.** Before a tag/Packagist release is authorized, use the [local installation procedure](docs/integration-acceptance.md). Validity never proves ownership or reachability; E.164 normalization rejects extensions.

Runnable agent examples: `sh examples/laravel/test.sh` builds a disposable Laravel application at localhost:8080, checks both workflows, then tears it down. See [example instructions](examples/laravel/AGENTS.md) and the authoritative [wiki Examples guide](https://github.com/moztopia/moztopia-phone/wiki/Examples).

Maintainers: [contributing](CONTRIBUTING.md), [verification](docs/verification.md), [changelog](CHANGELOG.md). Apache-2.0; [LICENSE](LICENSE) and [NOTICE](NOTICE). Google does not publish or endorse this SDK.
