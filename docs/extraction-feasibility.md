# Extraction feasibility — Phase 0

Gate passed on 22 November 2026 using PHP 8.4.27 (64-bit Linux container), libphonenumber-for-php 9.0.43. Run `php tools/extraction-feasibility.php` after `composer install`. The script is executable verification tooling, not implementation reference to copy into applications.

The inspected public `PhoneNumberUtil::findNumbers` API accepts `Leniency::VALID()` and `PHP_INT_MAX`. `PhoneNumberMatcher::next` advances to each match's end; unsuccessful scans advance past a finite candidate. Inner matching uses a fixed set of patterns and advances through the candidate. With at most 16,384 input bytes, the default integer budget is effectively unrestricted on the tested 64-bit PHP runtime. The internal budget is neither counted nor inspected by this package.

The executable spike asserted normal prose, no numbers, multiple numbers, duplicates, leading-zero extensions, full-width digits in Unicode prose, punctuation, 16 KiB invalid candidates, near/exact input-size boundaries, 100 matches, 101 matches, and oversized input. It also records upstream behavior at ambiguous adjacent boundaries. Ordinary successful scans exhausted the iterator. The 101st match and oversized input produce an explicit limit outcome without exposing a partial result.

Observed first-run measurements: ordinary prose 3.505 ms; 100 matches 8.644 ms; invalid numeric candidates at 16,384 bytes 1,139.922 ms; process peak allocated memory 2,097,152 bytes. These include PHP allocator reporting, not operating-system RSS. Repeat runs, machines, dependencies and input distributions vary. No worst-case time or universal resource guarantee is claimed. A host must apply its normal request execution limits when using extraction on untrusted text.

Completeness means all qualifying matches from one fully exhausted upstream VALID iterator. Upstream chooses substrings and may omit unusual forms or include adjacent numbers. Moztopia will filter each match's `rawString()` through the same supported single-number lexical and numbering-plan policy as direct parsing. Rejected candidates do not count toward the 100-result limit. No additional scanner, candidate budget or arbitrary preflight is needed. Such filtering can narrow the result set but cannot expand upstream coverage; supported extension and URI extraction coverage remains subject to upstream boundaries.

Sources inspected: [matcher](https://github.com/giggsey/libphonenumber-for-php/blob/9.0.43/src/PhoneNumberMatcher.php), [utility](https://github.com/giggsey/libphonenumber-for-php/blob/9.0.43/src/PhoneNumberUtil.php). Subsequent package contract tests must exercise filtering, exact byte boundaries and failure propagation as well as this upstream-only spike.

Repository starts at source version 0.0.0. This feasibility integration establishes tooling and preserves the owner-approved specification; it publishes no SDK API. The specification explicitly holds Git tags, GitHub releases and Packagist publication pending owner authorization, overriding automatic integration tagging for this task.

by-dolly
