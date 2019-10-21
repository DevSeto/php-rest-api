<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class CannedReplyCategories extends Model
{
    use SoftDeletes;

    protected $table = 'canned_reply_categories';
    protected $fillable = ['user_id', 'mailbox_id', 'name'];
    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cannedReplies()
    {
        return $this->hasMany('App\Models\CannedReply', 'category_id', 'id');
    }
}
