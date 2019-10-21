<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;

class WorkflowActionValues extends Model
{
    protected $fillable = ['id', 'action_id', 'value'];


}
