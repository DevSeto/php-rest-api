<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class Tickets extends Model
{
    use SoftDeletes, SearchableTrait, CascadeSoftDeletes;

    protected $table = 'tickets';
    protected $cascadeDeletes = ['comments', 'history', 'notifications', 'notes', 'labels'];
    protected $dates = ['deleted_at'];
    protected $searchable = [
        'columns' => [
            'tickets.customer_email' => 10,
            'tickets.customer_name' => 4,
            'tickets.subject' => 8,
            'tickets.body' => 6,
            'tickets.status' => 3,
            'mailboxes.name' => 3,
            'labels.body' => 3
        ],
        'joins' => [
            'ticket_labels' => ['tickets.id', 'ticket_labels.ticket_id'],
            'labels' => ['labels.id', 'ticket_labels.label_id'],
            'mailboxes' => ['tickets.mailbox_id', 'mailboxes.id']
        ]
    ];

    protected $fillable = [
        'id',
        'owner_id',
        'mailbox_id',
        'customer_name',
        'customer_email',
        'customer_id',
        'subject',
        'status',
        'body',
        'message_id',
        'ticket_id_hash',
        'all_email_data',
        'assign_agent_id',
        'status',
        'merged',
        'color',
        'is_demo',
        'snooze'
    ];

    /**
     * Get ticket notes
     *
     * @return int (removed rows count)
     */
    public function notes()
    {
        return $this->hasMany('App\Models\Notes', 'ticket_id', 'id')->with(['author', 'files'])->withTrashed();
    }

    /**
     * Get ticket assigned user
     *
     */
    public function assignedUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'assign_agent_id');
    }

    /**
     * Get ticket assigned user
     *
     *
     */
    public function opened()
    {
        return $this->hasOne('App\Models\TicketsVisibility', 'ticket_id', 'id')->where('user_id', Helper::$user['id']);
    }

    /**
     * Get ticket assigned user
     *
     */
    public function notifications()
    {
        return $this->hasMany('App\Models\Notifications', 'ticket_id', 'id');
    }

    /**
     * Get ticket notes
     *
     * @return int (removed rows count)
     */
    public function files()
    {
        return $this->hasMany('App\Models\TicketFiles', 'ticket_id', 'ticket_id_hash')->withTrashed();
    }

    /**
     * Get ticket notes
     *
     */
    public function history()
    {
        return $this->hasMany('App\Models\TicketsHistory', 'ticket_id', 'id');
    }

    /**
     * Get ticket comments
     *
     */
    public function comments()
    {
        return $this->hasMany('App\Models\TicketComments', 'ticket_id', 'id')->with(['author', 'files'])->withTrashed();
    }


    /**
     * Get ticket comments limit 5
     *
     */
    public function commentsLimited()
    {
        return $this->hasMany('App\Models\TicketComments', 'ticket_id', 'id')->with(['author', 'files'])->orderBy('id', 'desk')->limit(6)->withTrashed();
    }

    /**
     * Get ticket comments
     *
     */
    public function comment()
    {
        return $this->hasOne('App\Models\TicketComments', 'ticket_id', 'id');
    }

    /**
     * Get merged tickets data from 'merged_tickets' table
     *
     */
    public function mergedTickets()
    {
        return $this->hasMany('App\Models\MergedTickets', 'master_ticket_id', 'id')->with(['ticket'])->withTrashed();
    }

    /**
     * Get merged tickets data from 'merged_tickets' table
     *
     */
    public function labels()
    {
//        return $this->hasMany('App\Models\TicketLabels', 'ticket_id', 'id')->with(['label']);
        return $this->hasMany('App\Models\TicketLabels', 'ticket_id', 'id')
            ->join('labels', 'ticket_labels.label_id', '=', 'labels.id')
            ->select('ticket_labels.*', 'ticket_labels.label_id', 'labels.id as label_id', 'labels.color', 'labels.body');

    }

    public function customerData()
    {
        return $this->hasOne('App\Models\Customers', 'id', 'customer_id');
    }

    public function mailbox()
    {
        return $this->hasOne('App\Models\Mailbox', 'id', 'mailbox_id');
    }

    public static function boot()
    {
        parent::boot();

        static::restored(function ($ticket) {
            $ticket->notes()->withTrashed()->restore();
            $ticket->notifications()->withTrashed()->restore();
            $ticket->comments()->withTrashed()->restore();
            $ticket->history()->withTrashed()->restore();
        });
    }
}
