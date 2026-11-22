<?php

declare(strict_types=1);

namespace Moztopia\Phone;

use Moztopia\Phone\Exceptions\InvalidFormatException;
use Moztopia\Phone\Exceptions\InvalidPhoneNumberException;
use Moztopia\Phone\Exceptions\UnrepresentablePhoneNumberException;
use Moztopia\Phone\Internal\LibPhoneNumberAdapter;

/**
 * Provide a small, strict API over maintained international numbering metadata.
 */

final class PhoneService
{
    /**
     * Use one internal adapter without requiring consumer configuration.
     */

    public function __construct(private readonly LibPhoneNumberAdapter $adapter = new LibPhoneNumberAdapter())
    {
    }

    /**
     * Parse one fully valid number into an immutable value.
     */

    public function parse(string $number, ?string $region = null): ParsedPhoneNumber
    {
        return $this->adapter->parse($number, $region);
    }

    /**
     * Return null for invalid data while preserving developer and internal errors.
     */

    public function tryParse(string $number, ?string $region = null): ?ParsedPhoneNumber
    {
        try {
            return $this->parse($number, $region);
        } catch (InvalidPhoneNumberException) {
            return null;
        }
    }

    /**
     * Produce lossless E.164 storage input, rejecting extensions.
     */

    public function normalize(string $number, ?string $region = null): string
    {
        $parsed = $this->parse($number, $region);
        if ($parsed->extension() !== null) {
            throw new UnrepresentablePhoneNumberException('E.164 cannot represent a telephone extension.');
        }

        return $parsed->e164();
    }

    /**
     * Test strict syntax and full numbering-plan validity.
     */

    public function isValid(string $number, ?string $region = null): bool
    {
        return $this->tryParse($number, $region) !== null;
    }

    /**
     * Test full-number plausibility, independently of full validity.
     */

    public function isPossible(string $number, ?string $region = null): bool
    {
        return $this->adapter->isPossible($number, $region);
    }

    /**
     * Format valid input, preserving extensions where representable.
     */

    public function format(string $number, string $format = 'international', ?string $region = null): string
    {
        if (!in_array($format, ['e164', 'national', 'international', 'rfc3966'], true)) {
            throw new InvalidFormatException('Unsupported telephone format.');
        }
        if ($format === 'e164') {
            return $this->normalize($number, $region);
        }
        $parsed = $this->parse($number, $region);

        return match ($format) {
            'national' => $parsed->national(),
            'international' => $parsed->international(),
            'rfc3966' => $parsed->rfc3966(),
        };
    }

    /**
     * Identify an individual numbering region when reliably available.
     */

    public function country(string $number, ?string $region = null): ?string
    {
        return $this->parse($number, $region)->region();
    }

    /**
     * Return a validated number's international calling code.
     */

    public function callingCode(string $number, ?string $region = null): int
    {
        return $this->parse($number, $region)->callingCode();
    }

    /**
     * Return a stable telephone classification.
     */

    public function type(string $number, ?string $region = null): string
    {
        return $this->parse($number, $region)->type();
    }

    /**
     * Compare complete, resolved identifiers including their extensions.
     */

    public function equals(string $left, string $right, ?string $region = null): bool
    {
        return $this->adapter->equals($left, $right, $region);
    }

    /**
     * Return every qualifying upstream VALID match, or fail without partial results.
     *
     * @return list<ParsedPhoneNumber>
     */

    public function extract(string $text, ?string $region = null): array
    {
        return $this->adapter->extract($text, $region);
    }
}
