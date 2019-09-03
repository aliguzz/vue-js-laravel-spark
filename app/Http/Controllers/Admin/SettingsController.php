<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use Alert;
use DB;
use Image;
use App\Leads;
use File;

class SettingsController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index() {
        //$settings = DB::table('site_settings')->get();
        return view('admin.settings.settings');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request) {
        not_permissions_redirect(have_premission(array(52)));
        $requestData = $request->all();
        unset($requestData['_token']);

        $validateData = [];
        foreach ($requestData as $key => $value) {
            $validateData[$key] = 'required';
        }

        $this->validate($request, $validateData);

        foreach ($requestData as $key => $value) {
            if ($key != 'site_logo') {
                $setting = DB::table('site_settings')->where('option_name', $key)->count();
                if ($setting > 0) {
                    DB::table('site_settings')->where('option_name', $key)->update(['option_value' => $value]);
                } else {
                    DB::table('site_settings')->insert(['option_name' => $key, 'option_value' => $value]);
                }
            }
        }


        $data = DB::table('site_settings')->where('option_name', 'site_logo')->first();
        $old_image = $data->option_value;
        
        $photo = "";
        if (isset($_FILES['site_logo']['name']) && $_FILES['site_logo']['size'] > 0) {
            $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
            $photo = 'logo' . '-' . uniqid() . '.' . $ext;
            $destinationPath = "uploads/settings/" . $photo;
            move_uploaded_file($_FILES['site_logo']['tmp_name'], $destinationPath);
            //remove old image
            if ($old_image) {
                File::delete("uploads/settings/" . $old_image);
            }
            //insert image record            
            DB::table('site_settings')->where('option_name', 'site_logo')->update(['option_value' => $photo]);
        }
        Alert::success('Success Message', 'Settings updated!');

        return redirect('admin/settings');
    }

    /**
     * Change Password the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function changePassword(Request $request) {

        $validator = Validator::make($request->all(), [
                    'current_password' => 'required',
                    'password' => 'required|confirmed'
        ]);

        $requestData = $request->all();

        $validator->after(function($validator) use ($request) {

            if (!Auth::attempt(['password' => $request->current_password])) {
                $validator->errors()->add('current_password', 'Your current password is incorrect, please try again.');
            }
        });

        if ($validator->fails()) {
            return redirect('admin/settings')->withErrors($validator);
        }

        $user = Auth::user();
        $user->password = \Hash::make($request->current_password);
        $user->save();

        Alert::success('Success Message', 'Settings updated!');

        return redirect('admin/settings');
    }

}
