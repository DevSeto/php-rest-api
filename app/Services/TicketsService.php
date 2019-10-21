<?php

namespace App\Services;

use App\Exceptions\ForbiddenToMailboxException;
use App\Exceptions\TicketNotFoundException;
use App\Helpers\Helper;
use App\Models\CompanySetting;
use App\Models\Customers;
use App\Models\MergedTickets;
use App\Models\Notes;
use App\Models\Notifications;
use App\Models\NotifiedUsers;
use App\Models\TicketComments;
use App\Models\TicketLabels;
use App\Models\Tickets;
use App\Models\TicketSnooze;
use App\Models\TicketsVisibility;
use App\Models\User;
use Carbon\Carbon;
use DB;

class TicketsService
{
    public $commentsService;
    protected $filters = ['old_updated', 'new_updated', 'old_created'];


    public function __construct(TicketCommentsService $commentsService)
    {
        $this->commentsService = $commentsService;
    }

    /**
     * @param $status
     * @param $mailbox_id
     * @param null $assignedToUser
     * @param null $filters
     * @param $userId
     * @return array
     * @throws ForbiddenToMailboxException
     */

    public function getTickets($status, $mailbox_id, $assignedToUser = null, $filters = null, $userId)
    {

        if (!MailboxService::userHasPermission($userId, $mailbox_id)) {
            throw new ForbiddenToMailboxException();
        }

        if (!empty($filters['label'])) {
            $ticketIds = TicketLabels::where('label_id', $filters['label'])->pluck('ticket_id');
            if (!empty($ticketIds)) {
                $tickets = Tickets::whereIn('id', $ticketIds->toArray())->where('mailbox_id', $mailbox_id)->with(['labels', 'opened']);
            }
        } else {
            $tickets = Tickets::where('status', $status)->where('mailbox_id', $mailbox_id)->with(['labels', 'opened']);
        }

        if (!empty($assignedToUser)) {
            $tickets->where('assined_agent_id', $assignedToUser);
        }

        $total = $tickets->count();

        if (!empty($filters['count'])) {
            $tickets = $tickets->limit($filters['count']);
        }

        if (!empty($filters['offset'])) {
            $tickets = $tickets->offset($filters['offset']);
        }

        // filtering by url filter date

        if (isset($filters['date'])) {
            switch ($filters['date']) {
                case $this->filters[0]:
                    $tickets->orderBy('updated_at', 'asc');
                    break;
                case $this->filters[1]:
                    $tickets->orderBy('updated_at', 'desc');
                    break;
                case $this->filters[2]:
                    $tickets->orderBy('id', 'asc');
                    break;
                default :
                    $tickets->orderBy('id', 'desc');
                    break;
            }
        }

        $result = [];
        if (!empty($tickets->get())) {
            $result = $tickets->get()->toArray();
        }

        foreach ($result as $key => $val) {
            $result[$key]['last_action'] = $this->getLastAction($val['id']);
        }

        return [
            'data' => $result,
            'total' => $total
        ];
    }

    /**
     * @param $tickets_id_hashs
     */

    public function deleteSelectedTickets($tickets_id_hashs)
    {
        $ticketIds = Tickets::whereIn('ticket_id_hash', $tickets_id_hashs)->pluck('id')->toArray();
        Tickets::whereIn('ticket_id_hash', $tickets_id_hashs)->delete();
        $notificationIds = Notifications::where('ticket_id',$ticketIds)->pluck('id')->toArray();

        if (!empty($notificationIds)){
            NotifiedUsers::whereIn('notification_id',$notificationIds)->delete();
            Notifications::where('ticket_id',$ticketIds)->delete();
        }

    }

    /**
     * @param $tickets_ids
     */

    public function restoreSelectedTickets($tickets_ids)
    {
        Tickets::whereIn('id', $tickets_ids)->restore();
    }

    /**
     * @param $ticketId
     * @return array
     */

    private function getLastAction($ticketId)
    {
        $last_comment = TicketComments::where('ticket_id', $ticketId)->with('files')->orderBy('id', 'desc')->first();
        $last_note = Notes::where('ticket_id', $ticketId)->with('files')->orderBy('id', 'desc')->first();

        $lastAction = [];

        if (!empty($last_comment) && !empty($last_note)) {
            if ($last_note['created_at'] > $last_comment['created_at']) {
                $lastAction['body'] = $last_note['note'];
                $lastAction['files'] = $last_note['files'];
                $lastAction['created_at'] = $last_note['created_at']->toDateTimeString();
            } else {
                $lastAction['body'] = $last_comment['body'];
                $lastAction['files'] = $last_comment['files'];
                $lastAction['created_at'] = $last_comment['created_at']->toDateTimeString();
            }
        } elseif (!empty($last_comment)) {
            $lastAction['body'] = $last_comment['body'];
            $lastAction['files'] = $last_comment['files'];
            $lastAction['created_at'] = $last_comment['created_at']->toDateTimeString();
        } elseif (!empty($last_note)) {
            $lastAction['body'] = $last_note['note'];
            $lastAction['files'] = $last_note['files'];
            $lastAction['created_at'] = $last_note['created_at']->toDateTimeString();
        }
        return $lastAction;
    }


