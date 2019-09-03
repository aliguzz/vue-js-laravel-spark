<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\GameWeek;
use App\Http\Controllers\Controller;
use App\SiteSettings;
use Auth;
use DB;
use Illuminate\Http\Request;

class GameWeekController extends Controller
{
    /*
    |-----------------------------------------------------------
    | function to load gameweek wizard
    |-----------------------------------------------------------
     */
    public function index(Request $request, $id = "", $weekId = '')
    {
        $data = array();
        $team = array();
        $gameweek_number = settingValue('gameweek_number');
        $gameweek_number = (int) $gameweek_number;
        if ($weekId != '') {
            $data['week'] = $weekId;
        } else {
            $data['week'] = (int) $gameweek_number;
        }

        $data['current_week'] = $gameweek_number;
        for ($i = 0; $i <= $data['current_week']; $i++) {
            $data['weekDropdown'][] = $i;
        }

        $data['clubs'] = getClubsWithGameWeekFlag($data['week']);
        // get first club data
        if (!empty($id)) {
            $data['club'] = getClubById($id, $data['week']);
        } else {
            $data['club'] = getGameWeekFirstClub($data['week']);
        }
        if (!empty($data['club'])) {
            $data['players'] = \DB::table('player')->where('club', $data['club']->id)->orderBy('name')->select('*')->get();
        }

        $setting = SiteSettings::where('setting_key', 'gameweek_deadline')->first();
        $data['deadline'] = $setting;
        $data['weekNum'] = (int) $gameweek_number;
        $id = Auth::User()->id;
        return view('admin.gameweek.index')->with($data);
    }

