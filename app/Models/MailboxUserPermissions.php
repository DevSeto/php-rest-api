<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxUserPermissions extends Model
{
    protected $fillable = ['user_id', 'mailbox_id'];

    function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    function mailbox()
    {
        return $this->hasOne('App\Models\Mailbox', 'id', 'mailbox_id');
    }
}
