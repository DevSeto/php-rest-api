<?php

namespace App\Http\Middleware;

use App\Models\UserApiTokens;
use Closure;
use Validator;
use Response;
use App\Helpers\Helper;
use App\Models\User;
use Lang;
use Route;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $userData = UserApiTokens::where('api_token', $request->header('Authorization'))->first();
        $user = User::find($userData->user_id);

        if ($user->role_id <= 3) {
            return $next($request);
        } else {
            return Helper::send_error_response('company_url', Lang::get('auth.permission'), 422);
        }
    }
}
