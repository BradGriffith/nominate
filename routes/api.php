<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::put('positions/default', 'App\Http\Controllers\API\PositionController@setDefault');

Route::apiResources([
  'votes' => App\Http\Controllers\API\VoteController::class,
  'positions' => App\Http\Controllers\API\PositionController::class,
  'ranks' => App\Http\Controllers\API\RankingController::class,
  'nominations' => App\Http\Controllers\API\NominationController::class
]);

Route::get('voters/{position_id}', 'App\Http\Controllers\API\VoteController@getVoters');
Route::get('rankers/{position_id}', 'App\Http\Controllers\API\RankingController@getRankers');

Route::get('ranks/nominees/{position_id}', 'App\Http\Controllers\API\RankingController@getNominees');

Route::get('results', 'App\Http\Controllers\API\NominationController@getResults');

Route::post('nominations/preview', 'App\Http\Controllers\API\NominationController@preview');
Route::post('nominations/import', 'App\Http\Controllers\API\NominationController@import');
Route::get('nominations/position/{position_id}', 'App\Http\Controllers\API\NominationController@getByPosition');
Route::get('nominations/{id}/has-votes-or-ranks', 'App\Http\Controllers\API\NominationController@hasVotesOrRanks');

Route::delete('votes/{position_id}/{voter}', 'App\Http\Controllers\API\VoteController@destroy');
Route::delete('ranks/{position_id}/{ranker}', 'App\Http\Controllers\API\RankingController@destroy');
