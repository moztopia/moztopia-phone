# MOZTOPIA PHONE
## Complete Implementation Specification — Version 1.0

### Project Identity

| Property | Value |
|---|---|
| Product | Moztopia Phone |
| GitHub repository | `moztopia/moztopia-phone` |
| Composer package | `moztopia/phone` |
| PHP namespace | `Moztopia\Phone` |
| Framework | Laravel 13.x |
| Minimum PHP | 8.3 |
| Target test matrix | PHP 8.3, 8.4, 8.5 |
| Telephone engine | `giggsey/libphonenumber-for-php` 9.x |
| Package type | Headless Laravel SDK |
| Default license | Apache 2.0 |
| Testing framework | Pest |

The target test matrix represents intended compatibility coverage, not completed testing. Advertise a version as tested only after its tests have passed.

---

# 1. Mission and Product Philosophy

Create a production-ready, headless, AI-friendly Laravel SDK that provides international telephone-number parsing, validation, normalization, formatting, identification, comparison, and extraction through a small, stable, predictable public API.

The package wraps the PHP implementation of Google's libphonenumber.

Do not independently implement international telephone-number rules.

**Moztopia Phone is a small public interface over a mature, sophisticated implementation.**

The package is intended for developers and AI coding agents building applications.

An AI agent should be able to install Moztopia Phone, understand its documentation, and immediately use it without exploring its internals or learning international numbering standards.

## Fundamental principles

1. **One responsibility.** Telephone-number interpretation and manipulation.
2. **Complete within scope.** Handle validation, formatting, errors, edge cases, and Laravel integration.
3. **Small public API.** Expose only practical, clearly defined operations.
4. **Minimal integration.** Work in existing Laravel applications without architectural changes.
5. **Predictable behavior.** Invalid and ambiguous inputs have consistent outcomes.
6. **Mature foundations.** Delegate numbering rules to libphonenumber.
7. **AI-friendly documentation.** Agents should integrate the package using published instructions.
8. **No UI.** Do not implement forms, widgets, frontend components, or application interfaces.
9. **No speculative architecture.** Avoid unnecessary abstraction, extensibility, and configuration.
10. **Reliable releases.** Comprehensive tests and an explicitly verified public contract are mandatory.

### Product positioning

**Moztopia — Small tools. Powerful AI.**

Moztopia packages are narrowly focused, self-contained SDKs that provide complete technical capabilities an AI coding agent can use rather than developing from scratch.

AI-friendly does not mean the package requires an AI service.

Moztopia Phone must remain a conventional PHP/Laravel package usable by any developer.

---

# 2. Technology and Compatibility

## 2.1 Laravel compatibility

Target Laravel 13.x only for the initial release.

Do not advertise Laravel 14 or later until explicitly tested.

Use appropriate Composer version constraints such as `^13.0` for required Illuminate components.

Avoid requiring the entire Laravel framework if smaller Illuminate dependencies are sufficient.

The actual resolved Composer dependencies and successful integration tests establish compatibility.

## 2.2 PHP compatibility

Minimum PHP version: 8.3.

Target the following test matrix:

- PHP 8.3
- PHP 8.4
- PHP 8.5

Use PHP features compatible with the minimum supported version.

Do not assume that a target version is supported until its compatibility tests pass.

Declare required PHP extensions through Composer.

## 2.3 Underlying engine

Use:

```text
giggsey/libphonenumber-for-php
```

Target its compatible 9.x release family.

Use the full package because telephone-number extraction is required.

The library provides:

- International numbering metadata.
- Parsing.
- Formatting.
- Number validity.
- Possible-number checks.
- Number classification.
- Calling codes.
- Region identification.
- Number matching and extraction.

Do not duplicate this functionality.

## 2.4 Dependency isolation

Keep libphonenumber behind a small internal boundary.

Consuming applications must not need to instantiate, import, or understand upstream classes.

Do not expose upstream mutable phone-number objects or exceptions.

Do not create an elaborate provider, adapter registry, or plugin system.

A single internal adapter is acceptable if it meaningfully protects the public API.

The objective is to preserve Moztopia Phone's public contract if the underlying library eventually changes.

Do not fork libphonenumber at this stage.

---

# 3. Installation Requirements

Installation:

```bash
composer require moztopia/phone
```

The package must work immediately inside an ordinary Laravel 13 application.

No required:

- Artisan installation command.
- Published configuration.
- Database migration.
- Manual service provider registration.
- Environment variable.
- API key.
- External service.
- Background worker.
- Frontend setup.

Use Laravel package auto-discovery.

Register the main service in Laravel's service container.

Expose a facade and support dependency injection.

Do not automatically register a global `Phone` class alias.

---

# 4. Authoritative Public API

Implement the following public contract:

```php
namespace Moztopia\Phone;

final class PhoneService
{
    public function parse(
        string $number,
        ?string $region = null
    ): ParsedPhoneNumber;

    public function tryParse(
        string $number,
        ?string $region = null
    ): ?ParsedPhoneNumber;

    public function normalize(
        string $number,
        ?string $region = null
    ): string;

    public function isValid(
        string $number,
        ?string $region = null
    ): bool;

    public function isPossible(
        string $number,
        ?string $region = null
    ): bool;

    public function format(
        string $number,
        string $format = 'international',
        ?string $region = null
    ): string;

    public function country(
        string $number,
        ?string $region = null
    ): ?string;

    public function callingCode(
        string $number,
        ?string $region = null
    ): int;

    public function type(
        string $number,
        ?string $region = null
    ): string;

    public function equals(
        string $left,
        string $right,
        ?string $region = null
    ): bool;

    /**
     * @return list<ParsedPhoneNumber>
     */
    public function extract(
        string $text,
        ?string $region = null
    ): array;
}
```

These signatures are authoritative.

Do not change method names, parameter names, parameter order, or return types without identifying a genuine technical incompatibility.

PHP named arguments make parameter names part of the compatibility contract.

Example:

```php
Phone::format(
    number: $input,
    format: 'national',
    region: 'TH'
);
```

must work.

## 4.1 Input types and PHP coercion

The service API deliberately accepts strings.

However, PHP's scalar argument typing depends on the caller's strict-typing mode.

A caller without `declare(strict_types=1)` may pass certain scalar values that PHP coerces to strings before the service receives them.

Declaring strict types inside Moztopia Phone does not prevent that caller-side coercion.

