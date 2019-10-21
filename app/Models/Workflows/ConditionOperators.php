<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConditionOperators extends Model
{
    use SoftDeletes;

    protected $fillable = ['condition_id', 'operator_id'];
    protected $dates = ['deleted_at'];
    protected $table = 'condition_operators';

    public function data()
    {
        return $this->hasOne('App\Models\Workflows\ConditionsOperatorsList', 'id', 'operator_id')->select(['id', 'operator']);
    }
}
