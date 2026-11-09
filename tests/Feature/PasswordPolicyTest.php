<?php

namespace Tests\Feature;

use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    public function test_relaxed_local_policy_accepts_123_but_requires_a_password(): void
    {
        config(['helpdesk.strict_passwords' => false]);
        $this->assertTrue(Validator::make(['password' => '123'], ['password' => ['required', PasswordPolicy::rule()]])->passes());
        $this->assertFalse(Validator::make(['password' => ''], ['password' => ['required', PasswordPolicy::rule()]])->passes());
    }

    public function test_strict_policy_requires_length_case_number_and_symbol(): void
    {
        config(['helpdesk.strict_passwords' => true]);
        foreach (['123', 'longlowercasepassword', 'LongPassword123', 'LongPassword!!!', 'lowercase123!!!'] as $password) {
            $this->assertFalse(Validator::make(compact('password'), ['password' => [PasswordPolicy::rule()]])->passes());
        }
        $this->assertTrue(Validator::make(['password' => 'StrongPassword123!'], ['password' => [PasswordPolicy::rule()]])->passes());
    }

    public function test_production_enforces_strict_policy_even_if_switch_is_false(): void
    {
        config(['helpdesk.strict_passwords' => false]);
        $this->app->instance('env', 'production');
        $this->assertTrue(PasswordPolicy::strict());
        $this->assertFalse(Validator::make(['password' => '123'], ['password' => [PasswordPolicy::rule()]])->passes());
    }
}
