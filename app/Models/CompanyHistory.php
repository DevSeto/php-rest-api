<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyHistory extends Model
{

    use SoftDeletes;

    protected $table = 'company_histories';
    protected $fillable = ['type', 'item_id', 'author_id', 'content'];
    protected $dates = ['deleted_at'];

}
