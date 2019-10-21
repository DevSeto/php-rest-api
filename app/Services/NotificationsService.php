<?php

namespace App\Services;

use App\Helpers\SendEmailService;
use App\Models\Mailbox;
use App\Models\MailboxUserPermissions;
use App\Models\Notes;
use App\Models\Notifications;
use App\Models\TicketComments;
use App\Models\TicketFiles;
use App\Models\Tickets;
use App\Models\UserNotifications;
use App\Models\User;
use App\Helpers\Helper;
use Response;
use Validator;
use File;
use Lang;
use Exception;
use DB;
use App\Models\NotifiedUsers;

class NotificationsService
{
// TYPES
    // ticket
    // assign_to_me
    // assign_to_someone_else

    // user_replies
    // user_replies_to_my_ticket
    // user_notes
    // user_notes_to_unassigned_ticket
    // customer_notes_to_unassigned_ticket
    // another_user_add_note_to_unassigned_ticket
    // another_user_add_note_to_my_ticket
    // customer_replies_to_unassigned_ticket

    private $mailboxId;
    private $type;
    private $pageURL;

    function __construct()
    {
        $this->pageURL = Helper::$subDomain . env('PAGE_URL');
    }

    /**
     * @param $notification
     * @return string
     */
    private function getCreatedNotificationMessage($notification)
    {
        $text = config('constants.notification_messages.create');

        if (!empty($notification['author'])) {
            $text = str_replace("{author}", $notification['author']['first_name'], $text);
        } elseif (!empty($notification['customer'])) {
            $text = str_replace("{author}", $notification['customer']['first_name'], $text);
        }

        return $text;
    }

    /**
     * @param $notification
     * @param $userId
     * @return string
     */
    private function getAssignedNotificationMessage($notification, $userId)
    {
        $text = config('constants.notification_messages')['assign'];
        if (!empty($notification['author'])) {
            if ($notification['author']['id'] == $userId) {
                $text = str_replace("{author}", "You", $text);
            } else {
                $text = str_replace("{author}", $notification['author']['first_name'], $text);
            }
        }

        if (!empty($notification['assigned'])) {
            if ($notification['assigned']['id'] == $userId) {
                $text = str_replace("{assignUser}", "You", $text);
            } else {
                $text = str_replace("{assignUser}", $notification['assigned']['first_name'], $text);
            }
        } elseif ($notification['assigned_to'] == 0) {
            $text = str_replace("{assignUser}", "anyone", $text);
        }

        return $text;
    }

    /**
     * @param $notification
     * @return string
     */
    private function getCommentNotificationMessage($notification)
    {
        $text = config('constants.notification_messages.comment');

        if (!empty($notification['author'])) {
            $text = str_replace("{author}", $notification['author']['first_name'], $text);
        } elseif (!empty($notification['customer'])) {
            $text = str_replace("{author}", $notification['customer']['first_name'], $text);
        }
        return $text;
    }

    /**
     * @param $notification
     * @return string
     */
    private function getNoteNotificationMessage($notification)
    {
        $text = config('constants.notification_messages.note');

        if (!empty($notification['author'])) {
            $text = str_replace("{author}", $notification['author']['first_name'], $text);
        } elseif (!empty($notification['customer'])) {
            $text = str_replace("{author}", $notification['customer']['first_name'], $text);
        }
        return $text;
    }

    /**
     * @param $notification
     * @param $userId
     * @return string
     */
    private function getMentionedUserNotificationMessage($notification, $userId)
    {
        $text = config('constants.notification_messages.mention');

        if (!empty($notification['author'])) {
            if ($notification['author']['id'] == $userId) {
                $text = str_replace("{author}", "You", $text);
            } else {
                $text = str_replace("{author}", $notification['author']['first_name'], $text);
            }
        }

        if (!empty($notification['mentioned'])) {
            if ($notification['mentioned']['id'] == $userId) {
                $text = str_replace("{mentionedTo}", "You", $text);
            } else {
                $text = str_replace("{mentionedTo}", $notification['mentioned']['first_name'], $text);
            }
        }

        return $text;
    }

