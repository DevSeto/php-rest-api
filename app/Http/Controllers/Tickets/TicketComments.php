<?php

namespace App\Http\Controllers\Tickets;

use App\Exceptions\TicketNotFoundException;
use App\Helpers\FilesHelper;
use App\Helpers\SendEmailService;
use App\Http\Requests\CreateCommentRequest;
use App\Models\Mailbox;
use App\Services\MailboxService;
use App\Services\NotificationsService;
use App\Services\TicketCommentsService;
use App\Services\TicketHistoryService;
use App\Services\TicketsService;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Validator;
use Lang;
use Response;
use DB;


class TicketComments extends Controller
{
    protected $ticketCommentsService;

    function __construct(TicketCommentsService $ticketCommentsService)
    {
        $this->middleware('check_token');
        $this->ticketCommentsService = $ticketCommentsService;
    }

    public function create(CreateCommentRequest $request)
    {
        $data = $request->all();
        $user = Helper::$user;

        try {
            $ticket = TicketsService::getTicketByHash($data['ticket_id_hash']);
        } catch (TicketNotFoundException $e) {
            return Helper::send_error_response('ticket', $e->getMessage(), 422);
        }

        //get mailbox
        try {
            $mailbox = MailboxService::getMailboxById($ticket['mailbox_id'], $user['id']);
        } catch (\Exception $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }

        $ticketReply = $this->ticketCommentsService->createComment([
            'ticket_id' => $ticket['id'],
            'from_name' => $mailbox['name'],
            'from_email' => $mailbox['email'],
            'author_id' => $user['id'],
            'body' => $data['body']
        ]);

        //add to history
        TicketHistoryService::commentsTicketHistory($ticket['id'], $user['id'], null);

        //handle attached files
        $attachedFiles = [];
        if (isset($data['attachments']) && !empty($data['attachments'])) {
            $attachedFiles = FilesHelper::handleAttachedFiles($data['attachments'], $ticket['ticket_id_hash'], $ticketReply['id']);
        }

        $options = $this->ticketCommentsService->prepareDataForSendEmail($ticket, $mailbox, $data['body'], $attachedFiles);

        $options['cc'] = !empty($data['cc']) ? $data['cc'] : '';
        $options['bcc'] = !empty($data['bcc']) ? $data['bcc'] : '';

        $send_email = false;

        //if ticket is demo don't send email

        if ($ticket['is_demo'] != 1) {
            if ($ticket['is_demo'] != 1) {
                try{
                    $send_email = SendEmailService::sendEmail($options);
                } catch (\Exception $e){
                    dd($e->getResponse()->json());
                }
            }
        }

        if ($send_email) {
            $transmissionId = $send_email->getHeaders()['X-Message-Id'][0];

            $this->ticketCommentsService->updateCommentTransmissionId($ticketReply['id'], $transmissionId);
        }

        //change assign if needed
        if (isset($data['assign_agent_id']) && (!empty($data['assign_agent_id']) || $data['assign_agent_id'] == 0)) {
            TicketsService::processAssign($data['assign_agent_id'], [$ticket], $user);
        }

        //change status if exists
        if (!empty($data['status']) && $ticket['status'] != $data['status']) {
            TicketsService::changeStatus([$ticket['ticket_id_hash']], $data['status']);
        }

        $ticketReply['is_forwarded'] = 0;
        $ticketReply['forwarding_addresses'] = '';

        //forward comments to forwarding emails
        if (isset($data['forward']) && $data['forward'] == 1) {
            if (!empty($data['forwarding_emails'])) {
                $options['is_demo'] = $ticket['is_demo'];
                $this->ticketCommentsService->sendForwardingComments($ticketReply['id'], $data['forwarding_emails'], $options);
                $ticketReply['is_forwarded'] = 1;
                $ticketReply['forwarding_addresses'] = $data['forwarding_emails'];
            }
        }

        /**
         * send comment create notification
         */
        $ticketReply['author'] = $user;
        $ticketReply['type'] = 'comment';
        $ticketReply['files'] = $attachedFiles;
        $ticket['comment'] = $ticketReply;

        $notificationService = new NotificationsService();
        $notification = $notificationService->saveCommentNotification($ticket['id'], $user['id'], $ticketReply['id']);
        $notificationService->userRepliesTo($ticketReply, $mailbox['id'], $ticket['assign_agent_id'], $notification);

        return Response::make(json_encode(['success' => true,'data' => $ticketReply]), 200);
    }
}
