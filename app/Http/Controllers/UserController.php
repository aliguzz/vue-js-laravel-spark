<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers;
use App\FantasyTeams;
use App\PlayerTeam;
use App\UserTeam;
use Laravel\Spark\Team;
use Illuminate\Support\Facades\Hash;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;


class UserController extends Controller
{


    public $successStatus = 200;

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        
    }
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $api_tokens = \DB::table('api_tokens')->select('token')->where('user_id',$user->id)->first();
            $success['user_token'] =  $api_tokens->token;
            $success['user_id'] =  $user->id;
            return response()->json(['success' => $success]);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }


        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;


        return response()->json(['success'=>$success], $this->successStatus);
    }



    public function password_update(Request $request)
    {
        $input = $this->request->all();
        $user_id = Auth::user()->id;
        $password = Auth::user()->password;
        
        
        // var_dump($password);
        // die();
        $input = $this->request->all();
        $user = Auth::user();
        if (!Hash::check($input['old_password'], $password)) {
            return response()->json([
                'message' => 'Please enter correct password!',
                'status' =>  '401']);
        } else {
            $insertion_array = array('password' => Hash::make($input['new_password']));
            $user->update($insertion_array);
            
            return response()->json([
                'message' => 'your password has been updated successfully',
                'status' =>  '200']);
            
        }

            

      
    }


    public function resetpassword(Request $request)
    {
        $input = $this->request->all();
        $user_id = $input['user_id'];
        // var_dump($password);
        // die();
        if ($input['new_password'] != '') {
            $insertion_array = array('password' => Hash::make($input['new_password']));
            User::where("id",$user_id)->update($insertion_array);
            
            return response()->json([
                'message' => 'your password has been updated successfully',
                'status' =>  '200']);            
        }
    }



    public function forgotpassword(Request $request)
    {
        
        ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

        $input = $this->request->all();
        $user_email = $input['email'];
        $link = $input['link'];
        $user = User::where("email",$user_email)->first(); 
        
        if(!empty($user)){       
                $to = $user_email;
                $template_html_message = '<div style="color:#00d8ed;font-family:\'Lato\', Tahoma, Verdana, Segoe, sans-serif;line-height:150%;padding-top:10px;padding-right:10px;padding-bottom:20px;padding-left:10px;">
                <div style="font-family: \'Lato\', Tahoma, Verdana, Segoe, sans-serif; line-height: 18px; font-size: 12px; color: #00d8ed;">
                <p style="line-height: 18px; text-align: center; font-size: 12px; margin: 0;"><span style="color: #000000; font-size: 12px; line-height: 18px;"><span style="font-size: 26px; line-height: 39px;"><strong>Hi [NAME],</strong></span></span></p>
                <p style="line-height: 27px; text-align: center; font-size: 12px; margin: 0;"><span style="color: #000000; font-size: 18px;"><span style="line-height: 27px; font-size: 18px;">Fantasy Football Scotland recently received a request for a forgotten password.</span></span></p>
                <p style="line-height: 27px; text-align: center; font-size: 12px; margin: 0;"><span style="font-size: 18px;"><span style="color: #000000; line-height: 27px; font-size: 18px;"><span style="line-height: 27px; font-size: 18px;">To change your password, please click on this <a href="[LINK]" rel="noopener" style="text-decoration: underline; color: #0068A5;" target="_blank" title="reset password link">link</a></span></span></span></p>
                <p style="line-height: 18px; text-align: center; font-size: 12px; margin: 0;"> </p>
                <p style="line-height: 27px; text-align: center; font-size: 12px; margin: 0;"><span style="color: #000000; font-size: 18px;"><span style="line-height: 27px; font-size: 18px;"><span style="line-height: 27px; font-size: 18px;">Thanks,</span></span></span></p>
                <p style="line-height: 27px; text-align: center; font-size: 12px; margin: 0;"><span style="color: #000000; font-size: 18px;"><span style="line-height: 27px; font-size: 18px;"><span style="line-height: 27px; font-size: 18px;">Fantasy Football Scotland Support</span></span></span></p>
                <p style="font-size: 12px; line-height: 18px; margin: 0;"> </p>
                </div>
                </div>';
                $returnpath = "";
                $cc = "";
                $subject = "Fantasy Football Scotland Forgot Password";
                $link = $link.$user->id;
                $to_replace = ['[NAME]', '[LINK]'];
                $with_replace = [$user->name, $link];
                $html_body = '';
                $html_body .= str_replace($to_replace, $with_replace, $template_html_message);

                $mailContents = \View::make('admin.emailtemplates.fantasytemplate', ["data" => $html_body])->render();
                //$dd = DbModel::SendHTMLMail($to, $subject, $mailContents, $smtp->from_email, $smtp->from_name, $returnpath, $cc);
                //echo '<pre>'; dd($mailContents); die('here');
                $dd = sendMail($to, "", "", $subject, $mailContents, $returnpath, $cc,array());
                //echo '<pre>'; dd($dd); die('here');
                //$listIds[] = $email->id;


            if ($dd) {
                return response()->json([
                    'message' => 'Password reset link is sent to the provided email, please check INBOX/JUNK/SPAM to update your password',
                    'status' =>  '200']);
            } else {
                return response()->json([
                    'message' => 'Email didn\'t send',
                    'status' =>  '401']);
            }
        }else{
            return response()->json([
                'message' => 'The user associated to this email Id does not exist',
                'status' =>  '422']);
        }
    }




    public function name_update(Request $request)
    {
        $user_id = Auth::user()->id;
        $input = $this->request->all();
       
        $toupdate = array(
            'name'                  => $input['name']
        );
        $team = User::where('id',$user_id)->update($toupdate);
        

            return response()->json([
            'user_name'     =>  $input['name'],
            'message' => 'user name '.$input['name']. ' has been updated successfully',
            'status' =>  '200']);

      
    }



    public function team_update(Request $request)
    {
        $user_id = Auth::user()->id;
        $input = $this->request->all();

        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user_id)->orderBy('created_at', 'desc')->first()->toArray();

        $toinsertspark = array(
            'name' => $input['name'],
        );

        \DB::beginTransaction();
        try {
            $team = Team::where('id', $FantasyTeams['id'])->update($toinsertspark);
            //$team_id = $team->id;
            $toinsert = array(
                'name' => $input['name'],
            );

            FantasyTeams::where('id', $FantasyTeams['id'])->update($toinsert);

            
            \DB::commit();

            $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user_id)->orderBy('created_at', 'desc')->get()->toArray();

            return response()->json([
                'user_id' => $user_id,
                'team' => $FantasyTeams,
                'message' => $input['name'] . ' the team name has been updated successfully',
                'status' => '200']);

        } catch (\Exception $req) {
            \DB::rollBack();
            return $Responsearray = array(
                'Ex' => $req->getMessage(),
                'ErrorMessage' => 'Error updating Team Name',
                'status' => 422,
            );
        }
    }

    /////   team name update 


    public function email_update(Request $request)
    {
        $user_id = Auth::user()->id;
        $input = $this->request->all();

        $is_exist = User::where(['email' => $input['email']])->first();
            if(!empty($is_exist)) {
                return response()->json([
                'message' => 'same Email already exists or someone else has this email',
                'status' =>  '401']);
            }
       
        $toupdate = array(
            'email'                  => $input['email']
        );
        $team = User::where('id',$user_id)->update($toupdate);
        

            return response()->json([
            'user_name'     =>  $input['email'],
            'message' => 'user email '.$input['email']. ' has been updated successfully',
            'status' =>  '200']);

      
    }

    /**
     * destroy api
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $user = Auth::user();
        $user_id = Auth::user()->id;
        $user->delete($user_id);
        return response()->json([
            'user'     =>  $user,
            'message' => 'User deleted successfully',
            'status' =>  '200']);
    }



    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $user = Auth::user();
        $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first()->toArray();
        return response()->json(array(
            'user' => $user,
            'team' => $FantasyTeams,
        ), $this->successStatus);
    }
}