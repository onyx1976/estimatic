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
            'last_name'  => ['bail', 'required', 'string', 'max:100', "regex:/^[\p{L}\p{M} '\-]+$/u"],

            /* Company name: allow common punctuation seen in EU company names */
            'company_name' => ['bail', 'required', 'string', 'max:150', 'regex:/^[\p{L}\p{M}\p{N}\p{Zs}\.&\'"\-(),\/]+$/u'],

            /* Email is required, RFC-valid, unique across users (incl. soft-deleted) */
            'email' => [
                'required', 'email:rfc', 'max:128',
                Rule::unique('users', 'email')/*->ignore($this->userId)*/
            ],

            /* Strong password with confirmation */
            'password' => [
                'bail', 'required', 'string', 'confirmed',
                Password::min(8)
                    ->max(64)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'password_confirmation' => ['required', 'string'],

            /* Privacy consent */
            'accept_privacy' => ['bail', 'accepted'],

            /* Hidden UX fields – store to users table right away */
            'time_zone' => ['nullable', 'string', 'max:64', 'timezone'],
            'locale'    => ['nullable', 'string', 'max:10', 'regex:/^[a-z]{2}([-_][A-Z]{2})?$/'],
        ];
    }
}
