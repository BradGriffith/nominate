<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Vote;

class UnvotedVoterTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Make sure voters get removed from the unvoted list after they vote
     *
     * @return void
     */
    public function testUnvotedVoter()
    {
        $voters = [1,4,10,12];
        $expectedVotersRemaining = [2,3,5,6,7,8,9,11,13,14,15,16,17,18,19,20,21,22,23,24];

        $votes = [];
        foreach($voters as $voter) {
            $votes[] = [
                'voter' => $voter,
                'position_id' => 1,
                'nomination_id' => 1,
                'year' => 2020,
            ];
        }
        Vote::insert($votes);

        $this->assertEquals($voters, array_values(Vote::getVotedVoters()));

        $this->assertEquals($expectedVotersRemaining, array_values(Vote::getUnvotedVoters()));
    }
}
