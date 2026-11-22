<?php

declare(strict_types=1);

namespace Moztopia\Phone;

use Moztopia\Phone\Exceptions\UnrepresentablePhoneNumberException;

/**
 * Preserve an immutable snapshot of one validated telephone identifier.
 */

final readonly class ParsedPhoneNumber
{
    /**
     * Receive validated, formatted scalar data from the internal adapter.
     *
     * @internal Obtain instances using PhoneService::parse().
     */

    public function __construct(
        private string $mainNumber,
        private string $nationalNumber,
        private string $internationalNumber,
        private string $telephoneUri,
        private ?string $numberingRegion,
        private int $countryCallingCode,
        private string $numberType,
        private ?string $telephoneExtension,
    ) {
    }

    /**
     * Return the main number only, deliberately excluding its extension.
     */

    public function e164(): string
    {
        if (preg_match('/\A\+[1-9][0-9]{1,14}\z/', $this->mainNumber) !== 1) {
            throw new UnrepresentablePhoneNumberException('The main number cannot be represented as E.164.');
        }

        return $this->mainNumber;
    }

    /**
     * Format nationally, preserving the extension.
     */

    public function national(): string
    {
        return $this->nationalNumber;
    }

    /**
     * Format internationally, preserving the extension.
     */

    public function international(): string
    {
        return $this->internationalNumber;
    }

    /**
     * Return a global telephone URI, preserving the extension.
     */

    public function rfc3966(): string
    {
        return $this->telephoneUri;
    }

    /**
     * Return a reliable individual numbering region when available.
     */

    public function region(): ?string
    {
        return $this->numberingRegion;
    }

    /**
     * Return the international calling code.
     */

    public function callingCode(): int
    {
        return $this->countryCallingCode;
    }

    /**
     * Return the stable metadata-based classification.
     */

    public function type(): string
    {
        return $this->numberType;
    }

    /**
     * Return extension digits with significant leading zeros intact.
     */

    public function extension(): ?string
    {
        return $this->telephoneExtension;
    }

    /**
     * Require definitive mobile classification.
     */

    public function isMobile(): bool
    {
        return $this->numberType === 'mobile';
    }
}
