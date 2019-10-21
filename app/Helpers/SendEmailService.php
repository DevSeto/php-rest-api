<?php

namespace App\Helpers;

use App\Models\Tickets;
use App\Models\User;
use App\Services\TicketCommentsService;
use App\Services\TicketsService;
use DB;


class SendEmailService
{
    /**
     * tries to send email
     * if something went wrong save email into failed emails
     * @param $options
     * @return bool|mixed
     */

    public static function sendEmail($options,$track = true)
    {

        return SendgridApi::sendEmail($options);
        //get Sparkpost Data
//        $options['token'] = SparkPostApi::getSparkpostSubAccountsData()['key'];
//
//        try {
//            $result = SparkPostApi::createTransmission($options, $track);
//        } catch (Exception $e) {
//            DB::setDefaultConnection('mysql');
//
//            FailedEmails::create([
//                'toEmail' => $options['toEmail'],
//                'toName' => $options['toName'],
//                'fromEmail' => $options['fromEmail'],
//                'fromName' => $options['fromName'],
//                'token' => $options['token'],
//                'subject' => $options['subject'],
//                'attachedFiles' => !empty($options['attachedFiles']) ? json_encode($options['attachedFiles']) : '',
//                'messageId' => $options['messageId'],
//                'References' => !empty($options['References']) ? $options['References'] : '',
//                'cc' => !empty($options['cc']) ? $options['cc'] : '',
//                'bcc' => !empty($options['bcc']) ? $options['bcc'] : '',
//                'commentText' => $options['commentText'],
//                'sub_domain' => Helper::$subDomain,
//                'replyEmail' => $options['replyEmail'],
//                'reply_to' => $options['reply_to'],
//                'track' => !empty($options['track']) ? $options['track'] : 0
//            ]);
//
//            Helper::changeDataBaseConnection(Helper::$subDomain);
//
//            $result = false;
//        }
//
//        Helper::changeDataBaseConnection(Helper::$subDomain);
//
//        return $result;
    }

    /**
     * send welcome email when User registers
     * @param $toEmail
     * @param $toName
     * @param $domain
     */

    public static function sendWelcomeEmail($toEmail, $toName, $domain)
    {

        $html = view('templates.welcome', [
            'domain' => $domain,
            'url' => $domain . '.' . env('PAGE_URL_DOMAIN'),
        ])->render();
        $html = str_replace("\n", "", $html);

        $options = [
            'toEmail' => $toEmail,
            'toName' => $toName,
            'fromEmail' => 'welcome@mail' . env("PAGE_URL"),
            'fromName' => env("PAGE_URL_DOMAIN"),
            'subject' => "Welcome",
            'commentText' => $html,
            'reply_to' => 'welcome@' . env("PAGE_URL")
        ];

        try{
            SendEmailService::sendEmail($options);
        } catch (\Exception $e){
            dd($e->getResponse()->json());
        }
    }

    public static function sendWorkspaces($workspaces,$toEmail){
        $html = view('templates.find_workspace', [
            'workspaces' => $workspaces,
            'url' => env('PAGE_URL'),
        ])->render();
        $html = str_replace("\n", "", $html);
        $options = [
            'toEmail' => $toEmail,
            'toName' => '',
            'fromEmail' => 'welcome@mail' . env("PAGE_URL"),
            'fromName' => "mail".env("PAGE_URL"),
            'subject' => "Here is your Workspaces",
            'commentText' => $html,
            'reply_to' => 'welcome@' . env("PAGE_URL")
        ];

        try{
            SendEmailService::sendEmail($options);
        } catch (\Exception $e){
            dd($e->getResponse()->json());
        }
    }

    public static function sendMailboxConfirmationEmail($toEmail,$confirmationNumber){
        $html = view('templates.confirm_mailbox', [
            'email' => $toEmail,
            'confirmationNumber' => $confirmationNumber,
        ])->render();
        $html = str_replace("\n", "", $html);

        $options = [
            'toEmail' => $toEmail,
            'toName' => '',
            'fromEmail' => 'welcome@mail' . env("PAGE_URL"),
            'fromName' => env("PAGE_URL_DOMAIN"),
            'subject' => "Mailbox Confirmation",
            'commentText' => $html,
            'reply_to' => 'no-reply@' . env("PAGE_URL")
        ];

        try{
            SendEmailService::sendEmail($options);
        } catch (\Exception $e){
            dd($e->getResponse()->json());
        }
    }

    /**
     * html for notifications
     * @param $comment
     * @param $ticket_id_hash
     * @param $href
     * @return array
     */
    public static function prettyHtmlAndData($ticket_id, $href)
    {
        $ticketCommentsService_obj = new TicketCommentsService();
        $ticketService = new TicketsService($ticketCommentsService_obj);

        $ticketData = $ticketService->getAllTicketDataById($ticket_id,1,6,true);

        $ticket_comments = Tickets::where('id', $ticket_id)
            ->with(['commentsLimited', 'labels', 'assignedUser', 'mailbox'])
            ->first()
            ->toArray();

        $ticketData['ticket_data']['mailbox'] = $ticket_comments['mailbox'];
        $ticketData['ticket_data']['assignedUser'] = User::where('id',$ticketData['ticket_data']['assign_agent_id'])->first();
        if ($ticketData['ticket_data']['assignedUser']){
            $ticketData['ticket_data']['assignedUser'] = $ticketData['ticket_data']['assignedUser']->toArray();
        }
        $ticketData['ticket_data']['labels'] = $ticket_comments['labels'];
        $ticketData['last_comment'] = !empty($ticketData['timeline'][0]['note']) ? $ticketData['timeline'][0]['note'] : $ticketData['timeline'][0]['body'];
        $ticketData['href'] = $href;
        $html = view('templates.notification', ['data' => $ticketData])->render();
        $html = str_replace("\n", "", $html);
        $last_comment_id = $ticket_comments['comments_limited'][0]['id'];

        $result = [
            'html' => $html,
            'last_comment_id' => $last_comment_id,
            'mailbox' => $ticket_comments['mailbox'],
            'ticket_id_hash' => $ticket_comments['ticket_id_hash']
        ];

        return $result;
    }
}