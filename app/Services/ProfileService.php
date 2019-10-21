<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Tickets;
use App\Models\UserApiTokens;
use App\Models\User;
use Response;
use Validator;
use File;
use Lang;

class ProfileService
{

    private $pageURL;
    protected $subDomain;
    protected $accountOwnerRoleId;

    function __construct()
    {
        $this->pageURL = "https://" . env('APP_PROD') . env('PAGE_URL');
        $this->subDomain = Helper::getSubDomain();
        $this->accountOwnerRoleId = config('constants.roles.account_owner');
    }

    /**
     * Check passwords.
     *
     * @param  string $oldPassword
     * @param  string $password
     * @param  string $user
     *
     * @return string $newPassword || false
     */
    public function checkPassword($oldPassword, $password, $user)
    {
        $currentPassword = $user['password'];
        return (password_verify($oldPassword, $currentPassword)) ? bcrypt($password) : false;
    }

    public function resetPassword($request, $user_id)
    {
        //find user by id
        $user = User::where('id', $user_id)->first();
        //if not wrong user id
        if (empty($user)) {
            return Helper::send_error_response('id', Lang::get('user.wrong_id'), 422);
        }

        $data = $request->all();
        $validationRules = [
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed'
        ];

        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]), 422);
        }

        $newPassword = $this->checkPassword($data['old_password'], $data['password'], $user);
        if ($newPassword === true) {
            return Helper::send_error_response('password', Lang::get('auth.password'), 422);
        }

        User::find($user['id'])->update(['password' => $newPassword]);
        return Response::make(json_encode(['success' => true]), 200);
    }

    public function uploadAvatar($request, $userId)
    {
        $validationRules = ['avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'];
        $validator = Validator::make($request->file(), $validationRules);
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]), 422);
        }
        $storagePath = \Storage::disk('users_images')->getDriver()->getAdapter()->getPathPrefix();

        $avatar = $request->file('avatar');
        $destinationPath = "$storagePath/$this->subDomain/user/avatar";
        $avatarName = date('mdYHis') . uniqid() . $avatar->getClientOriginalName();

        if (empty(User::find($userId))) {
            return Helper::send_error_response('wrong_user_id', Lang::get('users.wrong_id'), 422);
        }

        $currentImage = User::find($userId)->avatar;
        if (!empty($currentImage)) {
            $currentImagePath = "$storagePath/$this->subDomain/user/avatar/$currentImage";
            (!file_exists($currentImagePath)) ?: unlink($currentImagePath);
        }

        $userAvatar = $avatar->move($destinationPath, $avatarName);
        if (!$userAvatar) {
            return Helper::send_error_response('error_saving', Lang::get('company.error_saving'), 422);
        }

        $avatarFullPath = "/uploads/$this->subDomain/user/avatar/$avatarName";
        $avatarUrl = $this->pageURL . $avatarFullPath;
        User::find($userId)->update([
            'avatar' => $avatarName,
            'avatar_full_path' => $avatarFullPath,
            'avatar_url' => $avatarUrl
        ]);

        return Response::make(json_encode([
            'success' => true,
            'data' => json_encode(['url' => $avatarUrl])
        ]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAvatar($userId)
    {
        $avatar = User::where('id', $userId)->first()->avatar;
        if (file_exists(public_path($avatar))) {
            unlink(public_path($avatar));
        }

        $update = User::where('id', $userId)->update(['avatar' => '']);
        return Response::make(json_encode([
            'success' => true,
            'data' => $update
        ]), 200);
    }

    /**
     * Get user data.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getUser($request)
    {
        $userApiToken = UserApiTokens::where('api_token', $request->header('Authorization'))->first();
        return Response::make(json_encode([
            'success' => true,
            'data' => Helper::$user
        ]), 200, ['Authorization' => $userApiToken->api_token]);
    }

    /**
     * Get assigned tickets
     *
     * @return \Illuminate\Http\Response
     */
    public function getAssignedTickets()
    {
        $user = Helper::$user;
        $tickets = Tickets::where('assign_agent_id', $user['id'])->get();

        return Response::make(json_encode([
            'success' => true,
            'data' => $tickets
        ]), 200);
    }

    public function generateUserProfileDefaultImage()
    {
        $user = Helper::$user;
        $subDomain = Helper::getSubDomain();
        $userImage = Helper::generateUserProfileDefaultImage($user['id'], $subDomain);

        return Response::make(json_encode(['success' => $userImage]), 200);
    }

    public function deactivateUser($userId)
    {
        User::withTrashed()->where('id', $userId)->restore();
        // check is owner
        $user = Helper::$user;

        if ($user['role_id'] != $this->accountOwnerRoleId) {
            return Helper::send_error_response('wrong_role_id', Lang::get('users.wrong_role_id'), 422);
        }
        $agent = User::find($userId);
        Tickets::where('assign_agent_id', $agent->id)->update(['assign_agent_id' => null]);
        $agent->delete();
        return Response::make(json_encode(['success' => true]), 200);
    }

    public function deactivateMyProfile()
    {
        $user = Helper::$user;
        Tickets::where('assign_agent_id', $user['id'])->update(['assign_agent_id' => null]);
        User::find($user['id'])->delete();
        return Response::make(json_encode(['success' => true]), 200);
    }

    public function updateUserPushNotificationsStatus($status)
    {
        $user = Helper::$user;
        User::where('id', $user['id'])->update(['push_notification_status' => $status]);
        return Response::make(json_encode(['success' => true]), 200);
    }

    public function checkUserPushNotificationsStatus()
    {
        $user = Helper::$user; // 43884884
        $status = User::where('id', $user['id'])->select('push_notification_status')->first()->push_notification_status;
        return Response::make(json_encode(['success' => true, 'status' => $status]), 200);
    }
}