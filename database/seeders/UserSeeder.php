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
        // Create admin user
        User::firstOrCreate(
            ['email' => 'brad@bradgriffith.com'],
            [
                'name' => 'Brad Griffith',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        echo "✅ Admin user created: brad@bradgriffith.com (password: password)\n";
    }
}
