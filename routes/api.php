<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register the API routes for your application as
| the routes are automatically authenticated using the API guard and
| loaded automatically by this application's RouteServiceProvider.
|
 */

Route::get('/social/{provider}', 'SocialiteController@redirectToProvider');
Route::get('/social/{provider}/callback', 'SocialiteController@handleProviderCallback');

Route::post('/register', 'RegisterController@register');
Route::post('/login', 'LoginController@login');
Route::get('/team/detail/{id}', 'PlayersController@single_player_details');
Route::get('/fantasyfootballset', 'TeamController@site_settings');

Route::post('/user/forgotpassword', 'UserController@forgotpassword');
    Route::post('/user/resetpassword', 'UserController@resetpassword');

// Route::middleware('auth:api')->group(function () {
//     Route::post('/logout', 'LoginController@logout');

//});

Route::group([
    'middleware' => 'auth:api',
], function () {
    Route::get('/logout', 'LoginController@logout');
    Route::post('/team/create', 'TeamController@store');
    Route::post('/team/edit', 'TeamController@edit');
    Route::post('/league/create', 'LeagueController@store');
    Route::post('/league/join', 'LeagueController@join_league');
    Route::get('/league/all', 'LeagueController@userleagues');
    Route::get('/league/details/{id}', 'LeagueController@index');
    Route::get('/league/leave/{id}', 'LeagueController@leave_league');
    Route::get('/team/detail', 'TeamController@index');
    Route::get('/team/show/{id}', 'TeamController@show');
    Route::get('team/overview', 'TeamController@get_all_team_members');
    Route::get('team/update_triple_values/{key}/{values}/{weeknumberkey}', 'TeamController@update_triple_values');
    Route::get('/players/list', 'PlayersController@index');
    Route::get('/transfers/all', 'PlayersController@transfers');
    Route::get('/players/detail/{id}', 'PlayersController@single_player_details');
    Route::get('/players/buy/{position_used_for}/{player_id}/{c_v_c}', 'PlayersController@buy');
    Route::get('/players/c_v_c/{player_id}/{c_v_c}', 'PlayersController@c_v_c');
    Route::get('/players/switch_team_bench_player/{bench_player_id}/{player_id}', 'PlayersController@switch_team_bench_player');
    Route::get('/players/sell/{player_id}', 'PlayersController@sell');
    Route::get('/user/profile', 'UserController@details');
    Route::post('/user/team_update', 'UserController@team_update');
    Route::post('/user/name_update', 'UserController@name_update');
    Route::post('/user/email_update', 'UserController@email_update');
    Route::post('/user/password_update', 'UserController@password_update');
    Route::get('/user/delete_user', 'UserController@destroy');

//////////////////////////////// Gameplay APIs
Route::get('/team/gameplay/details', 'GamePlayController@get_team_score_current_gameweek');
Route::get('/team/gameplay/details/allweeks', 'GamePlayController@get_team_score_all_gameweek');
//////////////////////////////// Gameplay APIs
});
