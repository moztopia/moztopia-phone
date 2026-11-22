<?php

declare(strict_types=1);

namespace Moztopia\Phone\Internal;

use libphonenumber\Leniency;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\ValidationResult;
use Moztopia\Phone\Exceptions\ExtractionLimitException;
use Moztopia\Phone\Exceptions\InvalidPhoneNumberException;
use Moztopia\Phone\Exceptions\InvalidRegionException;
use Moztopia\Phone\ParsedPhoneNumber;

/**
 * Own the SDK's only interaction with upstream numbering objects.
 *
 * @internal
 */

final class LibPhoneNumberAdapter
{
    private readonly PhoneNumberUtil $util;

    /**
     * Resolve the maintained numbering engine.
     */

    public function __construct(?PhoneNumberUtil $util = null)
    {
        $this->util = $util ?? PhoneNumberUtil::getInstance();
    }

    /**
     * Validate explicit context even for international or invalid input.
     */

    public function region(?string $region): ?string
    {
        if ($region === null) {
            return null;
        }
        $region = strtoupper($region);
        if (preg_match('/\A[A-Z]{2}\z/', $region) !== 1 || !in_array($region, $this->util->getSupportedRegions(), true)) {
            throw new InvalidRegionException('Unsupported telephone parsing region.');
        }

        return $region;
    }

    /**
     * Parse syntax before checking full numbering-plan validity when requested.
     */

    private function number(string $number, ?string $region, bool $requireValid = true): PhoneNumber
    {
        [$main, $extension] = (new SingleNumberInput())->prepare($number);
        try {
            $parsed = $this->util->parse($main, $region);
        } catch (NumberParseException) {
            throw new InvalidPhoneNumberException('The telephone number cannot be resolved.');
        }
        if ($requireValid && !$this->util->isValidNumber($parsed)) {
            throw new InvalidPhoneNumberException('The telephone number is invalid under current numbering metadata.');
        }
        if ($extension !== null) {
            $parsed->setExtension($extension);
        }

        return $parsed;
    }

    /**
     * Return an immutable snapshot of a validated number.
     */

    public function parse(string $number, ?string $region): ParsedPhoneNumber
    {
        $parsed = $this->number($number, $this->region($region));

        return new ParsedPhoneNumber(
            '+' . $parsed->getCountryCode() . $this->util->getNationalSignificantNumber($parsed),
            $this->util->format($parsed, PhoneNumberFormat::NATIONAL),
            $this->util->format($parsed, PhoneNumberFormat::INTERNATIONAL),
            $this->util->format($parsed, PhoneNumberFormat::RFC3966),
            $this->identifyRegion($parsed),
            (int) $parsed->getCountryCode(),
            match ($this->util->getNumberType($parsed)) {
                PhoneNumberType::FIXED_LINE => 'fixed_line',
                PhoneNumberType::MOBILE => 'mobile',
                PhoneNumberType::FIXED_LINE_OR_MOBILE => 'fixed_line_or_mobile',
                PhoneNumberType::TOLL_FREE => 'toll_free',
                PhoneNumberType::PREMIUM_RATE => 'premium_rate',
                PhoneNumberType::SHARED_COST => 'shared_cost',
                PhoneNumberType::VOIP => 'voip',
                PhoneNumberType::PERSONAL_NUMBER => 'personal_number',
                PhoneNumberType::PAGER => 'pager',
                PhoneNumberType::UAN => 'uan',
                PhoneNumberType::VOICEMAIL => 'voicemail',
                default => 'unknown',
            },
            $parsed->hasExtension() ? $parsed->getExtension() : null,
        );
    }

    /**
     * Avoid selecting the first valid region from an ambiguous shared code.
     */

    private function identifyRegion(PhoneNumber $number): ?string
    {
        $regions = [];
        foreach ($this->util->getRegionCodesForCountryCode((int) $number->getCountryCode()) as $region) {
            if ($region !== '001' && $this->util->isValidNumberForRegion($number, $region)) {
                $regions[] = $region;
            }
        }

        return count($regions) === 1 ? $regions[0] : null;
    }

    /**
     * Test full possibility without requiring validity or accepting local-only results.
     */

    public function isPossible(string $number, ?string $region): bool
    {
        $region = $this->region($region);
        try {
            return $this->util->isPossibleNumberWithReason($this->number($number, $region, false)) === ValidationResult::IS_POSSIBLE;
        } catch (InvalidPhoneNumberException) {
            return false;
        }
    }

    /**
     * Compare fully resolved significant identities, including extensions.
     */

    public function equals(string $left, string $right, ?string $region): bool
    {
        $region = $this->region($region);
        try {
            $a = $this->number($left, $region);
            $b = $this->number($right, $region);

            return $a->getCountryCode() === $b->getCountryCode()
                && $this->util->getNationalSignificantNumber($a) === $this->util->getNationalSignificantNumber($b)
                && $a->getExtension() === $b->getExtension();
        } catch (InvalidPhoneNumberException) {
            return false;
        }
    }

    /**
     * Exhaust the upstream matcher before returning any successful result.
     *
     * @return list<ParsedPhoneNumber>
     */

    public function extract(string $text, ?string $region): array
    {
        $region = $this->region($region);
        if (strlen($text) > 16384) {
            throw new ExtractionLimitException('Extraction input exceeds 16384 UTF-8 bytes.');
        }
        if (preg_match('//u', $text) !== 1) {
            throw new InvalidPhoneNumberException('Extraction requires valid UTF-8 text.');
        }
        $results = [];
        foreach ($this->util->findNumbers($text, $region, Leniency::VALID(), PHP_INT_MAX) as $match) {
            if ($match === null) {
                throw new \UnexpectedValueException('The numbering matcher yielded an absent match.');
            }
            try {
                $parsed = $this->parse($match->rawString(), $region);
            } catch (InvalidPhoneNumberException) {
                continue;
            }
            if (count($results) === 100) {
                throw new ExtractionLimitException('Extraction exceeds 100 qualifying matches.');
            }
            $results[] = $parsed;
        }

        return $results;
    }
}
