<?php

namespace App\Http\Middleware;
use Illuminate\Contracts\Auth\Guard;
use Closure;
use Auth;
use App\User;
use Entrust;
use App;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth) {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next) {
        $segments = explode('/',$request->fullUrl());
        $uri = $request->route()->uri;
        $excludes = array(
            'admin/login'
        );
        if ($this->auth->guest() && !in_array($uri, $excludes)) {
            if(in_array('admin', $segments))
                return redirect()->to('admin/login');
            else
                return redirect()->to('login');
        }
        return $next($request);
    }
}
