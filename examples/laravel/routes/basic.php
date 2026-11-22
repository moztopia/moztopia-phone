<?php

declare(strict_types=1);

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use Moztopia\Phone\Facades\Phone;
use Moztopia\Phone\PhoneService;

Route::get('/examples/basic', function (PhoneService $phone): JsonResource {
    $parsed = $phone->parse('+16502530000 ext. 0012');

    return new JsonResource([
        'normalized' => Phone::normalize('0812345678', 'TH'),
        'valid' => $phone->isValid('+66812345678'),
        'possible' => $phone->isPossible('+66812345678'),
        'national' => $phone->format('0812345678', 'national', 'TH'),
        'country' => $phone->country('+66812345678'),
        'calling_code' => $phone->callingCode('+66812345678'),
        'type' => $phone->type('+66812345678'),
        'equal' => $phone->equals('0812345678', '+66812345678', 'TH'),
        'invalid_parse' => $phone->tryParse('not a phone number'),
        'with_extension' => [
            'main_number' => $parsed->e164(), // Deliberately main number only.
            'extension' => $parsed->extension(),
            'uri' => $parsed->rfc3966(),
        ],
        'extracted' => array_map(
            fn ($match): string => $match->rfc3966(),
            $phone->extract('First +66812345678; then +16502530000 ext. 0012.'),
        ),
    ]);
});
