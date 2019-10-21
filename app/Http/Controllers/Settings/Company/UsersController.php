<?php

namespace App\Http\Controllers\Settings\Company;

use App\Helpers\CustomersHelper;
use App\Models\Tickets;
use App\Models\UserRoles;
use App\Services\UsersService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Helper;
use Response;
use Validator;
use App\Http\Controllers\Controller;
use Lang;
use DB;


class UsersController extends Controller
{
    protected $subDomain = '';
    protected $companyData;
    protected $usersService;

    function __construct(UsersService $usersService)
    {
        $this->middleware('check_token')->except(['confirmInvitation', 'getInvitation']);
        $this->middleware('admin')->only('sendInvitation');
        Helper::$subDomain = Helper::getSubDomain();
        $this->usersService = $usersService;
    }

    /**
     * Get all users
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->usersService->getAllUsers($request);
    }

    /**
     * Get invited users of company
     *
     * @return \Illuminate\Http\Response
     */
    public function invitedUsers()
    {
        return $this->usersService->getInvitedUsers();
    }

    /**
     * Send user invitation
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendInvitation(Request $request)
    {
        return $this->usersService->sendInvitation($request);
    }

    /**
     * Resend Invitation email
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function resendInvitation(Request $request)
    {
        return $this->usersService->resendInvitation($request);
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
        return $this->usersService->getInvitation($token);
    }

    /**
     * Confirm invite
     *
     * @param  \Illuminate\Http\Request $request
     * @param string $token
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmInvitation(Request $request, $token)
    {
        return $this->usersService->confirmInvitation($request, $token);
    }

    /**
     *
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Get user.
     *
     * @param  int $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function show($userId)
    {
        $user = User::find($userId);
        if (!empty($user)) {
            return Response::make(json_encode([
                'success' => true,
                'data' => !empty($user) ? $user : []
            ]), 200);
        }
        return Helper::send_error_response('wrong_user_id', Lang::get('users.wrong_id'), 422);
    }

    /**
     * Update user status.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId)
    {
        $user = User::find($userId);
        if (empty($user)) {
            return Helper::send_error_response('wrong_user_id', Lang::get('users.wrong_id'), 422);
        }
        $roleId = $request->get('role_id');
        $roles = UserRoles::where('id', $roleId)->first();

        User::find($userId)->update([
            'role_id' => $roles->id,
            'display_user_role' => $roles->display_name
        ]);
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * To archive user.
     *
     * @param  int $userId
     *
     * @return \Illuminate\Http\Response
     */

    public function destroy($userId)
    {
        $user = User::find($userId);
        if (empty($user)) {
            return Helper::send_error_response('wrong_user_id', Lang::get('users.wrong_id'), 422);
        }
        $user->delete();
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Get user`s assigned tickets
     * @param integer $userId
     * @return \Illuminate\Http\Response
     */
    function getUserAssignedTickets($userId)
    {
        $tickets = Tickets::where('assign_agent_id', $userId)->get();
        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($tickets) ? $tickets : []
        ]), 200);
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
        return $this->usersService->getMentionedUsers($mailboxId, $keyword);
    }

    /**
     * get customer details by email
     * @param $email
     * @return mixed
     */
    public function getCustomerData($email)
    {
        $customer = CustomersHelper::getCustomerByEmail($email);
        return Response::make(json_encode([
            'success' => true,
            'data' => $customer
        ]), 200);
    }
}
