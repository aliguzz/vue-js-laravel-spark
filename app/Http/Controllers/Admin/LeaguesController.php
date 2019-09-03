<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Leagues;
use App\FantasyTeams;
use Alert;

class LeaguesController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index() {
        
        $data['leagues'] = Leagues::orderBy('created_at', 'desc')->paginate(10);
        $data['total'] = Leagues::count();
        return view('admin.leagues.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create() {
        
        $data['action'] = "Add";
       
        return view('admin.leagues.edit')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */


    public function overallrank() {
        $gameweek_number = settingValue('gameweek_number');
        $week_number = (int)$gameweek_number - 1;
        if($week_number < 0){
            $week_number = 0;
        }
        $teampoints_by_id = [];
        $teams = FantasyTeams::select('id')->get()->toArray();
        foreach($teams as $key => $teamid){
            $points_by_team = get_team_score_current_gameweek($teamid, $week_number);
            $teampoints_by_id[$teamid["id"]] = $points_by_team;
            FantasyTeams::where('id',$teamid["id"])->update(['overall_rank' => $points_by_team]);
            echo '<pre>'.$teampoints_by_id[$teamid["id"]]."\n";
            //print_r($teampoints_by_id);
            my_flush();
        } 
        die();

    }



    public function edit($id) {
        
        $data['league'] = Leagues::findOrFail($id);
        $data['action'] = "Edit";
        return view('admin.leagues.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {
        $input = $request->all();
        // var_dump($input);
        // die('here');
        if ($input['action'] == 'Edit') {
            
            $leagues = Leagues::findOrFail($input['id']);
            $leagues->update($input);
            Alert::success('Success Message', 'League updated successfully!')->autoclose(3000);
        }
        else {
            //unset($input['action']);
            $id = Leagues::create($input)->id;
            $leagues = Leagues::findOrFail($id);
            Alert::success('Success Message', 'League added successfully!')->autoclose(3000);
        }
        return redirect('admin/leagues');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id) {
        Leagues::destroy($id);
        Alert::success('Success Message', 'League deleted successfully!')->autoclose(3000);
        return redirect('admin/leagues');
    }
}