    /*
    |-----------------------------------------------------------
    | function to add/edit gameweek against club
    |-----------------------------------------------------------
     */
    public function save(Request $request)
    {
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        @ini_set('max_execution_time', 0);
        $data = $request->except('_token');
        // loop through club data and save into database
        $gameweek = $data['form'];
        $clubId = $gameweek['clubID'];
        $week_date = $gameweek['week_date'];
        unset($gameweek['clubID'], $gameweek['week_date']);
        // echo '<pre>';
        // print_r($gameweek);
        // die();

        // save the data for each player in this form
        foreach ($gameweek as $key => $value) {
            // set values
            $player_data = array(
                'match_start' => (isset($value['match_start']) && $value['match_start']) ? $value['match_start'] : 0,
                'played_for_60_mins' => (isset($value['played_for_60_mins']) && $value['played_for_60_mins']) ? $value['played_for_60_mins'] : 0,
                'clean_sheet' => (isset($value['clean_sheet']) && $value['clean_sheet']) ? $value['clean_sheet'] : 0,
                'number_of_goals' => (isset($value['number_of_goals'])) ? intval($value['number_of_goals']) : 0,
                'number_of_assists' => (isset($value['number_of_assists'])) ? intval($value['number_of_assists']) : 0,
                'number_of_goals_conceded' => (isset($value['number_of_goals_conceded'])) ? intval($value['number_of_goals_conceded']) : 0,
                'penalty_save' => (isset($value['penalty_save'])) ? intval($value['penalty_save']) : 0,
                'penalty_miss' => (isset($value['penalty_miss'])) ? intval($value['penalty_miss']) : 0,
                'number_of_yellow_cards' => (isset($value['number_of_yellow_cards'])) ? intval($value['number_of_yellow_cards']) : 0,
                'number_of_red_cards' => (isset($value['number_of_red_cards']) && $value['number_of_red_cards']) ? $value['number_of_red_cards'] : 0,
                'best_player' => (isset($value['best_player']) && $value['best_player']) ? $value['best_player'] : 0,
                'second_best_player' => (isset($value['second_best_player']) && $value['second_best_player']) ? $value['second_best_player'] : 0,
                'third_best_player' => (isset($value['third_best_player']) && $value['third_best_player']) ? $value['third_best_player'] : 0,
                'hattrick' => (isset($value['hattrick']) && $value['hattrick']) ? $value['hattrick'] : 0,
            );
            if (!empty($week_date)) {
                $week_number = @$week_date;
            } elseif (!empty($value['week_number'])) {
                $week_number = @$value['week_number'];
            } else {
                $week_number = 0;
            }
            // calculate the points for the player once to save db queries and time (need to add column to model and db

            $is_saved = GameWeek::updateOrCreate(['player_id' => $key, 'week_number' => $week_number], $player_data);
            if ($player_data['match_start'] == 1) {
                $player_data['points'] = getPlayerGameWeekPoints($key, $week_number);
            } else {
                $player_data['points'] = 0.00;
            }
            $is_saved = GameWeek::updateOrCreate(['player_id' => $key, 'week_number' => $week_number], $player_data);

        }
        // echo '<pre>';
        // print_r($player_data);
        // die();

        // get all the players in the 'rangers' team to loop through

        // in here, first check the player_to_team_history exists

        $team_history = DB::table('player_to_team_history')->where('week_number', $week_number)->count();

        if ($team_history > 0) {
            // do nothing
        } else {
            // populate the whole table, for all teams etc in one go

            $query = \DB::select(\DB::raw("insert into player_to_team_history (select *, :week_number as week_number from player_to_team);"), array(
                ':week_number' => (int) $week_number,
            ));
        }

        // we need to delete the entries from here if they exist first (to prevent double scores) - however, we do need a flag for the admin to be able to specify 'double game week' where it doesnt delete
        // we will run this query first, and will wrap it with a condition and add a checkbox (or an extra save button?) in the admin area

        $query = \DB::select(\DB::raw("delete from player_gameweek_history where week_number=:week_number and club=:club;"), array(
            ':week_number' => (int) $week_number,
            ':club' => (int) $clubId,
        ));
        // grab all the data for this club, for everyone who has them in their team
        $query = \DB::select(\DB::raw("insert into player_gameweek_history(select
            '' as id,
            pt.player_id,
            pt.team_id,
            p.club,
            g.week_number,
            p.injured_out as injured,
            p.missing,
            p.suspended,
            pt.c_v_c,
            if (t.bench_boost_week_number=:week_number_a ,t.bench_boost_used, 0) bench_boost,
            if (t.triple_captain_week_number=:week_number_b, t.triple_captain_used, 0) triple_captain,
            pt.on_bench,
            pt.position_used_for,
            g.points,
            '',
            ''
        from player_gameweek g
            left outer join player_to_team_history pt on g.player_id=pt.player_id and g.week_number=pt.week_number
            left outer join player p on p.id=pt.player_id and pt.player_id = g.player_id
            left outer join team t on t.id=pt.team_id
        where g.week_number=:week_number_c and p.club=:club);"), array(
            ':week_number_a' => (int) $week_number,
            ':week_number_b' => (int) $week_number,
            ':week_number_c' => (int) $week_number,
            ':club' => (int) $clubId,
        ));

        // set points to 0 for injured / out
        $query = \DB::select(\DB::raw("update player_gameweek_history set points = 0 where (injured=1 or suspended=1 or missing=1) and week_number=:week_number and club=:club;"), array(
            ':week_number' => (int) $week_number,
            ':club' => (int) $clubId,
        ));
        // set points to 0 for players who dont have bench boost, but are on bench
        $query = \DB::select(\DB::raw("update player_gameweek_history set points = 0 where on_bench=1 and (bench_boost=0 or bench_boost is null) and week_number=:week_number and club=:club;"), array(
            ':week_number' => (int) $week_number,
            ':club' => (int) $clubId,
        ));
        // set double captatin points only for non-triple-captains
        $query = \DB::select(\DB::raw("update player_gameweek_history set points = points*2 where c_v_c=1 and (triple_captain=0 or triple_captain is null) and week_number=:week_number and club=:club;"), array(
            ':week_number' => (int) $week_number,
            ':club' => (int) $clubId,
        ));
        // set vice-captain points
        $query = \DB::select(\DB::raw("update player_gameweek_history set points = points*1.5 where c_v_c=2 and week_number=:week_number and club=:club;"), array(
            ':week_number' => (int) $week_number,
            ':club' => (int) $clubId,
        ));
        // set triple captain points
        $query = \DB::select(\DB::raw("update player_gameweek_history set points = points*3 where c_v_c=1 and triple_captain=1 and week_number=:week_number and club=:club;"), array(
            ':week_number' => (int) $week_number,
            ':club' => (int) $clubId,
        ));

