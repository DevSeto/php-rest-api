<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConditionValues extends Model
{
    use SoftDeletes;

    protected $fillable = ['condition_id', 'value_id'];
    protected $dates = ['deleted_at'];
    protected $table = 'condition_values';

    public function data()
    {
        return $this->hasOne('App\Models\Workflows\ConditionValuesList', 'id', 'value_id')
            ->select(['id', 'value']);
    }

}
