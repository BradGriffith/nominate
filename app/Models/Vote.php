<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    public static function getVotedVoters($position_id = null) {
        $position_id = is_null($position_id) ? Position::getDefault()->id : $position_id;

        $voter_count = \Config::get('fcc.voter_count');

        return Vote::where('position_id', $position_id)
            ->where('year', date('Y'))
            ->distinct()
            ->pluck('voter')
            ->toArray();
    }

    public static function getUnvotedVoters($position_id = null) {
        $position_id = is_null($position_id) ? Position::getDefault()->id : $position_id;

        $voter_count = \Config::get('fcc.voter_count');

        $already_voted = static::getVotedVoters();

        return array_diff(range(1,$voter_count), $already_voted);
    }
}
