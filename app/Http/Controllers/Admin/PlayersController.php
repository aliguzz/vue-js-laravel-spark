<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Clubs;
use App\Http\Controllers\Controller;
use App\Players;
use Auth;
use Illuminate\Http\Request;
use View;

class PlayersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        //\DB::enableQueryLog();
        $data['players'] = Players::join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*')->orderBy('clubs.name', 'asc')->orderBy('player.name', 'asc')->get();
        //var_dump(\DB::getQueryLog());

        $data['total'] = Players::select('*')->count();
        return view('admin.players.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $data['clubs'] = Clubs::all();
        $data['action'] = "Add";
        //$data['roles'] = \DB::table('roles')->where('id', '>=', $user->role)->where('is_active', 1)->get();
        return view('admin.players.edit')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $data['player'] = Players::findOrFail($id);
        $data['clubs'] = Clubs::all();
        $data['action'] = "Edit";
        return view('admin.players.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $input = $request->all();

        if ($input['action'] == 'Add') {
            $user_data = Players::select('id')->where('name', $input['name'])->first();
            if ($user_data) {
                Alert::error('Error Message', 'Player already exists!')->autoclose(3000);
                return redirect('admin/players/create');
            }
        } else {
            $user_data = Players::select('id')->where('name', $input['name'])->first();
            if ($input['id'] != $user_data['id']) {
                Alert::error('Error Message', 'Player already exists!')->autoclose(3000);
                return redirect('admin/players/create');
            }
        }
        if ($input['action'] == 'Edit') {
            $User = Players::findOrFail($input['id']);
            $res = $User->update($input);
            if ($res) {
                Alert::success('Success Message', 'Player updated successfully!')->autoclose(3000);
            } else {
                Alert::error('Error Message', 'Player cannot updated!')->autoclose(3000);
            }
        } else {
            $User = Players::create($input);
            Alert::success('Success Message', 'Player added successfully!')->autoclose(3000);
        }

        return redirect('admin/players');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {

        Players::destroy($id);
        Alert::success('Success Message', 'Player deleted!');
        return redirect('admin/players');
    }


    public function search(Request $request)
    {   
        $players=Players::join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*')->orderBy('clubs.name', 'asc')->orderBy('player.name', 'asc');
        return Datatables::of($players)->make(true);





    // if($request->ajax())
    // {
    // $output="";
    // $players=Players::join('clubs', 'clubs.id', '=', 'player.club')->select('clubs.name as club_name', 'clubs.club_shirt as shirt', 'player.*')->orderBy('clubs.name', 'asc')->orderBy('player.name', 'asc')
    //                 ->where('player.name','LIKE','%'.$request->search."%")->get();
    // if($players)
    // {
    // foreach ($players as $key => $player) {
    // $output.='<tr>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '<td>'.$player->name.'</td>'.
    // '</tr>';
    // }
    // return Response($output);
    //    }
    //    }
    }   


}
