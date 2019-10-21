<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Crypto;
use Validator;
use Response;
use App\Models\Subdomains;
use App\Helpers\Helper;
use App\Models\User;
use Lang;

class Agent
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
        $data = \GuzzleHttp\json_decode(Crypto::decrypt($request->getContent()), true);
        $data['authorization'] = $request->header('Authorization');
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
            $user = User::where('api_token', $data['authorization'])->first();
            return ($user->role_id == 4) ? $next($request) : Helper::send_error_response('company_url', Lang::get('auth.permission'), 422);
        } else {
            return Helper::send_error_response('company_url', Lang::get('auth.company_url'), 422);
        }
    }
}
