<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use App\Services\Contracts\LoginServiceInterface;
use App\SiteSettings;
use App\User;
use Illuminate\Http\Request;

class LoginService implements LoginServiceInterface
{
    public function login(LoginRequest $request): array
    {
        if (auth()->attempt($this->constructCredentials($request))) {
            $user = auth()->user();
            return $this->prepareSuccessResult($user);
        }
        return $this->prepareErrorResult();
    }

    public function logout(Request $request): array
    {

        return auth()->user()->revokeToken()->save()
        ? ['logged_out' => true, 'code' => 200]
        : ['error' => 'Error occurs', 'code' => 409];
    }

    private function constructCredentials($request): array
    {
        return [
            'email' => $request->email,
            'password' => $request->password,
            'social_type' => 'normal',
        ];
    }

    private function prepareErrorResult(): array
    {
        return [
            'error' => 'Account details do not match, please check username and password!',
            'code' => 422,
        ];
    }

    private function prepareSuccessResult(User $user): array
    {
        // print_r($user->trial_ends_at->format('Y-m-d H:i:s'))
        // ;
        // die('nill');
        $settings = new SiteSettings();
        return [
            'authenticated' => true,
            'api_token' => $user->getToken->token,
            'name' => $user->name,
            'email' => $user->email,
            'trial_ends_at' => $user->trial_ends_at->format('Y-m-d H:i:s'),
            'user_id' => $user->id,
            'site_settings' => $settings->getAllSettings(),
            'code' => 200,
        ];
    }
}
