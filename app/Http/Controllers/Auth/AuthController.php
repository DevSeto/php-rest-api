<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Cloudflare;
use App\Helpers\Crypto;
use App\Helpers\SendEmailService;
use App\Helpers\SendgridApi;
use App\Helpers\SparkPostApi;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\AddedMailboxes;
use App\Models\CompanySetting;
use App\Models\MailboxUserPermissions;
use App\Models\UsersRegistrationSteps;
use App\Services\MailboxService;
use App\Services\UsersService;
use Log;
use App\Models\Subdomains;
use App\Models\UserApiTokens;
use App\Models\UsersEmails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Response;
use App\Jobs\ConfigurateSparkpostAndCloudflare;
use App\Models\User;
use Lang;
use DB;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Exception;


class AuthController extends Controller
{
    /**
     * @param LoginRequest $request
     * @return mixed
     */

    public function login(LoginRequest $request)
    {
        $data = $request->all();
        $data = Helper::trimData($data);

        //checking if isset subdomain and database
        if (Subdomains::where('company_url', $data['company_url'])->first() && Helper::checkIfDatabaseExists($data['company_url'])) {
            Helper::changeDataBaseConnection($data['company_url']);
            $user = User::where('email', $data['email'])->first();

            //checking if isset user with this email
            if ($user) {
                $user->mailbox_id = MailboxUserPermissions::where('user_id', $user->id)->first()->mailbox_id;
                //checking if passwords match
                if (password_verify($data['password'], $user->password)) {
                    //checking if input subdomain is users
                    if ($data['company_url'] == $user->company_url) {
                        $user_api_token = Helper::generateUserApiToken();
                        $expires_at = (isset($data['remember']) && $data['remember'] == 1) ? Carbon::now()->addYear()->toDateTimeString() : Carbon::now()->addHours(24)->toDateTimeString();
                        UserApiTokens::create([
                            'user_id' => $user->id,
                            'api_token' => $user_api_token,
                            'expires_at' => $expires_at
                        ]);

                        //get user available mailboxes
                        $user['mailboxes'] = MailboxUserPermissions::where('user_id', $user->id)->pluck('mailbox_id')->toArray();
                        return Response::make(json_encode([
                            'success' => true,
                            'data' => $user
                        ]), 200, ['Authorization' => $user_api_token]);
                    }
                    return Helper::send_error_response('company_url', Lang::get('auth.company_url'), 422);
                }
                return Helper::send_error_response('password', Lang::get('auth.password'), 422);
            }
            return Helper::send_error_response('email', Lang::get('auth.email'), 422);
        }
        return Helper::send_error_response('company_url', Lang::get('auth.subdomain'), 422);
    }

    public function regStep1(Request $request)
    {
        $data = $request->all();
        $data = Helper::trimData($data);
        $validationRules = ['email' => 'required|email'];
        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]), 422);
        }

        $user = UsersRegistrationSteps::where('email', $data['email'])->first();
        if (empty($user)) {
            $user = UsersRegistrationSteps::create([
                'email' => $data['email'],
                'step' => 1
            ]);
        }

        $result = UsersRegistrationSteps::where('id', $user['id'])->first();

        return Response::make(json_encode([
            'success' => true,
            'data' => $result
        ]), 200);
    }

    public function regStep2(Request $request)
    {
        $data = $request->all();
        $data = Helper::trimData($data);
        $validationRules = [
            'email' => 'required|email',
            'first_name' => 'required|alpha|max:20',
            'last_name' => 'required|alpha|max:30',
            'company_name' => 'required|alpha_num|max:30',
            'company_url' => 'required|alpha_num|max:30|unique:subdomains',
            'password' => 'required|min:6'
        ];
        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]), 422);
        }

        $user = UsersRegistrationSteps::where('email', $data['email'])->first();
        if (!empty($user)) {
            UsersRegistrationSteps::where('email', $data['email'])->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'subdomain' => $data['company_url'],
                'company_name' => $data['company_name'],
                'password' => $data['password'],
                'step' => 2
            ]);
            $user = UsersRegistrationSteps::where('email', $data['email'])->first();

        } else {
            $user = UsersRegistrationSteps::create([
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'subdomain' => $data['company_url'],
                'company_name' => $data['company_name'],
                'password' => $data['password'],
                'step' => 2
            ]);
        }

        $result = UsersRegistrationSteps::where('id', $user['id'])->first();

        return Response::make(json_encode([
            'success' => true,
            'data' => $result
        ]), 200);

    }

