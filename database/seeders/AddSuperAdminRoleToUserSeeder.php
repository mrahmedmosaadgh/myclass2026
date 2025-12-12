<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
// use Spatie\Permission\Models\Role;

class AddSuperAdminRoleToUserSeeder extends Seeder
{
    public function run()
    {
        $user = User::find(266);
        if ($user) {
            // // Create the role if it doesn't exist
            // $role = Role::firstOrCreate(['name' => 'super_admin']);
            // $user->assignRole($role);
            $user->assignRole( 'super_admin' );
        }

 
    }
}