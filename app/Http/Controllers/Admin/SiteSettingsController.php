<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SiteSettings;
use Alert;

class SiteSettingsController extends Controller
{
    /**
     * Display site settings.
     *
     * @return \Illuminate\View\View
     */
    public function index() {
        $data['settings'] = SiteSettings::all();
        return view('admin.settings.settings')->with($data);
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
        $requestData = $request->all();
        unset($requestData['_token']);
        $validateData = [];
        foreach ($requestData as $key => $value) {
            $validateData[$key] = 'required';
        }

        // validate settings
        $validator = \Validator::make($requestData, $validateData);
        if ($validator->fails()) {
            return redirect('admin/settings')->withInput($requestData)->withErrors($validator);
        }

        foreach ($requestData as $key => $value) {
            $setting = SiteSettings::where('setting_key', $key)->count();
            if ($setting > 0) {
                SiteSettings::where('setting_key', $key)->update(['setting_value' => $value]);
            } else {
                SiteSettings::insert(['setting_key' => $key, 'setting_value' => $value]);
            }
        }
        Alert::success('Success Message', 'Settings updated!');
        return redirect('admin/settings');
    }
}
