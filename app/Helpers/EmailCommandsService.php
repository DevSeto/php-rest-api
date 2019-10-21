<?php

namespace App\Helpers;

use App\Http\Controllers\Tickets\NoteController;
use App\Http\Controllers\Tickets\TicketController;
use App\Http\Requests\ChangeStatusRequest;
use App\Http\Requests\CreateNoteRequest;
use App\Models\User;
use App\Services\NoteService;
use App\Services\NotificationsService;
use App\Services\TicketCommentsService;
use App\Services\TicketsService;
use DB;
use Illuminate\Http\Request;


class EmailCommandsService
{

    public static $subDomain;
    public static $ticket_id_hash;
    public static $user_api_token;
    public static $commands = [
        '@note' => 'addNote',
        '@assign' => 'assign',
        '@me' => 'assign',
        '@everyone' => 'assign',
        '@open' => 'changeStatus',
        '@pending' => 'changeStatus',
        '@closed' => 'changeStatus',
        '@spam' => 'changeStatus'
    ];

    public static function toRealizeCommands($subDomain, $ticket_id_hash, $commands, $user_api_token)
    {
        self::$subDomain = $subDomain;
        self::$ticket_id_hash = $ticket_id_hash;
        self::$user_api_token = $user_api_token;
        foreach (explode(PHP_EOL, $commands) as $command) {
            if (!empty($command)) {
                $command_name = explode(' ', trim($command))[0];
                if (array_key_exists($command_name, self::$commands)) {
                    $methodName = self::$commands[$command_name];
                    self::$methodName($command, $command_name);
                }
            }
        }
    }

    public static function assign($command, $command_name)
    {
        $command = trim(str_replace($command_name,'',$command));

        switch ($command_name){
            case "@assign" :
                $assign_agent = User::where('first_name',$command)->orWhere('last_name',$command)->first();
                if (!empty($assign_agent))
                    $assign_agentId = $assign_agent['id'];
                break;
            case "@me" :
                $assign_agentId = Helper::getUser(self::$user_api_token)['id'];
                break;
            case "@everyone" :
                $assign_agentId = 0;
                break;
            default :
                $assign_agentId = '';
                break;
        }

        if (!empty($assign_agentId)){

            $tickets_id_hashs = [
                'tickets_id_hashs' => [self::$ticket_id_hash]
            ];
            $req_obj = new Request([], [], [], [], [], [], json_encode($tickets_id_hashs));
            $req_obj->headers->add([
                'Authorization' => self::$user_api_token,
                "Content-Type" => "application/json"
            ]);

            $ticket_comments_service_obj = new TicketCommentsService();
            $ticket_service_obj = new TicketsService($ticket_comments_service_obj);
            $notification_service_obj = new NotificationsService();

            $ticket_obj = new TicketController($ticket_service_obj,$notification_service_obj);
            $ticket_obj->assignTicket($req_obj,$assign_agentId);
        }


    }

    public static function addNote($command, $command_name){
        $note = trim(str_replace($command_name,'',$command));

        $note = [
            'note' => $note
        ];
        $req_obj = new CreateNoteRequest([], [], [], [], [], [], json_encode($note));
        $req_obj->headers->add([
            'Authorization' => self::$user_api_token,
            "Content-Type" => "application/json"
        ]);

        $noteServiceObj = new NoteService();
        $userNotificationServiceObj = new NotificationsService();
        $note_obj = new NoteController($noteServiceObj, $userNotificationServiceObj);
        $note_obj->create($req_obj, self::$ticket_id_hash);
    }

    public static function changeStatus($command, $command_name){
        switch ($command_name){
            case "@open" :
                $status = 'open';
                break;
            case "@pending" :
                $status = 'pending';
                break;
            case "@closed" :
                $status = 'closed';
                break;
            case "@spam" :
                $status = 'spam';
                break;
            default :
                $status = '';
                break;
        }

        if (!empty($status)){
            $data = [
                'tickets_id_hashs' => [
                    self::$ticket_id_hash
                ],
                'status' => $status
            ];
        }

        $req_obj = new ChangeStatusRequest([], [], [], [], [], [], json_encode($data));
        $req_obj->headers->add([
            'Authorization' => self::$user_api_token,
            "Content-Type" => "application/json"
        ]);

        $ticket_comments_service_obj = new TicketCommentsService();
        $ticket_service_obj = new TicketsService($ticket_comments_service_obj);
        $notification_service_obj = new NotificationsService();

        $ticket_obj = new TicketController($ticket_service_obj,$notification_service_obj);
        $ticket_obj->changeStatus($req_obj);
    }

}