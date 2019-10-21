<?php
namespace App\Services;

use App\Helpers\SendEmailService;
use App\Models\Mailbox;
use App\Models\MailboxUserPermissions;
use App\Models\UserRoles;
use App\Models\UsersEmails;
use App\Models\Invitation;
use App\Models\User;
use App\Models\SparkpostSubAccounts;
use App\Helpers\Helper;
use Response;
use Validator;
use Lang;
use Carbon\Carbon;
use DB;
use App\Models\UserApiTokens;
use App\Models\UserPreferences;

class UsersService
{
    private $accountOwnerRole;

    function __construct()
    {
        $this->accountOwnerRole = config('constants.roles.account_owner');
    }

    /**
     * @return mixed
     */

    public static function getAgentsAndAdmins()
    {
        return User::where('role_id', 3)->orwhere('role_id', 4)->get();
    }

    /**
     * Get all users
     * @param $request
     * @return mixed
     */
    public function getAllUsers($request)
    {
        if ($request->has('mailbox_id') && !empty($request->get('mailbox_id'))) {
            $users = User::with(['preferences'])->get();
            $users->invitedUsers = Invitation::where('verified')->get();
            $users = $users->toArray();
            $available_users = MailboxUserPermissions::where('mailbox_id', $request->get('mailbox_id'))->pluck('user_id');
            if (!empty($available_users)) {
                foreach ($users as $key => $user) {
                    if (!in_array($user['id'], $available_users->toArray())) {
                        unset($users[$key]);
                    }
                }
            }
            $users = array_values($users);
        } elseif ($request->has('role') && !empty($request->all()['role'])) {
            $role = $request->all()['role'];
            if (is_numeric($role)) {
                $users = User::where('role_id', $role)->get();
            } else {
                switch ($role) {
                    case 'active' :
                        $users['confirmedUsers'] = User::with(['preferences', 'mailbox_names'])->get()->toArray();
                        foreach ($users['confirmedUsers'] as $key => $confirmed_user) {
                            $names = [];
                            foreach ($confirmed_user['mailbox_names'] as $mailbox_name) {
                                array_push($names, $mailbox_name['mailbox']['name']);
                            }
                            unset($users['confirmedUsers'][$key]['mailbox_names']);
                            $users['confirmedUsers'][$key]['mailbox_names'] = $names;
                        }
                        $users['invitedUsers'] = Invitation::where('verified', '!=', 1)->get()->toArray();
                        foreach ($users['invitedUsers'] as $key => $user) {
                            $mailboxes = json_decode($user['mailbox_id'], true);
                            $users['invitedUsers'][$key]['mailbox_names'] = Mailbox::whereIn('id', $mailboxes)->pluck('name');
                        }
                        break;
                    case 'inactive' :
                        $users = User::onlyTrashed()->get();
                        break;
                    default :
                        $users = User::with(['preferences'])->get();
                }
            }
        } else {
            $users = User::get();
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => $users
        ]), 200);
    }

    /**
     * Get invited users of company
     *
     * @return \Illuminate\Http\Response
     */
    public function getInvitedUsers()
    {
        $invitedUsers = Invitation::where('verified', '!=', 1)->get();
        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($invitedUsers->toArray()) ? $invitedUsers->toArray() : []
        ]), 200);
    }

    /**
     * Send user invitation
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendInvitation($request)
    {
        $subDomain = Helper::setSubDomain();
        $apiToken = $request->header('Authorization');

        $sender = Helper::getUser($apiToken);
        $resentData = $request->request->get('resentData');
        if (!empty($resentData)) {
            Helper::changeDataBaseConnection(Helper::$subDomain);
            $data = Invitation::find($resentData['userId'])->toArray();
            $data['to_email'] = $data['email'];
            $data['mailboxes'] = json_decode($data['mailbox_id'], true);
            $invitedUser['email'] = $data['email'];
        } else {
            $data = $request->all();

            // check in users table
            $invitedUser['email'] = $data['to_email'];
            $validationRules = ['email' => 'unique:users'];
            $validator = Validator::make($invitedUser, $validationRules);

            //if data didn't pass validation sends validation errors
            if ($validator->fails()) {
                return Response::make(json_encode([
                    'success' => false,
                    'errors' => json_encode($validator->errors(), true)
                ]), 422);
            }

            // check in invited users table
            $invitedUser['email'] = $data['to_email'];
            $validationRules = ['email' => 'required|email|max:255'];

            $validator = Validator::make($invitedUser, $validationRules);
            //if data didn't pass validation sends validation errors
            if ($validator->fails()) {
                return Response::make(json_encode([
                    'success' => false,
                    'errors' => json_encode($validator->errors(), true)
                ]), 422);
            }
        }

        if (empty($data['mailboxes'])) {
            return Helper::send_error_response('mailbox', Lang::get('mailbox.one_mailbox_required'), 422);
        }

        DB::setDefaultConnection('mysql');
        $confirmationToken = str_random(32);

        $url = [
            'confirmation_token' => $confirmationToken,
            'user_status' => $data['role_id'],
            'user_email' => $data['to_email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name']
        ];

        $mailboxes = $data['mailboxes'];
        $urlMailboxData = [];

        foreach ($mailboxes as $id) {
            $urlMailboxData[] = $id;
        }
        $url['mailbox_url'] = $urlMailboxData;
        $source = 'https://' . $subDomain . env('PAGE_URL') . '/agent-login/' . $confirmationToken; // live

        $email_html = view('templates.invitation', [
            'to_email' => $data['to_email'],
            'from_name' => $sender['first_name'],
            'source' => $source
        ])->render();

        $email_html = str_replace("\n", "", $email_html);
        Helper::changeDataBaseConnection(Helper::$subDomain);

        $options = [
            'toEmail' => $data['to_email'],
            'toName' => $data['first_name'] . ' ' . $data['last_name'],
            'fromEmail' => 'info@' . $subDomain . env('PAGE_URL'),
            'fromName' => $sender['first_name'],
            'subject' => (!empty($data['invitation'])) ? $data['invitation'] : Lang::get('users.team_invitation') . $data['first_name'],
            'commentText' => $email_html,
            'messageId' => '<' . md5(rand(999, 9999999)) . '@' . Helper::$subDomain . env('PAGE_URL') . '>',
            'reply_to' => 'info@' . $subDomain . env('PAGE_URL'),
            'track' => 1
        ];

        SendEmailService::sendEmail($options, false);

        // check this email has already been sent
        $email = Invitation::where('email', $invitedUser['email'])->first();
        if (!empty($email)) {
            Invitation::where('email', $invitedUser['email'])->update(['verified' => $confirmationToken]);
        }
        $inviteData = [
            'sender_id' => $sender['id'],
            'email' => $data['to_email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'verified' => $confirmationToken,
            'role_id' => $data['role_id']
        ];
        $inviteData['mailbox_id'] = json_encode($urlMailboxData, true);
        Invitation::create($inviteData);

        return Response::make(json_encode([
            'success' => true,
            'source' => $source,
            'data' => json_encode(['invitedUser' => Invitation::orderBy('id', 'desc')->first()])
        ]), 200);
    }

    /**
     * Resend Invitation email
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function resendInvitation($request)
    {
        $sender = Helper::$user;
        if ($sender['role_id'] != $this->accountOwnerRole) {
            return Helper::send_error_response('wrong_role_id', Lang::get('users.wrong_role_id'), 422);
        }
        $data = $request->all();
        $request->request->add(['resentData' => json_encode(['userId' => $data['user_id']])]);
        $this->sendInvitation($request);

        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Get user invite
     *
     * @param string $token
     *
     * @return \Illuminate\Http\Response
     */
    public function getInvitation($token)
    {
        $subDomain = Helper::setSubDomain();
        Helper::changeDataBaseConnection($subDomain);
        $data = Invitation::where('verified', $token)->first();

        return Response::make(json_encode([
            'success' => true,
            'data' => $data
        ]), 200);
    }

    /**
     * Confirm invite
     *
     * @param  \Illuminate\Http\Request $request
     * @param string $token
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmInvitation($request, $token)
    {
        $subDomain = Helper::setSubDomain();
        $data = $request->all();

        // Form data
        $formData = $data['form_data'];
        $password = $formData['password'];
        $confirmPassword = $formData['password_confirmation'];

        $dataCompany = Helper::getCompanyData($subDomain);
        // change DB connection
        Helper::changeDataBaseConnection($subDomain);

        $invitedUser = Invitation::where('verified', $token)->first();
        if (empty($invitedUser)) {
            return Helper::send_error_response('invalid_token', Lang::get('users.invalid_token'), 422);
        }

        // Url params
        $confirmationToken = $invitedUser->verified;
        $userStatus = $invitedUser->role_id;
        $userEmail = $invitedUser->email;
        $firstName = $invitedUser->first_name;
        $lastName = $invitedUser->last_name;
        $mailboxes = json_decode($invitedUser->mailbox_id, true);

        // User roles
        $userRolesData = UserRoles::where('id', $userStatus)->first();
        $displayUserRole = $userRolesData->display_name;

        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company_url' => $subDomain,
            'email' => $userEmail,
            'role_id' => $userStatus,
            'display_user_role' => $displayUserRole,
            'password' => $password,
            'password_confirmation' => $confirmPassword
        ];

        $validationRules = [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'role_id' => 'required',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ];

        $validator = Validator::make($userData, $validationRules);

        //if data didn't pass validation sends validation errors
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]), 422);
        }

        $userData['password'] = bcrypt($password);
        $invite = Invitation::where('email', $userEmail)->first();

        // check token expired
        $this->checkTokenExpied($invite, $userEmail, $confirmationToken);
        $user = User::create($userData);

        // add user new api token
        $userApiTokens = UserApiTokens::create([
            'user_id' => $user->id,
            'api_token' => Helper::generateUserApiToken(),
            'expires_at' => Carbon::now()->addHours(24)->toDateTimeString()
        ]);
        $user_api_token = $userApiTokens->api_token;

        // User notification conditions
        Helper::addUserDefaultNotifications($user->id);

        // User default preferences
        UserPreferences::create([
            'user_id' => $user->id,
            'answer' => 'available',
            'assign_after_reply' => 0,
            'take_back_after_reply' => 0,
            'assign_after_note' => 0,
            'take_back_after_note' => 0,
            'take_back_after_update' => 0,
            'delay_sending' => 0
        ]);

        // user notifications
        Helper::changeDataBaseConnection(env('DB_DATABASE'));
        UsersEmails::create([
            'company_id' => $dataCompany->id,
            'email' => $userEmail,
            'role_id' => $userStatus
        ]);

        Helper::changeDataBaseConnection($subDomain);

        if (empty($mailboxes)) {
            return Helper::send_error_response('empty_mailbox', Lang::get('users.empty_mailbox'), 422);
        }

        $mailboxUserPermissionsData = [];
        foreach ($mailboxes as $mailboxId) {
            array_push($mailboxUserPermissionsData, [
                'user_id' => $user->id,
                'mailbox_id' => $mailboxId
            ]);
        }
        DB::table('mailbox_user_permissions')->insert($mailboxUserPermissionsData);

        // user profile default image
        Helper::generateUserProfileDefaultImage($user->id, $subDomain);
        $user = User::where('id', $user->id)->first();
        Invitation::where('email', $userEmail)->update(['verified' => '1']);

        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($user) ? $user : []
        ]), 200, ['Authorization' => $user_api_token]);
    }

    /**
     * Get mentioned users
     *
     * @param int $mailboxId
     * @param  string $keyword
     *
     * @return \Illuminate\Http\Response
     */
    public function getMentionedUsers($mailboxId, $keyword = null)
    {
        $user_id = Helper::$user['id'];
        $mailbox_users = MailboxUserPermissions::where('mailbox_id', $mailboxId)->where('user_id', '!=', $user_id)->pluck('user_id');
        $users = User::whereIn('id', $mailbox_users)->get();

        if (!empty($keyword)) {
            $users = User::whereIn('id', $mailbox_users)->where('first_name', 'like', $keyword . '%')
                ->orWhere('last_name', 'like', $keyword . '%')
                ->get();
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($users) ? $users : []
        ]), 200);
    }

    private function checkTokenExpied($invite, $userEmail, $confirmationToken)
    {
        if (Carbon::now()->subMonth(1) > Carbon::parse($invite['created_at'])) {
            return Helper::send_error_response('expired_token', Lang::get('users.expired_token'), 422);
        }

        if (empty($invite)) {
            return Helper::send_error_response('invalid_token', Lang::get('users.invalid_token'), 422);
        }

        $checkInvitation = Invitation::where('email', $userEmail)->where('verified', '1')->first();
        if (!empty($checkInvitation)) {
            return Helper::send_error_response('already_confirmed', Lang::get('users.already_confirmed'), 422);
        }

        $confirm = (($invite->verified === $confirmationToken)) ? true : false;
        if ($confirm !== true) {
            return Helper::send_error_response('token', Lang::get('users.one_mailbox_required'), 422);
        }
    }

    public static function getWorkspacesByEmail($email)
    {
        $companies = UsersEmails::where('email', $email)->with('company')->get()->toArray();
        if (!empty($companies)){
            SendEmailService::sendWorkspaces($companies,$email);
        }
        return $companies;

    }
}