<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /* Authorize all guests to attempt registration */
    public function authorize(): bool
    {
        /* In case we add rate limits or IP checks later, this remains the hook */
        return true;
    }

    /* Server-side validation rules for /register */
    public function rules(): array
    {
        return [
            /* Names are optional but limited to sensible length */
            'first_name' => ['bail', 'required', 'string', 'max:100', "regex:/^[\p{L}\p{M} '\-]+$/u"],
            'last_name' => ['bail', 'required', 'string', 'max:100', "regex:/^[\p{L}\p{M} '\-]+$/u"],

            /* Email is required, RFC-valid, unique across users (incl. soft-deleted) */
            'email' => [
                'required', 'email:rfc', 'max:128',
                Rule::unique('users', 'email')/*->ignore($this->userId)*/
            ],

            /* Phone is required; keep it simple here—full E.164 comes at company profile step */
            'phone' => ['bail', 'required', 'string', 'max:30'],

            /* Password with confirmation; złożoność możemy rozszerzyć w kolejnym kroku */
            'password' => [
                'bail', 'required', 'string', 'confirmed',
                Password::min(8)
                    ->max(64)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];
    }
}
