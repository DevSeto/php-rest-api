<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataActions extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'workflow_id', 'action_id', 'action_value_id', 'action_value'];
    protected $dates = ['deleted_at'];
}