        $query = \DB::select(\DB::raw("update team set overall_rank = 0"));
        $query = \DB::select(\DB::raw("update team t left outer join (select team_id, rank() over (order by sum(points) desc) overall_rank from player_gameweek_history group by team_id) s on s.team_id=t.id set t.overall_rank = s.overall_rank"));
        $query = \DB::select(\DB::raw("update team t left outer join (select id, max(overall_rank) +1 overall_rank from team) s on s.id=t.id set t.overall_rank = s.overall_rank where t.id not in (select team_id from player_gameweek_history)"));

        $gameweek = settingValue("gameweek");
        $current_position_gameweek_value = $gameweek - 1;
        $previous_position_gameweek_value = $gameweek - 2;

        $query = \DB::select(\DB::raw("update team_to_league t left outer join (

            select
                h.team_id,
                if(d.deductions is null, 0, d.deductions),
                h.points,
                if(d.deductions is null, h.points, h.points-d.deductions),
                l.league_id,
                rank() over (
                    partition by l.league_id
                    order by if(d.deductions is null, h.points, h.points-d.deductions) desc
                ) overall_rank
            from (select team_id, sum(points) points from player_gameweek_history where week_number <= :gameweek0 group by team_id) h
            left outer join (select team_id, sum(number_of_deducted_points) deductions from deductions where week_number <= :gameweek1 group by team_id) d on h.team_id=d.team_id
            left outer join team_to_league l on l.team_id=h.team_id





            ) s on s.team_id=t.team_id and s.league_id=t.league_id set t.current_position = s.overall_rank;"), array(
            ':gameweek0' => $current_position_gameweek_value,
            ':gameweek1' => $current_position_gameweek_value,
        ));

        $query = \DB::select(\DB::raw("update team_to_league t left outer join (

            select
                h.team_id,
                if(d.deductions is null, 0, d.deductions),
                h.points,
                if(d.deductions is null, h.points, h.points-d.deductions),
                l.league_id,
                rank() over (
                    partition by l.league_id
                    order by if(d.deductions is null, h.points, h.points-d.deductions) desc
                ) overall_rank
            from (select team_id, sum(points) points from player_gameweek_history where week_number <= :gameweek0 group by team_id) h
            left outer join (select team_id, sum(number_of_deducted_points) deductions from deductions where week_number <= :gameweek1 group by team_id) d on h.team_id=d.team_id
            left outer join team_to_league l on l.team_id=h.team_id





            ) s on s.team_id=t.team_id and s.league_id=t.league_id set t.previous_position = s.overall_rank;"), array(
            ':gameweek0' => $previous_position_gameweek_value,
            ':gameweek1' => $previous_position_gameweek_value,
        ));

        // TODO - need these added in to the mix:
        // select max(overall_rank) +1 overall_rank from team;
        // update team set overall_rank = X from above where overall_rank is null;

        //die();

        Alert::success('Success Message', 'Game week data saved successfully!');
        return redirect('admin/gameweek/' . $clubId . '/' . $week_number);
    }

    public function showDeadline()
    {
        $data = array();
        $gameweek_number = settingValue('gameweek_number');
        if (empty($gameweek_number)) {
            $gameweek_number = 0;
        }

        $data['week'] = (int) $gameweek_number;
        $data['weekNum'] = (int) $gameweek_number;

        $data['clubs'] = getClubsWithGameWeekFlag($data['week']);
        // get first club data
        if (!empty($id)) {
            $data['club'] = getClubById($id, $data['week']);
        } else {
            $data['club'] = getGameWeekFirstClub($data['week']);
        }
        if (!empty($data['club'])) {
            $data['players'] = getVal('*', 'player', 'club', $data['club']->id);

            // $gameweekPlayer = getGameWeekPlayers($data['week'], $data['club']->id);
            // $data['players'] = $gameweekPlayer;
        }

        // $selection = array();
        // $date = Carbon::now();
        // for ($i = 0; $i <= 52; $i++) {
        //     $date->setISODate(date('Y'),$i);
        //     $data['selection'][$i] = "Week ".$i. '   [' .date('F d, Y', strtotime($date->startOfWeek())) .' >< '. date('F d, Y', strtotime($date->endOfWeek())).'] ';
        // }
        // echo '<pre>';
        // print_r($data['selection']);
        // die();

        $setting = SiteSettings::where('setting_key', 'gameweek_deadline')->first();
        $data['deadline'] = $setting;
        $id = Auth::User()->id;
        return view('admin.gameweek.deadline')->with($data);
    }
    public function saveDeadline(Request $request)
    {
        $requestData = $request->all();
        unset($request->_token);
        $validateData = [];
        $validateData['deadline'] = 'required';
        $validateData['weekNum'] = 'required';
        // validate settings
        $validator = \Validator::make($requestData, $validateData);

        if ($validator->fails()) {
            $data['message'] = "Please provide data.";
            Alert::Error($data['message'])->autoclose(4000);
            return redirect()->back();
        }
        //dd($validator);

        $setting = SiteSettings::where('setting_key', 'gameweek_deadline')->count();
        $weekSetting = SiteSettings::where('setting_key', 'gameweek_number')->count();
        if ($setting > 0) {
            SiteSettings::where('setting_key', 'gameweek_deadline')->update(['setting_value' => $request->deadline]);
        } else {
            SiteSettings::insert(['setting_key' => 'gameweek_deadline', 'setting_value' => $request->deadline]);
        }
        if ($weekSetting > 0) {
            SiteSettings::where('setting_key', 'gameweek_number')->update(['setting_value' => $request->weekNum]);
        } else {
            SiteSettings::insert(['setting_key' => 'gameweek_number', 'setting_value' => $request->weekNum]);
        }
        Alert::success('Success Message', 'Game week deadline saved successfully!');
        return redirect()->back();
    }

    public function showGameweek(Request $request, $weekId, $id = '')
    {
        $data = array();
        $gameweek_number = settingValue('gameweek_number');
        if (empty($gameweek_number)) {
            $gameweek_number = 0;
        }

        $week_date = $weekId;
        //var_dump($week_date);
        if ($week_date != "") {
            $data['week'] = $week_date;
            $data['weekNum'] = $week_date;
            //echo 'if condition';
        } else {
            $data['week'] = (int) $gameweek_number;
            $data['weekNum'] = (int) $gameweek_number;
        }

        //dd($data['week']);
        $data['clubs'] = getClubsWithGameWeekFlag($data['week']);
        // dd($data['clubs']);
        // get first club data
        $data['clubId'] = $id;
        if (!empty($id)) {
            $data['club'] = getClubById($id, $data['week']);
        } else {
            $data['club'] = getGameWeekFirstClub($data['week']);
        }
        if (!empty($data['club'])) {
            // $data['players'] = getVal('*', 'player', 'club', $data['club']->id);
            $data['players'] = \DB::table('player')->where('club', $data['club']->id)->orderBy('name')->select('*')->get();

            // $gameweekPlayer = getGameWeekPlayers($data['week'], $data['club']->id);
            // $data['players'] = $gameweekPlayer;
        }

        $id = Auth::User()->id;
        return view('admin.gameweek.ajax-week')->with($data);
    }
    public function records()
    {
        $data = GameWeek::all();
        dd($data);
    }
}