//    public function regStep3(Request $request)
//    {
//        $data = $request->all();
//        $data = Helper::trimData($data);
//        $validationRules = [
//            'email' => 'required|email',
//            'company_url' => 'required|alpha_num|max:30|unique:subdomains',
//        ];
//        $validator = Validator::make($data, $validationRules);
//        if ($validator->fails()) {
//            return Response::make(json_encode([
//                'success' => false,
//                'errors' => $validator->errors()
//            ]), 422);
//        }
//
//        $data['company_url'] = strtolower($data['company_url']);
//
//        $user = UsersRegistrationSteps::where('email', $data['email'])->first();
//        if (!empty($user)) {
//            UsersRegistrationSteps::where('email', $data['email'])->update([
//                'subdomain' => $data['company_url'],
//                'step' => 3,
//                'mailbox_forwarding' => 'forward_' . md5(str_random(45)) . '_1@' . $data['company_url'] . env('PAGE_URL')
//            ]);
//
//            $result = UsersRegistrationSteps::where('id', $user['id'])->first();
//
//            return Response::make(json_encode([
//                'success' => true,
//                'data' => $result
//            ]), 200);
//        }
//    }

    public function regStep3(Request $request){
        $data = $request->all();
        $data = Helper::trimData($data);
        if (isset($data['demo']) && !empty($data['demo'])){
            $validationRules = [
                'email' => 'required|email',
                'demo_mailbox_name' => 'required|alpha_num|max:30',
            ];
            $validator = Validator::make($data, $validationRules);
            if ($validator->fails()) {
                return Response::make(json_encode([
                    'success' => false,
                    'errors' => $validator->errors()
                ]), 422);
            }

            UsersRegistrationSteps::where('email', $data['email'])->update([
                'demo_mailbox_name' => $data['demo_mailbox_name'],
            ]);

            $result = UsersRegistrationSteps::where('email', $data['email'])->first();
            return Response::make(json_encode([
                'success' => true,
                'data' => $result
            ]), 200);

        } else {
            $data['domain'] = explode('@', $data['mailbox_email'])[1];
            $validationRules = [
                'email' => 'required|email',
                'mailbox_name' => 'required|alpha_num|max:30',
                'mailbox_email' => 'required|email|max:30',
                'domain' => 'required|max:50|unique:added_mailboxes',
            ];
            $validator = Validator::make($data, $validationRules);
            if ($validator->fails()) {
                return Response::make(json_encode([
                    'success' => false,
                    'errors' => $validator->errors()
                ]), 422);
            }
            $user = UsersRegistrationSteps::where('email', $data['email'])->first();

            if (!empty($user)){
                UsersRegistrationSteps::where('email', $data['email'])->update([
                    'mailbox_name' => $data['mailbox_name'],
                    'mailbox_email' => $data['mailbox_email'],
                    'step' => 4,
                ]);

                $result = UsersRegistrationSteps::where('id', $user['id'])->first();
                return Response::make(json_encode([
                    'success' => true,
                    'data' => $result
                ]), 200);
            }
        }

    }
