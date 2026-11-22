<?php

declare(strict_types=1);

namespace Moztopia\Phone\Exceptions;

/**
 * Reject a valid identifier that cannot be represented losslessly.
 */

final class UnrepresentablePhoneNumberException extends PhoneException
{
}
