<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    use HasFactory;

    public function getNameAttribute() {
      return $this->last_name . ', ' . $this->first_name;
    }

    public $appends = [
      'name',
    ];

    public function scopeWinners($query, $position_id = null, $year = null) {
      return Nomination::whereIn('id',Ranking::getWinners($position_id, $year)->pluck('nomination_id'));
    }

    public function votes() {
      return $this->hasMany('App\Models\Vote');
    }

    public function ranks() {
      return $this->hasMany('App\Models\Ranking');
    }

    public static function getNomineesForRanking($position_id = null)
    {
      $position_id = is_null($position_id) ? Position::getDefault()->id : $position_id;

      $nominee_min_count = Position::find($position_id)->num_to_select;

      //get the top $nominee_min_count nominees by number of votes
      $nominees = static::withCount('votes')
          ->where('position_id',$position_id)
          ->where('year', date('Y'))
          ->having('votes_count', '>', 0)
          ->orderBy('votes_count','desc')
          ->limit($nominee_min_count)
          ->get();

      if(count($nominees) == 0) {
        return [];
      }

      // find the minimum number of votes you'd have to get to tie for last place
      $min_votes = $nominees[min(count($nominees), $nominee_min_count)-1]->votes_count;

      // get all nominees with at least as many votes as the nominee in $nominee_min_count-th place
      return static::withCount('votes')
          ->where('position_id',$position_id)
          ->where('year', date('Y'))
          ->having('votes_count','>=', $min_votes)
          ->get();
    }
}
