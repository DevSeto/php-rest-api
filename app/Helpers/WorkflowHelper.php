<?php

namespace App\Helpers;

use DB;
use Response;
use Lang;
use Route;
use App\Models\Notes;

use App\Models\Conditions;
use App\Models\Actions;

class WorkflowHelper
{


    public static function toSortDataConditions($data)
    {
        $arrOr = [];
        $arrAnd = [];

        foreach ($data as $item) {
            if ($item->relation === 'or') {
                array_push($arrOr, $item);
            } else {
                array_push($arrAnd, $arrOr);
                $arrOr = [];
                array_push($arrAnd, [$item]);
            }
        }

        $result = $arrAnd;
        return $result;

    }


    public static function checkWorkflowConditions()
    {

    }

    public $conditions = [
        //People
        'Customer Name',
        'Customer Email',
        'Birddesk User',

        // Message
        'Type',
        'Status',
        'Assigned to User',
        'To',
        'Cc',
        'Subject',
        'Body',
        'Attachment(s)',
        'Tag(s)',
        'Rating',
        'Rating',
        'Rating Comments',

        // Timeframe
        'Date Created',
        'Last Updated'
    ];

    /*********************** Workflow Condition ************************/
    /**
     * Get current subDomain
     *
     * @param array $condition,
     *
     * @return string
     */
    public static function condition($condition)
    {

        // Time Frame
            // Date created
            // Last updated
            // Last User Reply
            // Last Customer Reply

        // Messages
            // Status
            // Assigned to User


            // To
            // Subject
            // Body
            // Attachment(s)
            // Tag(s)

        // People
            // Customer name
            // Customer Email
            // Birddesk User
    }

    /**
     * Added a note
     * @return string
     */
    public static function addedNote(){

    }

    /**
     * Contains
     * @return string
     */
    public static function contains(){

    }

    /**
     * Doesn't contain
     * @return string
     */
    public static function DoesNotContain(){

    }


    /**
     * Get current subDomain
     *
     * @param string $subConditionKey
     *
     * @return string
     */
    public static function subConditionKeys($subConditionKey)
    {
        $q = '';

        switch ($subConditionKey) {

            // Added a note
            case "Added a note":
                echo "Added a note";
                break;

            // Contains
            case "Contains":
                echo "Contains";
                break;

            // Doesn't contain
            case "Doesn't contain":
                echo "Doesn't contain";
                break;

            // Doesn't have an attachment
            case "Doesn't have an attachment":
                echo "Doesn't have an attachment";
                break;

            // Ends with
            case "Ends with":
                echo "Ends with";
                break;

            // Has an attachment
            case "Has an attachment":
                echo "Has an attachment";
                break;

            // Is equal
            case "Is equal":
                echo "Is equal";
                break;

            // Is equal to
            case "Is equal to":
                echo "Is equal to";
                break;

            // Is in the list
            case "Is in the list":
                echo "Is in the list";
                break;

            // Is not equal
            case "Is not equal":
                echo "Is not equal";
                break;

            // Is not in the list
            case "Is not in the list":
                echo "Is not in the list";
                break;

            // Isn't equal to
            case "Isn't equal to":
                echo "Isn't equal to";
                break;

            // Matches regex pattern
            case "Matches regex pattern":
                echo "Matches regex pattern";
                break;

            // Notes contain
            case "Notes contain":
                echo "Notes contain";
                break;

            // Replied
            case "Replied":
                echo "Replied";
                break;

            // Starts with
            case "Starts with":
                echo "Starts with";
                break;
        }

    }
    /*********************** /> Workflow Condition ************************/


    /*********************** Workflow Actions ************************/

    /**
     * Copy to Folder
     *
     * @param array $action
     * @param array $condition
     * @return string
     */
    public static function copyToFolder($action, $condition)
    {
        return 'copyToFolder';
    }

    /**
     * Send Notification
     *
     * @param array $action
     * @param array $condition
     *
     * @return string
     */
    public static function sendNotification($action, $condition)
    {
        dd(['$action' => $action]);

        return 'sendNotification';
    }

    /**
     * Forward
     *
     * @param array $action
     * @param array $condition
     *
     * @return string
     */
    public static function forward($action, $condition)
    {
        return 'forward';
    }

    /**
     * Add a Note
     *
     * @param array $action
     * @param array $condition
     *
     * @return array
     */
    public static function addNote($action, $condition)
    {
        $applyToPrevious = false;

        $notes['ticket_id'] = [];
        $notes['note']      = [];
        $notes['author_id'] = [];

        $addNote = Notes::create($notes);

//        $addNote = Notes::create($notes);
//        dd(['$action' => $action, '$condition' => $condition]);

        return $notes;
    }

    /**
     * Change Status
     *
     * @param array $action
     * @param array $action
     *
     * @return string
     */
    public static function changeStatus($action, $condition)
    {
        return 'changeStatus';
    }

    /**
     * Assign to User
     *
     * @param array $action
     * @param array $condition
     *
     * @return string
     */
    public static function assignToUser($action, $condition)
    {
        return 'assignToUser';
    }

    /**
     * Add Tag(s)
     *
     * @param array $action
     * @param array $condition
     *
     * @return string
     */
    public static function addTag($action, $condition)
    {
        return 'addTag';
    }

    /**
     * Remove Tag(s)
     *
     * @param array $action
     * @param array $condition
     *
     * @return string
     */
    public static function removeTag($action, $condition)
    {
        return 'removeTag';
    }

    /**
     * Move to Mailbox
     *
     * @param array $action
     * @param array $condition
     *
     * @return string
     */
    public static function moveToMailbox($action, $condition)
    {
        return 'moveToMailbox';
    }

    /**
     * To Delete
     *
     * @param array $action
     *
     * @return string
     */
    public static function delete($action, $condition)
    {
        return 'delete';
    }

    /**
     * Get current subDomain
     *
     * @param array $action
     * @param array $condition
     *
     * @return string
     */
    public static function action($action, $condition)
    {
        switch ($action['id']) {
            case 1: // Copy to Folder
                $result = self::copyToFolder($action, $condition);
                break;
            case 2: // Send Notification
                $result = self::sendNotification($action, $condition);
                break;
            case 3: // Forward
                $result = self::forward($action, $condition);
                break;
            case 4: // Add a Note
                $result = self::addNote($action, $condition);
                break;
            case 5: // Change Status
                $result = self::changeStatus($action, $condition);
                break;
            case 6: // Assign to User
                $result = self::assignToUser($action, $condition);
                break;
            case 7: // Add Tag(s)
                $result = self::addTag($action, $condition);
                break;
            case 8: // Remove Tag(s)
                $result = self::removeTag($action, $condition);
                break;
            case 9: // Move to Mailbox
                $result = self::moveToMailbox($action, $condition);
                break;
            case 10: // Delete
                $result = self::delete($action, $condition);
                break;
            default:
                return 'action Id cannot be null';
        }

        return $result;
    }


    /*********************** /> Workflow Actions ************************/

    /**
     * Get current subDomain
     *
     * @param array $request
     *
     * @return string
     */
    public static function event($request)
    {

        // if {{ condition }}
        // then {{ action }}
        $action     = $request->all()['action'];
        $condition  = $request->all()['condition'];


//        if (self::condition()) {
//
//            self::action();
//        }

        dd(self::action($action, $condition));

    }
}
