<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\DbModel;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use View;

class UsersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {

        $users = $request->get('query');
        \DB::enableQueryLog();
        $users_table = User::where('role', 1);
        if(!empty($users)){
            $users_table->where(function($q) use($users){
                return $q->where("email", "like", "%".$users."%")->orWhere("name", "like", "%".$users."%");
            });
        }
        $data['users'] = $users_table->select('*')->paginate(1000);
        // echo '<pre>';
        // var_dump(\DB::getQueryLog());
        // die();
        $data['total'] = User::where('role', 1)->count();
        return view('admin.users.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $data['action'] = "Add";
        return view('admin.users.edit')->with($data);
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
        $data['user'] = User::findOrFail($id);
        //$data['countries'] = \DB::table('countries')->get();
        $data['action'] = "Edit";
        //$data['roles'] = \DB::table('roles')->where('is_active', 1)->get();
        return view('admin.users.edit')->with($data);
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

        if (isset($input['new_password'])) {
            $input['password'] = Hash::make($input['new_password']);
            //$input['original_password'] = $input['new_password'];
        }

        if ($input['action'] == 'Add') {
            $user_data = User::select('id')->where('email', $input['email'])->first();
            if ($user_data) {
                Alert::error('Error Message', 'Email already exist for other user!')->autoclose(3000);
                return redirect('admin/users/create');
            }
        } else {
            $user_data = User::select('id')->where('email', $input['email'])->first();
            if ($input['id'] != $user_data['id']) {
                Alert::error('Error Message', 'Email already exist for other user!')->autoclose(3000);
                return redirect('admin/users/create');
            }
        }
        if ($input['action'] == 'Edit') {
            $User = User::findOrFail($input['id']);
            $res = $User->update($input);
            if ($res) {
                Alert::success('Success Message', 'User updated successfully!')->autoclose(3000);
            } else {
                Alert::error('Error Message', 'User cannot updated!')->autoclose(3000);
            }
        } else {
            $code_length = rand(20, 25);
            $unique_code = DbModel::unqiue_code($code_length);
            $input['unique_code'] = $unique_code;
            unset($input['action']);
            unset($input['new_password']);
            unset($input['confirm_password']);
            $User = User::create($input);
            Alert::success('Success Message', 'User added successfully!')->autoclose(3000);
        }
        return redirect('admin/users');
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
        User::destroy($id);
        Alert::success('Success Message', 'User deleted!');
        return redirect('admin/users');
    }
}