Therefore:

- Keep the specified string signatures.
- Use `declare(strict_types=1)` in Moztopia source files.
- Document caller-side coercion.
- Recommend strict typing for callers requiring strict scalar enforcement.
- Test strict and weak caller behavior.
- Do not claim every original non-string scalar input can be detected by the service.

Laravel validation rules and Eloquent casts receive mixed values through framework interfaces and must explicitly reject unsupported non-string inputs.

---

# 5. Strict Single-Number Input Policy

All methods except `extract()` operate on exactly one telephone number.

The upstream parser is intentionally permissive and may accept surrounding prose, vanity numbers, and other unintended input.

Moztopia Phone must impose its own input policy before relying on upstream parsing.

## 5.1 Accepted input

Accept:

- International numbers beginning with `+`.
- National-format numbers with an explicit region.
- International dialing prefixes interpretable through the supplied region.
- Conventional spaces.
- Hyphens.
- Periods used as separators.
- Balanced parentheses.
- Supported Unicode decimal digits.
- Explicitly supported telephone extensions.
- Supported global-number RFC 3966 telephone URIs.

Examples:

```text
+66812345678
+66 81 234 5678
0812345678
(02) 123 4567
+1 650 253 0000
+1 650 253 0000 ext 012
tel:+16502530000;ext=012
```

These examples illustrate syntax. Full numbering-plan validity must still be checked.

## 5.2 Rejected input

Reject:

- Empty strings.
- Whitespace-only strings.
- Arbitrary surrounding prose.
- Embedded labels such as `Call me at`.
- Multiple numbers within one input.
- Vanity numbers such as `1-800-FLOWERS`.
- Unbalanced formatting.
- Unsupported telephone URI parameters.
- Local RFC 3966 telephone URIs requiring `phone-context`.
- Emergency numbers.
- Short codes.
- USSD commands.
- Post-dial instructions.
- Unsupported control or invisible characters.
- Malformed Unicode.
- Input exceeding the defined single-number limit.

Do not silently extract a number from otherwise invalid input.

Example:

```php
Phone::isValid(
    'Call me at +66812345678'
);
// false
```

Searching arbitrary text belongs exclusively to `extract()`.

## 5.3 Unicode digits

Support Unicode decimal digits that can be safely and unambiguously normalized to ASCII.

Do not transliterate alphabetic vanity characters into digits.

Do not silently remove unsupported Unicode characters.

Prefer the upstream library's existing normalization capabilities where appropriate.

## 5.4 RFC 3966

Support this limited subset:

```text
tel:+16502530000
tel:+16502530000;ext=012
```

Reject unsupported parameters.

Reject local-number forms requiring `phone-context`.

Do not implement the entire RFC 3966 specification.

## 5.5 Single-number input limit

Maximum: **256 UTF-8 bytes**.

Reject longer input as invalid.

Apply consistently across single-number operations, including both operands of equality comparisons.

Use byte length, not Unicode character count.

---

# 6. Region Semantics

The optional `region` argument supplies parsing context.

**It does not restrict the number's nationality.**

Example:

```php
Phone::isValid(
    '+16502530000',
    'TH'
);
```

A valid US international number must not be rejected merely because Thailand is supplied as the parsing context.

## 6.1 Region identifiers

Accept recognized ISO 3166-1 alpha-2 region codes supported by the underlying numbering metadata.

Examples:

```text
TH
US
GB
AU
DE
JP
```

Accept lowercase equivalents by converting them to uppercase.

Reject:

```text
Thailand
USA
ZZ
001
unknown regions
empty strings
```

An explicitly supplied unsupported region raises:

```text
InvalidRegionException
```

This applies even when the telephone number includes a valid international calling code.

`null` means no default region was provided.

## 6.2 National input without region

National-format input without an explicit region is unresolved.

Never infer a region from:

- Server timezone.
- Application locale.
- Browser locale.
- Device settings.
- IP address.
- User location.
- Laravel configuration.

Use the normal invalid-input behavior.

## 6.3 Geographic interpretation

A number's numbering region does not establish its subscriber's current physical location.

Non-geographic services and ambiguous shared calling codes may not identify an individual country.

Return `null` when a specific region cannot be determined reliably.

---

# 7. Parsing

## 7.1 parse()

```php
$phone = Phone::parse(
    '0812345678',
    'TH'
);
```

Return `ParsedPhoneNumber`.

Parsing succeeds only when:

1. The input satisfies Moztopia's strict single-number syntax rules.
2. The numbering engine considers the number valid under its metadata.

Do not confuse low-level parsing success with complete numbering-plan validity.

## 7.2 tryParse()

```php
$phone = Phone::tryParse(
    $input,
    'TH'
);
```

Return `ParsedPhoneNumber|null`.

Invalid or unresolved input returns `null`.

Unsupported explicit regions still raise `InvalidRegionException`.

Unexpected internal failures propagate.

Do not catch every exception and convert it into `null`.

## 7.3 ParsedPhoneNumber

Implement:

```text
Moztopia\Phone\ParsedPhoneNumber
```

This must be an immutable value object.

Required public methods:

```php
$phone->e164(): string;
$phone->national(): string;
$phone->international(): string;
$phone->rfc3966(): string;
$phone->region(): ?string;
$phone->callingCode(): int;
$phone->type(): string;
$phone->extension(): ?string;
$phone->isMobile(): bool;
```

The object must:

- Be immutable.
- Preserve significant leading zeros.
- Preserve extensions.
- Not expose mutable upstream objects.
- Not allow consumers to modify numbering identity.
- Support repeated formatting without unnecessary reparsing.

## 7.4 E.164 representability

A number can be valid according to numbering metadata without fitting within E.164's maximum international digit length.

Therefore:

- `parse()` may succeed for such a number.
- E.164 conversion independently enforces representability.
- Numbers must never be truncated.

`ParsedPhoneNumber::e164()` returns the main number without extension.

If the main number cannot be represented within E.164, raise `UnrepresentablePhoneNumberException`.

---

# 8. Telephone Extensions

Extensions are meaningful data.

Never silently discard them.

## 8.1 Supported syntax

Support:

```text
ext 123
ext. 123
x123
#123
;ext=123
```

The last form applies to supported RFC 3966 input.

Extensions must contain digits.

Preserve leading zeros.

Example:

