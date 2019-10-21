<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Crypto;
use App\Helpers\SendEmailService;
use App\Http\Controllers\Controller;
use App\Models\ResetPasswords;
use App\Models\SparkpostSubAccounts;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Validator;
use Response;
use App\Models\User;
use Lang;
use DB;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $subDomain;

    function __construct()
    {
        $this->subDomain = Helper::setSubDomain();
        Helper::changeDataBaseConnection($this->subDomain);
    }

    /**
     * Reset password
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendConfirmationEmail(Request $request)
    {
        $subDomain = Helper::setSubDomain();
        $checkDbExist = Helper::checkIfDatabaseExists($subDomain);
        if (!$checkDbExist) {
            return Helper::send_error_response('subdomain', Lang::get('auth.subdomain'), 422);
        }

        Helper::changeDataBaseConnection($subDomain);
//        $data = json_decode(Crypto::decrypt($request->getContent()), true);
        $data = $request->all();
        $user = User::where('email', $data['email'])->first();
        if (!empty($user)) {
            $token = Helper::generateResetPasswordToken();
            ResetPasswords::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            $forgetPasswordUrl = "https://" . $subDomain . env('PAGE_URL') . "/forget-password?token=$token";

            $forgetPasswordEmailTemplate = view('templates.forget_password', [
                'user_email' => $user->email,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'new_password_url' => $forgetPasswordUrl
            ])->render();

            $forgetPasswordEmailTemplate = str_replace("\n", "", $forgetPasswordEmailTemplate);

            // to send email
            $options = [
                'toEmail' => $user->email,
                'toName' => $user->first_name . ' ' . $user->last_name,
                'fromEmail' => 'info@' . $this->subDomain . env('PAGE_URL'),
                'fromName' => $this->subDomain . env('PAGE_URL'),
                'subject' => 'Reset password',
                'messageId' => '<' . md5(rand(999, 9999999)) . '@' . $subDomain . env('PAGE_URL') . '>',
                'commentText' => $forgetPasswordEmailTemplate,
                'reply_to' => 'info@' . $this->subDomain . env('PAGE_URL')
            ];

            SendEmailService::sendEmail($options, false);

            Helper::changeDataBaseConnection($this->subDomain);
            return Response::make(json_encode(['success' => true]), 200);
        }
        return Helper::send_error_response('email', Lang::get("users.wrong_email"), 422);
    }

    /**
     * Reset password
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function createNewPassword(Request $request)
    {
        $checkDbExist = Helper::checkIfDatabaseExists($this->subDomain);
        if (!$checkDbExist) {
            return Helper::send_error_response('subdomain', Lang::get('auth.subdomain'), 422);
        }

        $token = $request->get('token');
        $data = $request->all();
        $checkUrlToken = json_decode($this->checkToken($request)->original, true);
        if ($checkUrlToken['success']) {
            // check token
            $checkToken = ResetPasswords::where('token', $token)->first();
            if (!empty($checkToken)) {
                $userId = $checkToken->user_id;
                $validation_rules = ['password' => 'required|min:6|confirmed'];
                $validator = Validator::make($data, $validation_rules);

                //if data didn't pass validation sends validation errors
                if ($validator->fails()) {
                    return Response::make(json_encode([
                        'success' => false,
                        'errors' => $validator->errors()
                    ]), 422);
                }

                $newPassword = bcrypt($data['password']);
                $user = User::find($userId);
                $user->update(['password' => $newPassword]);

                ResetPasswords::where('user_id', $userId)->delete();
                return Response::make(json_encode(['success' => true]), 200);
            }
            return Helper::send_error_response('wrong_user_token', Lang::get("users.wrong_token"), 422);
        }
        return $this->checkToken($request);
    }

    /**
     * Check email confirmation url token
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function checkToken(Request $request)
    {
        $checkDbExist = Helper::checkIfDatabaseExists($this->subDomain);
        if (!$checkDbExist) {
            return Helper::send_error_response('subdomain', Lang::get('auth.subdomain'), 422);
        }

        $token = $request->get('token');
        if (empty(ResetPasswords::where('token', $token)->first())) {
            return Helper::send_error_response('wrong_token', Lang::get('passwords.wrong_token'), 422);
        }

        $userId = ResetPasswords::where('token', $token)->first()->user_id;
        $resetPasswordTokenId = ResetPasswords::where('token', $token)->first()->id;
        $resetPasswordTokensMaxID = ResetPasswords::where('user_id', $userId)->max('id');
        if ($resetPasswordTokensMaxID !== $resetPasswordTokenId) {
            return Helper::send_error_response('expired_token', Lang::get('passwords.expired_token'), 422);
        }
        return Response::make(json_encode(['success' => true]), 200);
    }
}
