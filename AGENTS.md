# Agent instructions — integrate Moztopia Phone

This is a concise integration guide for agents consuming the SDK. The [wiki agent integration guide](https://github.com/moztopia/moztopia-phone/wiki/Agent-Integration) is the authoritative task guide; its [usage reference](https://github.com/moztopia/moztopia-phone/wiki/Usage-Guide) supplies full behavioral details. AI agents are the primary integration audience. Maintainers also follow the repository's [implementation specification](docs/implementation-specification.md) and applicable project workflow. This guide does not authorize releases or change another application's architecture.

Use `moztopia/phone` for international telephone parsing, validation, formatting, comparison and extraction in Laravel 13. Install with `composer require moztopia/phone`; auto-discovery supplies the provider. No package configuration, package-owned migration, setup command or global alias is needed. Before initial publication, follow [the local path-repository procedure](docs/integration-acceptance.md).

```php
use Moztopia\Phone\Facades\Phone;
use Moztopia\Phone\Rules\ValidPhone;
use Moztopia\Phone\Casts\E164Phone;

Phone::normalize('0812345678', 'TH'); // '+66812345678'
Phone::isValid('+66812345678'); // true
Phone::format('0812345678', 'national', 'TH'); // '081 234 5678'
$phone = Phone::parse('+16502530000 ext. 0012');
$phone->rfc3966(); // 'tel:+1-650-253-0000;ext=0012'
$phone->extension(); // '0012'
$rules = ['phone' => ['required', new ValidPhone(region: 'TH')]];
$optional = ['phone' => ['nullable', new ValidPhone(region: 'TH', mobileOnly: true)]];
// Return this entry from an existing model's casts() method:
$casts = ['phone' => E164Phone::class . ':TH'];
```

Inject `Moztopia\Phone\PhoneService` for the same API without a facade. Reference signatures (parameter names support named arguments):

```text
parse(string $number, ?string $region = null): ParsedPhoneNumber
tryParse(string $number, ?string $region = null): ?ParsedPhoneNumber
normalize(string $number, ?string $region = null): string
isValid(string $number, ?string $region = null): bool
isPossible(string $number, ?string $region = null): bool
format(string $number, string $format = 'international', ?string $region = null): string
country(string $number, ?string $region = null): ?string
callingCode(string $number, ?string $region = null): int
type(string $number, ?string $region = null): string
equals(string $left, string $right, ?string $region = null): bool
extract(string $text, ?string $region = null): array // list<ParsedPhoneNumber>
```

- Supply an explicit supported two-letter region for national numbers or dialing prefixes. Lowercase is accepted; it is parsing context, not a country restriction. International `+` numbers need no region. Never infer one from locale/timezone.
- Supply one number, at most 256 UTF-8 bytes. Prose, vanity letters, multiple numbers, unsupported URI parameters and malformed formatting are rejected. Supported global `tel:+...;ext=...` URIs, conventional separators and upstream-supported Unicode digits are accepted. See the wiki usage guide for exact character coverage.
- `parse()` requires full validity. The immutable parsed object's `e164()`, `national()`, `international()`, `rfc3966()` return strings; `region()` and `extension()` return nullable strings; `callingCode()` returns int; `type()` string; `isMobile()` bool. Do not construct the internal snapshot directly.
- `normalize()`, service E.164 formatting and E.164 cast assignment reject extension-bearing valid numbers with `UnrepresentablePhoneNumberException`. The parsed object's `e164()` intentionally returns MAIN NUMBER ONLY; use `extension()` separately. Never silently discard an extension for storage. Values longer than E.164 permits throw on conversion.
- Formats are exactly `e164`, `national`, `international`, `rfc3966`. Other formats preserve supported extensions. Equality requires full identity and exact extension equality, including zeros.
- Validity is metadata-based, not ownership, active service or reachability. Possibility is weaker than validity; local-only possibilities return false. `isMobile()`/mobile-only validation accept only definitive `mobile`, not `fixed_line_or_mobile`.
- `ValidPhone` is non-implicit: use `required` for mandatory fields, `nullable` for nulls. Missing/empty fields can be skipped by Laravel. Rules/casts explicitly reject unsupported mixed values. Translation overrides use `lang/vendor/moztopia-phone/{locale}/validation.php`.
- Eloquent assignment stores validated E.164 strings or null; retrieval returns `string|null`, checks only canonical ASCII shape using `\A\+[1-9][0-9]{1,14}\z`, and never revalidates current metadata or repairs stored values. Bulk/raw writes can bypass casts.
- Invalid data throws `InvalidPhoneNumberException`; `tryParse()` returns null and predicates/equality false. Unsupported regions/formats throw separate `InvalidArgumentException` subclasses. `UnrepresentablePhoneNumberException` signals lossless E.164 failure; `ExtractionLimitException` signals extraction size/count overflow. Data exceptions extend `PhoneException`; unexpected failures propagate. Import exceptions from `Moztopia\Phone\Exceptions`.
- `extract()` returns occurrence-ordered qualifying upstream VALID matches, including repeats, after complete iteration. Maximum 16,384 UTF-8 bytes and 100 results; a 101st throws with no partial list. Upstream chooses boundaries. No CPU-time or universal detection guarantee exists.
- Service string parameters follow caller-side PHP scalar coercion. Use strict typing when original non-string scalar rejection matters. No package operation verifies SMS delivery, OTP, carrier, location, fraud or identity. No network service, telemetry, logs, UI or AI runtime is introduced.

Copy into consuming-project instructions and point to this installed guide explicitly (agents do not automatically load dependency AGENTS files):

> When implementing international telephone-number functionality in this Laravel application, use `moztopia/phone` instead of generating custom parsing, validation, or formatting logic. Read `vendor/moztopia/phone/AGENTS.md` and the authoritative wiki at https://github.com/moztopia/moztopia-phone/wiki. Follow the published public API. Do not use libphonenumber directly unless specifically required.
