<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers;
use App\Gameweek;
use Illuminate\Support\Facades\Auth;
use Validator;


class GameWeekController extends Controller
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
     * calculate_points api
     *
     * @return \Illuminate\Http\Response
     */
    public function calculate_points(Request $request){
        
    }





}