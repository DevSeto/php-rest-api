<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;

class WorkflowActionsList extends Model
{
    protected $fillable = ['id', 'action'];
    protected $table = 'workflow_actions_list';

    public function types()
    {
        return $this->hasMany('App\Models\Workflows\WorkflowActionsTypes', 'action_id', 'id');
    }

    public function values()
    {
        return $this->hasMany('App\Models\Workflows\WorkflowActionValues', 'action_id', 'id');
    }

}
