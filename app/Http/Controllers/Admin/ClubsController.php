<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Clubs;
use Illuminate\Http\Request;
use Alert;
use Image;
use File;

class ClubsController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index() {
        
        $data['clubs'] = Clubs::paginate(10);
        $data['total'] = Clubs::count();
        return view('admin.clubs.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create() {
        
        $data['action'] = "Add";
       
        return view('admin.clubs.edit')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id) {
        
        $data['club'] = Clubs::findOrFail($id);
        $data['action'] = "Edit";
        return view('admin.clubs.edit')->with($data);
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
            
            $clubs = Clubs::findOrFail($input['id']);
            $clubs->update($input);
            $old_image = $clubs->club_shirt;
            $photo = "";
            if (isset($_FILES['club_shirt']['name']) && $_FILES['club_shirt']['size'] > 0) {
                $ext = strtolower(pathinfo($_FILES['club_shirt']['name'], PATHINFO_EXTENSION)); // getting image extension
                $photo = $input['id'] . '-' . uniqid() . '.' . $ext; // renameing image
                $destinationPath = "uploads/clubs/" . $photo;
                move_uploaded_file($_FILES['club_shirt']['tmp_name'], $destinationPath);
                //remove old image
                if ($old_image) {
                    File::delete("uploads/clubs/" . $old_image);
                }
            } else {
                $photo = $old_image;
            }
            $club_image['club_shirt'] = $photo;
            $clubs->update($club_image);
            Alert::success('Success Message', 'Club updated successfully!')->autoclose(3000);
        } else {
            //unset($input['action']);
            $id = Clubs::create($input)->id;
            $photo = "";
            if (isset($_FILES['club_shirt']['name']) && $_FILES['club_shirt']['size'] > 0) {
                $ext = strtolower(pathinfo($_FILES['club_shirt']['name'], PATHINFO_EXTENSION)); // getting image extension
                $photo = $id . '-' . uniqid() . '.' . $ext; // renameing image
                $destinationPath = "uploads/clubs/" . $photo;
                move_uploaded_file($_FILES['club_shirt']['tmp_name'], $destinationPath);
            }
            //insert image record   
            $Club = clubs::findOrFail($id);
            $club_image['club_shirt'] = $photo;
            $Club->update($club_image);
            
            Alert::success('Success Message', 'Club added successfully!')->autoclose(3000);
        }

            return redirect('admin/clubs');        
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id) {
        clubs::destroy($id);
        Alert::success('Success Message', 'Club deleted successfully!')->autoclose(3000);
        return redirect('admin/clubs');
    }

}
