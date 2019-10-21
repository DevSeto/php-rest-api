<?php

namespace App\Http\Controllers\Tickets;

use App\Exceptions\ForbiddenToMailboxException;
use App\Exceptions\TicketNotFoundException;
use App\Exceptions\WrongCustomerNameException;
use App\Helpers\CustomersHelper;
use App\Helpers\FilesHelper;
use App\Helpers\SendEmailService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeStatusRequest;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\MergeTicketsRequest;
use App\Http\Requests\SetSnoozeRequest;
use App\Models\Label;
use App\Models\Mailbox;
use App\Models\MailboxUserPermissions;
use App\Services\MailboxService;
use App\Services\NotificationsService;
use App\Services\TicketHistoryService;
use App\Services\TicketsService;
use App\Services\UserNotificationsService;
use Illuminate\Http\Request;
use App\Models\Tickets;
use App\Models\TicketFiles;
use App\Helpers\Helper;
use Response;
use App\Http\Requests\CreateTicketRequest;
use App\Helpers\Crypto;
use Illuminate\Support\Facades\Lang;
use DB;


class TicketController extends Controller
{
    protected $subDomain;
    protected $ticketsService;
    protected $userNotificationsService;

    function __construct(TicketsService $ticketsService, NotificationsService $userNotificationsService)
    {
        $this->middleware('check_token');
        $this->subDomain = Helper::$subDomain;
        $this->ticketsService = $ticketsService;
        $this->userNotificationsService = $userNotificationsService;
    }

