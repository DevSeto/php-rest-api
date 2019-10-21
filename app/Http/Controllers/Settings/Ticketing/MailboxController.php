<?php

namespace App\Http\Controllers\Settings\Ticketing;

use App\Exceptions\NoMailboxWithThisIdException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Company\UsersController;
use App\Exceptions\CreateMailboxException;
use App\Http\Requests\CreateMailboxRequest;
use App\Http\Requests\MailboxAutoReplyRequest;
use App\Http\Requests\UpdateMailboxRequest;
use App\Models\CompanySetting;
use App\Models\EmailProviders;
use App\Models\User;
use App\Helpers\SparkPostApi;
use App\Models\SparkpostSubAccounts;
use App\Models\UserApiTokens;
use App\Services\MailboxService;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Models\Mailbox;
use App\Models\MailboxUserPermissions;
use App\Helpers\Helper;
use App\Models\MailboxAvailableHours;
use Response;
use Exception;
use Lang;
use DB;
use Validator;
use App\Helpers\Crypto;

class MailboxController extends Controller
{
    protected $subDomain;
    protected $mailboxService;

    function __construct(MailboxService $mailboxService)
    {
        $this->middleware('check_token');
        $this->mailboxService = $mailboxService;
    }

    /**
     * @return mixed
     */

    public function index()
    {
        $user = Helper::$user;
        $mailboxes = MailboxService::getUsersAvailableMailboxes($user);

        return Response::make(json_encode([
            'success' => true,
            'data' => $mailboxes
        ]), 200);
    }

    /**
     * create new Mailbox
     * @param CreateMailboxRequest $request
     * @return mixed
     */
    public function create(CreateMailboxRequest $request)
    {
        $data = $request->all();
        $domain = explode('@', $data['email'])[1];

        try {
            $this->mailboxService->isMailingSystem($domain);
        } catch (CreateMailboxException $e) {
            return Helper::send_error_response('domain', $e->getMessage(), 422);
        }

        if (!$this->mailboxService->checkIfDomainForMailboxIsFree($domain)){
            return Helper::send_error_response('mailbox', Lang::get('mailbox.domain_exist'), 413);
        }


        $mailbox = $this->mailboxService->createMailbox($data, Helper::$subDomain);
        $defaultAvailableHours = config('constants.mailbox_available_hours');
        $mailbox['auto_reply_timeline'] = $this->mailboxService->setMailboxAvailableHours($mailbox['id'], $defaultAvailableHours);
        $this->mailboxService->createMailboxUserPermissions($mailbox['id'], $data['users'], $data['allowed_users']);

        return Response::make(json_encode([
            'success' => true,
            'data' => $mailbox
        ]), 200);
    }

