<?php

declare(strict_types=1);

namespace Moztopia\Phone\Internal;

use libphonenumber\PhoneNumberUtil;
use Moztopia\Phone\Exceptions\InvalidPhoneNumberException;

/**
 * Enforce syntax without reproducing numbering-plan rules.
 *
 * @internal
 */

final class SingleNumberInput
{
    /**
     * Separate one supported number and its optional numeric extension.
     *
     * @return array{string, ?string}
     */

    public function prepare(string $input): array
    {
        if (strlen($input) > 256 || preg_match('//u', $input) !== 1) {
            throw new InvalidPhoneNumberException('Expected one supported telephone number within 256 UTF-8 bytes.');
        }

        $input = preg_replace_callback('/\p{Nd}/u', static function (array $match): string {
            $digit = PhoneNumberUtil::normalizeDigitsOnly($match[0]);
            if (preg_match('/\A[0-9]\z/', $digit) !== 1) {
                throw new InvalidPhoneNumberException('Unsupported decimal digit.');
            }

            return $digit;
        }, $input);

        $input = trim(str_replace(["\u{00A0}", "\u{202F}"], ' ', $input ?? ''), ' ');
        $extension = null;
        if (str_starts_with($input, 'tel:')) {
            if (preg_match('/\Atel:(\+[0-9(). -]+)(?:;ext=([0-9]+))?\z/', $input, $parts) !== 1) {
                throw new InvalidPhoneNumberException('Unsupported telephone URI.');
            }
            $input = $parts[1];
            $extension = $parts[2] ?? null;
        } elseif (preg_match('/ *(?:ext\.? +|x|#)([0-9]+)\z/i', $input, $parts, PREG_OFFSET_CAPTURE) === 1) {
            $extension = $parts[1][0];
            $input = rtrim(substr($input, 0, $parts[0][1]), ' ');
        }

        if (preg_match('/\A\+?[0-9(][0-9 ().-]*[0-9)]\z/', $input) !== 1
            || preg_match('/[.-][ .-]*[.-]/', $input) === 1) {
            throw new InvalidPhoneNumberException('Expected exactly one telephone number without labels or instructions.');
        }

        $open = false;
        foreach (str_split($input) as $index => $character) {
            if ($character === '(') {
                if ($open || !isset($input[$index + 1]) || !ctype_digit($input[$index + 1])) {
                    throw new InvalidPhoneNumberException('Unsupported telephone parentheses.');
                }
                $open = true;
            } elseif ($character === ')') {
                if (!$open || $index === 0 || !ctype_digit($input[$index - 1])) {
                    throw new InvalidPhoneNumberException('Unsupported telephone parentheses.');
                }
                $open = false;
            }
        }
        if ($open) {
            throw new InvalidPhoneNumberException('Unbalanced telephone parentheses.');
        }

        return [$input, $extension];
    }
}
