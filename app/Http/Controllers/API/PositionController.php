<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Position::all();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Position::find($id);
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
        $position = Position::find($id);
        $status = $request->get('status');
        $num_to_select = $request->get('num_to_select');

        if($status) {
                $position->status = $status;
        }
        if($num_to_select) {
                $position->num_to_select = $num_to_select;
        }
        $position->save();
        return $position;
    }

    public function setDefault(Request $request)
    {
        $new_position_id = $request->get('position_id');
        Position::where('id', '!=', $new_position_id)->update(['is_default' => false, 'status' => 'vote']);
        Position::find($new_position_id)->update(['is_default' => true, 'status' => 'vote']);
        return Position::getDefault();
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