```php
$phone->extension();
// '0012'
```

Never convert extensions to integers.

Reject unsupported or ambiguous extension syntax.

## 8.2 normalize()

Reject extension-bearing input:

```php
Phone::normalize(
    '+16502530000 ext 123'
);
```

Raise `UnrepresentablePhoneNumberException`.

E.164 cannot represent telephone extensions.

## 8.3 format(..., 'e164')

Also reject extension-bearing input.

Do not silently return the main number.

## 8.4 ParsedPhoneNumber::e164()

This deliberately returns the main number only.

```php
$phone->e164();
$phone->extension();
```

The extension remains separately accessible.

Document the distinction between this explicitly named value-object method and lossless service-level normalization.

## 8.5 Other formats

National, international, and RFC 3966 formatting must preserve supported extensions using appropriate upstream formatting rules.

## 8.6 Equality

Two telephone identifiers are equal only when:

- Their main international telephone numbers are equivalent.
- Their extensions match exactly.

An absent extension differs from a present extension.

Extension leading zeros are significant.

---

# 9. Normalization

Example:

```php
Phone::normalize(
    '0812345678',
    'TH'
);
// '+66812345678'
```

Return a valid E.164 string.

Requirements:

- Begins with `+`.
- Includes the international calling code.
- Preserves significant digits.
- Contains no formatting separators.
- Contains no extension.
- Contains at most 15 digits after `+`.
- Does not truncate.
- Does not infer country from unrelated context.

Independently enforce E.164 representability.

Do not assume the upstream formatter enforces every limit.

Distinguish invalid input from valid but unrepresentable input.

---

# 10. Validation and Possibility

## 10.1 isValid()

```php
Phone::isValid(
    '0812345678',
    'TH'
);
```

Returns `bool`.

The number must satisfy:

- Strict single-number input policy.
- Full numbering-plan validity.

Invalid input returns `false`.

Unsupported explicit regions raise `InvalidRegionException`.

## 10.2 isPossible()

```php
Phone::isPossible(
    '0812345678',
    'TH'
);
```

Returns `bool`.

Use upstream possible-number rules after strict syntax validation and low-level parsing.

Do not implement `isPossible()` by invoking the validity-enforcing public `parse()` method.

A possible number need not be fully valid.

## 10.3 Local-only numbers

When upstream reports `IS_POSSIBLE_LOCAL_ONLY`, return `false`.

Only fully qualified possible-number results count as possible in the initial public contract.

Document this interpretation.

## 10.4 Meaning of validity

Validity does not prove:

- Number ownership.
- Number assignment.
- Active service.
- SMS reachability.
- Voice reachability.
- Current carrier.
- Subscriber identity.
- Physical location.

Do not integrate external verification services.

---

# 11. Formatting

Supported formats:

```text
e164
national
international
rfc3966
```

Example:

```php
Phone::format(
    number: '0812345678',
    format: 'national',
    region: 'TH'
);
```

Format identifiers are lowercase and case-sensitive.

Unsupported identifiers raise `InvalidFormatException`.

Validate format arguments independently of telephone-number validity.

Use upstream formatting rules.

Do not independently implement country-specific formatting.

## Extension contract

| Format | Extension behavior |
|---|---|
| E.164 | Reject |
| National | Preserve |
| International | Preserve |
| RFC 3966 | Preserve |

Test every case.

---

# 12. Country and Calling Codes

## 12.1 country()

```php
Phone::country('+66812345678');
// 'TH'
```

Return `?string`.

Return `null` when a specific region cannot be determined reliably.

Do not return the non-geographic marker `001` as an ordinary country.

## 12.2 callingCode()

```php
Phone::callingCode('+66812345678');
// 66
```

Return `int`.

The returned value is an international calling code, not a mobile prefix or carrier identifier.

---

# 13. Telephone Number Types

```php
Phone::type('+66812345678');
// 'mobile'
```

Return stable lowercase strings.

Supported mappings:

```text
fixed_line
mobile
fixed_line_or_mobile
toll_free
premium_rate
shared_cost
voip
personal_number
pager
uan
voicemail
unknown
```

Map upstream types explicitly.

Do not expose arbitrary new upstream values without reviewing the public contract.

Emergency numbers, short codes, and USSD are excluded.

## Strict mobile classification

A number is classified as mobile only when:

```php
$type === 'mobile'
```

Do not treat `fixed_line_or_mobile` as definitively mobile.

Apply the same rule in:

- `ParsedPhoneNumber::isMobile()`
- Laravel mobile-only validation.

Document metadata limitations and number portability.

---

# 14. Equality

```php
Phone::equals(
    '0812345678',
    '+66812345678',
    'TH'
);
// true
```

Compare resolved telephone identities, not raw formatting.

Requirements:

- Interpret both inputs using the supplied region context.
- Compare country calling codes.
- Compare national significant numbers.
- Preserve meaningful national leading-zero information.
- Compare extensions exactly.
- Never accept partial matching as equality.
- Return `false` for unresolved or invalid numbers.
- Raise `InvalidRegionException` for an unsupported explicit region.

Do not rely exclusively on E.164 conversion because metadata-valid numbers may be unrepresentable in E.164.

---

# 15. Telephone Number Extraction

Extraction searches arbitrary text.

```php
$phones = Phone::extract(
    $text,
    'TH'
);
```

Return:

```text
list<ParsedPhoneNumber>
```

Use an ordinary sequential PHP array.

Do not return a Laravel Collection or custom collection type.

## 15.1 Matching engine

Use the upstream:

```php
PhoneNumberUtil::findNumbers()
```

with `Leniency::VALID`.

Use the full libphonenumber package.

Do not independently implement international telephone-number scanning.

## 15.2 Accepted extraction match

A match qualifies when:

1. The upstream matcher yields it under `Leniency::VALID`.
2. The extracted candidate satisfies Moztopia's supported single-number syntax policy.
3. The candidate satisfies full numbering-plan validity.
4. The candidate can be represented as a `ParsedPhoneNumber`.

The surrounding text is not subject to the single-number syntax policy.

## 15.3 Completeness definition

A successful extraction returns all qualifying matches yielded by a fully exhausted upstream `Leniency::VALID` matcher over the supplied text.

Completeness is defined relative to this matching policy.

This is not a promise to identify every telephone-number-like substring a human might recognize.

