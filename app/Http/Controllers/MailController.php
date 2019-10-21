<?php

namespace App\Http\Controllers;

use App\Helpers\CustomersHelper;
use App\Helpers\EmailCommandsService;
use App\Helpers\FilesHelper;
use App\Helpers\SendEmailService;
use App\Http\Controllers\Tickets\TicketController;
use App\Models\Customers;
use App\Models\Mailbox;
use App\Models\SparkpostSubAccounts;
use App\Models\Subdomains;
use App\Models\User;
use App\Services\MailboxService;
use App\Services\NotificationsService;
use App\Services\TicketCommentsService;
use App\Services\TicketHistoryService;
use App\Services\TicketsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use DB;
use App\Helpers\IncomingEmailService;
use App\Models\Tickets;
use App\Helpers\Helper;
use App\Models\TicketComments;
use App\Models\MergedTickets;
use App\Helpers\SparkPostApi;
use App\Models\TicketsHistory;
use Lang;
use Response;
use App\Http\Controllers\Settings\User\UserNotificationsController;

class MailController extends Controller
{
    protected $attachedFiles = [];
    public $helper;
    public $commands = "";
    public $incomingEmailService;
    public $ticketsService;

    public function __construct(IncomingEmailService $incomingEmailService, TicketsService $ticketsService)
    {
        $this->incomingEmailService = $incomingEmailService;
        $this->ticketsService = $ticketsService;
    }

    /**
     * Get email tracking event
     * @param Request $request
     */

    public function getEvent(Request $request)
    {
        $data = $request->all();
        $transmissionId = explode('.',$data[0]['sg_message_id'])[0];
        if (isset($data[0]['bird-subdomain']) && !empty($data[0]['bird-subdomain'])){
            $subdomain = $data[0]['bird-subdomain'];
            if (Helper::checkIfDatabaseExists($subdomain)){
                Helper::changeDataBaseConnection($subdomain);

                $comment = TicketComments::where('transmission_id', $transmissionId)->first();
                if (!empty($comment) && $comment['email_status'] != "open") {
                    TicketComments::where('transmission_id', $transmissionId)->update([
                        'email_status' => $data[0]['event'],
                        'event_time' => Carbon::now()->format("Y-m-d H:i:s")
                    ]);

//                    if ($data['type'] == "open") {
                        $notification_data = [
                            "user_room" => $subdomain,
                            "ticket_id" => $comment['ticket_id'],
                            "comment_id" => $comment['id'],
                            "type" => $data[0]['event']
                        ];
                        Helper::sendNotification($notification_data, "seen");
//                    }
                }
            }

        }

        //for fixing bugs if they appears
//        DB::table('message_events')->insert(['content' => json_encode($request->all())]);

        return Response::make('', 200);
    }

    /**
     * Processing incoming email
     * @param Request $request
     */

