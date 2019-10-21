<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use SoftDeletes;

    protected $table = 'invitations';
    protected $fillable = ['sender_id', 'email', 'first_name', 'last_name', 'verified', 'role_id', 'mailbox_id'];
    protected $dates = ['deleted_at'];

}
