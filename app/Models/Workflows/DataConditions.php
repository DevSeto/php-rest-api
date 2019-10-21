<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataConditions extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'condition_id',
        'operator_id',
        'condition_value_id',
        'condition_value',
        'relation',
        'relative_condition_id'
    ];
    protected $dates = ['deleted_at'];
}
