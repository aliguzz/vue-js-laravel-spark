<?php

namespace App\Services;

use Laravel\Spark\Spark;
use Carbon\Carbon;
use Laravel\Spark\Token;
use Laravel\Spark\Team;
use App\FantasyTeams;
use App\Http\Controllers;
use App\PlayerTeam;
use App\UserTeam;
use Laravel\Spark\Repositories\TokenRepository;
use App\Http\Requests\RegistrationRequest;
use App\Services\Contracts\RegisterServiceInterface;
use App\User;

class RegisterService implements RegisterServiceInterface
{

    private $tokensRepo;

    public function register(RegistrationRequest $request): array
    {
        $errorResponse = [
            'error' => 'Account couldn\'t created, Error occured',
            'code' => 422
        ];

        \DB::beginTransaction();
        $user = new User($request->all());
        $endresponse = array();
        $user->last_read_announcements_at = Carbon::now();
        $user->trial_ends_at = Carbon::now()->addDays(Spark::trialDays());
        $user->social_type = 'normal';
        try{
            $endresponse = $user->hashPassword($request->password)->save()
            ? [
                'user_id' => $user->id,
                'code' => 200]
            : $errorResponse;

            
        $data = count(Spark::tokensCan()) > 0 ? ['abilities' => $request->abilities] : [];
        $finaltoken = (new TokenRepository())->createToken(
            $user, $user->name, $data
        )->token;


        //////////////////////////



        $toinsertspark = array(
            'name' => $user->name,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'owner_id' => $user->id,
        );

            $team = Team::forceCreate($toinsertspark);
            $team_id = $team->id;
            $toinsert = array(
                'name' => $user->name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_id' => $user->id,
                'formation' => '4-4-2',
                'spark_team_id' => $team_id,
                'budget' => settingValue('budget'),
            );

            FantasyTeams::forceCreate($toinsert);

            $userteam = array(
                'user_id' => $user->id,
                'team_id' => $team_id,
            );

            UserTeam::forceCreate($userteam);


        /////////////////////////// user team creation on his own name

        
            \DB::commit();


            $FantasyTeams = FantasyTeams::with('sparkteam')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->toArray();

            return $Responsearray =  array(
                'user'    => $user,
                'token'   => $finaltoken,
                'user_id' => $user->id,
                'Message' => 'You are successfully signed up please check you email',
                'status'  => 200
            );

        }
        catch(\Exception $req){
            \DB::rollBack();
            return $Responsearray =  array(
                'ErrorMessage' => $errorResponse,
                'status'  => 401
            );
        }
        
    }

    public function registerBySocialite(): array
    {

    }
}