    /**
     * Get mailbox.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $mailbox = MailboxService::getMailboxById($id, Helper::$user['id'], true);
        } catch (Exception $e) {
            return Helper::send_error_response('domain', $e->getMessage(), 422);
        }

        $mailbox['auto_reply_timeline'] = $this->mailboxService->getMailboxAvailableHours($mailbox['id']);
        return Response::make(json_encode([
            'success' => true,
            'data' => $mailbox
        ]), 200);
    }


    /**
     * Update mailbox.
     *
     * @param  UpdateMailboxRequest $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMailboxRequest $request, $id)
    {
        $data = $request->all();

        unset($data['users'], $data['auto_reply'], $data['auto_reply_subject'], $data['auto_reply_body'], $data['email']);

        Mailbox::find($id)->update($data);
        $dataMailbox = Mailbox::find($id)
            ->withCount([
                'open_tickets',
                'pending_tickets',
                'closed_tickets',
                'spam_tickets',
                'draft_tickets'
            ])->first();
        return Response::make(json_encode(['success' => true, 'data' => $dataMailbox]), 200);
    }

    /**
     * Remove mailbox.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // will be needed in the future
    }

    /**
     * @param $id
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyMailboxDomain(Request $request, $id)
    {
        //get sparkpost token
        $sparkPostKey = SparkPostApi::getSparkpostSubAccountsData()['key'];

        try {
            $mailbox = MailboxService::getMailboxById($id, Helper::$user['id']);
        } catch (Exception $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        try {
            $verify = SparkPostApi::verifySendingDomain(explode('@', $mailbox['email'])[1], $sparkPostKey, 1);
        } catch (Exception $e) {
            return Helper::send_error_response('mailbox', Lang::get('mailbox.unverified_domain'), 413);
        }

        if ($this->mailboxService->handleMailboxVerify($mailbox['id'], $verify))
            return Response::make(json_encode([
                'success' => true,
                'data' => ['message' => Lang::get('mailbox.verified_domain')]
            ]), 200);

        return Helper::send_error_response('mailbox', Lang::get('mailbox.unverified_domain'), 413);
    }

    /**
     * @param $mailboxId
     * @param MailboxAutoReplyRequest $request
     * @return mixed
     */
    public function editAutoReply($mailboxId, MailboxAutoReplyRequest $request)
    {
        try {
            $mailbox = MailboxService::getMailboxById($mailboxId, Helper::$user['id'], true);
        } catch (Exception $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        $data = $request->all();
        unset($data['q']);

        if ($data['auto_reply'] == 1) {
            // ToDo change
            if (empty(CompanySetting::first()->timezone_offset)) {
                return Helper::send_error_response('timezone', Lang::get('company.timezone_missing'), 422);
            }

            foreach ($data['auto_reply_timeline'] as $day => $timeline) {
                $data['auto_reply_timeline'][$day] = json_encode($timeline);
            }
            $data['auto_reply_timeline'] = $this->mailboxService->setMailboxAvailableHours($mailboxId, $data['auto_reply_timeline']);
            $data['auto_reply_timeline']['mailbox_id'] = $mailbox['id'];
        }

        unset($data['auto_reply_timeline']);
        $autoReply = $this->mailboxService->editMailboxAutoReplySettings($mailbox['id'], $data);

        return Response::make(json_encode([
            'success' => true,
            'data' => $autoReply
        ]), 200);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return mixed
     */
    public function updateDefaultMailboxName(Request $request)
    {
        $data = $request->all();
        try {
            $this->mailboxService->updateDefaultMailboxName($data);
        } catch (ValidationException $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Get users of mailbox
     *
     * @param integer $mailboxId
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsersOfMailbox($mailboxId)
    {
        $users = MailboxService::getUsersOfMailBox($mailboxId, true);

        return Response::make(json_encode([
            'success' => true,
            'data' => $users
        ]), 200);
    }

    /**
     * @param $mailboxId
     * @param $userId
     * @param $status
     * @return mixed
     */
    public function setMailboxUserPermissions($mailboxId, $userId, $status)
    {
        try {
            MailboxService::getMailboxById($mailboxId);
        } catch (Exception $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        try {
            $this->mailboxService->setUserMailboxPermission($status, $userId, $mailboxId);
        } catch (NoMailboxWithThisIdException $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => ''
        ]), 200);
    }


    /**
     * @param $mailboxId
     * @return mixed
     */
    public function checkForwarding($mailboxId)
    {
        try {
            $mailbox = MailboxService::getMailboxById($mailboxId);
        } catch (Exception $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        try {
            $this->mailboxService->sendCheckForwardingEmail($mailbox);
        } catch (Exception $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => []
        ]), 200);
    }

    /**
     * @param $confirmNumber
     * @return mixed
     */

    public function confirmMailbox($confirmNumber){
        $result = $this->mailboxService->confirmMailbox($confirmNumber);
        return Response::make(json_encode([
            'success' => true,
            'data' => [
                'result' => $result
            ]
        ]), 200);
    }

    /**
     * @param $id
     * @return mixed
     */

    public function resendMailboxConfirmationEmail($id){
        $this->mailboxService->resendConfirmation($id);
        return Response::make(json_encode([
            'success' => true,
            'data' => []
        ]), 200);
    }


}
