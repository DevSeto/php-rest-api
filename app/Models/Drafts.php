<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drafts extends Model
{
    protected $fillable = [
        'id',
        'ticket_id',
        'owner_id',
        'mailbox_id',
        'customer_name',
        'customer_id',
        'customer_email',
        'subject',
        'body',
        'status',
        'reply',
        'note',
        'forward'
    ];

    public function forwardingEmails()
    {
        return $this->hasMany('App\Models\ForwardedEmailsDraft', 'draft_id', 'id');
    }
}
