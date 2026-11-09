<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    public static function strict(): bool
    {
        return !app()->environment('local', 'testing') || (bool) config('helpdesk.strict_passwords');
    }

    public static function rule(): Password
    {
        return static::strict()
            ? Password::min(12)->mixedCase()->numbers()->symbols()
            : Password::min(1);
    }
}