Upstream determines candidate boundaries and ordering.

Document that ambiguous, concatenated, or unusual text may not produce matches.

## 15.4 Candidate attempts

The upstream matcher uses an internal `maxTries` mechanism.

A candidate attempt is an internal matcher validation attempt, including unsuccessful candidate examinations.

The upstream library does not expose a reliable public count of these attempts.

Therefore:

- Do not publish a candidate-attempt limit.
- Do not claim to observe exact upstream attempt counts.
- Do not create a second scanning engine merely to count attempts.
- Do not impose an arbitrary small `maxTries` value.
- Do not interpret ordinary iterator exhaustion as proof that a finite retry budget was not exhausted.

The previously proposed 512-attempt limit is eliminated.

## 15.5 Retry behavior

Use the upstream matcher's effectively unrestricted default retry budget:

```php
PHP_INT_MAX
```

Do not use `maxTries` as the package's resource-limiting mechanism.

The package instead enforces explicit input-size and match-count limits.

The input-size limit bounds text length but does not establish a strict execution-time guarantee.

Do not claim otherwise.

## 15.6 Public limits

| Limit | Value |
|---|---:|
| Maximum input text | 16,384 UTF-8 bytes |
| Maximum returned matches | 100 |

These are fixed initial-release limits.

Do not introduce configuration or tuning APIs for them.

## 15.7 Match-count behavior

Consume the upstream iterator until exhaustion.

Apply the acceptance policy to each yielded candidate.

Preserve accepted matches in occurrence order.

Preserve duplicates.

If a 101st qualifying match is encountered, raise:

```text
ExtractionLimitException
```

Do not return the first 100 as a successful complete result.

Exactly 100 qualifying matches is allowed, but iteration must still complete to confirm there is no 101st.

## 15.8 Input-size behavior

If text exceeds 16,384 UTF-8 bytes, raise `ExtractionLimitException` before invoking the matcher.

Do not truncate input.

## 15.9 Failure behavior

Return extracted results only after the upstream iterator has completed normally.

If iteration fails, propagate the appropriate exception.

Do not return partial results.

Do not add arbitrary preflight rejection rules based on the amount of punctuation, whitespace, or numeric-looking text.

## 15.10 Mandatory extraction feasibility gate

**Before implementing the rest of the SDK, investigate and demonstrate the extraction contract's feasibility.**

Inspect the actual upstream matcher implementation and documented public APIs.

Create a small executable spike covering:

- Normal prose.
- Prose containing no telephone numbers.
- One valid number.
- Multiple valid numbers.
- Repeated numbers.
- Large quantities of invalid numeric candidates.
- Mixed punctuation.
- Unicode.
- Near-limit input.
- More than 100 qualifying matches.
- Extension-bearing matches.
- Ambiguous number boundaries.

Measure representative memory usage and execution time for adversarial inputs.

Do not infer universal runtime guarantees from successful benchmarks.

Verify that:

- The matcher can be consumed completely.
- The package can enforce its match-count limit.
- Ordinary inputs are not unpredictably rejected.
- The documented completeness policy is not misleading.
- The approach does not require substantial custom scanning.

### If feasibility fails

If the extraction contract cannot be achieved reliably through the upstream public API within reasonable complexity:

1. Do not build a custom comprehensive scanner.
2. Do not silently weaken the contract.
3. Document the exact limitation.
4. Recommend the smallest viable change.
5. Stop before undertaking substantial dependent implementation work.

Resolve this feasibility question before building the complete package.

---

# 16. Error Contract

Create package-owned exceptions.

```text
Moztopia\Phone\Exceptions\PhoneException
Moztopia\Phone\Exceptions\InvalidPhoneNumberException
Moztopia\Phone\Exceptions\UnrepresentablePhoneNumberException
Moztopia\Phone\Exceptions\ExtractionLimitException
Moztopia\Phone\Exceptions\InvalidRegionException
Moztopia\Phone\Exceptions\InvalidFormatException
```

## Exception hierarchy

- `PhoneException` extends `RuntimeException`.
- `InvalidPhoneNumberException` extends `PhoneException`.
- `UnrepresentablePhoneNumberException` extends `PhoneException`.
- `ExtractionLimitException` extends `PhoneException`.
- `InvalidRegionException` extends `InvalidArgumentException`.
- `InvalidFormatException` extends `InvalidArgumentException`.

Developer-argument exceptions remain distinct from invalid telephone-data exceptions.

## Behavior

| Condition | Result |
|---|---|
| Invalid input to `parse()` | `InvalidPhoneNumberException` |
| Invalid input to `tryParse()` | `null` |
| Invalid input to predicates | `false` |
| Invalid input to `equals()` | `false` |
| Invalid input to throwing information methods | `InvalidPhoneNumberException` |
| Unsupported explicit region | `InvalidRegionException` |
| Unsupported format | `InvalidFormatException` |
| Valid but E.164-unrepresentable number | `UnrepresentablePhoneNumberException` |
| Extraction limit exceeded | `ExtractionLimitException` |
| Unexpected internal failure | Propagate |

## Exception privacy

Do not include raw telephone-number input in package exception messages.

Avoid chaining upstream exceptions if they expose raw numbers.

Do not log telephone-number values.

Do not use indiscriminate `catch (Throwable)` blocks to disguise internal failures as invalid input.

---

# 17. Laravel Service Provider and Facade

Implement:

```text
Moztopia\Phone\PhoneServiceProvider
Moztopia\Phone\Facades\Phone
```

Use Composer's Laravel auto-discovery mechanism.

Example:

```php
use Moztopia\Phone\Facades\Phone;

Phone::normalize(
    '0812345678',
    'TH'
);
```

The same service must be available through Laravel dependency injection.

No global aliases.

No mandatory configuration.

---

# 18. Laravel Validation Rule

Implement:

```text
Moztopia\Phone\Rules\ValidPhone
```

Usage:

```php
use Moztopia\Phone\Rules\ValidPhone;

'phone' => [
    'required',
    new ValidPhone(region: 'TH'),
];
```

Mobile-only usage:

```php
new ValidPhone(
    region: 'TH',
    mobileOnly: true
);
```

Constructor parameter names must be:

```text
region
mobileOnly
```

## 18.1 Validation behavior

