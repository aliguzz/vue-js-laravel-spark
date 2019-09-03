<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use Excel;
use File;
use App\Imports\playersImport;
use App\AdminModels\Player;
use Alert;
use App\Clubs;

class ImportContoller extends Controller
{
    public function index()
    {
        return view('admin.imports.excel');
    }

    public function import(Request $request)
    {
        $validateData = [];
        $requestData = $request->all();
        $validateData['file'] = 'required';

        // validate settings
        $validator = \Validator::make($requestData, $validateData);

        if ($validator->fails()) {

            $data['message'] = "Please choose File.";
            Alert::Error($data['message'])->autoclose(4000);
            return redirect()->back();
        
        }else{
            $clubArray = [];
            $clubs = Clubs::all();
            foreach($clubs AS $club){
                $clubArray[$club->name]=$club->id;
                }

                $positionArray = ['A'=> 'FOR',
                'M' => 'MID',
                'D' => 'DEF', 
                'G'=> 'GK']; 

        // to import file
        $data = Excel::toCollection(new playersImport, $request->file('file'));
            // dd($data);
        //set cost
        foreach($data AS $row_in){
            foreach($row_in As $row){
            $cost = $row[4];
            $cost = str_replace('£','',$cost);
            $cost = str_replace(',','',$cost);
            (float)$new_cost = (float)( $cost ) / (1000000);
        //set position   
            
            $position = $row[5];
                 if(array_key_exists($position, $positionArray)){
                    $position = $positionArray[$position];
                    }

        //set club column
                $club = $row[6];
                if(array_key_exists($club, $clubArray)){
                    $club = $clubArray[$club];
                    }

             Player::create([
            'name'              => $row[0],
            'colours'           => $row[1],
            'injured_available' => $row[2],
            'injured_out'       => $row[3],
            'cost'              => $new_cost,
            'position'          => $position,
            'club'              => $club,
            'points'            => $row[7],
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')

         ]);
        }
    }

        //update any column after import
        // $clubs = Clubs::all();

        // foreach($clubs AS $club){
        // Player::where('club' , $club->name)->update(['club' => $club->id]);
        // }
        
        Alert::success('Success Message', 'Data Imported Successfully!');
        return redirect('admin/upload-excel')->with('Success Message', 'Data Imported Successfully!');
       
    }
    }
}