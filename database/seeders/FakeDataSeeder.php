<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Nomination;
use App\Models\Vote;
use App\Models\Ranking;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates fake data for testing the voting/ranking system.
     *
     * @return void
     */
    public function run()
    {
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        // Ensure positions exist
        $this->call(PositionSeeder::class);

        $governingBoard = Position::find(1); // Governing Board
        $diaconate = Position::find(4); // Diaconate

        // Fake nominee names
        $maleFirstNames = ['James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Joseph', 'Thomas', 'Charles', 'Christopher', 'Daniel', 'Matthew', 'Anthony', 'Mark', 'Donald', 'Steven', 'Paul', 'Andrew', 'Joshua'];
        $femaleFirstNames = ['Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen', 'Nancy', 'Lisa', 'Betty', 'Margaret', 'Sandra', 'Ashley', 'Kimberly', 'Emily', 'Donna', 'Michelle'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Thompson', 'White', 'Harris', 'Clark', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen'];

        echo "Creating fake nominations for {$currentYear} and {$nextYear}...\n";

        // Create nominations for Governing Board (current year)
        echo "- Governing Board ({$currentYear}): ";
        $gbNominees = [];
        for ($i = 0; $i < 25; $i++) {
            $firstName = ($i % 2 == 0) ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $gbNominees[] = Nomination::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position_id' => 1, // Governing Board
                'year' => $currentYear,
            ]);
        }
        echo count($gbNominees) . " nominees created\n";

        // Create nominations for Diaconate (current year)
        echo "- Diaconate ({$currentYear}): ";
        $diaconateNominees = [];
        for ($i = 0; $i < 20; $i++) {
            $firstName = ($i % 2 == 0) ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $diaconateNominees[] = Nomination::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position_id' => 4, // Diaconate
                'year' => $currentYear,
            ]);
        }
        echo count($diaconateNominees) . " nominees created\n";

        // Create nominations for next year (Governing Board only)
        echo "- Governing Board ({$nextYear}): ";
        $nextYearNominees = [];
        for ($i = 0; $i < 20; $i++) {
            $firstName = ($i % 2 == 0) ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $nextYearNominees[] = Nomination::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position_id' => 1, // Governing Board
                'year' => $nextYear,
            ]);
        }
        echo count($nextYearNominees) . " nominees created\n";

        // Add some fake votes for current year Governing Board
        echo "\nCreating fake votes for Governing Board ({$currentYear})...\n";
        $voterCount = config('fcc.voter_count', 24);
        $numToVoteFor = $governingBoard->num_to_select;

        // Have 60% of voters vote
        $votersWhoVoted = (int)($voterCount * 0.6);
        for ($voter = 1; $voter <= $votersWhoVoted; $voter++) {
            // Each voter votes for the required number of nominees
            $selectedNominees = array_rand($gbNominees, min($numToVoteFor, count($gbNominees)));
            if (!is_array($selectedNominees)) {
                $selectedNominees = [$selectedNominees];
            }

            foreach ($selectedNominees as $nomineeIndex) {
                Vote::create([
                    'voter' => $voter,
                    'nomination_id' => $gbNominees[$nomineeIndex]->id,
                    'position_id' => $governingBoard->id,
                    'year' => $currentYear,
                ]);
            }
        }
        echo "- {$votersWhoVoted} voters cast votes\n";

        // Add some fake rankings (if position is in rank status)
        if ($governingBoard->status === 'rank') {
            echo "\nCreating fake rankings for Governing Board ({$currentYear})...\n";

            // Get top nominees for ranking
            $topNominees = Nomination::getNomineesForRanking($governingBoard->id);

            if (count($topNominees) > 0) {
                // Have 50% of voters rank
                $rankersWhoRanked = (int)($voterCount * 0.5);
                for ($ranker = 1; $ranker <= $rankersWhoRanked; $ranker++) {
                    // Shuffle nominees and assign ranks
                    $shuffled = $topNominees->shuffle();

                    foreach ($shuffled as $index => $nominee) {
                        Ranking::create([
                            'voter' => $ranker,
                            'nomination_id' => $nominee->id,
                            'rank' => $index + 1,
                            'position_id' => $governingBoard->id,
                            'year' => $currentYear,
                        ]);
                    }
                }
                echo "- {$rankersWhoRanked} voters ranked nominees\n";
            }
        }

        echo "\n✅ Fake data seeding complete!\n";
        echo "   Current year ({$currentYear}): " . (count($gbNominees) + count($diaconateNominees)) . " nominations\n";
        echo "   Next year ({$nextYear}): " . count($nextYearNominees) . " nominations\n";
    }
}
