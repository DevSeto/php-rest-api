<?php

namespace App\Helpers;

use DB;
use Response;
use Lang;
use Route;

use App\Models\Conditions;
use App\Models\Workflows\Workflows;
use App\Models\Actions;


class WorkflowBrainHelper
{

    /* **********************
     *   Workflow actions   *
     * **********************/
    //  copyToFolder
    //  sendNotification
    //  forward
    //  addNote
    //  changeStatus
    //  assignToUser
    //  addTags
    //  removeTags
    //  moveToMailbox
    //  delete
    //  setCustomField


    /**
     *
     *
     *
     *
     * @param int $actionIds
     */
    public static function action ($actionIds)
    {

        switch ($actionIds) {
            case 1: WorkflowActionsHelper::copyToFolder(); break;
            case 2: WorkflowActionsHelper::sendNotification(); break;
            case 3: WorkflowActionsHelper::forward(); break;
            case 4: WorkflowActionsHelper::addNote(); break;
            case 5: WorkflowActionsHelper::changeStatus(); break;
            case 6: WorkflowActionsHelper::assignToUser(); break;
            case 7: WorkflowActionsHelper::addTags(); break;
            case 8: WorkflowActionsHelper::removeTags(); break;
            case 9: WorkflowActionsHelper::moveToMailbox(); break;
            case 10: WorkflowActionsHelper::delete(); break;
        }
    }


    public static function userDataWorkflow ($userId)
    {
        $dataWorkflow = Workflows::where('user_id', $userId)->where('activity', 1)->with(['actions', 'conditions'])->get();

        if (!empty($dataWorkflow->toArray())) {
//            $workflows = $dataWorkflow->toArray();

            foreach ($dataWorkflow as $workflow) {
                var_dump($workflow->conditions());
                WorkflowHelper::toSortDataConditions($workflow->conditions()->get());
//                $workflowConditions = WorkflowHelper::toSortDataConditions($workflow->conditions());
//                var_dump($workflowConditions);
            }
        }
    }

}
