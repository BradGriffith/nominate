<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get admin role (created by Voyager)
        $adminRole = \DB::table('roles')->where('name', 'admin')->first();

        // Create admin user
        $user = User::firstOrCreate(
            ['email' => 'brad@bradgriffith.com'],
            [
                'name' => 'Brad Griffith',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $adminRole ? $adminRole->id : null,
            ]
        );

        // Update role_id if Voyager was installed after user creation
        if ($adminRole && !$user->role_id) {
            $user->role_id = $adminRole->id;
            $user->save();
        }

        echo "✅ Admin user created: brad@bradgriffith.com\n";
        if ($adminRole) {
            echo "   Role: admin (Voyager access granted)\n";
        } else {
            echo "   ⚠️  Note: Run 'php artisan voyager:install' to enable admin access\n";
        }
    }
}
