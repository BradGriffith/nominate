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