//
//    public function regStep5(Request $request){
//        $data = $request->all();
//        $data = Helper::trimData($data);
//        $data['domain'] = explode('@', $data['mailbox_email'])[1];
//        $validationRules = [
//            'email' => 'required|email',
//            'mailbox_name' => 'required|alpha_num|max:30',
//            'mailbox_email' => 'required|email|max:30',
//            'domain' => 'required|max:50|unique:added_mailboxes',
//        ];
//        $validator = Validator::make($data, $validationRules);
//        if ($validator->fails()) {
//            return Response::make(json_encode([
//                'success' => false,
//                'errors' => $validator->errors()
//            ]), 422);
//        }
//        $user = UsersRegistrationSteps::where('email', $data['email'])->first();
//
//        if (!empty($user)){
//            UsersRegistrationSteps::where('email', $data['email'])->update([
//                'mailbox_name' => $data['mailbox_name'],
//                'mailbox_email' => $data['mailbox_email'],
//                'step' => 5,
//            ]);
//
//            $result = UsersRegistrationSteps::where('id', $user['id'])->first();
//
//            return Response::make(json_encode([
//                'success' => true,
//                'data' => $result
//            ]), 200);
//        }
//
//
//    }

    public function registerFinalStep(Request $request){

        $data = $request->all();
        $data = Helper::trimData($data);
        $data = UsersRegistrationSteps::where('email', $data['email'])->first();
        if (!empty($data)){
            $data['company_url'] = $data['subdomain'];

            //add to subdomains table
            $company = Subdomains::create(['company_url' => $data['company_url']]);

            //create user company relationship
            if (!empty($company->id)) {
                UsersEmails::create([
                    'company_id' => $company->id,
                    'email' => $data['email'],
                    'role_id' => 2
                ]);
            }
            //creating new database by subdomain name
            //changing db connections to new created db
            //migrating ang seeding tables into new created db
            //creating user and sending user daa back as response
            if (Helper::createNewDataBase($data['company_url'])) {
                Helper::changeDataBaseConnection($data['company_url']);
                Helper::$subDomain = $data['company_url'];
                Artisan::call('migrate', ['--path' => '/database/migrations/company_db', '--force' => true]);

                $user = User::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'company_url' => $data['company_url'],
                    'email' => $data['email'],
                    'role_id' => 2,
                    'display_user_role' => 'Account Owner',
                    'password' => bcrypt($data['password'])
                ]);

                Helper::setUser($user->toArray());

                Artisan::call('db:seed', ['--class' => 'DatabaseSeederCompany', '--force' => true]);

                $company = CompanySetting::create([
                    'subdomain' => Helper::$subDomain,
                    'company_name' => $data['company_name'],
                ]);

                // generate api_token and add into user_api_tokens table
                $user_new_api_token = UserApiTokens::create([
                    'user_id' => $user->id,
                    'api_token' => Helper::generateUserApiToken(),
                    'expires_at' => Carbon::now()->addHours(24)->toDateTimeString()
                ]);

                //create default mailbox
                $mailboxes =  [];
                $mailboxService = new MailboxService();
                $defaultAvailableHours = config('constants.mailbox_available_hours');

                if (!empty($data['demo_mailbox_name'])){
                    $mailboxData = [
                        "name" => $data['demo_mailbox_name'],
                        "creator_user_id" => $user->id,
                        "email" => 'info@' . $data['company_url'] . env('PAGE_URL'),
                        "default" => 1,
                        "from_name" => "0",
                        "signature" => "",
                        "auto_reply" => "3",
                        "auto_reply_subject" => "",
                        "auto_reply_body" => "",
                        "auto_bcc" => "0",
                        "dns_name" => "",
                        "dns_value" => "",
                        "dns_verified" => "1",
                        "forward_address" => '',
                        "users" => 0
                    ];

                    $default_mailbox = $mailboxService->createMailbox($mailboxData, $data['company_url']);

                    $mailbox['auto_reply_timeline'] = $mailboxService->setMailboxAvailableHours($default_mailbox['id'], $defaultAvailableHours);

                    $domain = '';
                    array_push($mailboxes,$default_mailbox);
                } elseif (!empty($data['mailbox_name']) && !empty($data['mailbox_email'])){
                    $domain = explode('@', $data['mailbox_email'])[1];

                    $anotherMailboxData = [
                        "name" => $data['mailbox_name'],
                        "creator_user_id" => $user->id,
                        "email" => $data['mailbox_email'],
                        "default" => 1,
                        "from_name" => "0",
                        "signature" => "",
                        "auto_reply" => "3",
                        "auto_reply_subject" => "",
                        "auto_reply_body" => "",
                        "auto_bcc" => "0",
                        "dns_name" => "",
                        "dns_value" => "",
                        "dns_verified" => "0",
                        "forward_address" => $data['mailbox_forwarding'],
                        "users" => 0
                    ];

                    $anotherMailbox = $mailboxService->createMailbox($anotherMailboxData, $data['company_url']);
                    $anotherMailbox['auto_reply_timeline'] = $mailboxService->setMailboxAvailableHours($anotherMailbox['id'], $defaultAvailableHours);
                    array_push($mailboxes,$anotherMailbox);
                }

                // User notification conditions
                //hardcoding ! becouse it is a first user of the company and for sure his id is 1
                Helper::addUserDefaultNotifications($user->id);

                Helper::generateUserProfileDefaultImage($user->id, $data['company_url']);
                $user_api_token = $user_new_api_token->api_token;
                $user['mailbox_id'] = (isset($default_mailbox)) ? $default_mailbox['id'] : $anotherMailbox['id'];

                SendEmailService::sendWelcomeEmail($data['email'], $data['first_name'] . ' ' . $data['last_name'], $data['company_url']);

                DB::setDefaultConnection('mysql');

                if (!empty($domain)){
                    AddedMailboxes::create([
                        'domain' => $domain,
                        'company_id' => $company->id
                    ]);
                }

                try{
                    Cloudflare::addSubdomain($data['company_url'], 'A');
                    Cloudflare::addSubdomain($data['company_url'], 'MX', 'mx.sendgrid.net', null, 10);
                }catch (Exception $e){

                }

                SendgridApi::createInboundParse($data['company_url']);

                UsersRegistrationSteps::where('email',$data['email'])->delete();
//            $job = (new ConfigurateSparkpostAndCloudflare($user->toArray()))->onQueue('configure');
//            $this->dispatch($job);
                $user['mailboxes'] = $mailboxes;
                return Response::make(json_encode([
                    'success' => true,
                    'data' => $user
                ]), 200, ['Authorization' => $user_api_token]);
            }
            return Helper::send_error_response('database', Lang::get('auth.database'), 422);

        }
    }


        /**
     * @param RegisterRequest $request
     * @return mixed
     */
