<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers;
use App\Transfer;
use Illuminate\Support\Facades\Auth;
use Validator;


class TransferController extends Controller
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
        return response()->json(Transfer::all()->toArray());

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
            'Transfer' => 'required',
            'user_id' => 'required',
            'version_id' => 'required'
        ]);

        $toinsert = array(
            'Transfer'       => $input['Transfer'],
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
            'user_id'       => $input['user_id'],
            'version_id'    => $input['version_id']
        );

        \DB::table('Transfer')->insert($toinsert);

        $Transfer =  Transfer::where('version_id', $input['version_id'])->orderBy('created_at', 'desc')->get()->toArray();
        $version =  Version::where('id', $input['version_id'])->first();

        return ['Transfer' => response()->json($Transfer),
        'user_id' => $version->user_id,
        'message' => 'Transfer has been created successfully',
        'status' =>  '1'];
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Transfer  $Transfer
     * @return \Illuminate\Http\Response
     */
    public function show(Transfer $Transfer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transfer  $Transfer
     * @return \Illuminate\Http\Response
     */
    public function edit(Transfer $Transfer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transfer  $Transfer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transfer $Transfer)
    {
        $input = $this->request->all();
        $findidorfail = Transfer::where('id', $input['id'])->first();
        if($findidorfail === null){
            return ['message' => 'Transfer doesn\'t exist'];
            die();
        }   

        $finalvalidation = $this->validate($this->request, [
            'Transfer' => 'required',
            'user_id' => 'required',
            'version_id' => 'required'
        ]);

        $toupdate = array(
            'Transfer'       => $input['Transfer'],
            'updated_at'    => date('Y-m-d H:i:s'),
            'user_id'       => $input['user_id'],
            'version_id'    => $input['version_id']
        );

        \DB::table('Transfer')->where('id',$input['id'])->update($toupdate);

        return ['Transfer' => response()->json(Transfer::where('id',$input['id'])->get()->toArray()),
        'message' => 'Transfer name has been updated successfully',
        'status' =>  '1'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Transfer  $Transfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transfer $Transfer)
    {
        $input = $this->request->all();
        $findidorfail = Transfer::where('id', $input['id'])->first();
        if($findidorfail === null){
            return ['message' => 'Transfer doesn\'t exist'];
            die();
        }

        \DB::table('Transfer')->where('id',$input['id'])->delete();

        return ['message' => 'Transfer has been deleted successfully'];
    }

    /**
     * transfer_free_or_points api
     *
     * @return \Illuminate\Http\Response
     */
    public function transfer_free_or_points(Request $request){
        
    }



}