    /**
     * @param int $mailbox_id
     * @param Request $request
     * @return mixed
     */
    public function index($mailbox_id = 1, Request $request)
    {
        $user = Helper::$user;
        //get url params
        $status = ($request->has('status')) ? $request->get('status') : 'open';
        $assignedToUser = ($request->has('assigned_user_id')) ? $request->get('assigned_user_id') : '';
        $filters['date'] = ($request->has('filter')) ? $request->get('filter') : '';
        $filters['count'] = ($request->has('count')) ? $request->get('count') : 15;
        $filters['offset'] = ($request->has('offset')) ? $request->get('offset') : '';
        $filters['label'] = ($request->has('label')) ? $request->get('label') : '';

        //get tickets
        try {
            $tickets = $this->ticketsService->getTickets($status, $mailbox_id, $assignedToUser, $filters, $user['id']);
        } catch (ForbiddenToMailboxException $e) {
            return Helper::send_error_response('customer_name', $e->getMessage(), 422);
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => $tickets['data'],
            'total' => $tickets['total']
        ]), 200);
    }

    /**
     *
     * @param CreateTicketRequest $request
     * @return mixed
     */
    public function create(CreateTicketRequest $request)
    {
        $data = $request->request->all();
        $user = Helper::$user;

        //if not isset mailbox_id, get default mailbox id 1
        $mailbox_id = !empty($data['mailbox_id']) ? $data['mailbox_id'] : 1;

        //get customer if exist or create new customer
        try {
            $customer = CustomersHelper::getCustomer($data['customer_email'], $data['customer_name']);
        } catch (WrongCustomerNameException $e) {
            return Helper::send_error_response('customer_name', $e->getMessage(), 422);
        }

        //get random color for ticket
        $color = Helper::getRandomColor();

        //get mailbox
        try {
            $mailbox = MailboxService::getMailboxById($mailbox_id, $user['id']);
        } catch (\Exception $e) {
            return Helper::send_error_response('mailbox', $e->getMessage(), 422);
        }
        //create ticket in DB
        $ticket = $this->ticketsService->createTicket($user['id'], $mailbox, $customer, $data, $color);

        //add to history
        TicketHistoryService::createTicketHistory($ticket['id'], $user['id'], null);

        //handle attached files
        $ticket['files'] = [];

        if (isset($data['attachments']) && !empty($data['attachments'])) {
            $ticket['files'] = FilesHelper::handleAttachedFiles($data['attachments'], $ticket['ticket_id_hash'], $ticket['comment']['id']);
        }

        //send Email to customer

        $options = $this->ticketsService->prepareDataForSendEmail($customer, $ticket, $mailbox, $ticket['files']);

        $options['cc'] = !empty($data['cc']) ? $data['cc'] : '';
        $options['bcc'] = !empty($data['bcc']) ? $data['bcc'] : '';
//        dd($options);
        try{
            $send_email = SendEmailService::sendEmail($options);
        } catch (\Exception $e){
            dd($e->getResponse()->json());
        }
        $transmissionId = $send_email->getHeaders()['X-Message-Id'][0];

        if ($send_email) {
            $this->ticketsService->updateCommentTransmissionId($ticket['comment']['id'], $transmissionId);
        }

        //if isset labels stick them
        $ticket['labels'] = [];
        if (!empty($data['labels'])) {
            $ticket['labels'] = $this->ticketsService->stickLabels($data['labels'], $ticket['id']);
        }

        //send notifications to other users
        $ticket['opened'] = '';
        $ticket['author'] = $user;
        $ticket['customer'] = '';
        $savedNotification = $this->userNotificationsService->saveCreateTicketNotification($ticket['id'], $ticket['comment']['id'], $user['id']);
        $this->userNotificationsService->isNewTicket($ticket, $savedNotification);

        //assign to someone if needed
        if (isset($data['assign_agent_id']) && !empty($data['assign_agent_id'])) {
            TicketsService::assignTicket($data['assign_agent_id'], $ticket);
        }

        //set status
        if (isset($data['status']) && !empty($data['status'])) {
            TicketsService::changeStatus([$ticket['ticket_id_hash']], $data['status'], true);
        }

        //author viewed the
        $ticket['opened'] = $this->ticketsService->setTicketViewed($ticket['id']);

        return Response::make(json_encode([
            'success' => true,
            'data' => $ticket
        ]), 200);
    }

    /**
     * Get ticket information.
     * @param Request $request
     * @param $ticketId
     * @return mixed
     */
    public function show(Request $request, $ticketId)
    {
        $this->ticketsService->deleteDelayedTickets();
        $offset = !empty($request->input('offset')) ? $request->input('offset') : 1; // current page of pagination
        $count = !empty($request->input('count')) ? $request->input('count') : 10; // count of items in request
        $replies = !empty($request->input('replies')) ? $request->input('replies') : false;

        try {
            $ticket = $this->ticketsService->getAllTicketDataById($ticketId, $offset, $count, false, $replies);
        } catch (\Exception $e) {
            return Helper::send_error_response('ticket', $e->getMessage(), 422);
        }

        // set viewed all notifications on this ticket
        $this->userNotificationsService->setAllNotificationsViewedByTicketId($ticketId);

        return Response::make(json_encode([
            'success' => true,
            'data' => $ticket
        ]), 200);
    }

    public function getTimeLine(Request $request,$ticketId){
        $offset = !empty($request->input('offset')) ? $request->input('offset') : 1; // current page of pagination
        $count = !empty($request->input('count')) ? $request->input('count') : 10; // count of items in request
        $replies = !empty($request->input('replies')) ? $request->input('replies') : false;

        $result = $this->ticketsService->getTimeline($ticketId,$offset,$count,$replies,true);

        return Response::make(json_encode([
            'success' => true,
            'data' => $result
        ]), 200);
    }

    /**
     * Delete all tickets.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(Request $request)
    {
        $data = $request->all();
        $ticketsIdHashs = $data['tickets_id_hashs'];
        $this->ticketsService->deleteSelectedTickets($ticketsIdHashs);

        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * assign tickets to agent
     *
     * @param integer $userId
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function assignTicket(Request $request, $userId = 0)
    {
        $data = $request->all();
        $ticketsIdHashs = $data['tickets_id_hashs'];
        if (!empty($ticketsIdHashs)) {
            try {
                foreach ($ticketsIdHashs as $ticket_id_hash) {
                    $ticket = TicketsService::getTicketByHash($ticket_id_hash);
                    TicketsService::assignTicket($userId, $ticket);
                }
            } catch (TicketNotFoundException $e) {
                return Helper::send_error_response('ticket', $e->getMessage(), 422);
            }
        }

        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * TicketFileUpload
     * @param FileUploadRequest $request
     * @return mixed
     */
    public function uploadFile(FileUploadRequest $request)
    {
        $file = $request->file('file');
        $result = FilesHelper::uploadFile($file);

        return Response::make(json_encode([
            'success' => true,
            'link' => $result
        ]), 200);
    }

    /**
     * Remove Ticket file
     *
     * @param  int $ticketId
     * @param  \Illuminate\Http\Request $request
     * @param  int $fileId
     *
     * @return \Illuminate\Http\Response
     * ToDO must change
     */
    public function deleteUploadedFile($ticketId, Request $request, $fileId)
    {
        $file = TicketFiles::find($fileId);
        $fileType = $file->file_type;
        $path = public_path('uploads/' . $this->subDomain . '/ticket/files/' . $file->file_name);

        if (!empty($file)) {
            if (file_exists(public_path('uploads/' . $this->subDomain . '/ticket/files/' . $file->file_name))) {
                $deleteFile = unlink(public_path('uploads/' . $this->subDomain . '/ticket/files/' . $file->file_name));
                $ticketFiles = TicketFiles::find($fileId);
                if (!empty($ticketFiles)) {
                    $ticketFiles->delete();
                    return Response::make(json_encode(['success' => true]), 200);
                }
                return Helper::send_error_response('wrong_file_id', Lang::get('ticket.wrong_file_id'), 422);
            }
            return Helper::send_error_response('missing_file_name', Lang::get('ticket.missing_file_name'), 422);
        }
        return Helper::send_error_response('wrong_file_id', Lang::get('ticket.wrong_file_id'), 422);
    }


    /**
     * Get merged tickets
     *
     * @param int $masterTicketIdHash
     *
     * @return \Illuminate\Http\Response
     */
    public function getTicketsToMerge($masterTicketIdHash)
    {
        try {
            $ticketsForMerge = $this->ticketsService->getTicketsForMerge($masterTicketIdHash);
        } catch (TicketNotFoundException $e) {
            return Helper::send_error_response('ticket', $e->getMessage(), 422);
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => $ticketsForMerge
        ]), 200);
    }

    /**
     * @param MergeTicketsRequest $request
     * @param $masterTicketIdHash
     * @return mixed
     */
    public function mergeTickets(MergeTicketsRequest $request, $masterTicketIdHash)
    {
        try {
            $ticket = TicketsService::getTicketByHash($masterTicketIdHash);
        } catch (TicketNotFoundException $e) {
            return Helper::send_error_response('ticket', $e->getMessage(), 422);
        }

        $mergedTicketData = $this->ticketsService->mergeTickets($ticket, $request->all()['tickets_id_hashs'][0]);

        //add to history
        TicketHistoryService::mergeTicketHistory($ticket['id'], Helper::$user['id'], null, json_encode($mergedTicketData['ticketIds']));

        return Response::make(json_encode([
            'success' => true,
            'data' => $mergedTicketData
        ]), 200);
    }


    /**
     * Stick labels
     *
     * @param  \Illuminate\Http\Request $request
     * @param integer $labelId
     *
     * @return \Illuminate\Http\Response
     */
    public function stickLabel(Request $request, $labelId)
    {
        $ticketIds = $request->all()['ticket_ids'];
        $stickLabelsOfTicket = [];
        $label = Label::find($labelId);
        if (empty($label)) {
            return Helper::send_error_response('label_id', Lang::get('labels.wrong_id'), 404);
        }

        if (empty($ticketIds)) {
            return Response::make(json_encode(['success' => false]), 200);
        }

        foreach ($ticketIds as $key => $ticketId) {
            $stickLabelsOfTicket = $this->ticketsService->stickLabels([$labelId], $ticketId);
        }

        return Response::make(json_encode([
            'success' => true,
            'data' => $stickLabelsOfTicket
        ]), 200);
    }

    /**
     * remove label from ticket
     * @param $labelId
     * @param $ticketId
     * @return mixed
     */
    public function removeLabel($labelId, $ticketId)
    {
        $result = $this->ticketsService->removeLabelFromTicket($ticketId, $labelId);
        return Response::make(json_encode(['success' => $result]), 200);
    }

    /**
     * @param $ticketIdHash
     * @return mixed
     */
    public function setTicketViewed($ticketIdHash)
    {
        try {
            $ticket = TicketsService::getTicketByHash($ticketIdHash);
        } catch (TicketNotFoundException $e) {
            return Helper::send_error_response('ticket', $e->getMessage(), 422);
        }

        $this->ticketsService->setTicketViewed($ticket['id']);

        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * returns all tickets without user created
     * @param Request $request
     * @return mixed
     *
     */
    public function getTicketsForNotifications(Request $request)
    {
        $urlParams = $request->all();
        $ownerId = Helper::$user->toArray()['id'];
        $availableMailboxArray = MailboxUserPermissions::where('user_id', $ownerId)
            ->get()
            ->pluck('mailbox_id')
            ->toArray();

        $count = (empty($urlParams['count'])) ? 5 : $urlParams['count'];
        $offset = (empty($urlParams['offset'])) ? 0 : $urlParams['offset'];

        array_push($availableMailboxArray, 0);

        $tickets = Tickets::whereIn('mailbox_id', $availableMailboxArray)
            ->where('owner_id', '!=', $ownerId)
            ->offset($offset)
            ->limit($count)
            ->orderBy('id', 'desc')
            ->get();

        $data = [
            'tickets' => (!empty($tickets->toArray())) ? $tickets->toArray() : [],
            'total_new' => Tickets::where('viewed', '0')
                ->where('owner_id', '!=', $ownerId)
                ->whereIn('mailbox_id', $availableMailboxArray)
                ->get()
                ->count(),
            'total' => Tickets::whereIn('mailbox_id', $availableMailboxArray)
                ->where('owner_id', '!=', $ownerId)
                ->get()
                ->count()
        ];

        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($data) ? $data : []
        ]), 200);
    }

    /**
     * @param $time
     * @param SetSnoozeRequest $request
     * @return mixed
     */
    public function setSnooze($time, SetSnoozeRequest $request)
    {
        $data = $request->all()['tickets_id_hashs'];

        $snoozeTime = $this->ticketsService->setSnooze($data, $time);

        return Response::make(json_encode(['success' => true, 'snoozeData' => $snoozeTime]), 200);
    }

    /**
     * Change ticket(s) status
     * @param ChangeStatusRequest $request
     * @return mixed
     */
    public function changeStatus(ChangeStatusRequest $request)
    {
        $data = $request->all();
        $ticketsIdHashs = $data['tickets_id_hashs'];
        $status = $data['status'];

        $result = TicketsService::changeStatus($ticketsIdHashs, $status);

        return Response::make(json_encode(['success' => $result]), 200);
    }

    /**
     * remove snooze by ticket id
     */
    public function removeSnoozeByTicketIdHash(Request $request)
    {
        $ticket_id_hashes = $request->all();

        TicketsService::removeSnooze($ticket_id_hashes);

        return Response::make(json_encode(['success' => true]), 200);
    }

    public function getDeletedTicketsByMailboxId($mailboxId)
    {
        $deletedTickets = $this->ticketsService->getDeletedTickets($mailboxId);
        return Response::make(json_encode([
            'success' => true,
            'data' => $deletedTickets
        ]), 200);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function restoreTickets(Request $request)
    {
        $data = $request->all();
        $ticketsIds = $data['ticket_ids'];

        $this->ticketsService->restoreSelectedTickets($ticketsIds);

        return Response::make(json_encode(['success' => true]), 200);
    }

    public function searchTickets(Request $request)
    {
        $key = $request->all();

        $result = $this->ticketsService->search($key);
        return Response::make(json_encode(['success' => $result]), 200);
    }

}
