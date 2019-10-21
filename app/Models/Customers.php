<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Customers extends Model
{
    use SearchableTrait;

    protected $fillable = ['id', 'email', 'first_name', 'last_name', 'mailbox_id', 'reply', 'avatar'];
    protected $dates = ['deleted_at'];
    protected $searchable = [
        'columns' => [
            'customers.email' => 1,
            'customers.first_name' => 1,
            'customers.last_name' => 1
        ]
    ];


}
