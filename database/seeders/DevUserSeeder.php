<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DevUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('testing')) {
            return;
        }
        $user = User::firstOrNew(['username' => 'admin']);
        $user->name = 'Development Admin';
        $user->email = 'admin@example.test';
        $user->username = 'admin';
        $user->password = '123';
        $user->is_staff = true;
        $user->is_dev_admin = true;
        $user->save();
    }
}