- `region` provides parsing context.
- `mobileOnly` defaults to `false`.
- Valid international numbers from other countries remain acceptable.
- Mobile-only validation requires definitive `mobile` classification.
- Invalid values produce normal Laravel validation failures.
- Unsupported rule configuration produces a developer-argument exception.

Do not introduce an implicit country restriction.

## 18.2 Laravel empty-field semantics

**ValidPhone must be a conventional, non-implicit Laravel custom validation rule.**

Laravel ordinarily skips non-implicit validation rules when an attribute is absent or contains an empty string.

Do not make ValidPhone implicit.

Do not implement an implicit rule merely to reject missing or empty fields.

Use Laravel's standard rules to control field presence and nullability.

### Required field

```php
'phone' => [
    'required',
    new ValidPhone(region: 'TH'),
];
```

`required` determines whether a missing, null, or empty value is rejected.

`ValidPhone` validates non-empty values when Laravel invokes it.

### Optional nullable field

```php
'phone' => [
    'nullable',
    new ValidPhone(region: 'TH'),
];
```

`nullable` permits null values.

Missing attributes and empty strings follow Laravel's normal non-implicit rule behavior.

In ordinary HTTP request pipelines, Laravel's default `TrimStrings` and `ConvertEmptyStringsToNull` middleware may transform empty strings into null before validation.

Do not depend on that transformation when implementing the rule itself.

### ValidPhone without required

```php
'phone' => [
    new ValidPhone(region: 'TH'),
];
```

This must not be presented as sufficient to require a value.

Missing or empty fields may pass because the ordinary custom rule is not invoked.

This is intentional Laravel behavior.

### Explicit requirements

- Do not make ValidPhone responsible for field presence.
- Do not make it responsible for nullability.
- Do not override Laravel's validation execution rules.
- When invoked with a non-empty value, reject unsupported input.
- Document the difference between `required`, `nullable`, and `ValidPhone`.
- Test these combinations explicitly.

## 18.3 Mixed-value handling

The rule receives a mixed value from Laravel.

When invoked with an unsupported non-string value, reject it.

Examples include:

- Integers.
- Floats.
- Booleans.
- Arrays.
- Objects.

Do not rely on public service string signatures to reject framework-provided mixed values.

Do not coerce integers into phone-number strings.

The cast and service API retain their own separately defined behavior.

## 18.4 Localization

Provide default English translation strings using Laravel package translations.

Suggested keys:

```text
moztopia-phone::validation.invalid
moztopia-phone::validation.mobile_only
```

Support standard Laravel package translation overrides.

Do not require publishing files or configuration to use the defaults.

Do not include rejected telephone numbers in validation messages.

---

# 19. Eloquent Casting

Implement:

```text
Moztopia\Phone\Casts\E164Phone
```

Example:

```php
use Moztopia\Phone\Casts\E164Phone;

protected function casts(): array
{
    return [
        'phone' => E164Phone::class . ':TH',
    ];
}
```

Support an optional region parameter.

Without a region, assignment accepts only resolvable international input.

## 19.1 Assignment behavior

On assignment:

- Accept `string` or `null`.
- Normalize valid strings to E.164.
- Preserve `null`.
- Reject empty strings.
- Reject whitespace-only strings.
- Reject integers, floats, booleans, arrays, and objects.
- Reject invalid numbers.
- Reject extensions.
- Reject numbers that cannot be represented in E.164.
- Require a region for national input.
- Accept internationally formatted input without a region.

Assignment must use the same normalization rules as the public service.

Repeated assignment of the same normalized value must be stable and idempotent.

## 19.2 Retrieval behavior — structural validation only

**On retrieval, verify canonical E.164 structure, not current numbering-plan validity.**

International numbering metadata changes over time.

A number accepted when originally saved may later become invalid under newer metadata.

Ordinary database retrieval and JSON serialization must not fail merely because metadata changed.

Therefore:

- Return `string|null`.
- Preserve null.
- Require non-null stored values to be strings.
- Check canonical E.164 structure.
- Do not revalidate against current libphonenumber metadata.
- Do not call `Phone::isValid()` during retrieval.
- Do not silently repair malformed values.
- Do not rewrite stored values.
- Raise `InvalidPhoneNumberException` for malformed stored values.

## 19.3 Canonical E.164 structure

A stored non-null value must:

- Begin with exactly one ASCII `+`.
- Contain 2–15 ASCII digits after the plus sign.
- Have a nonzero first digit after `+`.
- Contain no spaces.
- Contain no separators.
- Contain no extension.
- Contain no trailing newline.
- Contain no extra leading or trailing characters.

Use the following exact PCRE expression:

```regex
\A\+[1-9][0-9]{1,14}\z
```

In PHP, with delimiters:

```php
preg_match(
    '/\A\+[1-9][0-9]{1,14}\z/',
    $value
) === 1;
```

**Use absolute anchors `\A` and `\z`.**

Do not replace them with `^` and `$`.

In PHP's PCRE implementation, `$` can match immediately before a trailing newline. That would incorrectly allow a malformed stored value to pass the canonical E.164 structure check.

`\z` requires the actual end of the string.

Do not use `\Z` as a substitute.

The expression validates canonical shape only.

It does not establish that the country calling code is assigned or that the number remains valid under current numbering metadata.

## 19.4 Operational consequences

A structurally valid stored number that becomes invalid under updated metadata:

- Must remain readable.
- Must remain serializable.
- Must not be rewritten automatically.
- May fail deliberate reassignment through the normal setter.
- May fail a new explicit `Phone::isValid()` check.

This asymmetry is intentional.

Assignment validates against current metadata.

Retrieval preserves stored-data stability.

## 19.5 Serialization

Eloquent array and JSON serialization must expose `string|null`.

Do not expose upstream objects or metadata.

Do not change stored values during serialization.

## 19.6 Invalid stored values

Malformed stored values must raise a package exception.

Do not silently return malformed values.

Do not repair them automatically.

Explicitly test a trailing newline, leading whitespace, trailing whitespace, non-ASCII digits, extra plus signs, and overlength values.

## 19.7 Database limitations

Document that Eloquent casting can be bypassed by:

- Raw SQL.
- Query-builder updates.
- Bulk database operations.
- External imports.
- Direct database writes.

The cast does not guarantee database-wide validity.

Do not introduce database triggers, constraints, or migrations.

---

# 20. Package Architecture

Keep the structure conventional and minimal.

Suggested layout:

```text
moztopia-phone/
├── src/
│   ├── Casts/
│   │   └── E164Phone.php
│   ├── Exceptions/
│   │   ├── PhoneException.php
│   │   ├── InvalidPhoneNumberException.php
│   │   ├── UnrepresentablePhoneNumberException.php
│   │   ├── ExtractionLimitException.php
│   │   ├── InvalidRegionException.php
│   │   └── InvalidFormatException.php
│   ├── Facades/
│   │   └── Phone.php
│   ├── Internal/
│   │   └── LibPhoneNumberAdapter.php
│   ├── Rules/
│   │   └── ValidPhone.php
│   ├── ParsedPhoneNumber.php
│   ├── PhoneService.php
│   └── PhoneServiceProvider.php
├── lang/
│   └── en/
│       └── validation.php
├── tests/
│   ├── Unit/
│   ├── Feature/
│   └── Integration/
├── .github/
│   └── workflows/
│       └── tests.yml
├── composer.json
├── README.md
├── AGENTS.md
├── CHANGELOG.md
└── LICENSE
```

Adjust the structure when repository conventions or implementation simplicity justify it.

Do not create empty layers.

Do not create interfaces for concrete classes without demonstrated need.

Do not create a generalized adapter registry.

Do not introduce speculative extensibility.

---

# 21. Explicit Scope Exclusions

Do not implement:

- SMS delivery.
- Voice calling.
- OTP generation or verification.
- Subscriber identification.
- Carrier verification APIs.
- Contact management.
- Country-selector widgets.
- Telephone input masks.
- Frontend JavaScript.
- Flutter or Dart components.
- HTML forms.
- Geographic address lookup.
- Live number verification.
- External API integrations.
- Background jobs.
- Queues.
- Caching infrastructure.
- Database migrations.
- Application controllers.
- HTTP routes.
- AI API integrations.
- MCP servers.
- Emergency-number validation.
- Short-code handling.
- USSD.
- Post-dial instruction processing.
- Full RFC 3966 URI support.

The package must remain a focused SDK.

---

# 22. Automated Testing

Use Pest, consistent with the Moztopia package standard.

Use Orchestra Testbench for Laravel integration testing where appropriate.

**Develop tests alongside implementation rather than postponing them until the end.**

## 22.1 Core API tests

Cover every public method.

Test:

- Valid national numbers.
- Valid international numbers.
- Invalid numbers.
- Missing regions.
- Unsupported regions.
- Lowercase regions.
- Calling codes.
- Non-geographic calling codes.
- Ambiguous region identification.
- Number classification.
- Mobile-only classification.
- Formatting.
- E.164 conversion.
- Possible-number checks.
- Local-only possible-number results.
- Equality.
- Extensions.
- Significant leading zeros.
- Unicode decimal digits.
- Rejected vanity numbers.
- Rejected prose.
- Rejected multiple-number input.
- Supported RFC 3966 input.
- Unsupported URI parameters.
- Invalid format identifiers.
- E.164 length limits.
- Value-object immutability.

## 22.2 Regional tests

Use representative verified cases from:

- Thailand.
- United States.
- United Kingdom.
- Australia.
- Germany.
- India.
- Japan.
- Brazil.

Prefer upstream example-number generation or verified test fixtures.

Do not invent numbers and assume they are valid.

## 22.3 Extension tests

Verify:

- Supported extension syntax.
- Leading-zero preservation.
- Normalization rejection.
- Service-level E.164 formatting rejection.
- Value-object `e164()` behavior.
- National formatting preservation.
- International formatting preservation.
- RFC 3966 preservation.
- Equality with matching extensions.
- Equality with different extensions.
- Equality with one absent extension.

## 22.4 Extraction tests

Verify:

- Normal prose.
- No matches.
- One match.
- Multiple matches.
- Repeated matches.
- Mixed valid and invalid candidates.
- Strict candidate acceptance.
- Extension-bearing matches.
- Occurrence ordering.
- Exactly 100 qualifying matches.
- More than 100 qualifying matches.
- Exact input-size boundary.
- Input-size overflow.
- Empty-result behavior.
- No partial results after failure.
- Iterator exhaustion under the published matching policy.

Test adversarial text and record representative performance.

Do not claim a strict execution-time guarantee that has not been implemented.

## 22.5 PHP typing tests

Test:

- Strict caller behavior.
- Weak caller scalar coercion.
- Documented PHP semantics.
- Mixed-value rejection by Laravel validation.
- Mixed-value rejection by Eloquent casts.

## 22.6 Laravel validation tests

Explicitly test conventional Laravel rule execution.

Required cases:

| Rules | Input | Expected behavior |
|---|---|---|
| `required` + `ValidPhone` | Missing | Fail required |
| `required` + `ValidPhone` | Empty string | Fail required |
| `required` + `ValidPhone` | Null | Fail required |
| `required` + `ValidPhone` | Valid number | Pass |
| `required` + `ValidPhone` | Invalid non-empty number | Fail ValidPhone |
| `nullable` + `ValidPhone` | Missing | Pass |
| `nullable` + `ValidPhone` | Null | Pass |
| `nullable` + `ValidPhone` | Valid number | Pass |
| `nullable` + `ValidPhone` | Invalid non-empty number | Fail ValidPhone |
| `ValidPhone` alone | Missing | Pass under non-implicit behavior |
| `ValidPhone` alone | Empty string | Follow Laravel's normal non-implicit behavior |
| `ValidPhone` alone | Invalid non-empty number | Fail ValidPhone |

Also test:

- Mobile-only validation.
- Ambiguous fixed-line/mobile classification.
- International numbers with a different parsing region.
- Unsupported rule configuration.
- Arrays and unsupported scalar values.
- Translation resolution.
- Application translation overrides.

Use both direct Laravel Validator tests and appropriate HTTP request-pipeline tests to account for `TrimStrings` and `ConvertEmptyStringsToNull`.

Do not implement a custom required-field mechanism inside ValidPhone.

## 22.7 Eloquent tests

Verify:

- Valid assignment.
- National-number normalization.
- International assignment.
- Null handling.
- Empty-string rejection.
- Non-string rejection.
- Invalid assignment.
- Extension rejection.
- Repeated assignment.
- Canonical retrieval.
- Invalid stored structure.
- JSON serialization.
- Array serialization.
- Metadata-independent retrieval.