    /**
     * @param $usersOfMailbox
     * @param $selectedConditionUsers
     * @param null $savedNotification
     * @return array
     */
    private function getNotificationDataUsers($usersOfMailbox, $selectedConditionUsers, $savedNotification = null)
    {
        $type = $this->getTypeForSaveNotification($this->type);

        $result = [];
        foreach ($usersOfMailbox as $key => $value) {
            $result[$key] = [
                'message' => $this->getPrettyMessage($type, $savedNotification, $value),
                'user_id' => $value,
                'notify' => in_array($value, $selectedConditionUsers)
            ];
        }
        $this->saveToNotifiedUsers($savedNotification['id'], $result);
        return $result;
    }

    /**
     * @param $type
     * @param $notification
     * @param $userId
     * @return mixed|null
     */
    public function getPrettyMessage($type, $notification, $userId)
    {
        switch ($type) {
            case 'new_ticket':
                $result = $this->getCreatedNotificationMessage($notification);
                break;
            case 'assign' :
                $result = $this->getAssignedNotificationMessage($notification, $userId);
                break;
            case 'comment' :
                $result = $this->getCommentNotificationMessage($notification);
                break;
            case 'note' :
                $result = $this->getNoteNotificationMessage($notification);
                break;
            case'mentioned' :
                $result = $this->getMentionedUserNotificationMessage($notification, $userId);
                break;
            default :
                $result = null;
        }
        return $result;
    }

