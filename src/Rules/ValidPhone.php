<?php

declare(strict_types=1);

namespace Moztopia\Phone\Rules;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Moztopia\Phone\PhoneService;

/**
 * Validate invoked values while Laravel controls presence and nullability.
 */

final class ValidPhone implements ValidationRule
{
    private readonly PhoneService $phone;

    /**
     * Validate developer configuration even when Laravel skips an empty field.
     */

    public function __construct(private readonly ?string $region = null, private readonly bool $mobileOnly = false)
    {
        $this->phone = Container::getInstance()->make(PhoneService::class);
        $this->phone->isValid('', $region);
    }

    /**
     * Fail with translated, input-free messages for invalid or non-mobile data.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parsed = is_string($value) ? $this->phone->tryParse($value, $this->region) : null;
        if ($parsed === null) {
            $fail('moztopia-phone::validation.invalid')->translate();
        } elseif ($this->mobileOnly && !$parsed->isMobile()) {
            $fail('moztopia-phone::validation.mobile_only')->translate();
        }
    }
}
