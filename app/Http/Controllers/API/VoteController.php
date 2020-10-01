<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
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

        $votes = [];
        foreach($request->input('votes') as $nomination_id) {
          $votes[] = compact([
            'voter',
            'position_id',
            'nomination_id',
            'year',
          ]);
        }

        return Vote::insert($votes);
    }

    public function getVoters($position_id) {
      $already_voted = Vote::where('position_id', $position_id)
        ->where('year', date('Y'))
        ->pluck('voter');

      $voters = [];
      for($i = 1;$i <= 24;$i++) {
        if(in_array($i, $already_voted->toArray())) {
          continue;
        };

        $voters[] = $i;
      }

      return $voters;
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
