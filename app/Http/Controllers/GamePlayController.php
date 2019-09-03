<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers;
use App\Gameweek;
use App\GameWeekHistory;
use App\FantasyTeams;
use App\Players;
use App\PlayerTeam;
use Carbon\Carbon;
use App\SiteSettings;
use App\UserTeam;
use Illuminate\Support\Facades\Auth;
use Validator;
use Laravel\Spark\Team;


class GamePlayController extends Controller
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
    public function index()
    {
        //
        return response()->json(Gameweek::all()->toArray());

    }

    /**
     * get_team_score_current_gameweek
     *
     * @return \Illuminate\Http\Response
     */
    public function get_team_score_current_gameweek()
    {
        $user_id = Auth::user()->id;
        $gameweek_number = settingValue('gameweek_number');
        $week_number = (int)$gameweek_number - 1;
        if($week_number < 0){
            $week_number = 0;
        }
        $data['week'] = (int)$gameweek_number;
        $response['team_id'] = FantasyTeams::where('user_id', Auth::user()->id)->first()->id;      
        $response['get_team_score_current_gameweek'] = get_team_score_current_gameweek($response['team_id'], $week_number);
        // echo '<pre>';
        // print_r($response['get_team_score_current_gameweek']);
        // die();
        return [
            'team_current_gameweek_points' => $response['get_team_score_current_gameweek'],
            'user_id' => $user_id,
            'status' => '200',
        ];

    }

    /**
     * get_team_score_all_gameweek
     *
     * @return \Illuminate\Http\Response
     */
    public function get_team_score_all_gameweek()
    {
        $user_id = Auth::user()->id;
        $gameweek_number = settingValue('gameweek_number');
        $week_number = (int)$gameweek_number;
        if($week_number < 0){
            $week_number = 0;
        }
        $data['week'] = (int)$gameweek_number;
        $response['team_id'] = FantasyTeams::where('user_id', Auth::user()->id)->first()->id;
        for($i = 0; $i <= $week_number; $i++){
            $response['all_weeks_score'] += get_team_score_current_gameweek($response['team_id'], $i);
        }
        
        return [
            'team_all_game_week_points' => $response['all_weeks_score'],
            'user_id' => $user_id,
            'status' => '200',
        ];
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
            'Gameweek' => 'required',
            'user_id' => 'required',
            'version_id' => 'required'
        ]);

        $toinsert = array(
            'Gameweek'       => $input['Gameweek'],
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
            'user_id'       => $input['user_id'],
            'version_id'    => $input['version_id']
        );

        \DB::table('Gameweek')->insert($toinsert);

        $Gameweek =  Gameweek::where('version_id', $input['version_id'])->orderBy('created_at', 'desc')->get()->toArray();
        $version =  Version::where('id', $input['version_id'])->first();

        return ['Gameweek' => response()->json($Gameweek),
        'user_id' => $version->user_id,
        'message' => 'Gameweek has been created successfully',
        'status' =>  '1'];
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Gameweek  $Gameweek
     * @return \Illuminate\Http\Response
     */
    public function show(Gameweek $Gameweek)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Gameweek  $Gameweek
     * @return \Illuminate\Http\Response
     */
    public function edit(Gameweek $Gameweek)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Gameweek  $Gameweek
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Gameweek $Gameweek)
    {
        $input = $this->request->all();
        $findidorfail = Gameweek::where('id', $input['id'])->first();
        if($findidorfail === null){
            return ['message' => 'Gameweek doesn\'t exist'];
            die();
        }   

        $finalvalidation = $this->validate($this->request, [
            'Gameweek' => 'required',
            'user_id' => 'required',
            'version_id' => 'required'
        ]);

        $toupdate = array(
            'Gameweek'       => $input['Gameweek'],
            'updated_at'    => date('Y-m-d H:i:s'),
            'user_id'       => $input['user_id'],
            'version_id'    => $input['version_id']
        );

        \DB::table('Gameweek')->where('id',$input['id'])->update($toupdate);

        return ['Gameweek' => response()->json(Gameweek::where('id',$input['id'])->get()->toArray()),
        'message' => 'Gameweek name has been updated successfully',
        'status' =>  '1'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Gameweek  $Gameweek
     * @return \Illuminate\Http\Response
     */
    public function destroy(Gameweek $Gameweek)
    {
        $input = $this->request->all();
        $findidorfail = Gameweek::where('id', $input['id'])->first();
        if($findidorfail === null){
            return ['message' => 'Gameweek doesn\'t exist'];
            die();
        }

        \DB::table('Gameweek')->where('id',$input['id'])->delete();

        return ['message' => 'Gameweek has been deleted successfully'];
    }

    /**
     * deadline_date api
     *
     * @return \Illuminate\Http\Response
     */
    public function deadline_date(Request $request){
        
    }


    /**
     * calculate_points api
     *
     * @return \Illuminate\Http\Response
     */
    public function calculate_points(Request $request){
        
    }



}