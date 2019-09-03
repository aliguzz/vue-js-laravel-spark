<?php

namespace App\Http\Controllers;

use Laravel\Spark\Spark;
use App\Http\Requests\RegistrationRequest;
use App\Services\Contracts\RegisterServiceInterface;
use Laravel\Spark\Events\Auth\UserRegistered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Spark\Contracts\Interactions\Auth\Register;
use Laravel\Spark\Contracts\Http\Requests\Auth\RegisterRequest;
use App\User;
use Validator;

class RegisterController extends Controller
{
    private $registerService;

    public function __construct(RegisterServiceInterface $registerService)
    {
        $this->registerService = $registerService;
    }

    public function register(RegistrationRequest $request)
    {
        $result = $this->registerService->register($request);
        if($result['status'] == 200){
            if ($result['user'] instanceof MustVerifyEmail && ! $result['user']->hasVerifiedEmail()) {
                $result['user']->sendEmailVerificationNotification();
            }
            unset($result['user']);
        }
        return response()->json($result, $result['status']);
        
    }
}