    /**
     * @param $usersOfMailbox
     * @param $conditionId
     * @param string $type
     * @return array
     */
    private function getSelectedConditionUsers($usersOfMailbox, $conditionId, $type = 'browser')
    {
        return UserNotifications::whereIn('user_id', $usersOfMailbox)
            ->where('condition_id', $conditionId)
            ->where($type, 1)
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * @param $type
     * @return mixed
     */
    private function getTypeForSaveNotification($type)
    {
        $notificationTypes = [
            'assigned_to_me' => 'assign',
            'assigned_to_someone_else' => 'assign',
            'customer_replies_to_unassigned_ticket' => 'comment',
            'customer_replies_to_my_ticket' => 'comment',
            'user_replies_to_unassigned_ticket' => 'comment',
            'user_replies_to_my_ticket' => 'comment',
            'user_add_note_to_my_ticket' => 'note',
            'user_add_note_to_unassigned_ticket' => 'note',
            'mentioned_in_ticket' => 'mentioned',
            'new_ticket' => 'new_ticket'
        ];
        return $notificationTypes[$type];
    }


    /**
     * @param array $usersIDs
     * @param int $notificationId
     *
     */
    private function saveToNotifiedUsers($notificationId, $usersIDs)
    {
        $data = [];
        for ($i = 0; $i < count($usersIDs); $i++) {
            array_push($data, [
                'notification_id' => $notificationId,
                'user_id' => $usersIDs[$i]['user_id'],
                'is_viewed' => 0
            ]);
        }
        DB::table('notified_users')->insert($data);
    }

    /**
     * @param $notificationData
     * @param string $type
     * @param array $users
     */
    private function sendNotificationToBrowser($notificationData, $type, $users = null)
    {
        $data['data_notification'] = $notificationData;
        $data['room'] = Helper::$subDomain;
        $data['type'] = $type;
        $data['users'] = $users;

        try {
            Helper::sendNotification($data, $type);
        } catch (Exception $e) {
            // $e->getMessage();
        }
    }

    /**
     * @param $userId
     * @param $conditionId
     * @param string $type
     * @return bool
     */
    private function checkSelectedConditionId($userId, $conditionId, $type = 'browser')
    {
        $check = UserNotifications::where('user_id', $userId)
            ->where('condition_id', $conditionId)
            ->where($type, 1)
            ->first();
        return !empty($check);
    }

    /**
     * @param $usersIDs
     * @param $ticketId
     * @param bool $mention
     * @return bool
     */
    private function sendEmailNotification($usersIDs, $ticketId, $mention = false)
    {
        if (empty($usersIDs)) {
            return true;
        }

        $href = "https://" . Helper::$subDomain . env('PAGE_URL') . '/ticket/' . $ticketId;

        $data = SendEmailService::prettyHtmlAndData($ticketId, $href);

        $userEmails = User::whereIn('id', $usersIDs)->where('email','!=',$data['mailbox']['email'])->get()->toArray();
        // todo need to change

        $options = [
            'toEmail' => '',
            'fromEmail' => $data['mailbox']['email'],
            'fromName' => $data['mailbox']['name'],
            'subject' => 'Notification for ticket #' . $ticketId,
            'messageId' => '<' . md5(rand(999, 9999999)) . '@mail' . env('PAGE_URL') . '>',
            'commentText' => $data['html'],
            'replyEmail' => '',
            'reply_to' => ''
        ];

        foreach ($userEmails as $user) {
            $options['toEmail'] = $user['email'];
            $options['toName'] = $user['first_name'] . ' ' . $user['last_name'];
            $options['reply_to'] = (!$mention) ?
                '35f90c33eaa7cbe39be15f40316c2ab6_' . $data['ticket_id_hash'] . '_' . $user['id'] . '_' . $data['last_comment_id'] . '@' . Helper::$subDomain . env('PAGE_URL') :
                'ac5362cd5e10431cd60b04720b75450b_' . $data['ticket_id_hash'] . '_' . $user['id'] . '@' . Helper::$subDomain . env('PAGE_URL');

            SendEmailService::sendEmail($options,false);
        }
    }


    // notify me then ...
    /**
     * There is a new ticket
     * @param array $dataTicket
     * @param $savedNotification
     */
    public function isNewTicket($dataTicket, $savedNotification)
    {
        $this->mailboxId = $dataTicket['mailbox_id'];
        $conditionId = config('constants.notifications.new_ticket.id');

        $this->type = 'new_ticket';
        $usersOfMailbox = MailboxService::getUsersOfMailBox($this->mailboxId);
        $usersOfMailbox = array_values(array_diff($usersOfMailbox, [Helper::$user['id']]));

        $selectedConditionUsers = $this->getSelectedConditionUsers($usersOfMailbox, $conditionId);
        $usersForEmailNotification = $this->getSelectedConditionUsers($usersOfMailbox, $conditionId, 'email');
        $this->sendEmailNotification($usersForEmailNotification, $savedNotification['ticket_id']);
        $users = $this->getNotificationDataUsers($usersOfMailbox, $selectedConditionUsers, $savedNotification);
        $this->sendNotificationToBrowser($dataTicket, $this->type, $users);
    }

    /**
     * Ticket assign to me or someone else
     * @param array $dataTicket
     * @param int $assignedAgentId
     * @param $savedNotification
     */
    public function assignedTicket($dataTicket, $assignedAgentId, $savedNotification)
    {
        // assign to me or to someone else
        $assignedToSomeoneElseConditionId = config('constants.notifications.assigned_to_someone_else.id');
        $assignedToMeConditionId = config('constants.notifications.assigned_to_me.id');

        // assign to someone else
        $this->mailboxId = $dataTicket['mailbox_id'];
        $this->type = 'assigned_to_someone_else';
        $usersOfMailbox = MailboxService::getUsersOfMailBox($this->mailboxId);
        $usersOfMailbox = array_values(array_diff($usersOfMailbox, [Helper::$user['id']]));
        $selectedConditionUsers = $this->getSelectedConditionUsers($usersOfMailbox, $assignedToSomeoneElseConditionId);
        if (in_array($assignedAgentId, $selectedConditionUsers)) {
            if ($key = array_search($assignedAgentId, $selectedConditionUsers) !== false) {
                unset($selectedConditionUsers[$key]);
            }
        }

        $dataTicket['author'] = Helper::$user;
        $users = $this->getNotificationDataUsers($usersOfMailbox, $selectedConditionUsers, $savedNotification);
        $usersForEmailNotification = $this->getSelectedConditionUsers($usersOfMailbox, $assignedToSomeoneElseConditionId, 'email');
        $this->sendEmailNotification($usersForEmailNotification, $savedNotification['ticket_id']);
        $this->sendNotificationToBrowser($dataTicket, $this->type, $users);
        // assign to me
        if ($this->checkSelectedConditionId($assignedAgentId, $assignedToMeConditionId)) {
            $this->type = 'assigned_to_me';
            $users = [
                'message' => $this->getPrettyMessage('assign', $savedNotification, $assignedAgentId),
                'notify' => true,
                'user_id' => $assignedAgentId
            ];
            $this->sendNotificationToBrowser($dataTicket, $this->type, $users);
        }

        if ($this->checkSelectedConditionId($assignedAgentId, $assignedToMeConditionId, 'email')) {
            $users = [$assignedAgentId];
            $this->sendEmailNotification($users, $savedNotification['ticket_id']);
        }
    }

    /**
     * I am mentioned in a ticket
     * @param $note
     * @param $mailboxId
     * @param $mentionedUsers
     * @param $notifications
     * @param $ticketId
     */
    public function mentionedInTicket($note, $mailboxId, $mentionedUsers, $notifications, $ticketId)
    {
        $this->mailboxId = $mailboxId;
        $conditionId = config('constants.notifications.mentioned_in_ticket.id');
        $this->type = 'mention_in_ticket';
        $usersOfMailbox = MailboxService::getUsersOfMailBox($this->mailboxId);
        $usersOfMailbox = array_values(array_diff($usersOfMailbox, [Helper::$user['id']]));
        $selectedConditionUsers = $this->getSelectedConditionUsers($usersOfMailbox, $conditionId);
        $selectedConditionUsersEmail = $this->getSelectedConditionUsers($usersOfMailbox, $conditionId, 'email');
        $selectedConditionUsers = array_intersect($selectedConditionUsers, $mentionedUsers);
        $this->sendEmailNotification($selectedConditionUsersEmail, $ticketId);
        $dataNotification = [];
        $notifiedUsersData = [];

        foreach ($selectedConditionUsers as $userId) {
            $arr = array_column($notifications, 'mentioned');
            $key = array_search($userId, array_column($arr, 'id'));
            $dataNotification[$key] = [
                'message' => $this->getMentionedUserNotificationMessage($notifications[$key], $userId),
                'notify' => true,
                'user_id' => $notifications[$key]['mentioned']['id']
            ];

            array_push($notifiedUsersData, [
                'notification_id' => $notifications[$key]['id'],
                'user_id' => $notifications[$key]['mentioned']['id'],
                'is_viewed' => 0
            ]);
        }

        DB::table('notified_users')->insert($notifiedUsersData);
        $this->sendNotificationToBrowser($note, $this->type, $dataNotification);
    }

    // notify me when customer replies ...

    /**
     * Customer replies to
     * @param $dataTicket
     * @param $mailboxId
     * @param null $assignedAgentId
     * @param $notification
     */
    public function customerReplies($dataTicket, $mailboxId, $assignedAgentId = null, $notification)
    {
        // assign to me or to someone else
        $repliesToUnassignedTicketConditionId = config('constants.notifications.customer_replies_to_unassigned_ticket.id');
        $repliesToMyTicketConditionId = config('constants.notifications.customer_replies_to_my_ticket.id');

        // assign to someone else
        $this->mailboxId = $mailboxId;
        $this->type = 'customer_replies_to_unassigned_ticket';
        $usersOfMailbox = MailboxService::getUsersOfMailBox($this->mailboxId);
        $usersOfMailbox = array_values(array_diff($usersOfMailbox, [Helper::$user['id']]));
        $selectedConditionUsers = $this->getSelectedConditionUsers($usersOfMailbox, $repliesToUnassignedTicketConditionId);
        if (in_array($assignedAgentId, $selectedConditionUsers)) {
            if ($key = array_search($assignedAgentId, $selectedConditionUsers) !== false) {
                unset($selectedConditionUsers[$key]);
            }
        }
        $usersForEmailNotification = $this->getSelectedConditionUsers($usersOfMailbox, $repliesToUnassignedTicketConditionId, 'email');
        $this->sendEmailNotification($usersForEmailNotification, $notification['ticket_id']);
        $users = $this->getNotificationDataUsers($usersOfMailbox, $selectedConditionUsers, $notification);
        $this->sendNotificationToBrowser($dataTicket, $this->type, $users);

        // assign to me
        if ($this->checkSelectedConditionId($assignedAgentId, $repliesToMyTicketConditionId)) {
            $this->type = 'customer_replies_to_my_ticket';
            $users = [
                'message' => $this->getCommentNotificationMessage($notification),
                'notify' => true,
                'user_id' => $assignedAgentId
            ];
            $this->sendNotificationToBrowser($dataTicket, $this->type, $users);
        }

        if ($this->getSelectedConditionUsers($usersOfMailbox, $repliesToUnassignedTicketConditionId, 'email')) {
            $users = [$assignedAgentId];
            $this->sendEmailNotification($users, $notification['ticket_id']);
        }
    }

    // notify me when another user replies ...

    /**
     * Another user replies to ticket
     * @param $dataTicket
     * @param $mailboxId
     * @param null $assignedAgentId
     * @param $notification
     */
    public function userRepliesTo($dataTicket, $mailboxId, $assignedAgentId = null, $notification)
    {
        $repliesToUnassignedTicketConditionId = config('constants.notifications.user_replies_to_unassigned_ticket.id');
        $repliesToMyTicketConditionId = config('constants.notifications.user_replies_to_my_ticket.id');

        // assign to someone else
        $this->mailboxId = $mailboxId;
        $this->type = 'customer_replies_to_unassigned_ticket';
        $usersOfMailbox = MailboxService::getUsersOfMailBox($this->mailboxId);
        $usersOfMailbox = array_values(array_diff($usersOfMailbox, [Helper::$user['id']]));
        $selectedConditionUsers = $this->getSelectedConditionUsers($usersOfMailbox, $repliesToUnassignedTicketConditionId);
        $usersForEmailNotification = $this->getSelectedConditionUsers($usersOfMailbox, $repliesToUnassignedTicketConditionId, 'email');
        $this->sendEmailNotification($usersForEmailNotification, $notification['ticket_id']);

        if (in_array($assignedAgentId, $selectedConditionUsers)) {
            if ($key = array_search($assignedAgentId, $selectedConditionUsers) !== false) {
                unset($selectedConditionUsers[$key]);
            }
        }

        $users = $this->getNotificationDataUsers($usersOfMailbox, $selectedConditionUsers, $notification);
        $this->sendNotificationToBrowser($dataTicket, $this->type, $users);

        // assign to me
        if ($this->checkSelectedConditionId($assignedAgentId, $repliesToMyTicketConditionId)) {
            $this->type = 'customer_replies_to_my_ticket';
            $users = [
                'notify' => true,
                'user_id' => $assignedAgentId
            ];
            $this->sendNotificationToBrowser($dataTicket, $this->type, $users);
        }

        if ($this->getSelectedConditionUsers($usersOfMailbox, $repliesToUnassignedTicketConditionId, 'email')) {
            $users = [$assignedAgentId];
            $this->sendEmailNotification($users, $notification['ticket_id']);
        }
    }

    // notify me when another user add note ...

    /**
     * Another user add note to ticket
     * @param array $dataNote
     * @param int $mailboxId
     * @param int $assignedAgentId
     * @param $notification
     */
    public function userAddNote($dataNote, $mailboxId, $assignedAgentId, $notification)
    {
        $addNoteToUnassignedTicketConditionId = config('constants.notifications.user_add_note_to_unassigned_ticket.id');
        $addNoteToMyTicketConditionId = config('constants.notifications.user_replies_to_my_ticket.id');

        // add note to unassigned ticket
        $this->mailboxId = $mailboxId;
        $this->type = 'user_add_note_to_unassigned_ticket';
        $usersOfMailbox = MailboxService::getUsersOfMailBox($this->mailboxId);
        $usersOfMailbox = array_values(array_diff($usersOfMailbox, [Helper::$user['id']]));
        $selectedConditionUsers = $this->getSelectedConditionUsers($usersOfMailbox, $addNoteToUnassignedTicketConditionId);
        if (in_array($assignedAgentId, $selectedConditionUsers)) {
            if ($key = array_search($assignedAgentId, $selectedConditionUsers) !== false) {
                unset($selectedConditionUsers[$key]);
            }
        }
        $users = $this->getNotificationDataUsers($usersOfMailbox, $selectedConditionUsers, $notification);
        $usersForEmailNotification = $this->getSelectedConditionUsers($usersOfMailbox, $addNoteToUnassignedTicketConditionId, 'email');
        $this->sendEmailNotification($usersForEmailNotification, $notification['ticket_id'], true);

        $this->sendNotificationToBrowser($dataNote, $this->type, $users);
        if ($this->checkSelectedConditionId($assignedAgentId, $addNoteToMyTicketConditionId)) {
            // add note to my ticket
            $this->type = 'user_add_note_to_my_ticket';
            $users = [
                'message' => $this->getNoteNotificationMessage($notification),
                'notify' => true,
                'user_id' => $assignedAgentId
            ];

            $this->sendNotificationToBrowser($dataNote, $this->type, $users);
        }

        if ($this->checkSelectedConditionId($assignedAgentId, $addNoteToMyTicketConditionId, 'email')) {
            // add note to my ticket
            $this->type = 'user_add_note_to_my_ticket';
            $users = [$assignedAgentId];
            $this->sendEmailNotification($users, $notification['ticket_id'], true);
        }
    }

    /**
     * @param $ticketId
     * @param $commentId
     * @param null $authorId
     * @param null $customerId
     * @return mixed
     */
    public function saveCreateTicketNotification($ticketId, $commentId, $authorId = null, $customerId = null)
    {
        $notification = Notifications::create([
            'type' => 'new_ticket',
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'customer_id' => $customerId,
            'comment_id' => $commentId
        ]);
        return Notifications::where('id', $notification->id)->with(['author', 'customer'])->first()->toArray();
    }

    /**
     * @param $ticketId
     * @param $authorId
     * @param $assignedTo
     * @return mixed
     */
    public function saveAssignTicketNotification($ticketId, $authorId, $assignedTo)
    {
        $notification = Notifications::create([
            'type' => 'assign',
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'assigned_to' => $assignedTo
        ]);
        return Notifications::where('id', $notification->id)->with(['author', 'assigned'])->first()->toArray();
    }

    /**
     * @param $ticketId
     * @param null $authorId
     * @param $commentId
     * @param null $customerId
     * @return mixed
     */
    public function saveCommentNotification($ticketId, $authorId = null, $commentId, $customerId = null)
    {
        $notification = Notifications::create([
            'type' => 'comment',
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'customer_id' => $customerId,
            'comment_id' => $commentId
        ]);
        return Notifications::where('id', $notification->id)->with(['author'])->first()->toArray();
    }

    /**
     * @param $ticketId
     * @param $noteId
     * @param null $authorId
     * @return mixed
     */
    public function saveNoteNotification($ticketId, $noteId, $authorId = null)
    {
        $notification = Notifications::create([
            'type' => 'note',
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'note_id' => $noteId
        ]);
        return Notifications::where('id', $notification->id)->with(['author'])->first()->toArray();
    }

    /**
     * @param $users
     * @param $ticketId
     * @param $noteId
     * @param $authorId
     * @return array
     */
    public function saveMentionedUsersNotification($users, $ticketId, $noteId, $authorId)
    {
        $data = [];
        foreach ($users as $userId) {
            $notification = Notifications::create([
                'type' => 'mentioned',
                'ticket_id' => $ticketId,
                'author_id' => $authorId,
                'note_id' => $noteId,
                'mentioned' => $userId
            ]);

            $notification = Notifications::where('id', $notification->id)->with(['author', 'mentioned'])->first()->toArray();
            array_push($data, $notification);
        }
        return $data;
    }

    /**
     * Get Notifications start
     * @param $notification
     * @return array
     */
    public function getNotificationDataByType($notification)
    {
        switch ($notification['type']) {
            case 'comment' :
                return $this->getNewTicketNotificationData($notification['comment_id']);
                break;
            case 'note' :
                return $this->getNoteNotificationData($notification['note_id']);
                break;
            case 'assign' :
                return [
                    'body' => '',
                    'color' => Tickets::where('id', $notification['ticket_id'])->first()['color'],
                    'files' => []
                ];
                break;
            case 'mentioned' :
                return [
                    'body' => '',
                    'color' => Tickets::where('id', $notification['ticket_id'])->first()['color'],
                    'files' => []
                ];
                break;
            default:
                return $this->getNewTicketNotificationData($notification['comment_id']);
                break;
        }
    }

    /**
     * @param $commentId
     * @return array
     */
    private function getNewTicketNotificationData($commentId)
    {
        $result = TicketComments::where('id', $commentId)->with('files')->first()->toArray();
        return [
            'body' => $result['body'],
            'files' => $result['files'],
            'color' => Tickets::where('id', $result['ticket_id'])->first()['color']
        ];
    }

    /**
     * @param $noteId
     * @return array
     */
    private function getNoteNotificationData($noteId)
    {
        $result = Notes::where('id', $noteId)->with('files')->first()->toArray();
        return [
            'body' => $result['note'],
            'files' => $result['files'],
            'color' => Tickets::where('id', $result['ticket_id'])->first()['color']
        ];
    }

    /**
     * Get Notifications end
     * @param $ticketId
     */
    public function setAllNotificationsViewedByTicketId($ticketId)
    {
        $notificationsIDs = Notifications::where('ticket_id', $ticketId)->pluck('id');
        foreach ($notificationsIDs as $id) {
            NotifiedUsers::where(['notification_id' => $id, 'user_id' => Helper::$user['id']])->update(['is_viewed' => 1]);
        }
    }

}