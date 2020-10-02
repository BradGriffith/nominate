<?php

namespace App\Models;

use App\Models\Position;
use App\Models\Nomination;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    public static function getWinners($position_id = null, $year = null) {
        if($position_id === null) {
          $position_id = Position::getDefault()->id;
        }
        if($year === null) {
          $year = date('Y');
        }
        $winning_ids = static::where('year', date('Y'))
            ->where('position_id', $position_id)
            ->groupBy('nomination_id')
            ->selectRaw('sum(rank) as sum, nomination_id')
            ->pluck('sum','nomination_id');

        $winners = [];
        foreach($winning_ids as $nomination_id => $count) {
            $winners[] = Nomination::find($nomination_id);
        }

        return $winners;
      }
}
