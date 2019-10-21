<?php

namespace App\Services;


use App\Helpers\Helper;
use App\Models\TicketsHistory;

class TicketHistoryService
{
    /***
     * Ticket History Types are 'create','comment','note','assign','merge','status'
     */


    /**
     * @param $ticketId
     * @param null $authorId
     * @param null $customerId
     * @return mixed
     */
    public static function createTicketHistory($ticketId, $authorId = null, $customerId = null)
    {
        $type = 'create';
        return TicketsHistory::create([
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'customer_id' => $customerId,
            'type' => $type
        ]);
    }

    public static function commentsTicketHistory($ticketId, $authorId = null, $customerId = null)
    {
        $type = 'comment';
        return TicketsHistory::create([
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'customer_id' => $customerId,
            'type' => $type
        ]);
    }

    public static function assignTicketHistory($ticketId, $authorId = null, $customerId = null, $assignTo)
    {
        $type = 'assign';
        return TicketsHistory::create([
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'type' => $type,
            'assigned_to' => $assignTo
        ]);
    }

    public static function statusTicketHistory($ticketId, $authorId = null, $customerId = null, $statusTo)
    {
        $type = 'status';
        return TicketsHistory::create([
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'type' => $type,
            'status_to' => $statusTo
        ]);
    }

    public static function mergeTicketHistory($ticketId, $authorId = null, $customerId = null, $mergedWith)
    {
        $type = 'merge';
        return TicketsHistory::create([
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'type' => $type,
            'merged_with' => $mergedWith
        ]);
    }

    public static function noteTicketHistory($ticketId, $authorId = null, $customerId = null)
    {
        $type = 'note';
        return TicketsHistory::create([
            'ticket_id' => $ticketId,
            'author_id' => $authorId,
            'customer_id' => $customerId,
            'type' => $type,
        ]);
    }

    public function getTicketHistoryByTicketId($ticketId)
    {
        //if null toArray
        $prettyHistory = [];
        $history = TicketsHistory::where('ticket_id', $ticketId)->with(['author', 'customer', 'assignedToUser'])->get()->toArray();
        foreach ($history as $item) {
            $result['text'] = $this->getPrettyHistory($item);
            $result['created_at'] = $item['created_at'];
            array_push($prettyHistory, $result);
        }

        return $prettyHistory;
    }

    private function getPrettyHistory($data){

        switch ($data['type']) {
            case 'comment' :
                $text = $this->getPrettyCommentMessage($data);
                break;
            case 'assign' :
                $text = $this->getAssignPrettyMessage($data);
                break;
            case 'status' :
                $text = $this->getStatusPrettyMessage($data);
                break;
            case 'merge' :
                $text = $this->getMergePrettyMessage($data);
                break;
            case 'note' :
                $text = $this->getNotePrettyMessage($data);
                break;
            default :
                $text = $this->getCreatePrettyMessage($data);
                break;
        }
        return $text;
    }

    private function getCreatePrettyMessage($data){
        $text = config('constants.ticket_history')['create'];
        if (!empty($data['author'])){
            if ($data['author']['id'] == Helper::$user['id']){
                $text = str_replace("{author}","Me",$text);
            } else{
                $text = str_replace("{author}",$data['author']['first_name'],$text);
            }
        } elseif (!empty($data['customer'])) {
            $text = str_replace("{author}",$data['customer']['first_name'],$text);
        }
        return $text;
    }

    private function getPrettyCommentMessage($data){
        $text = config('constants.ticket_history')['comment'];
        if (!empty($data['author'])){
            if ($data['author']['id'] == Helper::$user['id']){
                $text = str_replace("{author}","Me",$text);
            } else{
                $text = str_replace("{author}",$data['author']['first_name'],$text);
            }
        } elseif (!empty($data['customer'])) {
            $text = str_replace("{author}",$data['customer']['first_name'],$text);
        }
        return $text;
    }

    private  function getAssignPrettyMessage($data){
        $text = config('constants.ticket_history')['assign'];
        if (!empty($data['author'])){
            if ($data['author']['id'] == Helper::$user['id']){
                $text = str_replace("{author}","Me",$text);
            } else{
                $text = str_replace("{author}",$data['author']['first_name'],$text);
            }
        }

        if (!empty($data['assigned_to_user'])){
            if ($data['assigned_to_user']['id'] == Helper::$user['id']){
                $text = str_replace("{assignUser}","You",$text);
            } else{
                $text = str_replace("{assignUser}",$data['assigned_to_user']['first_name'],$text);
            }
        } elseif ($data['assigned_to'] == 0){
            $text = str_replace("{assignUser}","anyone",$text);
        }
        return $text;
    }

    private function getStatusPrettyMessage($data){
        $text = config('constants.ticket_history')['status'];
        if (!empty($data['author'])){
            if ($data['author_id'] == Helper::$user['id']){
                $text = str_replace("{author}","Me",$text);
            } else{
                $text = str_replace("{author}",$data['author']['first_name'],$text);
            }
        } else {
            $text = config('constants.ticket_history')['status_from_email'];
        }

        if (!empty($data['status_to'])){
            $text = str_replace("{toStatus}",$data['status_to'],$text);
        }
        return $text;
    }

    private function getMergePrettyMessage($data){
        $text = config('constants.ticket_history')['merge'];
        if (!empty($data['author'])){
            if ($data['author']['id'] == Helper::$user['id']){
                $text = str_replace("{author}","Me",$text);
            } else{
                $text = str_replace("{author}",$data['author']['first_name'],$text);
            }
        }

        if (!empty($data['merged_with'])){
            $text = str_replace("{mergeTickets}",$data['merged_with'],$text);
        }
        return $text;
    }

    private function getNotePrettyMessage($data){
        $text = config('constants.ticket_history')['note'];
        if (!empty($data['author'])){
            if ($data['author'] == Helper::$user['id']){
                $text = str_replace("{author}","Me",$text);
            } else{
                $text = str_replace("{author}",$data['author']['first_name'],$text);
            }
            $text = str_replace("{author}",$data['author']['first_name'],$text);
        }
        return $text;
    }
}