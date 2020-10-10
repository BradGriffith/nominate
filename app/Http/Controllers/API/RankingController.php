<?php

namespace App\Http\Controllers\API;

use App\Events\Resultsevent;
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
        $created_at = date('Y-m-d H:i:s');
        foreach($request->input('ranks') as $rank) {
          $nomination_id = $rank['id'];
          $rank = $rank['rank'];

          $ranks[] = compact([
            'voter',
            'position_id',
            'nomination_id',
            'year',
            'rank',
            'created_at',
          ]);
        }

        $inserted = Ranking::insert($ranks);
        event(new ResultsEvent());
        return $inserted;
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
        return Nomination::getNomineesForRanking($position_id);
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
