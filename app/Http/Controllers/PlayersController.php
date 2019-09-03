<?php
namespace App\Http\Controllers;

use App\FantasyTeams;
use App\Http\Controllers;
use App\Players;
use App\PlayerTeam;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayersController extends Controller
{

    private $request;
    public $gameweek_number;
    public $current_week;
    public $previous_week;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->gameweek_number = settingValue('gameweek_number');
        $this->current_week = (int) $this->gameweek_number;
        $this->previous_week = $this->current_week - 1;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response['players'] = Players::join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*')->orderBy('cost', 'desc')->get();
        $response['team_buget'] = FantasyTeams::where('user_id', Auth::user()->id)->first()->budget;
        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', Auth::user()->id)->first()->toArray();
        $response['team_id'] = FantasyTeams::where('user_id', Auth::user()->id)->first()->id;
        $response['user_team_players'] = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $response['team_id'])->get()->toArray();
        //$response['players'] = array_diff($response['players'], array($response['user_team_players']));
        /*foreach ($response['user_team_players'] as $key => $val) {
        $index = array_search($val['name'], array_column($response['players'], 'name'));
        // echo 'outsite if \n'.$index;
        // print_r($val['name']);
        // echo '\n';
        if ($index !== false) {
        //array_diff($response['players'], array($val['name']))
        unset($response['players'][$index]);
        //echo 'inside if '.$index;
        }
        }*/
        $gameweek_number = settingValue('gameweek_number');
        $current_week = (int) $gameweek_number;
        $previous_week = ($current_week == 0) ? 0 : $current_week - 1;
        foreach ($response['players'] as $key => $val) {
            $val->points = get_player_gameweek_points_history_my_team($val->id, $FantasyTeams['id'], $previous_week);
        }
        $response['players'] = array_values($response['players']->toArray());

        return response()->json($response);

    }

    public function transfers()
    {
        $user = Auth::user();
        $budget_exist = FantasyTeams::where(['user_id' => $user->id])->first()->toArray();
        $response['players'] = Players::join('transfers', 'transfers.player_id', '=', 'player.id')->join('player_gameweek', 'player_gameweek.player_id', '=', 'player.id')->join('clubs', 'clubs.id', '=', 'player.club')->select('transfers.*', 'clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_gameweek.*', 'player_gameweek.player_id as id')->where('transfers.team_id', $budget_exist['id'])->groupBy('player_gameweek.player_id')->orderBy('cost', 'desc')->get()->toArray();
        $response['team_buget'] = FantasyTeams::where('user_id', Auth::user()->id)->first()->budget;
        return response()->json($response);

    }

    public function single_player_details($playerid)
    {
        $user = Auth::user();
        $gameweek_number = settingValue('gameweek_number');
        $current_week = (int) $gameweek_number;
        $previous_week = ($current_week == 0) ? 0 : $current_week - 1;
        $budget_exist = FantasyTeams::where(['user_id' => $user->id])->first()->toArray();
        $array['player_game_week'] = Players::join('player_gameweek', 'player_gameweek.player_id', '=', 'player.id')
            ->select('player.*', 'player_gameweek.*')
            ->where('player_gameweek.player_id', $playerid)->where('player_gameweek.week_number', $current_week)->first();

        if (empty($array['player_game_week'])) {
            $array['player_game_week'] = [];
        } else {
            $array['player_game_week'] = $array['player_game_week']->toArray();
        }

        $array['PlayerDetail'] = Players::join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*')->where('player.id', $playerid)->get()->toArray();

        $array['sell_PlayerDetail'] = Players::join('clubs', 'clubs.id', '=', 'player.club')->join('player_to_team', 'player.id', '=', 'player_to_team.player_id')->select('player_to_team.*')
            ->where('player.id', $playerid)->where('player_to_team.team_id', $budget_exist['id'])->get()->toArray();

        //$array['PlayerDetail'] = array_merge($array['PlayerDetail'][0], (count($array['sell_PlayerDetail']) > 0 ) ? $array['sell_PlayerDetail'][0] : []);

        $already_taken = PlayerTeam::where('player_id', $playerid)->where('team_id', $budget_exist['id'])->count();
        if ($already_taken > 0) {
            $array['already_taken'] = 'Already in team';
        } else {
            $array['already_taken'] = 0;
        }

        $array['benchselection'] = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->select('player_to_team.*', 'player.*')->where('player_to_team.team_id', $budget_exist['id'])->get()->toArray();
        $array['week_of_year'] = $current_week;
        $array['gameweek_number'] = settingValue('gameweek_number');
        //$array['player_gameweek_points'] = get_player_gameweek_points_history_my_team($playerid, $budget_exist['id'], $previous_week);
        $array['player_gameweek_points'] = getPlayerGameWeekDataHistory($playerid, $previous_week, $budget_exist['id']);

        return response()->json($array);

    }

    public function switch_team_bench_player($bench_player_id, $player_id)
    {
        $user = Auth::user();

        $budget_exist = FantasyTeams::where(['user_id' => $user->id])->first()->toArray();
        $team_id = $budget_exist['id'];

        $player_details = Players::where(['id' => $player_id])->first()->toArray();
        $bench_player_details = Players::where(['id' => $bench_player_id])->first()->toArray();

        $query = \DB::select(\DB::raw("update player_to_team a
        inner join player_to_team b on a.player_id <> b.player_id
          set a.player_club = b.player_club,
              a.player_cost = b.player_cost,
              a.position_used_for = b.position_used_for,
              a.c_v_c = b.c_v_c,
              a.on_bench = b.on_bench
        where a.player_id in ('$bench_player_id','$player_id') and b.player_id in ('$bench_player_id','$player_id') and a.team_id = '$team_id' and b.team_id = '$team_id'"));

        return response()->json([
            'message' => 'Player has been swaped with bench player successfully',
            'status' => '200']);
    }

    public function c_v_c($player_id, $c_v_c)
    {
        $user = Auth::user();

        $budget_exist = FantasyTeams::where(['user_id' => $user->id])->first()->toArray();

        $player_details = Players::where(['id' => $player_id])->first()->toArray();

        if ($c_v_c > 0) {
            $captain_vice_captain = \DB::table('player_to_team')->where('c_v_c', $c_v_c)->where(['team_id' => $budget_exist['id']])->get()->toArray();

            if (count($captain_vice_captain) <= 0) {

            } else {
                foreach ($captain_vice_captain as $k => $cvc) {
                    $cvcupdate = array(
                        'c_v_c' => 0,
                    );
                    PlayerTeam::where(['player_id' => $cvc->player_id])->where(['team_id' => $budget_exist['id']])->update($cvcupdate);
                }
            }
        }

        $cvcupdate = array(
            'c_v_c' => $c_v_c,
        );

        PlayerTeam::where(['player_id' => $player_id])->where(['team_id' => $budget_exist['id']])->update($cvcupdate);

        return response()->json(['selected' => $c_v_c,
            'message' => 'Player type has been updated successfully',
            'status' => '200']);
    }

    // NOTE: no more than 3 players can be bought from a single club  DONE

    // NOTE: only 1 goal keeper can be selected the other goal keeper will be bench goalkeeper default

    // NOTE: budget will be consumed once a single player is added

    public function buy($position_used_for, $player_id, $c_v_c)
    {

        //$input = $this->request->all();
        $user = Auth::user();

        $budget_exist = FantasyTeams::where(['user_id' => $user->id])->first()->toArray();

        $player_exist = PlayerTeam::where(['team_id' => $budget_exist['id']])->where(['player_id' => $player_id])->get();
        if (!empty($player_exist->toArray())) {
            return response()->json(['player_to_team' => [],
                'message' => 'You have this player already',
                'status' => '401']);
        }

        if (((float) $budget_exist['budget'] <= 0.00)) {
            return response()->json(['player_to_team' => [],
                'message' => 'You haven’t got enough money to buy this player',
                'status' => '401']);
        }

        $player_details = Players::where(['id' => $player_id])->first()->toArray();

        if (((float) $budget_exist['budget'] < (float) $player_details['cost'])) {
            return response()->json(['player_to_team' => [],
                'message' => 'You haven’t got enough money to buy this player',
                'status' => '401']);
        }

        if ($c_v_c > 0) {
            $captain_vice_captain = \DB::table('player_to_team')->where('c_v_c', $c_v_c)->where(['team_id' => $budget_exist['id']])->get()->toArray();

            if (count($captain_vice_captain) <= 0) {

            } else {
                foreach ($captain_vice_captain as $k => $cvc) {
                    $cvcupdate = array(
                        'c_v_c' => 0,
                    );
                    PlayerTeam::where(['player_id' => $cvc->player_id])->where(['team_id' => $budget_exist['id']])->update($cvcupdate);
                }
            }
        }

        // echo '<pre>';
        // print_r($budget_exist);
        // print_r($captain_vice_captain);
        // die();

        $player_to_team_club_count = \DB::table('player_to_team')->where(['player_club' => $player_details['club']])->where(['team_id' => $budget_exist['id']])->count();
        if (($player_to_team_club_count > 2)) {
            return response()->json(['player_to_team' => [],
                'message' => 'Oops! You can only pick three players from a single club',
                'status' => '401']);
        }

        \DB::beginTransaction();
        try {
            $insert_player_to_team = array(
                'player_id' => $player_id,
                'team_id' => $budget_exist['id'],
                'player_cost' => $player_details['cost'],
                'on_bench' => ($position_used_for == 'bgk' || $position_used_for == 'bp1' || $position_used_for == 'bp2' || $position_used_for == 'bp3') ? 1 : 0,
                'position_used_for' => $position_used_for,
                'c_v_c' => $c_v_c,
                'player_club' => $player_details['club'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            );

            PlayerTeam::forceCreate($insert_player_to_team);

            $team_budget_update = $budget_exist['budget'] - $player_details['cost'];
            $toupdate = array(
                'budget' => $team_budget_update,
            );
            FantasyTeams::where(['id' => $budget_exist['id']])->update($toupdate);

            $boughtstatusupdate = array(
                'bought_status' => 1,
            );
            Players::where(['id' => $player_id])->update($boughtstatusupdate);

            \DB::commit();

            $player_to_team = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player.id', $player_id)->first()->toArray();

            return response()->json(['player_to_team' => $player_to_team,
                'message' => 'Player has been added to your team successfully',
                'status' => '200']);

        } catch (\Exception $req) {
            \DB::rollBack();
            return $Responsearray = array(
                'Ex' => $req->getMessage(),
                'ErrorMessage' => 'Error Buying Player',
                'status' => 401,
            );
        }

    }

// NOTE: Budget will be updated in the user team table  DONE

// NOTE: bought_status wil be updated back to 0 when user will sell this player  from player table DONE

// NOTE: Free_transfer_used bit will be updated in team table  DONE

// NOTE: Delete player from player_to_team table DONE

// NOTE: Deductions will be inserted into Deductions table if more than 1 free_transfer_used DONE

// NOTE: Entry in Transfers table for that game week if count == 0 then free transfer

// NOTE:  more than 1 free_transfer_per_game_week will cost –4 points from team’s points team table

    public function sell($player_id, $threeprops = "")
    {
        $user = Auth::user();
        $gameweek_number = settingValue('gameweek_number');
        $current_week = (int) $gameweek_number;
        $previous_week = ($current_week == 0) ? 0 : $current_week - 1;

        $budget_exist = FantasyTeams::where(['user_id' => $user->id])->first()->toArray();

        $player_details = Players::where(['id' => $player_id])->first()->toArray();

        $player_exist = PlayerTeam::where(['team_id' => $budget_exist['id']])->where(['player_id' => $player_id])->first();

        if (!empty($player_exist)) {

        } else {
            return response()->json([
                'message' => 'This player is no longer in your team or sold',
                'status' => '200']);
        }

        \DB::beginTransaction();
        try {

            //  Deletes the player from the team
            PlayerTeam::where('player_id', $player_id)->where('team_id', $budget_exist['id'])->delete();
            //  Deletes the player from the team

            $team_budget_update = $budget_exist['budget'] + $player_details['cost'];

            $transfersexist = \DB::table('transfers')->where('team_id', $budget_exist['id'])->where('week_number', $current_week)->count();

            $transfers = array(
                'team_id' => $budget_exist['id'],
                'user_id' => $user->id,
                'player_id' => $player_id,
                'week_number' => $current_week,
                'transfer_type' => ($transfersexist > 0 && $threeprops != "wildcard_used") ? 1 : 0,
            );

            \DB::table('transfers')->insert($transfers);

            $toupdate = array(
                'budget' => $team_budget_update,
            );

            if ($transfersexist > 1 && $threeprops != "wildcard_used") {

                $deductions = array(
                    'team_id' => $budget_exist['id'],
                    'week_number' => $current_week,
                    'number_of_deducted_points' => ($current_week == 0) ? 0 : 4,
                );
                \DB::table('deductions')->insert($deductions);
            } else {
                $deductions = array(
                    'team_id' => $budget_exist['id'],
                    'week_number' => $current_week,
                    'number_of_deducted_points' => 0,
                );
                \DB::table('deductions')->insert($deductions);
            }

            FantasyTeams::where(['id' => $budget_exist['id']])->update($toupdate);

            $boughtstatusupdate = array(
                'bought_status' => 0,
            );
            Players::where(['id' => $player_id])->update($boughtstatusupdate);

            \DB::commit();

            return response()->json([
                'message' => 'This player is no longer in your team',
                'status' => '200']);

        } catch (\Exception $req) {
            \DB::rollBack();
            return $Responsearray = array(
                'Ex' => $req->getMessage(),
                'ErrorMessage' => 'Error Selling Player',
                'status' => 401,
            );
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        $input = $this->request->all();
        $finalvalidation = $this->validate($this->request, [
            'Players' => 'required',
            'user_id' => 'required',
            'version_id' => 'required',
        ]);

        $toinsert = array(
            'Players' => $input['Players'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $input['user_id'],
            'version_id' => $input['version_id'],
        );

        \DB::table('Players')->insert($toinsert);

        $Players = Players::where('version_id', $input['version_id'])->orderBy('created_at', 'desc')->get()->toArray();
        $version = Version::where('id', $input['version_id'])->first();

        return ['Players' => response()->json($Players),
            'user_id' => $version->user_id,
            'message' => 'Players has been created successfully',
            'status' => '1'];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Players  $Players
     * @return \Illuminate\Http\Response
     */
    public function show(Players $Players)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Players  $Players
     * @return \Illuminate\Http\Response
     */
    public function edit(Players $Players)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Players  $Players
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Players $Players)
    {
        $input = $this->request->all();
        $findidorfail = Players::where('id', $input['id'])->first();
        if ($findidorfail === null) {
            return ['message' => 'Players doesn\'t exist'];
            die();
        }

        $finalvalidation = $this->validate($this->request, [
            'Players' => 'required',
            'user_id' => 'required',
            'version_id' => 'required',
        ]);

        $toupdate = array(
            'Players' => $input['Players'],
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $input['user_id'],
            'version_id' => $input['version_id'],
        );

        \DB::table('Players')->where('id', $input['id'])->update($toupdate);

        return ['Players' => response()->json(Players::where('id', $input['id'])->get()->toArray()),
            'message' => 'Players name has been updated successfully',
            'status' => '1'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Players  $Players
     * @return \Illuminate\Http\Response
     */
    public function destroy(Players $Players)
    {
        $input = $this->request->all();
        $findidorfail = Players::where('id', $input['id'])->first();
        if ($findidorfail === null) {
            return ['message' => 'Players doesn\'t exist'];
            die();
        }

        \DB::table('Players')->where('id', $input['id'])->delete();

        return ['message' => 'Players has been deleted successfully'];
    }

    /**
     * buy_player api
     *
     * @return \Illuminate\Http\Response
     */
    public function buy_player(Request $request)
    {

    }

    /**
     * sell_player api
     *
     * @return \Illuminate\Http\Response
     */
    public function sell_player(Request $request)
    {

    }

    /**
     * get_player api
     *
     * @return \Illuminate\Http\Response
     */
    public function get_player(Request $request)
    {

    }

    /**
     * get_player api
     *
     * @return \Illuminate\Http\Response
     */
    public function get_all_player(Request $request)
    {

    }

}
