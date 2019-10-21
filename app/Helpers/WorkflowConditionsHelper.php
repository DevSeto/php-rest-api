<?php

namespace App\Helpers;

use DB;
use Response;
use Lang;
use Route;
use App\Models\Conditions;
use App\Models\Actions;

class WorkflowConditionsHelper
{

    /**
     * @param array $dataActionsIDs
     *
     *
     */
    public static function dataActions ($dataActionsIDs)
    {
        foreach ($dataActionsIDs as $actionID) {
            WorkflowBrainHelper::action($actionID);
        }
    }

}
