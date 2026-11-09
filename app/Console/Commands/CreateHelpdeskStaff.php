<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateHelpdeskStaff extends Command
{
    protected $signature = 'helpdesk:create-staff';
    protected $description = 'Create a helpdesk staff login using a privately entered password';

    public function handle(): int
    {
        $name = $this->ask('Full name');
        $email = $this->ask('Email address');
        $password = $this->secret(\App\Support\PasswordPolicy::strict() ? 'Password (12+ characters, uppercase, lowercase, number, symbol)' : 'Password (local development policy)');
        $validator = validator(compact('name', 'email', 'password'), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:254|unique:users,email',
            'password' => ['required', 'string', \App\Support\PasswordPolicy::rule(), 'max:1024'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->is_staff = true;
        $user->save();
        $this->info('Staff account created. You can now sign in at /login.');
        return self::SUCCESS;
    }
}
