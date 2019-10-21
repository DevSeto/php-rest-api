<?php

namespace App\Http\Controllers\Settings\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Response;
use Validator;
use File;
use Lang;
use App\Services\ProfileService;


class ProfileController extends Controller
{
    protected $company;
    private $profileService;

    function __construct(ProfileService $profileService)
    {
        $this->middleware('check_token');
        $this->profileService = $profileService;
    }


    /**
     * Get user profile
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response::make(json_encode([
            'success' => true,
            'data' => Helper::$user
        ]), 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $user_id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user_id)
    {
        $data = $request->all();
        $validationRules = [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'time_zone' => 'required|max:255'
        ];

        if (empty($data)) {
            return Response::make(json_encode(['success' => false]), 422);
        }

        $validator = Validator::make($data, $validationRules);
        //if data didn't pass validation sends validation errors
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]), 422);
        }
        unset($data['q']);
        User::where('id', $user_id)->update($data);
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Insert or update account image
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadAvatar(Request $request, $userId)
    {
        return $this->profileService->uploadAvatar($request, $userId);
    }

    /**
     * Remove the specified resource from storage.
     * @param  int $userId
     * @return \Illuminate\Http\Response
     */
    public function deleteAvatar($userId)
    {
        return $this->profileService->deleteAvatar($userId);
    }

    /**
     * Get user data.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getUser(Request $request)
    {
        return $this->profileService->getUser($request);
    }

    /**
     * Get assigned tickets
     *
     * @return \Illuminate\Http\Response
     */
    public function getAssignedTickets()
    {
        return $this->profileService->getAssignedTickets();
    }

    public function generateUserProfileDefaultImage()
    {
        return $this->profileService->generateUserProfileDefaultImage();
    }

    /**
     * Deactivate User
     *
     * @param int $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function deactivateUser($userId)
    {
        return $this->profileService->deactivateUser($userId);
    }

    /**
     * Deactivate User
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function deactivateMyProfile()
    {
        return $this->profileService->deactivateMyProfile();
    }

    public function resetPassword(Request $request, $user_id)
    {
        return $this->profileService->resetPassword($request, $user_id);
    }

    public function updateUserPushNotificationsStatus(Request $request)
    {
        $status = $request->all()['status'];
        return $this->profileService->updateUserPushNotificationsStatus($status);
    }

    public function checkUserPushNotificationsStatus()
    {
        return $this->profileService->checkUserPushNotificationsStatus();
    }

}