    public function index(Request $request)
    {

        $data = $request->all();
//        dd($data);
        $to = json_decode($data['envelope'],true)['to'][0];
        $subDomain = explode('.',explode('@',$to)[1])[0];
        $body_text = IncomingEmailService::parseMail($data['email'])->getMessageBody('text');
        $body = IncomingEmailService::parseMail($data['email'])->getMessageBody('html');
//        dd($body);
//        dd($attachments);
//        if ($attachments){
//            dd($attachments[0]->getContent());
//        }
//        dd($attachments);
//
//        file_put_contents('test.txt',base64_encode($_FILES));
//        $data = $request->all();

        if ($this->incomingEmailService->isNoReply($to)) {
            return Response::make('', 200);
        }

        Helper::$subDomain = $subDomain;

        if (!Helper::checkIfDatabaseExists(Helper::$subDomain)) {
            return Response::make('', 200);
        }

        $attachments = IncomingEmailService::parseMail($data['email'])->getAttachments();

//        dd($attachments);
        //for fixing bugs if they appears
        if (empty($attachments)) {
            DB::table('test')->insert(['content' => json_encode($request->all())]);
        }else{
            $body = IncomingEmailService::parseMail($data['email'])->getMessageBody('htmlEmbedded');
        }
        $body = preg_replace("/[\n\r]/","",$body);
//        dd($body);
        Helper::changeDataBaseConnection(Helper::$subDomain);

        //checking if email is forwarded
        //if yes checking which mailbox is it else it has come to default mailbox

        $from = IncomingEmailService::parseMail($data['email'])->getAddresses('from');
        if ($this->incomingEmailService->isForwardCheck($from[0]['address'],$data['subject']))
            return Response::make('', 200);
        if ($this->incomingEmailService->isForwarded($to)) {
            $mailbox_id = $this->incomingEmailService->getMailboxId($to);
        } else {
            $mailbox_id = 1;
        }

        $customer = CustomersHelper::getCustomer($from[0]['address'], trim($from[0]['display']));
        $email['mailbox_id'] = $mailbox_id;
        $email['subject'] = $data['subject'];
        $email['body'] = $this->incomingEmailService->getPrettyBody($body, $customer);
//        dd($email['body']);
        $email_body_commands = explode('@commands',$body_text);
        if (isset($email_body_commands[1])){
            $email['body'] = $email_body_commands[0];
            $this->commands = strip_tags($email_body_commands[1]);
        }

        //change DB connections

        if ($agent_id = IncomingEmailService::isMention($to)) {
            $this->incomingEmailService->addNote($agent_id, Helper::$subDomain, $email['body']);
            return Response::make('', 200);
        }

        $mailboxData = MailboxService::getMailboxById($mailbox_id);

        /*
         * check if email is reply to ticket or
         * is a new ticket
         * if isset In-Reply-To header than it is reply
         * otherwise is a new ticket
         */

        $is_reply = IncomingEmailService::parseMail($data['email'])->getRawHeader('In-Reply-To');
        $notificationService = new NotificationsService();
        if ($is_reply) {
            // is reply
            $references = IncomingEmailService::parseMail($data['email'])->getRawHeader('References');
            $ticketIdHashArray = [];
            foreach (explode(' ', $references) as $reference) {
                $ticket_id_hash = str_replace(['<', '>'], '', explode('@', $reference)[0]);
                array_push($ticketIdHashArray, $ticket_id_hash);
            }
            $ticket = Tickets::whereIn('ticket_id_hash', $ticketIdHashArray)->withTrashed()->first();
            
            $isFromAgent = IncomingEmailService::isFromAgent($to, $email['body'], Helper::$subDomain,$this->commands);

            if (!$ticket || $isFromAgent) {
                return Response::make('', 200);
            }

            if ($ticket->merged == 1) {
                $ticket = Tickets::find(MergedTickets::where('ticket_id', $ticket->id)->first()->master_ticket_id);
            }

            $ticket = $ticket->toArray();
            $ticketCommentObj = new TicketCommentsService();

            $ticketComment = $ticketCommentObj->createComment([
                'ticket_id' => $ticket['id'],
                'from_name' => $customer['first_name'] . ' ' . $customer['last_name'],
                'from_email' => $customer['email'],
                'author_id' => null,
                'body' => $email['body']
            ]);

            //add to history
            TicketHistoryService::commentsTicketHistory($ticket['id'], null, $customer['id']);

            $ticketComment['files'] = '';

            //get attachments
            if ($attachments) {
                $get_file_properties = FilesHelper::uploadFileFromEmail(Helper::$subDomain, $ticket_id_hash, $attachments, $ticketComment['id']);
                //insert into DB file properties
                $ticketComment['files'] = IncomingEmailService::saveFileProperties($get_file_properties);
            }

            if ($ticket['status'] == 'closed') {
                Tickets::where('id', $ticket['id'])->update(['status' => 'open']);
                TicketHistoryService::statusTicketHistory($ticket['id'], null, null, 'open');
            }

            /**
             * send notification for create comment
             *
             */
            $ticket['data_comment'] = $ticketComment;
            $notification = $notificationService->saveCommentNotification($ticket['id'], null, $ticketComment['id'], $customer['id']);
            $notificationService->customerReplies($ticket, $mailbox_id, $ticket['assign_agent_id'], $notification);

            return Response::make('', 200);
        } else {
            //is a new ticket
            try{
                IncomingEmailService::checkStep(Helper::$subDomain);
            } catch (Exception $e){
                //need to log
            }
            $color = Helper::getRandomColor();

            $ticket = $this->ticketsService->createTicket(0, $mailboxData, $customer, $email, $color, true);

            //get attachments
            if ($attachments) {
                $get_file_properties = FilesHelper::uploadFileFromEmail(Helper::$subDomain, $ticket['ticket_id_hash'], $attachments, $ticket['comment']['id']);
                //insert into DB file properties
                $ticket['files'] = IncomingEmailService::saveFileProperties($get_file_properties);
            }


            //add to history
            TicketHistoryService::createTicketHistory($ticket['id'], 0, $customer['id']);

            /**
             * Notification
             */

            $ticket['author'] = '';
            $ticket['customer'] = $customer;
            $savedNotification = $notificationService->saveCreateTicketNotification($ticket['id'], $ticket['comment']['id'], null, $customer['id']);
            $notificationService->isNewTicket($ticket, $savedNotification);

            // ----------------- Auto reply -------------- //
            $mailboxData = null;
            $mailboxData = Mailbox::where('id', $mailbox_id)->first();
            if (!empty($mailboxData)) {
                // if auto reply is enabled
                if ($this->incomingEmailService->checkAutoReply($mailboxData)) {
                    $this->incomingEmailService->handleAutoReply($mailboxData, $customer, $ticket['message_id']);
                }
            }
            dd('ok');
            return Response::make('', 200);
        }
    }

}