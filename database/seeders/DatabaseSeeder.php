<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed positions
        $this->call([
          PositionSeeder::class,
        ]);

        // For development/testing, you can also run:
        // php artisan db:seed --class=FakeDataSeeder
    }
}
