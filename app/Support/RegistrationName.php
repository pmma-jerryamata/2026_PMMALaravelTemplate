<?php

namespace App\Support;

class RegistrationName
{
    public static function rules(): array
    {
        $rules = ['required', 'string', 'max:100', "regex:/\\A(?=.*\\p{L})[\\p{L}\\p{M} .’'\\-]+\\z/u"];
        return ['first_name' => $rules, 'last_name' => $rules];
    }

    public static function fullName(array $data): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $data['first_name'].' '.$data['last_name'])));
    }
}
