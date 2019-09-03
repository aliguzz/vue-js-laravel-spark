<?php

namespace App\Services;

use App\FantasyTeams;
use App\Helpers\SocialiteHelper;
use App\Services\Contracts\SocialiteServiceInterface;
use App\SiteSettings;
use App\User;
use App\UserTeam;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Spark\Repositories\TokenRepository;
use Laravel\Spark\Spark;
use Laravel\Spark\Team;
use Laravel\Spark\Token;

class SocialiteService implements SocialiteServiceInterface
{
    public function getRedirectUrlByProvider($provider): array
    {
        return [
            'redirectUrl' => Socialite::driver($provider)
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
        ];
    }

    public function loginWithSocialite($provider): array
    {

        $userSocial = Socialite::driver($provider)->stateless()->user();
        if (SocialiteHelper::isSocialPresent($userSocial)) {
            $user = $this->searchUserByEmail($userSocial->email, $provider);

            if ($user) {
                // return SocialiteHelper::compareUserWithSocialite($user, $userSocial)
                // && $user->createToken()->save()
                //     ? $this->prepareSuccessResult($user)
                //     : $this->prepareErrorResult();

                $settings = new SiteSettings();
                return $this->makeAuthenticationCookie([
                    'user' => $user,
                    'redirect_url' => '/#/',
                    'authenticated' => true,
                    'api_token' => $user->getToken->token,
                    'name' => $user->name,
                    'email' => $user->email,
                    'trial_ends_at' => $user->trial_ends_at->format('Y-m-d H:i:s'),
                    'user_id' => $user->id,
                    'site_settings' => $settings->getAllSettings(),
                    'code' => 200,
                ]);

            } else {
                // $user = New User([], $userSocial);
                // return $user->save()
                //     ? $this->prepareSuccessResult($user)
                //     : $this->prepareErrorResult();

                $errorResponse = [
                    'error' => 'Account details do not match, please check username and password!',
                    'code' => 422,
                ];

                \DB::beginTransaction();
                $user = new User([], $userSocial);

                $endresponse = array();
                $user->last_read_announcements_at = Carbon::now();
                $user->trial_ends_at = Carbon::now()->addDays(Spark::trialDays());
                $user->social_type = $provider;
                unset($user->api_token);
                try {
                    $endresponse = $user->save()
                    ? [
                        'user_id' => $user->id,
                        'code' => 200]
                    : $errorResponse;

                    $data = count(Spark::tokensCan()) > 0 ? ['abilities' => $request->abilities] : [];
                    $finaltoken = (new TokenRepository())->createToken(
                        $user, $user->name, $data
                    )->token;

                    //////////////////////////   create team at time of user creation

                    $toinsertspark = array(
                        'name' => $user->name,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'owner_id' => ($user->id > 0) ? $user->id : null,
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
                    //die('success');

                    return $this->makeAuthenticationCookie([
                        'user' => $user,
                        'redirect_url' => '/#/',
                        'token' => $finaltoken,
                        'user_id' => $user->id,
                        'Message' => 'You are successfully signed up please check you email',
                        'status' => 200,
                    ]);

                } catch (\Exception $req) {
                    \DB::rollBack();
                    return $this->makeAuthenticationCookie([
                        'ErrorMessage' => $req,
                        'status' => 401,
                        'redirect_url' => '/#/',
                    ]);
                }

            }
        } else {
            return $this->prepareErrorResult();
        }
    }

    private function makeAuthenticationCookie($result)
    {
        $result['cookie'] = cookie('authentication',
            json_encode($result),
            8000,
            null,
            '.adeogroup.co.uk',
            false,
            false
        );
        return $result;
    }

    private function searchUserByEmail($email, $provider = 'facebook'): ?User
    {
        return User::where('email', $email)->where('social_type', $provider)
            ->first();
    }

    private function prepareErrorResult(): array
    {
        return $this->makeAuthenticationCookie([
            'error' => 'User is unavailable. Try another social account!',
            'redirect' => '/login',
            'redirect_url' => '/#/',
        ]);
    }

    private function prepareSuccessResult(User $user): array
    {
        return $this->makeAuthenticationCookie([
            'api_token' => $user->api_token,
            'user_id' => $user->id,
            'redirect_url' => '/#/',
        ]);
    }
}