### Mandatory canonical regex regression tests

Ensure that retrieval rejects:

```text
+66812345678\n
 +66812345678
+66812345678 
++66812345678
+66812345678x
```

Also test:

- No plus sign.
- Too few digits.
- More than 15 digits.
- Non-ASCII digits.
- Embedded newline.
- Leading newline.
- Null.
- Valid canonical E.164 strings.

The trailing-newline test is especially important.

It must fail validation.

Tests must demonstrate that `\z` provides strict end-of-string matching.

## 22.8 Metadata-change stability

Use controlled fixtures or test doubles to simulate a number becoming invalid under updated numbering metadata.

Verify that:

- Assignment uses current metadata validity.
- Retrieval uses structural validation only.
- Existing canonical values remain readable.
- Serialization remains stable.

Do not rely on actual metadata changes occurring during tests.

---

# 23. Compatibility and Dependency Testing

Test against:

- Lowest compatible dependency versions.
- Latest compatible dependency versions.
- PHP 8.3.
- PHP 8.4.
- PHP 8.5.
- Compatible Laravel 13.x versions.

Use Composer to verify real constraints.

Do not advertise untested environments as supported.

## Metadata updates

Maintain regression tests covering:

- Number validity.
- Formatting.
- Region identification.
- Number type classification.

Investigate genuine changes caused by upstream metadata updates.

Do not blindly modify test expectations.

Stored E.164 retrieval must remain independent of upstream metadata validity changes.

---

# 24. Clean Laravel Installation Test

Do not rely exclusively on Orchestra Testbench.

Create a clean Laravel 13 application.

Before publication, configure a local Composer path repository referencing the Moztopia Phone source directory.

Verify:

1. Composer installs the package.
2. Dependencies resolve.
3. Laravel auto-discovers the provider.
4. The facade works.
5. Dependency injection works.
6. The validation rule works.
7. Translation messages work.
8. Eloquent casting works.
9. No migrations are required.
10. No configuration is required.
11. README examples execute correctly.

Do not confuse manually registering a Testbench provider with genuine Composer auto-discovery.

---

# 25. CI and Code Quality

Configure GitHub Actions to run:

- Composer validation.
- Automated tests.
- Static analysis.
- Code-style checks.
- Dependency compatibility tests.
- Supported PHP version tests.

Preferred tools:

```text
Pest
PHPStan
Laravel Pint
Orchestra Testbench
```

Use compatible versions.

Follow repository-established conventions where appropriate.

Do not suppress errors merely to obtain a passing CI result.

---

# 26. Privacy, Security, and Reliability

Telephone numbers are potentially personal data.

The package must not:

- Transmit telephone numbers externally.
- Log telephone-number values.
- Collect analytics.
- Send telemetry.
- Persist numbers independently of the consuming application.
- Include raw numbers in exception messages.
- Expose raw numbers through chained upstream exceptions.

Operations must be deterministic for a given dependency and metadata version.

Avoid uncontrolled processing of large text inputs.

Do not claim that numbering-plan validity proves ownership, identity, or reachability.

---

# 27. README.md

Create complete and accurate documentation.

Include:

1. Product purpose.
2. PHP and Laravel compatibility.
3. Installation.
4. Quick start.
5. Public API.
6. Parsed value object.
7. Strict single-number input policy.
8. Region semantics.
9. Extension behavior.
10. Formatting.
11. E.164 limitations.
12. Number comparison.
13. Laravel validation.
14. Required and nullable field behavior.
15. Eloquent casting.
16. Structural-only retrieval validation.
17. Canonical E.164 regex.
18. PHP scalar coercion limitations.
19. Extraction policy and resource limits.
20. Error contract.
21. Privacy and security.
22. Scope exclusions.
23. Testing instructions.
24. Licensing and acknowledgments.

Provide simple examples first.

Every example must match the actual implementation.

Do not document planned methods as existing functionality.

---

# 28. AGENTS.md — AI-Friendly Integration

Create a concise guide specifically for AI coding agents consuming Moztopia Phone.

It must explain:

- When to use the package.
- How to install it.
- The correct namespace.
- The public methods.
- Return types.
- Parsing and region requirements.
- Normalization.
- Validation.
- Formatting.
- Extensions.
- Eloquent casting.
- Empty-field validation behavior.
- Error handling.
- Extraction limits.
- Scope exclusions.

Keep it short enough to read in one pass.

Provide executable examples.

## Copy-ready integration instruction

Include a prominent instruction similar to:

> When implementing international telephone-number functionality in this Laravel application, use `moztopia/phone` instead of generating custom parsing, validation, or formatting logic. Follow the package's published API and integration guide. Do not use libphonenumber directly unless specifically required.

Document how consuming projects can reference the guide in their own coding-agent instructions.

Do not assume an installed dependency's `AGENTS.md` is automatically loaded.

No MCP server, hosted AI service, or special agent runtime is required.

---

# 29. Package Metadata and Licensing

Composer identity:

```json
{
  "name": "moztopia/phone",
  "type": "library"
}
```

Provide:

- Accurate description.
- Relevant keywords.
- Homepage.
- GitHub source URL.
- License.
- PHP version requirements.
- Illuminate requirements.
- libphonenumber dependency.
- PSR-4 autoloading.
- Laravel auto-discovery metadata.

Use the existing repository license if one is already established.

Otherwise use Apache License 2.0.

Preserve Moztopia attribution.

Retain required upstream licenses and acknowledgments.

Do not imply Google endorses or directly maintains Moztopia Phone.

---

# 30. Implementation Workflow

**Develop and run tests throughout implementation.**

Do not defer testing until the entire package has been written.

Before modifying repository files:

1. Inspect the repository.
2. Read existing `AGENTS.md` and project instructions.
3. Consult relevant harness documentation or project wiki references.
4. Identify existing architecture and conventions.
5. Verify license decisions.
6. Verify Composer dependencies.
7. Review actual upstream libphonenumber APIs.

This specification governs product behavior and scope.

Existing repository conventions govern implementation details where they do not conflict.

## Phase 0 — Extraction feasibility

This is the first technical task.

Investigate Section 15 and implement a small executable spike.

Verify that extraction can meet its documented matching and completion policy using upstream functionality without substantial custom scanning.

Record observed performance and limitations.

If a material feasibility problem exists, report it before undertaking substantial further implementation.

