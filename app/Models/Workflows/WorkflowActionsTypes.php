<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;

class WorkflowActionsTypes extends Model
{
    protected $fillable = ['id', 'action_id', 'type'];

}