    /**
     * create ticket in DB
     * @param $user_id
     * @param $mailbox
     * @param $customer
     * @param $data
     * @param $color
     * @return mixed
     */


    public function createTicket($user_id, $mailbox, $customer, $data, $color, $fromCustomer = false)
    {
        //create ticket
        $responseTickets = Tickets::create([
            'owner_id' => $user_id,
            'mailbox_id' => $mailbox['id'],
            'customer_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'customer_email' => $customer['email'],
            'customer_id' => $customer['id'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'message_id' => ' ',
            'ticket_id_hash' => ' ',
            'status' => !empty($data['status']) ? $data['status'] : 'open',
            'assign_agent_id' => !empty($data['assign_agent_id']) ? $data['assign_agent_id'] : 0,
            'color' => $color
        ]);

        //generate message id

        $message_id = '<' . md5($responseTickets->id) . '@' . Helper::$subDomain . env('PAGE_URL') . '>';

        //update message id field

        Tickets::find($responseTickets->id)->update([
            'message_id' => $message_id,
            'ticket_id_hash' => md5($responseTickets->id)
        ]);

        $ticket = Tickets::where('id', $responseTickets->id)->first()->toArray();

        //create first comment by ticket data
        $commentData = [
            'ticket_id' => $responseTickets->id,
            'from_name' => ($fromCustomer) ? $customer['first_name'] . ' ' . $customer['last_name'] : $mailbox['name'],
            'from_email' => ($fromCustomer) ? $customer['email'] : $mailbox['email'],
            'author_id' => $user_id,
            'body' => $data['body']
        ];

        $ticket['comment'] = $this->commentsService->createComment($commentData);
        $ticket['last_action'] = $this->getLastAction($responseTickets->id);

        return $ticket;
    }

    /**
     * @param $ticketId
     * @return static
     */

    public function setTicketViewed($ticketId)
    {
        $userId = Helper::$user['id'];
        return TicketsVisibility::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId
        ]);
    }

    /**
     * @param $hash
     * @return mixed
     * @throws TicketNotFoundException
     */

    public static function getTicketByHash($hash)
    {
        $ticket = Tickets::where('ticket_id_hash', $hash)->first();

        if (empty($ticket)) {
            throw new TicketNotFoundException();
        }

        return $ticket->toArray();
    }

    /**
     * @param $ticket
     * @return mixed
     */

    public function getTicketAuthor($ticket)
    {
        if ($ticket['owner_id'] == 0) {
            return Customers::where('id', $ticket['customer_id'])->first();
        } else {
            return User::where('id', $ticket['owner_id'])->first();
        }
    }

    /**
     * @param $ticketId
     * @param $offset
     * @param $count
     * @param bool $fromCustomer
     * @param bool $replies
     * @return mixed
     * @throws ForbiddenToMailboxException
     * @throws TicketNotFoundException
     */
    public function getAllTicketDataById($ticketId, $offset, $count, $fromCustomer = false, $replies = false)
    {
        $ticket = Tickets::where('id',$ticketId)->first();
        if (!$fromCustomer) {
            if (empty($ticket)) {
                throw new TicketNotFoundException();
            } elseif (!MailboxService::userHasPermission(Helper::$user['id'], $ticket->mailbox_id)) {
                throw new ForbiddenToMailboxException();
            }
        }

        $res = $this->getTimeline($ticketId, $offset, $count,$replies);
//        dd(78);

        $dataTicket = Tickets::where('id', $ticketId)->with(['assignedUser', 'files', 'labels'])
            ->withCount(['comment', 'notes'])
            ->first()
            ->toArray();

        if (!empty($dataTicket['data_draft']['forwarding_emails'])) {
            $dataTicket['data_draft']['forwarding_emails'] = array_map(function ($q) {
                return $q['email'];
            }, $dataTicket['data_draft']['forwarding_emails']);
        }

        $res['ticket_data'] = $dataTicket;
        if ($replies) {
            $res['ticket_data']['body'] = $res['firstReply'];
        }

        $res['ticket_data']['server_time'] = Carbon::now()->toDateTimeString();
        return $res;
    }

    public function getTimeline($ticketId,$offset,$count,$replies,$trashed = false){
        if ($trashed){
            $ticket = Tickets::with(['notes', 'comments', 'files', 'mergedTickets'])->where('id', $ticketId)->withTrashed()->first()->toArray();
        }else{
            $ticket = Tickets::with(['notes', 'comments', 'files', 'mergedTickets'])->where('id', $ticketId)->first()->toArray();
        }
        $ticketData = [];
        $ticketData['notes'] = $ticket['notes'];
        $ticketData['comments'] = $ticket['comments'];
        $ticketData['merged_tickets'] = $ticket['merged_tickets'];
        $ticketData['author'] = $this->getTicketAuthor($ticket);

        //add type to notes
        if (!empty($ticketData['notes'])) {
            foreach ($ticketData['notes'] as $key_note => $note) {
                $ticketData['notes'][$key_note]['type'] = 'note';
            }
        }

        //add type to comment
        if (!empty($ticketData['comments'])) {
            foreach ($ticketData['comments'] as $key_comment => $comment) {
                $ticketData['comments'][$key_comment]['type'] = 'comment';
                $ticketData['comments'][$key_comment]['forwarding_addresses'] = json_decode($ticketData['comments'][$key_comment]['forwarding_addresses'], true);
            }
        }

        //add type to merge
        $arr = array();
        foreach ($ticketData['merged_tickets'] as $key => $item) {
            $arr[$item['batch']]['merged_tickets_data'] = $item;
            $arr[$item['batch']]['created_at'] = $item['created_at'];
            $arr[$item['batch']]['type'] = 'merge';
        }

        ksort($arr, SORT_NUMERIC);

        //merge data arrays
        $result = array_merge($ticketData['notes'], $ticketData['comments'], $arr);
        usort($result, array($this, 'date_compare'));
        $res['firstReply'] = [];
        if ($replies) {
            $res['firstReply'] = array_shift($result);
        }

        $index = $offset - 1;
        $res['timeline'] = array_slice(array_reverse($result), $index, $count);
        return $res;
    }

    /**
     * @param $commentId
     * @param $transmissionId
     */
    public function updateCommentTransmissionId($commentId, $transmissionId)
    {
        $this->commentsService->updateCommentTransmissionId($commentId, $transmissionId);
    }

    /**
     * @param $assignId
     * @param $ticket
     * @return mixed
     */
    public static function assignTicket($assignId, $ticket)
    {
        Tickets::where('id', $ticket['id'])->update(['assign_agent_id' => $assignId]);
        if ($assignId != 0) {
            $userNotificationsService = new NotificationsService();
            $savedNotification = $userNotificationsService->saveAssignTicketNotification($ticket['id'], Helper::$user['id'], $assignId);
            $userNotificationsService->assignedTicket($ticket, $assignId, $savedNotification);
        }

        //add to ticket history
        TicketHistoryService::assignTicketHistory($ticket['id'], Helper::$user['id'], null, $assignId);

        return true;
    }

    /**
     * @param $newAssignAgentId
     * @param $tickets
     * @param $user
     * @param bool $note
     * @return bool|mixed
     */
    public static function processAssign($newAssignAgentId, $tickets, $user, $note = false)
    {
        foreach ($tickets as $ticket) {
            if ($newAssignAgentId == $user['id']) {
                if ($newAssignAgentId == $ticket['assign_agent_id']) {
                    return true;
                } else {
                    return self::assignTicket($user['id'], $ticket);
                }
            }

            if ($newAssignAgentId == $ticket['assign_agent_id']) {
                //prefs assign after reply
                if ($note) {
                    if (UserPreferenceService::assignAfterNote($user['id'])) {
                        return self::assignTicket($user['id'], $ticket);
                    }
                } else {
                    if (UserPreferenceService::assignAfterReply($user['id'])) {
                        return self::assignTicket($user['id'], $ticket);
                    }
                }
            } else {
                return self::assignTicket($newAssignAgentId, $ticket);
            }
        }
    }

    /**
     * @param $customer
     * @param $ticket
     * @param $mailbox
     * @param null $attachedFiles
     * @return array
     */
    public function prepareDataForSendEmail($customer, $ticket, $mailbox, $attachedFiles = null)
    {
        //get mailbox pretty signature
        $mailbox['signature'] = MailboxService::getMailboxPrettySignature(Helper::$user, $mailbox);

        return [
            'toEmail' => $customer['email'],
            'toName' => $customer['first_name'] . ' ' . $customer['last_name'],
            'fromEmail' => $mailbox['email'],
            'fromName' => $mailbox['name'],
            'subject' => $ticket['subject'],
            'messageId' => $ticket['message_id'],
            'attachedFiles' => (!empty($attachedFiles)) ? $attachedFiles : [],
            'commentText' => $ticket['body'] . "<br><div> " . $mailbox['signature'] . " </div>",
            'reply_to' => ($mailbox['forwarding_verified'] == 1) ? $mailbox['email'] : $mailbox['forward_address']
        ];
    }

    /**
     * @param $ticketIdHashs
     * @param $status
     * @param bool $new
     * @return mixed
     */
    public static function changeStatus($ticketIdHashs, $status, $new = false)
    {
        $update = Tickets::whereIn('ticket_id_hash', $ticketIdHashs)->update(['status' => $status]);

        //add history with a author
        foreach ($ticketIdHashs as $ticketIdHash) {
            $ticketId = Tickets::where('ticket_id_hash', $ticketIdHash)->first()['id'];
            TicketHistoryService::statusTicketHistory($ticketId, Helper::$user['id'], null, $status);
        }

        if (!$new) {
            self::removeSnooze([$ticketIdHashs]);
        }

        return $update;
    }

    /**
     * @param $a
     * @param $b
     * @return false|int
     */
    function date_compare($a, $b)
    {
        $t1 = strtotime($a['created_at']);
        $t2 = strtotime($b['created_at']);

        return $t1 - $t2;
    }

    /**
     * @param $ticketIdHashs
     */

    public static function removeSnooze($ticketIdHashs)
    {
        Tickets::whereIn('ticket_id_hash', $ticketIdHashs)->update(['snooze' => null]);

        DB::setDefaultConnection('mysql');
        TicketSnooze::whereIn('ticket_id_hash', $ticketIdHashs)->delete();
        Helper::changeDataBaseConnection(Helper::$subDomain);
    }

    /**
     * @param $ticketIdHashs
     * @param $time
     * @return mixed
     */
    public function setSnooze($ticketIdHashs, $time)
    {
        $timing = [
            '1' => Carbon::now()->addHours(4)->toDateTimeString(),
            '2' => Carbon::now()->addDay(1)->setTime('9', '0', '0')->toDateTimeString(),
            '3' => Carbon::now()->addDay(1)->setTime('13', '0', '0')->toDateTimeString(),
            '4' => Carbon::now()->addDays(2)->toDateTimeString(),
            '5' => Carbon::now()->addDays(4)->toDateTimeString(),
            '6' => Carbon::now()->addDays(7)->toDateTimeString()
        ];

        Tickets::whereIn('ticket_id_hash', $ticketIdHashs)->update([
            'snooze' => $timing[$time],
            'status' => 'closed'
        ]);

        DB::setDefaultConnection('mysql');

        $ticketIdHashData = [];
        foreach ($ticketIdHashs as $ticket_id_hash) {
            array_push($ticketIdHashData, [
                'ticket_id_hash' => $ticket_id_hash,
                'sub_domain' => Helper::$subDomain,
                'snooze' => $timing[$time]
            ]);
        }
        Helper::insertTo('ticket_snoozes', $ticketIdHashData);

        return $timing[$time];
    }

    /**
     * @param $labels
     * @param $ticketId
     * @return array
     */
    public function stickLabels($labels, $ticketId)
    {
        $ticketLabels = [];
        Tickets::find($ticketId)->touch();
        foreach ($labels as $label) {
            // stick label to a ticket
            array_push($ticketLabels, [
                'ticket_id' => $ticketId,
                'label_id' => $label
            ]);
            //add to ticket history
        }
        Helper::insertTo('ticket_labels', $ticketLabels);
        return $ticketLabels;
    }

    /**
     * @param $ticketIdHash
     * @return mixed
     * @throws TicketNotFoundException
     */
    public function getTicketsForMerge($ticketIdHash)
    {
        $ticket = Tickets::where('ticket_id_hash', $ticketIdHash)->first();

        if (empty($ticket)) {
            throw new TicketNotFoundException();
        }

        $result = Tickets::where('customer_email', $ticket->customer_email)
            ->where('mailbox_id', $ticket->mailbox_id)
            ->where('ticket_id_hash', '!=', $ticketIdHash)
            ->get();

        if (!empty($result)) {
            $result = $result->toArray();
        }

        return $result;
    }

    /**
     * @param $ticketId
     * @param $labelId
     * @return mixed
     */
    public function removeLabelFromTicket($ticketId, $labelId)
    {
        return TicketLabels::where('label_id', $labelId)->where('ticket_id', $ticketId)->delete();
    }

    /**
     * @param $masterTicket
     * @param $ticketIdHash
     * @return mixed
     * @throws TicketNotFoundException
     */
    public function mergeTickets($masterTicket, $ticketIdHash)
    {
        $batch = MergedTickets::max('batch') + 1;

        $mergedTicketsId = Tickets::where('ticket_id_hash', $ticketIdHash)->pluck('id')->toArray();
        if (!empty($mergedTicketsId)) {
            $mergedTicketsId = $mergedTicketsId[0];
        } else {
            throw new TicketNotFoundException();
        }
        $notificationIds = Notifications::where('ticket_id',$mergedTicketsId)->pluck('id')->toArray();

        if (!empty($notificationIds)){
            NotifiedUsers::whereIn('notification_id',$notificationIds)->delete();
            Notifications::where('ticket_id',$mergedTicketsId)->delete();
        }

        //set merged
        Tickets::where('ticket_id_hash', $ticketIdHash)->update(['merged' => 1]);
        //soft delete
        Tickets::where('ticket_id_hash', $ticketIdHash)->delete();

        //delete merged tickets notifications *

        TicketLabels::where('ticket_id', $mergedTicketsId)->update(['ticket_id' => $masterTicket['id']]);

        MergedTickets::create([
            'master_ticket_id' => $masterTicket['id'],
            'ticket_id' => $mergedTicketsId,
            'batch' => $batch
        ]);

        MergedTickets::where('master_ticket_id', $mergedTicketsId)->update(['master_ticket_id' => $masterTicket['id']]);

        //add to history *

        Tickets::find($masterTicket['id'])->update(['merged' => 2]);
//        $result['data'] = Tickets::with(['mergedTickets' => function ($query) use ($batch) {
//            $query->where('merged_tickets.batch', $batch);
//        }, 'labels'])->where('id', $masterTicket['id'])->first();

        $result['data'] = Tickets::with(['mergedTickets','labels'])->where('id', $masterTicket['id'])->first();

        $result['ticketIds'] = $mergedTicketsId;
        return $result;
    }

    /**
     * delete old tickets
     */
    public function deleteDelayedTickets()
    {
        $last_trash_cleared = CompanySetting::first()['last_trash_clear'];

        if ($last_trash_cleared < Carbon::now()->toDateString()) {
            $deleteDay = Carbon::now()->addDays(-30)->toDateString();
            $ticketsForDelete = Tickets::where('merged', 0)->where('deleted_at', '<', $deleteDay)->onlyTrashed()->get()->pluck('id')->toArray();
            $mergedTicketsToDelete = Tickets::where('merged', 2)->where('deleted_at', '<', $deleteDay)->onlyTrashed()->get()->pluck('id')->toArray();
            if (!empty($mergedTicketsToDelete)) {
                foreach ($mergedTicketsToDelete as $masterTicket) {
                    $mergetTicketIds = MergedTickets::where('master_ticket_id', $masterTicket)->pluck('ticket_id')->toArray();
                    $ticketsForDelete = array_merge($ticketsForDelete, $mergetTicketIds);
                }
            }

            $ticketsForDelete = array_merge($ticketsForDelete, $mergedTicketsToDelete);

            Tickets::whereIn('id', $ticketsForDelete)->forcedelete();
            if (!is_null(CompanySetting::first())) {
                CompanySetting::first()->update([
                    'last_trash_clear' => Carbon::now()->toDateString()
                ]);
            }
        }
    }

    /**
     * @param $mailboxId
     * @return mixed
     */
    public function getDeletedTickets($mailboxId)
    {
        return Tickets::whereIn('merged', [0, 2])->where('mailbox_id', $mailboxId)->onlyTrashed()->get()->toArray();
    }

    /**
     * @param array $urlParams
     * @return string
     */
    private function removeEmptyValuesOfUrlParams($urlParams)
    {
        $query = "";
        foreach ($urlParams as $key => $value) {
            if (!empty($value)) {
                $query .= empty($query) ? "" : " ";
                $query .= str_replace(",", " ", $value);
            }
        }
        return $query;
    }

    /**
     * @param $urlParams
     * @return mixed
     */
    public function search($urlParams)
    {
        $query = $this->removeEmptyValuesOfUrlParams($urlParams);
        return Tickets::search($query)->get();
    }

}