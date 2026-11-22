<?php

declare(strict_types=1);

namespace Moztopia\Phone\Facades;

use Illuminate\Support\Facades\Facade;
use Moztopia\Phone\ParsedPhoneNumber;
use Moztopia\Phone\PhoneService;

/**
 * Access the same SDK service supplied by constructor injection.
 *
 * @method static ParsedPhoneNumber parse(string $number, ?string $region = null)
 * @method static ?ParsedPhoneNumber tryParse(string $number, ?string $region = null)
 * @method static string normalize(string $number, ?string $region = null)
 * @method static bool isValid(string $number, ?string $region = null)
 * @method static bool isPossible(string $number, ?string $region = null)
 * @method static string format(string $number, string $format = 'international', ?string $region = null)
 * @method static ?string country(string $number, ?string $region = null)
 * @method static int callingCode(string $number, ?string $region = null)
 * @method static string type(string $number, ?string $region = null)
 * @method static bool equals(string $left, string $right, ?string $region = null)
 * @method static list<ParsedPhoneNumber> extract(string $text, ?string $region = null)
 */

final class Phone extends Facade
{
    /**
     * Resolve the class binding without registering a global alias.
     */

    protected static function getFacadeAccessor(): string
    {
        return PhoneService::class;
    }
}
