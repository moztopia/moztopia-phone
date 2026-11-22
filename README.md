# Moztopia Phone

Small tools. Powerful AI.

A strict international telephone-number SDK for Laravel 13, powered by `giggsey/libphonenumber-for-php` 9.x. This source is in development; it has not been tagged or published to Packagist.

The implemented core can be used directly:

```php
use Moztopia\Phone\PhoneService;

$phone = new PhoneService();
$phone->normalize('0812345678', 'TH'); // '+66812345678'
$phone->isValid('+66812345678'); // true
$parsed = $phone->parse('+16502530000 ext. 0012');
$parsed->rfc3966(); // 'tel:+1-650-253-0000;ext=0012'
```

Service E.164 normalization rejects extensions; the parsed object's `e164()` deliberately returns only its main number. Region arguments supply context, not country restrictions. Validity never proves assignment, ownership or reachability.

The remaining Laravel features, complete consumer documentation and compatibility evidence are tracked in issues #3 and #5. See [the implementation specification](docs/implementation-specification.md) and [extraction feasibility evidence](docs/extraction-feasibility.md).

Run `composer install`, `composer test`, `composer analyse` and `composer lint` in an isolated supported PHP environment. The intentionally weak-typing caller fixture is excluded from automatic strict-type formatting. Dependencies and lockfiles are local and untracked.

Source PHP constraints preserve the shared security floors: 8.3.36+, 8.4.27+ or 8.5.12+ within each line. Initial local core verification used PHP 8.4.27; the remaining compatibility matrix is pending.

Apache-2.0. libphonenumber-for-php is independently maintained; Google does not publish or endorse this package.
