<?php

namespace App\Http\Controllers\Tickets;

use App\Exceptions\TicketNotFoundException;
use App\Helpers\FilesHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNoteRequest;
use App\Models\Mailbox;
use App\Models\UserNotifications;
use App\Services\NoteService;
use App\Services\NotificationsService;
use App\Services\TicketHistoryService;
use App\Services\TicketsService;
use App\Models\Notes;
use App\Helpers\Helper;
use Response;
use Lang;

class NoteController extends Controller
{

    protected $noteService;
    protected $userNotificationsService;

    function __construct(NoteService $noteService, NotificationsService $userNotificationsService)
    {
        $this->middleware('check_token');
        $this->noteService = $noteService;
        $this->userNotificationsService = $userNotificationsService;
    }

    /**
     * Get all notes
     *
     * @param int $ticketId
     *
     * @return \Illuminate\Http\Response
     */
    public function index($ticketId)
    {
        $notes = $this->noteService->getNotesByTicketId($ticketId);

        return Response::make(json_encode([
            'success' => true,
            'content' => $notes
        ]), 200);
    }

    /**
     * Create new note.
     *
     * @param  CreateNoteRequest $request
     * @param  string $ticketIdHash
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateNoteRequest $request, $ticketIdHash)
    {
        try {
            $ticket = TicketsService::getTicketByHash($ticketIdHash);
        } catch (TicketNotFoundException $e) {
            return Helper::send_error_response('ticket', $e->getMessage(), 422);
        }

        $author = Helper::$user;
        $data = $request->all();
        $note = Notes::create([
            'ticket_id' => $ticket['id'],
            'author_id' => $author['id'],
            'note' => $data['note']
        ])->toArray();

        $dataNotifications = $note;
        $dataNotifications['author'] = $author;
        if (isset($data['assign_agent_id']) && (!empty($data['assign_agent_id']) || $data['assign_agent_id'] == 0)) {
            TicketsService::processAssign($data['assign_agent_id'], [$ticket], $author, true);
        }

        if (!empty($data['status'])) {
            TicketsService::changeStatus([$ticket['ticket_id_hash']], $data['status']);
        }

        $note['files'] = [];

        if (isset($data['attachments']) && !empty($data['attachments'])) {
            $note['files'] = FilesHelper::handleAttachedFiles($data['attachments'], $ticket['ticket_id_hash'], null, $note['id']);
        }

        $note['author'] = $author;
        //add to history
        TicketHistoryService::noteTicketHistory($ticket['id'], $author['id']);

        /***
         *
         * Send Notification
         *
         **/
        // Send notification to mentioned users
        $mentionedUsers = $this->noteService->getMentionedUsersIDs($data['note']);
        if (!empty($mentionedUsers)) {
            $notifications = $this->userNotificationsService->saveMentionedUsersNotification($mentionedUsers, $ticket['id'], $note['id'], Helper::$user['id']);
            $this->userNotificationsService->mentionedInTicket($dataNotifications, $ticket['mailbox_id'], $mentionedUsers, $notifications, $ticket['id']);
        }

        // send notifications to users
        $ticket['data_note'] = $note;
        $notification = $this->userNotificationsService->saveNoteNotification($ticket['id'], $note['id'], Helper::$user['id']);
        $this->userNotificationsService->userAddNote($dataNotifications, $ticket['mailbox_id'], $ticket['assign_agent_id'], $notification);
        return Response::make(json_encode([
            'success' => true,
            'data' => $note
        ]), 200);
    }

    /**
     * Get note.
     *
     * @param  int $ticketId
     * @param  int $noteId
     *
     * @return \Illuminate\Http\Response
     */
    public function show($ticketId, $noteId)
    {
        $note = $this->noteService->getNoteByTicketIdAndNoteId($ticketId, $noteId);

        return Response::make(json_encode([
            'success' => true,
            'content' => $note
        ]), 200);
    }

}
