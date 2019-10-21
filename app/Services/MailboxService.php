<?php

namespace App\Services;


use App\Exceptions\ForbiddenToMailboxException;
use App\Exceptions\NoMailboxWithThisIdException;
use App\Exceptions\UnverifiedMailboxException;
use App\Helpers\Helper;
use App\Helpers\SendEmailService;
use App\Helpers\SparkPostApi;
use App\Exceptions\CreateMailboxException;
use App\Models\AddedMailboxes;
use App\Models\EmailProviders;
use App\Models\Mailbox;
use App\Models\MailboxAvailableHours;
use App\Models\User;
use DB;
use Dotenv\Exception\ValidationException;
use Validator;
use App\Models\MailboxUserPermissions;
use Illuminate\Support\Facades\Lang;

class MailboxService {

    /**
     * @param $mailboxId
     * @param null $userId
     * @return mixed
     * @throws ForbiddenToMailboxException
     * @throws NoMailboxWithThisIdException
     * @throws UnverifiedMailboxException
     */

    public static function getMailboxById($mailboxId,$userId = null,$forShow = false){
        $mailbox = Mailbox::where('id', $mailboxId)->first();
        if (empty($mailbox)) {
            throw new NoMailboxWithThisIdException(Lang::get('mailbox.wrong_id').$mailboxId);
        }

        if (!$forShow){
            if ($mailbox['dns_verified'] != 1){
                throw new UnverifiedMailboxException();
            }
        }

        if (!empty($userId)){

            if (!self::userHasPermission($userId,$mailbox->id)){
                throw new ForbiddenToMailboxException();
            }
        }

        return $mailbox->toArray();
    }

    /**
     * Get users who have permissions to this mailbox
     * @param int $mailboxId
     * @return array
     */
    public static function getUsersOfMailBox($mailboxId,$usersData = false)
    {
        if(!$usersData){
            $result = MailboxUserPermissions::where('mailbox_id', $mailboxId)->pluck('user_id')->toArray();
        }else{
            $result = User::with(['mailboxes' => function ($query) use ($mailboxId) {
                $query->where('mailbox_user_permissions.mailbox_id', $mailboxId);
            }])->get();
        }
        return $result;
    }

    public static function getUsersAvailableMailboxes($user){
        $mailboxIds = MailboxUserPermissions::where('user_id',$user['id'])->pluck('mailbox_id')->toArray();
        $mailboxes = Mailbox::whereIn('id',$mailboxIds)->withCount([
            'open_tickets',
            'pending_tickets',
            'closed_tickets',
            'spam_tickets'
        ])->get()->toArray();

        foreach ($mailboxes as $key => $mailbox){
            $mailboxes[$key]['signature'] = self::getMailboxPrettySignature($user,$mailbox);
        }

        return $mailboxes;
    }

    /**
     * @param $user_id
     * @param $mailbox_id
     * @return bool
     */

    public static function userHasPermission($user_id, $mailbox_id)
    {
        return boolval(MailboxUserPermissions::where('user_id', $user_id)->where('mailbox_id', $mailbox_id)->first());
    }

    /**
     * return pretty signature without vars
     * @param $user
     * @param $mailbox
     * @return mixed
     */

    public static function getMailboxPrettySignature($user, $mailbox){
        $user_first_name = $user['first_name'];
        $user_last_name = $user['last_name'];
        $user_full_name = $user_first_name . ' ' . $user_last_name;
        $company_name = Helper::$subDomain;

        $search_arr = [
            "{%user.first_name%}",
            "{%user.last_name%}",
            "{%user.full_name%}",
            "{%company.name%}",
            "{%mailbox.name%}",
            "{%mailbox.email%}"
        ];

        $replace_arr = [
            $user_first_name,
            $user_last_name,
            $user_full_name,
            $company_name,
            $mailbox['name'],
            $mailbox['email']
        ];

        $mailbox['signature'] = str_replace($search_arr,$replace_arr,$mailbox['signature']);

        return $mailbox['signature'];
    }

    /**
     * Check if domain is mailing systems
     * @param $domain
     * @throws CreateMailboxException
     */
    public function isMailingSystem($domain)
    {
        DB::setDefaultConnection('mysql');
        if (!empty(EmailProviders::where('name', $domain)->first())){
            Helper::changeDataBaseConnection(Helper::$subDomain);
            throw new CreateMailboxException(Lang::get('mailbox.mailing_system'));
        }
        Helper::changeDataBaseConnection(Helper::$subDomain);
    }

    public function sendingDomainExists($sendingDomain,$subAccountDataId){
        //if sending domain already exists checking is this users sending domain
        if ($sendingDomain['results']['subaccount_id'] == $subAccountDataId) {
            //you have already set up this domain
            throw new CreateMailboxException(Lang::get('mailbox.domain_exist'));
        } else {
            //already been taken by another user
            throw new CreateMailboxException(Lang::get('mailbox.domain_exist_another_user'));
        }
    }

