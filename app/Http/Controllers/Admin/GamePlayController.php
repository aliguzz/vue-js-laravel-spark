<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\GameWeek;
use App\Http\Controllers\Controller;
use App\Players;
use App\SiteSettings;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Excel;
use DB;
use App\Exports\GameweekExport;

class GamePlayController extends Controller
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
        $weekStart = settingValue('first_gameweek_number');
        // dd($weekStart);
        if (empty($weekStart))
            $weekStart = 0;
        if (!empty($weekId)) {
            $data['week'] = $weekId;
        } else {
            $data['week'] = Carbon::now()->weekOfYear - $weekStart;
        }

        //$data['weekDropdown'] = getAllWeeks('2019');
        $firstgameweek = SiteSettings::where('setting_key', 'gameweek_number')->first();
        //print_r($firstgameweek->setting_value);
        //die();
        $firstgameweek = $firstgameweek->setting_value;
        for($i = $firstgameweek; $i <= Carbon::now()->weekOfYear; $i++){
            $data['weekDropdown'][] = $i;    
        }
        
        // print_r($data['weekDropdown']);
        // die();

        $data['current_week'] = Carbon::now()->weekOfYear - $weekStart;
        $data['clubs'] = getClubsWithGameWeekFlag($data['week']);
        // get first club data
        if (!empty($id)) {
            $data['club'] = getClubById($id, $data['week']);
        } else {
            $data['club'] = getGameWeekFirstClub($data['week']);
        }
        if (!empty($data['club'])) {
            // $data['players'] = getVal('*', 'player', 'club', $data['club']->id);
            $data['players'] =\DB::table('player')->where('club', $data['club']->id)->orderBy('name')->select('*')->get();

            // $gameweekPlayer = getGameWeekPlayers($data['week'], $data['club']->id);
            // $data['players'] = $gameweekPlayer;
        }

        $setting = SiteSettings::where('setting_key', 'gameweek_deadline')->first();
        $weekSetting = SiteSettings::where('setting_key', 'first_gameweek_number')->first();
        $data['deadline'] = $setting;
        $data['weekNum'] = $weekSetting;

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
        $data = $request->except('_token');
        // loop through club data and save into database
        $gameweek = $data['form'];

        // dd($gameweek['clubID']);
        // dd($gameweek['week_date']);
        // dd($gameweek);
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
            if (!empty($gameweek['week_date'])) {
                $week_number = $gameweek['week_date'];
            } else {
                $week_number = $value['week_number'];
            }

            $is_saved = GameWeek::updateOrCreate(['player_id' => $key, 'week_number' => $week_number], $player_data);

            $points = getPlayerTotalPoints($key);
            Players::where(['id' => $key])->update(['points' => $points]);
        }
        Alert::success('Success Message', 'Game week data saved successfully!');
        return redirect('admin/gameweek/' . $gameweek['clubID'] . '/' . $week_number);
    }
    public function showDeadline()
    {
        $data = array();
        $weekStart = settingValue('first_gameweek_number');
        if (empty($weekStart))
            $weekStart = 0;
        $data['week'] = Carbon::now()->weekOfYear - $weekStart;

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
        $setting = SiteSettings::where('setting_key', 'gameweek_deadline')->first();
        $weekSetting = SiteSettings::where('setting_key', 'first_gameweek_number')->first();
        $data['deadline'] = $setting;
        $data['weekNum'] = $weekSetting;
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
        $weekSetting = SiteSettings::where('setting_key', 'first_gameweek_number')->count();
        if ($setting > 0) {
            SiteSettings::where('setting_key', 'gameweek_deadline')->update(['setting_value' => $request->deadline]);
        } else {
            SiteSettings::insert(['setting_key' => 'gameweek_deadline', 'setting_value' => $request->deadline]);
        }
        if ($weekSetting > 0) {
            SiteSettings::where('setting_key', 'first_gameweek_number')->update(['setting_value' => $request->weekNum]);
        } else {
            SiteSettings::insert(['setting_key' => 'first_gameweek_number', 'setting_value' => $request->weekNum]);
        }
        Alert::success('Success Message', 'Game week deadline saved successfully!');
        return redirect()->back();
    }

    public function showGameweek(Request $request, $weekId, $id = '')
    {
        $data = array();
        $weekStart = settingValue('first_gameweek_number');
        if (empty($weekStart))
            $weekStart = 0;
        $week_date = $weekId;
        // dd($week_date);
        if (!empty($week_date)) {
            $data['week'] = $week_date;
        } else {
            $data['week'] = Carbon::now()->weekOfYear - $weekStart;
        }

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
