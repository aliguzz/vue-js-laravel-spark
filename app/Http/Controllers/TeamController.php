<?php
namespace App\Http\Controllers;

use App\FantasyTeams;
use App\Http\Controllers;
use App\Http\Controllers\PlayersController;
use App\PlayerTeam;
use App\SiteSettings;
use App\UserTeam;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Team;

class TeamController extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->endpoint = !empty($_ENV['ENDPOINT_URL']) ? $_ENV['ENDPOINT_URL'] : 'https://' . $_SERVER['SERVER_NAME'];

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Auth::user()->id;
        $array = $this->build_team($user_id);
        return response()->json(
            $array
        )->header('Content-Type', 'text/plain');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\FantasyTeams  $FantasyTeams
     * @return \Illuminate\Http\Response
     */
    public function show(int $team_id)
    {
        $array = $this->show_team($team_id);
        return response()->json(
            $array
        )->header('Content-Type', 'text/plain');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
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
        $user_id = Auth::user()->id;
        $input = $this->request->all();
        $finalvalidation = $this->validate($this->request, [
            'name' => 'required',
            'formation' => 'required',
        ]);

        $toinsertspark = array(
            'name' => $input['name'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'owner_id' => $user_id,
        );

        \DB::beginTransaction();
        try {
            $is_exist = Team::where(['name' => $input['name']])->first();
            if (!empty($is_exist)) {
                return [
                    'error' => 'Team with same name already exists',
                    'code' => 422,
                ];

            }

            $team = Team::forceCreate($toinsertspark);
            $team_id = $team->id;
            $toinsert = array(
                'name' => $input['name'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_id' => $user_id,
                'formation' => $input['formation'],
                'spark_team_id' => $team_id,
                'budget' => settingValue('budget'),
            );

            FantasyTeams::forceCreate($toinsert);

            $userteam = array(
                'user_id' => $user_id,
                'team_id' => $team_id,
            );

            UserTeam::forceCreate($userteam);
            \DB::commit();

            $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user_id)->orderBy('created_at', 'desc')->get()->toArray();

            return response()->json(['FantasyTeam' => $FantasyTeams,
                'user_id' => $user_id,
                'message' => $input['name'] . ' has been created successfully',
                'status' => '200']);

        } catch (\Exception $req) {
            \DB::rollBack();
            return $Responsearray = array(
                'Ex' => $req->getMessage(),
                'ErrorMessage' => 'Error creating Team',
                'status' => 422,
            );
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FantasyTeams  $FantasyTeams
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $user_id = Auth::user()->id;
        $input = $this->request->all();
        $finalvalidation = $this->validate($this->request, [
            'name' => 'required',
            'formation' => 'required',
        ]);

        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user_id)->orderBy('created_at', 'desc')->first()->toArray();

        $player_to_team = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $FantasyTeams['id'])->get()->toArray();

        $defenders = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $FantasyTeams['id'])->where('position_used_for', 'like', 'def' . '%')->get()->toArray();

        $midfielders = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $FantasyTeams['id'])->where('position_used_for', 'like', 'mid' . '%')->get()->toArray();

        $forwards = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $FantasyTeams['id'])->where('position_used_for', 'like', 'for' . '%')->get()->toArray();

        $my_team_formation = $FantasyTeams['formation'];
        $input_team_formation = $input['formation'];

        $my_team_formation = explode('-', $my_team_formation);
        $input_team_formation = explode('-', $input_team_formation);
        $this->array_sort_by_column($defenders, 'position_used_for');
        $this->array_sort_by_column($midfielders, 'position_used_for');
        $this->array_sort_by_column($forwards, 'position_used_for');
        $d = 0; $m = 0; $f = 0;
        for ($i = 0; $i < count($defenders); $i++) {
            $d = (int)$i+1;
            $toinsertspark = array(
                'position_used_for' => 'def' . (string) $d,
            );
            PlayerTeam::where(['player_id'=> $defenders[$i]['id'], 'team_id' => $defenders[$i]['team_id']])->update($toinsertspark);
        }
        
        for ($i = 0; $i < count($midfielders); $i++) {
            $m = (int)$i+1;
            $toinsertspark = array(
                'position_used_for' => 'mid' . (string) $m,
            );
            PlayerTeam::where(['player_id'=> $midfielders[$i]['id'], 'team_id' => $midfielders[$i]['team_id']])->update($toinsertspark);
        }
       
        for ($i = 0; $i < count($forwards); $i++) {
            $f = (int)$i+1;
            $toinsertspark = array(
                'position_used_for' => 'for' . (string) $f,
            );
            PlayerTeam::where(['player_id'=> $forwards[$i]['id'], 'team_id' => $forwards[$i]['team_id']])->update($toinsertspark);
        }

        if (count($defenders) > (int) $input_team_formation[0] && !empty($defenders)) {
                return $response = array(
                        'error' => 'Invalid Team formation, Please sell the defenders before changing formation',
                        'code' => 422,
                );

        }

        if (count($midfielders) > (int) $input_team_formation[1] && !empty($midfielders)) {
            return $response = array(
                'error' => 'Invalid Team formation, Please sell the midfielders before changing formation',
                'code' => 422,
        );

        }

        if (count($forwards) > (int) $input_team_formation[2] && !empty($forwards)) {
            return $response = array(
                'error' => 'Invalid Team formation, Please sell the forwards before changing formation',
                'code' => 422,
            );
        }

        
        
        $toinsertspark = array(
            'name' => $input['name'],
        );

        \DB::beginTransaction();
        try {
            $team = Team::where('id', $FantasyTeams['id'])->update($toinsertspark);
            //$team_id = $team->id;
            $toinsert = array(
                'name' => $input['name'],
                'formation' => $input['formation'],
            );

            FantasyTeams::where('id', $FantasyTeams['id'])->update($toinsert);

            \DB::commit();

            //$FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user_id)->orderBy('created_at', 'desc')->get()->toArray();

            return response()->json([
                'user_id' => $user_id,
                'message' => $input['name'] . ' has been updated successfully',
                'status' => '200']);

        } catch (\Exception $req) {
            \DB::rollBack();
            return $Responsearray = array(
                'Ex' => $req->getMessage(),
                'ErrorMessage' => 'Error updating Team',
                'status' => 422,
            );
        }

    }

    function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            $sort_col[$key] = $row[$col];
        }
    
        array_multisort($sort_col, $dir, $arr);
    }
    
    
   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FantasyTeams  $FantasyTeams
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user_id = Auth::user()->id;
        $input = $this->request->all();
        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user_id)->orderBy('created_at', 'desc')->first()->toArray();

        // echo '<pre>';
        // print_r($FantasyTeams['sparkteam'][0]['name']);
        // die();
        $toupdate = array(
            'name' => $input['name'],
            'updated_at' => Carbon::now(),
        );
        $team = Team::where('id', $FantasyTeams['id'])->update($toupdate);

        $toupdatespark = array(
            'name' => $input['name'],
            'updated_at' => Carbon::now(),
        );

        FantasyTeams::where('id', $FantasyTeams['sparkteam'][0]['id'])->update($toupdatespark);

        return response()->json([
            'team_name' => $input['name'],
            'message' => $input['name'] . ' has been updated successfully',
            'status' => '200']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FantasyTeams  $FantasyTeams
     * @return \Illuminate\Http\Response
     */
    public function destroy(FantasyTeams $FantasyTeams)
    {
        $input = $this->request->all();
        $findidorfail = FantasyTeams::where('id', $input['id'])->first();
        if ($findidorfail === null) {
            return ['message' => 'FantasyTeams doesn\'t exist'];
            die();
        }

        \DB::table('FantasyTeams')->where('id', $input['id'])->delete();

        return ['message' => 'FantasyTeams has been deleted successfully'];
    }

    /**
     * get_remaining_budget api
     *
     * @return \Illuminate\Http\Response
     */
    public function get_remaining_budget(Request $request)
    {

    }

    /**
     * banned words list
     */
    public function banned_words()
    {

        $array = settingValue("banned_wordslist");
        return response()->json(
            $array
        )->header('Content-Type', 'text/plain');
    }

    /**
     * validate_team api
     *
     * @return \Illuminate\Http\Response
     */
    public function validate_team(Request $request)
    {

    }

    /**
     * update triple values api
     *
     * @return \Illuminate\Http\Response
     */
    public function update_triple_values($key, $values, $weeknumberkey = '')
    {

        $user = Auth::user();
        $gameweek_number = settingValue('gameweek_number');
        $current_week = (int) $gameweek_number;
        $previous_week = ($current_week == 0) ? 0 : $current_week - 1;
        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user->id)->first()->toArray();
        if ($weeknumberkey == 'wildcard_used') {
            $toupdatespark = array(
                $key => $values,
                'updated_at' => Carbon::now(),
            );
        } else {
            $toupdatespark = array(
                $key => $values,
                $weeknumberkey => ($weeknumberkey != '') ? $current_week : -1,
                'updated_at' => Carbon::now(),
            );
        }

        $team = \DB::table('team')->where('id', $FantasyTeams['id'])->update($toupdatespark);

        $team_players = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $FantasyTeams['id'])->get()->toArray();

        if ($key == "wildcard_used") {
            foreach ($team_players as $player) {
                $updated = (new PlayersController($this->request))->sell($player['player_id'], "wildcard_used");
            }
        }

        //$teampoints = getTeamTotalPoints($team_id) - getTotalTeamDeductions($team_id);
        //$teampoints = getUserTeamWeekTotalPoints($FantasyTeams['id'], $previous_week) - getTeamDeductionsByWeek($FantasyTeams['id'], $previous_week);
        $teampoints = get_player_gameweek_points_history($FantasyTeams['id'], $previous_week) - getTeamDeductionsByWeek($FantasyTeams['id'], $previous_week);

        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user->id)->first()->toArray();
        return response()->json([
            'team_total_points' => floatval($teampoints),
            'fantasy_team' => $FantasyTeams,
            'team_players' => $team_players,
            'message' => $key . ' value has been updated',
            'status' => 200,
        ]);
    }

    /**
     * get_all_team_members api
     *
     * @return \Illuminate\Http\Response
     */
    public function get_all_team_members()
    {
        $user = Auth::user();
        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user->id)->first()->toArray();

        $team_players = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $FantasyTeams['id'])->get();

        // $teampoints = getTeamTotalPoints($FantasyTeams['id']) - getTotalTeamDeductions($FantasyTeams['id']);

        $gameweek_number = settingValue('gameweek_number');
        $current_week = (int) $gameweek_number;
        $previous_week = ($current_week == 0) ? 0 : $current_week - 1;
        //$current_week = Carbon::now()->weekOfYear;
        //$previous_week = Carbon::now()->weekOfYear - 1;
        foreach ($team_players as $key => $val) {
            // echo '<pre>';
            // print_r($val);
            // echo '<br>';
            $val->points = get_player_gameweek_points_history_my_team($val->id, $FantasyTeams['id'], $previous_week);
        }
        //die();
        //$teampoints = getTeamTotalPoints($team_id) - getTotalTeamDeductions($team_id);
        //$teampoints = get_team_score_current_gameweek($FantasyTeams['id'], $previous_week);

        $teampoints = getTotalPointsAfterTeamDeductions($FantasyTeams['id']);

        // $this_week_points = get_player_gameweek_points_history($FantasyTeams['id'], $previous_week) - getTeamDeductionsByWeek($FantasyTeams['id'], $previous_week);
        // $overall_points = getTeamTotalPoints($FantasyTeams['id']) - getTotalTeamDeductions($FantasyTeams['id']);
        // $teampoints = $overall_points . ' (' . $this_week_points . ' this week)';

        return response()->json([
            'team_total_points' => $teampoints,
            'fantasy_team' => $FantasyTeams,
            'team_players' => $team_players->toArray(),
            'message' => 'all team players are fetched',
            'status' => 200,
        ]);
    }

    public function build_team($user_id)
    {

        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user_id)->orderBy('created_at', 'desc')->first();
        if (empty($FantasyTeams)) {
            return response()->json([
                'FantasyTeam' => [],
                'player_to_team' => [],
                'user_id' => $user_id,
                'status' => '200',
            ]);

        } else {

            $gameweek_number = settingValue('gameweek_number');
            $current_week = (int) $gameweek_number;
            $last_week = ($current_week == 0) ? 0 : $current_week - 1;

            //$last_week = (Carbon::now()->weekOfYear) - 1;
            $FantasyTeams = $FantasyTeams->toArray();
            $team_id = $FantasyTeams['id'];

            $player_to_team = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $team_id)->get()->toArray();

            $my_team_formation = $FantasyTeams['formation'];

            $my_team_formation = explode('-', $my_team_formation);

            $defenders = '';
            $midfielders = '';
            $forwards = '';

            $goalkeepers = '';
            $benchgoalkeepers = '';
            $benchplayerones = '';
            $benchplayertwos = '';
            $benchplayerthrees = '';

            $alldefenders = array();
            $allmidfielders = array();
            $allforwards = array();
            $goalkeeper = array();
            $benchgoalkeeper = array();
            $benchplayerone = array();
            $benchplayertwo = array();
            $benchplayerthree = array();

