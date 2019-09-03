<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\Contracts\LoginServiceInterface;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Spark\Spark;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Spark\Contracts\Interactions\Settings\Security\VerifyTwoFactorAuthToken as Verify;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        AuthenticatesUsers::login as traitLogin;
    }
    private $loginService;

    public function __construct(LoginServiceInterface $loginService)
    {
        $this->loginService = $loginService;
        $this->middleware('guest')->except('logout');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginService->login($request);
        return response()->json($result, $result['code']);
    }

    public function logout(Request $request): JsonResponse
    {
        $result = $this->loginService->logout($request);
        return response()->json($result, $result['code']);
    }
}
