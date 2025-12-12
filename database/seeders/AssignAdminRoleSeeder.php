<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AssignAdminRoleSeeder extends Seeder
{
    public function run()
    {
        $user = null;
        try {
            //code...
            //throw $th;
            $user =  User::create([
                'name' => 'SuperAdmin', // You can change this as needed
                'email' => 'me@me.com',
                'password' => Hash::make('12345678'), // Hash the password for security
            ]);
        } catch (\Throwable $th) {

                $user = User::find(1);
        if ($user) {
            $user->assignRole( 'super_admin' );
            $user->assignRole( 'admin' );
        }
        }

    }
}
