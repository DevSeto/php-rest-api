<?php

namespace App\Http\Controllers\Settings\Company;

use App\Http\Controllers\Controller;
use App\Models\Permissions;
use App\Models\UserRoles;
use Illuminate\Http\Request;
use App\Models\Subdomains;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Lang;
use Response;
use Validator;
use File;
use DB;
use App\Models\CompanySetting;


class CompanySettingsController extends Controller
{
    protected $subDomain;
    protected $companyData;

    function __construct()
    {
        $this->middleware('check_token');
        $this->companyData = Subdomains::where('company_url', Helper::$subDomain)->get();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = CompanySetting::first();

        return Response::make(json_encode([
            'success' => true,
            'data' => $settings
        ]), 200);
    }

    /**
     * Update company profile.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data = $request->all();

        CompanySetting::first()->update($data);
        return Response::make(json_encode([
            'success' => true,
            'data' => CompanySetting::first()
        ]), 200);
    }

    /**
     * Insert or update account image
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadLogo(Request $request)
    {
        $validationRules = ['avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'];
        $validator = Validator::make($request->file(), $validationRules);
        if ($validator->fails()) {
            return Response::make(json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]), 422);
        }

        $avatar = $request->file('avatar');
        $destinationPath = public_path() . '/uploads/' . Helper::$subDomain;
        $avatarName = time() . $avatar->getClientOriginalName();
        $old_logo = CompanySetting::first()->logo;

        if (!empty($old_logo)) {
            (!file_exists(public_path($old_logo))) ?: unlink(public_path($old_logo));
        }

        $companyLogo = $avatar->move($destinationPath, $avatarName);
        if (!$companyLogo) {
            return Helper::send_error_response('team_id', Lang::get('company.error_saving'), 422);
        }

        CompanySetting::first()->update([
            'logo' => 'uploads/' . Helper::$subDomain . '/' . $companyLogo->getFilename(),
            'logo_full_path' => 'https://' . env('APP_PROD') . env('PAGE_URL') . '/uploads/' . Helper::$subDomain . '/' . $companyLogo->getFilename()
        ]);

        $update = [
            'logo' => 'uploads/' . Helper::$subDomain . '/' . $companyLogo->getFilename(),
            'logo_full_path' => 'https://' . env('APP_PROD') . env('PAGE_URL') . '/uploads/' . Helper::$subDomain . '/' . $companyLogo->getFilename()
        ];

        return Response::make(json_encode([
            'success' => true,
            'data' => $update
        ]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAvatar(Request $request, $userId)
    {
        $oldLogo = CompanySetting::first()->logo;

        if (!empty($oldLogo)) {
            (!file_exists(public_path($oldLogo))) ?: unlink(public_path($oldLogo));
        }

        $delete = CompanySetting::first()->update(['logo' => '']);
        return Response::make(json_encode([
            'success' => true,
            'deletedAvatar' => $delete
        ]), 200);
    }

    /****************************       Permissions and User Roles   ************************************/
    /**
     * Get All Permissions of User
     *
     * @return \Illuminate\Http\Response
     */
    public function getPermissions()
    {
        $permissions = Permissions::get();
        return Response::make(json_encode([
            'success' => true,
            'content' => !empty($permissions->toArray()) ? $permissions : []
        ]), 200);
    }

    /**
     * Create new user roles and set permissions
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function createUserRole(Request $request)
    {
        $dataOfRequest = $request->all();
        $data = [
            'name' => $dataOfRequest['name'],
            'display_name' => $dataOfRequest['display_name'],
            'permissions_id' => json_encode($dataOfRequest['permissions_id'], true)
        ];
        UserRoles::create($data);
    }
    /****************************    />  Permissions and User Roles     ************************************/

}