    public function createMailbox($data,$subAccountName){
        $confirmationNumber = (isset($data['dns_verified']) && $data['dns_verified'] == 1) ? $data['dns_verified'] : mt_rand(100000, 999999);

        $mailbox = Mailbox::create([
            'creator_user_id' => Helper::$user['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'signature' => !empty($data['signature']) ? $data['signature'] : '',
            'auto_reply' => !empty($data['auto_reply']) ? $data['auto_reply'] : '',
            'auto_reply_subject' => !empty($data['auto_reply_subject']) ? $data['auto_reply_subject'] : '',
            'auto_reply_body' => !empty($data['auto_reply_body']) ? $data['auto_reply_body'] : '',
            'dns_verified' => $confirmationNumber,
            'auto_bcc' => $data['auto_bcc'],
            'forward_address' => !empty($data['forward_address']) ? $data['forward_address'] : '',
            'users' => $data['users']
        ])->toArray();

        if (empty($data['forward_address'])){
            $forwardAddress = 'forward_' . md5(str_random(45)) . '_' . $mailbox['id'] . '@' . $subAccountName . env('PAGE_URL');

            Mailbox::find($mailbox['id'])->update([
                'forward_address' => $forwardAddress
            ]);

            $mailbox['forward_address'] = $forwardAddress;
        }

        MailboxUserPermissions::create([
            'user_id' => Helper::$user['id'],
            'mailbox_id' => $mailbox['id']
        ]);
        if ($mailbox['dns_verified'] != 1){
            SendEmailService::sendMailboxConfirmationEmail($data['email'],$confirmationNumber);
        }

        return $mailbox;
    }

    public function resendConfirmation($id){
        $mailbox = Mailbox::where('id',$id)->first();
        $confirmationNumber = mt_rand(100000, 999999);
        Mailbox::where('id',$id)->update(['dns_verified' => $confirmationNumber]);
        SendEmailService::sendMailboxConfirmationEmail($mailbox['email'],$confirmationNumber);
    }

    public function createMailboxUserPermissions($mailboxId,$mailboxUsers, $allowedUsers = null){

        $users = [];

        switch ($mailboxUsers) {
            case 1:
                //admins
                if (!empty($allowedUsers)) {
                    $users = $allowedUsers;
                }
                break;
            case 2:
                //everyone
                $users = UsersService::getAgentsAndAdmins()->pluck('id');
                break;
            default :
                break;
        }

        $mailboxUserPermissionsData = [];
        if (!empty($users)){
            foreach ($users as $user) {
                array_push($mailboxUserPermissionsData, [
                    'user_id' => $user,
                    'mailbox_id' => $mailboxId
                ]);
            }
        }
        DB::table('mailbox_user_permissions')->insert($mailboxUserPermissionsData);

    }

    public function setUserMailboxPermission($status,$userId,$mailboxId){
        switch (intval($status)) {
            case 0:
                if (MailboxUserPermissions::where('user_id', $userId)->count() > 1) {
                    MailboxUserPermissions::where('mailbox_id', $mailboxId)->where('user_id', $userId)->delete();
                } else {
                    throw new NoMailboxWithThisIdException(Lang::get('mailbox.last_mailbox'));
                }
                break;
            case 1:
                MailboxUserPermissions::firstOrCreate([
                    'user_id' => $userId,
                    'mailbox_id' => $mailboxId
                ]);
                break;
        }
    }

    public function updateDefaultMailboxName($data){
        $validationRules = ['name' => 'required'];

        //validating request
        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
        //update default mailbox name, default mailbox id is 1
        $update = Mailbox::where('id', 1)->update(['name' => $data['name']]);
        //update account owner step
        if ($update) {
            User::where('id', 1)->update([
                'step' => json_encode([
                    'step' => 1,
                    'mailbox_id' => ''
                ])
            ]);
        }
    }

    public function sendCheckForwardingEmail($mailbox){
        $html = view('templates.check_forwarding')->render();
        $html = str_replace("\n", "", $html);

        $options = [
            'toEmail' => $mailbox['email'],
            'toName' => $mailbox['name'],
            'fromEmail' => 'check_forward@mail' . env('PAGE_URL'),
            'fromName' => env('PAGE_URL_DOMAIN'),
            'subject' => 'mailbox_' . $mailbox['id'] . '_' . Helper::$user['id'],
            'commentText' => $html,
            'reply_to' => 'check_forward@mail' . env('PAGE_URL')
        ];

        SendEmailService::sendEmail($options);
    }

    /**
     * @param $mailboxId
     * @param $timeLine
     * @return mixed
     */

    public function setMailboxAvailableHours($mailboxId, $timeLine){
        return MailboxAvailableHours::updateOrCreate(['mailbox_id' => $mailboxId], $timeLine);
    }

    /**
     * @param $mailboxId
     * @param $data
     * @return mixed
     */

    public function editMailboxAutoReplySettings($mailboxId,$data){
        return Mailbox::where('id', $mailboxId)->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getMailboxAvailableHours($id){

        $data = MailboxAvailableHours::where('mailbox_id', $id)->first();
        unset(
            $data['id'],
            $data['mailbox_id'],
            $data['deleted_at'],
            $data['created_at'],
            $data['updated_at']
        );

        if (!empty($data)) {
            $data = $data->toArray();
            foreach ($data as $day => $timeline) {
                $data[$day] = json_decode($timeline, true);
            }
        }
        return $data;
    }

    /**
     * @param $mailboxId
     * @param $verifyData
     * @return bool
     */

    public function handleMailboxVerify($mailboxId,$verifyData){
        if ($verifyData['results']['ownership_verified'] == true && $verifyData['results']['dkim_status'] == 'valid') {
            return Mailbox::where('id', $mailboxId)->update(['dns_verified' => '1']);
        }
        return false;
    }

    /**
     * @param $confirmationNumber
     * @return bool
     */

    public function confirmMailbox($confirmationNumber){
        return boolval($mailbox = Mailbox::where('dns_verified',$confirmationNumber)->update([
            'dns_verified' => 1
        ]));
    }

    public function checkIfDomainForMailboxIsFree($domain){
        DB::setDefaultConnection('mysql');

        if (empty(AddedMailboxes::where('domain', $domain)->first())){
            $result = true;
            AddedMailboxes::create([
                'domain' => $domain,
                'company_id' => 1
            ]);
        }else{
            $result = false;
        }

        Helper::changeDataBaseConnection(Helper::$subDomain);

        return $result;
    }
}