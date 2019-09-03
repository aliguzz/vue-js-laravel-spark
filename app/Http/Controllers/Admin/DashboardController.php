<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AdminModels\DbModel;
use Session;
use Auth;
use Redirect;
use Alert;
use Stripe\Error\Card;
use Cartalyst\Stripe\Stripe;

class DashboardController extends Controller {

    /**
     * Display a dashboard.
     *
     * @return \Illuminate\View\View
     */
	 
    private $_apiContext;

    public function __construct() {
       $this->_apiContext = 'laravel spark';
    }
	
	
    public function index(Request $request) {
		//echo 'hello'; exit;
                $data = array();
		$id = Auth::User()->id;
		
        return view('admin.dashboard')->with($data);
    }

    public function download_transfer_csv() {
		// $headers = array(
        //     'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        //     'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        //     'Content-Disposition' => 'attachment; filename=abc.csv',
        //     'Expires' => '0',
        //     'Pragma' => 'public',
        // );
        // $headers = array(
        //     'Content-Type' => 'text/csv',
        //     'Content-Disposition' => 'attachment; filename="ExportFileName.csv"',
        // );
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
        ,   'Content-type'        => 'text/csv'
        ,   'Content-Disposition' => 'attachment; filename=transfers.csv'
        ,   'Expires'             => '0'
        ,   'Pragma'              => 'public'
    ];
    
    $filename = "transfers.csv";
    $handle = fopen($filename, 'w');
    fputcsv($handle, [
        "id",
        "name"
    ]);
    
    $callback =  DB::table("users")->chunk(100, function ($data) use ($handle) {
        foreach ($data as $row) {
            // Add a new row with data
            fputcsv($handle, [
                $row->id,
                $row->name
            ]);
        }
    });
    
    fclose($handle);
    
    //return Response::download($filename, "transfers.csv", $headers);
    return Response::stream($callback, 200, $headers);
    }
	
	public function my_packages() {
        $data    = array();
		$user_id = Auth::User()->id;   				 
        return view('admin.my_packages')->with($data);
    }
	
	public function payment_history($type='') {
        $data    = array();
		$user_id = Auth::User()->id;
		 
        return view('admin.payment_history')->with($data);
    }
	
	public function upgrade_account() {
        $data = array();
		$id = Auth::User()->id;
			
        return view('admin.upgrade_account')->with($data);
    }
	
	
    public function payment_method($id) {
                $data = array();
		$user_id = Auth::User()->id;
                return view('admin.my_packages')->with($data);
    }


}