//  goal keeper formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('gk', array_column($player_to_team, 'position_used_for'));

                if ($key < 0 || gettype($key) != 'integer') {
                    $goalkeeper[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $goalkeeper[$k - 1] = $player_to_team[$key];
                    $goalkeeper[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench goal keeper formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bgk', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchgoalkeeper[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchgoalkeeper[$k - 1] = $player_to_team[$key];
                    $benchgoalkeeper[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench player one formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bp1', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchplayerone[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchplayerone[$k - 1] = $player_to_team[$key];
                    $benchplayerone[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench player two formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bp2', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchplayertwo[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchplayertwo[$k - 1] = $player_to_team[$key];
                    $benchplayertwo[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench player three formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bp3', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchplayerthree[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchplayerthree[$k - 1] = $player_to_team[$key];
                    $benchplayerthree[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

            // loop for defenders searching and formatting
            for ($k = 1; $k <= (int) $my_team_formation[0]; $k++) {
                $key = array_search('def' . (string) $k, array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $alldefenders[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $alldefenders[$k - 1] = $player_to_team[$key];
                    $alldefenders[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);

                }

            }

            // echo '<pre>';
            // print_r($alldefenders);
            // die();

            // loop for midfielders searching and formatting

            for ($k = 1; $k <= (int) $my_team_formation[1]; $k++) {
                $key = array_search('mid' . (string) $k, array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $allmidfielders[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $allmidfielders[$k - 1] = $player_to_team[$key];
                    $allmidfielders[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

            // loop for forwards searching and formatting

            for ($k = 1; $k <= (int) $my_team_formation[2]; $k++) {
                $key = array_search('for' . (string) $k, array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $allforwards[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $allforwards[$k - 1] = $player_to_team[$key];
                    $allforwards[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

            for ($i = 1; $i <= count($alldefenders); $i++) {

                $defenders .= '<div class="col player-box">';

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {
                    $defenders .= '<a href="#/sell-player/' . $alldefenders[$i - 1]['player_id'] . '">';
                } else {
                    $defenders .= '<a href="#/allplayers/def' . $i . '">';
                }

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {
                    $defenders .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $alldefenders[$i - 1]['shirt'] . '" alt="shirt" />';
                } else {
                    $defenders .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-no-players/no-team-shadow.svg" alt="shirt" />';
                }

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {
                    $defenders .= '<div class="player-point-box">
                        <p class="box-margin">' . $alldefenders[$i - 1]['weekpoints'] . '</p>
                    </div>';
                } else {

                    $defenders .= '<div class="player-point-box">
                    <p class="box-margin">0</p>
                    </div>';
                }

                if ($alldefenders[$i - 1]['c_v_c'] == 1) {
                    $defenders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($alldefenders[$i - 1]['c_v_c'] == 2) {
                    $defenders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($alldefenders[$i - 1]['injured_out'] == 1) {
                    $defenders .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($alldefenders[$i - 1]['missing'] == 1) {
                    $defenders .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($alldefenders[$i - 1]['suspended'] == 1) {
                    $defenders .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $defenders .= '<div class="gk-box">
                //             <p>DEF ' . $i . '</p>
                //         </div>';

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {

                    $surname = substr(strrchr($alldefenders[$i - 1]['name'], " "), 1);
                    $defenders .= '<div class="player-name-box">
                    <p>' . $surname . '</p>
                </div> ';
                } else {
                    $defenders .= '<div class="player-name-box">
                    <p>No Player</p>
                </div> ';
                }

                $defenders .= '</a></div>';

            }

//  loop for defenders ended

            for ($i = 1; $i <= count($allmidfielders); $i++) {

                $midfielders .= '<div class="col player-box">';

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $midfielders .= '<a href="#/sell-player/' . $allmidfielders[$i - 1]['player_id'] . '">';
                } else {
                    $midfielders .= '<a href="#/allplayers/mid' . $i . '">';
                }

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $midfielders .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $allmidfielders[$i - 1]['shirt'] . '" alt="shirt" />';
                } else {
                    $midfielders .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-no-players/no-team-shadow.svg" alt="shirt" />';
                }

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $midfielders .= '<div class="player-point-box">
                    <p class="box-margin">' . $allmidfielders[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $midfielders .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($allmidfielders[$i - 1]['c_v_c'] == 1) {
                    $midfielders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($allmidfielders[$i - 1]['c_v_c'] == 2) {
                    $midfielders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($allmidfielders[$i - 1]['injured_out'] == 1) {
                    $midfielders .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($allmidfielders[$i - 1]['missing'] == 1) {
                    $midfielders .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($allmidfielders[$i - 1]['suspended'] == 1) {
                    $midfielders .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $midfielders .= '<div class="gk-box">
                //         <p>MID ' . $i . '</p>
                //     </div>';

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $surname = substr(strrchr($allmidfielders[$i - 1]['name'], " "), 1);
                    $midfielders .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $midfielders .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $midfielders .= '</a></div>';

            }

// loop for midfielders ended here

            for ($i = 1; $i <= count($allforwards); $i++) {

                $forwards .= '<div class="col player-box">';

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $forwards .= '<a href="#/sell-player/' . $allforwards[$i - 1]['player_id'] . '">';
                } else {
                    $forwards .= '<a href="#/allplayers/for' . $i . '">';
                }

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $forwards .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $allforwards[$i - 1]['shirt'] . '" alt="shirt" />';
                } else {
                    $forwards .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-no-players/no-team-shadow.svg" alt="shirt" />';
                }

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $forwards .= '<div class="player-point-box">
                    <p class="box-margin">' . $allforwards[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $forwards .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($allforwards[$i - 1]['c_v_c'] == 1) {
                    $forwards .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($allforwards[$i - 1]['c_v_c'] == 2) {
                    $forwards .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($allforwards[$i - 1]['injured_out'] == 1) {
                    $forwards .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($allforwards[$i - 1]['missing'] == 1) {
                    $forwards .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($allforwards[$i - 1]['suspended'] == 1) {
                    $forwards .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $forwards .= '<div class="gk-box">
                //         <p>FOR ' . $i . '</p>
                //     </div>';

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $surname = substr(strrchr($allforwards[$i - 1]['name'], " "), 1);
                    $forwards .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $forwards .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $forwards .= '</a></div>';

            }

// looop for forwards ended here

            for ($i = 1; $i <= count($goalkeeper); $i++) {

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    $goalkeepers .= '<a href="#/sell-player/' . $goalkeeper[$i - 1]['player_id'] . '">';
                } else {
                    $goalkeepers .= '<a href="#/allplayers/gk">';
                }

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    // $goalkeepers .= '<img class="img-fluid mx-auto d-block" src="'.$this->endpoint.'/uploads/clubs/'.$goalkeeper[$i-1]['shirt'].'" alt="shirt" />';
                    $goalkeepers .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-players/goalie.svg" alt="shirt" />';
                } else {
                    $goalkeepers .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-players/goalie.svg" alt="shirt" />';
                }

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    $goalkeepers .= '<div class="player-point-box">
                    <p class="box-margin">' . $goalkeeper[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $goalkeepers .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($goalkeeper[$i - 1]['c_v_c'] == 1) {
                    $goalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($goalkeeper[$i - 1]['c_v_c'] == 2) {
                    $goalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($goalkeeper[$i - 1]['injured_out'] == 1) {
                    $goalkeepers .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($goalkeeper[$i - 1]['missing'] == 1) {
                    $goalkeepers .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($goalkeeper[$i - 1]['suspended'] == 1) {
                    $goalkeepers .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $goalkeepers .= '<div class="gk-box">
                //         <p>GK</p>
                //     </div>';

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    $surname = substr(strrchr($goalkeeper[$i - 1]['name'], " "), 1);
                    $goalkeepers .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $goalkeepers .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $goalkeepers .= '</a>';

            }

// looop for goalkeepers ended here

            for ($i = 1; $i <= count($benchgoalkeeper); $i++) {

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $benchgoalkeepers .= '<a href="#/sell-player/' . $benchgoalkeeper[$i - 1]['player_id'] . '">';
                } else {
                    $benchgoalkeepers .= '<a href="#/allplayers/bgk">';
                }

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $benchgoalkeepers .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-players/goalie.svg" alt="shirt" />';
                } else {
                    $benchgoalkeepers .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-players/goalie.svg" alt="shirt" />';
                }

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $benchgoalkeepers .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchgoalkeeper[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchgoalkeepers .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchgoalkeeper[$i - 1]['c_v_c'] == 1) {
                    $benchgoalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchgoalkeeper[$i - 1]['c_v_c'] == 2) {
                    $benchgoalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($benchgoalkeeper[$i - 1]['injured_out'] == 1) {
                    $benchgoalkeepers .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($benchgoalkeeper[$i - 1]['missing'] == 1) {
                    $benchgoalkeepers .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($benchgoalkeeper[$i - 1]['suspended'] == 1) {
                    $benchgoalkeepers .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $benchgoalkeepers .= '<div class="gk-box">
                //         <p>GK</p>
                //     </div>';

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $surname = substr(strrchr($benchgoalkeeper[$i - 1]['name'], " "), 1);
                    $benchgoalkeepers .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchgoalkeepers .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchgoalkeepers .= '</a>';

            }

// looop for bench goalkeepers ended here

            for ($i = 1; $i <= count($benchplayerthree); $i++) {

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $benchplayerthrees .= '<a href="#/sell-player/' . $benchplayerthree[$i - 1]['player_id'] . '">';
                } else {
                    $benchplayerthrees .= '<a href="#/allplayers/bp3">';
                }

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $benchplayerthrees .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $benchplayerthree[$i - 1]['shirt'] . '" alt="shirt" />';
                } else {
                    $benchplayerthrees .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-no-players/no-team-shadow.svg" alt="shirt" />';
                }

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $benchplayerthrees .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchplayerthree[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchplayerthrees .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchplayerthree[$i - 1]['c_v_c'] == 1) {
                    $benchplayerthrees .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchplayerthree[$i - 1]['c_v_c'] == 2) {
                    $benchplayerthrees .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($benchplayerthree[$i - 1]['injured_out'] == 1) {
                    $benchplayerthrees .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($benchplayerthree[$i - 1]['missing'] == 1) {
                    $benchplayerthrees .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($benchplayerthree[$i - 1]['suspended'] == 1) {
                    $benchplayerthrees .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $benchplayerthrees .= '<div class="gk-box">
                //         <p></p>
                //     </div>';

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $surname = substr(strrchr($benchplayerthree[$i - 1]['name'], " "), 1);
                    $benchplayerthrees .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchplayerthrees .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchplayerthrees .= '</a>';

            }

// looop for bench player three ended here

            for ($i = 1; $i <= count($benchplayertwo); $i++) {

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $benchplayertwos .= '<a href="#/sell-player/' . $benchplayertwo[$i - 1]['player_id'] . '">';
                } else {
                    $benchplayertwos .= '<a href="#/allplayers/bp2">';
                }

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $benchplayertwos .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $benchplayertwo[$i - 1]['shirt'] . '" alt="shirt" />';
                } else {
                    $benchplayertwos .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-no-players/no-team-shadow.svg" alt="shirt" />';
                }

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $benchplayertwos .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchplayertwo[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchplayertwos .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchplayertwo[$i - 1]['c_v_c'] == 1) {
                    $benchplayertwos .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchplayertwo[$i - 1]['c_v_c'] == 2) {
                    $benchplayertwos .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($benchplayertwo[$i - 1]['injured_out'] == 1) {
                    $benchplayertwos .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($benchplayertwo[$i - 1]['missing'] == 1) {
                    $benchplayertwos .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($benchplayertwo[$i - 1]['suspended'] == 1) {
                    $benchplayertwos .= '<div class="icon-status">
                    <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $benchplayertwos .= '<div class="gk-box">
                //         <p></p>
                //     </div>';

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $surname = substr(strrchr($benchplayertwo[$i - 1]['name'], " "), 1);
                    $benchplayertwos .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchplayertwos .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchplayertwos .= '</a>';

            }

// looop for bench player two ended here

            for ($i = 1; $i <= count($benchplayerone); $i++) {

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $benchplayerones .= '<a href="#/sell-player/' . $benchplayerone[$i - 1]['player_id'] . '">';
                } else {
                    $benchplayerones .= '<a href="#/allplayers/bp1">';
                }

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $benchplayerones .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $benchplayerone[$i - 1]['shirt'] . '" alt="shirt" />';
                } else {
                    $benchplayerones .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-no-players/no-team-shadow.svg" alt="shirt" />';
                }

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $benchplayerones .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchplayerone[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchplayerones .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchplayerone[$i - 1]['c_v_c'] == 1) {
                    $benchplayerones .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchplayerone[$i - 1]['c_v_c'] == 2) {
                    $benchplayerones .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="vice captain" />
                    </div>';
                }

                if ($benchplayerone[$i - 1]['injured_out'] == 1) {
                    $benchplayerones .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="injured out" />
                    </div>';
                } else if ($benchplayerone[$i - 1]['missing'] == 1) {
                    $benchplayerones .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="missing out" />
                    </div>';
                } else if ($benchplayerone[$i - 1]['suspended'] == 1) {
                    $benchplayerones .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block" style="height:16px; margin: 26px 0 0 0;" src="' . $this->endpoint . '/img/images/home-players/injured-out.svg" alt="suspended out" />
                    </div>';
                }

                // $benchplayerones .= '<div class="gk-box">
                //         <p></p>
                //     </div>';

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $surname = substr(strrchr($benchplayerone[$i - 1]['name'], " "), 1);
                    $benchplayerones .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchplayerones .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchplayerones .= '</a>';

            }

// looop for bench player one ended here

//  calculate team points

            $gameweek_number = settingValue('gameweek_number');
            $current_week = (int) $gameweek_number;
            $previous_week = ($current_week == 0) ? 0 : $current_week - 1;

            //$teampoints = get_player_gameweek_points_history($team_id, $previous_week) - getTeamDeductionsByWeek($team_id, $previous_week);
            $teampoints = getTotalPointsAfterTeamDeductions($team_id);

            // $this_week_points = get_player_gameweek_points_history($team_id, $previous_week) - getTeamDeductionsByWeek($team_id, $previous_week);
            // $overall_points = getTeamTotalPoints($team_id) - getTotalTeamDeductions($team_id);
            // $teampoints = $overall_points . ' (' . $this_week_points . ' this week)';

            $settings = new SiteSettings();
            // echo '<pre>';
            // print_r($settings->getAllSettings());
            // die();

            return [
                'site_settings' => $settings->getAllSettings(),
                'team_total_points' => floatval($teampoints),
                'FantasyTeam' => $FantasyTeams,
                'player_to_team' => $player_to_team,
                'defenders' => $defenders,
                'midfielders' => $midfielders,
                'forwards' => $forwards,
                'goalkeeper' => $goalkeepers,
                'benchgoalkeeper' => $benchgoalkeepers,
                'benchplayerone' => $benchplayerones,
                'benchplayertwo' => $benchplayertwos,
                'benchplayerthree' => $benchplayerthrees,
                'user_id' => $user_id,
                'status' => '200',
            ];
        }
    }

    public function show_team($team_id)
    {

        $FantasyTeams = FantasyTeams::with('sparkteam')->where('id', $team_id)->orderBy('created_at', 'desc')->first();
        if (empty($FantasyTeams)) {
            return response()->json([
                'status' => '200',
            ]);

        } else {

            $gameweek_number = settingValue('gameweek_number');
            $current_week = (int) $gameweek_number;
            $previous_week = ($current_week == 0) ? 0 : $current_week - 1;
            $last_week = $previous_week;
            $FantasyTeams = $FantasyTeams->toArray();
            // $team_id = $FantasyTeams['id'];

            $player_to_team = PlayerTeam::join('player', 'player.id', '=', 'player_to_team.player_id')->join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*', 'player_to_team.*')->where('player_to_team.team_id', $team_id)->get()->toArray();

            $my_team_formation = $FantasyTeams['formation'];

            $my_team_formation = explode('-', $my_team_formation);

            $defenders = '';
            $midfielders = '';
            $forwards = '';

            $goalkeepers = '';
            $benchgoalkeepers = '';
            $benchplayerones = '';
            $benchplayertwos = '';
            $benchplayerthrees = '';

            $alldefenders = array();
            $allmidfielders = array();
            $allforwards = array();
            $goalkeeper = array();
            $benchgoalkeeper = array();
            $benchplayerone = array();
            $benchplayertwo = array();
            $benchplayerthree = array();

//  goal keeper formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('gk', array_column($player_to_team, 'position_used_for'));

                if ($key < 0 || gettype($key) != 'integer') {
                    $goalkeeper[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $goalkeeper[$k - 1] = $player_to_team[$key];
                    $goalkeeper[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench goal keeper formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bgk', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchgoalkeeper[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchgoalkeeper[$k - 1] = $player_to_team[$key];
                    $benchgoalkeeper[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench player one formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bp1', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchplayerone[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchplayerone[$k - 1] = $player_to_team[$key];
                    $benchplayerone[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench player two formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bp2', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchplayertwo[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchplayertwo[$k - 1] = $player_to_team[$key];
                    $benchplayertwo[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

//  bench player three formation of array

            for ($k = 1; $k < 2; $k++) {
                $key = array_search('bp3', array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $benchplayerthree[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $benchplayerthree[$k - 1] = $player_to_team[$key];
                    $benchplayerthree[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

            // loop for defenders searching and formatting
            for ($k = 1; $k <= (int) $my_team_formation[0]; $k++) {
                $key = array_search('def' . (string) $k, array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $alldefenders[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $alldefenders[$k - 1] = $player_to_team[$key];
                    $alldefenders[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);

                }

            }

            // loop for midfielders searching and formatting

            for ($k = 1; $k <= (int) $my_team_formation[1]; $k++) {
                $key = array_search('mid' . (string) $k, array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $allmidfielders[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $allmidfielders[$k - 1] = $player_to_team[$key];
                    $allmidfielders[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

            // loop for forwards searching and formatting

            for ($k = 1; $k <= (int) $my_team_formation[2]; $k++) {
                $key = array_search('for' . (string) $k, array_column($player_to_team, 'position_used_for'));
                if ($key < 0 || gettype($key) != 'integer') {
                    $allforwards[$k - 1] = array(
                        'club_name' => '',
                        'shirt' => '',
                        'id' => '',
                        'name' => '',
                        'colours' => '',
                        'injured_available' => '',
                        'injured_out' => '',
                        'missing' => '',
                        'suspended' => '',
                        'cost' => '',
                        'position' => '',
                        'club' => '',
                        'points' => '',
                        'bought_status' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'player_id' => '',
                        'player_club' => '',
                        'team_id' => '',
                        'player_cost' => '',
                        'position_used_for' => '',
                        'c_v_c' => '',
                        'on_bench' => '',
                        'weekpoints' => '',
                    );
                } else {
                    $allforwards[$k - 1] = $player_to_team[$key];
                    $allforwards[$k - 1]['weekpoints'] = get_player_gameweek_points_history_my_team($player_to_team[$key]['player_id'], $team_id, $last_week);
                }

            }

            // echo '<pre>';
            // print_r($alldefenders);
            // die();

            for ($i = 1; $i <= count($alldefenders); $i++) {

                $defenders .= '<div class="col player-box">';

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {
                    $defenders .= '<a href="#/show-player/all/' . $alldefenders[$i - 1]['player_id'] . '">';
                } else {
                    $defenders .= '<a href="#/transfermarket/all">';
                }

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {
                    $defenders .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $alldefenders[$i - 1]['shirt'] . '" alt="shirt" />';
                }

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {
                    $defenders .= '<div class="player-point-box">
                        <p class="box-margin">' . $alldefenders[$i - 1]['weekpoints'] . '</p>
                    </div>';
                } else {

                    $defenders .= '<div class="player-point-box">
                    <p class="box-margin">0</p>
                    </div>';
                }

                if ($alldefenders[$i - 1]['c_v_c'] == 1) {
                    $defenders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($alldefenders[$i - 1]['c_v_c'] == 2) {
                    $defenders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }
                // $defenders .= '<div class="gk-box">
                //             <p>DEF ' . $i . '</p>
                //         </div>';

                if ($alldefenders[$i - 1]['position_used_for'] == 'def' . (string) $i) {

                    $surname = substr(strrchr($alldefenders[$i - 1]['name'], " "), 1);
                    $defenders .= '<div class="player-name-box">
                    <p>' . $surname . '</p>
                </div> ';
                } else {
                    $defenders .= '<div class="player-name-box">
                    <p>No Player</p>
                </div> ';
                }

                $defenders .= '</a></div>';

            }

//  loop for defenders ended

            for ($i = 1; $i <= count($allmidfielders); $i++) {

                $midfielders .= '<div class="col player-box">';

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $midfielders .= '<a href="#/show-player/all/' . $allmidfielders[$i - 1]['player_id'] . '">';
                } else {
                    $midfielders .= '<a href="#/transfermarket/all">';
                }

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $midfielders .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $allmidfielders[$i - 1]['shirt'] . '" alt="shirt" />';
                }

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $midfielders .= '<div class="player-point-box">
                    <p class="box-margin">' . $allmidfielders[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $midfielders .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($allmidfielders[$i - 1]['c_v_c'] == 1) {
                    $midfielders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($allmidfielders[$i - 1]['c_v_c'] == 2) {
                    $midfielders .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }

                // $midfielders .= '<div class="gk-box">
                //         <p>MID ' . $i . '</p>
                //     </div>';

                if ($allmidfielders[$i - 1]['position_used_for'] == 'mid' . (string) $i) {
                    $surname = substr(strrchr($allmidfielders[$i - 1]['name'], " "), 1);
                    $midfielders .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $midfielders .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $midfielders .= '</a></div>';

            }

// loop for midfielders ended here

            for ($i = 1; $i <= count($allforwards); $i++) {

                $forwards .= '<div class="col player-box">';

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $forwards .= '<a href="#/show-player/all/' . $allforwards[$i - 1]['player_id'] . '">';
                } else {
                    $forwards .= '<a href="#/transfermarket/all">';
                }

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $forwards .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $allforwards[$i - 1]['shirt'] . '" alt="shirt" />';
                }

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $forwards .= '<div class="player-point-box">
                    <p class="box-margin">' . $allforwards[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $forwards .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($allforwards[$i - 1]['c_v_c'] == 1) {
                    $forwards .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($allforwards[$i - 1]['c_v_c'] == 2) {
                    $forwards .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }

                // $forwards .= '<div class="gk-box">
                //         <p>FOR ' . $i . '</p>
                //     </div>';

                if ($allforwards[$i - 1]['position_used_for'] == 'for' . (string) $i) {
                    $surname = substr(strrchr($allforwards[$i - 1]['name'], " "), 1);
                    $forwards .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $forwards .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $forwards .= '</a></div>';

            }

// looop for forwards ended here

            for ($i = 1; $i <= count($goalkeeper); $i++) {

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    $goalkeepers .= '<a href="#/show-player/all/' . $goalkeeper[$i - 1]['player_id'] . '">';
                } else {
                    $goalkeepers .= '<a href="#/transfermarket/all">';
                }

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    // $goalkeepers .= '<img class="img-fluid mx-auto d-block" src="'.$this->endpoint.'/uploads/clubs/'.$goalkeeper[$i-1]['shirt'].'" alt="shirt" />';
                    $goalkeepers .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-players/goalie.svg" alt="shirt" />';
                }

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    $goalkeepers .= '<div class="player-point-box">
                    <p class="box-margin">' . $goalkeeper[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $goalkeepers .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($goalkeeper[$i - 1]['c_v_c'] == 1) {
                    $goalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($goalkeeper[$i - 1]['c_v_c'] == 2) {
                    $goalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }

                // $goalkeepers .= '<div class="gk-box">
                //         <p>GK</p>
                //     </div>';

                if ($goalkeeper[$i - 1]['position_used_for'] == 'gk') {
                    $surname = substr(strrchr($goalkeeper[$i - 1]['name'], " "), 1);
                    $goalkeepers .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $goalkeepers .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $goalkeepers .= '</a>';

            }

// looop for goalkeepers ended here

            for ($i = 1; $i <= count($benchgoalkeeper); $i++) {

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $benchgoalkeepers .= '<a href="#/show-player/all/' . $benchgoalkeeper[$i - 1]['player_id'] . '">';
                } else {
                    $benchgoalkeepers .= '<a href="#/transfermarket/all">';
                }

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $benchgoalkeepers .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/img/images/home-players/goalie.svg" alt="shirt" />';
                }

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $benchgoalkeepers .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchgoalkeeper[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchgoalkeepers .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchgoalkeeper[$i - 1]['c_v_c'] == 1) {
                    $benchgoalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchgoalkeeper[$i - 1]['c_v_c'] == 2) {
                    $benchgoalkeepers .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }

                // $benchgoalkeepers .= '<div class="gk-box">
                //         <p>GK</p>
                //     </div>';

                if ($benchgoalkeeper[$i - 1]['position_used_for'] == 'bgk') {
                    $surname = substr(strrchr($benchgoalkeeper[$i - 1]['name'], " "), 1);
                    $benchgoalkeepers .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchgoalkeepers .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchgoalkeepers .= '</a>';

            }

// looop for bench goalkeepers ended here

            for ($i = 1; $i <= count($benchplayerthree); $i++) {

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $benchplayerthrees .= '<a href="#/show-player/all/' . $benchplayerthree[$i - 1]['player_id'] . '">';
                } else {
                    $benchplayerthrees .= '<a href="#/transfermarket/all">';
                }

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $benchplayerthrees .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $benchplayerthree[$i - 1]['shirt'] . '" alt="shirt" />';
                }

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $benchplayerthrees .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchplayerthree[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchplayerthrees .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchplayerthree[$i - 1]['c_v_c'] == 1) {
                    $benchplayerthrees .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchplayerthree[$i - 1]['c_v_c'] == 2) {
                    $benchplayerthrees .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }

                // $benchplayerthrees .= '<div class="gk-box">
                //         <p></p>
                //     </div>';

                if ($benchplayerthree[$i - 1]['position_used_for'] == 'bp3') {
                    $surname = substr(strrchr($benchplayerthree[$i - 1]['name'], " "), 1);
                    $benchplayerthrees .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchplayerthrees .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchplayerthrees .= '</a>';

            }

// looop for bench player three ended here

            for ($i = 1; $i <= count($benchplayertwo); $i++) {

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $benchplayertwos .= '<a href="#/show-player/all/' . $benchplayertwo[$i - 1]['player_id'] . '">';
                } else {
                    $benchplayertwos .= '<a href="#/transfermarket/all">';
                }

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $benchplayertwos .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $benchplayertwo[$i - 1]['shirt'] . '" alt="shirt" />';
                }

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $benchplayertwos .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchplayertwo[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchplayertwos .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchplayertwo[$i - 1]['c_v_c'] == 1) {
                    $benchplayertwos .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchplayertwo[$i - 1]['c_v_c'] == 2) {
                    $benchplayertwos .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }

                // $benchplayertwos .= '<div class="gk-box">
                //         <p></p>
                //     </div>';

                if ($benchplayertwo[$i - 1]['position_used_for'] == 'bp2') {
                    $surname = substr(strrchr($benchplayertwo[$i - 1]['name'], " "), 1);
                    $benchplayertwos .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchplayertwos .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchplayertwos .= '</a>';

            }

// looop for bench player two ended here

            for ($i = 1; $i <= count($benchplayerone); $i++) {

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $benchplayerones .= '<a href="#/show-player/all/' . $benchplayerone[$i - 1]['player_id'] . '">';
                } else {
                    $benchplayerones .= '<a href="#/transfermarket/all">';
                }

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $benchplayerones .= '<img class="img-fluid mx-auto d-block" src="' . $this->endpoint . '/uploads/clubs/' . $benchplayerone[$i - 1]['shirt'] . '" alt="shirt" />';
                }

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $benchplayerones .= '<div class="player-point-box">
                    <p class="box-margin">' . $benchplayerone[$i - 1]['weekpoints'] . '</p>
                </div>';
                } else {

                    $benchplayerones .= '<div class="player-point-box">
                <p class="box-margin">0</p>
                </div>';
                }

                if ($benchplayerone[$i - 1]['c_v_c'] == 1) {
                    $benchplayerones .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/captain-icon.svg" alt="captain" />
                    </div>';
                } else if ($benchplayerone[$i - 1]['c_v_c'] == 2) {
                    $benchplayerones .= '<div class="icon-status">
            <img class="img-fluid mx-auto d-block smt" src="' . $this->endpoint . '/img/images/home-players/vice-captain.svg" alt="captain" />
                    </div>';
                }

                // $benchplayerones .= '<div class="gk-box">
                //         <p></p>
                //     </div>';

                if ($benchplayerone[$i - 1]['position_used_for'] == 'bp1') {
                    $surname = substr(strrchr($benchplayerone[$i - 1]['name'], " "), 1);
                    $benchplayerones .= '<div class="player-name-box">
                <p>' . $surname . '</p>
            </div> ';
                } else {
                    $benchplayerones .= '<div class="player-name-box">
                <p>No Player</p>
            </div> ';
                }

                $benchplayerones .= '</a>';

            }

// looop for bench player one ended here

//  calculate team points
            //$teampoints = get_player_gameweek_points_history($team_id, $previous_week) - getTeamDeductionsByWeek($team_id, $previous_week);
            $teampoints = getTotalPointsAfterTeamDeductions($team_id);

            $settings = new SiteSettings();
            //echo '<pre>';
            //print_r($settings->getAllSettings());
            //die();
            return [
                'site_settings' => $settings->getAllSettings(),
                'team_total_points' => floatval($teampoints),
                'FantasyTeam' => $FantasyTeams,
                'player_to_team' => $player_to_team,
                'defenders' => $defenders,
                'midfielders' => $midfielders,
                'forwards' => $forwards,
                'goalkeeper' => $goalkeepers,
                'benchgoalkeeper' => $benchgoalkeepers,
                'benchplayerone' => $benchplayerones,
                'benchplayertwo' => $benchplayertwos,
                'benchplayerthree' => $benchplayerthrees,
                'user_id' => $team_id,
                'status' => '200',
            ];
        }
    }

    public function site_settings(Request $request)
    {
        $settings = new SiteSettings();

        return ['site_settings' => $settings->getAllSettings()];
    }

}
