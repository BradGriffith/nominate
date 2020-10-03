<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Nomination;
use App\Models\Position;
use App\Models\Ranking;
use App\Models\Vote;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $voter = $request->input('voter');
        $position_id = $request->input('position_id');
        $year = date('Y');

        // Check if votes have been submitted yet

        $ranks = [];
        foreach($request->input('ranks') as $rank => $nomination_id) {
            if(empty($nomination_id)) {
                continue;
            }

          $ranks[] = compact([
            'voter',
            'position_id',
            'nomination_id',
            'year',
            'rank',
          ]);
        }

        return Ranking::insert($ranks);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function getNominees($position_id) {
        $nominee_min_count = Position::where('id',$position_id)->first()->num_to_select;

        //get the top $nominee_min_count nominees by number of votes
        $nominees = Nomination::withCount('votes')
            ->where('position_id',$position_id)
            ->where('year', date('Y'))
            ->orderBy('votes_count','desc')
            ->limit($nominee_min_count)
            ->get();

        // find the minimum number of votes you'd have to get to tie for last place
        $min_votes = $nominees[min(count($nominees), $nominee_min_count)-1]->votes_count;

        // get all nominees with at least as many votes as the nominee in $nominee_min_count-th place
        $nominees_final = Nomination::withCount('votes')
            ->where('position_id',$position_id)
            ->where('year', date('Y'))
            ->having('votes_count','>=', $min_votes)
            ->get();

        return $nominees_final;
    }

    public function getRankers($position_id) {
      $already_ranked = Ranking::where('position_id', $position_id)
        ->where('year', date('Y'))
        ->pluck('voter');

      $voters = [];
      for($i = 1;$i <= 24;$i++) {
        if(in_array($i, $already_ranked->toArray())) {
          continue;
        };

        $voters[] = $i;
      }

      return $voters;
    }
}
