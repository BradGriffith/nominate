<?php

namespace App\Http\Controllers\API;

use App\Events\ResultsEvent;
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
        $created_at = date('Y-m-d H:i:s');
        foreach($request->input('votes') as $nomination_id) {
          $votes[] = compact([
            'voter',
            'position_id',
            'nomination_id',
            'year',
            'created_at',
          ]);
        }

        $inserted = Vote::insert($votes);
        event(new ResultsEvent());
        return $inserted;
    }

    public function getVoters($position_id) {
      return Vote::getUnvotedVoters($position_id);
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
    public function destroy(Request $request, $position_id, $voter)
    {
        $year = date('Y');

        $query = Vote::where('position_id',$position_id)
                ->where('year',$year);

        if($voter != 'all') {
                $query->where('voter',$voter);
        }

        $result = $query->delete();
        event(new ResultsEvent());

        return $result;
    }
}
