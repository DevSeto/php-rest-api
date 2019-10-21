<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class CannedReply extends Model
{
    use SoftDeletes;

    protected $table = 'canned_replies';
    protected $fillable = ['user_id', 'category_id', 'mailbox_id', 'name', 'body'];
    protected $dates = ['deleted_at'];


    public function category()
    {
        return $this->hasOne('App\Models\CannedReplyCategories', 'id', 'category_id');
    }
}
