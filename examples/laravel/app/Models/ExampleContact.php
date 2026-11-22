<?php

declare(strict_types=1);

namespace App\Models;

use Moztopia\Phone\Casts\E164Phone;

final class ExampleContact extends BaseModel
{
    protected $fillable = ['phone', 'mobile'];

    protected function casts(): array
    {
        return [
            'phone' => E164Phone::class.':TH',
            'mobile' => E164Phone::class.':TH',
        ];
    }
}
