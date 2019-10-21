<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class Mailbox extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = [
        'creator_user_id',
        'name',
        'email',
        'default',
        'signature',
        'auto_reply',
        'auto_reply_subject',
        'auto_reply_body',
        'auto_bcc',
        'dns_name',
        'dns_value',
        'dns_verified',
        'forwarding_verified',
        'forward_address'
    ];
    protected $dates = ['deleted_at'];
    protected $cascadeDeletes = ['tickets'];

    /**
     * Delete all mailboxes
     *
     * @return int (removed rows count)
     */
    public function deleteAllMailboxes()
    {
        return DB::table('mailboxes')->update(['deleted' => 1]);
    }

    public function users()
    {
        return $this->hasMany('App\Models\MailboxUserPermissions', 'mailbox_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Tickets', 'mailbox_id', 'id');
    }

    /**
     * returns all tickets with open status by mailbox id
     * @return mixed
     *
     */
    public function open_tickets()
    {
        return $this->hasMany('App\Models\Tickets', 'mailbox_id', 'id')->where('status', 'open');
    }

    /**
     * returns all tickets with closed status by mailbox id
     * @return mixed
     *
     */
    public function closed_tickets()
    {
        return $this->hasMany('App\Models\Tickets', 'mailbox_id', 'id')->where('status', 'closed');
    }

    /**
     * returns all tickets with pending status by mailbox id
     * @return mixed
     *
     */
    public function pending_tickets()
    {
        return $this->hasMany('App\Models\Tickets', 'mailbox_id', 'id')->where('status', 'pending');
    }

    /**
     * returns all tickets with spam status by mailbox id
     * @return mixed
     *
     */
    public function spam_tickets()
    {
        return $this->hasMany('App\Models\Tickets', 'mailbox_id', 'id')->where('status', 'spam');
    }

    /**
     * returns all tickets with draft status by mailbox id
     * @return mixed
     *
     */
    public function draft_tickets()
    {
        return $this->hasMany('App\Models\Drafts', 'mailbox_id', 'id');
    }

}
