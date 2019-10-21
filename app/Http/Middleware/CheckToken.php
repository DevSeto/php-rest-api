<?php

namespace App\Http\Middleware;

use App\Models\UserApiTokens;
use Closure;
use App\Helpers\Helper;
use Lang;
use App\Helpers\Crypto;
use Validator;
use Response;
use App\Models\User;
use App\Models\Subdomains;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

class CheckToken
{

    public function handle($request, Closure $next)
    {
        $data = $request->all();
        $request->request->add($data);
        if ($request->hasHeader('Authorization')) {
            $data['authorization'] = $request->header('Authorization');
            $data['url'] = Helper::setSubDomain();
            $validationRules = [
                'url' => 'required|min:3|',
                'authorization' => 'required'
            ];

            $validator = Validator::make($data, $validationRules);

            //if data didn't pass validation sends validation errors
            if ($validator->fails()) {
                return Response::make(json_encode([
                    'success' => false,
                    'errors' => $validator->errors()
                ]), 422);
            }

            if (Subdomains::where('company_url', $data['url'])->first() && Helper::checkIfDatabaseExists($data['url'])) {
                //changing db connections

                Helper::changeDataBaseConnection($data['url']);
                //getting user if exist

                // Check permission of request
                $api_token = UserApiTokens::where('api_token', $data['authorization'])->first();
                if ($api_token) {
                    if ($api_token->expires_at > date("Y-m-d h:i:s", time())) {
                        UserApiTokens::where('api_token', $api_token->api_token)->update([
                            'expires_at' => Carbon::now()->addHours(24)->toDateTimeString()
                        ]);
                        $user = User::where('id', $api_token->user_id)->first();
                        Helper::setUser($user->toArray());
                    } else {
                        return Helper::send_error_response('token', Lang::get('auth.expired_time'), 482);
                    }
                } else {
                    return Helper::send_error_response('token', Lang::get('auth.wrong_token'), 482);
                }

                // ToDo User Permissions
//                $checkUserPermission = Permission::checkUserPermissions($user->toArray(), $requestUrl, $requestType );
//
//                if(!$checkUserPermission){
//                    return Helper::send_error_response('permission',Lang::get('auth.permission'),422);
//                } else {
//                    if($user){
//                        return $next($request);
//                    }else{
//                        return Helper::send_error_response('company_url',Lang::get('auth.authorization'),422);
//                    }
//                }

                if ($user) {
                    return $next($request);
                } else {
                    return Helper::send_error_response('company_url', Lang::get('auth.authorization'), 482);
                }
            } else {
                return Helper::send_error_response('company_url', Lang::get('auth.company_url'), 466);
            }
        } else {
            return Helper::send_error_response('authorization', Lang::get('auth.authorization-token'), 482);
        }
    }
}