## Phase 1 — Foundation and tests

Implement:

- Composer metadata.
- PSR-4 autoloading.
- Laravel service provider.
- Facade.
- Container registration.
- Internal libphonenumber integration.

Write and run tests alongside implementation.

## Phase 2 — Input and parsing

Implement:

- Strict input policy.
- Region handling.
- Unicode digit normalization.
- Supported URI parsing.
- Extension parsing.
- Parsed value object.
- Parsing exceptions.

Write unit tests for each component.

## Phase 3 — Telephone operations

Implement:

- Normalization.
- Validity.
- Possibility.
- Formatting.
- Country identification.
- Calling codes.
- Number types.
- Mobile classification.
- Equality.
- E.164 representability.

Test each operation as introduced.

## Phase 4 — Extraction

Implement the verified extraction design from Phase 0.

Add:

- Input-size limits.
- Match-count limits.
- Strict candidate filtering.
- Occurrence preservation.
- Complete-iteration handling.
- Explicit limit failures.

Test normal and adversarial inputs.

## Phase 5 — Laravel integration

Implement:

- Validation rule.
- Conventional non-implicit rule behavior.
- Mobile-only validation.
- Translation files.
- Eloquent cast.
- Structural-only retrieval.
- Absolute PCRE anchors.
- Serialization compatibility.

Test the Laravel rule combinations and canonical E.164 retrieval thoroughly.

## Phase 6 — Coverage and compatibility

Complete remaining:

- Unit tests.
- Laravel integration tests.
- Extraction boundary tests.
- PHP compatibility matrix.
- Dependency version matrix.
- Static analysis.
- Formatting checks.

Repair failures without weakening the public contract.

## Phase 7 — Documentation

Write README.md and AGENTS.md against the completed implementation.

Execute documented examples.

Confirm every public signature matches documentation.

## Phase 8 — Release verification

Install the package in a clean Laravel application using a local Composer path repository.

Verify auto-discovery and normal integration.

Review the final public API for unnecessary complexity.

Do not publish or tag a release without explicit authorization.

---

# 31. Release Acceptance Criteria

The package is ready for its first release only when:

- [ ] Composer identity is correct.
- [ ] Laravel 13 compatibility is verified.
- [ ] Target PHP versions have been tested.
- [ ] Service-provider auto-discovery works.
- [ ] No configuration is required for ordinary use.
- [ ] All public methods are implemented.
- [ ] Public signatures match the specification.
- [ ] Strict single-number input works.
- [ ] PHP caller-side coercion limitations are documented.
- [ ] Invalid and unsupported regions behave correctly.
- [ ] Extensions are preserved or rejected as specified.
- [ ] E.164 representability is enforced.
- [ ] Validity and possibility remain distinct.
- [ ] Number types are consistently mapped.
- [ ] Equality handles extensions correctly.
- [ ] Extraction feasibility is demonstrated.
- [ ] Extraction's matching policy is documented.
- [ ] Extraction resource limits are enforced.
- [ ] No incomplete extraction result is returned as successful.
- [ ] Laravel validation works.
- [ ] ValidPhone is non-implicit.
- [ ] `required` and `nullable` behavior is documented and tested.
- [ ] Framework-provided mixed inputs are handled correctly.
- [ ] Eloquent assignment normalizes correctly.
- [ ] Eloquent retrieval uses structural-only validation.
- [ ] Canonical E.164 validation uses absolute anchors.
- [ ] Trailing-newline regression tests pass.
- [ ] Stored-number retrieval is metadata-independent.
- [ ] Serialization is correct.
- [ ] Exceptions do not expose raw telephone numbers.
- [ ] Automated tests pass.
- [ ] Static analysis passes.
- [ ] Formatting checks pass.
- [ ] Clean Laravel installation succeeds.
- [ ] README.md is complete.
- [ ] AGENTS.md is complete.
- [ ] Licenses and acknowledgments are correct.
- [ ] No unrelated functionality has been added.

---

# 32. Independent AI Integration Acceptance Test

Moztopia Phone's primary product differentiator is its ease of integration by AI coding agents.

Create a reproducible acceptance scenario using a clean Laravel application.

An unfamiliar coding agent, using only published documentation, should be able to:

1. Install Moztopia Phone.
2. Normalize a Thai telephone number.
3. Validate an international telephone number.
4. Format a number containing an extension.
5. Apply a Laravel validation rule.
6. Correctly use `required` and `nullable`.
7. Add E.164 casting to an Eloquent model.
8. Handle invalid numbers correctly.
9. Explain numbering-plan validity versus actual number ownership.
10. Understand extraction limits.
11. Integrate the package without reading internal implementation code.

If independent-agent testing cannot be performed, create the fixture and reproducible instructions.

Do not claim independent-agent testing passed unless it was actually executed.

---

# 33. Final Deliverables

When implementation is complete, report:

1. Implemented capabilities.
2. Final public API.
3. Composer dependencies.
4. Verified supported environments.
5. Extraction feasibility findings.
6. Extraction performance observations.
7. Unit and integration test results.
8. Compatibility test results.
9. Static-analysis results.
10. Formatting results.
11. Clean Laravel installation results.
12. Documentation completion.
13. Remaining technical risks or limitations.
14. Release-readiness assessment.

Do not overstate implementation or testing status.

Do not create a Git tag, GitHub release, or Packagist publication without explicit authorization.

---

# FINAL ENGINEERING DIRECTIVE

Build **Moztopia Phone** as a small, dependable, production-ready Laravel SDK.

Use libphonenumber as the underlying international numbering engine.

Keep the public interface exceptionally small.

Do not recreate upstream functionality.

Enforce predictable input and error behavior.

Preserve telephone extensions correctly.

Separate numbering-plan validity from E.164 representability.

Keep stored-number retrieval stable across metadata updates.

Use absolute PCRE anchors for canonical storage checks.

Respect Laravel's conventional non-implicit validation behavior.

Make extraction guarantees realistic and testable.

Write tests alongside implementation.

Make documentation exceptionally easy for AI coding agents to consume.

Avoid unnecessary configuration, speculative abstraction, and feature expansion.

The objective is not to create the largest or most sophisticated telephone-number library.

**The objective is to create the telephone-number SDK that an AI coding agent can install, trust, and use correctly with almost no effort.**

**Moztopia — Small tools. Powerful AI.**