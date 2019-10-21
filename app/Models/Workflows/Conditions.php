<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conditions extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'name'];
    protected $dates = ['deleted_at'];
    protected $table = 'conditions';

    public function operators()
    {
        return $this->hasMany('App\Models\Workflows\ConditionOperators', 'condition_id', 'id')
            ->select(['condition_id', 'operator_id'])
            ->with(['data']);
    }

    public function values()
    {
        return $this->hasMany('App\Models\Workflows\ConditionValues', 'condition_id', 'id')
            ->select(['condition_id', 'value_id'])
            ->with(['data']);
    }
}
