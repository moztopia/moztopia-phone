<?php

declare(strict_types=1);

namespace Moztopia\Phone\Casts;

use Illuminate\Container\Container;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Moztopia\Phone\Exceptions\InvalidPhoneNumberException;
use Moztopia\Phone\Exceptions\InvalidRegionException;
use Moztopia\Phone\PhoneService;

/**
 * Normalize assignments while preserving metadata-independent stored reads.
 *
 * @implements CastsAttributes<string|null, mixed>
 */

final class E164Phone implements CastsAttributes
{
    private readonly PhoneService $phone;

    /**
     * Accept only one optional, supported parsing region.
     */

    public function __construct(private readonly ?string $region = null)
    {
        if (func_num_args() > 1) {
            throw new InvalidRegionException('The E.164 cast accepts only one optional parsing region.');
        }
        $this->phone = Container::getInstance()->make(PhoneService::class);
        $this->phone->isValid('', $region);
    }

    /**
     * Require canonical ASCII structure without consulting current number validity.
     *
     * @param array<string, mixed> $attributes
     */

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value) || preg_match('/\A\+[1-9][0-9]{1,14}\z/', $value) !== 1) {
            throw new InvalidPhoneNumberException('Stored telephone data must have canonical E.164 structure.');
        }

        return $value;
    }

    /**
     * Normalize valid strings and reject unsupported values without coercion.
     *
     * @param array<string, mixed> $attributes
     */

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidPhoneNumberException('Telephone assignment requires a string or null.');
        }

        return $this->phone->normalize($value, $this->region);
    }
}
