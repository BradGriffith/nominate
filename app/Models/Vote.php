<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    public static function getUnvotedVoters($position_id, $voter_count = null) {
        if($voter_count === null) {
            $voter_count = \Config::get('fcc.voter_count');
        }

        $already_voted = Vote::where('position_id', $position_id)
            ->where('year', date('Y'))
            ->pluck('voter');

        $voters = [];
        for($i = 1;$i <= $voter_count;$i++) {
            if(in_array($i, $already_voted->toArray())) {
                continue;
            };

            $voters[] = $i;
        }

        return $voters;
    }
}
