<?php

namespace App\Models;

use App\Events\ResultsEvent;
use App\Models\Position;
use App\Models\Nomination;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    public function nomination() {
      return $this->belongsTo('App\Models\Nomination');
    }

    public static function getRankedVoters($position_id = null) {
        $position_id = is_null($position_id) ? Position::getDefault()->id : $position_id;

        $ranker_count = \Config::get('fcc.voter_count');

        return static::where('position_id', $position_id)
            ->where('year', date('Y'))
            ->distinct()
            ->pluck('voter')
            ->toArray();
    }

    public static function getUnrankedVoters($position_id = null) {
        $position_id = is_null($position_id) ? Position::getDefault()->id : $position_id;

        $ranker_count = \Config::get('fcc.voter_count');

        $already_ranked = static::getRankedVoters();

        return array_diff(range(1,$ranker_count), $already_ranked);
    }

    public static function getWinners($position_id = null, $year = null) {
      if($position_id === null) {
        $position_id = Position::getDefault()->id;
      }
      if($year === null) {
        $year = date('Y');
      }
      $winners = static::where('year', date('Y'))
          ->where('position_id', $position_id)
          ->groupBy('nomination_id')
          ->selectRaw('sum(`rank`) as sum, nomination_id')
          ->orderBy('sum')
          ->with('nomination')
          ->get();

      return $winners;
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            event(new ResultsEvent());
        });
    }
}
