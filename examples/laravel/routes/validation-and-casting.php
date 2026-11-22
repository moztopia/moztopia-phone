<?php

declare(strict_types=1);

use App\Models\ExampleContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Moztopia\Phone\Exceptions\UnrepresentablePhoneNumberException;
use Moztopia\Phone\Rules\ValidPhone;

Route::post('/examples/contacts', function (Request $request): JsonResponse {
    $values = $request->validate([
        'phone' => ['required', new ValidPhone(region: 'TH')],
        'mobile' => ['nullable', new ValidPhone(region: 'TH', mobileOnly: true)],
    ]);

    $contact = new ExampleContact();

    foreach ($values as $attribute => $value) {
        try {
            $contact->setAttribute($attribute, $value);
        } catch (UnrepresentablePhoneNumberException) {
            // ValidPhone permits extensions; this E.164-only storage example cannot.
            throw ValidationException::withMessages([
                $attribute => 'Use a number without an extension that fits E.164 storage.',
            ]);
        }
    }

    $contact->save();

    return (new JsonResource($contact->fresh()))->response()->setStatusCode(201);
});

Route::get('/examples/contacts/{contact}', fn (ExampleContact $contact) => new JsonResource($contact));
