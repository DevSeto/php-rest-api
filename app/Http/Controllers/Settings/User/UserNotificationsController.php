<?php

namespace App\Http\Controllers\Settings\User;

use App\Helpers\SendEmailService;
use App\Models\CustomizedNotificationsOfMailboxes;
use App\Models\FilesOfNotification;
use App\Models\Mailbox;
use App\Models\MailboxUserPermissions;
use App\Models\TicketFiles;
use App\Models\TicketsVisibility;
use App\Services\MailboxService;
use App\Services\NotificationsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NotificationsList;
use App\Models\Tickets;
use App\Models\UserNotifications;
use App\Models\User;
use App\Helpers\Helper;
use Response;
use Validator;
use File;
use Lang;
use App\Helpers\Crypto;
use Exception;
use DB;
use stdClass;
use App\Models\Notifications;
use App\Models\NotifiedUsers;

class UserNotificationsController extends Controller
{
    private $notificationsService;

    function __construct(NotificationsService $notificationsService)
    {
        $this->middleware('check_token');
        $this->notificationsService = $notificationsService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Helper::getUser($request->header('Authorization'));

        $notifications = UserNotifications::where('user_id', $user['id'])->with(['notification'])->get();
        if (empty($notifications->toArray())) {
            $notifications = NotificationsList::get();
        }
        return Response::make(json_encode([
            'success' => true,
            'data' => $notifications
        ]), 200);
    }

    /**
     * @param int $ticketId
     *
     * @return array
     */
    public static function ticketFiles($ticketId)
    {
        $ticket = Tickets::find($ticketId)->first();
        return (!empty($ticket)) ? TicketFiles::where('ticket_id', $ticket->ticket_id_hash)->first() : [];
    }

    /**
     * Get user`s all notifications
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserNotifications(Request $request)
    {
        $offset = !empty($request->input('offset')) ? $request->input('offset') : 0;
        $count = !empty($request->input('count')) ? $request->input('count') : 5;

        $notifications = NotifiedUsers::with(['notifications'])
            ->where('user_id', Helper::$user['id'])
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($count)
            ->get()
            ->toArray();

        $total = NotifiedUsers::where('user_id', Helper::$user['id'])->count();
        $newest = NotifiedUsers::where('user_id', Helper::$user['id'])->where('is_viewed', 0)->count();
        $finalResult = [];
        foreach ($notifications as $key => $notification) {
            $data = $this->notificationsService->getNotificationDataByType($notification['notifications']);
            array_push($finalResult, [
                'author' => $notification['notifications']['author'],
                'customer' => $notification['notifications']['customer'],
                'message' => $this->notificationsService->getPrettyMessage($notification['notifications']['type'], $notification['notifications'], Helper::$user['id']),
                'body' => $data['body'],
                'is_viewed' => $notification['is_viewed'],
                'color' => $data['color'],
                'files' => $data['files'],
                'ticket_id' => $notification['notifications']['ticket_id'],
                'created_at' => $notification['notifications']['created_at'],
            ]);
        }

        $result = ['total_new' => $newest, 'total' => $total, 'notifications' => $finalResult];

        return Response::make(json_encode([
            'success' => true,
            'data' => $result
        ]), 200);
    }

    /**
     * Get user`s notification by notification id
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $notificationId
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserNotification(Request $request, $notificationId)
    {
        $apiToken = $request->header('Authorization');
        $user = Helper::getUser($apiToken);
        NotifiedUsers::find($notificationId)->update(['is_viewed' => 1]);

        $notification = NotifiedUsers::where('user_id', $user['id'])
            ->where('notification_id', $notificationId)
            ->with(['notifications'])
            ->first();

        return Response::make(json_encode([
            'success' => true,
            'data' => $notification
        ]), 200);
    }

    /**
     * Delete user`s notification by notification id
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $notificationId
     *
     * @return bool
     */
    public function deleteUserNotification(Request $request, $notificationId)
    {
        $apiToken = $request->header('Authorization');
        $user = Helper::getUser($apiToken);
        NotifiedUsers::where('user_id', $user['id'])->where('notification_id', $notificationId)->delete();
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Delete notifications by ticket id
     *
     * @param  int $ticketId
     *
     * @return bool
     */
    public static function deleteNotifications($ticketId)
    {
        $notifications = Notifications::where('ticket_id', $ticketId);
        if (!empty($notifications->pluck('id'))) {
            foreach ($notifications->pluck('id') as $notificationId) {
                NotifiedUsers::where('notification_id', $notificationId)->delete();
            }
            $notifications->delete();
        }
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Delete user`s all notifications
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function deleteUserNotifications(Request $request)
    {
        $apiToken = $request->header('Authorization');
        $user = Helper::getUser($apiToken);
        NotifiedUsers::where('user_id', $user['id'])->delete();
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Change user's conditions
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function changeConditions(Request $request)
    {
        $dataNotifications = $request->all();
        $user = Helper::getUser($request->header('Authorization'));
        $userId = $user['id'];

        foreach ($dataNotifications['conditions'] as $value) {
//            $data['user_id'] = $userId;
//            $data['condition_id'] = $value['id'];
            UserNotifications::updateOrCreate([
                'condition_id' => $value['id'],
                'user_id' => $userId
            ], [
                'email' => !empty($value['email']) ? $value['email'] : 0,
                'browser' => !empty($value['browser']) ? $value['browser'] : 0
            ]);
        }

        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * @param $mailboxID
     * @param Request $request
     * @return mixed
     */
    public function customizeNotifications(Request $request, $mailboxID = null)
    {
        $user = Helper::$user;
        $data = $request->all();
        if (is_null($mailboxID)) {
            UserNotifications::where('user_id', $user['id'])->forceDelete();
            $dataConditions = [];
            foreach ($data['condition_ids'] as $key => $value) {
                array_push($dataConditions, [
                    'user_id' => $user['id'],
                    'condition_id' => $key,
                    'email' => !empty($value['email']) ? $value['email'] : 0,
                    'browser' => !empty($value['browser']) ? $value['browser'] : 0
                ]);
            }
            Helper::insertTo('user_notifications', $dataConditions);
        } else {
            //get mailbox
            try {
                MailboxService::getMailboxById($mailboxID, $user['id']);
            } catch (\Exception $e) {
                return Helper::send_error_response('mailbox', $e->getMessage(), 422);
            }
            foreach ($data['condition_ids'] as $key => $value) {
                UserNotifications::updateOrCreate([
                    'condition_id' => $key,
                    'user_id' => $user['id'],
                    'mailbox_id' => $mailboxID
                ], [
                    'email' => !empty($value['email']) ? $value['email'] : 0,
                    'browser' => !empty($value['browser']) ? $value['browser'] : 0,
                    'mailbox_id' => $mailboxID
                ]);
            }
        }
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * @param $mailboxID
     */
    public function getCustomizedNotifications($mailboxID = null)
    {
        $user = Helper::$user;
        $query = UserNotifications::where('user_id', $user['id']);
        if (is_null($mailboxID)) {
            $data = $query->select('mailbox_id')->with('mailbox')->groupBy('mailbox_id')->get();
            return Response::make(json_encode(['success' => true, 'data' => $data]), 200);
        }
        $data = $query->where(['mailbox_id' => !null])->with('mailbox')->get();
        return Response::make(json_encode(['success' => true, 'data' => $data]), 200);
    }

}
