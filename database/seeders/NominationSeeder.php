<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nomination;

class NominationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Nomination::create([
          'last_name' => 'Sally',
          'first_name' => 'Smith',
          'position_id' => 1,
          'year' => '2020'
        ]);
        Nomination::create([
          'last_name' => 'Billy Bob Jo',
          'first_name' => 'Penney',
          'position_id' => 1,
          'year' => '2020'
        ]);
        Nomination::create([
          'last_name' => 'Ken',
          'first_name' => 'King',
          'position_id' => 1,
          'year' => '2020'
        ]);
    }
}
