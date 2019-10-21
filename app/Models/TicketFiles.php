<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;


class TicketFiles extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table = 'ticket_files';
    protected $fillable = [
        'ticket_id',
        'file_name',
        'file_full_path',
        'file_type',
        'main_type',
        'comment_id',
        'note_id',
        'disposition',
        'cid'
    ];
    protected $dates = ['deleted_at'];

}
