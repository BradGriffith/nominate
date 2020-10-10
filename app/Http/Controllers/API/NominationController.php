<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nomination;
use App\Models\Position;
use App\Models\Ranking;
use App\Models\Vote;

class NominationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Nomination::where('position_id', $request->get('position_id'))
          ->where('year', date('Y'))
          ->get();
    }

    public function getResults() {
        $voterNumbers = range(1, \Config::get('fcc.voter_count'));
        $position = Position::getDefault();
        $votersReceived = Vote::getVotedVoters();
        $rankersReceived = Ranking::getRankedVoters();
        $votesCount = Vote::count();
        $ranksCount = Ranking::count();

        return compact([
            'voterNumbers',
            'position',
            'votersReceived',
            'rankersReceived',
            'votesCount',
            'ranksCount',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
