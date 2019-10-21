<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedEmails extends Model
{
    protected $fillable = [
        'toEmail',
        'toName',
        'fromEmail',
        'fromName',
        'token',
        'subject',
        'messageId',
        'status',
        'attachedFiles',
        'commentText',
        'replyEmail',
        'reply_to',
        'attempts',
        'cc',
        'bcc',
        'sub_domain',
        'sent_status',
        'track'
    ];
}
