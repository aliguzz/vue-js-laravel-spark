<?php
namespace App\Http\Controllers;

use App\FantasyTeams;
use App\Http\Controllers;
use App\Leagues;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LeagueController extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($league_id)
    {

        $gameweek_number = settingValue('gameweek_number');
        $current_week = (int) $gameweek_number;
        $previous_week = ($current_week == 0) ? 0 : $current_week - 1;

        $team_id = FantasyTeams::where('user_id', Auth::user()->id)->first()->id;
        $leaguechairman = \DB::table('team_to_league')->join('league', 'league.id', '=', 'team_to_league.league_id')->join('team', 'team.id', '=', 'team_to_league.team_id')->join('users', 'users.id', '=', 'team.user_id')->select('users.name as user_name', 'team_to_league.*', 'league.*', 'team.*')->selectRaw("LEFT(league.name, 50) as league_name")->where('team_to_league.league_id', $league_id)->orderBy('team_to_league.created_at', 'asc')->first();

        // \DB::enableQueryLog();
        // dont use this one
        //$teamtoleague = \DB::table('team_to_league')->join('league', 'league.id', '=', 'team_to_league.league_id')->join('team', 'team.id', '=', 'team_to_league.team_id')->join('users', 'users.id', '=', 'team.user_id')->select('users.name as user_name', 'team.*', 'team.id as team_id')->selectRaw("LEFT(league.name, 50) as league_name")->selectRaw("LEFT(team.name, 50) as name")->selectRaw("(SELECT (SELECT COALESCE(SUM(points),0) FROM player_gameweek_history pg WHERE pg.week_number = $previous_week-1 AND pg.team_id = team.id) - (SELECT COALESCE(SUM(number_of_deducted_points),0) FROM deductions dd WHERE dd.week_number = $previous_week-1 AND dd.team_id = team.id)) as previous_position")->selectRaw("(SELECT (SELECT COALESCE(SUM(points),0) FROM player_gameweek_history pg WHERE pg.week_number = $previous_week AND pg.team_id = team.id) - (SELECT COALESCE(SUM(number_of_deducted_points),0) FROM deductions dd WHERE dd.week_number = $previous_week AND dd.team_id = team.id)) as current_position")->selectRaw("(SELECT (SELECT COALESCE(SUM(points),0) FROM player_gameweek_history pg WHERE pg.week_number = $previous_week AND pg.team_id = team.id) - (SELECT COALESCE(SUM(number_of_deducted_points),0) FROM deductions dd WHERE dd.week_number = $previous_week AND dd.team_id = team.id)) as totalteams_points")->selectRaw("$current_week as gameweek")->where('team_to_league.league_id', $league_id)->get();
        // this one calculates the prev position incorrectly
        //$teamtoleague = \DB::table('team_to_league')->join('league', 'league.id', '=', 'team_to_league.league_id')->join('team', 'team.id', '=', 'team_to_league.team_id')->join('users', 'users.id', '=', 'team.user_id')->select('users.name as user_name', 'team.*', 'team.id as team_id')->selectRaw("LEFT(league.name, 50) as league_name")->selectRaw("LEFT(team.name, 50) as name")->selectRaw("(SELECT (SELECT COALESCE(SUM(points),0) FROM player_gameweek_history pg WHERE pg.week_number = $previous_week-1 AND pg.team_id = team.id) - (SELECT COALESCE(SUM(number_of_deducted_points),0) FROM deductions dd WHERE dd.week_number = $previous_week-1 AND dd.team_id = team.id)) as previous_position")->selectRaw("(SELECT (SELECT COALESCE(SUM(points),0) FROM player_gameweek_history pg WHERE pg.week_number = $previous_week AND pg.team_id = team.id) - (SELECT COALESCE(SUM(number_of_deducted_points),0) FROM deductions dd WHERE dd.week_number = $previous_week AND dd.team_id = team.id)) as current_position")->selectRaw("(SELECT (SELECT COALESCE(SUM(points),0) FROM player_gameweek_history pg WHERE pg.week_number <= $previous_week AND pg.team_id = team.id) - (SELECT COALESCE(SUM(number_of_deducted_points),0) FROM deductions dd WHERE dd.week_number <= $previous_week AND dd.team_id = team.id)) as totalteams_points")->selectRaw("$current_week as gameweek")->where('team_to_league.league_id', $league_id)->get();
        // this one is correct, except the arrows
        $teamtoleague = \DB::table('team_to_league')->join('league', 'league.id', '=', 'team_to_league.league_id')->join('team', 'team.id', '=', 'team_to_league.team_id')->join('users', 'users.id', '=', 'team.user_id')->select('users.name as user_name', 'team.*', 'team.id as team_id')->selectRaw("LEFT(league.name, 50) as league_name")->selectRaw("LEFT(team.name, 50) as name")->selectRaw("team_to_league.previous_position")->selectRaw("team_to_league.current_position")->selectRaw("(SELECT (SELECT COALESCE(SUM(points),0) FROM player_gameweek_history pg WHERE pg.week_number <= $previous_week AND pg.team_id = team.id) - (SELECT COALESCE(SUM(number_of_deducted_points),0) FROM deductions dd WHERE dd.week_number <= $previous_week AND dd.team_id = team.id)) as totalteams_points")->selectRaw("$current_week as gameweek")->where('team_to_league.league_id', $league_id)->get();

        // print_r(\DB::getQueryLog());
        // die();

        // $teamtoleague = \DB::table('team_to_league')->join('league','league.id','=','team_to_league.league_id')->join('team','team.id','=','team_to_league.team_id')->join('users','users.id','=','team.user_id')->select('users.name as user_name','team_to_league.*','league.name as league_name','team.*')->where('team_to_league.league_id',490)->get();
        // $query = \DB::getQueryLog();

        // $teampoints = getTeamTotalPoints($team_id) - getTotalTeamDeductions($team_id);
        $teampoints = get_player_gameweek_points_history($team_id, $previous_week) - getTeamDeductionsByWeek($team_id, $previous_week);

        if (empty($teamtoleague)) {
            $teampoints = array_map('floatval', $teampoints);
            return response()->json([
                'team_total_points' => $teampoints,
                'league_owner_data' => [],
                'leagues' => [],
            ]);
        } else {
            $teamarray = $teamtoleague->toArray();
            $teampoints = array_column($teamarray, 'totalteams_points');
            array_multisort($teampoints, SORT_DESC, $teamarray);
            $teampoints = array_map('floatval', $teampoints);
            // \DB::enableQueryLog();
            // $query = \DB::select(\DB::raw("EXPLAIN SELECT SUM(points) FROM player_gameweek_history pg WHERE pg.week_number = 0 AND pg.team_id = 10472 ORDER BY id asc"));

            return response()->json([
                // 'query' =>  $query,
                'team_total_points' => $teampoints,
                'league_owner_data' => $leaguechairman,
                'leagues' => $teamarray,
                // 'error' =>  \DB::getQueryLog(),

            ]);

        }

    }

    public function userleagues()
    {

        $fantasyteam = FantasyTeams::where('user_id', Auth::user()->id)->first();
        $team_id = $fantasyteam->id;
        $overall_rank = $fantasyteam->overall_rank;

        /* $userleagues = \DB::table('team_to_league')
        ->join('league', 'league.id', '=', 'team_to_league.league_id')
        ->join('team', 'team.id', '=', 'team_to_league.team_id')
        ->join('users', 'users.id', '=', 'team.user_id')
        ->select('users.name as user_name', 'team_to_league.*', 'team.*')
        ->selectRaw("LEFT(league.name, 50) as league_name")
        ->where('team_to_league.team_id', $team_id)->get();
         */
        $userleagues = \DB::select(\DB::raw("select `users`.`name` as `user_name`, `team_to_league`.*, `team`.*, LEFT(league.name, 50) as league_name, team_to_league.current_position as myposition  from `team_to_league` inner join `league` on `league`.`id` = `team_to_league`.`league_id` inner join `team` on `team`.`id` = `team_to_league`.`team_id` inner join `users` on `users`.`id` = `team`.`user_id` where `team_to_league`.`team_id` = :team_id;"), array(
            ':team_id' => $team_id,
        ));

        $gameweek_number = settingValue('gameweek_number');
        $current_week = (int) $gameweek_number;
        $previous_week = ($current_week == 0) ? 0 : $current_week - 1;

        //$teampoints = get_player_gameweek_points_history($team_id, $previous_week) - getTeamDeductionsByWeek($team_id, $previous_week);
        $teampoints = getTeamTotalPoints($team_id) - getTotalTeamDeductions($team_id);

        if (empty($userleagues)) {
            return response()->json([
                'team_total_points' => $teampoints,
                'overallrank' => [],
                'fantasyteam' => '',
                'leagues' => [],
            ]);
        } else {

            foreach ($userleagues as $index => $league) {

                //  $teamtoleague = \DB::table('team_to_league')
                // ->join('league', 'league.id', '=', 'team_to_league.league_id')
                // ->join('team', 'team.id', '=', 'team_to_league.team_id')
                // ->join('users', 'users.id', '=', 'team.user_id')
                // ->select('users.name as user_name', 'team_to_league.*', 'league.name as league_name', 'team.*')
                // ->where('team_to_league.league_id', $league->league_id)
                // ->get();

                // $teamarray = \DB::select(\DB::raw("select `users`.`name` as `user_name`, `team_to_league`.*, `league`.`name` as `league_name`, `team`.* from `team_to_league` inner join `league` on `league`.`id` = `team_to_league`.`league_id` inner join `team` on `team`.`id` = `team_to_league`.`team_id` inner join `users` on `users`.`id` = `team`.`user_id` where `team_to_league`.`league_id` = :league_id"), array(
                //     ':league_id' => $league->league_id,
                // ));

                //$teamarray = $teamtoleague->toArray();

                // foreach ($teamarray as $key => $val) {
                //     $val->gameweek = $current_week;
                //     // $val->totalteams_points = get_player_gameweek_points_history($val->team_id, $previous_week) - getTeamDeductionsByWeek($val->team_id, $previous_week);
                //     $this_week_points = get_player_gameweek_points_history($val->team_id, $previous_week) - getTeamDeductionsByWeek($val->team_id, $previous_week);
                //     $overall_points = getTeamTotalPoints($val->team_id) - getTotalTeamDeductions($val->team_id);
                //     $val->totalteams_points = $overall_points . ' (' . $this_week_points . ' this week)';
                // }

                // $teampoints = array_column($teamarray, 'totalteams_points');
                // array_multisort($teampoints, SORT_DESC, $teamarray);

                $this_week_points = get_player_gameweek_points_history($team_id, $previous_week) - getTeamDeductionsByWeek($team_id, $previous_week);
                $overall_points = getTeamTotalPoints($team_id) - getTotalTeamDeductions($team_id);

                $teampoints = array(
                    "$overall_points ($this_week_points this week)",
                );

                // $userleagues[$index]->myposition = array_search($team_id, array_column($teamarray, 'team_id')) + 1;
                // $userleagues[$index]->myposition = '';
                // $userleagues[$index]->previous_position = get_player_gameweek_points_history($team_id, ($previous_week - 1)) - getTeamDeductionsByWeek($team_id, ($previous_week - 1));
                // $userleagues[$index]->current_position = get_player_gameweek_points_history($team_id, $previous_week) - getTeamDeductionsByWeek($team_id, $previous_week);
                // $userleagues[$index]->previous_position = 1;
                // $userleagues[$index]->current_position = 1;

            }
            // echo '<pre>';
            // print_r($teamarray);
            // print_r($teampoints);
            // die();

            return response()->json([
                'team_total_points' => $teampoints,
                'overallrank' => $overall_rank,
                'fantasyteam' => $fantasyteam,
                'leagues' => $userleagues,
            ]);

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
        $user_id = Auth::user()->id;
        $input = $this->request->all();
        $finalvalidation = $this->validate($this->request, [
            'name' => 'required',
        ]);

        $insertleague = array(
            'name' => substr($input['name'], 0, 50),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'league_code' => strtoupper(Str::random(8)),
        );

        // var_dump($toinsertspark);
        // die();
        \DB::beginTransaction();
        try {
            // check if team already exists
            $is_exist = Leagues::where(['name' => $input['name']])->first();
            if (!empty($is_exist)) {
                return response()->json([
                    'message' => 'League with same name already exists',
                    'status' => '401']);
            }

            $League = Leagues::forceCreate($insertleague);
            $league_id = $League->id;
            $teams = FantasyTeams::select('id')->where('user_id', $user_id)->first();
            $teamtoleague = array(
                'league_id' => $league_id,
                'team_id' => $teams->id,
            );

            \DB::table('team_to_league')->insert($teamtoleague);

            \DB::commit();

            $leagues = Leagues::where('id', $league_id)->orderBy('created_at', 'desc')->get()->toArray();

            return response()->json(['Leagues' => $leagues,
                'user_id' => $user_id,
                'league' => $insertleague,
                'message' => 'Your new league has been created',
                'status' => '200']);

        } catch (\Exception $req) {
            \DB::rollBack();
            return $Responsearray = array(
                'Ex' => $req->getMessage(),
                'ErrorMessage' => 'Error creating League',
                'status' => 401,
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Leagues  $League
     * @return \Illuminate\Http\Response
     */
    public function show(League $League)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Leagues  $League
     * @return \Illuminate\Http\Response
     */
    public function edit(League $League)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\League  $League
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, League $League)
    {
        $input = $this->request->all();
        $findidorfail = Leagues::where('id', $input['id'])->first();
        if ($findidorfail === null) {
            return ['message' => 'League doesn\'t exist'];
            die();
        }

        $finalvalidation = $this->validate($this->request, [
            'League' => 'required',
            'user_id' => 'required',
            'version_id' => 'required',
        ]);

        $toupdate = array(
            'League' => $input['League'],
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $input['user_id'],
            'version_id' => $input['version_id'],
        );

        \DB::table('League')->where('id', $input['id'])->update($toupdate);

        return ['League' => response()->json(Leagues::where('id', $input['id'])->get()->toArray()),
            'message' => 'League name has been updated successfully',
            'status' => '1'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\League  $League
     * @return \Illuminate\Http\Response
     */
    public function destroy(League $League)
    {
        $input = $this->request->all();
        $findidorfail = Leagues::where('id', $input['id'])->first();
        if ($findidorfail === null) {
            return ['message' => 'League doesn\'t exist'];
            die();
        }

        \DB::table('League')->where('id', $input['id'])->delete();

        return ['message' => 'League has been deleted successfully'];
    }

    /**
     * join_league api
     *
     * @return \Illuminate\Http\Response
     */
    public function join_league(Request $request)
    {
        $user_id = Auth::user()->id;
        $input = $this->request->all();

        $finalvalidation = $this->validate($this->request, [
            'league_code' => 'required',
        ]);
        $league = Leagues::where(['league_code' => $input['league_code']])->first();

        if (empty($league)) {
            return response()->json([
                'message' => 'League Code is incorrect or does not exist',
                'status' => '401']);
        }

        $league_id = $league->id;
        $teams = FantasyTeams::select('id')->where('user_id', $user_id)->first();

        // var_dump($toinsertspark);
        // die();
        \DB::beginTransaction();
        try {
            // check if team already exists
            $is_exist = \DB::table('team_to_league')->where(['league_id' => $league_id, 'team_id' => $teams->id])->first();
            if (!empty($is_exist)) {
                return response()->json([
                    'message' => 'You already joined this league',
                    'status' => '401']);
            }

            $teamtoleague = array(
                'league_id' => $league_id,
                'team_id' => $teams->id,
            );

            \DB::table('team_to_league')->insert($teamtoleague);

            \DB::commit();

            $leagues = Leagues::where('id', $league_id)->get()->toArray();

            return response()->json(['Leagues' => $leagues,
                'user_id' => $user_id,
                'message' => 'You joined this league successfully',
                'status' => '200']);

        } catch (\Exception $req) {
            \DB::rollBack();
            return $Responsearray = array(
                'Ex' => $req->getMessage(),
                'ErrorMessage' => 'Error joining League',
                'status' => 401,
            );
        }

    }

    /**
     * create_league api
     *
     * @return \Illuminate\Http\Response
     */
    public function create_league(Request $request)
    {

    }

    /**
     * show_league_table api
     *
     * @return \Illuminate\Http\Response
     */
    public function show_league_table(Request $request)
    {

    }

    /**
     * leave_league api
     *
     * @return \Illuminate\Http\Response
     */
    public function leave_league($league_id)
    {
        $user_id = Auth::user()->id;
        $teams = FantasyTeams::select('id')->where('user_id', $user_id)->first();
        \DB::table('team_to_league')->where(['league_id' => $league_id, 'team_id' => $teams->id])->delete();
        return response()->json([
            'message' => 'You have left this league',
            'status' => '200']);
    }

}