//    public function register(RegisterRequest $request)
//    {
//        $data = $request->all();
//
////        $a = SendgridApi::createInboundParse($data['company_url']);
////        dd();
//        $data = Helper::trimData($data);
////        dd($data);
//        //inserting subdomain in the main subdomain table
//        $data['company_url'] = strtolower($data['company_url']);
//        $company = Subdomains::create(['company_url' => $data['company_url']]);
//
//        if (!empty($company->id)) {
//            UsersEmails::create([
//                'company_id' => $company->id,
//                'email' => $data['email'],
//                'role_id' => 2
//            ]);
//        }
//
//        //creating new database by subdomain name
//        //changing db connections to new created db
//        //migrating ang seeding tables into new created db
//        //creating user and sending user daa back as response
//        if (Helper::createNewDataBase($data['company_url'])) {
//            Helper::changeDataBaseConnection($data['company_url']);
//            Artisan::call('migrate', ['--path' => '/database/migrations/company_db', '--force' => true]);
//
//            $user = User::create([
//                'first_name' => $data['first_name'],
//                'last_name' => $data['last_name'],
//                'company_url' => $data['company_url'],
//                'email' => $data['email'],
//                'role_id' => 2,
//                'display_user_role' => 'Account Owner',
//                'password' => bcrypt($data['password'])
//            ]);
//
//            Helper::setUser($user->toArray());
//
//            Artisan::call('db:seed', ['--class' => 'DatabaseSeederCompany', '--force' => true]);
//
//            // generate api_token and add into user_api_tokens table
//            $user_new_api_token = UserApiTokens::create([
//                'user_id' => $user->id,
//                'api_token' => Helper::generateUserApiToken(),
//                'expires_at' => Carbon::now()->addHours(24)->toDateTimeString()
//            ]);
//
//            //create default mailbox
//
//            $mailboxData = [
//                "name" => "index",
//                "creator_user_id" => $user->id,
//                "email" => 'info@' . $data['company_url'] . env('PAGE_URL'),
//                "default" => 1,
//                "from_name" => "0",
//                "signature" => "",
//                "auto_reply" => "3",
//                "auto_reply_subject" => "",
//                "auto_reply_body" => "",
//                "auto_bcc" => "0",
//                "dns_name" => "",
//                "dns_value" => "",
//                "dns_verified" => "1",
//                "forward_address" => '',
//                "users" => 0
//            ];
//
//            $mailboxService = new MailboxService();
//            $default_mailbox = $mailboxService->createMailbox($mailboxData, $data['company_url']);
//
//            $defaultAvailableHours = config('constants.mailbox_available_hours');
//            $mailbox['auto_reply_timeline'] = $mailboxService->setMailboxAvailableHours($default_mailbox['id'], $defaultAvailableHours);
//
//            // User notification conditions
//            //hardcoding ! becouse it is a first user of the company and for sure his id is 1
//            Helper::addUserDefaultNotifications(1);
//
//            Helper::generateUserProfileDefaultImage($user->id, $data['company_url']);
//            $user_api_token = $user_new_api_token->api_token;
//            $user['mailbox_id'] = $default_mailbox['id'];
//
//            SendEmailService::sendWelcomeEmail($data['email'], $data['first_name'] . ' ' . $data['last_name'], $data['company_url']);
//
//            DB::setDefaultConnection('mysql');
//
//            try{
//                Cloudflare::addSubdomain($data['company_url'], 'A');
//                Cloudflare::addSubdomain($data['company_url'], 'MX', 'mx.sendgrid.net', null, 10);
//            }catch (Exception $e){
//
//            }
//
//            SendgridApi::createInboundParse($data['company_url']);
////            $job = (new ConfigurateSparkpostAndCloudflare($user->toArray()))->onQueue('configure');
////            $this->dispatch($job);
//            return Response::make(json_encode([
//                'success' => true,
//                'data' => $user
//            ]), 200, ['Authorization' => $user_api_token]);
//        }
//        return Helper::send_error_response('database', Lang::get('auth.database'), 422);
//    }

    /**
     * @param $sub_domain
     *
     * @return mixed
     */
    public function checkIfSubDomainExists($sub_domain)
    {
        if (Subdomains::where('company_url', $sub_domain)->first()) {
            return Response::make(json_encode(['success' => true]), 200);
        }
        return Response::make(json_encode(['success' => false]), 200);
    }

    /**
     * updating user api token by following params
     * user_id,company_url and user current api token is coming by request header
     * @param Request $request
     * @return mixed
     */
    public function updateUserApiToken(Request $request)
    {
        //checking if isset Authorization header
        if ($request->hasHeader('Authorization')) {
            $data = json_decode(Crypto::decrypt($request->getContent()), true);
            $data['company_url'] = Helper::getSubDomain();
            $validation_rules = ['user_id' => 'required'];

            //validating request
            $validator = Validator::make($data, $validation_rules);
            //sending back validation errors if they occurred
            if ($validator->fails()) {
                return Response::make(json_encode([
                    'success' => false,
                    'errors' => $validator->errors()
                ]), 422);
            }

            //checking is there db and sub_domain equal to request company_url
            if (Subdomains::where('company_url', $data['company_url'])->first() && Helper::checkIfDatabaseExists($data['company_url'])) {
                //changing db connections
                Helper::changeDataBaseConnection($data['company_url']);

                //getting user if exist
                $user = UserApiTokens::where('user_id', $data['user_id'])->where('api_token', $request->header('Authorization'))->first();
                if (isset($user)) {
                    //update user api token
                    UserApiTokens::where('user_id', $user->user_id)->where('api_token', $user->api_token)->update([
                        'expires_at' => Carbon::now()->addHours(24)->toDateTimeString()
                    ]);
                    return Response::make(json_encode([
                        'success' => true,
                        'data' => $user
                    ]), 200, ['Authorization' => $request->header('Authorization')]);
                }
                return Helper::send_error_response('inputs', Lang::get('auth.wrong_token'), 422);
            }
            return Helper::send_error_response('company_url', Lang::get('auth.company_url'), 422);
        }
        return Helper::send_error_response('authorization', Lang::get('auth.authorization-token'), 422);
    }

    /**
     * Get users by email on all companies
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function findUserByEmail(Request $request)
    {
        $data = $request->all();
        $email = $data['email'];
        $users = UsersEmails::with(['company'])->where('email', $email)->get();

        return Response::make(json_encode([
            'success' => true,
            'data' => (!empty($users)) ? $users : []
        ]), 200);
    }

    /**
     * workspaces
     * @param $email
     * @return mixed
     */

    public function getWorkspace($email){
        $workspaces = UsersService::getWorkspacesByEmail($email);
        $message =  Lang::get('auth.workspaces_sent');
        if (empty($workspaces)){
            $message =  Lang::get('auth.workspaces_empty');
        }
        return Response::make(json_encode([
            'success' => true,
            'data' => [
                'message' => $message,
                'count' => count($workspaces)
            ]
        ]), 200);
    }

    /**
     * @param $subdomain
     * @return mixed
     */

    public function getSubdomain($subdomain,Request $request){
        $login = false;
        if ($request->has('login')){
            $login = true;
        }

        if (!empty(Subdomains::where('company_url',$subdomain)->first())){
            if (!$login)
            return Helper::send_error_response('company_url', Lang::get('auth.subdomain_exist'), 422);
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => []
        ]), 200);
    }
}
