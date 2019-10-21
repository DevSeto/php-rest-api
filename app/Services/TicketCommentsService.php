<?php

namespace App\Services;


use App\Helpers\Helper;
use App\Helpers\SendEmailService;
use App\Models\TicketComments;
use App\Models\Tickets;

class TicketCommentsService
{

    public function createComment($data){
        Tickets::find($data['ticket_id'])->touch();

        return TicketComments::create($data)->toArray();
    }

    public function updateCommentTransmissionId($commentId,$transmissionId){
        TicketComments::where('id', $commentId)->update(['transmission_id' => $transmissionId]);
    }

    public function sendForwardingComments($commentId,$forwardingEmails,$data){

        TicketComments::where('id', $commentId)->update([
            'is_forwarded' => '1',
            'forwarding_addresses' => json_encode($forwardingEmails)
        ]);

        foreach ($forwardingEmails as $forwarding_email) {
            if ($data['toEmail'] != $forwarding_email) {
                $options = [
                    'toEmail' => $forwarding_email,
                    'toName' => Helper::$subDomain,
                    'fromEmail' => $data['fromEmail'],
                    'fromName' => $data['fromName'],
                    'subject' => $data['subject'],
                    'messageId' => $data['messageId'],
                    'attachedFiles' => (!empty($data['attachedFiles'])) ? $data['attachedFiles'] : [],
                    'commentText' => $data['commentText'],
                    'replyEmail' => $data['replyEmail'],
                    'reply_to' => $data['reply_to']
                ];

                if ($data['is_demo'] != 1) {
                    SendEmailService::sendEmail($options);
                }
            }
        }
    }

    public function prepareDataForSendEmail($ticket,$mailbox,$body,$attachedFiles = null){
        //get mailbox pretty signature
        $mailbox['signature'] = MailboxService::getMailboxPrettySignature(Helper::$user, $mailbox);

        return [
            'toEmail' => $ticket['customer_email'],
            'toName' => $ticket['customer_name'],
            'fromEmail' => $mailbox['email'],
            'fromName' => $mailbox['name'],
            'subject' => $ticket['subject'],
            'attachedFiles' => (!empty($attachedFiles)) ? $attachedFiles : [],
            'messageId' => $ticket['message_id'],
            'commentText' => $body . "<br><div> " . $mailbox['signature'] . " </div>",
            'replyEmail' => $mailbox['email'],
            'reply_to' => ($mailbox['forwarding_verified'] == 1) ? $mailbox['email'] : $mailbox['forward_address']
        ];
    }
}